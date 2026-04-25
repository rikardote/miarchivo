<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $onlyWithExpedient = true;
    public array $sortBy = ['column' => 'first_name', 'direction' => 'asc'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedOnlyWithExpedient()
    {
        $this->resetPage();
    }

    public function render()
    {
        $employees = Employee::query()
            ->with(['department', 'branch', 'expedients'])
            ->when($this->search, fn (Builder $q) => $q->search($this->search))
            ->when($this->onlyWithExpedient, fn (Builder $q) => $q->whereHas('expedients'))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        return view('livewire.employees.index', [
            'employees' => $employees,
        ]);
    }
}
