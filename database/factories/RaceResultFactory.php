<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\StageCategoryEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaceResultFactory extends Factory
{
    protected $model = RaceResult::class;

    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'stage_category_entry_id' => StageCategoryEntry::factory(),
            'grid_position' => 1,
            'finish_position' => 1,
            'kart_number' => fake()->numberBetween(1, 15),
            'status' => 'finished',
            'points_awarded' => 15,
            'is_official' => true,
            'notes' => null,
        ];
    }
}
