<?php

namespace Database\Factories;

use App\Models\StageCategoryEntry;
use App\Models\StageStanding;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageStandingFactory extends Factory
{
    protected $model = StageStanding::class;

    public function definition(): array
    {
        return [
            'stage_category_entry_id' => StageCategoryEntry::factory(),
            'race1_points' => 15,
            'race2_points' => 13,
            'completion_bonus' => 1,
            'gross_stage_points' => 29,
            'championship_penalty_points' => 0,
            'manual_adjustment_points' => 0,
            'manual_override_points' => null,
            'championship_points' => 29,
            'stage_position' => 1,
            'is_technical_tie' => false,
            'tie_breaker_resolved_manually' => false,
            'tie_break_notes' => null,
            'is_disqualified' => false,
            'disqualification_reason' => null,
            'discard_blocked' => false,
            'override_reason' => null,
            'override_user_id' => null,
            'override_at' => null,
            'last_recalculated_at' => now(),
        ];
    }
}
