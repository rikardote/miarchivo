<?php

namespace App\Http\Controllers\Api\V1\Operator;

use App\Enums\ExpedientStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuditScanRequest;
use App\Http\Requests\Api\V1\FixMisplacedRequest;
use App\Http\Resources\Api\V1\ArchiveLocationResource;
use App\Http\Resources\Api\V1\ExpedientResource;
use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Services\ExpedientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class AuditController extends Controller
{
    public function locations(Request $request): AnonymousResourceCollection
    {
        $query = ArchiveLocation::with('branch')
            ->where('is_active', true);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        $locations = $query->withCount('expedients')
            ->orderBy('archive_name')
            ->orderBy('cabinet')
            ->orderBy('drawer')
            ->get();

        return ArchiveLocationResource::collection($locations);
    }

    public function status(int $locationId): JsonResponse
    {
        $location = ArchiveLocation::with('branch')->findOrFail($locationId);
        $scannedCodes = Cache::get("active_audit_{$locationId}", []);

        $results = $this->calculateAuditResults($locationId, $scannedCodes);

        return response()->json([
            'location' => new ArchiveLocationResource($location),
            'scanned_codes' => $scannedCodes,
            'summary' => [
                'total_expected' => count($results['missing']) + count($results['correct']),
                'total_scanned' => count($scannedCodes),
                'correct_count' => count($results['correct']),
                'misplaced_count' => count($results['misplaced']),
                'missing_count' => count($results['missing']),
            ],
            'results' => [
                'correct' => ExpedientResource::collection($results['correct']),
                'misplaced' => ExpedientResource::collection($results['misplaced']),
                'missing' => ExpedientResource::collection($results['missing']),
            ],
        ]);
    }

    public function scan(AuditScanRequest $request): JsonResponse
    {
        $locationId = (int) $request->location_id;
        $rawCode = trim($request->code);

        $location = ArchiveLocation::findOrFail($locationId);

        // Resolve code
        $expedient = Expedient::with(['employee', 'currentLocation'])
            ->where('expedient_code', $rawCode)
            ->orWhere('barcode', $rawCode)
            ->first();

        $canonicalCode = $expedient ? $expedient->expedient_code : $rawCode;
        $cachedCodes = Cache::get("active_audit_{$locationId}", []);

        $isNewScan = false;
        if (! in_array($canonicalCode, $cachedCodes)) {
            $cachedCodes[] = $canonicalCode;
            Cache::put("active_audit_{$locationId}", $cachedCodes, now()->addHours(6));
            $isNewScan = true;
        }

        $isCorrect = $expedient && $expedient->current_location_id === $locationId;
        $isMisplaced = $expedient && $expedient->current_location_id !== $locationId;
        $isUnknown = ! $expedient;

        return response()->json([
            'is_new_scan' => $isNewScan,
            'status' => $isCorrect ? 'correct' : ($isMisplaced ? 'misplaced' : 'unknown'),
            'message' => $isCorrect
                ? "Expediente {$canonicalCode} correcto en esta ubicación."
                : ($isMisplaced
                    ? "Expediente {$canonicalCode} fuera de lugar (Ubicación actual: ".($expedient->currentLocation?->short_label ?? 'Sin asignar').').'
                    : "Código {$rawCode} no corresponde a ningún expediente registrado."),
            'expedient' => $expedient ? new ExpedientResource($expedient) : null,
            'total_scanned' => count($cachedCodes),
        ]);
    }

    public function fixMisplaced(FixMisplacedRequest $request, ExpedientService $service): JsonResponse
    {
        $locationId = (int) $request->location_id;
        $location = ArchiveLocation::findOrFail($locationId);

        if ($request->boolean('all')) {
            $scannedCodes = Cache::get("active_audit_{$locationId}", []);
            $results = $this->calculateAuditResults($locationId, $scannedCodes);

            $fixedCount = 0;
            foreach ($results['misplaced'] as $exp) {
                $service->changeLocation($exp, $locationId, 'Corregido masivamente durante auditoría Android');
                $fixedCount++;
            }

            return response()->json([
                'message' => "Se corrigió la ubicación de {$fixedCount} expedientes.",
                'fixed_count' => $fixedCount,
            ]);
        }

        $expedient = Expedient::findOrFail($request->expedient_id);
        $service->changeLocation($expedient, $locationId, 'Corregido durante auditoría Android');

        return response()->json([
            'message' => "Ubicación corregida para el expediente {$expedient->expedient_code}.",
            'expedient' => new ExpedientResource($expedient->fresh(['employee', 'currentLocation'])),
        ]);
    }

    public function reset(int $locationId): JsonResponse
    {
        Cache::forget("active_audit_{$locationId}");

        return response()->json([
            'message' => 'Sesión de auditoría reiniciada correctamente.',
        ]);
    }

    private function calculateAuditResults(int $locationId, array $scannedCodes): array
    {
        $expectedExpedients = Expedient::with(['employee', 'currentLocation.branch'])
            ->where('current_location_id', $locationId)
            ->whereIn('current_status', [
                ExpedientStatus::Available,
                ExpedientStatus::Returned,
                ExpedientStatus::Archived,
                ExpedientStatus::InStorage,
                ExpedientStatus::Reserved,
            ])
            ->get();

        $results = [
            'correct' => collect(),
            'misplaced' => collect(),
            'missing' => collect(),
        ];

        if (! empty($scannedCodes)) {
            $scannedExpedients = Expedient::with(['employee', 'currentLocation.branch'])
                ->whereIn('expedient_code', $scannedCodes)
                ->get()
                ->keyBy('expedient_code');

            foreach ($scannedCodes as $code) {
                if ($scannedExpedients->has($code)) {
                    $expedient = $scannedExpedients->get($code);
                    if ($expedient->current_location_id == $locationId) {
                        $results['correct']->push($expedient);
                    } else {
                        $results['misplaced']->push($expedient);
                    }
                }
            }

            foreach ($expectedExpedients as $exp) {
                if (! in_array($exp->expedient_code, $scannedCodes)) {
                    $results['missing']->push($exp);
                }
            }
        } else {
            $results['missing'] = $expectedExpedients;
        }

        return $results;
    }
}
