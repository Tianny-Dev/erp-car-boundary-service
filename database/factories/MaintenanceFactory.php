<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Franchise;
use App\Models\Status;
use App\Models\UserTechnician;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $maintenanceTypes = ['Oil Change', 'Tire Replacement', 'Brake Service', 'Engine Repair', 'Inspection', 'Other'];

        if ($this->faker->boolean) {
            $franchise = Franchise::inRandomOrder()->first();
            $franchise_id = $franchise->id;
            $branch_id = null;

           $technician = $franchise->technicians()->inRandomOrder()->first()
                        ?? UserTechnician::inRandomOrder()->first();
        } else {
            $franchise_id = null;
            $branch_id = $this->faker->numberBetween(1, 3);

            $technician = UserTechnician::inRandomOrder()->first();
        }

        return [
            'vehicle_id' => Vehicle::factory(),
            'franchise_id' => $franchise_id,
            'branch_id' => $branch_id,
            'expense_id' => Expense::factory(),
            'maintenance_type' => $this->faker->randomElement($maintenanceTypes),
            'description' => $this->faker->paragraph,
            'maintenance_date' => $this->faker->dateTimeBetween('now', '+3 month')->format('Y-m-d'),
            'next_maintenance_date' => $this->faker->dateTimeBetween('now', '+3 month')->format('Y-m-d'),
            'status_id' => $this->faker->randomElement([1, 1, 1, 2, 6]),
            'technician_id' => $technician->id,
        ];
    }
}
