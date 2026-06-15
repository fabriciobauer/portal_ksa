<?php

namespace App\Services;

use App\Models\PointAdjustment;
use App\Models\StageStanding;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointAdjustmentService
{
    public function __construct(protected ChampionshipCalculatorService $calculator)
    {
    }

    public function applyAdjustment(StageStanding $standing, float $delta, string $reason, User $user): void
    {
        DB::transaction(function () use ($standing, $delta, $reason, $user): void {
            $previous = (float) $standing->manual_adjustment_points;
            $standing->forceFill([
                'manual_adjustment_points' => $previous + $delta,
            ])->save();

            PointAdjustment::query()->create([
                'stage_standing_id' => $standing->id,
                'type' => 'manual_adjustment',
                'delta' => $delta,
                'previous_value' => $previous,
                'new_value' => (float) $standing->manual_adjustment_points,
                'reason' => $reason,
                'user_id' => $user->id,
            ]);

            $this->calculator->recalculateStageCategory($standing->stageCategoryEntry->stageCategory);
        });
    }

    public function applyOverride(StageStanding $standing, float $value, string $reason, User $user): void
    {
        DB::transaction(function () use ($standing, $value, $reason, $user): void {
            $previous = $standing->manual_override_points;

            $standing->forceFill([
                'manual_override_points' => $value,
                'override_reason' => $reason,
                'override_user_id' => $user->id,
                'override_at' => now(),
            ])->save();

            PointAdjustment::query()->create([
                'stage_standing_id' => $standing->id,
                'type' => 'manual_override',
                'previous_value' => $previous,
                'new_value' => $value,
                'reason' => $reason,
                'user_id' => $user->id,
            ]);

            $this->calculator->recalculateStageCategory($standing->stageCategoryEntry->stageCategory);
        });
    }

    public function revertOverride(StageStanding $standing, string $reason, User $user): void
    {
        DB::transaction(function () use ($standing, $reason, $user): void {
            $previous = $standing->manual_override_points;

            $standing->forceFill([
                'manual_override_points' => null,
                'override_reason' => null,
                'override_user_id' => null,
                'override_at' => null,
            ])->save();

            PointAdjustment::query()->create([
                'stage_standing_id' => $standing->id,
                'type' => 'revert_override',
                'previous_value' => $previous,
                'new_value' => null,
                'reason' => $reason,
                'user_id' => $user->id,
            ]);

            $this->calculator->recalculateStageCategory($standing->stageCategoryEntry->stageCategory);
        });
    }
}
