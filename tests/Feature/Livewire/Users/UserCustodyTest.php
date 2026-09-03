<?php

namespace Tests\Feature\Livewire\Users;

use App\Enums\LoanStatus;
use App\Livewire\Users\Index;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserCustodyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $custodian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('superuser');

        $this->custodian = User::factory()->create(['name' => 'Licenciado Ramirez']);
        $this->custodian->assignRole('user');
    }

    public function test_it_displays_custody_folder_count_in_users_table(): void
    {
        $employee = Employee::factory()->create(['rfc' => 'RAML800101']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'RAML800101-V1',
            'current_holder_id' => $this->custodian->id,
        ]);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->custodian->id,
            'status' => LoanStatus::Delivered,
            'delivered_at' => now()->subDay(),
            'due_date' => now()->addDays(3),
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSee('1 carpeta')
            ->call('showCustody', $this->custodian->id)
            ->assertSet('custodyModal', true)
            ->assertSee('RAML800101-V1');
    }
}
