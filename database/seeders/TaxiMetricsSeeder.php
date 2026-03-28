<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxiMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('taxi_metrics')->insert([
            'flag'       => 50.00,
            'per_km'     => 13.50,
            'per_minute' => 2.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
