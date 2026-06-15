<?php

namespace Database\Factories;

use App\Models\Pilot;
use Illuminate\Database\Eloquent\Factories\Factory;

class PilotFactory extends Factory
{
    protected $model = Pilot::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'cpf' => null,
            'nickname' => fake()->firstName(),
            'photo_path' => null,
            'birth_date' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'base_weight' => fake()->randomFloat(2, 70, 100),
            'notes' => null,
            'metadata' => null,
            'is_active' => true,
        ];
    }
}
