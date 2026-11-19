<?php

namespace App\Support;

/**
 * The six audit steps, in the order requested in the client's Menu.docx:
 * 1 Identitas, 2 Checklist, 3 Daftar Periksa, 4 CAR, 5 Rekap, 6 Kesimpulan.
 */
final class AuditSteps
{
    /**
     * `count` names a key of AuditStep::stepCounts(); `unit` is the word read after that number.
     *
     * @return list<array{key: string, route: string, number: int, label: string, icon: string, count: ?string, unit: ?string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'identity', 'route' => 'audits.identity', 'number' => 1, 'label' => 'Identitas Audit', 'icon' => 'identification-card', 'count' => null, 'unit' => null],
            ['key' => 'checklist', 'route' => 'audits.checklist', 'number' => 2, 'label' => 'Checklist Audit', 'icon' => 'list-checks', 'count' => 'items', 'unit' => 'pertanyaan'],
            ['key' => 'inspection', 'route' => 'audits.inspection', 'number' => 3, 'label' => 'Daftar Periksa', 'icon' => 'file-text', 'count' => 'departments', 'unit' => 'departemen'],
            ['key' => 'car', 'route' => 'audits.car', 'number' => 4, 'label' => 'CAR', 'icon' => 'warning-octagon', 'count' => 'findings', 'unit' => 'temuan'],
            ['key' => 'recap', 'route' => 'audits.recap', 'number' => 5, 'label' => 'Rekap Audit', 'icon' => 'chart-bar', 'count' => null, 'unit' => null],
            ['key' => 'conclusion', 'route' => 'audits.conclusion', 'number' => 6, 'label' => 'Kesimpulan Audit', 'icon' => 'seal-check', 'count' => null, 'unit' => null],
        ];
    }

    /**
     * @return array{key: string, route: string, number: int, label: string, icon: string, count: ?string, unit: ?string}|null
     */
    public static function neighbour(string $key, int $offset): ?array
    {
        $steps = self::all();
        $index = array_search($key, array_column($steps, 'key'), true);

        return $index === false ? null : ($steps[$index + $offset] ?? null);
    }
}
