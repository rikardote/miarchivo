<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\ArchiveLocation;
use Livewire\Component;

class Inventory extends Component
{
    public function render()
    {
        $expedients = \App\Models\Expedient::with(['employee', 'currentLocation'])
            ->whereIn('current_status', ['available', 'returned', 'archived', 'in_storage', 'reserved'])
            ->join('employees', 'expedients.employee_id', '=', 'employees.id')
            ->orderBy('employees.last_name')
            ->select('expedients.*')
            ->get();

        return view('livewire.reports.inventory', [
            'expedients' => $expedients
        ])->layout('layouts.app');
    }
}
