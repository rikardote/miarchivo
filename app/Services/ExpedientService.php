<?php

namespace App\Services;

use App\Enums\ExpedientStatus;
use App\Enums\MovementType;
use App\Models\Employee;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpedientService
{
    /**
     * Create a new expedient for an employee.
     */
    public function createExpedient(Employee $employee, array $data): Expedient
    {
        return DB::transaction(function () use ($employee, $data) {
            $volume = Expedient::where('employee_id', $employee->id)->max('volume_number') + 1;
            
            // Format: RFC-V1
            $code = strtoupper($employee->rfc) . '-V' . $volume;

            $expedient = Expedient::create([
                'employee_id' => $employee->id,
                'expedient_code' => $code,
                'volume_number' => $volume,
                'current_status' => ExpedientStatus::Available,
                'current_location_id' => $data['location_id'] ?? null,
                'opened_at' => $data['opened_at'] ?? now(),
                'is_active' => true,
            ]);

            $this->recordMovement(
                $expedient,
                MovementType::Created,
                null,
                $expedient->current_location_id,
                'Expediente creado.'
            );

            return $expedient;
        });
    }

    /**
     * Change the physical location of an expedient.
     */
    public function changeLocation(Expedient $expedient, int $newLocationId, ?string $notes = null): void
    {
        DB::transaction(function () use ($expedient, $newLocationId, $notes) {
            $oldLocationId = $expedient->current_location_id;
            
            if ($oldLocationId === $newLocationId) {
                return;
            }

            $expedient->update([
                'current_location_id' => $newLocationId,
            ]);

            $this->recordMovement(
                $expedient,
                MovementType::Relocated,
                $oldLocationId,
                $newLocationId,
                $notes
            );
        });
    }

    /**
     * Internal helper to record an immutable movement.
     */
    public function recordMovement(
        Expedient $expedient,
        MovementType $type,
        ?int $fromLocationId = null,
        ?int $toLocationId = null,
        ?string $notes = null
    ): ExpedientMovement {
        return ExpedientMovement::create([
            'expedient_id' => $expedient->id,
            'user_id' => Auth::id() ?? 1, // Fallback to superuser if not authenticated (e.g. system commands)
            'movement_type' => $type,
            'from_location_id' => $fromLocationId,
            'to_location_id' => $toLocationId,
            'notes' => $notes,
        ]);
    }
}
