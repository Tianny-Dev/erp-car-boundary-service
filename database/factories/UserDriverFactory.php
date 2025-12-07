<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserDriver;

class UserDriverFactory extends Factory
{
    protected $model = UserDriver::class;

    public function definition(): array
    {

        $statusId = fake()->randomElement([1, 1, 1, 2]);

        return [
            // 'id' is intentionally omitted here
            // It will be provided by the UserFactory
            'status_id' => $statusId,
            'payment_option_id' => random_int(1, 4), // From PaymentOptionSeeder
            'license_number' => fake()->unique()->bothify('??-########'),
            'is_verified' => $statusId == 1,
            'license_expiry' => fake()->dateTimeBetween('+1 year', '+5 years'),
            'front_license_picture' => fake()->imageUrl(640, 480, 'license', true),
            'back_license_picture' => fake()->imageUrl(640, 480, 'license', true),
            'nbi_clearance' => fake()->imageUrl(640, 480, 'document', true),
            'selfie_picture' => fake()->imageUrl(640, 480, 'person', true),
            'hire_date' => fake()->optional()->date(),
        ];
    }
}