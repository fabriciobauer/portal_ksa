<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@ksa.local'],
            [
                'name' => 'Administrador KSA',
                'password' => Hash::make('admin123'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@ksa.local'],
            [
                'name' => 'Staff KSA',
                'password' => Hash::make('staff123'),
                'role' => UserRole::STAFF,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
