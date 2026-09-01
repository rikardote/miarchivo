<?php

namespace Tests\Feature\Livewire\Loans;

use App\Enums\ExpedientStatus;
use App\Livewire\Loans\Request;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequestLoanTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $requester;
    protected Expedient $expedient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->requester = User::factory()->create();
        $this->requester->assignRole('user');

        $branch = Branch::create(['name' => 'Mexicali', 'code' => 'MEX']);
        $location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero 1',
            'cabinet' => 'A',
            'drawer' => 1,
            'alpha_range' => 'A-Z',
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create(['rfc' => 'LOPE850101']);
        $this->expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'LOPE850101-V1',
            'current_location_id' => $location->id,
            'current_status' => ExpedientStatus::Available,
        ]);
    }

    public function test_user_can_access_and_request_loan()
    {
        Livewire::actingAs($this->requester)
            ->test(Request::class, ['expedient' => $this->expedient->id])
            ->assertStatus(200)
            ->set('observations', 'Préstamo para trámite')
            ->call('save')
            ->assertRedirect(route('loans.index', ['mine' => 1]));

        $this->assertDatabaseHas('loan_requests', [
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->requester->id,
            'observations' => 'Préstamo para trámite',
        ]);
    }

    public function test_operator_cannot_access_request_loan()
    {
        Livewire::actingAs($this->operator)
            ->test(Request::class, ['expedient' => $this->expedient->id])
            ->assertStatus(403);
    }
}
