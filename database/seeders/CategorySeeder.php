<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Iniciantes 80 Kg', 'slug' => 'iniciantes-80kg', 'target_weight' => 80, 'default_pilot_limit' => 12],
            ['name' => 'Iniciantes 90 Kg', 'slug' => 'iniciantes-90kg', 'target_weight' => 90, 'default_pilot_limit' => 12],
            ['name' => 'Graduados 90 Kg', 'slug' => 'graduados-90kg', 'target_weight' => 90, 'default_pilot_limit' => 12],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'is_active' => true,
                    'description' => null,
                ],
            );
        }
    }
}
