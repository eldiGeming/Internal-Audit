<?php

namespace App\Livewire\Audits;

use App\Enums\CheckResult;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Shared behaviour of the six audit step pages.
 *
 * Every lookup by id goes through findDepartment()/findItem(), which are scoped to $this->audit,
 * so a tampered id can never reach another auditor's data.
 */
abstract class AuditStep extends Component
{
    public Audit $audit;

    public function mount(Audit $audit): void
    {
        $this->authorize('view', $audit);

        $this->audit = $audit;

        $this->mountStep();
    }

    protected function mountStep(): void {}

    protected function authorizeEdit(): void
    {
        $this->authorize('update', $this->audit);
    }

    /**
     * Badge numbers for the step navigation.
     *
     * @return array{items: int, departments: int, findings: int}
     */
    #[Computed]
    public function stepCounts(): array
    {
        $filled = fn (): Builder => $this->itemsQuery()->whereNotNull('question')->where('question', '<>', '');

        return [
            'items' => $filled()->count(),
            'departments' => AuditDepartment::query()->whereBelongsTo($this->audit)->count(),
            'findings' => $filled()->where('result', CheckResult::TidakSesuai)->count(),
        ];
    }

    /**
     * @return Builder<ChecklistItem>
     */
    protected function itemsQuery(): Builder
    {
        return ChecklistItem::query()->whereIn(
            'audit_department_id',
            AuditDepartment::query()->whereBelongsTo($this->audit)->select('id'),
        );
    }

    protected function findDepartment(int $id): AuditDepartment
    {
        return AuditDepartment::query()->whereBelongsTo($this->audit)->findOrFail($id);
    }

    protected function findItem(int $id): ChecklistItem
    {
        return $this->itemsQuery()->findOrFail($id);
    }

    /**
     * Marks the audit as recently edited (drives "terakhir diubah" on the audit list).
     */
    protected function touchAudit(): void
    {
        $this->audit->touch();
        unset($this->stepCounts);
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }
}
