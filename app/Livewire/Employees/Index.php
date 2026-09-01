<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Services\EmployeeApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedOnlyWithExpedient()
    {
        $this->resetPage();
    }

    public function syncFromApi()
    {
        $this->isSyncing = true;
        try {
            Artisan::call('employees:sync', ['--max-pages' => 5]);
            $this->success('Sincronización de empleados completada exitosamente.');
        } catch (\Exception $e) {
            $this->error('Error al sincronizar: ' . $e->getMessage());
        } finally {
            $this->isSyncing = false;
        }
    }

    public function searchApi(EmployeeApiService $apiService)
    {
        $term = trim($this->search);
        if (empty($term)) {
            $this->warning('Ingresa un RFC, número de empleado o nombre para buscar en el API.');
            return;
        }

        $results = $apiService->search($term);
        $count = 0;
        foreach ($results as $item) {
            if ($apiService->syncEmployee($item)) {
                $count++;
            }
        }

        if ($count > 0) {
            $this->success("Se encontraron y sincronizaron {$count} empleados desde el API.");
        } else {
            $this->warning("No se encontraron resultados en el API para '{$term}'.");
        }
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
