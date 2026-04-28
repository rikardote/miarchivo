<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use Livewire\Component;

class PrintLabel extends Component
{
    public Expedient $expedient;

    public function mount(Expedient $expedient)
    {
        $this->expedient = $expedient->load(['employee.branch', 'employee.department']);
    }

    public function render()
    {
        return view('livewire.expedients.print-label')
            ->layout('layouts.print');
    }
}
