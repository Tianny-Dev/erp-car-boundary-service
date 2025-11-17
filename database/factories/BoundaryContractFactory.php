<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoundaryContract>
 */
class BoundaryContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween(
            startDate: now()->startOfYear(),
            endDate: now()->endOfYear()->subMonths(2)
        );
        $end = $this->faker->dateTimeBetween(
            startDate: $start,
            endDate: now()->endOfYear()
        );

        return [
            'status_id' => $this->faker->numberBetween(1, 3),
            'franchise_id' => $this->faker->numberBetween(1, 5),
            'name' => 'Contract ' . Str::upper(Str::random(5)),
            'coverage_area' => $this->faker->city() . ', ' . $this->faker->state(),
            'contract_terms' => $this->faker->paragraph(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'renewal_terms' => $this->faker->sentence(),
        ];
    }
}
