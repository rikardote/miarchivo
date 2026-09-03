<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Livewire\Expedients\ContinuousCreate;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable as TestableLivewire;
use Livewire\Livewire;
use Tests\TestCase;

class ContinuousCreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Branch $branch;

    protected ArchiveLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->branch = Branch::create([
            'name' => 'RH DELEGACION ESTATAL',
            'code' => 'MEX',
            'is_active' => true,
        ]);

        $this->location = ArchiveLocation::create([
            'branch_id' => $this->branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'ARCHIVO ACTIVO',
            'cabinet' => 'G-01',
            'drawer' => '1',
            'alpha_range' => 'D - G',
            'is_active' => true,
        ]);
    }

    protected function createEmployee(string $firstName, string $lastName, string $rfc): Employee
    {
        return Employee::factory()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'rfc' => $rfc,
            'employee_number' => null,
            'employment_status' => 'active',
        ]);
    }

    protected function openSession(ArchiveLocation $location): TestableLivewire
    {
        return Livewire::actingAs($this->admin)
            ->test(ContinuousCreate::class)
            ->set('selectedCabinet', $location->cabinet)
            ->set('location_id', $location->id);
    }

    public function test_route_is_forbidden_for_users_without_create_permission()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expedients.continuous-create'))
            ->assertForbidden();
    }

    public function test_route_renders_for_admin()
    {
        $this->actingAs($this->admin)
            ->get(route('expedients.continuous-create'))
            ->assertOk()
            ->assertSee('Alta Continua de Expedientes');
    }

    public function test_cascading_selects_require_cabinet_before_drawer_and_start_session()
    {
        $second = ArchiveLocation::create([
            'branch_id' => $this->branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'ARCHIVO ACTIVO',
            'cabinet' => 'G-02',
            'drawer' => '1',
            'alpha_range' => 'A - C',
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($this->admin)->test(ContinuousCreate::class);

        // Sin gaveta no hay cajones disponibles ni sesión activa.
        $component->assertSet('location_id', null)
            ->assertDontSee('Sesión activa');

        // Al elegir gaveta aparecen sus cajones con rango, pero aún no hay sesión.
        $component->set('selectedCabinet', 'G-02')
            ->assertSee('G-02', escape: false)
            ->assertSee('Cajón 1')
            ->assertSee('Rango: A - C')
            ->assertSet('location_id', null)
            ->assertDontSee('Sesión activa');

        // Elegir un cajón inicia la sesión.
        $component->set('location_id', $second->id)
            ->assertSet('location_id', $second->id)
            ->assertSee('Sesión activa');

        // Cambiar de gaveta limpia la sesión y obliga a re-elegir cajón.
        $component->set('selectedCabinet', 'G-01')
            ->assertSet('location_id', null)
            ->assertDontSee('Sesión activa');
    }

    public function test_queue_only_lists_pending_employees_matching_drawer_range()
    {
        $pendingInRange = $this->createEmployee('Alejandro', 'Diaz Lopez', 'DILA850215');
        $this->createEmployee('Alejandro', 'Gomez Martinez', 'GOMA850215');
        $outsideRange = $this->createEmployee('Rosa', 'Hernandez Lopez', 'HELR850215');
        $withExpediente = $this->createEmployee('Carlos', 'Dominguez Perez', 'DOPC850215');

        Expedient::factory()->create([
            'employee_id' => $withExpediente->id,
            'current_location_id' => $this->location->id,
        ]);

        $component = $this->openSession($this->location);

        // El visor muestra al primero pendiente en rango (DIAZ por orden alfabético).
        $component->assertSee('DIAZ LOPEZ ALEJANDRO')
            ->assertDontSee('GOMEZ MARTINEZ ALEJANDRO')
            ->assertDontSee('HERNANDEZ LOPEZ ROSA')
            ->assertDontSee('DOMINGUEZ PEREZ CARLOS');
    }

    public function test_it_creates_the_expediente_and_readies_the_label_for_printing()
    {
        $employee = $this->createEmployee('Alejandro', 'Gomez Martinez', 'GOMA850215');

        $component = $this->openSession($this->location);

        $component->assertSee('GOMEZ MARTINEZ ALEJANDRO')
            ->call('createAndPrint')
            ->assertHasNoErrors()
            ->assertSet('readyToPrint', true);

        $expedient = Expedient::where('employee_id', $employee->id)->first();

        $this->assertNotNull($expedient);
        $this->assertEquals('GOMA850215-V1', $expedient->expedient_code);
        $this->assertEquals(1, $expedient->volume_number);
        $this->assertEquals($this->location->id, $expedient->current_location_id);
        $this->assertEquals($expedient->id, $component->get('lastCreatedExpedientId'));
    }

    public function test_confirm_next_moves_to_the_following_pending_employee()
    {
        $this->createEmployee('Alejandro', 'Diaz Lopez', 'DILA850215');
        $second = $this->createEmployee('Alejandro', 'Gomez Martinez', 'GOMA850215');

        $component = $this->openSession($this->location);

        $component->call('createAndPrint')
            ->assertSet('readyToPrint', true)
            ->call('confirmNext')
            ->assertSet('readyToPrint', false)
            ->assertSet('lastCreatedExpedientId', null)
            ->assertSee('GOMEZ MARTINEZ ALEJANDRO');

        // El siguiente empleado aún NO se ha creado: queda listo para el alta.
        $this->assertDatabaseMissing('expedients', ['employee_id' => $second->id]);
    }

    public function test_skip_does_not_create_an_expediente_and_restore_reincorporates_it()
    {
        $first = $this->createEmployee('Alejandro', 'Diaz Lopez', 'DILA850215');
        $this->createEmployee('Alejandro', 'Gomez Martinez', 'GOMA850215');

        $component = $this->openSession($this->location);

        $component->assertSee('DIAZ LOPEZ ALEJANDRO')
            ->call('skipCurrent')
            ->assertSet('readyToPrint', false)
            ->assertSee('GOMEZ MARTINEZ ALEJANDRO');

        $this->assertDatabaseMissing('expedients', ['employee_id' => $first->id]);
        $this->assertTrue(in_array($first->id, $component->get('skippedIds')));

        $component->call('restoreSkipped', $first->id)
            ->assertSet('currentEmployeeId', $first->id)
            ->assertSee('DIAZ LOPEZ ALEJANDRO')
            ->call('createAndPrint')
            ->assertSet('readyToPrint', true);

        $this->assertDatabaseHas('expedients', ['employee_id' => $first->id]);
        $this->assertFalse(in_array($first->id, $component->get('skippedIds')));
    }

    public function test_it_does_not_create_a_second_volume_when_employee_already_has_an_expediente()
    {
        $employee = $this->createEmployee('Alejandro', 'Gomez Martinez', 'GOMA850215');

        Expedient::factory()->create([
            'employee_id' => $employee->id,
            'current_location_id' => $this->location->id,
        ]);

        $component = $this->openSession($this->location);

        // Forzamos al visor hacia un empleado que ya tiene expediente.
        $component->set('currentEmployeeId', $employee->id)
            ->call('createAndPrint')
            ->assertSet('readyToPrint', false);

        $this->assertEquals(1, Expedient::where('employee_id', $employee->id)->count());
    }
}
