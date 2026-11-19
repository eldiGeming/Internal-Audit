<?php

namespace App\Enums;

enum CarStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open / Dalam Proses',
            self::Closed => 'Closed / Efektif',
        };
    }
}
