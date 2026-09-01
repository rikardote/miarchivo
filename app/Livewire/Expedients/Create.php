<?php

namespace App\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Department;
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
    public array $searchResults = [];

    // Captura Manual de Empleado
    public bool $showManualModal = false;
    public string $manual_rfc = '';
    public string $manual_first_name = '';
    public string $manual_last_name = '';
    public ?string $manual_employee_number = null;

    public function mount($employee = null)
    {
        $this->authorize('create', Expedient::class);

        if ($employee) {
            $found = Employee::where('id', $employee)
                ->orWhere('employee_number', $employee)
                ->orWhere('rfc', $employee)
                ->first();

            if ($found) {
                $this->employee_id = $found->id;
                $this->searchEmployee = $found->full_name;
            }
        }
    }

    public function updatedSearchEmployee($value)
    {
        $value = trim($value);
        
        if (strlen($value) < 3) {
            $this->searchResults = [];
            return;
        }

        // 1. Buscar en BD Local
        $localEmployees = Employee::search($value)
            ->take(5)
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->rfc,
                    'name' => $emp->full_name,
                    'rfc' => $emp->rfc,
                    'employee_number' => $emp->employee_number ?? 'S/N',
                    'source' => 'local',
                    'local_id' => $emp->id,
                    'raw' => null,
                ];
            });

        // 2. Buscar en API externa
        $apiService = app(EmployeeApiService::class);
        $apiResults = collect();
        try {
            $results = $apiService->search($value);
            $apiResults = collect($results)
                ->sortByDesc('id')
                ->unique('id_legal')
                ->map(function ($item) {
                    return [
                        'id' => $item['id_legal'],
                        'name' => trim(($item['nombre'] ?? '') . ' ' . ($item['apellido_1'] ?? '') . ' ' . ($item['apellido_2'] ?? '')),
                        'rfc' => $item['id_legal'] ?? 'S/RFC',
                        'employee_number' => $item['id_empleado'] ?? 'S/N',
                        'source' => 'api',
                        'local_id' => null,
                        'raw' => $item,
                    ];
                });
        } catch (\Throwable $e) {
            // Silently fallback to local results if API is temporarily unavailable
        }

        // 3. Unir y deduplicar por RFC
        $this->searchResults = $localEmployees
            ->concat($apiResults)
            ->unique('rfc')
            ->take(8)
            ->values()
            ->toArray();
    }

    public function selectEmployee($rfc, $source = 'api', $localId = null)
    {
        if ($source === 'local' && $localId) {
            $employee = Employee::find($localId);
        } else {
            $selected = collect($this->searchResults)->firstWhere('id', $rfc);
            if ($selected && isset($selected['raw'])) {
                $apiService = app(EmployeeApiService::class);
                $employee = $apiService->syncEmployee($selected['raw']);
            } else {
                $employee = Employee::where('rfc', $rfc)->first();
            }
        }

        if ($employee) {
            $this->employee_id = $employee->id;
            $this->searchEmployee = $employee->full_name;
            $this->searchResults = [];
            $this->success("Empleado seleccionado: {$employee->full_name}");
        }
    }

    public function openManualModal()
    {
        $term = trim($this->searchEmployee);
        if (strlen($term) >= 10 && preg_match('/^[A-Za-z0-9]+$/', $term)) {
            $this->manual_rfc = strtoupper($term);
        } elseif (!empty($term) && !is_numeric($term)) {
            $this->manual_first_name = $term;
        }

        $this->showManualModal = true;
    }

    public function saveManualEmployee()
    {
        $this->validate([
            'manual_rfc' => 'required|string|size:10|unique:employees,rfc',
            'manual_first_name' => 'required|string|max:100',
            'manual_last_name' => 'required|string|max:100',
            'manual_employee_number' => 'nullable|string|max:50|unique:employees,employee_number',
        ], [
            'manual_rfc.required' => 'El RFC es obligatorio.',
            'manual_rfc.size' => 'El RFC debe tener exactamente 10 caracteres (ej: GOMA850215).',
            'manual_rfc.unique' => 'Ya existe un empleado con este RFC en el sistema.',
            'manual_first_name.required' => 'El nombre es obligatorio.',
            'manual_last_name.required' => 'Los apellidos son obligatorios.',
            'manual_employee_number.unique' => 'Este número de empleado ya está en uso.',
        ]);

        $employee = Employee::create([
            'rfc' => strtoupper(trim($this->manual_rfc)),
            'first_name' => trim($this->manual_first_name),
            'last_name' => trim($this->manual_last_name),
            'employee_number' => $this->manual_employee_number ? trim($this->manual_employee_number) : null,
            'employment_status' => 'active',
        ]);

        $this->employee_id = $employee->id;
        $this->searchEmployee = $employee->full_name;
        $this->searchResults = [];
        $this->showManualModal = false;

        // Limpiar campos del formulario manual
        $this->reset([
            'manual_rfc',
            'manual_first_name',
            'manual_last_name',
            'manual_employee_number',
        ]);

        $this->success("Empleado {$employee->full_name} registrado y seleccionado exitosamente.");
    }

    public function save(ExpedientService $expedientService)
    {
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'required|exists:archive_locations,id',
        ], [
            'employee_id.required' => 'Debes seleccionar o capturar un empleado.',
            'location_id.required' => 'Debes seleccionar una ubicación física.',
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
            'locations' => ArchiveLocation::where('is_active', true)->orderBy('archive_name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

