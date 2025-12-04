<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\UserTechnician;
use Illuminate\Support\Facades\DB;

class TechnicianAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $franchises = Franchise::all();
        $branches = Branch::all();

        // Get all technicians
        $technicians = UserTechnician::all();

        // Shuffle technicians so assignments are random
        $technicians = $technicians->shuffle();

        // Split technicians into two groups:
        // Half go to franchises, half go to branches
        $franchiseTechs = $technicians->slice(0, floor($technicians->count() / 2));
        $branchTechs = $technicians->slice(floor($technicians->count() / 2));

        // Assign franchise technicians
        foreach ($franchises as $franchise) {
            if ($franchiseTechs->isEmpty()) break;

            // Pick 1–2 technicians from the pool
            $assignedTechs = $franchiseTechs->splice(0, rand(1, 2))->pluck('id');
            $franchise->technicians()->sync($assignedTechs);
        }

        // Assign branch technicians
        foreach ($branches as $branch) {
            if ($branchTechs->isEmpty()) break;

            // Pick 1–2 technicians from the pool
            $assignedTechs = $branchTechs->splice(0, rand(1, 2))->pluck('id');
            $branch->technicians()->sync($assignedTechs);
        }
    }
}
