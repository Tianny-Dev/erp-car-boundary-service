<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Franchise;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Determine Status (Active: 1, Maintenance: 5, Available: 15)
        $statusId = $this->faker->randomElement([1, 1, 1, 5, 15]);
        $franchiseId = Franchise::inRandomOrder()->value('id');
        $driverId = null;

        // If we randomly rolled a status that requires a driver
        if ($statusId !== 15) {
            $driverId = DB::table('franchise_user_driver')
                ->where('franchise_id', $franchiseId)
                // Look for drivers who don't have a vehicle yet
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('vehicles')
                        ->whereColumn('vehicles.driver_id', 'franchise_user_driver.user_driver_id');
                })
                ->inRandomOrder()
                ->value('user_driver_id');

            // FALLBACK: If no unique driver is available for this franchise, 
            // force it to be Available (15) and set driver to null.
            if (!$driverId) {
                $statusId = 15;
            }
        }

        return [
            'status_id'    => $statusId,
            'franchise_id' => $franchiseId,
            'driver_id'    => $driverId, // Will be null if status forced or rolled to 15
            'plate_number' => strtoupper($this->faker->unique()->bothify('??###??')),
            'vin'          => strtoupper(Str::random(17)),
            'brand'        => $this->faker->randomElement(['Toyota', 'Honda', 'Ford', 'Nissan', 'Hyundai']),
            'model'        => $this->faker->word(),
            'year'         => $this->faker->year(),
            'color'        => $this->faker->safeColorName(),
            'or_cr'        => fake()->imageUrl(640, 480, 'id', true),
        ];
    }
}
