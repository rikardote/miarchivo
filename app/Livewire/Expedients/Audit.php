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
    public array $scanned_codes = [];
    public string $current_scan = '';
    
    public bool $is_auditing = false;

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

    public function render()
    {
        $expectedExpedients = $this->location_id 
            ? Expedient::where('current_location_id', $this->location_id)->get()
            : collect();

        $results = [
            'correct' => [],
            'misplaced' => [],
            'missing' => [],
        ];

        if ($this->is_auditing) {
            // Check scanned against expected
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

            // Check what's missing from expected
            foreach ($expectedExpedients as $exp) {
                if (!in_array($exp->expedient_code, $this->scanned_codes)) {
                    $results['missing'][] = $exp;
                }
            }
        }

        return view('livewire.expedients.audit', [
            'locations' => ArchiveLocation::with('branch')->get(),
            'results' => $results,
            'expectedCount' => $expectedExpedients->count(),
        ]);
    }
}
