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
    use Toast;

    public string $search = '';
    public bool $onlyWithExpedient = false;
    public array $sortBy = ['column' => 'first_name', 'direction' => 'asc'];

    // Resultados cacheados de la última consulta a la API (para acciones bajo demanda)
    public array $apiResults = [];

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

    /**
     * Registra bajo demanda (solo en este momento) al empleado consultado en la API
     * y lo lleva a la pantalla de creación de expediente.
     */
    public function createExpedientFromApi(string $rfc)
    {
        $rfc = mb_strtoupper(mb_substr(trim($rfc), 0, 10), 'UTF-8');

        $employee = Employee::where('rfc', $rfc)->first();

        if (!$employee) {
            $selected = collect($this->apiResults)->firstWhere('rfc', $rfc);

            if (!$selected || empty($selected['raw'])) {
                $this->warning('No fue posible recuperar el empleado de la API. Intenta buscar de nuevo.');
                return;
            }

            $employee = app(EmployeeApiService::class)->syncEmployee($selected['raw']);
        }

        if (!$employee) {
            $this->error('No fue posible registrar al empleado.');
            return;
        }

        return redirect()->route('expedients.create', $employee);
    }

    public function render()
    {
        $searchTerm = trim($this->search);
        $apiSearched = false;
        $employees = collect();

        if (mb_strlen($searchTerm) >= 3) {
            // 1. Empleados ya registrados localmente
            $localEmployees = Employee::query()
                ->with(['branch', 'expedients'])
                ->search($searchTerm)
                ->get()
                ->each(fn ($emp) => $emp->setAttribute('source', 'local'));

            // 2. Consulta bajo demanda a la API de empleados
            $apiRows = collect();
            try {
                $apiService = app(EmployeeApiService::class);
                $apiRows = collect($apiService->search($searchTerm))
                    ->sortByDesc('id')
                    ->map(function ($item) {
                        $rfc10 = !empty($item['id_legal'])
                            ? mb_strtoupper(mb_substr(trim($item['id_legal']), 0, 10), 'UTF-8')
                            : null;

                        return [
                            'rfc' => $rfc10,
                            'employee_number' => $item['id_empleado'] ?? null,
                            'first_name' => trim(($item['nombre'] ?? '') . ' ' . ($item['apellido_1'] ?? '') . ' ' . ($item['apellido_2'] ?? '')),
                            'last_name' => '',
                            'employment_status' => str_contains(mb_strtoupper($item['estado_empleado'] ?? 'ACTIVO'), 'ACTIVO') ? 'active' : 'inactive',
                            'position' => $item['n_puesto_plaza'] ?? null,
                            'work_center' => $item['n_centro_trabajo'] ?? null,
                            'source' => 'api',
                            // Solo los campos mínimos necesarios para registrarlo bajo demanda
                            'raw' => [
                                'id' => $item['id'] ?? null,
                                'id_legal' => $item['id_legal'] ?? null,
                                'id_empleado' => $item['id_empleado'] ?? null,
                                'nombre' => $item['nombre'] ?? null,
                                'apellido_1' => $item['apellido_1'] ?? null,
                                'apellido_2' => $item['apellido_2'] ?? null,
                                'n_puesto_plaza' => $item['n_puesto_plaza'] ?? null,
                                'n_centro_trabajo' => $item['n_centro_trabajo'] ?? null,
                                'poblacion' => $item['poblacion'] ?? null,
                                'estado_empleado' => $item['estado_empleado'] ?? null,
                            ],
                        ];
                    })
                    ->filter(fn ($row) => !empty($row['rfc']))
                    ->unique('rfc')
                    ->values();
            } catch (\Throwable $e) {
                // API temporalmente no disponible: continuar solo con resultados locales
            }

            $this->apiResults = $apiRows->toArray();

            // "Solo con Expediente": los resultados de la API nunca tienen expediente local
            if ($this->onlyWithExpedient) {
                $localEmployees = $localEmployees->filter(fn ($emp) => $emp->expedients->isNotEmpty());
                $apiRows = collect();
            }

            $employees = $localEmployees
                ->concat($apiRows->map(fn ($row) => (object) $row))
                ->unique('rfc')
                ->values();

            $apiSearched = true;
        } else {
            $employees = Employee::query()
                ->with(['branch', 'expedients'])
                ->when($this->onlyWithExpedient, fn (Builder $q) => $q->whereHas('expedients'))
                ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
                ->paginate(15)
                ->through(fn ($emp) => $emp->setAttribute('source', 'local'));
        }

        return view('livewire.employees.index', [
            'employees' => $employees,
            'apiSearched' => $apiSearched,
        ]);
    }
}
