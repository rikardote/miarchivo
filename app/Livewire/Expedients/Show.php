<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use App\Services\ExpedientService;
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
        $this->expedient = $expedient->load(['employee.branch', 'currentLocation.branch', 'currentHolder', 'movements.user', 'loanRequests.requester']);
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
            'currentHolder',
            'movements.user',
            'loanRequests.requester',
        ]);

        return view('livewire.expedients.show');
    }
}
