<?php

namespace App\Livewire\Admin;

use App\Enums\CheckResult;
use App\Models\Audit;
use App\Models\ChecklistItem;
use App\Models\Company;
use App\Support\AuditReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin register of every CAR, grouped per company (PT), with read and delete.
 * Deleting a CAR follows the reference prototype: the CAR is removed and the finding is marked "Sesuai".
 */
#[Title('Dokumen CAR per PT')]
class CorrectiveActionRegister extends Component
{
    #[Url(as: 'pt', except: null)]
    public ?int $companyId = null;

    public bool $showDetail = false;

    public ?int $viewingItemId = null;

    public function view(int $itemId): void
    {
        $this->viewingItemId = $itemId;
        $this->showDetail = true;
    }

    public function deleteCorrectiveAction(int $itemId): void
    {
        $item = ChecklistItem::query()->with(['department.audit', 'correctiveAction'])->findOrFail($itemId);
        $this->authorize('deleteCorrectiveAction', $item->department->audit);

        $item->correctiveAction?->delete();
        $item->update(['result' => CheckResult::Sesuai, 'severity' => null]);
        $item->department->audit->touch();

        $this->showDetail = false;
        $this->dispatch('toast', message: 'CAR dihapus dan temuan ditandai Sesuai.', type: 'info');
    }

    public function render(): View
    {
        $audits = Audit::query()
            ->with('company')
            ->when($this->companyId, fn (Builder $query) => $query->where('company_id', $this->companyId))
            ->whereHas('items', fn (Builder $query) => $query
                ->where('result', CheckResult::TidakSesuai)
                ->whereNotNull('question')->where('question', '<>', ''))
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->get();

        $groups = $audits
            ->map(fn (Audit $audit): array => ['audit' => $audit, 'findings' => (new AuditReport($audit))->findings()])
            ->groupBy(fn (array $entry): int => $entry['audit']->company_id);

        $viewing = $this->viewingItemId === null ? null : $groups->flatten(1)
            ->flatMap(fn (array $entry) => $entry['findings']->map(fn (array $finding): array => [...$finding, 'audit' => $entry['audit']]))
            ->first(fn (array $finding): bool => $finding['item']->id === $this->viewingItemId);

        return view('livewire.admin.corrective-action-register', [
            'groups' => $groups,
            'companies' => Company::query()->whereHas('audits')->orderBy('name')->get(['id', 'name']),
            'viewing' => $viewing,
        ]);
    }
}
