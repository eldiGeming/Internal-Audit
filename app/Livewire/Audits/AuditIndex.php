<?php

namespace App\Livewire\Audits;

use App\Models\Audit;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Daftar Audit')]
class AuditIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showCreateModal = false;

    public string $companyName = '';

    public string $auditNumber = '';

    public string $auditDate = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset('companyName', 'auditNumber');
        $this->auditDate = now()->format('Y-m-d');
        $this->showCreateModal = true;
    }

    public function createAudit(): void
    {
        $this->authorize('create', Audit::class);

        $validated = $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'auditNumber' => ['nullable', 'string', 'max:100'],
            'auditDate' => ['nullable', 'date'],
        ], attributes: [
            'companyName' => 'nama client/PT',
            'auditNumber' => 'no. audit',
            'auditDate' => 'tanggal audit',
        ]);

        $audit = Audit::companyFor($validated['companyName'])->audits()->create([
            'user_id' => auth()->id(),
            'audit_number' => $validated['auditNumber'] ?: null,
            'audit_date' => $validated['auditDate'] ?: null,
        ]);

        session()->flash('toast', ['message' => 'Audit baru dibuat. Lengkapi identitas audit.', 'type' => 'success']);

        $this->redirectRoute('audits.identity', $audit, navigate: true);
    }

    public function deleteAudit(int $auditId): void
    {
        $audit = Audit::query()->visibleTo(auth()->user())->findOrFail($auditId);
        $this->authorize('delete', $audit);

        $company = $audit->company;
        $label = $company->name.' ('.$audit->displayTitle().')';
        $audit->delete();

        if ($company->audits()->doesntExist()) {
            $company->delete();
        }

        $this->dispatch('toast', message: "Audit {$label} dihapus.", type: 'info');
    }

    public function render(): View
    {
        $user = auth()->user();

        $audits = Audit::query()
            ->visibleTo($user)
            ->with(['company', 'user'])
            ->withCount('departments')
            ->withResultCounts()
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.$this->search.'%';
                $query->where(fn (Builder $q) => $q
                    ->where('audit_number', 'like', $term)
                    ->orWhereHas('company', fn (Builder $c) => $c->where('name', 'like', $term)));
            })
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.audits.index', [
            'audits' => $audits,
            'isAdmin' => $user->isAdmin(),
            'companySuggestions' => Company::suggestionsFor(auth()->user()),
        ]);
    }
}
