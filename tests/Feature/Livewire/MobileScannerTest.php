<?php

namespace Tests\Feature\Livewire;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Livewire\GlobalScannerModal;
use App\Livewire\Mobile\Scanner;
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

class MobileScannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $encargado;

    protected User $requester;

    protected ArchiveLocation $location;

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

        $branch = Branch::create(['name' => 'Tijuana', 'code' => 'TIJ']);
        $this->location = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero Móvil',
            'cabinet' => 'M',
            'drawer' => 2,
            'alpha_range' => 'A - Z',
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create([
            'first_name' => 'Laura',
            'last_name' => 'Mendoza',
            'rfc' => 'MENL850101XYZ',
            'employee_number' => 'EMP-9901',
            'branch_id' => $branch->id,
        ]);

        $this->expedient = Expedient::create([
            'expedient_code' => 'EXP-2024-9901',
            'employee_id' => $employee->id,
            'current_location_id' => $this->location->id,
            'current_status' => ExpedientStatus::Available,
        ]);
    }

    public function test_scanner_route_requires_auth(): void
    {
        $this->get('/scanner')->assertRedirect(route('login'));
    }

    public function test_scanner_route_accessible_for_authenticated_users(): void
    {
        $this->actingAs($this->operator)
            ->get('/scanner')
            ->assertOk()
            ->assertSee('Escáner Móvil');
    }

    public function test_processes_valid_expedient_code(): void
    {
        $this->actingAs($this->encargado);

        Livewire::test(Scanner::class)
            ->call('processCode', 'EXP-2024-9901')
            ->assertDispatched('scan-success')
            ->assertSet('expedientId', $this->expedient->id)
            ->assertSet('statusType', 'success')
            ->assertSee('EXP-2024-9901')
            ->assertSee('MENDOZA');
    }

    public function test_processes_unknown_code_with_error_event(): void
    {
        $this->actingAs($this->encargado);

        Livewire::test(Scanner::class)
            ->call('processCode', 'NO-EXISTE-999')
            ->assertDispatched('scan-error')
            ->assertSet('statusType', 'error')
            ->assertSet('currentExpedient', null)
            ->assertSee('Código no encontrado');
    }

    public function test_auto_return_mode_automatically_closes_loan_and_records_count(): void
    {
        // Poner expediente en préstamo
        $this->expedient->update([
            'current_status' => ExpedientStatus::Loaned,
            'current_holder_id' => $this->requester->id,
        ]);
        LoanRequest::create([
            'expedient_id' => $this->expedient->id,
            'requester_id' => $this->requester->id,
            'status' => LoanStatus::Delivered,
            'requested_at' => now()->subDays(2),
            'delivered_at' => now()->subDay(),
            'due_date' => now()->addDays(5),
        ]);

        $this->actingAs($this->encargado);

        Livewire::test(Scanner::class)
            ->set('scannerMode', 'auto-return')
            ->call('processCode', 'EXP-2024-9901')
            ->assertDispatched('scan-success')
            ->assertSet('autoReturnsCount', 1)
            ->assertSet('statusType', 'success');

        $this->assertEquals(ExpedientStatus::Returned, $this->expedient->fresh()->current_status);
    }

    public function test_operator_can_store_in_drawer_directly(): void
    {
        $this->expedient->update(['current_status' => ExpedientStatus::Returned]);
        $this->actingAs($this->operator);

        Livewire::test(Scanner::class)
            ->call('processCode', 'EXP-2024-9901')
            ->call('storeInDrawer')
            ->assertDispatched('scan-success');

        $this->assertEquals(ExpedientStatus::Available, $this->expedient->fresh()->current_status);
    }

    public function test_toggling_scanner_modes(): void
    {
        $this->actingAs($this->encargado);

        Livewire::test(Scanner::class)
            ->assertSet('scannerMode', 'interactive')
            ->call('setScannerMode', 'auto-return')
            ->assertSet('scannerMode', 'auto-return')
            ->call('setScannerMode', 'inquiry')
            ->assertSet('scannerMode', 'inquiry');
    }

    public function test_mobile_scanner_acts_as_wireless_barcode_gun_for_desktop(): void
    {
        $this->actingAs($this->encargado);

        // 1. Escanear con el teléfono móvil en /scanner
        Livewire::test(Scanner::class)
            ->call('processCode', 'EXP-2024-9901')
            ->assertDispatched('scan-success');

        // 2. La computadora de escritorio que tiene GlobalScannerModal escucha y recibe la lectura automáticamente
        Livewire::test(GlobalScannerModal::class)
            ->assertSet('isOpen', false)
            ->call('checkRemoteGunScans')
            ->assertSet('isOpen', true)
            ->assertSet('scannedCode', 'EXP-2024-9901')
            ->assertSet('expedientId', $this->expedient->id)
            ->assertDispatched('desktop-remote-gun-beep');
    }

    public function test_operator_in_autonomous_mode_does_not_affect_desktop(): void
    {
        // Operador en el almacén con su celular
        $this->actingAs($this->operator);

        Livewire::test(Scanner::class)
            ->assertSet('transmitToDesktop', false) // Por defecto es autónomo
            ->call('processCode', 'EXP-2024-9901');

        // Encargado en la PC de mostrador
        $this->actingAs($this->encargado);

        Livewire::test(GlobalScannerModal::class)
            ->assertSet('isOpen', false)
            ->call('checkRemoteGunScans')
            ->assertSet('isOpen', false); // Cero interferencias: la PC no se abre
    }
}
