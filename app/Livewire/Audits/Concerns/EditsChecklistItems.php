<?php

namespace App\Livewire\Audits\Concerns;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Livewire\Audits\AuditStep;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Inline editing of checklist items for the selected department (autosaved field by field).
 * Shared by the Checklist (step 2) and Daftar Periksa (step 3) pages, which edit the same data.
 *
 * @mixin AuditStep
 */
trait EditsChecklistItems
{
    /**
     * Editable item values keyed by item id. Display order comes from the database, not from this array.
     *
     * @var array<int, array{question: string, criteria: string, evidence: string, result: string, severity: string}>
     */
    public array $items = [];

    abstract protected function selectedDepartment(): ?AuditDepartment;

    protected function loadItems(): void
    {
        $this->items = ($this->selectedDepartment()?->items ?? collect())
            ->mapWithKeys(fn (ChecklistItem $item): array => [$item->id => $this->itemState($item)])
            ->all();
    }

    /**
     * @return array{question: string, criteria: string, evidence: string, result: string, severity: string}
     */
    protected function itemState(ChecklistItem $item): array
    {
        return [
            'question' => (string) $item->question,
            'criteria' => (string) $item->criteria,
            'evidence' => (string) $item->evidence,
            'result' => $item->result?->value ?? '',
            'severity' => $item->severity?->value ?? '',
        ];
    }

    /**
     * Livewire hook: persists one field whenever an "items.{id}.{field}" binding changes.
     */
    public function updatedItems(mixed $value, string $key): void
    {
        $this->authorizeEdit();

        [$id, $field] = array_pad(explode('.', $key, 2), 2, null);

        $rules = [
            'question' => ['nullable', 'string', 'max:2000'],
            'criteria' => ['nullable', 'string', 'max:255'],
            'evidence' => ['nullable', 'string', 'max:2000'],
            'result' => ['nullable', Rule::enum(CheckResult::class)],
            'severity' => ['nullable', Rule::enum(FindingSeverity::class)],
        ];

        if (! isset($rules[$field])) {
            return;
        }

        $item = $this->findItem((int) $id);

        $labels = [
            'question' => 'pertanyaan',
            'criteria' => 'criteria',
            'evidence' => 'catatan / bukti objektif',
            'result' => 'hasil pemeriksaan',
            'severity' => 'kategori temuan',
        ];

        Validator::make(
            ['items' => [$item->id => [$field => $value]]],
            ["items.{$item->id}.{$field}" => $rules[$field]],
            attributes: ["items.{$item->id}.{$field}" => $labels[$field]],
        )->validate();

        $value = is_string($value) ? trim($value) : $value;
        $item->{$field} = $value === '' ? null : $value;

        if ($field === 'result') {
            $item->severity = $item->result === CheckResult::TidakSesuai
                ? ($item->severity ?? FindingSeverity::Major)
                : null;
        }

        $item->save();
        $this->items[$item->id] = $this->itemState($item);
        $this->touchAudit();
    }

    public function addItems(int $count = 1): void
    {
        $this->authorizeEdit();

        $department = $this->selectedDepartment();

        if ($department === null) {
            return;
        }

        $count = max(1, min($count, 20));
        $position = (int) $department->items()->max('position');

        for ($i = 1; $i <= $count; $i++) {
            $item = $department->items()->create(['position' => $position + $i]);
            $this->items[$item->id] = $this->itemState($item);
        }

        $this->touchAudit();
    }

    public function removeItem(int $itemId): void
    {
        $this->authorizeEdit();

        $this->findItem($itemId)->delete();
        unset($this->items[$itemId]);
        $this->touchAudit();
    }

    /**
     * "Rapikan kosong": removes rows with no question, no evidence and no result.
     */
    public function clearEmptyItems(): void
    {
        $this->authorizeEdit();

        $department = $this->selectedDepartment();

        if ($department === null) {
            return;
        }

        $removed = $department->items()
            ->where(fn ($q) => $q->whereNull('question')->orWhere('question', ''))
            ->where(fn ($q) => $q->whereNull('evidence')->orWhere('evidence', ''))
            ->whereNull('result')
            ->delete();

        $this->loadItems();
        $this->touchAudit();
        $this->toast($removed > 0 ? "{$removed} baris kosong dihapus." : 'Tidak ada baris kosong.', 'info');
    }
}
