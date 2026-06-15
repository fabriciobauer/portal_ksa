<?php

namespace App\Services;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GridService
{
    public function generateRaceOneGrid(StageCategory $stageCategory, bool $persist = true): Collection
    {
        $stageCategory->loadMissing([
            'entries.pilot',
            'entries.qualifyingResult.kartChanges',
            'drawBatches.draws',
            'races.results',
        ]);

        $baseOrder = $stageCategory->entries
            ->sort(function (StageCategoryEntry $left, StageCategoryEntry $right) {
                $leftResult = $left->qualifyingResult;
                $rightResult = $right->qualifyingResult;

                return $this->compareQualifying($leftResult?->status, $leftResult?->lap_time_ms, $rightResult?->status, $rightResult?->lap_time_ms);
            })
            ->values();

        $adjusted = $this->moveKartSwapsToBack($baseOrder);

        $grid = $adjusted->values()->map(function (StageCategoryEntry $entry, int $index) {
            $kartNumber = $entry->qualifyingResult?->current_kart_number
                ?? $entry->qualifyingResult?->initial_kart_number
                ?? $entry->kartDraws()->latest('id')->value('kart_number');

            return [
                'entry' => $entry,
                'grid_position' => $index + 1,
                'kart_number' => $kartNumber,
                'origin' => 'Tomada de tempo',
            ];
        });

        if ($persist) {
            DB::transaction(function () use ($stageCategory, $grid): void {
                $race = $this->ensureRace($stageCategory, 1, 'Corrida 1');

                foreach ($grid as $row) {
                    $result = $row['entry']->qualifyingResult;

                    if ($result) {
                        $result->forceFill([
                            'auto_grid_position' => $row['grid_position'],
                            'final_grid_position' => $result->final_grid_position ?: $row['grid_position'],
                        ])->save();
                    }

                    RaceResult::query()->updateOrCreate(
                        [
                            'race_id' => $race->id,
                            'stage_category_entry_id' => $row['entry']->id,
                        ],
                        [
                            'grid_position' => $result?->final_grid_position ?: $row['grid_position'],
                            'kart_number' => $row['kart_number'],
                            'status' => 'finished',
                        ],
                    );
                }
            });
        }

        return $grid;
    }

    public function generateRaceTwoGrid(StageCategory $stageCategory, bool $persist = true): Collection
    {
        $raceOne = $this->ensureRace($stageCategory, 1, 'Corrida 1');
        $raceTwo = $this->ensureRace($stageCategory, 2, 'Corrida 2');

        $raceOne->loadMissing('results.stageCategoryEntry.pilot');

        $orderedResults = $raceOne->results
            ->sortBy([
                ['finish_position', 'asc'],
                ['grid_position', 'asc'],
            ])
            ->values();

        $reversedPilots = $orderedResults->reverse()->values();

        $grid = $orderedResults->values()->map(function (RaceResult $originalRow, int $index) use ($reversedPilots) {
            $pilotRow = $reversedPilots[$index];

            return [
                'grid_position' => $index + 1,
                'entry' => $pilotRow->stageCategoryEntry,
                'kart_number' => $originalRow->kart_number,
                'origin' => sprintf('Invertido da Corrida 1 (pos. final %d)', $pilotRow->finish_position ?? $pilotRow->grid_position),
            ];
        });

        if ($persist) {
            DB::transaction(function () use ($raceTwo, $grid): void {
                foreach ($grid as $row) {
                    RaceResult::query()->updateOrCreate(
                        [
                            'race_id' => $raceTwo->id,
                            'stage_category_entry_id' => $row['entry']->id,
                        ],
                        [
                            'grid_position' => $row['grid_position'],
                            'kart_number' => $row['kart_number'],
                            'status' => 'finished',
                        ],
                    );
                }

                $raceTwo->forceFill([
                    'grid_generated_from' => 'race_one_reverse',
                ])->save();
            });
        }

        return $grid;
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

    protected function compareQualifying(?string $leftStatus, ?int $leftTime, ?string $rightStatus, ?int $rightTime): int
    {
        $rank = [
            'valid' => 0,
            'no_time' => 1,
            'absent' => 2,
            'dsq' => 3,
            null => 4,
        ];

        $leftRank = $rank[$leftStatus] ?? 4;
        $rightRank = $rank[$rightStatus] ?? 4;

        if ($leftRank !== $rightRank) {
            return $leftRank <=> $rightRank;
        }

        return ($leftTime ?? PHP_INT_MAX) <=> ($rightTime ?? PHP_INT_MAX);
    }

    protected function moveKartSwapsToBack(Collection $entries): Collection
    {
        $swapEntries = $entries
            ->filter(fn (StageCategoryEntry $entry) => $entry->qualifyingResult && $entry->qualifyingResult->kartChanges->isNotEmpty())
            ->sortBy(fn (StageCategoryEntry $entry) => optional($entry->qualifyingResult->kartChanges)->min('happened_at'))
            ->values();

        $remaining = $entries
            ->reject(fn (StageCategoryEntry $entry) => $swapEntries->contains('id', $entry->id))
            ->values();

        return $remaining->concat($swapEntries)->values();
    }
}
