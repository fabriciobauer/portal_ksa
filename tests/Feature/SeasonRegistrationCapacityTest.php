<?php

namespace Tests\Feature;

use App\Models\Pilot;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use App\Models\Stage;
use App\Models\StageCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonRegistrationCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_have_more_confirmed_registrations_than_operational_limit(): void
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
        $pilotIds = Pilot::factory()->count(13)->create()->pluck('id')->all();

        $response = $this->actingAs($user)->post(route('registrations.store'), [
            'season_category_id' => $seasonCategory->id,
            'pilot_ids' => $pilotIds,
            'registration_type' => SeasonCategoryRegistration::TYPE_ANNUAL,
            'status' => SeasonCategoryRegistration::STATUS_CONFIRMED,
        ]);

        $response->assertRedirect(route('registrations.index'));

        $this->assertSame(13, SeasonCategoryRegistration::query()
            ->where('season_category_id', $seasonCategory->id)
            ->where('status', SeasonCategoryRegistration::STATUS_CONFIRMED)
            ->count());

        $this->assertSame(13, $stageCategory->entries()->count());
        $this->assertSame(12, $stageCategory->entries()->where('confirmation_status', 'confirmed')->count());
        $this->assertSame(1, $stageCategory->entries()->where('confirmation_status', 'waiting')->count());
    }
}
