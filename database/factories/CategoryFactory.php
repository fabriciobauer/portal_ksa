<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'target_weight' => fake()->randomElement([80, 90]),
            'description' => fake()->sentence(),
            'is_active' => true,
            'default_pilot_limit' => 12,
            'notes' => null,
        ];
    }
}
