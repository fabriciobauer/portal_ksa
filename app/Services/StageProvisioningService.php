<?php

namespace App\Services;

use App\Models\Race;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use Illuminate\Support\Facades\DB;

class StageProvisioningService
{
    public function provisionStage(Stage $stage): void
    {
        DB::transaction(function () use ($stage): void {
            $seasonCategories = $stage->season->seasonCategories()
                ->where('is_active', true)
                ->get();

            foreach ($seasonCategories as $seasonCategory) {
                $stageCategory = StageCategory::query()->firstOrCreate(
                    [
                        'stage_id' => $stage->id,
                        'season_category_id' => $seasonCategory->id,
                    ],
                    [
                        'status' => $stage->status,
                    ],
                );

                $this->ensureRaces($stageCategory);
                $this->syncStageEntries($stageCategory);
            }
        });
    }

    public function syncSeasonCategoryStages(SeasonCategory $seasonCategory): void
    {
        $stageCategories = StageCategory::query()
            ->where('season_category_id', $seasonCategory->id)
            ->whereHas('stage', fn ($query) => $query->whereIn('status', ['planned', 'open']))
            ->get();

        foreach ($stageCategories as $stageCategory) {
            $this->syncStageEntries($stageCategory);
        }
    }

    public function syncStageEntries(StageCategory $stageCategory): void
    {
        $stageCategory->loadMissing('seasonCategory.category');

        $registrations = $stageCategory->seasonCategory->registrations()
            ->where('status', SeasonCategoryRegistration::STATUS_CONFIRMED)
            ->orderByRaw("CASE WHEN registration_type = 'annual' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->with('pilot')
            ->get();
        $registrationIds = $registrations->pluck('id')->filter();
        $operationalLimit = $stageCategory->effectivePilotLimit();

        DB::transaction(function () use ($stageCategory, $registrations, $registrationIds, $operationalLimit): void {
            StageCategoryEntry::query()
                ->where('stage_category_id', $stageCategory->id)
                ->whereNotIn('season_category_registration_id', $registrationIds)
                ->whereDoesntHave('raceResults')
                ->whereDoesntHave('qualifyingResult')
                ->delete();

            $existingEntries = StageCategoryEntry::query()
                ->where('stage_category_id', $stageCategory->id)
                ->get()
                ->keyBy('pilot_id');
            $confirmedCount = $existingEntries
                ->where('confirmation_status', 'confirmed')
                ->count();

            foreach ($registrations as $registration) {
                $entry = $existingEntries->get($registration->pilot_id);

                if ($entry) {
                    if ((int) $entry->season_category_registration_id !== (int) $registration->id) {
                        $entry->forceFill([
                            'season_category_registration_id' => $registration->id,
                        ])->save();
                    }

                    continue;
                }

                $shouldAutoConfirm = $confirmedCount < $operationalLimit;
                $entry = StageCategoryEntry::query()->create([
                    'stage_category_id' => $stageCategory->id,
                    'pilot_id' => $registration->pilot_id,
                    'season_category_registration_id' => $registration->id,
                    'confirmation_status' => $shouldAutoConfirm ? 'confirmed' : 'waiting',
                    'attendance_status' => 'pending',
                    'briefing_status' => 'pending',
                ]);

                $existingEntries->put($entry->pilot_id, $entry);

                if ($shouldAutoConfirm) {
                    $confirmedCount++;
                }
            }

        });
    }

    public function ensureRaces(StageCategory $stageCategory): void
    {
        foreach ([1 => 'Corrida 1', 2 => 'Corrida 2'] as $number => $name) {
            Race::query()->firstOrCreate(
                [
                    'stage_category_id' => $stageCategory->id,
                    'number' => $number,
                ],
                [
                    'name' => $name,
                ],
            );
        }
    }
}
