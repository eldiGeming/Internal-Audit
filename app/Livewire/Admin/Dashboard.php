<?php

namespace App\Livewire\Admin;

use App\Enums\CarProgress;
use App\Enums\FindingSeverity;
use App\Enums\UserRole;
use App\Models\Audit;
use App\Models\User;
use App\Support\AuditReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin dashboard: headline figures, CAR follow-up (status, overdue, upcoming targets), findings per
 * category, company compliance ranking, audits per month, auditor workload and recent audits.
 * A company is ranked by the compliance of its most recent audit that has checked items.
 */
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $today = Carbon::today();

        $audits = Audit::query()
            ->with(['company', 'user', 'departments.items.correctiveAction', 'recapEntries'])
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

        $findings = $this->findings($audits, $today);
        $auditors = User::query()->where('role', UserRole::Auditor)->orderBy('name')->get();
        $closedCount = $findings->where('progress', CarProgress::Closed)->count();

        return view('livewire.admin.dashboard', [
            'today' => $today,
            'companiesCount' => $audits->pluck('company_id')->unique()->count(),
            'auditsCount' => $audits->count(),
            'auditorsCount' => $auditors->count(),
            'activeAuditorsCount' => $auditors->where('is_active', true)->count(),
            'averageCompliance' => $latestPerCompany->isEmpty() ? null : (int) round($latestPerCompany->avg(fn (Audit $audit): int => $audit->compliance())),
            'findingsCount' => $findings->count(),
            'openCount' => $findings->count() - $closedCount,
            'overdueCount' => $findings->where('progress', CarProgress::Overdue)->count(),
            'closedCount' => $closedCount,
            'progress' => collect(CarProgress::cases())->map(fn (CarProgress $state): array => [
                'state' => $state,
                'count' => $findings->where('progress', $state)->count(),
            ]),
            'severities' => collect(FindingSeverity::cases())->map(fn (FindingSeverity $severity): array => [
                'severity' => $severity,
                'count' => $findings->where('severity', $severity)->count(),
            ]),
            'followUps' => $findings
                ->filter(fn (array $finding): bool => $finding['progress'] !== CarProgress::Closed && $finding['due'] !== null)
                ->sortBy(fn (array $finding): int => $finding['due']->getTimestamp())
                ->take(6)
                ->values(),
            'ranking' => $latestPerCompany->take(8),
            'best' => $latestPerCompany->first(),
            'auditsPerMonth' => $this->auditsPerMonth($audits, $today),
            'workload' => $this->workload($auditors, $audits, $findings),
            'recentAudits' => $audits->sortByDesc('updated_at')->take(6),
        ]);
    }

    /**
     * Every CAR across all audits, numbered per audit as in AuditReport, with its follow-up state.
     *
     * @param  Collection<int, Audit>  $audits
     * @return Collection<int, array<string, mixed>>
     */
    private function findings(Collection $audits, Carbon $today): Collection
    {
        return $audits->flatMap(fn (Audit $audit): Collection => (new AuditReport($audit))->findings()
            ->map(fn (array $finding): array => [
                ...$finding,
                'audit' => $audit,
                'progress' => CarProgress::of($finding['action'], $today),
                'due' => $finding['action']?->nextDueDate(),
            ]));
    }

    /**
     * Audits per month (by audit date) for the last six months, oldest first.
     *
     * @param  Collection<int, Audit>  $audits
     * @return Collection<int, array{month: Carbon, count: int}>
     */
    private function auditsPerMonth(Collection $audits, Carbon $today): Collection
    {
        return collect(range(5, 0))->map(function (int $monthsBack) use ($audits, $today): array {
            $month = $today->copy()->startOfMonth()->subMonthsNoOverflow($monthsBack);

            return [
                'month' => $month,
                'count' => $audits->filter(fn (Audit $audit): bool => $audit->audit_date?->isSameMonth($month) ?? false)->count(),
            ];
        });
    }

    /**
     * Per auditor: audits owned, open and overdue CARs, last time one of their audits changed.
     *
     * @param  Collection<int, User>  $auditors
     * @param  Collection<int, Audit>  $audits
     * @param  Collection<int, array<string, mixed>>  $findings
     * @return Collection<int, array<string, mixed>>
     */
    private function workload(Collection $auditors, Collection $audits, Collection $findings): Collection
    {
        return $auditors
            ->map(function (User $auditor) use ($audits, $findings): array {
                $owned = $audits->where('user_id', $auditor->id);
                $theirFindings = $findings->filter(fn (array $finding): bool => $finding['audit']->user_id === $auditor->id);

                return [
                    'user' => $auditor,
                    'audits' => $owned->count(),
                    'open' => $theirFindings->where('progress', '!==', CarProgress::Closed)->count(),
                    'overdue' => $theirFindings->where('progress', CarProgress::Overdue)->count(),
                    'lastActivity' => $owned->max('updated_at'),
                ];
            })
            ->sortByDesc(fn (array $row): array => [$row['overdue'], $row['open'], $row['audits']])
            ->values();
    }
}
