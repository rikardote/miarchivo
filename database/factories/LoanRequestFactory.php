<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Expedient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRequest>
 */
class LoanRequestFactory extends Factory
{
    protected $model = \App\Models\LoanRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expedient_id' => Expedient::factory(),
            'requester_id' => User::factory(),
            'status' => LoanStatus::Pending,
            'requested_at' => now(),
            'due_date' => now()->addDays(7),
        ];
    }
}
