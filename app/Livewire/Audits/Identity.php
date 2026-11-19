<?php

namespace App\Livewire\Audits;

use App\Models\AuditDepartment;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Step 1: audit identity, client logo and the department / auditee / auditor schedule.
 */
#[Title('Identitas Audit')]
class Identity extends AuditStep
{
    use WithFileUploads;

    public string $companyName = '';

    public string $auditNumber = '';

    public string $auditDate = '';

    public string $objective = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    /**
     * Schedule rows keyed by department id.
     *
     * @var array<int, array{name: string, auditee: string, auditor: string}>
     */
    public array $departments = [];

    protected function mountStep(): void
    {
        $this->companyName = $this->audit->company->name;
        $this->auditNumber = (string) $this->audit->audit_number;
        $this->auditDate = $this->audit->audit_date?->format('Y-m-d') ?? '';
        $this->objective = (string) $this->audit->objective;

        $this->departments = $this->orderedDepartments()
            ->mapWithKeys(fn (AuditDepartment $department): array => [$department->id => [
                'name' => $department->name,
                'auditee' => (string) $department->auditee,
                'auditor' => (string) $department->auditor,
            ]])
            ->all();
    }

    public function updatedCompanyName(): void
    {
        $this->authorizeEdit();
        $this->validateOnly('companyName', ['companyName' => ['required', 'string', 'max:255']], attributes: ['companyName' => 'nama client/PT']);

        $this->audit->moveToCompany($this->companyName);
        $this->audit->refresh();
        $this->touchAudit();
    }

    public function updatedAuditNumber(): void
    {
        $this->saveAuditField('auditNumber', 'audit_number', ['nullable', 'string', 'max:100'], 'no. audit');
    }

    public function updatedAuditDate(): void
    {
        $this->saveAuditField('auditDate', 'audit_date', ['nullable', 'date'], 'tanggal audit');
    }

    public function updatedObjective(): void
    {
        $this->saveAuditField('objective', 'objective', ['nullable', 'string', 'max:5000'], 'tujuan audit');
    }

    /**
     * @param  list<mixed>  $rules
     */
    private function saveAuditField(string $property, string $column, array $rules, string $label): void
    {
        $this->authorizeEdit();
        $this->validateOnly($property, [$property => $rules], attributes: [$property => $label]);

        $value = trim((string) $this->{$property});
        $this->audit->update([$column => $value === '' ? null : $value]);
        $this->touchAudit();
    }

    public function updatedLogo(): void
    {
        $this->authorizeEdit();
        $this->validate(
            ['logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048']],
            attributes: ['logo' => 'logo'],
        );

        /** @var Company $company */
        $company = $this->audit->company;
        $company->deleteLogoFile();
        $company->update(['logo_path' => $this->logo->store('logos', 'public')]);

        $this->logo = null;
        $this->touchAudit();
        $this->toast('Logo client diperbarui.');
    }

    public function removeLogo(): void
    {
        $this->authorizeEdit();

        $company = $this->audit->company;
        $company->deleteLogoFile();
        $company->update(['logo_path' => null]);
        $this->touchAudit();
        $this->toast('Logo client dihapus.', 'info');
    }

    /**
     * Livewire hook: persists one schedule cell ("departments.{id}.{field}").
     */
    public function updatedDepartments(mixed $value, string $key): void
    {
        $this->authorizeEdit();

        [$id, $field] = array_pad(explode('.', $key, 2), 2, null);

        if (! in_array($field, ['name', 'auditee', 'auditor'], true)) {
            return;
        }

        $department = $this->findDepartment((int) $id);

        Validator::make(
            ['departments' => [$department->id => [$field => $value]]],
            ["departments.{$department->id}.{$field}" => ['nullable', 'string', 'max:255']],
            attributes: ["departments.{$department->id}.{$field}" => $field === 'name' ? 'departemen' : $field],
        )->validate();

        $value = trim((string) $value);
        $department->update([$field => $field === 'name' ? $value : ($value === '' ? null : $value)]);
        $this->touchAudit();
    }

    public function addDepartment(): void
    {
        $this->authorizeEdit();

        $department = $this->audit->departments()->create([
            'position' => (int) AuditDepartment::query()->whereBelongsTo($this->audit)->max('position') + 1,
            'name' => '',
            'auditor' => auth()->user()->name,
            'criteria' => [''],
        ]);

        $this->departments[$department->id] = ['name' => '', 'auditee' => '', 'auditor' => (string) $department->auditor];
        $this->touchAudit();
    }

    public function removeDepartment(int $departmentId): void
    {
        $this->authorizeEdit();

        $department = $this->findDepartment($departmentId);
        $name = $department->displayName();
        $department->delete();

        unset($this->departments[$departmentId]);
        $this->touchAudit();
        $this->toast("Departemen \"{$name}\" dihapus.", 'info');
    }

    /**
     * @return Collection<int, AuditDepartment>
     */
    private function orderedDepartments()
    {
        return AuditDepartment::query()
            ->whereBelongsTo($this->audit)
            ->withCount('items')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.audits.identity', [
            'departmentRows' => $this->orderedDepartments(),
            'companySuggestions' => Company::suggestionsFor(auth()->user()),
        ]);
    }
}
