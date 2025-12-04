<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_no'       => 'INV-' . $this->faker->unique()->numerify('#####'),
            'name'          => $this->faker->unique()->words(3, true),
            'category'      => $this->faker->randomElement(['Electrical', 'Mechanical', 'Safety Equipment', 'Consumables']),
            'specification' => $this->faker->sentence(),
            'quantity'      => $this->faker->numberBetween(10, 100),
            'unit_price'    => $this->faker->randomFloat(2, 50, 5000),
            'notes'         => $this->faker->optional()->sentence(),
        ];
    }
}
