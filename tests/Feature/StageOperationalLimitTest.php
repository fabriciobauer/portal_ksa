<?php

namespace Tests\Feature;

use App\Models\Pilot;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageOperationalLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_cannot_confirm_more_pilots_than_operational_limit(): void
    {
        $user = User::factory()->create();
        $season = Season::factory()->create();
        $seasonCategory = SeasonCategory::factory()->create([
            'season_id' => $season->id,
            'pilot_limit' => 12,
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

        $registrations = collect();

        foreach (Pilot::factory()->count(13)->create() as $pilot) {
            $registrations->push(SeasonCategoryRegistration::query()->create([
                'season_category_id' => $seasonCategory->id,
                'pilot_id' => $pilot->id,
                'registration_type' => SeasonCategoryRegistration::TYPE_ANNUAL,
                'status' => SeasonCategoryRegistration::STATUS_CONFIRMED,
                'registered_at' => now()->toDateString(),
                'confirmed_at' => now(),
            ]));
        }

        foreach ($registrations->take(12) as $registration) {
            StageCategoryEntry::query()->create([
                'stage_category_id' => $stageCategory->id,
                'pilot_id' => $registration->pilot_id,
                'season_category_registration_id' => $registration->id,
                'confirmation_status' => 'confirmed',
                'attendance_status' => 'pending',
                'briefing_status' => 'pending',
            ]);
        }

        $thirteenthRegistration = $registrations->last();

        StageCategoryEntry::query()->create([
            'stage_category_id' => $stageCategory->id,
            'pilot_id' => $thirteenthRegistration->pilot_id,
            'season_category_registration_id' => $thirteenthRegistration->id,
            'confirmation_status' => 'waiting',
            'attendance_status' => 'pending',
            'briefing_status' => 'pending',
        ]);

        $response = $this->from(route('stage-management.show', $stageCategory))
            ->actingAs($user)
            ->post(route('stage-management.entries.store', $stageCategory), [
                'pilot_id' => $thirteenthRegistration->pilot_id,
                'season_category_registration_id' => $thirteenthRegistration->id,
                'confirmation_status' => 'confirmed',
                'attendance_status' => 'pending',
                'briefing_status' => 'pending',
                'briefing_penalty_grid_positions' => 0,
            ]);

        $response->assertRedirect(route('stage-management.show', $stageCategory));
        $response->assertSessionHasErrors('confirmation_status');

        $this->assertSame(12, $stageCategory->entries()->where('confirmation_status', 'confirmed')->count());
        $this->assertTrue(StageCategoryEntry::query()
            ->where('stage_category_id', $stageCategory->id)
            ->where('pilot_id', $thirteenthRegistration->pilot_id)
            ->where('confirmation_status', 'waiting')
            ->exists());
    }
}
