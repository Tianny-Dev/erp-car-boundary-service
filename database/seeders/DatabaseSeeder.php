<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // fix value
        $this->call(UserTypeSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(PaymentOptionSeeder::class);

        User::factory()->create([
            'user_type_id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(30)->create();

        User::factory(20)->create([
            'user_type_id' => 4,
        ]);

        $this->call(DriverAssignmentSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(VehicleSeeder::class);
        Vehicle::factory(10)->create([
            'driver_id' => null
        ]);

        $this->call(BoundaryContractSeeder::class);
        $this->call(RevenueSeeder::class);

        $this->call(PercentageTypeSeeder::class);
        $this->call(RevenueBreakdownSeeder::class);
    }
}
