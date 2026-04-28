<?php

namespace Tests\Unit\Models;

use App\Enums\ExpedientStatus;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Enums\LoanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpedientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_filter_available_expedients()
    {
        Expedient::factory()->count(3)->create(['current_status' => ExpedientStatus::Available]);
        Expedient::factory()->count(2)->create(['current_status' => ExpedientStatus::Loaned]);

        $this->assertEquals(3, Expedient::available()->count());
    }

    public function test_it_can_filter_overdue_expedients()
    {
        $expedient = Expedient::factory()->create(['current_status' => ExpedientStatus::Loaned]);
        
        // Create an overdue loan
        LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'status' => 'delivered',
            'due_date' => now()->subDay()
        ]);

        $this->assertEquals(1, Expedient::overdue()->count());
    }

    public function test_it_can_search_by_code_and_employee()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'rfc' => 'JD001'
        ]);
        
        Expedient::factory()->create([
            'expedient_code' => 'EXP-999',
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(1, Expedient::search('EXP-999')->count());
        $this->assertEquals(1, Expedient::search('John')->count());
        $this->assertEquals(1, Expedient::search('JD001')->count());
    }

    public function test_it_knows_if_it_is_available()
    {
        $available = Expedient::factory()->make(['current_status' => ExpedientStatus::Available]);
        $loaned = Expedient::factory()->make(['current_status' => ExpedientStatus::Loaned]);

        $this->assertTrue($available->isAvailable());
        $this->assertFalse($loaned->isAvailable());
    }

    public function test_it_can_get_its_active_loan()
    {
        $expedient = Expedient::factory()->create();
        $loan = LoanRequest::factory()->create([
            'expedient_id' => $expedient->id,
            'status' => 'pending'
        ]);

        $activeLoan = $expedient->activeLoan();
        
        $this->assertNotNull($activeLoan);
        $this->assertEquals($loan->id, $activeLoan->id);
    }
}
