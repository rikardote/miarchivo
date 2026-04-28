<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = \App\Models\Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_api_id' => fake()->unique()->numberBetween(1000, 9999),
            'employee_number' => fake()->unique()->numerify('#####'),
            'rfc' => fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'position' => fake()->jobTitle(),
            'work_center' => fake()->company(),
            'city' => fake()->city(),
            'department_id' => Department::factory(),
            'branch_id' => Branch::factory(),
            'employment_status' => 'active',
            'last_synced_at' => now(),
        ];
    }
}
