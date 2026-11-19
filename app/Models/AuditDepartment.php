<?php

namespace App\Models;

use Database\Factories\AuditDepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['audit_id', 'position', 'name', 'auditee', 'auditor', 'criteria', 'recap_note'])]
class AuditDepartment extends Model
{
    /** @use HasFactory<AuditDepartmentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Audit, $this>
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * @return HasMany<ChecklistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Criteria entries that are not blank.
     *
     * @return list<string>
     */
    public function criteriaList(): array
    {
        return array_values(array_filter(
            array_map(fn ($value): string => trim((string) $value), $this->criteria ?? []),
            fn (string $value): bool => $value !== '',
        ));
    }

    public function criteriaText(): string
    {
        return implode('; ', $this->criteriaList());
    }

    public function displayName(): string
    {
        return trim($this->name) !== '' ? $this->name : 'Departemen tanpa nama';
    }
}
