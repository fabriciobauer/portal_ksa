<?php

namespace Tests\Feature;

use App\Models\Pilot;
use App\Models\QualifyingResult;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Services\GridService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GridPenaltyInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_briefing_penalty_positions_do_not_change_auto_grid(): void
    {
        $season = Season::factory()->create();
        $seasonCategory = SeasonCategory::factory()->create([
            'season_id' => $season->id,
        ]);
        $stage = Stage::factory()->create([
            'season_id' => $season->id,
            'status' => 'open',
        ]);
        $stageCategory = StageCategory::factory()->create([
            'stage_id' => $stage->id,
            'season_category_id' => $seasonCategory->id,
            'status' => 'open',
        ]);
        $firstEntry = StageCategoryEntry::query()->create([
            'stage_category_id' => $stageCategory->id,
            'pilot_id' => Pilot::factory()->create()->id,
            'confirmation_status' => 'confirmed',
            'attendance_status' => 'present',
            'briefing_status' => 'late',
            'briefing_penalty_grid_positions' => 5,
        ]);
        $secondEntry = StageCategoryEntry::query()->create([
            'stage_category_id' => $stageCategory->id,
            'pilot_id' => Pilot::factory()->create()->id,
            'confirmation_status' => 'confirmed',
            'attendance_status' => 'present',
            'briefing_status' => 'present',
            'briefing_penalty_grid_positions' => 0,
        ]);

        QualifyingResult::query()->create([
            'stage_category_entry_id' => $firstEntry->id,
            'current_kart_number' => 1,
            'lap_time_ms' => 60000,
            'status' => 'valid',
            'confirmed_at' => now(),
        ]);
        QualifyingResult::query()->create([
            'stage_category_entry_id' => $secondEntry->id,
            'current_kart_number' => 2,
            'lap_time_ms' => 61000,
            'status' => 'valid',
            'confirmed_at' => now(),
        ]);

        $grid = app(GridService::class)->generateRaceOneGrid($stageCategory, false);

        $this->assertSame($firstEntry->id, data_get($grid->first(), 'entry.id'));
        $this->assertSame($secondEntry->id, data_get($grid->get(1), 'entry.id'));
    }
}
