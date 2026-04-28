<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArchiveLocation>
 */
class ArchiveLocationFactory extends Factory
{
    protected $model = \App\Models\ArchiveLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'location_type' => fake()->randomElement(['Archivo de Concentración', 'Archivo de Trámite']),
            'archive_name' => fake()->word(),
            'cabinet' => fake()->numberBetween(1, 10),
            'drawer' => fake()->numberBetween(1, 5),
            'alpha_range' => 'A-Z',
            'notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
