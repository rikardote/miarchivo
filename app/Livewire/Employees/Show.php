<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;

class Show extends Component
{
    public Employee $employee;

    public function mount(Employee $employee)
    {
        abort_unless(auth()->user()->can('employees.view'), 403);
        $this->employee = $employee->load(['department', 'branch', 'expedients.currentLocation']);
    }

    public function render()
    {
        return view('livewire.employees.show');
    }
}
