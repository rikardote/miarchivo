<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Livewire\Expedients\Create;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateExpedientTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ArchiveLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $branch = Branch::create([
            'name' => 'Oficina Matriz',
            'code' => 'MTZ',
            'is_active' => true,
        ]);

        $this->location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'Archivo Activo',
            'cabinet' => 'A-01',
            'drawer' => '1',
            'is_active' => true,
        ]);
    }

    public function test_employee_coming_from_employees_list_is_preloaded_on_create_screen()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'ALEJANDRO',
            'last_name' => 'GOMEZ MARTINEZ',
            'rfc' => 'GOMA850215',
            'employee_number' => null,
            'employment_status' => 'active',
        ]);

        // El botón "+" en /employees genera la URL con el slug del empleado.
        // El empleado debe llegar preseleccionado en el snapshot del componente
        // (employee_id seteado y búsqueda precargada), sin volver a buscarlo.
        $snapshot = htmlspecialchars('"employee_id":'.$employee->id, ENT_QUOTES);
        $searchField = htmlspecialchars('"searchEmployee":"GOMEZ MARTINEZ, ALEJANDRO"', ENT_QUOTES);

        $this->actingAs($this->admin)
            ->get(route('expedients.create', $employee))
            ->assertOk()
            ->assertSee('GOMEZ MARTINEZ, ALEJANDRO')
            ->assertSee($snapshot, escape: false)
            ->assertSee($searchField, escape: false);
    }

    public function test_it_can_create_an_employee_manually_and_link_it_to_an_expedient()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('manual_rfc', 'goma850215')
            ->set('manual_first_name', 'alejandro')
            ->set('manual_last_name', 'gomez martinez')
            ->set('manual_employee_number', 'emp-99001')
            ->call('saveManualEmployee')
            ->assertHasNoErrors()
            ->assertSet('searchEmployee', 'GOMEZ MARTINEZ, ALEJANDRO')
            ->set('selectedCabinet', 'A-01')
            ->set('location_id', $this->location->id)
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::where('rfc', 'GOMA850215')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('ALEJANDRO', $employee->first_name);
        $this->assertEquals('GOMEZ MARTINEZ', $employee->last_name);
        $this->assertEquals('EMP-99001', $employee->employee_number);
        $this->assertEquals(1, $employee->expedients()->count());
    }
}
