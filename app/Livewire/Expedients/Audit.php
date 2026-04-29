<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Expedient;
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
    
    public bool $is_auditing = false;

    public function mount()
    {
        $this->authorize('changeLocation', \App\Models\Expedient::class);
    }

    public function startAudit()
    {
        $this->validate([
            'location_id' => 'required|exists:archive_locations,id'
        ]);
        $this->is_auditing = true;
        $this->scanned_codes = [];
    }

    public function addScan()
    {
        if (empty($this->current_scan)) return;

        if (!in_array($this->current_scan, $this->scanned_codes)) {
            $this->scanned_codes[] = $this->current_scan;
            $this->success("Escaneado: " . $this->current_scan);
        } else {
            $this->warning("Ya escaneado: " . $this->current_scan);
        }

        $this->current_scan = '';
    }

    public function resetAudit()
    {
        $this->reset(['location_id', 'scanned_codes', 'is_auditing', 'current_scan']);
    }

    public function fixMisplaced(int $expedientId, \App\Services\ExpedientService $service)
    {
        $expedient = Expedient::findOrFail($expedientId);
        $service->changeLocation($expedient, $this->location_id, "Corregido durante auditoría de ubicación ID: {$this->location_id}");
        $this->success("Ubicación corregida para: {$expedient->expedient_code}");
    }

    public function fixAllMisplaced(\App\Services\ExpedientService $service)
    {
        $misplaced = $this->getResults()['misplaced'];
        
        foreach ($misplaced as $exp) {
            $service->changeLocation($exp, $this->location_id, "Corregido masivamente durante auditoría");
        }

        $this->success("Se corrigieron " . count($misplaced) . " expedientes.");
    }

    private function getResults()
    {
        $expectedExpedients = $this->location_id 
            ? Expedient::where('current_location_id', $this->location_id)
                ->whereIn('current_status', ['available', 'returned', 'archived', 'in_storage', 'reserved'])
                ->get()
            : collect();

        $results = [
            'correct' => [],
            'misplaced' => [],
            'missing' => [],
        ];

        if ($this->is_auditing) {
            foreach ($this->scanned_codes as $code) {
                $expedient = Expedient::where('expedient_code', $code)->first();
                if ($expedient) {
                    if ($expedient->current_location_id == $this->location_id) {
                        $results['correct'][] = $expedient;
                    } else {
                        $results['misplaced'][] = $expedient;
                    }
                }
            }

            foreach ($expectedExpedients as $exp) {
                if (!in_array($exp->expedient_code, $this->scanned_codes)) {
                    $results['missing'][] = $exp;
                }
            }
        }

        return $results;
    }

    public function render()
    {
        $expectedCount = $this->location_id 
            ? Expedient::where('current_location_id', $this->location_id)->count()
            : 0;

        $locationsQuery = ArchiveLocation::with('branch')
            ->when($this->selectedBranch, fn($q) => $q->where('branch_id', $this->selectedBranch))
            ->when($this->selectedType, fn($q) => $q->where('location_type', $this->selectedType));

        return view('livewire.expedients.audit', [
            'branches' => \App\Models\Branch::all(),
            'types' => [
                ['id' => 'Archivo Muerto', 'name' => 'Archivo Muerto'],
                ['id' => 'Archivo Activo', 'name' => 'Archivo Activo'],
                ['id' => 'Almacén Central', 'name' => 'Almacén Central'],
            ],
            'locations' => $locationsQuery->get(),
            'results' => $this->getResults(),
            'expectedCount' => $expectedCount,
        ]);
    }
}
