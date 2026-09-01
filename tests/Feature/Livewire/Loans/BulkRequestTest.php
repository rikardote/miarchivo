<?php

namespace Tests\Feature\Livewire\Loans;

use App\Models\Expedient;
use App\Models\User;
use App\Livewire\Loans\BulkRequest;
use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $recipient;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
        $this->admin = User::factory()->create();
        $this->recipient = User::factory()->create();
    }

    public function test_it_can_scan_and_add_expedients_to_the_list()
    {
        $expedient = Expedient::factory()->create([
            'expedient_code' => 'TEST-001',
            'current_status' => ExpedientStatus::Available
        ]);

        Livewire::actingAs($this->admin)
            ->test(BulkRequest::class)
            ->set('scannedCode', 'TEST-001')
            ->call('processScan')
            ->assertSet('scannedCode', '')
            ->assertCount('items', 1)
            ->assertSee('TEST-001');
    }

    public function test_it_marks_unavailable_expedients_as_invalid()
    {
        $expedient = Expedient::factory()->create([
            'expedient_code' => 'BUSY-001',
            'current_status' => ExpedientStatus::Loaned
        ]);

        Livewire::actingAs($this->admin)
            ->test(BulkRequest::class)
            ->set('scannedCode', 'BUSY-001')
            ->call('processScan')
            ->assertCount('items', 1)
            ->assertSet('items.0.isValid', false);
    }

    public function test_it_prevents_duplicate_scans_in_the_same_session()
    {
        $expedient = Expedient::factory()->create(['expedient_code' => 'DUP-001']);

        Livewire::actingAs($this->admin)
            ->test(BulkRequest::class)
            ->set('scannedCode', 'DUP-001')
            ->call('processScan')
            ->set('scannedCode', 'DUP-001')
            ->call('processScan')
            ->assertCount('items', 1); // Should still be 1
    }

    public function test_it_processes_the_bulk_request_for_a_selected_user()
    {
        $exp1 = Expedient::factory()->create(['expedient_code' => 'MASS-001']);
        $exp2 = Expedient::factory()->create(['expedient_code' => 'MASS-002']);

        Livewire::actingAs($this->admin)
            ->test(BulkRequest::class)
            ->set('user_id', $this->recipient->id)
            ->set('scannedCode', 'MASS-001')
            ->call('processScan')
            ->set('scannedCode', 'MASS-002')
            ->call('processScan')
            ->call('save')
            ->assertRedirect(route('loans.index'));

        $this->assertEquals(2, \App\Models\LoanRequest::where('requester_id', $this->recipient->id)->count());
        $this->assertEquals(ExpedientStatus::Requested, $exp1->refresh()->current_status);
        $this->assertEquals(ExpedientStatus::Requested, $exp2->refresh()->current_status);
    }
}
