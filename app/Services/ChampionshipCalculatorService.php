<?php

namespace App\Services;

use App\Models\ChampionshipPoint;
use App\Models\ChampionshipStanding;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Models\StageStanding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChampionshipCalculatorService
{
    public function __construct(protected SettingService $settingService)
    {
    }

    public function recalculateStage(Stage $stage): void
    {
        $stage->loadMissing('stageCategories');

        foreach ($stage->stageCategories as $stageCategory) {
            $this->recalculateStageCategory($stageCategory);
        }
    }

    public function recalculateSeason(Season $season): void
    {
        $season->loadMissing('seasonCategories');

        foreach ($season->seasonCategories as $seasonCategory) {
            $this->recalculateSeasonCategory($seasonCategory);
        }
    }

    public function recalculateStageCategory(StageCategory $stageCategory): void
    {
        DB::transaction(function () use ($stageCategory): void {
            $stageCategory->loadMissing([
                'stage',
                'seasonCategory.category',
                'entries.pilot',
                'entries.qualifyingResult.kartChanges',
                'entries.stageStanding',
                'races.results',
            ]);

            $pointsTable = $this->settingService->pointsTable();
            $raceOne = $this->ensureRace($stageCategory, 1, 'Corrida 1');
            $raceTwo = $this->ensureRace($stageCategory, 2, 'Corrida 2');

            $raceOne->load('results');
            $raceTwo->load('results');

            $raceOneResults = $raceOne->results->keyBy('stage_category_entry_id');
            $raceTwoResults = $raceTwo->results->keyBy('stage_category_entry_id');
            $standings = collect();

            foreach ($stageCategory->entries as $entry) {
                $raceOneResult = $raceOneResults->get($entry->id);
                $raceTwoResult = $raceTwoResults->get($entry->id);

                $raceOnePoints = $this->calculateBatteryPoints($raceOneResult, $pointsTable);
                $raceTwoPoints = $this->calculateBatteryPoints($raceTwoResult, $pointsTable);

                if ($raceOneResult && (float) $raceOneResult->points_awarded !== $raceOnePoints) {
                    $raceOneResult->forceFill(['points_awarded' => $raceOnePoints])->save();
                }

                if ($raceTwoResult && (float) $raceTwoResult->points_awarded !== $raceTwoPoints) {
                    $raceTwoResult->forceFill(['points_awarded' => $raceTwoPoints])->save();
                }

                $existing = $entry->stageStanding ?: new StageStanding([
                    'stage_category_entry_id' => $entry->id,
                ]);

                $completionBonus = $this->calculateCompletionBonus($raceOneResult, $raceTwoResult);
                $grossStagePoints = $raceOnePoints + $raceTwoPoints;
                $championshipPenalty = 0.0;
                [$isDisqualified, $disqualificationReason] = $this->summarizeDisqualification($raceOneResult, $raceTwoResult);
                $manualAdjustment = (float) ($existing->manual_adjustment_points ?? 0);
                $manualOverride = $existing->manual_override_points !== null ? (float) $existing->manual_override_points : null;
                $effectiveChampionshipPoints = $manualOverride ?? ($grossStagePoints + $completionBonus + $championshipPenalty + $manualAdjustment);

                $existing->forceFill([
                    'race1_points' => $raceOnePoints,
                    'race2_points' => $raceTwoPoints,
                    'completion_bonus' => $completionBonus,
                    'gross_stage_points' => $grossStagePoints,
                    'championship_penalty_points' => $championshipPenalty,
                    'manual_adjustment_points' => $manualAdjustment,
                    'championship_points' => $effectiveChampionshipPoints,
                    'is_technical_tie' => false,
                    'is_disqualified' => $isDisqualified,
                    'disqualification_reason' => $disqualificationReason,
                    'discard_blocked' => $isDisqualified,
                    'last_recalculated_at' => now(),
                ])->save();

                $standings->push($existing->fresh(['stageCategoryEntry.pilot']));
            }

            $this->applyStagePositions($standings);

            foreach ($standings as $standing) {
                $this->syncChampionshipPoint($standing->fresh([
                    'stageCategoryEntry.stageCategory.stage',
                    'stageCategoryEntry.pilot',
                ]));
            }

            $this->recalculateSeasonCategory($stageCategory->seasonCategory);
        });
    }

    public function recalculateSeasonCategory(SeasonCategory $seasonCategory): void
    {
        DB::transaction(function () use ($seasonCategory): void {
            $seasonCategory->loadMissing('category');

            $pointsByPilot = ChampionshipPoint::query()
                ->where('season_category_id', $seasonCategory->id)
                ->with(['stage', 'pilot'])
                ->get()
                ->groupBy('pilot_id');

            $discardCount = $this->settingService->discardCount();
            $standings = collect();

            foreach ($pointsByPilot as $pilotId => $points) {
                $discardedIds = $this->applyDiscardRule($points, $discardCount);

                ChampionshipPoint::query()
                    ->whereKey($points->pluck('id'))
                    ->update(['is_discarded' => false]);

                if ($discardedIds->isNotEmpty()) {
                    ChampionshipPoint::query()
                        ->whereKey($discardedIds)
                        ->update(['is_discarded' => true]);
                }

                $points = ChampionshipPoint::query()
                    ->whereKey($points->pluck('id'))
                    ->with(['stage', 'pilot'])
                    ->get();

                $gross = (float) $points->sum(fn (ChampionshipPoint $point) => (float) $point->gross_points + (float) $point->adjustment_points);
                $discarded = (float) $points->where('is_discarded', true)->sum('valid_points');
                $valid = (float) $points->where('is_discarded', false)->sum('valid_points');
                $counters = $this->buildTiebreakCounters($seasonCategory, (int) $pilotId, $points);

                $standing = ChampionshipStanding::query()->updateOrCreate(
                    [
                        'season_category_id' => $seasonCategory->id,
                        'pilot_id' => $pilotId,
                    ],
                    [
                        'total_gross_points' => $gross,
                        'discarded_points' => $discarded,
                        'total_valid_points' => $valid,
                        'tiebreak_counters' => $counters,
                        'last_recalculated_at' => now(),
                    ],
                );

                $standings->push($standing->fresh(['pilot']));
            }

            ChampionshipStanding::query()
                ->where('season_category_id', $seasonCategory->id)
                ->whereNotIn('pilot_id', $pointsByPilot->keys())
                ->delete();

            $ordered = $standings->sort(function (ChampionshipStanding $left, ChampionshipStanding $right) {
                if ((float) $left->total_valid_points !== (float) $right->total_valid_points) {
                    return (float) $right->total_valid_points <=> (float) $left->total_valid_points;
                }

                return $this->compareTiebreakCounters($left->tiebreak_counters ?? [], $right->tiebreak_counters ?? []);
            })->values();

            $currentPosition = 0;
            $displayIndex = 0;
            $previousSignature = null;

            foreach ($ordered as $standing) {
                $displayIndex++;
                $signature = sprintf(
                    '%s|%s',
                    number_format((float) $standing->total_valid_points, 2, '.', ''),
                    json_encode($standing->tiebreak_counters ?? []),
                );

                if ($signature !== $previousSignature) {
                    $currentPosition = $displayIndex;
                }

                $standing->forceFill([
                    'final_position' => $currentPosition,
                    'promotion_eligible' => $this->determinePromotionEligibility($seasonCategory, $standing->pilot_id, $currentPosition),
                ])->save();

                $previousSignature = $signature;
            }
        });
    }

    protected function ensureRace(StageCategory $stageCategory, int $number, string $name): Race
    {
        return Race::query()->firstOrCreate(
            [
                'stage_category_id' => $stageCategory->id,
                'number' => $number,
            ],
            [
                'name' => $name,
            ],
        );
    }

    protected function calculateBatteryPoints(?RaceResult $result, array $pointsTable): float
    {
        if (! $result || $result->status !== 'finished' || ! $result->finish_position) {
            return 0;
        }

        return (float) ($pointsTable[(string) $result->finish_position] ?? 0);
    }

    protected function calculateCompletionBonus(?RaceResult $raceOne, ?RaceResult $raceTwo): float
    {
        return $raceOne?->status === 'finished' && $raceTwo?->status === 'finished' ? 1 : 0;
    }

    protected function summarizeDisqualification(?RaceResult $raceOne, ?RaceResult $raceTwo): array
    {
        if ($raceOne?->status === 'dsq' || $raceTwo?->status === 'dsq') {
            return [true, 'Desclassificação em bateria.'];
        }

        return [false, null];
    }

    protected function applyStagePositions(Collection $standings): void
    {
        $ordered = $standings->sort(function (StageStanding $left, StageStanding $right) {
            if ((float) $left->gross_stage_points !== (float) $right->gross_stage_points) {
                return (float) $right->gross_stage_points <=> (float) $left->gross_stage_points;
            }

            if ((float) $left->race1_points !== (float) $right->race1_points) {
                return (float) $right->race1_points <=> (float) $left->race1_points;
            }

            return strcmp(
                $left->stageCategoryEntry->pilot->name,
                $right->stageCategoryEntry->pilot->name,
            );
        })->values();

        $currentPosition = 0;
        $displayIndex = 0;
        $groups = $ordered->groupBy(fn (StageStanding $standing) => $standing->gross_stage_points.'|'.$standing->race1_points);

        foreach ($groups as $group) {
            if (
                $group->count() > 1
                && $group->every(fn (StageStanding $standing) => $standing->tie_breaker_resolved_manually && $standing->stage_position)
            ) {
                foreach ($group->sortBy('stage_position') as $standing) {
                    $standing->forceFill([
                        'is_technical_tie' => false,
                    ])->save();
                }

                $displayIndex += $group->count();
                $currentPosition = $displayIndex;

                continue;
            }

            $displayIndex++;
            $currentPosition = $displayIndex;

            foreach ($group as $offset => $standing) {
                $standing->forceFill([
                    'stage_position' => $currentPosition,
                    'is_technical_tie' => $group->count() > 1,
                ])->save();
            }

            $displayIndex += $group->count() - 1;
        }
    }

    protected function syncChampionshipPoint(StageStanding $standing): void
    {
        $entry = $standing->stageCategoryEntry;
        $stageCategory = $entry->stageCategory;
        $effective = (float) $standing->effective_championship_points;
        $gross = (float) $standing->gross_stage_points;

        ChampionshipPoint::query()->updateOrCreate(
            [
                'season_category_id' => $stageCategory->season_category_id,
                'stage_id' => $stageCategory->stage_id,
                'pilot_id' => $entry->pilot_id,
            ],
            [
                'stage_standing_id' => $standing->id,
                'gross_points' => $gross,
                'adjustment_points' => $effective - $gross,
                'valid_points' => $effective,
                'discard_blocked' => $standing->discard_blocked,
                'notes' => $standing->is_disqualified ? $standing->disqualification_reason : null,
            ],
        );
    }

    protected function applyDiscardRule(Collection $points, int $discardCount): Collection
    {
        if ($discardCount <= 0) {
            return collect();
        }

        return $points
            ->where('discard_blocked', false)
            ->sort(function (ChampionshipPoint $left, ChampionshipPoint $right) {
                if ((float) $left->valid_points !== (float) $right->valid_points) {
                    return (float) $left->valid_points <=> (float) $right->valid_points;
                }

                return $left->stage->stage_number <=> $right->stage->stage_number;
            })
            ->take($discardCount)
            ->pluck('id');
    }

    protected function buildTiebreakCounters(SeasonCategory $seasonCategory, int $pilotId, Collection $points): array
    {
        $scope = $this->settingService->get('standings.championship_tiebreak_scope', 'all_heats');
        $discardedStageIds = $scope === 'valid_heats_only'
            ? $points->where('is_discarded', true)->pluck('stage_id')
            : collect();

        $results = RaceResult::query()
            ->whereHas('stageCategoryEntry', function ($query) use ($seasonCategory, $pilotId) {
                $query
                    ->where('pilot_id', $pilotId)
                    ->whereHas('stageCategory', fn ($stageCategoryQuery) => $stageCategoryQuery->where('season_category_id', $seasonCategory->id));
            })
            ->where('status', 'finished')
            ->whereNotNull('finish_position')
            ->when(
                $discardedStageIds->isNotEmpty(),
                fn ($query) => $query->whereHas('race.stageCategory.stage', fn ($stageQuery) => $stageQuery->whereNotIn('id', $discardedStageIds))
            )
            ->get();

        return $results
            ->groupBy(fn (RaceResult $result) => (string) $result->finish_position)
            ->map(fn (Collection $group) => $group->count())
            ->sortKeys()
            ->toArray();
    }

    protected function compareTiebreakCounters(array $left, array $right): int
    {
        foreach (range(1, 30) as $position) {
            $leftCount = (int) ($left[(string) $position] ?? 0);
            $rightCount = (int) ($right[(string) $position] ?? 0);

            if ($leftCount !== $rightCount) {
                return $rightCount <=> $leftCount;
            }
        }

        return 0;
    }

    protected function determinePromotionEligibility(SeasonCategory $seasonCategory, int $pilotId, int $position): bool
    {
        $categorySlug = Str::lower($seasonCategory->category->slug ?? $seasonCategory->category->name ?? '');
        $isBeginnerCategory = Str::contains($categorySlug, 'iniciante');

        $seasonsCount = SeasonCategoryRegistration::query()
            ->where('pilot_id', $pilotId)
            ->where('status', SeasonCategoryRegistration::STATUS_CONFIRMED)
            ->with('seasonCategory')
            ->get()
            ->pluck('seasonCategory.season_id')
            ->filter()
            ->unique()
            ->count();

        return ($isBeginnerCategory && in_array($position, [1, 2], true)) || $seasonsCount >= 3;
    }
}
