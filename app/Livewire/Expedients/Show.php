<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Expedient $expedient;
    public string $notes = '';
    public bool $showLostModal = false;

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient->load(['employee', 'currentLocation', 'currentHolder', 'movements.user', 'loanRequests.user']);
    }

    public function markAsLost()
    {
        $this->authorize('update', $this->expedient);
        $this->notes = '';
        $this->showLostModal = true;
    }

    public function confirmMarkAsLost(\App\Services\ExpedientService $service)
    {
        $this->authorize('update', $this->expedient);
        $service->reportLost($this->expedient, $this->notes ?: null);
        $this->expedient->refresh();
        $this->showLostModal = false;
        $this->success("Expediente marcado como extraviado.");
    }

    public function markAsFound(\App\Services\ExpedientService $service)
    {
        $this->authorize('update', $this->expedient);
        $service->reportFound($this->expedient);
        $this->expedient->refresh();
        $this->success("Expediente marcado como disponible.");
    }

    public function render()
    {
        return view('livewire.expedients.show');
    }
}
