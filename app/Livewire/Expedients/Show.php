<?php

namespace App\Livewire\Expedients;

use App\Enums\MovementType;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use App\Services\ExpedientService;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Expedient $expedient;

    public string $notes = '';

    public bool $showLostModal = false;

    public bool $showReprintModal = false;

    public string $reprintReason = 'Etiqueta rota o dañada';

    public string $reprintNotes = '';

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient->load([
            'employee.branch',
            'currentLocation.branch',
            'currentLocation.latestAudit.user',
            'currentHolder',
            'movements.user',
            'loanRequests.requester',
        ]);
    }

    public function openReprintModal()
    {
        $this->authorize('update', $this->expedient);
        $this->reprintReason = 'Etiqueta rota o dañada';
        $this->reprintNotes = '';
        $this->showReprintModal = true;
    }

    public function confirmReprint()
    {
        $this->authorize('update', $this->expedient);

        ExpedientMovement::create([
            'expedient_id' => $this->expedient->id,
            'user_id' => auth()->id(),
            'movement_type' => MovementType::LabelReprinted,
            'from_location_id' => $this->expedient->current_location_id,
            'to_location_id' => $this->expedient->current_location_id,
            'notes' => "Reimpresión de etiqueta térmica. Motivo: {$this->reprintReason}".($this->reprintNotes ? " — {$this->reprintNotes}" : ''),
        ]);

        $this->showReprintModal = false;
        $this->expedient->load('movements.user');
        $this->success('Reimpresión registrada en bitácora.');

        $this->js("window.open('".route('expedients.print', $this->expedient)."', '_blank')");
    }

    public function markAsLost()
    {
        $this->authorize('update', $this->expedient);
        $this->notes = '';
        $this->showLostModal = true;
    }

    public function confirmMarkAsLost(ExpedientService $service)
    {
        $this->authorize('update', $this->expedient);
        $service->reportLost($this->expedient, $this->notes ?: null);
        $this->expedient->refresh();
        $this->showLostModal = false;
        $this->notes = '';
        $this->success('Expediente marcado como extraviado.');
    }

    public function markAsFound(ExpedientService $service)
    {
        $this->authorize('update', $this->expedient);
        $service->reportFound($this->expedient);
        $this->expedient->refresh();
        $this->success('Expediente marcado como disponible.');
    }

    public function render()
    {
        $this->expedient->loadMissing([
            'employee.branch',
            'currentLocation.branch',
            'currentLocation.latestAudit.user',
            'currentHolder',
            'movements.user',
            'loanRequests.requester',
        ]);

        return view('livewire.expedients.show');
    }
}
