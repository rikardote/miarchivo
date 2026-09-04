<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Services\ExpedientService;
use Carbon\Carbon;
use Livewire\Component;
use Mary\Traits\Toast;

class Edit extends Component
{
    use Toast;

    public Expedient $expedient;

    public ?string $selectedCabinet = null;

    public ?int $location_id = null;

    public ?int $volume_number = null;

    public ?string $opened_at = null;

    public string $movement_notes = '';

    public function mount(Expedient $expedient)
    {
        $this->authorize('update', $expedient);

        $this->expedient = $expedient->load(['employee', 'currentLocation']);
        $this->location_id = $expedient->current_location_id;
        if ($this->location_id) {
            $this->selectedCabinet = $expedient->currentLocation?->cabinet;
        }
        $this->volume_number = $expedient->volume_number ?? 1;
        $this->opened_at = $expedient->opened_at ? $expedient->opened_at->format('Y-m-d') : null;
    }

    public function updatedSelectedCabinet($value)
    {
        if ($this->location_id) {
            $currentLoc = ArchiveLocation::find($this->location_id);
            if (! $currentLoc || $currentLoc->cabinet !== $value) {
                $this->location_id = null;
            }
        }
    }

    public function save(ExpedientService $expedientService)
    {
        $this->authorize('update', $this->expedient);

        $this->validate([
            'selectedCabinet' => 'required',
            'location_id' => 'required|exists:archive_locations,id',
            'volume_number' => 'required|integer|min:1|max:99',
            'opened_at' => 'nullable|date',
            'movement_notes' => 'nullable|string|max:255',
        ], [
            'selectedCabinet.required' => 'Debes seleccionar una gaveta o archivero.',
            'location_id.required' => 'Debes seleccionar un cajón de archivo.',
            'volume_number.required' => 'El número de tomo es obligatorio.',
            'volume_number.min' => 'El número de tomo debe ser mayor o igual a 1.',
        ]);

        try {
            // Actualizar ubicación física si cambió
            if ($this->location_id !== $this->expedient->current_location_id) {
                $expedientService->changeLocation(
                    $this->expedient,
                    $this->location_id,
                    $this->movement_notes ?: 'Actualización de ubicación física vía edición'
                );
            }

            // Actualizar metadatos físicos
            $dirty = false;
            if ($this->volume_number && $this->volume_number !== $this->expedient->volume_number) {
                $this->expedient->volume_number = $this->volume_number;
                $baseRfc = strtoupper($this->expedient->employee?->rfc ?? 'EXP');
                $this->expedient->expedient_code = "{$baseRfc}-V{$this->volume_number}";
                $dirty = true;
            }

            $parsedDate = $this->opened_at ? Carbon::parse($this->opened_at)->startOfDay() : null;
            $currentDate = $this->expedient->opened_at ? $this->expedient->opened_at->startOfDay() : null;
            if ($parsedDate?->toDateString() !== $currentDate?->toDateString()) {
                $this->expedient->opened_at = $parsedDate;
                $dirty = true;
            }

            if ($dirty) {
                $this->expedient->save();
            }

            $this->success('Expediente y metadatos físicos actualizados correctamente.', position: 'toast-top toast-end');

            return redirect()->route('expedients.show', $this->expedient);

        } catch (\Exception $e) {
            $this->error('Ocurrió un error al actualizar: '.$e->getMessage());
        }
    }

    public function render()
    {
        $cabinets = ArchiveLocation::where('is_active', true)
            ->whereNotNull('cabinet')
            ->select('cabinet')
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
                        $label .= " — [ Rango: {$item->alpha_range} ]";
                    }

                    return [
                        'id' => $item->id,
                        'name' => $label,
                    ];
                });
        }

        $newLocation = $this->location_id ? ArchiveLocation::find($this->location_id) : null;

        return view('livewire.expedients.edit', [
            'cabinets' => $cabinets,
            'drawers' => $drawers,
            'newLocation' => $newLocation,
        ]);
    }
}
