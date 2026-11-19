<?php

namespace App\Support;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\CorrectiveAction;
use Illuminate\Support\Collection;

/**
 * Single source of truth for the numbers shown on the Rekap, CAR and Kesimpulan pages and their PDFs.
 *
 * Mirrors the reference prototype: compliance = Sesuai / total filled-in questions, rounded to a whole percent,
 * and CAR numbers run CAR-001, CAR-002, ... across departments in their display order.
 */
final class AuditReport
{
    public function __construct(public readonly Audit $audit)
    {
        $audit->loadMissing(['company', 'user', 'departments.items.correctiveAction', 'recapEntries']);
    }

    /**
     * @param  iterable<ChecklistItem>  $items
     * @return array{total: int, sesuai: int, major: int, minor: int, observasi: int, findings: int, compliance: int}
     */
    public static function countItems(iterable $items): array
    {
        $counts = ['total' => 0, 'sesuai' => 0, 'major' => 0, 'minor' => 0, 'observasi' => 0];

        foreach ($items as $item) {
            if (! $item->hasQuestion()) {
                continue;
            }

            $counts['total']++;

            if ($item->result === CheckResult::Sesuai) {
                $counts['sesuai']++;
            } elseif ($item->result === CheckResult::TidakSesuai) {
                $counts[$item->findingSeverity()->value]++;
            }
        }

        return self::withDerived($counts);
    }

    /**
     * @param  array{total: int, sesuai: int, major: int, minor: int, observasi: int}  $counts
     * @return array{total: int, sesuai: int, major: int, minor: int, observasi: int, findings: int, compliance: int}
     */
    private static function withDerived(array $counts): array
    {
        $counts['findings'] = $counts['major'] + $counts['minor'] + $counts['observasi'];
        $counts['compliance'] = $counts['total'] > 0 ? (int) min(100, round($counts['sesuai'] / $counts['total'] * 100)) : 0;

        return $counts;
    }

    /**
     * Default "Kesimpulan / Catatan" text for a recap row, same wording rules as the prototype.
     *
     * @param  array{total: int, major: int, minor: int, observasi: int}  $counts
     */
    public static function autoNote(array $counts): string
    {
        if ($counts['total'] === 0) {
            return 'Belum ada item pemeriksaan.';
        }

        if ($counts['major'] > 0) {
            return "Terdapat {$counts['major']} temuan Major, perlu tindakan korektif segera.";
        }

        if ($counts['minor'] + $counts['observasi'] > 0) {
            return 'Terdapat temuan Minor/Observasi.';
        }

        return 'Memenuhi kriteria audit.';
    }

    /**
     * Recap rows: one per department (automatic) followed by manual entries.
     *
     * @return Collection<int, array{key: string, is_manual: bool, id: int, department: string, auditee: string, auditor: string, total: int, sesuai: int, major: int, minor: int, observasi: int, findings: int, compliance: int, note: string, auto_note: string}>
     */
    public function rows(): Collection
    {
        $automatic = $this->audit->departments->map(function (AuditDepartment $department): array {
            $counts = self::countItems($department->items);
            $autoNote = self::autoNote($counts);

            return [
                'key' => 'dept-'.$department->id,
                'is_manual' => false,
                'id' => $department->id,
                'department' => $department->displayName(),
                'auditee' => $department->auditee ?: '-',
                'auditor' => $department->auditor ?: '-',
                ...$counts,
                'note' => trim((string) $department->recap_note) !== '' ? $department->recap_note : $autoNote,
                'auto_note' => $autoNote,
            ];
        });

        $manual = $this->audit->recapEntries->map(function ($entry): array {
            $counts = self::withDerived([
                'total' => $entry->total,
                'sesuai' => $entry->sesuai,
                'major' => $entry->major,
                'minor' => $entry->minor,
                'observasi' => $entry->observasi,
            ]);

            return [
                'key' => 'manual-'.$entry->id,
                'is_manual' => true,
                'id' => $entry->id,
                'department' => $entry->department !== '' ? $entry->department : 'Departemen tambahan',
                'auditee' => $entry->auditee ?: '-',
                'auditor' => $entry->auditor ?: '-',
                ...$counts,
                'note' => (string) $entry->note,
                'auto_note' => self::autoNote($counts),
            ];
        });

        return $automatic->concat($manual)->values();
    }

    /**
     * @return array{departments: int, total: int, sesuai: int, major: int, minor: int, observasi: int, findings: int, compliance: int}
     */
    public function totals(): array
    {
        $rows = $this->rows();

        $counts = self::withDerived([
            'total' => (int) $rows->sum('total'),
            'sesuai' => (int) $rows->sum('sesuai'),
            'major' => (int) $rows->sum('major'),
            'minor' => (int) $rows->sum('minor'),
            'observasi' => (int) $rows->sum('observasi'),
        ]);

        return ['departments' => $rows->count(), ...$counts];
    }

    /**
     * All non-conformities with their CAR number, in department and item order.
     *
     * @return Collection<int, array{number: string, item: ChecklistItem, department: AuditDepartment, severity: FindingSeverity, action: ?CorrectiveAction, criteria: string}>
     */
    public function findings(): Collection
    {
        return $this->audit->departments
            ->flatMap(fn (AuditDepartment $department) => $department->items
                ->filter(fn (ChecklistItem $item): bool => $item->isFinding())
                ->map(fn (ChecklistItem $item): array => [$department, $item]))
            ->values()
            ->map(fn (array $pair, int $index): array => [
                'number' => sprintf('CAR-%03d', $index + 1),
                'item' => $pair[1],
                'department' => $pair[0],
                'severity' => $pair[1]->findingSeverity(),
                'action' => $pair[1]->correctiveAction,
                'criteria' => $pair[1]->effectiveCriteria($pair[0]) ?: '-',
            ]);
    }

    /**
     * Criteria of all departments, de-duplicated, for the Kesimpulan header.
     */
    public function criteriaSummary(): string
    {
        $all = $this->audit->departments
            ->flatMap(fn (AuditDepartment $department): array => $department->criteriaList())
            ->unique()
            ->values();

        return $all->isEmpty() ? '-' : $all->implode('; ');
    }
}
