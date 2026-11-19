<?php

namespace App\Support;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

/**
 * Checklist import/export as Excel (.xlsx) in the column layout of the reference template:
 * No | Pertanyaan / Poin Pemeriksaan | Catatan / Bukti Objektif | Hasil Pemeriksaan | Kategori
 */
final class ChecklistSpreadsheet
{
    public const HEADER = [
        'No',
        'Pertanyaan / Poin Pemeriksaan',
        'Catatan / Bukti Objektif',
        'Hasil Pemeriksaan (SESUAI / TIDAK SESUAI)',
        'Kategori (Major / Minor / Observasi)',
    ];

    /**
     * Reads an .xlsx file. Columns are read by position (No, Pertanyaan, Catatan, Hasil, Kategori), so header text may differ.
     *
     * @return list<array{question: string, evidence: ?string, result: ?CheckResult, severity: ?FindingSeverity}>
     */
    public static function parse(string $path): array
    {
        $items = [];

        foreach (SimpleExcelReader::create($path, 'xlsx')->noHeaderRow()->getRows() as $cells) {
            [, $question, $evidence, $result, $severity] = array_pad(array_values($cells), 5, null);
            $question = trim((string) $question);

            // Skip blank rows and the header row ("Pertanyaan", "Pertanyaan / Poin Pemeriksaan", ...).
            if ($question === '' || Str::startsWith(Str::lower($question), 'pertanyaan') || Str::contains(Str::lower($question), 'poin pemeriksaan')) {
                continue;
            }

            $result = CheckResult::fromLoose((string) $result);

            $items[] = [
                'question' => Str::limit($question, 2000, ''),
                'evidence' => filled($evidence) ? Str::limit(trim((string) $evidence), 2000, '') : null,
                'result' => $result,
                'severity' => $result === CheckResult::TidakSesuai
                    ? (FindingSeverity::fromLoose((string) $severity) ?? FindingSeverity::Major)
                    : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<list<string|int>>
     */
    public static function rowsFor(AuditDepartment $department): array
    {
        return $department->items
            ->filter(fn (ChecklistItem $item): bool => $item->hasQuestion())
            ->values()
            ->map(fn (ChecklistItem $item, int $index): array => [
                $index + 1,
                (string) $item->question,
                (string) $item->evidence,
                $item->result ? Str::upper($item->result->label()) : '',
                $item->result === CheckResult::TidakSesuai ? $item->findingSeverity()->label() : '',
            ])
            ->all();
    }

    /**
     * Writes header + rows to a temporary .xlsx file and returns its path.
     *
     * @param  list<list<string|int>>  $rows
     */
    public static function writeTemporary(array $rows): string
    {
        $base = tempnam(sys_get_temp_dir(), 'checklist');
        @unlink($base);
        $path = $base.'.xlsx';

        $writer = SimpleExcelWriter::create($path, 'xlsx')->noHeaderRow();
        $writer->addRow(self::HEADER);
        $writer->addRows($rows);
        $writer->close();

        return $path;
    }
}
