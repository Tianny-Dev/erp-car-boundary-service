<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BoundaryContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BoundaryContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find drivers who have been assigned to a franchise
        $franchiseDrivers = DB::table('franchise_user_driver')->get();
        
        // Find drivers who have been assigned to a branch
        $branchDrivers = DB::table('branch_user_driver')->get();

        $allAssignments = $franchiseDrivers->map(fn($item) => ['type' => 'franchise', 'data' => $item])
            ->merge($branchDrivers->map(fn($item) => ['type' => 'branch', 'data' => $item]));

        foreach ($allAssignments as $assignment) {
            $isFranchise = $assignment['type'] === 'franchise';
            $data = $assignment['data'];
            $driverId = $data->user_driver_id;

            $vehicle = DB::table('vehicles')
                ->where('driver_id', $driverId)
                // Optional: Only allow if vehicle is 'active' (1)
                // ->where('status_id', 1) 
                ->first();

            // CRITICAL: If this driver does NOT have a vehicle, skip creating a contract.
            if (!$vehicle) {
                continue;
            }

            $startDate = Carbon::now()->subMonth();
            $endDate = Carbon::now()->addMonths(2);
            $boundaryAmount = fake()->randomElement([200, 300, 500]);

            BoundaryContract::create([
                'status_id' => 1, // Active
                'franchise_id' => $isFranchise ? $data->franchise_id : null,
                'branch_id' => !$isFranchise ? $data->branch_id : null,
                'driver_id'       => $driverId,
                'vehicle_id'      => $vehicle->id,
                'name' => 'Boundary Contract - ' . ($isFranchise ? 'Franchise' : 'Branch'),
                'amount' => $boundaryAmount,
                'currency' => 'PHP',
                'coverage_area' => fake()->city(),
                'contract_terms' => 'Standard 3 Month Boundary Agreement',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'renewal_terms' => 'Auto-renewal upon review',
            ]);
        }
    }
}
