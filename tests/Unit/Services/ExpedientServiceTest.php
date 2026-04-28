<?php

namespace Tests\Unit\Services;

use App\Enums\ExpedientStatus;
use App\Enums\MovementType;
use App\Models\ArchiveLocation;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use App\Models\User;
use App\Services\ExpedientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ExpedientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExpedientService $expedientService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->expedientService = app(ExpedientService::class);
        $this->user = User::factory()->create();
        
        Auth::login($this->user);
    }

    public function test_it_can_create_a_new_expedient()
    {
        $employee = Employee::factory()->create(['rfc' => 'TEST123456ABC']);
        $location = ArchiveLocation::factory()->create();

        $expedient = $this->expedientService->createExpedient($employee, [
            'location_id' => $location->id,
            'opened_at' => now()->subYear()
        ]);

        $this->assertInstanceOf(Expedient::class, $expedient);
        $this->assertEquals('TEST123456ABC-V1', $expedient->expedient_code);
        $this->assertEquals(1, $expedient->volume_number);
        $this->assertEquals($location->id, $expedient->current_location_id);
        
        // Verify movement was recorded
        $this->assertDatabaseHas('expedient_movements', [
            'expedient_id' => $expedient->id,
            'movement_type' => MovementType::Created,
            'to_location_id' => $location->id
        ]);
    }

    public function test_it_increments_volume_number_for_same_employee()
    {
        $employee = Employee::factory()->create(['rfc' => 'TEST123456ABC']);
        
        // Create first volume
        $this->expedientService->createExpedient($employee, []);
        
        // Create second volume
        $expedient2 = $this->expedientService->createExpedient($employee, []);

        $this->assertEquals(2, $expedient2->volume_number);
        $this->assertEquals('TEST123456ABC-V2', $expedient2->expedient_code);
    }

    public function test_it_can_change_expedient_location()
    {
        $location1 = ArchiveLocation::factory()->create();
        $location2 = ArchiveLocation::factory()->create();
        
        $expedient = Expedient::factory()->create([
            'current_location_id' => $location1->id
        ]);

        $this->expedientService->changeLocation($expedient, $location2->id, 'Moving to new shelf');

        $expedient->refresh();
        $this->assertEquals($location2->id, $expedient->current_location_id);

        // Verify movement was recorded
        $this->assertDatabaseHas('expedient_movements', [
            'expedient_id' => $expedient->id,
            'movement_type' => MovementType::Relocated,
            'from_location_id' => $location1->id,
            'to_location_id' => $location2->id,
            'notes' => 'Moving to new shelf'
        ]);
    }

    public function test_it_does_not_record_movement_if_location_is_the_same()
    {
        $location = ArchiveLocation::factory()->create();
        $expedient = Expedient::factory()->create([
            'current_location_id' => $location->id
        ]);

        // Count existing movements (one from factory creation if it uses service, but factory uses Eloquent)
        $initialCount = ExpedientMovement::count();

        $this->expedientService->changeLocation($expedient, $location->id);

        $this->assertEquals($initialCount, ExpedientMovement::count());
    }
}
