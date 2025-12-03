<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Franchise;
use App\Models\Branch;
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
        // We weight it so we have more active vehicles for testing
        $statusId = $this->faker->randomElement([1, 1, 1, 5, 15]);

        // 2. Decide: Franchise or Branch?
        $assignFranchise = $this->faker->boolean;
        $franchiseId = null;
        $branchId = null;
        $driverId = null;

        if ($assignFranchise) {
            $franchiseId = Franchise::inRandomOrder()->value('id');
            // If status is NOT Available (15), we need a driver from this franchise
            if ($statusId !== 15) {
                $driverId = DB::table('franchise_user_driver')
                    ->where('franchise_id', $franchiseId)
                    // Ensure we pick a driver not already assigned to another vehicle (optional, but good for data integrity)
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('vehicles')
                              ->whereColumn('vehicles.driver_id', 'franchise_user_driver.user_driver_id');
                    })
                    ->inRandomOrder()
                    ->value('user_driver_id');

                // If no available driver found for this franchise, fallback to 'Available' status
                if (!$driverId) {
                    $statusId = 15;
                }
            }
        } else {
            $branchId = Branch::inRandomOrder()->value('id');
            // If status is NOT Available (15), we need a driver from this branch
            if ($statusId !== 15) {
                $driverId = DB::table('branch_user_driver')
                    ->where('branch_id', $branchId)
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('vehicles')
                              ->whereColumn('vehicles.driver_id', 'branch_user_driver.user_driver_id');
                    })
                    ->inRandomOrder()
                    ->value('user_driver_id');

                if (!$driverId) {
                    $statusId = 15;
                }
            }
        }

        return [
            'status_id'    => $statusId,
            'franchise_id' => $franchiseId,
            'branch_id'    => $branchId,
            'driver_id'    => $driverId, // Will be null if status is 15 (Available)
            'plate_number' => strtoupper($this->faker->unique()->bothify('??###??')),
            'vin'          => strtoupper(Str::random(17)),
            'brand'        => $this->faker->randomElement(['Toyota', 'Honda', 'Ford', 'Nissan', 'Hyundai']),
            'model'        => $this->faker->word(),
            'year'         => $this->faker->year(),
            'color'        => $this->faker->safeColorName(),
        ];
    }
}
