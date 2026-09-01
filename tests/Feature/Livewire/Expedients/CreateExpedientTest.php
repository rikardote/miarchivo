<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Livewire\Expedients\Create;
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
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
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

    public function test_it_can_create_an_employee_manually_and_link_it_to_an_expedient()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('manual_rfc', 'GOMA850215ABC')
            ->set('manual_first_name', 'Alejandro')
            ->set('manual_last_name', 'Gomez Martinez')
            ->set('manual_employee_number', '99001')
            ->call('saveManualEmployee')
            ->assertHasNoErrors()
            ->assertSet('searchEmployee', 'Alejandro Gomez Martinez')
            ->set('location_id', $this->location->id)
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::where('rfc', 'GOMA850215ABC')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('Alejandro', $employee->first_name);
        $this->assertEquals(1, $employee->expedients()->count());
    }
}
