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

    public ?int $location_id = null;
    public string $movement_notes = '';

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient;
        $this->location_id = $expedient->current_location_id;
    }

    public function save(ExpedientService $expedientService)
    {
        $this->validate([
            'location_id' => 'required|exists:archive_locations,id',
            'movement_notes' => 'nullable|string|max:255',
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
        return view('livewire.expedients.edit', [
            'locations' => ArchiveLocation::orderBy('archive_name')->get(),
        ]);
    }
}
