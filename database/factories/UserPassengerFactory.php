<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserPassenger;
use Illuminate\Support\Carbon; // Import Carbon for age calculation

class UserPassengerFactory extends Factory
{
    protected $model = UserPassenger::class;

    public function definition(): array
    {
        $languages = ['English', 'Filipino', 'Others'];
        $accessOptions = ['Normal', 'Wheelchair Access', 'Pet-Friendly Ride'];

        // Ensure age and birth_date match
        $birthDate = fake()->dateTimeBetween('-80 years', '-18 years');

        return [
            'status_id' => random_int(1, 8),
            'payment_option_id' => random_int(1, 4),
            'preferred_language' => fake()->randomElement($languages),
            'accessibility_option' => fake()->randomElement($accessOptions),
            'birth_date' => $birthDate,
        ];
    }
}
