<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Season;
use App\Models\SeasonCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonCategoryFactory extends Factory
{
    protected $model = SeasonCategory::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'category_id' => Category::factory(),
            'pilot_limit' => 12,
            'is_active' => true,
            'notes' => null,
        ];
    }
}
