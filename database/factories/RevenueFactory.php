<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenue>
 */
class RevenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $serviceTypes = ['Trips', 'Ads', 'Logistics', 'Boundary', 'Other'];

        return [
            'status_id' => $this->faker->numberBetween(1, 3),
            'franchise_id' => 1,
            'payment_option_id' => $this->faker->numberBetween(1, 3),
            'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'PHP',
            'service_type' => $this->faker->randomElement($serviceTypes),
            'payment_date' => $this->faker->dateTimeBetween(now()->startOfWeek(),now()->endOfWeek()),
            'notes' => $this->faker->sentence(),
        ];
    }
}
