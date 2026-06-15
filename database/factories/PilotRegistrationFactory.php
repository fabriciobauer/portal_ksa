<?php

namespace Database\Factories;

use App\Models\Pilot;
use App\Models\PilotRegistration;
use App\Models\PilotRegistrationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PilotRegistrationFactory extends Factory
{
    protected $model = PilotRegistration::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'whatsapp' => fake()->numerify('119########'),
            'cpf' => fake()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'has_kart_experience' => fake()->boolean(),
            'has_championship_experience' => fake()->boolean(),
            'weight_kg' => fake()->randomFloat(2, 55, 110),
            'age' => fake()->numberBetween(16, 55),
            'pilot_registration_category_id' => PilotRegistrationCategory::factory(),
            'payment_status' => false,
            'paid_at' => null,
            'converted_to_pilot_at' => null,
            'pilot_id' => null,
            'metadata' => ['source' => 'website'],
            'notes' => null,
            'is_archived' => false,
            'archived_at' => null,
            'created_by' => null,
            'updated_by' => null,
            'payment_marked_by' => null,
            'payment_unmarked_by' => null,
            'archived_by' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => true,
            'paid_at' => now(),
            'pilot_id' => Pilot::factory(),
            'converted_to_pilot_at' => now(),
        ]);
    }
}
