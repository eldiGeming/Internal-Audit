<?php

namespace App\Enums;

enum CheckResult: string
{
    case Sesuai = 'sesuai';
    case TidakSesuai = 'tidak_sesuai';

    public function label(): string
    {
        return match ($this) {
            self::Sesuai => 'Sesuai',
            self::TidakSesuai => 'Tidak Sesuai',
        };
    }

    /**
     * Parse loose spreadsheet/JSON input such as "SESUAI", "Tidak Sesuai" or "tidak_sesuai".
     */
    public static function fromLoose(?string $value): ?self
    {
        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'TIDAK') || str_contains($normalized, 'NC')) {
            return self::TidakSesuai;
        }

        return str_contains($normalized, 'SESUAI') ? self::Sesuai : null;
    }
}
