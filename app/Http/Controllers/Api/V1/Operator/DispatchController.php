<?php

namespace App\Http\Controllers\Api\V1\Operator;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExtractBulkRequest;
use App\Http\Requests\Api\V1\ExtractLoanRequest;
use App\Http\Requests\Api\V1\RearchiveBulkRequest;
use App\Http\Requests\Api\V1\RearchiveRequest;
use App\Http\Resources\Api\V1\ExpedientResource;
use App\Http\Resources\Api\V1\LoanRequestResource;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    public function toExtract(Request $request): AnonymousResourceCollection
    {
        $status = $request->input('status', 'approved');
        $search = trim($request->input('search', ''));
        $locationId = $request->input('location_id');

        $query = LoanRequest::query()
            ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester', 'approver']);

        if ($status === 'all') {
            $query->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved]);
        } elseif ($status === 'pending') {
            $query->where('status', LoanStatus::Pending);
        } else {
            $query->where('status', LoanStatus::Approved);
        }

        if (! empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('expedient', fn ($q) => $q->search($search))
                    ->orWhereHas('requester', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($locationId) {
            $query->whereHas('expedient', fn ($q) => $q->where('current_location_id', $locationId));
        }

        // Sort by location for logical picking order
        $loans = $query->orderBy('created_at', 'asc')->paginate($request->input('per_page', 20));

        return LoanRequestResource::collection($loans);
    }

    public function extract(ExtractLoanRequest $request, LoanService $loanService): JsonResponse
    {
        $loan = null;

        if ($request->filled('loan_id')) {
            $loan = LoanRequest::with(['expedient.employee', 'expedient.currentLocation', 'requester', 'approver'])->find($request->loan_id);
        } elseif ($request->filled('code')) {
            $code = trim($request->code);
            $expedient = Expedient::where('expedient_code', $code)
                ->orWhere('barcode', $code)
                ->first();

            if (! $expedient) {
                return response()->json([
                    'message' => "No se encontró ningún expediente con código: {$code}",
                ], 404);
            }

            $loan = LoanRequest::where('expedient_id', $expedient->id)
                ->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])
                ->latest()
                ->first();

            if (! $loan) {
                return response()->json([
                    'message' => "El expediente {$expedient->expedient_code} no tiene órdenes de extracción pendientes.",
                ], 422);
            }
        }

        if (! $loan) {
            return response()->json(['message' => 'Solicitud de préstamo no encontrada.'], 404);
        }

        if ($loan->status === LoanStatus::Pending) {
            return response()->json([
                'message' => "El expediente {$loan->expedient->expedient_code} aún requiere aprobación por el encargado de RH antes de poder extraerse.",
            ], 422);
        }

        if ($loan->status !== LoanStatus::Approved) {
            return response()->json([
                'message' => "La solicitud no se encuentra en un estado válido para extracción ({$loan->status->label()}).",
            ], 422);
        }

        try {
            $loanService->extractLoan($loan);
            $loan->load(['expedient.employee', 'expedient.currentLocation', 'requester', 'approver']);

            return response()->json([
                'message' => "Expediente {$loan->expedient->expedient_code} extraído y enviado a Recursos Humanos.",
                'loan' => new LoanRequestResource($loan),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al extraer expediente desde API', [
                'loan_id' => $loan?->id,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Ocurrió un error inesperado al extraer el expediente. Inténtelo de nuevo o contacte al administrador.',
            ], 500);
        }
    }

    public function extractBulk(ExtractBulkRequest $request, LoanService $loanService): JsonResponse
    {
        $loanIds = $request->input('loan_ids', []);
        $codes = $request->input('codes', []);

        if (! empty($codes)) {
            $resolvedLoans = LoanRequest::whereHas('expedient', function ($q) use ($codes) {
                $q->whereIn('expedient_code', $codes)
                    ->orWhereIn('barcode', $codes);
            })
                ->where('status', LoanStatus::Approved)
                ->pluck('id')
                ->all();

            $loanIds = array_unique(array_merge($loanIds, $resolvedLoans));
        }

        $successful = 0;
        $failed = 0;
        $pendingSkipped = 0;

        foreach ($loanIds as $id) {
            $loan = LoanRequest::find($id);
            if (! $loan) {
                $failed++;

                continue;
            }

            if ($loan->status === LoanStatus::Pending) {
                $pendingSkipped++;

                continue;
            }

            if ($loan->status === LoanStatus::Approved) {
                try {
                    $loanService->extractLoan($loan);
                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        return response()->json([
            'successful' => $successful,
            'failed' => $failed,
            'pending_skipped' => $pendingSkipped,
            'message' => "Se procesaron {$successful} expedientes extraídos.",
        ]);
    }

    public function toReturn(Request $request): AnonymousResourceCollection
    {
        $search = trim($request->input('search', ''));
        $locationId = $request->input('location_id');

        $query = LoanRequest::query()
            ->where('status', LoanStatus::Returned)
            ->whereHas('expedient', fn ($q) => $q->where('current_status', ExpedientStatus::Returned))
            ->with(['expedient.employee', 'expedient.currentLocation.branch', 'requester', 'approver']);

        if (! empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('expedient', fn ($q) => $q->search($search))
                    ->orWhereHas('requester', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($locationId) {
            $query->whereHas('expedient', fn ($q) => $q->where('current_location_id', $locationId));
        }

        $items = $query->orderBy('returned_at', 'asc')->paginate($request->input('per_page', 20));

        return LoanRequestResource::collection($items);
    }

    public function rearchive(RearchiveRequest $request, LoanService $loanService): JsonResponse
    {
        $expedient = null;

        if ($request->filled('expedient_id')) {
            $expedient = Expedient::with(['employee', 'currentLocation'])->find($request->expedient_id);
        } elseif ($request->filled('code')) {
            $code = trim($request->code);
            $expedient = Expedient::with(['employee', 'currentLocation'])
                ->where('expedient_code', $code)
                ->orWhere('barcode', $code)
                ->first();
        }

        if (! $expedient) {
            return response()->json(['message' => 'Expediente no encontrado.'], 404);
        }

        try {
            if ($expedient->current_status === ExpedientStatus::Returned) {
                $loanService->rearchiveExpedient($expedient);
                $locationName = $expedient->currentLocation ? $expedient->currentLocation->full_label : 'su gaveta';

                return response()->json([
                    'message' => "Expediente {$expedient->expedient_code} guardado correctamente en {$locationName}.",
                    'expedient' => new ExpedientResource($expedient->fresh(['employee', 'currentLocation'])),
                ]);
            }

            if ($expedient->current_status === ExpedientStatus::Loaned) {
                // If the employee returned directly to physical archive PB
                $activeLoan = LoanRequest::where('expedient_id', $expedient->id)
                    ->where('status', LoanStatus::Delivered)
                    ->latest()
                    ->first();

                if ($activeLoan) {
                    $loanService->returnLoan($activeLoan);
                }
                $loanService->rearchiveExpedient($expedient);
                $locationName = $expedient->currentLocation ? $expedient->currentLocation->full_label : 'su gaveta';

                return response()->json([
                    'message' => "Devolución registrada y expediente {$expedient->expedient_code} guardado en {$locationName}.",
                    'expedient' => new ExpedientResource($expedient->fresh(['employee', 'currentLocation'])),
                ]);
            }

            if ($expedient->current_status === ExpedientStatus::Available) {
                return response()->json([
                    'message' => "El expediente {$expedient->expedient_code} ya se encuentra disponible en su gaveta.",
                    'expedient' => new ExpedientResource($expedient),
                ]);
            }

            return response()->json([
                'message' => "El expediente {$expedient->expedient_code} no se puede re-archivar porque su estado actual es {$expedient->current_status->label()}.",
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al procesar re-archivo desde API', [
                'expedient_id' => $expedient->id,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar el re-archivo. Inténtelo de nuevo o contacte al administrador.',
            ], 500);
        }
    }

    public function rearchiveBulk(RearchiveBulkRequest $request, LoanService $loanService): JsonResponse
    {
        $expedientIds = $request->input('expedient_ids', []);
        $codes = $request->input('codes', []);

        if (! empty($codes)) {
            $resolvedIds = Expedient::where(function ($q) use ($codes) {
                $q->whereIn('expedient_code', $codes)
                    ->orWhereIn('barcode', $codes);
            })
                ->pluck('id')
                ->all();

            $expedientIds = array_unique(array_merge($expedientIds, $resolvedIds));
        }

        $successful = 0;
        $failed = 0;

        foreach ($expedientIds as $id) {
            $expedient = Expedient::find($id);
            if (! $expedient) {
                $failed++;

                continue;
            }

            try {
                if ($expedient->current_status === ExpedientStatus::Returned || $expedient->current_status === ExpedientStatus::Loaned) {
                    if ($expedient->current_status === ExpedientStatus::Loaned) {
                        $activeLoan = LoanRequest::where('expedient_id', $expedient->id)
                            ->where('status', LoanStatus::Delivered)
                            ->latest()
                            ->first();
                        if ($activeLoan) {
                            $loanService->returnLoan($activeLoan);
                        }
                    }

                    $loanService->rearchiveExpedient($expedient);
                    $successful++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'successful' => $successful,
            'failed' => $failed,
            'message' => "Se re-archivaron {$successful} expedientes correctamente.",
        ]);
    }
}
