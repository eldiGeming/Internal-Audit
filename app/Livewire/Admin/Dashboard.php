<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin dashboard: number of companies (PT), users, CARs, and the best audited company.
 * A company is ranked by the compliance of its most recent audit that has checked items.
 */
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $audits = Audit::query()
            ->with(['company', 'user'])
            ->withResultCounts()
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->get();

        /** @var Collection<int, Audit> $latestPerCompany */
        $latestPerCompany = $audits
            ->filter(fn (Audit $audit): bool => $audit->items_total > 0)
            ->groupBy('company_id')
            ->map(fn (Collection $companyAudits): Audit => $companyAudits->first())
            ->sortByDesc(fn (Audit $audit): array => [$audit->compliance(), $audit->items_total])
            ->values();

        return view('livewire.admin.dashboard', [
            'companiesCount' => $audits->pluck('company_id')->unique()->count(),
            'auditorsCount' => User::query()->where('role', UserRole::Auditor)->count(),
            'activeAuditorsCount' => User::query()->where('role', UserRole::Auditor)->where('is_active', true)->count(),
            'auditsCount' => $audits->count(),
            'findingsCount' => $audits->sum('items_findings'),
            'averageCompliance' => $latestPerCompany->isEmpty() ? null : (int) round($latestPerCompany->avg(fn (Audit $audit): int => $audit->compliance())),
            'ranking' => $latestPerCompany->take(8),
            'best' => $latestPerCompany->first(),
            'recentAudits' => $audits->sortByDesc('updated_at')->take(6),
        ]);
    }
}
