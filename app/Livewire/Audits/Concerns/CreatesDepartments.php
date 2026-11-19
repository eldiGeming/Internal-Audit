<?php

namespace App\Livewire\Audits\Concerns;

use App\Livewire\Audits\AuditStep;
use App\Models\AuditDepartment;

/**
 * "New Input / Departemen baru" modal, shared by the Checklist and Daftar Periksa pages.
 *
 * @mixin AuditStep
 */
trait CreatesDepartments
{
    public bool $showDepartmentModal = false;

    public string $newDepartmentName = '';

    public string $newDepartmentAuditee = '';

    public string $newDepartmentAuditor = '';

    public function openDepartmentModal(): void
    {
        $this->resetValidation();
        $this->newDepartmentName = '';
        $this->newDepartmentAuditee = '';
        $this->newDepartmentAuditor = auth()->user()->name;
        $this->showDepartmentModal = true;
    }

    public function createDepartment(): void
    {
        $this->authorizeEdit();

        $validated = $this->validate([
            'newDepartmentName' => ['required', 'string', 'max:255'],
            'newDepartmentAuditee' => ['nullable', 'string', 'max:255'],
            'newDepartmentAuditor' => ['nullable', 'string', 'max:255'],
        ], attributes: [
            'newDepartmentName' => 'nama departemen',
            'newDepartmentAuditee' => 'auditee',
            'newDepartmentAuditor' => 'auditor',
        ]);

        $department = $this->audit->departments()->create([
            'position' => (int) AuditDepartment::query()->whereBelongsTo($this->audit)->max('position') + 1,
            'name' => trim($validated['newDepartmentName']),
            'auditee' => $validated['newDepartmentAuditee'] ?: null,
            'auditor' => $validated['newDepartmentAuditor'] ?: null,
            'criteria' => [''],
        ]);

        $this->showDepartmentModal = false;
        $this->touchAudit();
        $this->selectDepartment($department->id);
        $this->toast("Departemen \"{$department->name}\" ditambahkan.");
    }

    abstract public function selectDepartment(int $departmentId): void;
}
