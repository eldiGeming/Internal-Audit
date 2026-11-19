<?php

namespace App\Enums;

use App\Models\CorrectiveAction;
use Carbon\CarbonInterface;

/**
 * Where a CAR stands on a given day, for the admin follow-up overview.
 * Cases are listed in display order: what needs attention first.
 */
enum CarProgress: string
{
    case Overdue = 'overdue';
    case NotPlanned = 'not_planned';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    /**
     * A finding without a CAR sheet yet counts as not planned. Open CARs whose earliest target date
     * has passed are overdue; open CARs with a plan or a target date are in progress.
     */
    public static function of(?CorrectiveAction $action, CarbonInterface $today): self
    {
        if ($action?->status === CarStatus::Closed) {
            return self::Closed;
        }

        $due = $action?->nextDueDate();

        if ($due !== null && $due->lt($today->copy()->startOfDay())) {
            return self::Overdue;
        }

        return $due !== null || filled($action?->correction) || filled($action?->prevention)
            ? self::InProgress
            : self::NotPlanned;
    }

    public function label(): string
    {
        return match ($this) {
            self::Overdue => 'Lewat target',
            self::NotPlanned => 'Belum ada rencana',
            self::InProgress => 'Dalam proses',
            self::Closed => 'Selesai (efektif)',
        };
    }

    /**
     * Solid fill for chart segments and legend swatches. White text on all of them passes AA.
     * Checked with the dataviz palette validator: every adjacent pair is distinct for normal and
     * colour-blind vision; the gray is deliberately colourless ("nothing planned yet") and every
     * segment also has a named legend entry with its count.
     */
    public function fillClass(): string
    {
        return match ($this) {
            self::Overdue => 'bg-red-700',
            self::NotPlanned => 'bg-slate-500',
            self::InProgress => 'bg-blue-600',
            self::Closed => 'bg-emerald-700',
        };
    }
}
