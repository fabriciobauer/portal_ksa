<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Season;
use App\Models\SeasonCategory;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->updateOrCreate(
            ['slug' => 'temporada-2026'],
            [
                'name' => 'Temporada 2026',
                'status' => 'active',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_current' => true,
                'description' => null,
            ],
        );

        foreach (Category::query()->get() as $category) {
            SeasonCategory::query()->updateOrCreate(
                [
                    'season_id' => $season->id,
                    'category_id' => $category->id,
                ],
                [
                    'pilot_limit' => $category->default_pilot_limit,
                    'is_active' => true,
                ],
            );
        }
    }
}
