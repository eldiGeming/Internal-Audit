<?php

namespace App\Support;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use Illuminate\Support\Str;
use JsonException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

/**
 * Checklist import/export in the column layout of the reference template:
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
     * @return list<array{question: string, evidence: ?string, result: ?CheckResult, severity: ?FindingSeverity}>
     *
     * @throws JsonException when a .json file is not valid JSON
     */
    public static function parse(string $path, string $extension): array
    {
        $extension = Str::lower($extension);

        $rows = $extension === 'json'
            ? self::rowsFromJson((string) file_get_contents($path))
            : self::rowsFromSpreadsheet($path, $extension);

        $items = [];

        foreach ($rows as $row) {
            $question = trim((string) ($row['question'] ?? ''));

            // Skip blank rows and the header row ("Pertanyaan", "Pertanyaan / Poin Pemeriksaan", ...).
            if ($question === '' || Str::startsWith(Str::lower($question), 'pertanyaan') || Str::contains(Str::lower($question), 'poin pemeriksaan')) {
                continue;
            }

            $result = CheckResult::fromLoose($row['result'] ?? null);

            $items[] = [
                'question' => Str::limit($question, 2000, ''),
                'evidence' => filled($row['evidence'] ?? null) ? Str::limit(trim((string) $row['evidence']), 2000, '') : null,
                'result' => $result,
                'severity' => $result === CheckResult::TidakSesuai
                    ? (FindingSeverity::fromLoose($row['severity'] ?? null) ?? FindingSeverity::Major)
                    : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{question: mixed, evidence: mixed, result: mixed, severity: mixed}>
     */
    private static function rowsFromSpreadsheet(string $path, string $extension): array
    {
        $reader = SimpleExcelReader::create($path, $extension)->noHeaderRow();

        if ($extension === 'csv') {
            // Excel with an Indonesian locale saves CSV with ";" as the separator.
            $handle = fopen($path, 'r');
            $firstLine = (string) fgets($handle);
            fclose($handle);
            $reader->useDelimiter(substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',');
        }

        return $reader->getRows()
            ->map(fn (array $cells): array => [
                'question' => $cells[1] ?? null,
                'evidence' => $cells[2] ?? null,
                'result' => $cells[3] ?? null,
                'severity' => $cells[4] ?? null,
            ])
            ->values()
            ->all();
    }

    /**
     * Accepts a plain array of items or {"items": [...]} (the shape this app exports).
     *
     * @return list<array{question: mixed, evidence: mixed, result: mixed, severity: mixed}>
     *
     * @throws JsonException
     */
    private static function rowsFromJson(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $items = is_array($data) && array_is_list($data) ? $data : ($data['items'] ?? []);

        return array_values(array_map(fn ($item): array => [
            'question' => $item['question'] ?? $item['Pertanyaan'] ?? null,
            'evidence' => $item['evidence'] ?? $item['note'] ?? $item['Catatan'] ?? null,
            'result' => $item['result'] ?? $item['Hasil'] ?? null,
            'severity' => $item['severity'] ?? $item['Kategori'] ?? null,
        ], array_filter((array) $items, 'is_array')));
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
     * Writes header + rows to a temporary xlsx/csv file and returns its path.
     *
     * @param  list<list<string|int>>  $rows
     */
    public static function writeTemporary(string $extension, array $rows): string
    {
        $base = tempnam(sys_get_temp_dir(), 'checklist');
        @unlink($base);
        $path = $base.'.'.$extension;

        $writer = SimpleExcelWriter::create($path, $extension, shouldAddBom: $extension === 'csv')->noHeaderRow();
        $writer->addRow(self::HEADER);
        $writer->addRows($rows);
        $writer->close();

        return $path;
    }
}
