<?php

namespace Database\Factories;

use App\Models\Pilot;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonCategoryRegistrationFactory extends Factory
{
    protected $model = SeasonCategoryRegistration::class;

    public function definition(): array
    {
        return [
            'season_category_id' => SeasonCategory::factory(),
            'pilot_id' => Pilot::factory(),
            'registration_type' => SeasonCategoryRegistration::TYPE_ANNUAL,
            'status' => SeasonCategoryRegistration::STATUS_CONFIRMED,
            'registered_at' => now()->toDateString(),
            'confirmed_at' => now(),
            'waitlist_order' => null,
            'notes' => null,
        ];
    }
}
