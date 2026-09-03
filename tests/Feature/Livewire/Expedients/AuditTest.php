<?php

namespace Tests\Feature\Livewire\Expedients;

use App\Livewire\Expedients\Audit;
use App\Livewire\GlobalScannerModal;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LocationAudit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Branch $branch;

    protected ArchiveLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->branch = Branch::create([
            'name' => 'RH DELEGACION ESTATAL',
            'code' => 'MEX',
            'is_active' => true,
        ]);

        $this->location = ArchiveLocation::create([
            'branch_id' => $this->branch->id,
            'location_type' => 'Archivero',
            'archive_name' => 'ARCHIVO ACTIVO',
            'cabinet' => 'G-01',
            'drawer' => '1',
            'alpha_range' => 'A - C',
            'is_active' => true,
        ]);
    }

    public function test_audit_route_is_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('expedients.audit'))
            ->assertOk()
            ->assertSee('Auditoría de Inventario');
    }

    public function test_user_can_scan_and_save_audit_report_persisting_session(): void
    {
        $employee = Employee::factory()->create(['rfc' => 'TEST800101XYZ']);
        $expedient = Expedient::factory()->create([
            'employee_id' => $employee->id,
            'expedient_code' => 'EXP-99901-V1',
            'current_location_id' => $this->location->id,
            'current_status' => 'available',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Audit::class)
            ->set('location_id', $this->location->id)
            ->call('startAudit')
            ->assertSet('is_auditing', true)
            ->call('addScan', $expedient->expedient_code)
            ->set('audit_notes', 'Auditoría preventiva de prueba')
            ->call('saveAuditReport')
            ->assertSet('saved_audit', true);

        $this->assertDatabaseHas('location_audits', [
            'archive_location_id' => $this->location->id,
            'user_id' => $this->admin->id,
            'expected_count' => 1,
            'scanned_count' => 1,
            'correct_count' => 1,
            'missing_count' => 0,
            'notes' => 'Auditoría preventiva de prueba',
        ]);

        $auditRecord = LocationAudit::first();
        $this->assertNotNull($auditRecord);
        $this->assertContains('EXP-99901-V1', $auditRecord->details['correct_codes']);
    }

    public function test_audit_receives_scans_from_mobile_gun(): void
    {
        $expedient = Expedient::factory()->create([
            'expedient_code' => 'EXP-88801-V1',
            'current_location_id' => $this->location->id,
            'current_status' => 'available',
        ]);

        // Iniciar auditoría en la PC
        $auditComponent = Livewire::actingAs($this->admin)
            ->test(Audit::class)
            ->set('location_id', $this->location->id)
            ->call('startAudit')
            ->assertSet('is_auditing', true);

        // El celular escanea y coloca el código en el canal del usuario
        Cache::put("scanner_gun_user_{$this->admin->id}", 'EXP-88801-V1', now()->addSeconds(30));

        // El componente de auditoría lo recibe en vivo mediante checkRemoteGunAuditScans
        $auditComponent->call('checkRemoteGunAuditScans')
            ->assertDispatched('audit-remote-gun-beep')
            ->assertSet('scanned_codes', ['EXP-88801-V1']);

        // El modal global NO debe abrirse porque la auditoría está activa
        Livewire::actingAs($this->admin)
            ->test(GlobalScannerModal::class)
            ->call('checkRemoteGunScans')
            ->assertSet('isOpen', false);
    }
}
