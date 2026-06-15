<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\StageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaceFactory extends Factory
{
    protected $model = Race::class;

    public function definition(): array
    {
        return [
            'stage_category_id' => StageCategory::factory(),
            'number' => 1,
            'name' => 'Corrida 1',
            'grid_generated_from' => null,
            'is_grid_confirmed' => false,
            'is_result_confirmed' => false,
            'confirmed_at' => null,
        ];
    }
}
