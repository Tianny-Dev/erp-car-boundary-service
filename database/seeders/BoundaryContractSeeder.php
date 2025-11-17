<?php

namespace Database\Seeders;

use App\Models\BoundaryContract;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoundaryContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BoundaryContract::factory()->count(50)->create();
    }
}
