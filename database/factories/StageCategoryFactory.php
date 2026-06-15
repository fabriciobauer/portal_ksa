<?php

namespace Database\Factories;

use App\Models\SeasonCategory;
use App\Models\Stage;
use App\Models\StageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageCategoryFactory extends Factory
{
    protected $model = StageCategory::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'season_category_id' => SeasonCategory::factory(),
            'status' => 'open',
            'management_locked_at' => null,
            'notes' => null,
        ];
    }
}
