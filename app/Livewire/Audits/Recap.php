<?php

namespace App\Livewire\Audits;

use App\Models\AuditDepartment;
use App\Models\RecapEntry;
use App\Support\AuditReport;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Attributes\Title;

/**
 * Step 5: recap of all departments (automatic) plus manually entered rows ("New Input").
 */
#[Title('Rekap Audit')]
class Recap extends AuditStep
{
    /**
     * Custom "Kesimpulan / Catatan" per department; empty means the automatic note is used.
     *
     * @var array<int, string>
     */
    public array $notes = [];

    /**
     * Manual recap rows keyed by id.
     *
     * @var array<int, array<string, string|int>>
     */
    public array $manual = [];

    private const MANUAL_TEXT = ['department', 'auditee', 'auditor', 'note'];

    private const MANUAL_NUMBERS = ['total', 'sesuai', 'major', 'minor', 'observasi'];

    protected function mountStep(): void
    {
        $this->notes = AuditDepartment::query()->whereBelongsTo($this->audit)->pluck('recap_note', 'id')
            ->map(fn (?string $note): string => (string) $note)
            ->all();

        $this->manual = $this->audit->recapEntries()->get()
            ->mapWithKeys(fn (RecapEntry $entry): array => [$entry->id => $this->manualState($entry)])
            ->all();
    }

    /**
     * @return array<string, string|int>
     */
    private function manualState(RecapEntry $entry): array
    {
        return $entry->only([...self::MANUAL_TEXT, ...self::MANUAL_NUMBERS]);
    }

    public function updatedNotes(mixed $value, string $key): void
    {
        $this->authorizeEdit();

        $department = $this->findDepartment((int) $key);

        Validator::make(['notes' => [$department->id => $value]], ["notes.{$department->id}" => ['nullable', 'string', 'max:1000']], attributes: ["notes.{$department->id}" => 'catatan'])->validate();

        $value = trim((string) $value);
        $department->update(['recap_note' => $value === '' ? null : $value]);
        $this->touchAudit();
    }

    public function useAutomaticNote(int $departmentId): void
    {
        $this->authorizeEdit();

        $this->findDepartment($departmentId)->update(['recap_note' => null]);
        $this->notes[$departmentId] = '';
        $this->touchAudit();
    }

    public function addManualEntry(): void
    {
        $this->authorizeEdit();

        $entry = $this->audit->recapEntries()->create(['department' => 'Departemen tambahan']);
        $this->manual[$entry->id] = $this->manualState($entry->refresh());
        $this->touchAudit();
    }

    public function updatedManual(mixed $value, string $key): void
    {
        $this->authorizeEdit();

        [$id, $field] = array_pad(explode('.', $key, 2), 2, null);
        $isNumber = in_array($field, self::MANUAL_NUMBERS, true);

        if (! $isNumber && ! in_array($field, self::MANUAL_TEXT, true)) {
            return;
        }

        $entry = RecapEntry::query()->whereBelongsTo($this->audit)->findOrFail((int) $id);

        $rule = match (true) {
            $isNumber => ['required', 'integer', 'min:0', 'max:100000'],
            $field === 'note' => ['nullable', 'string', 'max:1000'],
            default => ['nullable', 'string', 'max:255'],
        };

        Validator::make(['manual' => [$entry->id => [$field => $value]]], ["manual.{$entry->id}.{$field}" => $rule], attributes: ["manual.{$entry->id}.{$field}" => $field === 'department' ? 'departemen' : $field])->validate();

        $entry->update([$field => $isNumber ? (int) $value : trim((string) $value)]);
        $this->manual[$entry->id] = $this->manualState($entry);
        $this->touchAudit();
    }

    public function removeManualEntry(int $entryId): void
    {
        $this->authorizeEdit();

        RecapEntry::query()->whereBelongsTo($this->audit)->findOrFail($entryId)->delete();
        unset($this->manual[$entryId]);
        $this->touchAudit();
    }

    public function render(): View
    {
        $report = new AuditReport($this->audit);

        return view('livewire.audits.recap', [
            'rows' => $report->rows(),
            'totals' => $report->totals(),
        ]);
    }
}
