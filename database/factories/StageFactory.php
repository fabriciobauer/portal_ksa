<?php

namespace Database\Factories;

use App\Models\Season;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'name' => 'Etapa '.fake()->numberBetween(1, 12),
            'stage_number' => fake()->unique()->numberBetween(1, 12),
            'stage_date' => fake()->date(),
            'briefing_time' => '08:00',
            'draw_time' => '08:30',
            'location' => fake()->city(),
            'track_layout' => 'Traçado A',
            'notes' => null,
            'status' => 'planned',
        ];
    }
}
