<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Enums\ExpedientStatus;
use App\Livewire\Expedients\Edit;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpedientEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected ArchiveLocation $locationA;

    protected ArchiveLocation $locationB;

    protected Employee $employee;

    protected Expedient $expedient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->regularUser = User::factory()->create();

        $branch = Branch::create([
            'name' => 'Oficina Central',
            'code' => 'OC01',
            'is_active' => true,
        ]);

        $this->locationA = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'Archivo Principal',
            'cabinet' => 'G-01',
            'drawer' => '1',
            'alpha_range' => 'A - G',
            'is_active' => true,
        ]);

        $this->locationB = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'Archivo Principal',
            'cabinet' => 'G-02',
            'drawer' => '3',
            'alpha_range' => 'H - M',
            'is_active' => true,
        ]);

        $this->employee = Employee::factory()->create([
            'first_name' => 'CARLOS',
            'last_name' => 'HERNANDEZ LOPEZ',
            'rfc' => 'HELC800101',
            'employee_number' => 'EMP-1001',
            'employment_status' => 'active',
        ]);

        $this->expedient = Expedient::create([
            'employee_id' => $this->employee->id,
            'expedient_code' => 'HELC800101-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Available,
            'current_location_id' => $this->locationA->id,
            'opened_at' => '2023-01-15',
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_access_edit_screen(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('expedients.edit', $this->expedient))
            ->assertForbidden();
    }

    public function test_authorized_user_can_render_edit_screen_with_cockpit_data(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['expedient' => $this->expedient])
            ->assertOk()
            ->assertSee('HERNANDEZ LOPEZ, CARLOS')
            ->assertSee('HELC800101')
            ->assertSee('G-01')
            ->assertSee('Tomo 1');
    }

    public function test_can_update_expedient_location_and_records_movement(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['expedient' => $this->expedient])
            ->set('selectedCabinet', 'G-02')
            ->set('location_id', $this->locationB->id)
            ->set('movement_notes', 'Traslado de reestructuración')
            ->call('save')
            ->assertRedirect(route('expedients.show', $this->expedient));

        $this->assertDatabaseHas('expedients', [
            'id' => $this->expedient->id,
            'current_location_id' => $this->locationB->id,
        ]);

        $this->assertDatabaseHas('expedient_movements', [
            'expedient_id' => $this->expedient->id,
            'from_location_id' => $this->locationA->id,
            'to_location_id' => $this->locationB->id,
            'notes' => 'Traslado de reestructuración',
        ]);
    }

    public function test_can_update_physical_metadata(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['expedient' => $this->expedient])
            ->set('volume_number', 2)
            ->set('opened_at', '2024-05-20')
            ->call('save')
            ->assertRedirect(route('expedients.show', $this->expedient->fresh()));

        $this->assertDatabaseHas('expedients', [
            'id' => $this->expedient->id,
            'volume_number' => 2,
            'expedient_code' => 'HELC800101-V2',
            'opened_at' => '2024-05-20 00:00:00',
        ]);
    }
}
