<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Rules\ValidRfc;
use App\Services\EmployeeApiService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination;
    use Toast;

    public string $search = '';
    public bool $onlyWithExpedient = true;
    public array $sortBy = ['column' => 'first_name', 'direction' => 'asc'];

    // Modal de Creación Manual
    public bool $createEmployeeModal = false;
    public string $rfc = '';
    public string $first_name = '';
    public string $last_name = '';
    public ?string $employee_number = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedOnlyWithExpedient()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset([
            'rfc',
            'first_name',
            'last_name',
            'employee_number',
        ]);
        $this->createEmployeeModal = true;
    }

    public function saveEmployee()
    {
        $this->validate([
            'rfc' => ['required', 'string', new ValidRfc(), 'unique:employees,rfc'],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number',
        ], [
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'Este RFC ya se encuentra registrado.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'employee_number.unique' => 'Este número de empleado ya está en uso.',
        ]);

        $employee = Employee::create([
            'rfc' => strtoupper(trim($this->rfc)),
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
            'employee_number' => $this->employee_number ? trim($this->employee_number) : null,
            'employment_status' => 'active',
        ]);

        $this->createEmployeeModal = false;
        $this->onlyWithExpedient = false; // Mostrar al nuevo empleado
        $this->success("Empleado {$employee->full_name} registrado exitosamente.");
    }

    public function render()
    {
        $employees = Employee::query()
            ->with(['branch', 'expedients'])
            ->when($this->search, fn (Builder $q) => $q->search($this->search))
            ->when($this->onlyWithExpedient, fn (Builder $q) => $q->whereHas('expedients'))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        return view('livewire.employees.index', [
            'employees' => $employees,
        ]);
    }
}


