<?php

namespace Tests\Unit\Services;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use App\Services\LoanService;
use App\Services\ExpedientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LoanService $loanService;
    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loanService = app(LoanService::class);
        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
        
        Auth::login($this->user);
    }

    public function test_it_can_request_a_loan_for_an_available_expedient()
    {
        $expedient = Expedient::factory()->create([
            'current_status' => ExpedientStatus::Available
        ]);

        $loan = $this->loanService->requestLoan($expedient, 'Test observations');

        $this->assertInstanceOf(LoanRequest::class, $loan);
        $this->assertEquals($expedient->id, $loan->expedient_id);
        $this->assertEquals($this->user->id, $loan->requester_id);
        $this->assertEquals(LoanStatus::Pending, $loan->status);
        
        $expedient->refresh();
        $this->assertEquals(ExpedientStatus::Requested, $expedient->current_status);
    }

    public function test_it_cannot_request_a_loan_for_an_unavailable_expedient()
    {
        $expedient = Expedient::factory()->create([
            'current_status' => ExpedientStatus::Loaned
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El expediente no está disponible para préstamo.');

        $this->loanService->requestLoan($expedient);
    }

    public function test_an_admin_can_approve_a_loan()
    {
        $loan = LoanRequest::factory()->create(['status' => LoanStatus::Pending]);
        
        Auth::login($this->admin);
        $this->loanService->approveLoan($loan);

        $loan->refresh();
        $this->assertEquals(LoanStatus::Approved, $loan->status);
        $this->assertEquals($this->admin->id, $loan->approved_by);
        
        $loan->expedient->refresh();
        $this->assertEquals(ExpedientStatus::Reserved, $loan->expedient->current_status);
    }

    public function test_an_operator_can_extract_a_loan()
    {
        $loan = LoanRequest::factory()->create(['status' => LoanStatus::Pending]);
        
        $this->loanService->extractLoan($loan);

        $loan->refresh();
        $this->assertEquals(LoanStatus::Reserved, $loan->status);
        $this->assertEquals(ExpedientStatus::Reserved, $loan->expedient->current_status);
        $this->assertNull($loan->expedient->current_holder_id);
    }

    public function test_it_can_deliver_an_approved_loan()
    {
        $loan = LoanRequest::factory()->create(['status' => LoanStatus::Approved]);
        
        Auth::login($this->admin);
        $this->loanService->deliverLoan($loan);

        $loan->refresh();
        $this->assertEquals(LoanStatus::Delivered, $loan->status);
        $this->assertNotNull($loan->delivered_at);
        $this->assertNotNull($loan->due_date);

        $loan->expedient->refresh();
        $this->assertEquals(ExpedientStatus::Loaned, $loan->expedient->current_status);
        $this->assertEquals($loan->requester_id, $loan->expedient->current_holder_id);
    }

    public function test_it_can_process_a_return()
    {
        $loan = LoanRequest::factory()->create([
            'status' => LoanStatus::Delivered,
            'delivered_at' => now()->subDays(2)
        ]);
        
        Auth::login($this->admin);
        $this->loanService->returnLoan($loan, 'All good');

        $loan->refresh();
        $this->assertEquals(LoanStatus::Returned, $loan->status);
        $this->assertNotNull($loan->returned_at);
        $this->assertEquals('All good', $loan->return_notes);

        $loan->expedient->refresh();
        $this->assertEquals(ExpedientStatus::Returned, $loan->expedient->current_status);
        $this->assertNull($loan->expedient->current_holder_id);
    }

    public function test_it_can_rearchive_an_expedient()
    {
        $expedient = Expedient::factory()->create([
            'current_status' => ExpedientStatus::Returned,
        ]);

        $this->loanService->rearchiveExpedient($expedient);

        $expedient->refresh();
        $this->assertEquals(ExpedientStatus::Available, $expedient->current_status);
    }
}
