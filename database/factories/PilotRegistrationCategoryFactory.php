<?php

namespace Database\Factories;

use App\Models\PilotRegistrationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PilotRegistrationCategoryFactory extends Factory
{
    protected $model = PilotRegistrationCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
            'notes' => null,
        ];
    }
}
