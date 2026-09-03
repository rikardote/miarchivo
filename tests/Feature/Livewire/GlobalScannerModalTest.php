<?php

namespace Tests\Feature\Livewire;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Livewire\GlobalScannerModal;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalScannerModalTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $encargado;

    protected User $requester;

    protected ArchiveLocation $location;

    protected ArchiveLocation $secondLocation;

    protected Expedient $expedient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->encargado = User::factory()->create();
        $this->encargado->assignRole('admin');

        $this->requester = User::factory()->create();
        $this->requester->assignRole('user');

        $branch = Branch::create(['name' => 'Mexicali', 'code' => 'MEX']);
        $this->location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero Central',
            'cabinet' => 'A',
            'drawer' => 1,
            'alpha_range' => 'A - M',
            'is_active' => true,
        ]);

        $this->secondLocation = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero Central',
            'cabinet' => 'B',
            'drawer' => 2,
            'alpha_range' => 'N - Z',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'rfc' => 'PERJ850101XYZ',
            'branch_id' => $branch->id,
            'employment_status' => 'active',
        ]);

        $this->expedient = Expedient::create([
            'expedient_code' => 'EXP-2024-0099',
            'employee_id' => $employee->id,
            'current_location_id' => $this->location->id,
            'current_status' => ExpedientStatus::Available,
        ]);
    }

    public function test_modal_opens_on_event_and_finds_expedient(): void
    {
        $this->actingAs($this->operator);

        Livewire::test(GlobalScannerModal::class)
            ->assertSet('isOpen', false)
            ->dispatch('open-global-scanner', code: 'EXP-2024-0099')
            ->assertSet('isOpen', true)
            ->assertSet('expedientId', $this->expedient->id)
            ->assertSee('PÉREZ, JUAN')
            ->assertSee('EXP-2024-0099')
            ->assertSee('Archivero Central');
    }

    public function test_modal_shows_error_when_code_not_found(): void
    {
        $this->actingAs($this->operator);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: 'CODIGO-INEXISTENTE')
            ->assertSet('expedientId', null)
            ->assertSee('No se encontró ningún expediente');
    }

    public function test_mobile_camera_scan_callback_searches_expedient(): void
    {
        $this->actingAs($this->operator);

        Livewire::test(GlobalScannerModal::class)
            ->call('openScanner')
            ->assertSet('isOpen', true)
            ->call('searchScannedCode', 'EXP-2024-0099')
            ->assertSet('expedientId', $this->expedient->id)
            ->assertSee('PÉREZ, JUAN')
            ->assertSee('EXP-2024-0099');
    }

    public function test_quick_return_closes_active_loan_and_returns_to_drawer(): void
    {
        $this->actingAs($this->operator);

        // Put expedient in delivered loan
        $loan = LoanRequest::create([
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
            'requested_at' => now()->subDays(2),
            'delivered_at' => now()->subDay(),
            'due_date' => now()->addDays(5),
        ]);

        $this->expedient->update([
            'current_status' => ExpedientStatus::Loaned,
            'current_holder_id' => $this->requester->id,
        ]);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->assertSee('Expediente en Préstamo Activo')
            ->call('receiveReturn')
            ->assertDispatched('loan-updated');

        $this->assertEquals(LoanStatus::Returned, $loan->fresh()->status);
        $this->assertEquals(ExpedientStatus::Returned, $this->expedient->fresh()->current_status);
        $this->assertNull($this->expedient->fresh()->current_holder_id);
    }

    public function test_operator_can_store_in_drawer_directly(): void
    {
        $this->actingAs($this->operator);

        // Put expedient in returned state
        $this->expedient->update([
            'current_status' => ExpedientStatus::Returned,
        ]);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->assertSee('Expediente Devuelto (En Mostrador)')
            ->call('storeInDrawer')
            ->assertDispatched('expedient-updated');

        $this->assertEquals(ExpedientStatus::Available, $this->expedient->fresh()->current_status);
    }

    public function test_quick_deliver_hands_out_approved_expedient(): void
    {
        $this->actingAs($this->operator);

        $loan = LoanRequest::create([
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Approved,
            'requested_at' => now()->subDay(),
            'approved_at' => now(),
        ]);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->assertSee('Solicitud Aprobada Lista para Entrega')
            ->call('quickDeliver')
            ->assertDispatched('loan-updated');

        $this->assertEquals(LoanStatus::Delivered, $loan->fresh()->status);
        $this->assertEquals(ExpedientStatus::Loaned, $this->expedient->fresh()->current_status);
        $this->assertEquals($this->requester->id, $this->expedient->fresh()->current_holder_id);
    }

    public function test_quick_relocate_updates_expedient_location(): void
    {
        $this->actingAs($this->operator);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->set('targetLocationId', $this->secondLocation->id)
            ->call('quickRelocate')
            ->assertDispatched('expedient-updated');

        $this->assertEquals($this->secondLocation->id, $this->expedient->fresh()->current_location_id);
    }

    public function test_modal_shows_encargado_ui_for_admin_user(): void
    {
        $this->actingAs($this->encargado);

        // Put expedient in delivered loan
        LoanRequest::create([
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
            'requested_at' => now()->subDays(2),
            'delivered_at' => now()->subDay(),
            'due_date' => now()->addDays(5),
        ]);

        $this->expedient->update([
            'current_status' => ExpedientStatus::Loaned,
            'current_holder_id' => $this->requester->id,
        ]);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->assertSee('Encargado / Mostrador')
            ->assertSee('Registrar Devolución (Recepción en Mostrador)');
    }

    public function test_modal_shows_operator_ui_for_operator_user(): void
    {
        $this->actingAs($this->operator);

        Livewire::test(GlobalScannerModal::class)
            ->dispatch('open-global-scanner', code: $this->expedient->expedient_code)
            ->assertSee('Operador de Archivo')
            ->assertSee('Ubicación Física Asignada');
    }
}
