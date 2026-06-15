<?php

namespace Tests\Feature;

use App\Models\PilotRegistrationCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPilotRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_form_saves_new_unpaid_registration(): void
    {
        $category = PilotRegistrationCategory::factory()->create([
            'name' => 'Rookie',
            'slug' => 'rookie',
        ]);

        $response = $this->post(route('public.registrations.store'), [
            'full_name' => '  João da Silva  ',
            'whatsapp' => '(11) 98765-4321',
            'cpf' => '123.456.789-01',
            'email' => 'JOAO@EXAMPLE.COM ',
            'address' => ' Rua A, 123 ',
            'has_kart_experience' => '1',
            'has_championship_experience' => '0',
            'weight_kg' => '82,50',
            'age' => '29',
            'pilot_registration_category_id' => $category->id,
        ]);

        $response->assertRedirect(route('public.registrations.create'));

        $this->assertDatabaseHas('pilot_registrations', [
            'full_name' => 'João da Silva',
            'whatsapp' => '11987654321',
            'cpf' => '12345678901',
            'email' => 'joao@example.com',
            'address' => 'Rua A, 123',
            'payment_status' => false,
            'pilot_registration_category_id' => $category->id,
        ]);
    }

    public function test_public_form_shows_only_active_categories(): void
    {
        $activeCategory = PilotRegistrationCategory::factory()->create([
            'name' => 'F4',
            'slug' => 'f4',
            'is_active' => true,
        ]);
        $inactiveCategory = PilotRegistrationCategory::factory()->create([
            'name' => 'Graduados',
            'slug' => 'graduados',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.registrations.create'));

        $response->assertOk();
        $response->assertSee($activeCategory->name);
        $response->assertDontSee($inactiveCategory->name);
    }

    public function test_public_form_validates_required_fields(): void
    {
        $response = $this->post(route('public.registrations.store'), []);

        $response->assertSessionHasErrors([
            'full_name',
            'whatsapp',
            'cpf',
            'email',
            'address',
            'has_kart_experience',
            'has_championship_experience',
            'weight_kg',
            'age',
            'pilot_registration_category_id',
        ]);
    }
}
