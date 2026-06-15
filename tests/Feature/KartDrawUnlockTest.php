<?php

namespace Tests\Feature;

use App\Models\KartDrawBatch;
use App\Models\StageCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KartDrawUnlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_unlock_latest_locked_draw_batch(): void
    {
        $user = User::factory()->create();
        $stageCategory = StageCategory::factory()->create();
        $batch = KartDrawBatch::query()->create([
            'stage_category_id' => $stageCategory->id,
            'session_key' => 'qualifying',
            'sequence' => 1,
            'status' => 'locked',
            'range_start' => 1,
            'range_end' => 3,
            'drawn_at' => now(),
            'locked_at' => now(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('kart-draws.unlock', $batch));

        $response->assertRedirect();

        $batch->refresh();

        $this->assertSame('draft', $batch->status);
        $this->assertNull($batch->locked_at);
    }

    public function test_admin_cannot_unlock_non_latest_draw_batch(): void
    {
        $user = User::factory()->create();
        $stageCategory = StageCategory::factory()->create();
        $oldBatch = KartDrawBatch::query()->create([
            'stage_category_id' => $stageCategory->id,
            'session_key' => 'qualifying',
            'sequence' => 1,
            'status' => 'locked',
            'range_start' => 1,
            'range_end' => 3,
            'drawn_at' => now()->subMinute(),
            'locked_at' => now()->subMinute(),
            'created_by' => $user->id,
        ]);
        KartDrawBatch::query()->create([
            'stage_category_id' => $stageCategory->id,
            'session_key' => 'qualifying',
            'sequence' => 2,
            'status' => 'draft',
            'range_start' => 1,
            'range_end' => 3,
            'drawn_at' => now(),
            'created_by' => $user->id,
        ]);

        $response = $this->from(route('kart-draws.show', $stageCategory))
            ->actingAs($user)
            ->post(route('kart-draws.unlock', $oldBatch));

        $response->assertRedirect(route('kart-draws.show', $stageCategory));
        $response->assertSessionHasErrors('batch');

        $oldBatch->refresh();

        $this->assertSame('locked', $oldBatch->status);
        $this->assertNotNull($oldBatch->locked_at);
    }
}
