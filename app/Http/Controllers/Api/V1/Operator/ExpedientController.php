<?php

namespace App\Http\Controllers\Api\V1\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RelocateExpedientRequest;
use App\Http\Requests\Api\V1\ReportStatusRequest;
use App\Http\Resources\Api\V1\ExpedientResource;
use App\Models\Expedient;
use App\Services\ExpedientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpedientController extends Controller
{
    public function search(Request $request): AnonymousResourceCollection
    {
        $search = trim($request->input('q', $request->input('search', '')));
        $status = $request->input('status');
        $locationId = $request->input('location_id');

        $query = Expedient::query()
            ->with(['employee.department', 'employee.branch', 'currentLocation.branch', 'currentHolder']);

        if (! empty($search)) {
            $query->search($search);
        }

        if (! empty($status)) {
            $query->where('current_status', $status);
        }

        if ($locationId) {
            $query->where('current_location_id', $locationId);
        }

        $expedients = $query->orderBy('expedient_code')
            ->paginate($request->input('per_page', 20));

        return ExpedientResource::collection($expedients);
    }

    public function lookup(string $code): ExpedientResource|JsonResponse
    {
        $code = trim($code);
        if (str_contains($code, '/')) {
            $parts = explode('/', rtrim($code, '/'));
            $code = end($parts);
        }

        $expedient = Expedient::where('expedient_code', $code)
            ->orWhere('barcode', $code)
            ->with([
                'employee.department',
                'employee.branch',
                'currentLocation.branch',
                'currentHolder',
                'movements.fromLocation.branch',
                'movements.toLocation.branch',
                'movements.user',
                'loanRequests.requester',
            ])
            ->first();

        if (! $expedient) {
            return response()->json([
                'message' => "Expediente no encontrado para el código: {$code}",
            ], 404);
        }

        return new ExpedientResource($expedient);
    }

    public function relocate(int $id, RelocateExpedientRequest $request, ExpedientService $service): JsonResponse
    {
        $expedient = Expedient::findOrFail($id);
        $newLocationId = (int) $request->location_id;
        $notes = $request->input('notes', 'Reubicado desde aplicación móvil Android');

        $service->changeLocation($expedient, $newLocationId, $notes);

        $expedient->load(['employee', 'currentLocation.branch', 'movements' => fn ($q) => $q->latest()->take(5)]);

        return response()->json([
            'message' => "Expediente {$expedient->expedient_code} reubicado exitosamente en {$expedient->currentLocation?->full_label}.",
            'expedient' => new ExpedientResource($expedient),
        ]);
    }

    public function reportLost(int $id, ReportStatusRequest $request, ExpedientService $service): JsonResponse
    {
        $expedient = Expedient::findOrFail($id);
        $notes = $request->input('notes', 'Reportado como extraviado desde aplicación móvil Android');

        $service->reportLost($expedient, $notes);

        return response()->json([
            'message' => "Expediente {$expedient->expedient_code} reportado como extraviado.",
            'expedient' => new ExpedientResource($expedient->fresh(['employee', 'currentLocation'])),
        ]);
    }

    public function reportFound(int $id, ReportStatusRequest $request, ExpedientService $service): JsonResponse
    {
        $expedient = Expedient::findOrFail($id);
        $notes = $request->input('notes', 'Localizado y recuperado desde aplicación móvil Android');

        $service->reportFound($expedient, $notes);

        return response()->json([
            'message' => "Expediente {$expedient->expedient_code} marcado como disponible.",
            'expedient' => new ExpedientResource($expedient->fresh(['employee', 'currentLocation'])),
        ]);
    }
}
