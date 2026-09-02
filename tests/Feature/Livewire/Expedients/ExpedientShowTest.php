<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Enums\ExpedientStatus;
use App\Enums\MovementType;
use App\Livewire\Expedients\Show;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use App\Models\LoanRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpedientShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Expedient $expedient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $branch = Branch::create([
            'name' => 'RH DELEGACION ESTATAL',
            'code' => 'MEX',
            'is_active' => true,
        ]);

        $location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'ARCHIVO ACTIVO',
            'cabinet' => 'G-01',
            'drawer' => '1',
            'alpha_range' => 'A - C',
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create(['branch_id' => $branch->id]);

        $this->expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'current_location_id' => $location->id,
            'current_status' => 'available',
        ]);

        ExpedientMovement::create([
            'expedient_id' => $this->expedient->id,
            'user_id' => $this->admin->id,
            'movement_type' => MovementType::Created,
            'notes' => 'Expediente creado en sistema.',
        ]);
    }

    public function test_it_renders_expedient_show_without_lazy_loading_violation(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expedients.show', $this->expedient))
            ->assertOk()
            ->assertSee($this->expedient->expedient_code)
            ->assertSee($this->admin->name);
    }

    public function test_it_handles_livewire_action_updates_without_lazy_loading_violation(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Show::class, ['expedient' => $this->expedient])
            ->assertSee('confirmMarkAsLost')
            ->call('markAsLost')
            ->assertSet('showLostModal', true)
            ->set('notes', 'Carpeta no encontrada en gaveta')
            ->call('confirmMarkAsLost')
            ->assertSet('showLostModal', false)
            ->assertSet('notes', '')
            ->assertHasNoErrors();

        $this->expedient->refresh();
        $this->assertEquals(ExpedientStatus::Lost, $this->expedient->current_status);

        $this->assertDatabaseHas('expedient_movements', [
            'expedient_id' => $this->expedient->id,
            'movement_type' => MovementType::Lost,
            'notes' => 'Carpeta no encontrada en gaveta',
        ]);
    }

    public function test_it_renders_operational_fast_lookup_cockpit_answering_key_questions(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expedients.show', $this->expedient))
            ->assertOk()
            ->assertSee('Tomo '.$this->expedient->volume_number)
            ->assertSee('¿De quién es?')
            ->assertSee('¿Dónde está?')
            ->assertSee('Custodia / Préstamo')
            ->assertSee('Última Trazabilidad')
            ->assertSee('G-01')
            ->assertSee('A - C')
            ->assertSee($this->expedient->employee->rfc);
    }

    public function test_it_renders_expedient_with_loans_history(): void
    {
        LoanRequest::create([
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->admin->id,
            'status' => 'delivered',
            'requested_at' => now()->subDays(2),
            'delivered_at' => now()->subDay(),
            'due_date' => now()->addDays(5),
            'observations' => 'Para revisión de trámite de jubilación',
        ]);

        $this->actingAs($this->admin)
            ->get(route('expedients.show', $this->expedient))
            ->assertOk()
            ->assertSee('Historial de Préstamos')
            ->assertSee('Para revisión de trámite de jubilación');
    }
}
