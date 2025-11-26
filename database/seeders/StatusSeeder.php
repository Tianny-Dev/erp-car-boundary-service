<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'active'],
            ['id' => 2, 'name' => 'inactive'],
            ['id' => 3, 'name' => 'suspended'],
            ['id' => 4, 'name' => 'retired'],
            ['id' => 5, 'name' => 'maintenance'],
            ['id' => 6, 'name' => 'pending'],
            ['id' => 7, 'name' => 'overdue'],
            ['id' => 8, 'name' => 'paid'],
            ['id' => 9, 'name' => 'que'],
            ['id' => 10, 'name' => 'to_pick_up'],
            ['id' => 11, 'name' => 'start_trip'],
            ['id' => 12, 'name' => 'end_trip'],
            ['id' => 13, 'name' => 'cancelled'],
            ['id' => 14, 'name' => 'confirm_pick_up'],
        ]);
    }
}
