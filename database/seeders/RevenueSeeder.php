<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use App\Models\Revenue;
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
        // PART 2: GENERATE TRIP REVENUES (Random Logic)
        // ---------------------------------------------------------
        // Use the factory for this to generate extra noise/data
        Revenue::factory()->count(300)->create([
            'service_type' => 'Trips',
            'boundary_contract_id' => null, // Trips don't link to boundary contracts
        ]);
    }
}
