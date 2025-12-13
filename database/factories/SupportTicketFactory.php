<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Franchise;
use App\Models\Branch;
use App\Models\Status;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $belongsToFranchise = $this->faker->boolean;
        $statusId = $this->faker->randomElement([1, 16, 16, 16]);
        $franchiseId = Franchise::pluck('id')->random();
        $branchId = Branch::pluck('id')->random();
        $userId = User::pluck('id')->random();

        return [
            'ticket_code' => strtoupper(Str::random(10)),
            'user_id' => $userId,
            'franchise_id' => $belongsToFranchise ? $franchiseId : null,
            'branch_id' => $belongsToFranchise ? null : $branchId,
            'type' => $this->faker->randomElement(['Payment Dispute', 'Adjustment Request']),
            'description' => $this->faker->paragraph(),
            'date' => $this->faker->date(),
            'status_id' => $statusId,
        ];
    }
}
