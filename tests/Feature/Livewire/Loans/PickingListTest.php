<?php

namespace Tests\Feature\Livewire\Loans;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Livewire\Loans\PickingList;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickingListTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $requester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->requester = User::factory()->create();
        $this->requester->assignRole('user');
    }

    public function test_operator_can_render_picking_list()
    {
        $branch = Branch::create(['name' => 'Tijuana', 'code' => 'TIJ']);
        $location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero A',
            'cabinet' => '1',
            'drawer' => 1,
            'alpha_range' => 'A - M',
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create(['rfc' => 'TEST800101']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'TEST800101-V1',
            'current_location_id' => $location->id,
            'current_status' => ExpedientStatus::Requested,
        ]);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Approved,
        ]);

        $pendingExpedient = Expedient::factory()->create([
            'expedient_code' => 'PENDING99-V1',
            'current_location_id' => $location->id,
        ]);

        LoanRequest::factory()->create([
            'expedient_id' => $pendingExpedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Pending,
        ]);

        Livewire::actingAs($this->operator)
            ->test(PickingList::class)
            ->assertStatus(200)
            ->assertSee('TEST800101-V1')
            ->assertSee('Archivero A')
            ->assertDontSee('PENDING99-V1');
    }
}
