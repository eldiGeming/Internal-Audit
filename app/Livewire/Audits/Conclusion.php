<?php

namespace App\Livewire\Audits;

use App\Support\AuditReport;
use Illuminate\View\View;
use Livewire\Attributes\Title;

/**
 * Step 6 (replaces the old "Arsip" menu): recap table, every CAR filled in so far, and the final statement.
 */
#[Title('Kesimpulan Audit')]
class Conclusion extends AuditStep
{
    public string $finalConclusion = '';

    protected function mountStep(): void
    {
        $this->finalConclusion = (string) $this->audit->final_conclusion;
    }

    public function updatedFinalConclusion(): void
    {
        $this->authorizeEdit();
        $this->validateOnly('finalConclusion', ['finalConclusion' => ['nullable', 'string', 'max:10000']], attributes: ['finalConclusion' => 'kesimpulan akhir']);

        $value = trim($this->finalConclusion);
        $this->audit->update(['final_conclusion' => $value === '' ? null : $value]);
        $this->touchAudit();
    }

    public function render(): View
    {
        $report = new AuditReport($this->audit);

        return view('livewire.audits.conclusion', [
            'rows' => $report->rows(),
            'totals' => $report->totals(),
            'findings' => $report->findings(),
            'criteria' => $report->criteriaSummary(),
        ]);
    }
}
