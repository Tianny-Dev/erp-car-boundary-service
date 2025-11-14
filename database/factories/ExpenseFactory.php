<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

        return [
            'status_id' => $this->faker->numberBetween(1, 3),
            'franchise_id' => 1,
            'payment_option_id' => $this->faker->numberBetween(1, 3),
            'invoice_no' => 'EXP-' . Str::upper(Str::random(6)),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'currency' => 'PHP',
            'expense_type' => $this->faker->randomElement($expenseTypes),
            'payment_date' => $this->faker->dateTimeBetween(now()->startOfWeek(),now()->endOfWeek()),
            'notes' => $this->faker->sentence(),
        ];
    }
}
