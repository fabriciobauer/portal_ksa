<?php

namespace Database\Factories;

use App\Models\Pilot;
use App\Models\SeasonCategoryRegistration;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageCategoryEntryFactory extends Factory
{
    protected $model = StageCategoryEntry::class;

    public function definition(): array
    {
        return [
            'stage_category_id' => StageCategory::factory(),
            'pilot_id' => Pilot::factory(),
            'season_category_registration_id' => SeasonCategoryRegistration::factory(),
            'confirmation_status' => 'confirmed',
            'attendance_status' => 'present',
            'briefing_status' => 'present',
            'briefing_penalty_grid_positions' => 0,
            'notes' => null,
            'created_by' => null,
            'checked_in_at' => now(),
        ];
    }
}
