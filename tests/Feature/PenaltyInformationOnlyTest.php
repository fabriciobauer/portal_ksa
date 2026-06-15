<?php

namespace Tests\Feature;

use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceOccurrence;
use App\Models\RaceResult;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Models\User;
use App\Services\ChampionshipCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenaltyInformationOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_penalty_record_does_not_override_race_result_or_stage_scoring(): void
    {
        [$user, $stageCategory, $entry, $raceOneResult] = $this->buildRaceContext();
        $calculator = app(ChampionshipCalculatorService::class);

        $calculator->recalculateStageCategory($stageCategory);
        $baselineStanding = $entry->stageStanding()->firstOrFail();
        $baselinePoints = (float) $baselineStanding->championship_points;

        $response = $this->actingAs($user)->post(route('stage-management.penalties.store', $stageCategory), [
            'stage_category_entry_id' => $entry->id,
            'race_id' => $raceOneResult->race_id,
            'type' => 'race_dsq',
            'penalty_scope' => 'stage_and_championship',
            'points_delta' => -5,
            'is_disqualification' => 1,
            'affects_discard_block' => 1,
            'reason' => 'Registro apenas informativo',
        ]);

        $response->assertRedirect();

        $raceOneResult->refresh();
        $this->assertSame('finished', $raceOneResult->status);

        $calculator->recalculateStageCategory($stageCategory);

        $standing = $entry->stageStanding()->firstOrFail()->fresh();

        $this->assertSame(0.0, (float) $standing->championship_penalty_points);
        $this->assertFalse((bool) $standing->is_disqualified);
        $this->assertFalse((bool) $standing->discard_blocked);
        $this->assertSame($baselinePoints, (float) $standing->championship_points);
    }

    public function test_occurrence_record_does_not_override_race_result_or_stage_scoring(): void
    {
        [$user, $stageCategory, $entry, $raceOneResult] = $this->buildRaceContext();
        $calculator = app(ChampionshipCalculatorService::class);

        $calculator->recalculateStageCategory($stageCategory);
        $baselineStanding = $entry->stageStanding()->firstOrFail();
        $baselinePoints = (float) $baselineStanding->championship_points;

        foreach (range(1, 3) as $warning) {
            $response = $this->actingAs($user)->post(route('stage-management.occurrences.store', $stageCategory), [
                'stage_category_entry_id' => $entry->id,
                'race_id' => $raceOneResult->race_id,
                'type' => 'warning',
                'description' => "Advertencia {$warning}",
            ]);

            $response->assertRedirect();
        }

        $latestOccurrence = RaceOccurrence::query()->latest('id')->firstOrFail();

        $this->assertTrue((bool) $latestOccurrence->auto_disqualified);

        $raceOneResult->refresh();
        $this->assertSame('finished', $raceOneResult->status);

        $calculator->recalculateStageCategory($stageCategory);

        $standing = $entry->stageStanding()->firstOrFail()->fresh();

        $this->assertSame(0.0, (float) $standing->championship_penalty_points);
        $this->assertFalse((bool) $standing->is_disqualified);
        $this->assertFalse((bool) $standing->discard_blocked);
        $this->assertSame($baselinePoints, (float) $standing->championship_points);
    }

    protected function buildRaceContext(): array
    {
        $user = User::factory()->create();
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
        $raceOneResult = RaceResult::query()->create([
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

        return [$user, $stageCategory, $entry, $raceOneResult];
    }
}
