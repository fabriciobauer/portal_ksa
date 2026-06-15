<?php

namespace Tests\Feature;

use App\Models\Pilot;
use App\Models\PilotRegistration;
use App\Models\PilotRegistrationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPilotRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_registrations(): void
    {
        $user = User::factory()->create();
        PilotRegistration::factory()->create(['full_name' => 'Piloto Um']);
        PilotRegistration::factory()->create(['full_name' => 'Piloto Dois']);

        $response = $this->actingAs($user)->get(route('admin.pilot-registrations.index'));

        $response->assertOk();
        $response->assertSee('Piloto Um');
        $response->assertSee('Piloto Dois');
    }

    public function test_admin_can_filter_paid_and_unpaid_registrations(): void
    {
        $user = User::factory()->create();
        PilotRegistration::factory()->create(['full_name' => 'Piloto Alfa', 'payment_status' => true, 'paid_at' => now()]);
        PilotRegistration::factory()->create(['full_name' => 'Piloto Beta', 'payment_status' => false]);

        $paidResponse = $this->actingAs($user)->get(route('admin.pilot-registrations.index', ['status' => 'paid']));
        $paidResponse->assertSee('Piloto Alfa');
        $paidResponse->assertDontSee('Piloto Beta');

        $unpaidResponse = $this->actingAs($user)->get(route('admin.pilot-registrations.index', ['status' => 'unpaid']));
        $unpaidResponse->assertSee('Piloto Beta');
        $unpaidResponse->assertDontSee('Piloto Alfa');
    }

    public function test_marking_registration_as_paid_updates_existing_pilot_when_match_is_found(): void
    {
        $user = User::factory()->create();
        $category = PilotRegistrationCategory::factory()->create([
            'name' => 'Rookie',
            'slug' => 'rookie',
        ]);
        $pilot = Pilot::factory()->create([
            'name' => 'João',
            'cpf' => '12345678901',
            'email' => null,
            'phone' => null,
        ]);
        $registration = PilotRegistration::factory()->create([
            'full_name' => 'João da Silva',
            'cpf' => '12345678901',
            'email' => 'joao@example.com',
            'whatsapp' => '11999998888',
            'pilot_registration_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.pilot-registrations.payment', $registration),
            ['payment_status' => 1],
        );

        $response->assertRedirect();

        $registration->refresh();
        $pilot->refresh();

        $this->assertTrue($registration->payment_status);
        $this->assertSame($pilot->id, $registration->pilot_id);
        $this->assertSame('joao@example.com', $pilot->email);
        $this->assertSame('11999998888', preg_replace('/\D+/', '', (string) $pilot->phone));
    }

    public function test_marking_registration_as_paid_twice_does_not_duplicate_pilot(): void
    {
        $user = User::factory()->create();
        $registration = PilotRegistration::factory()->create([
            'cpf' => '98765432100',
            'email' => 'piloto@example.com',
            'whatsapp' => '11912345678',
        ]);

        $this->actingAs($user)->patch(route('admin.pilot-registrations.payment', $registration), ['payment_status' => 1]);
        $this->actingAs($user)->patch(route('admin.pilot-registrations.payment', $registration), ['payment_status' => 1]);

        $registration->refresh();

        $this->assertTrue($registration->payment_status);
        $this->assertNotNull($registration->pilot_id);
        $this->assertSame(1, Pilot::query()->count());
    }

    public function test_unmarking_paid_registration_keeps_pilot_record(): void
    {
        $user = User::factory()->create();
        $registration = PilotRegistration::factory()->create([
            'cpf' => '45645645645',
            'email' => 'novo@example.com',
            'whatsapp' => '11911112222',
        ]);

        $this->actingAs($user)->patch(route('admin.pilot-registrations.payment', $registration), ['payment_status' => 1]);
        $registration->refresh();
        $pilotId = $registration->pilot_id;

        $response = $this->actingAs($user)->patch(
            route('admin.pilot-registrations.payment', $registration),
            ['payment_status' => 0],
        );

        $response->assertRedirect();

        $registration->refresh();

        $this->assertFalse($registration->payment_status);
        $this->assertSame($pilotId, $registration->pilot_id);
        $this->assertDatabaseHas('pilots', ['id' => $pilotId]);
    }
}
