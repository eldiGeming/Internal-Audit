<?php

namespace App\Enums;

enum FindingSeverity: string
{
    case Major = 'major';
    case Minor = 'minor';
    case Observasi = 'observasi';

    public function label(): string
    {
        return match ($this) {
            self::Major => 'Major',
            self::Minor => 'Minor',
            self::Observasi => 'Observasi',
        };
    }

    /**
     * Tailwind classes for the severity badge (light surface).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Major => 'bg-red-50 text-red-800 ring-red-200',
            self::Minor => 'bg-orange-50 text-orange-800 ring-orange-200',
            self::Observasi => 'bg-amber-50 text-amber-800 ring-amber-200',
        };
    }

    /**
     * Parse loose spreadsheet/JSON input such as "1. Major" or "observasi".
     */
    public static function fromLoose(?string $value): ?self
    {
        $normalized = strtolower(trim((string) $value));

        foreach (self::cases() as $case) {
            if ($normalized !== '' && str_contains($normalized, $case->value)) {
                return $case;
            }
        }

        return str_contains($normalized, 'obs') ? self::Observasi : null;
    }
}
