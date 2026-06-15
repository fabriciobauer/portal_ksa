<?php

namespace Tests\Feature;

use App\Models\PilotRegistrationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PilotRegistrationCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_registration_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.registration-categories.store'), [
            'name' => 'Rookie Cup',
            'sort_order' => 1,
            'is_active' => 1,
            'notes' => 'Categoria de entrada',
        ]);

        $response->assertRedirect(route('admin.registration-categories.index'));

        $this->assertDatabaseHas('pilot_registration_categories', [
            'name' => 'Rookie Cup',
            'slug' => 'rookie-cup',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_deactivate_registration_category(): void
    {
        $user = User::factory()->create();
        $category = PilotRegistrationCategory::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('admin.registration-categories.status', $category), [
            'is_active' => 0,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pilot_registration_categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }
}
