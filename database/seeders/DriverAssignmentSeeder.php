<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserDriver;
use App\Models\Franchise;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class DriverAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $franchiseIds = Franchise::pluck('id')->toArray();
        $branchIds    = Branch::pluck('id')->toArray();

        // Loop through all drivers
        UserDriver::all()->each(function ($driver) use ($franchiseIds, $branchIds) {
            // Randomly decide whether this driver goes to a franchise or a branch
            if (fake()->boolean) {
                // Assign to a random franchise
                $franchiseId = fake()->randomElement($franchiseIds);

                DB::table('franchise_user_driver')->insert([
                    'franchise_id'   => $franchiseId,
                    'user_driver_id' => $driver->id,
                ]);
            } else {
                // Assign to a random branch
                $branchId = fake()->randomElement($branchIds);

                DB::table('branch_user_driver')->insert([
                    'branch_id'      => $branchId,
                    'user_driver_id' => $driver->id,
                ]);
            }
        });
    }
}
