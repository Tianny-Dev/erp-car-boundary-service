<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Franchise;
use App\Models\Branch;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expenseTypes = ['Salaries', 'Rent', 'Utilities', 'Supplies', 'Maintenance', 'Marketing', 'Other'];

        // Get random IDs from existing franchises and branches
        $franchiseId = Franchise::inRandomOrder()->value('id');
        $branchId = Branch::inRandomOrder()->value('id');

        // Decide randomly whether to assign franchise or branch
        $assignFranchise = $this->faker->boolean;

        return [
            'status_id' => $this->faker->numberBetween(6, 9), // pending, overdue, cancelled, paid
            'franchise_id' => $assignFranchise ? $franchiseId : null,
            'branch_id'    => $assignFranchise ? null : $branchId,
            'payment_option_id' => $this->faker->numberBetween(1, 4),
            'invoice_no' => 'EXP-' . Str::upper(Str::random(6)),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'currency' => 'PHP',
            'expense_type' => $this->faker->randomElement($expenseTypes),
            'payment_date' => $this->faker->dateTimeBetween(now()->startOfWeek(),now()->endOfWeek()),
            'notes' => $this->faker->sentence(),
        ];
    }
}
