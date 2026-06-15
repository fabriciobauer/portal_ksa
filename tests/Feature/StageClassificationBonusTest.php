<?php

namespace Tests\Feature;

use App\Models\ChampionshipPoint;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Services\ChampionshipCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageClassificationBonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_bonus_counts_only_for_championship_points(): void
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
        $entry = StageCategoryEntry::query()->create([
            'stage_category_id' => $stageCategory->id,
            'pilot_id' => Pilot::factory()->create()->id,
            'confirmation_status' => 'confirmed',
            'attendance_status' => 'present',
            'briefing_status' => 'present',
        ]);
        $raceOne = Race::factory()->create([
            'stage_category_id' => $stageCategory->id,
            'number' => 1,
            'name' => 'Corrida 1',
        ]);
        $raceTwo = Race::factory()->create([
            'stage_category_id' => $stageCategory->id,
            'number' => 2,
            'name' => 'Corrida 2',
        ]);

        RaceResult::query()->create([
            'race_id' => $raceOne->id,
            'stage_category_entry_id' => $entry->id,
            'grid_position' => 1,
            'finish_position' => 1,
            'kart_number' => 1,
            'status' => 'finished',
            'points_awarded' => 0,
        ]);
        RaceResult::query()->create([
            'race_id' => $raceTwo->id,
            'stage_category_entry_id' => $entry->id,
            'grid_position' => 1,
            'finish_position' => 1,
            'kart_number' => 1,
            'status' => 'finished',
            'points_awarded' => 0,
        ]);

        app(ChampionshipCalculatorService::class)->recalculateStageCategory($stageCategory);

        $standing = $entry->stageStanding()->firstOrFail();
        $championshipPoint = ChampionshipPoint::query()
            ->where('season_category_id', $seasonCategory->id)
            ->where('stage_id', $stage->id)
            ->where('pilot_id', $entry->pilot_id)
            ->firstOrFail();

        $this->assertSame(1.0, (float) $standing->completion_bonus);
        $this->assertSame(30.0, (float) $standing->gross_stage_points);
        $this->assertSame(31.0, (float) $standing->championship_points);

        $this->assertSame(30.0, (float) $championshipPoint->gross_points);
        $this->assertSame(1.0, (float) $championshipPoint->adjustment_points);
        $this->assertSame(31.0, (float) $championshipPoint->valid_points);
    }
}
