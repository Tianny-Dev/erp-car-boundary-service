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
        $serviceType = $this->faker->randomElement($serviceTypes);

        // Randomly decide whether to assign franchise or branch
        $assignFranchise = $this->faker->boolean;

        $franchiseId = null;
        $branchId = null;
        $driverId = null;

        if ($assignFranchise) {
            $franchiseId = Franchise::inRandomOrder()->value('id');

            // Only assign driver if service type is Trips
            if ($serviceType === 'Trips') {
                $driverId = DB::table('franchise_user_driver')
                    ->where('franchise_id', $franchiseId)
                    ->inRandomOrder()
                    ->value('user_driver_id');
            }
        } else {
            $branchId = Branch::inRandomOrder()->value('id');

            // Only assign driver if service type is Trips
            if ($serviceType === 'Trips') {
                $driverId = DB::table('branch_user_driver')
                    ->where('branch_id', $branchId)
                    ->inRandomOrder()
                    ->value('user_driver_id');
            }
        }

        // Decide status first
        $statusId = $this->faker->numberBetween(6, 9); // pending, overdue, paid, cancelled
        $paymentDate = $statusId === 8 // paid
        ? $this->faker->dateTimeBetween(
            now()->startOfWeek(),
            now()->endOfWeek()
        )
        : null;

        return [
            'status_id' => $statusId,
            'franchise_id' => $franchiseId,
            'branch_id'    => $branchId,
            'driver_id'    => $driverId,
            'payment_option_id' => $this->faker->numberBetween(1, 4),
            'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'PHP',
            'service_type' => $serviceType,
            'payment_date' => $paymentDate,
            'notes' => $this->faker->sentence(),
        ];
    }
}
