<?php

namespace App\Models;

use App\Enums\CheckResult;
use Database\Factories\AuditFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['company_id', 'user_id', 'audit_number', 'audit_date', 'objective', 'final_conclusion'])]
class Audit extends Model
{
    /** @use HasFactory<AuditFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'items_total' => 'integer',
            'items_sesuai' => 'integer',
            'items_findings' => 'integer',
            'departments_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The auditor account that owns this audit.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AuditDepartment, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(AuditDepartment::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @return HasManyThrough<ChecklistItem, AuditDepartment, $this>
     */
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(ChecklistItem::class, AuditDepartment::class);
    }

    /**
     * @return HasMany<RecapEntry, $this>
     */
    public function recapEntries(): HasMany
    {
        return $this->hasMany(RecapEntry::class)->orderBy('id');
    }

    /**
     * Admins see every audit, auditors only the audits they own.
     *
     * @param  Builder<Audit>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->whereBelongsTo($user);
        }
    }

    /**
     * Adds items_total, items_sesuai and items_findings counts (questions that are filled in).
     *
     * @param  Builder<Audit>  $query
     */
    #[Scope]
    protected function withResultCounts(Builder $query): void
    {
        $filled = fn (Builder $q) => $q->whereNotNull('question')->where('question', '<>', '');

        $query->withCount([
            'items as items_total' => $filled,
            'items as items_sesuai' => fn (Builder $q) => $filled($q)->where('result', CheckResult::Sesuai),
            'items as items_findings' => fn (Builder $q) => $filled($q)->where('result', CheckResult::TidakSesuai),
        ]);
    }

    /**
     * Compliance percentage based on the counts from withResultCounts().
     */
    public function compliance(): int
    {
        $total = (int) ($this->items_total ?? 0);

        return $total > 0 ? (int) round(((int) $this->items_sesuai / $total) * 100) : 0;
    }

    public function displayTitle(): string
    {
        return $this->audit_number ?: 'Audit #'.$this->id;
    }

    /**
     * Point this audit at the company with the given name.
     *
     * Renames the current company when no other audit uses it (keeps its logo), otherwise links to an
     * existing company with that name or creates a new one. Companies left without audits are removed.
     */
    public function moveToCompany(string $name): void
    {
        $name = trim($name);
        $current = $this->company;

        if ($current->name === $name) {
            return;
        }

        $existing = Company::query()->where('name', $name)->whereKeyNot($current->id)->first();
        $sharedWithOtherAudits = $current->audits()->whereKeyNot($this->id)->exists();

        if ($existing === null && ! $sharedWithOtherAudits) {
            $current->update(['name' => $name]);

            return;
        }

        $this->company()->associate($existing ?? Company::query()->create(['name' => $name]))->save();

        if (! $sharedWithOtherAudits) {
            $current->delete();
        }
    }

    /**
     * Find or create the company for a new audit by name.
     */
    public static function companyFor(string $name): Company
    {
        return Company::query()->firstOrCreate(['name' => trim($name)]);
    }
}
