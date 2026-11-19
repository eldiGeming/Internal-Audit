<?php

namespace App\Livewire\Audits\Concerns;

use App\Livewire\Audits\AuditStep;
use App\Models\AuditDepartment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

/**
 * The department currently open on the Checklist / Daftar Periksa page, plus its editable metadata.
 * The selection lives in the URL (?dept=ID) so both pages open the same department.
 *
 * @mixin AuditStep
 */
trait ManagesSelectedDepartment
{
    #[Url(as: 'dept', except: null)]
    public ?int $departmentId = null;

    public string $departmentName = '';

    public string $auditee = '';

    public string $auditor = '';

    /** @var list<string> */
    public array $criteria = [];

    protected function initDepartment(): void
    {
        $query = AuditDepartment::query()->whereBelongsTo($this->audit);

        $id = $this->departmentId !== null && (clone $query)->whereKey($this->departmentId)->exists()
            ? $this->departmentId
            : $query->orderBy('position')->orderBy('id')->value('id');

        if ($id !== null) {
            $this->selectDepartment((int) $id);
        } else {
            $this->departmentId = null;
        }
    }

    public function selectDepartment(int $departmentId): void
    {
        $department = $this->findDepartment($departmentId);

        $this->departmentId = $department->id;
        $this->departmentName = $department->name;
        $this->auditee = (string) $department->auditee;
        $this->auditor = (string) $department->auditor;
        $this->criteria = array_values(array_map('strval', $department->criteria ?: ['']));

        $this->loadItems();
        $this->resetValidation();
    }

    public function updatedDepartmentId(mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $this->selectDepartment((int) $value);
        }
    }

    protected function selectedDepartment(): ?AuditDepartment
    {
        return $this->departmentId === null
            ? null
            : AuditDepartment::query()->whereBelongsTo($this->audit)->with('items.correctiveAction')->find($this->departmentId);
    }

    public function updatedDepartmentName(): void
    {
        $this->saveDepartmentField('departmentName', 'name', 'nama departemen', required: true);
    }

    public function updatedAuditee(): void
    {
        $this->saveDepartmentField('auditee', 'auditee', 'auditee');
    }

    public function updatedAuditor(): void
    {
        $this->saveDepartmentField('auditor', 'auditor', 'auditor');
    }

    private function saveDepartmentField(string $property, string $column, string $label, bool $required = false): void
    {
        $this->authorizeEdit();
        $this->validateOnly($property, [$property => [$required ? 'required' : 'nullable', 'string', 'max:255']], attributes: [$property => $label]);

        $value = trim((string) $this->{$property});
        $this->findDepartment((int) $this->departmentId)->update([$column => $value === '' && ! $required ? null : $value]);
        $this->touchAudit();
    }

    /**
     * Livewire hook for "criteria.{index}" inputs. Blank entries are kept so the input row stays visible.
     */
    public function updatedCriteria(): void
    {
        $this->authorizeEdit();
        $this->validate(['criteria' => ['array', 'max:20'], 'criteria.*' => ['nullable', 'string', 'max:255']], attributes: ['criteria.*' => 'criteria']);

        $this->saveCriteria();
    }

    public function addCriterion(): void
    {
        $this->authorizeEdit();

        if (count($this->criteria) < 20) {
            $this->criteria[] = '';
            $this->saveCriteria();
        }
    }

    public function removeCriterion(int $index): void
    {
        $this->authorizeEdit();

        unset($this->criteria[$index]);
        $this->criteria = array_values($this->criteria) ?: [''];
        $this->saveCriteria();
    }

    private function saveCriteria(): void
    {
        $this->criteria = array_values(array_map(fn ($value): string => trim((string) $value), $this->criteria));
        $this->findDepartment((int) $this->departmentId)->update(['criteria' => $this->criteria]);
        $this->touchAudit();
    }

    /**
     * Departments for the selector, in display order.
     *
     * @return Collection<int, AuditDepartment>
     */
    protected function departmentOptions()
    {
        return AuditDepartment::query()->whereBelongsTo($this->audit)->orderBy('position')->orderBy('id')->get(['id', 'name', 'position']);
    }
}
