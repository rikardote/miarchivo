<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Expedient;
use App\Models\Employee;
use App\Services\EmployeeApiService;
use App\Services\ExpedientService;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public ?int $employee_id = null;
    public ?int $location_id = null;
    public string $searchEmployee = '';
    public array $apiResults = [];

    public function mount()
    {
        $this->authorize('create', Expedient::class);
    }

    public function updatedSearchEmployee($value)
    {
        $value = trim($value);
        
        if (strlen($value) < 3) {
            $this->apiResults = [];
            return;
        }

        $apiService = app(EmployeeApiService::class);
        $results = $apiService->search($value);

        // Deduplicate and filter results
        $this->apiResults = collect($results)
            ->sortByDesc('id')
            ->unique('id_legal')
            ->map(function ($item) {
                return [
                    'id' => $item['id_legal'],
                    'name' => trim(($item['nombre'] ?? '') . ' ' . ($item['apellido_1'] ?? '') . ' ' . ($item['apellido_2'] ?? '')),
                    'rfc' => $item['id_legal'] ?? 'S/RFC',
                    'employee_number' => $item['id_empleado'] ?? 'S/N',
                    'raw' => $item
                ];
            })
            ->filter(function($item) use ($value) {
                if (is_numeric($value) || strlen($value) >= 10) {
                    return str_contains($item['rfc'], $value) || str_contains($item['employee_number'], $value);
                }
                return true;
            })
            ->sortByDesc(function($item) use ($value) {
                if ($item['rfc'] === $value || $item['employee_number'] === $value) return 100;
                return 0;
            })
            ->take(8)
            ->values()
            ->toArray();
    }

    public function selectEmployee($rfc)
    {
        $selected = collect($this->apiResults)->firstWhere('id', $rfc);

        if ($selected) {
            $apiService = app(EmployeeApiService::class);
            $employee = $apiService->syncEmployee($selected['raw']);
            
            if ($employee) {
                $this->employee_id = $employee->id;
                $this->searchEmployee = $employee->full_name;
                $this->apiResults = [];
                $this->success("Empleado seleccionado: {$employee->full_name}");
            }
        }
    }

    public function save(ExpedientService $expedientService)
    {
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'required|exists:archive_locations,id',
        ], [
            'employee_id.required' => 'Debes seleccionar un empleado del buscador.',
            'location_id.required' => 'Debes seleccionar una ubicación.',
        ]);

        $employee = Employee::find($this->employee_id);

        try {
            $expedient = $expedientService->createExpedient($employee, [
                'location_id' => $this->location_id,
            ]);

            $this->success('Expediente creado con éxito.');
            return redirect()->route('expedients.show', $expedient);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.expedients.create', [
            'locations' => ArchiveLocation::orderBy('archive_name')->get(),
        ]);
    }
}
