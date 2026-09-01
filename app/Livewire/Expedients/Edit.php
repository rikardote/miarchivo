<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Services\ExpedientService;
use Livewire\Component;
use Mary\Traits\Toast;

class Edit extends Component
{
    use Toast;

    public Expedient $expedient;

    public ?string $selectedCabinet = null;
    public ?int $location_id = null;
    public string $movement_notes = '';

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient;
        $this->location_id = $expedient->current_location_id;
        if ($this->location_id) {
            $currentLoc = ArchiveLocation::find($this->location_id);
            $this->selectedCabinet = $currentLoc?->cabinet;
        }
    }

    public function updatedSelectedCabinet($value)
    {
        if ($this->location_id) {
            $currentLoc = ArchiveLocation::find($this->location_id);
            if (!$currentLoc || $currentLoc->cabinet !== $value) {
                $this->location_id = null;
            }
        }
    }

    public function save(ExpedientService $expedientService)
    {
        $this->validate([
            'selectedCabinet' => 'required',
            'location_id' => 'required|exists:archive_locations,id',
            'movement_notes' => 'nullable|string|max:255',
        ], [
            'selectedCabinet.required' => 'Debes seleccionar una gaveta o archivero.',
            'location_id.required' => 'Debes seleccionar un cajón.',
        ]);

        try {
            if ($this->location_id !== $this->expedient->current_location_id) {
                $expedientService->changeLocation(
                    $this->expedient, 
                    $this->location_id, 
                    $this->movement_notes ?: 'Actualización de ubicación física vía edición'
                );
            }

            $this->success('Expediente actualizado correctamente.', position: 'toast-top toast-end');
            return redirect()->route('expedients.show', $this->expedient);
            
        } catch (\Exception $e) {
            $this->error('Ocurrió un error al actualizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $cabinets = ArchiveLocation::where('is_active', true)
            ->whereNotNull('cabinet')
            ->select('cabinet', 'archive_name')
            ->distinct()
            ->orderBy('cabinet')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->cabinet,
                    'name' => "Gaveta / Archivero {$item->cabinet}",
                ];
            });

        $drawers = collect();
        if ($this->selectedCabinet) {
            $drawers = ArchiveLocation::where('is_active', true)
                ->where('cabinet', $this->selectedCabinet)
                ->orderBy('drawer')
                ->get()
                ->map(function ($item) {
                    $label = "Cajón {$item->drawer}";
                    if ($item->alpha_range) {
                        $label .= "  —  [ Rango: {$item->alpha_range} ]";
                    }
                    return [
                        'id' => $item->id,
                        'name' => $label,
                    ];
                });
        }

        return view('livewire.expedients.edit', [
            'cabinets' => $cabinets,
            'drawers' => $drawers,
        ]);
    }
}

