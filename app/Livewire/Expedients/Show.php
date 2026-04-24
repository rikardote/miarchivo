<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use Livewire\Component;

class Show extends Component
{
    public Expedient $expedient;

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient->load(['employee', 'currentLocation', 'currentHolder', 'movements.user', 'loanRequests.user']);
    }

    public function render()
    {
        return view('livewire.expedients.show');
    }
}
