<?php

namespace Database\Factories;

use App\Enums\ExpedientStatus;
use App\Models\ArchiveLocation;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expedient>
 */
class ExpedientFactory extends Factory
{
    protected $model = \App\Models\Expedient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'expedient_code' => fake()->unique()->numerify('EXP-####'),
            'volume_number' => 1,
            'current_status' => ExpedientStatus::Available,
            'current_location_id' => ArchiveLocation::factory(),
            'current_holder_id' => null,
            'opened_at' => now(),
            'is_active' => true,
        ];
    }
}
