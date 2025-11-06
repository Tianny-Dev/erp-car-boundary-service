<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserManager;

class UserManagerFactory extends Factory
{
    protected $model = UserManager::class;

    public function definition(): array
    {
        return [
            'status_id' => random_int(1, 8), // From StatusSeeder
        ];
    }
}