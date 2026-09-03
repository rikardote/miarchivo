<?php

namespace Tests\Feature\Livewire\Loans;

use App\Enums\LoanStatus;
use App\Livewire\Loans\Index;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExportLoansTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $requester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->requester = User::factory()->create();
        $this->requester->assignRole('user');
    }

    public function test_it_can_export_loans_to_csv()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Perez Lopez',
            'rfc' => 'PELJ800101',
        ]);

        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'PELJ800101-V1',
        ]);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Pending,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->call('exportActiveLoans');

        $component->assertFileDownloaded();
    }

    public function test_it_can_sort_loans_by_relational_columns()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Gomez',
            'rfc' => 'GOMA850215',
        ]);

        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'GOMA850215-V1',
        ]);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Pending,
        ]);

        // Sort by requester.name
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->set('sortBy', ['column' => 'requester.name', 'direction' => 'asc'])
            ->assertStatus(200);

        // Sort by expedient.expedient_code
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->set('sortBy', ['column' => 'expedient.expedient_code', 'direction' => 'desc'])
            ->assertStatus(200);
    }

    public function test_it_filters_by_overdue_and_delivered_tabs()
    {
        $employee1 = Employee::factory()->create(['rfc' => 'OVER123456']);
        $expedient1 = Expedient::factory()->create(['employee_id' => $employee1->id, 'expedient_code' => 'OVER123456-V1']);

        // Overdue loan: delivered 10 days ago, due 5 days ago
        LoanRequest::factory()->create([
            'expedient_id' => $expedient1->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
            'delivered_at' => now()->subDays(10),
            'due_date' => now()->subDays(5),
        ]);

        $employee2 = Employee::factory()->create(['rfc' => 'ACTIVE12345']);
        $expedient2 = Expedient::factory()->create(['employee_id' => $employee2->id, 'expedient_code' => 'ACTIVE12345-V1']);

        // Active non-overdue loan: due in 5 days
        LoanRequest::factory()->create([
            'expedient_id' => $expedient2->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
            'delivered_at' => now()->subDays(2),
            'due_date' => now()->addDays(5),
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->call('setTab', 'overdue')
            ->assertSee('OVER123456-V1')
            ->assertDontSee('ACTIVE12345-V1')
            ->assertSee('día(s) de atraso');
    }

    public function test_it_filters_by_pending_tab_and_excludes_returned_loans(): void
    {
        $employee1 = Employee::factory()->create(['rfc' => 'PEND111111']);
        $expedient1 = Expedient::factory()->create(['employee_id' => $employee1->id, 'expedient_code' => 'PEND111111-V1']);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient1->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Pending,
        ]);

        $employee2 = Employee::factory()->create(['rfc' => 'RET2222222']);
        $expedient2 = Expedient::factory()->create(['employee_id' => $employee2->id, 'expedient_code' => 'RET2222222-V1']);

        LoanRequest::factory()->create([
            'expedient_id' => $expedient2->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Returned,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->call('setTab', 'pending')
            ->assertSee('PEND111111-V1')
            ->assertDontSee('RET2222222-V1');
    }

    public function test_it_filters_by_specific_custodian()
    {
        $user2 = User::factory()->create(['name' => 'Otro Custodio']);
        $user2->assignRole('user');

        $emp1 = Employee::factory()->create(['rfc' => 'CUST111111']);
        $exp1 = Expedient::factory()->create(['employee_id' => $emp1->id, 'expedient_code' => 'CUST111111-V1']);

        LoanRequest::factory()->create([
            'expedient_id' => $exp1->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
        ]);

        $emp2 = Employee::factory()->create(['rfc' => 'CUST222222']);
        $exp2 = Expedient::factory()->create(['employee_id' => $emp2->id, 'expedient_code' => 'CUST222222-V1']);

        LoanRequest::factory()->create([
            'expedient_id' => $exp2->id,
            'requester_id' => $user2->id,
            'status' => LoanStatus::Delivered,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->set('selectedUserId', $user2->id)
            ->assertSee('CUST222222-V1')
            ->assertDontSee('CUST111111-V1');
    }
}
