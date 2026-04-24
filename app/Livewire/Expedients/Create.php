<?php

namespace App\Livewire\Expedients;

use App\Models\Employee;
use App\Models\ArchiveLocation;
use App\Services\ExpedientService;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public ?int $employee_id = null;
    public ?int $location_id = null;

    public function save(ExpedientService $expedientService)
    {
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'required|exists:archive_locations,id',
        ], [
            'employee_id.required' => 'Debes seleccionar un empleado.',
            'location_id.required' => 'Debes seleccionar una ubicación para el expediente.',
        ]);

        $employee = Employee::find($this->employee_id);

        try {
            $expedient = $expedientService->createExpedient($employee, [
                'location_id' => $this->location_id,
            ]);

            $this->success('Expediente creado con éxito.', position: 'toast-top toast-end');
            return redirect()->route('expedients.show', $expedient);
        } catch (\Exception $e) {
            $this->error('Ocurrió un error al crear el expediente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.expedients.create', [
            'employees' => Employee::orderBy('first_name')->take(50)->get(), // En un caso real usaríamos un search typeahead
            'locations' => ArchiveLocation::orderBy('archive_name')->get(),
        ]);
    }
}
