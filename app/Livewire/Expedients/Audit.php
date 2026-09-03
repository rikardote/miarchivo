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

    public ?string $selectedCabinet = null;

    public ?int $selectedCabinetBlock = 0;

    public string $locationSearch = '';

    public array $scanned_codes = [];

    public string $current_scan = '';

    public ?string $audit_notes = null;

    public bool $saved_audit = false;

    public bool $is_auditing = false;

    public string $activeTab = 'missing';

    public string $searchFilter = '';

    public bool $showCamera = false;

    public bool $showNotesModal = false;

    public ?ArchiveLocation $currentLocation = null;

    public function selectCabinet(?string $cabinet): void
    {
        $this->selectedCabinet = $this->selectedCabinet === $cabinet ? null : $cabinet;
    }

    public function mount()
    {
        $this->authorize('changeLocation', Expedient::class);
    }

    public function selectLocationAndStart(int $id): void
    {
        $this->location_id = $id;
        $this->startAudit();
    }

    public function startAudit()
    {
        $this->validate([
            'location_id' => 'required|exists:archive_locations,id',
        ]);
        $this->is_auditing = true;
        $this->scanned_codes = Cache::get("active_audit_{$this->location_id}", []);
        $this->currentLocation = ArchiveLocation::with('branch')->find($this->location_id);

        // Registrar auditoría activa del usuario para canalizar la pistola celular
        if (Auth::check() && $this->currentLocation) {
            Cache::put('active_user_audit_'.Auth::id(), [
                'location_id' => $this->location_id,
                'label' => $this->currentLocation->short_label,
            ], now()->addHours(2));
        }
    }

    /**
     * Escucha en vivo cada 800ms las lecturas transmitidas desde el celular en modo pistola.
     */
    public function checkRemoteGunAuditScans(): void
    {
        if (! $this->is_auditing || ! $this->location_id || ! Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $code = Cache::pull("scanner_gun_user_{$userId}");

        if ($code) {
            $code = trim($code);
            $this->addScan($code);
            $this->dispatch('audit-remote-gun-beep', ['code' => $code]);
        }
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

    public function removeScan(string $code): void
    {
        $this->scanned_codes = array_values(array_filter($this->scanned_codes, fn ($c) => $c !== $code));
        if ($this->location_id) {
            Cache::put("active_audit_{$this->location_id}", $this->scanned_codes, now()->addHours(6));
        }
        $this->info("Escaneo removido: {$code}");
    }

    public function resetAudit()
    {
        if ($this->location_id) {
            Cache::forget("active_audit_{$this->location_id}");
        }
        if (Auth::check()) {
            Cache::forget('active_user_audit_'.Auth::id());
        }
        $this->reset(['location_id', 'scanned_codes', 'is_auditing', 'current_scan', 'audit_notes', 'saved_audit', 'currentLocation', 'searchFilter', 'showCamera', 'showNotesModal']);
        $this->activeTab = 'missing';
    }

    public function saveAuditReport()
    {
        if (! $this->location_id || ! $this->is_auditing) {
            return;
        }

        if (Auth::check()) {
            Cache::forget('active_user_audit_'.Auth::id());
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
        $this->showNotesModal = false;
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
            ? Expedient::with(['employee.branch', 'currentLocation.branch'])
                ->where('current_location_id', $this->location_id)
                ->whereIn('current_status', ['available', 'returned', 'archived', 'in_storage', 'reserved'])
                ->get()
            : collect();

        $results = [
            'correct' => [],
            'misplaced' => [],
            'missing' => [],
        ];

        if ($this->is_auditing) {
            $scannedExpedients = ! empty($this->scanned_codes)
                ? Expedient::with(['employee.branch', 'currentLocation.branch'])
                    ->whereIn('expedient_code', $this->scanned_codes)
                    ->get()
                    ->keyBy('expedient_code')
                : collect();

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

        $cabinets = ArchiveLocation::query()
            ->when($this->selectedBranch, fn ($q) => $q->where('branch_id', $this->selectedBranch))
            ->when($this->selectedType, fn ($q) => $q->where('location_type', $this->selectedType))
            ->whereNotNull('cabinet')
            ->where('cabinet', '!=', '')
            ->distinct()
            ->orderBy('cabinet')
            ->pluck('cabinet');

        // Agrupar gabinetes en bloques de 5 en 5 (ej. G-01 — G-05)
        $cabinetBlocks = [];
        foreach ($cabinets->chunk(5) as $index => $chunk) {
            $first = $chunk->first();
            $last = $chunk->last();
            $cabinetBlocks[$index] = [
                'index' => $index,
                'label' => $first === $last ? $first : "{$first} — {$last}",
                'cabinets' => $chunk->values()->all(),
            ];
        }

        $activeCabinetFilter = null;
        if (! is_null($this->selectedCabinetBlock) && isset($cabinetBlocks[$this->selectedCabinetBlock]) && empty($this->locationSearch)) {
            $activeCabinetFilter = $cabinetBlocks[$this->selectedCabinetBlock]['cabinets'];
        }

        $locationsQuery = ArchiveLocation::with('branch')
            ->withCount('expedients')
            ->when($this->selectedBranch, fn ($q) => $q->where('branch_id', $this->selectedBranch))
            ->when($this->selectedType, fn ($q) => $q->where('location_type', $this->selectedType))
            ->when($this->selectedCabinet, fn ($q) => $q->where('cabinet', $this->selectedCabinet))
            ->when(! $this->selectedCabinet && ! empty($activeCabinetFilter), fn ($q) => $q->whereIn('cabinet', $activeCabinetFilter))
            ->when($this->locationSearch, function ($q) {
                $term = '%'.$this->locationSearch.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('cabinet', 'like', $term)
                        ->orWhere('drawer', 'like', $term)
                        ->orWhere('archive_name', 'like', $term)
                        ->orWhere('alpha_range', 'like', $term);
                });
            })
            ->orderBy('cabinet')
            ->orderBy('drawer');

        $pastAudits = LocationAudit::with(['user', 'location'])
            ->when($this->location_id, fn ($q) => $q->where('archive_location_id', $this->location_id))
            ->latest()
            ->take(8)
            ->get();

        $results = $this->getResults();
        $correctCount = count($results['correct']);
        $progressPercentage = $expectedCount > 0
            ? min(100, (int) round(($correctCount / $expectedCount) * 100))
            : 0;

        $locationsList = $locationsQuery->get();
        $groupedLocations = $locationsList->groupBy(fn ($loc) => $loc->cabinet ?: ($loc->archive_name ?: 'Sin Gabinete'));

        return view('livewire.expedients.audit', [
            'branches' => Branch::all(),
            'types' => [
                ['id' => 'Archivo Muerto', 'name' => 'Archivo Muerto'],
                ['id' => 'Archivo Activo', 'name' => 'Archivo Activo'],
                ['id' => 'Almacén Central', 'name' => 'Almacén Central'],
            ],
            'cabinets' => $cabinets,
            'cabinetBlocks' => $cabinetBlocks,
            'locations' => $locationsList,
            'groupedLocations' => $groupedLocations,
            'results' => $results,
            'expectedCount' => $expectedCount,
            'progressPercentage' => $progressPercentage,
            'pastAudits' => $pastAudits,
        ]);
    }
}
