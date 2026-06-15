<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2030);

        return [
            'name' => "Temporada {$year}",
            'slug' => "temporada-{$year}-".fake()->unique()->numberBetween(1, 999),
            'status' => 'active',
            'start_date' => "{$year}-01-01",
            'end_date' => "{$year}-12-31",
            'is_current' => false,
            'description' => fake()->sentence(),
        ];
    }
}
