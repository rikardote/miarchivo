<?php

namespace Tests\Feature\Api;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\LoanRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OperatorApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $regularUser;

    protected ArchiveLocation $location1;

    protected ArchiveLocation $location2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->operator = User::factory()->create([
            'email' => 'operador@empresa.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->operator->assignRole('operator');

        $this->regularUser = User::factory()->create([
            'email' => 'usuario@empresa.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->regularUser->assignRole('user');

        $branch = Branch::create(['name' => 'Mexicali', 'code' => 'MEX']);

        $this->location1 = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero A',
            'cabinet' => '1',
            'drawer' => 1,
            'alpha_range' => 'A - M',
            'is_active' => true,
        ]);

        $this->location2 = ArchiveLocation::create([
            'branch_id' => $branch->id,
            'location_type' => 'archivero',
            'archive_name' => 'Archivero B',
            'cabinet' => '2',
            'drawer' => 2,
            'alpha_range' => 'N - Z',
            'is_active' => true,
        ]);
    }

    public function test_operator_can_login_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'operador@empresa.com',
            'password' => 'secret123',
            'device_name' => 'Zebra TC26 Android',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                ],
            ]);
    }

    public function test_user_without_operator_permission_cannot_login_to_operator_api(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'usuario@empresa.com',
            'password' => 'secret123',
            'device_name' => 'Android Phone',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Acceso no autorizado: Su usuario no cuenta con perfil de operador de archivo.',
            ]);
    }

    public function test_authenticated_operator_can_view_me_profile(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'operador@empresa.com');
    }

    public function test_operator_can_view_dashboard_stats(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/v1/operator/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'to_extract',
                    'pending_approval',
                    'to_rearchive',
                    'active_loans',
                    'overdue_loans',
                ],
                'recent_activity',
            ]);
    }

    public function test_operator_can_list_expedients_to_extract(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP001',
            'rfc' => 'GOMA850101AA1',
            'first_name' => 'JUAN',
            'last_name' => 'PEREZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'GOMA850101AA1-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Reserved,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        $loan = LoanRequest::create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->regularUser->id,
            'status' => LoanStatus::Approved,
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/operator/dispatch/to-extract');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $loan->id]);
    }

    public function test_operator_can_extract_expedient(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP002',
            'rfc' => 'RODJ880202BB2',
            'first_name' => 'MARIA',
            'last_name' => 'RODRIGUEZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'RODJ880202BB2-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Reserved,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        $loan = LoanRequest::create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->regularUser->id,
            'status' => LoanStatus::Approved,
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/operator/dispatch/extract', [
            'loan_id' => $loan->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('loan.status', LoanStatus::Reserved->value);
    }

    public function test_operator_cannot_extract_pending_loan_without_rh_approval(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP003',
            'rfc' => 'LOPE900303CC3',
            'first_name' => 'CARLOS',
            'last_name' => 'LOPEZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'LOPE900303CC3-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Requested,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        $loan = LoanRequest::create([
            'expedient_id' => $expedient->id,
            'requester_id' => $this->regularUser->id,
            'status' => LoanStatus::Pending,
            'requested_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/operator/dispatch/extract', [
            'loan_id' => $loan->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "El expediente {$expedient->expedient_code} aún requiere aprobación por el encargado de RH antes de poder extraerse.",
            ]);
    }

    public function test_operator_can_rearchive_returned_expedient(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP004',
            'rfc' => 'HERM920404DD4',
            'first_name' => 'ANA',
            'last_name' => 'HERNANDEZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'HERM920404DD4-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Returned,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/operator/dispatch/rearchive', [
            'code' => $expedient->expedient_code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('expedient.current_status', ExpedientStatus::Available->value);
    }

    public function test_operator_can_perform_audit_scan_and_get_feedback(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP005',
            'rfc' => 'MART940505EE5',
            'first_name' => 'LUCIA',
            'last_name' => 'MARTINEZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'MART940505EE5-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Available,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        // Scan in correct location
        $response = $this->postJson('/api/v1/operator/audit/scan', [
            'location_id' => $this->location1->id,
            'code' => $expedient->expedient_code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'correct');

        // Scan in wrong location
        $responseWrong = $this->postJson('/api/v1/operator/audit/scan', [
            'location_id' => $this->location2->id,
            'code' => $expedient->expedient_code,
        ]);

        $responseWrong->assertStatus(200)
            ->assertJsonPath('status', 'misplaced');
    }

    public function test_operator_can_fix_misplaced_expedient(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP006',
            'rfc' => 'CAST960606FF6',
            'first_name' => 'PEDRO',
            'last_name' => 'CASTILLO',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'CAST960606FF6-V1',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Available,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/operator/audit/fix-misplaced', [
            'location_id' => $this->location2->id,
            'expedient_id' => $expedient->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($this->location2->id, $expedient->fresh()->current_location_id);
    }

    public function test_operator_can_lookup_and_relocate_expedient(): void
    {
        Sanctum::actingAs($this->operator);

        $employee = Employee::create([
            'employee_number' => 'EMP007',
            'rfc' => 'SANC980707GG7',
            'first_name' => 'SOFIA',
            'last_name' => 'SANCHEZ',
            'employment_status' => 'active',
        ]);

        $expedient = Expedient::create([
            'employee_id' => $employee->id,
            'expedient_code' => 'SANC980707GG7-V1',
            'barcode' => 'BC-SANC980707GG7',
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Available,
            'current_location_id' => $this->location1->id,
            'is_active' => true,
        ]);

        // Lookup by barcode
        $lookupResponse = $this->getJson('/api/v1/operator/expedients/lookup/BC-SANC980707GG7');
        $lookupResponse->assertStatus(200)
            ->assertJsonPath('data.expedient_code', 'SANC980707GG7-V1');

        // Relocate
        $relocateResponse = $this->postJson("/api/v1/operator/expedients/{$expedient->id}/relocate", [
            'location_id' => $this->location2->id,
            'notes' => 'Traslado a módulo B',
        ]);

        $relocateResponse->assertStatus(200);
        $this->assertEquals($this->location2->id, $expedient->fresh()->current_location_id);
    }

    public function test_operator_can_logout(): void
    {
        $token = $this->operator->createToken('Android Device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Sesión cerrada exitosamente.']);

        $this->assertCount(0, $this->operator->fresh()->tokens);
    }
}
