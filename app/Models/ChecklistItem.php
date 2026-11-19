<?php

namespace App\Models;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use Database\Factories\ChecklistItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['audit_department_id', 'position', 'question', 'criteria', 'evidence', 'result', 'severity'])]
class ChecklistItem extends Model
{
    /** @use HasFactory<ChecklistItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'result' => CheckResult::class,
            'severity' => FindingSeverity::class,
        ];
    }

    /**
     * @return BelongsTo<AuditDepartment, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(AuditDepartment::class, 'audit_department_id');
    }

    /**
     * @return HasOne<CorrectiveAction, $this>
     */
    public function correctiveAction(): HasOne
    {
        return $this->hasOne(CorrectiveAction::class);
    }

    public function hasQuestion(): bool
    {
        return trim((string) $this->question) !== '';
    }

    /**
     * A finding (non-conformity) is a filled-in question marked "Tidak Sesuai"; each one gets a CAR.
     */
    public function isFinding(): bool
    {
        return $this->hasQuestion() && $this->result === CheckResult::TidakSesuai;
    }

    /**
     * Severity of a finding, defaulting to Major like the reference prototype.
     */
    public function findingSeverity(): FindingSeverity
    {
        return $this->severity ?? FindingSeverity::Major;
    }

    /**
     * Item-level criteria override, falling back to the department criteria.
     */
    public function effectiveCriteria(AuditDepartment $department): string
    {
        $own = trim((string) $this->criteria);

        return $own !== '' ? $own : $department->criteriaText();
    }
}
