<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Franchise;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

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
        $serviceTypes = ['Trips', 'Boundary'];

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
            'status_id' => $this->faker->numberBetween(6, 9), // pending, overdue, cancelled, paid
            'franchise_id' => $franchiseId,
            'branch_id'    => $branchId,
            'driver_id'    => $driverId,
            'payment_option_id' => $this->faker->numberBetween(1, 4),
            'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'PHP',
            'service_type' => $this->faker->randomElement($serviceTypes),
            'payment_date' => $this->faker->dateTimeBetween(
                now()->startOfWeek(),
                now()->endOfWeek()
            ),
            'notes' => $this->faker->sentence(),
        ];
    }
}
