<?php

namespace Tests\Feature\Livewire\Loans;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Livewire\Loans\Dispatch;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $requester;
    protected ArchiveLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->requester = User::factory()->create();
        $this->requester->assignRole('user');

        $branch = Branch::create(['name' => 'Mexicali', 'code' => 'MEX']);
        $this->location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero Principal',
            'cabinet' => 'A',
            'drawer' => 2,
            'alpha_range' => 'A - Z',
            'is_active' => true,
        ]);
    }

    public function test_operator_can_view_dispatch_screen()
    {
        Livewire::actingAs($this->operator)
            ->test(Dispatch::class)
            ->assertStatus(200);
    }

    public function test_regular_user_cannot_access_dispatch_screen()
    {
        Livewire::actingAs($this->requester)
            ->test(Dispatch::class)
            ->assertStatus(403);
    }

    public function test_operator_can_extract_loan_single()
    {
        $employee = Employee::factory()->create(['rfc' => 'GOMA850215']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'GOMA850215-V1',
            'current_location_id' => $this->location->id,
            'current_status' => ExpedientStatus::Requested,
        ]);

        $loan = LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Approved,
        ]);

        Livewire::actingAs($this->operator)
            ->test(Dispatch::class)
            ->call('extractSingle', $loan->id)
            ->assertHasNoErrors();

        // Must be Reserved (Surtido / En Mesa RH), NOT yet delivered
        $this->assertEquals(LoanStatus::Reserved, $loan->fresh()->status);
        $this->assertEquals(ExpedientStatus::Reserved, $expedient->fresh()->current_status);
        $this->assertNull($expedient->fresh()->current_holder_id);
    }

    public function test_operator_can_extract_by_scanning_code()
    {
        $employee = Employee::factory()->create(['rfc' => 'LOPE900101']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'LOPE900101-V1',
            'current_location_id' => $this->location->id,
            'current_status' => ExpedientStatus::Requested,
        ]);

        $loan = LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Approved,
        ]);

        Livewire::actingAs($this->operator)
            ->test(Dispatch::class)
            ->set('scannedCode', 'LOPE900101-V1')
            ->call('processScan')
            ->assertHasNoErrors();

        // Must be Reserved (Surtido / En Mesa RH)
        $this->assertEquals(LoanStatus::Reserved, $loan->fresh()->status);
        $this->assertEquals(ExpedientStatus::Reserved, $expedient->fresh()->current_status);
    }

    public function test_operator_can_return_loan_by_scanning_code()
    {
        $employee = Employee::factory()->create(['rfc' => 'MARA800505']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'MARA800505-V1',
            'current_location_id' => $this->location->id,
            'current_status' => ExpedientStatus::Loaned,
            'current_holder_id' => $this->requester->id,
        ]);

        $loan = LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
        ]);

        Livewire::actingAs($this->operator)
            ->test(Dispatch::class)
            ->set('tab', 'to_return')
            ->set('scannedCode', 'MARA800505-V1')
            ->call('processScan')
            ->assertHasNoErrors();

        $this->assertEquals(LoanStatus::Returned, $loan->fresh()->status);
        $this->assertEquals(ExpedientStatus::Available, $expedient->fresh()->current_status);
        $this->assertNull($expedient->fresh()->current_holder_id);
    }
}
