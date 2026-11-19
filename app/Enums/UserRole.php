<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Auditor => 'Auditor',
        };
    }
}
