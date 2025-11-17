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
        // Randomly decide whether to assign franchise or branch
        $assignFranchise = $this->faker->boolean;

        if ($assignFranchise) {
            // Pick a random franchise
            $franchiseId = Franchise::inRandomOrder()->value('id');
            // Pick a driver that belongs to this franchise
            $driverId = DB::table('franchise_user_driver')
                ->where('franchise_id', $franchiseId)
                ->inRandomOrder()
                ->value('user_driver_id');
            $branchId = null;
        } else {
            // Pick a random branch
            $branchId = Branch::inRandomOrder()->value('id');
            // Pick a driver that belongs to this branch
            $driverId = DB::table('branch_user_driver')
                ->where('branch_id', $branchId)
                ->inRandomOrder()
                ->value('user_driver_id');
            $franchiseId = null;
        }

        return [
            'status_id'    => $this->faker->randomElement([1, 5]),
            'franchise_id' => $franchiseId,
            'branch_id'    => $branchId,
            'driver_id'    => $driverId,
            'plate_number' => strtoupper($this->faker->unique()->bothify('??###??')),
            'vin'          => strtoupper(Str::random(17)), // typical VIN length
            'brand'        => $this->faker->randomElement(['Toyota', 'Honda', 'Ford', 'Nissan', 'Hyundai']),
            'model'        => $this->faker->word(),
            'year'         => $this->faker->year(),
            'color'        => $this->faker->safeColorName(),
        ];
    }
}
