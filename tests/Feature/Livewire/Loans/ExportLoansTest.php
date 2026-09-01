<?php

namespace Tests\Feature\Livewire\Loans;

use App\Enums\LoanStatus;
use App\Livewire\Loans\Index;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
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
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

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
}
