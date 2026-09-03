<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Expedient;
use App\Models\LocationAudit;
use App\Services\ExpedientService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Mary\Traits\Toast;

class Audit extends Component
{
    use Toast;

    public ?int $location_id = null;

    public ?int $selectedBranch = null;

    public ?string $selectedType = null;

    public array $scanned_codes = [];

    public string $current_scan = '';

    public ?string $audit_notes = null;

    public bool $saved_audit = false;

    public bool $is_auditing = false;

    public function mount()
    {
        $this->authorize('changeLocation', Expedient::class);
    }

    public function startAudit()
    {
        $this->validate([
            'location_id' => 'required|exists:archive_locations,id',
        ]);
        $this->is_auditing = true;
        $this->scanned_codes = Cache::get("active_audit_{$this->location_id}", []);
    }

    public function addScan(?string $code = null)
    {
        $rawCode = trim($code ?: $this->current_scan);
        if (empty($rawCode)) {
            return;
        }

        // Resolve barcode or QR code to actual expedient_code if applicable
        $expedient = Expedient::where('expedient_code', $rawCode)
            ->orWhere('barcode', $rawCode)
            ->first();

        $canonicalCode = $expedient ? $expedient->expedient_code : $rawCode;

        // Pull latest from cache to ensure multi-device synchronization
        $cachedCodes = Cache::get("active_audit_{$this->location_id}", $this->scanned_codes);

        if (! in_array($canonicalCode, $cachedCodes)) {
            $cachedCodes[] = $canonicalCode;
            $this->scanned_codes = $cachedCodes;
            Cache::put("active_audit_{$this->location_id}", $cachedCodes, now()->addHours(6));
            $this->success('Escaneado: '.$canonicalCode);
        } else {
            $this->warning('Ya escaneado en esta sesión: '.$canonicalCode);
        }

        $this->current_scan = '';
    }

    public function resetAudit()
    {
        if ($this->location_id) {
            Cache::forget("active_audit_{$this->location_id}");
        }
        $this->reset(['location_id', 'scanned_codes', 'is_auditing', 'current_scan', 'audit_notes', 'saved_audit']);
    }

    public function saveAuditReport()
    {
        if (! $this->location_id || ! $this->is_auditing) {
            return;
        }

        $results = $this->getResults();
        $expectedCount = Expedient::where('current_location_id', $this->location_id)->count();

        $audit = LocationAudit::create([
            'archive_location_id' => $this->location_id,
            'user_id' => Auth::id(),
            'expected_count' => $expectedCount,
            'scanned_count' => count($this->scanned_codes),
            'correct_count' => count($results['correct']),
            'missing_count' => count($results['missing']),
            'misplaced_count' => count($results['misplaced']),
            'details' => [
                'correct_codes' => array_map(fn ($e) => $e->expedient_code, $results['correct']),
                'missing_codes' => array_map(fn ($e) => $e->expedient_code, $results['missing']),
                'misplaced_codes' => array_map(fn ($e) => [
                    'code' => $e->expedient_code,
                    'original_location' => $e->currentLocation?->short_label ?? 'N/A',
                ], $results['misplaced']),
            ],
            'notes' => $this->audit_notes,
        ]);

        $this->saved_audit = true;
        $this->success("Acta de auditoría #{$audit->id} guardada exitosamente en el historial persistente.");
    }

    public function fixMisplaced(int $expedientId, ExpedientService $service)
    {
        $expedient = Expedient::findOrFail($expedientId);
        $service->changeLocation($expedient, $this->location_id, "Corregido durante auditoría de ubicación ID: {$this->location_id}");
        $this->success("Ubicación corregida para: {$expedient->expedient_code}");
    }

    public function fixAllMisplaced(ExpedientService $service)
    {
        $misplaced = $this->getResults()['misplaced'];

        foreach ($misplaced as $exp) {
            $service->changeLocation($exp, $this->location_id, 'Corregido masivamente durante auditoría');
        }

        $this->success('Se corrigieron '.count($misplaced).' expedientes.');
    }

    private function getResults()
    {
        $expectedExpedients = $this->location_id
            ? Expedient::with(['employee', 'currentLocation'])
                ->where('current_location_id', $this->location_id)
                ->whereIn('current_status', ['available', 'returned', 'archived', 'in_storage', 'reserved'])
                ->get()
            : collect();

        $results = [
            'correct' => [],
            'misplaced' => [],
            'missing' => [],
        ];

        if ($this->is_auditing && ! empty($this->scanned_codes)) {
            $scannedExpedients = Expedient::with(['employee', 'currentLocation'])
                ->whereIn('expedient_code', $this->scanned_codes)
                ->get()
                ->keyBy('expedient_code');

            foreach ($this->scanned_codes as $code) {
                if ($scannedExpedients->has($code)) {
                    $expedient = $scannedExpedients->get($code);
                    if ($expedient->current_location_id == $this->location_id) {
                        $results['correct'][] = $expedient;
                    } else {
                        $results['misplaced'][] = $expedient;
                    }
                }
            }

            foreach ($expectedExpedients as $exp) {
                if (! in_array($exp->expedient_code, $this->scanned_codes)) {
                    $results['missing'][] = $exp;
                }
            }
        }

        return $results;
    }

    public function render()
    {
        if ($this->is_auditing && $this->location_id) {
            $this->scanned_codes = Cache::get("active_audit_{$this->location_id}", $this->scanned_codes);
        }

        $expectedCount = $this->location_id
            ? Expedient::where('current_location_id', $this->location_id)->count()
            : 0;

        $locationsQuery = ArchiveLocation::with('branch')
            ->when($this->selectedBranch, fn ($q) => $q->where('branch_id', $this->selectedBranch))
            ->when($this->selectedType, fn ($q) => $q->where('location_type', $this->selectedType));

        $pastAudits = LocationAudit::with(['user', 'location'])
            ->when($this->location_id, fn ($q) => $q->where('archive_location_id', $this->location_id))
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.expedients.audit', [
            'branches' => Branch::all(),
            'types' => [
                ['id' => 'Archivo Muerto', 'name' => 'Archivo Muerto'],
                ['id' => 'Archivo Activo', 'name' => 'Archivo Activo'],
                ['id' => 'Almacén Central', 'name' => 'Almacén Central'],
            ],
            'locations' => $locationsQuery->get(),
            'results' => $this->getResults(),
            'expectedCount' => $expectedCount,
            'pastAudits' => $pastAudits,
        ]);
    }
}
