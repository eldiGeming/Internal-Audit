<?php

namespace App\Livewire\Audits;

use App\Enums\CarStatus;
use App\Models\CorrectiveAction;
use App\Support\AuditReport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;

/**
 * Step 4: one CAR sheet per finding (sections A to E), exported together as one PDF document.
 */
#[Title('CAR')]
class CorrectiveActions extends AuditStep
{
    /**
     * CAR form values keyed by checklist item id.
     *
     * @var array<int, array<string, string>>
     */
    public array $cars = [];

    protected function mountStep(): void
    {
        foreach ((new AuditReport($this->audit))->findings() as $finding) {
            $action = $finding['action'];
            $auditee = (string) $finding['department']->auditee;

            $this->cars[$finding['item']->id] = [
                'root_cause' => (string) $action?->root_cause,
                'correction' => (string) $action?->correction,
                'correction_pic' => $action ? (string) $action->correction_pic : $auditee,
                'correction_due_date' => $action?->correction_due_date?->format('Y-m-d') ?? '',
                'prevention' => (string) $action?->prevention,
                'prevention_pic' => $action ? (string) $action->prevention_pic : $auditee,
                'prevention_due_date' => $action?->prevention_due_date?->format('Y-m-d') ?? '',
                'verification_note' => (string) $action?->verification_note,
                'status' => $action?->status->value ?? CarStatus::Open->value,
            ];
        }
    }

    /**
     * Livewire hook for "cars.{itemId}.{field}". Saves the whole CAR so default PICs are persisted too.
     */
    public function updatedCars(mixed $value, string $key): void
    {
        $this->authorizeEdit();

        [$id, $field] = array_pad(explode('.', $key, 2), 2, null);

        if (! in_array($field, CorrectiveAction::EDITABLE_FIELDS, true)) {
            return;
        }

        $item = $this->findItem((int) $id);
        abort_unless($item->isFinding(), 404);

        $rules = [
            'root_cause' => ['nullable', 'string', 'max:5000'],
            'correction' => ['nullable', 'string', 'max:5000'],
            'correction_pic' => ['nullable', 'string', 'max:255'],
            'correction_due_date' => ['nullable', 'date'],
            'prevention' => ['nullable', 'string', 'max:5000'],
            'prevention_pic' => ['nullable', 'string', 'max:255'],
            'prevention_due_date' => ['nullable', 'date'],
            'verification_note' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(CarStatus::class)],
        ];

        // Validate the whole sheet so a previously rejected value can never be saved alongside another field.
        $prefixed = [];
        $labels = [];

        foreach ($rules as $name => $rule) {
            $prefixed["cars.{$item->id}.{$name}"] = $rule;
            $labels["cars.{$item->id}.{$name}"] = match ($name) {
                'correction_due_date', 'prevention_due_date' => 'target tanggal selesai',
                'correction_pic', 'prevention_pic' => 'PIC',
                default => str_replace('_', ' ', $name),
            };
        }

        Validator::make(['cars' => [$item->id => $this->cars[$item->id] ?? []]], $prefixed, attributes: $labels)->validate();

        $values = [];

        foreach (CorrectiveAction::EDITABLE_FIELDS as $editable) {
            $raw = trim((string) ($this->cars[$item->id][$editable] ?? ''));
            $values[$editable] = $raw === '' ? null : $raw;
        }

        $values['status'] ??= CarStatus::Open->value;

        CorrectiveAction::query()->updateOrCreate(['checklist_item_id' => $item->id], $values);
        $this->touchAudit();
    }

    public function render(): View
    {
        return view('livewire.audits.corrective-actions', [
            'findings' => (new AuditReport($this->audit))->findings(),
        ]);
    }
}
