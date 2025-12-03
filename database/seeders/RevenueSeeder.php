<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use App\Models\Revenue;
use App\Models\Vehicle;
use App\Models\BoundaryContract;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---------------------------------------------------------
        // PART 1: GENERATE BOUNDARY REVENUES (Strict Logic)
        // ---------------------------------------------------------
        
        // Fetch all active contracts
        $contracts = BoundaryContract::all();

        // Status IDs (Adjust based on your StatusSeeder)
        $STATUS_PAID = 8;    
        $STATUS_OVERDUE = 7; 

        foreach ($contracts as $contract) {
            // Create a period from Start Date until Today
            $period = CarbonPeriod::create($contract->start_date, Carbon::now());

            foreach ($period as $date) {
                // Randomize status: 80% chance Paid, 20% Overdue
                $isPaid = fake()->boolean(80); 
                $statusId = $isPaid ? $STATUS_PAID : $STATUS_OVERDUE;

                Revenue::create([
                    'status_id' => $statusId,
                    'franchise_id' => $contract->franchise_id,
                    'branch_id' => $contract->branch_id,
                    'driver_id' => $contract->driver_id,
                    'boundary_contract_id' => $contract->id,
                    'payment_option_id' => fake()->numberBetween(1, 4), // Cash, Gcash, etc
                    'invoice_no' => 'BND-' . strtoupper(Str::random(8)),
                    'amount' => $contract->amount, // STRICT: Must match contract
                    'currency' => 'PHP',
                    'service_type' => 'Boundary',
                    // If paid, date is the loop date. If overdue, date is null or the due date
                    'payment_date' => $isPaid ? $date : null, 
                    'notes' => $isPaid ? 'Daily boundary remittance' : 'Missed payment',
                    'created_at' => $date, // Backdate the creation
                    'updated_at' => $date,
                ]);
            }
        }

        // ---------------------------------------------------------
        // PART 2: GENERATE TRIP REVENUES (Aligned Logic)
        // ---------------------------------------------------------
        
        // 1. Get Boundary Contracts (Because drivers MUST have a contract to have trip revenue)
        $contracts = BoundaryContract::with(['driver', 'vehicle'])->get();

        foreach ($contracts as $contract) {
            
            // 2. Validate Vehicle Status
            // Check if the vehicle associated with this contract/driver is valid for trips.
            // We assume only 'Active' (1) vehicles generate trip revenue. 
            // 'Maintenance' (5) or 'Available' (15) usually do not have trips.
            $vehicle = Vehicle::where('driver_id', $contract->driver_id)
                              ->where('id', $contract->vehicle_id)
                              ->first();

            // If driver has no vehicle, or vehicle is not Active, skip.
            if (!$vehicle || $vehicle->status_id !== 1) {
                continue;
            }

            // 3. Generate Trips for this specific Setup
            // Generate random number of trips within the contract period
            $numberOfTrips = rand(5, 15); 

            for ($i = 0; $i < $numberOfTrips; $i++) {
                
                // Random date within contract start and now
                $tripDate =  fake()->dateTimeBetween($contract->start_date, 'now');
                
                Revenue::create([
                    'status_id'          => 8, // Paid
                    'franchise_id'       => $contract->franchise_id, // Inherit from contract
                    'branch_id'          => $contract->branch_id,    // Inherit from contract
                    'driver_id'          => $contract->driver_id,
                    'boundary_contract_id' => null, // Trips don't link to boundary contracts ID directly
                    'payment_option_id'  => fake()->numberBetween(1, 4),
                    'invoice_no'         => 'TRP-' . strtoupper(Str::random(8)),
                    'amount'             => fake()->randomFloat(2, 50, 1500), // Typical trip fare
                    'currency'           => 'PHP',
                    'service_type'       => 'Trips',
                    'payment_date'       => $tripDate,
                    'notes'              => 'Trip from ' . fake()->streetName() . ' to ' . fake()->streetName(),
                    'created_at'         => $tripDate,
                    'updated_at'         => $tripDate,
                ]);
            }
        }
    }
}
