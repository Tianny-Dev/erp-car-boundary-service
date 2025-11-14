<?php

namespace Database\Seeders;

use App\Models\User;
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
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(30)->create();

        $this->call(RevenueSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(BoundaryContractSeeder::class);
    }
}
