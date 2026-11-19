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
     * Ordinal weight drawn as filled bars in the severity marker (3 = most severe).
     */
    public function level(): int
    {
        return match ($this) {
            self::Major => 3,
            self::Minor => 2,
            self::Observasi => 1,
        };
    }

    /**
     * Text color for the severity marker. All pass WCAG AA on white.
     */
    public function toneClass(): string
    {
        return match ($this) {
            self::Major => 'text-red-700',
            self::Minor => 'text-orange-700',
            self::Observasi => 'text-amber-700',
        };
    }

    /**
     * Solid bar fill for charts: an ordinal ramp, darkest for the most severe, every step at least 3:1
     * against white (checked with the dataviz palette validator: amber-600, orange-700, red-800).
     */
    public function barClass(): string
    {
        return match ($this) {
            self::Major => 'bg-red-800',
            self::Minor => 'bg-orange-700',
            self::Observasi => 'bg-amber-600',
        };
    }

    /**
     * Parse loose spreadsheet input such as "1. Major" or "observasi".
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
