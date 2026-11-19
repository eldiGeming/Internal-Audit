<?php

namespace App\Livewire\Audits;

use App\Livewire\Audits\Concerns\CreatesDepartments;
use App\Livewire\Audits\Concerns\EditsChecklistItems;
use App\Livewire\Audits\Concerns\ManagesSelectedDepartment;
use App\Support\AuditReport;
use Illuminate\View\View;
use Livewire\Attributes\Title;

/**
 * Step 3: the formal inspection sheet of one department ("1 Dept = 1 File" PDF).
 * Edits the same items as step 2, with a per-item criteria column.
 */
#[Title('Daftar Periksa')]
class Inspection extends AuditStep
{
    use CreatesDepartments, EditsChecklistItems, ManagesSelectedDepartment;

    protected function mountStep(): void
    {
        $this->initDepartment();
    }

    public function render(): View
    {
        $department = $this->selectedDepartment();

        return view('livewire.audits.inspection', [
            'department' => $department,
            'departmentOptions' => $this->departmentOptions(),
            'stats' => $department ? AuditReport::countItems($department->items) : null,
        ]);
    }
}
