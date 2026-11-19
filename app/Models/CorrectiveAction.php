<?php

namespace App\Models;

use App\Enums\CarStatus;
use Database\Factories\CorrectiveActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'checklist_item_id',
    'root_cause',
    'correction',
    'correction_pic',
    'correction_due_date',
    'prevention',
    'prevention_pic',
    'prevention_due_date',
    'verification_note',
    'status',
])]
class CorrectiveAction extends Model
{
    /** @use HasFactory<CorrectiveActionFactory> */
    use HasFactory;

    /**
     * Fields an auditor fills in on a CAR sheet (sections B to E).
     */
    public const EDITABLE_FIELDS = [
        'root_cause',
        'correction',
        'correction_pic',
        'correction_due_date',
        'prevention',
        'prevention_pic',
        'prevention_due_date',
        'verification_note',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'correction_due_date' => 'date',
            'prevention_due_date' => 'date',
            'status' => CarStatus::class,
        ];
    }

    /**
     * The earliest target date set for the correction or the prevention, if any.
     */
    public function nextDueDate(): ?Carbon
    {
        return collect([$this->correction_due_date, $this->prevention_due_date])->filter()->min();
    }

    /**
     * @return BelongsTo<ChecklistItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }
}
