<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::STAFF => 'Staff',
        };
    }
}
