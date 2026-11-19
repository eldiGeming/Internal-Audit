<?php

namespace App\Support;

/**
 * The six audit steps, in the order requested in the client's Menu.docx:
 * 1 Identitas, 2 Checklist, 3 Daftar Periksa, 4 CAR, 5 Rekap, 6 Kesimpulan.
 */
final class AuditSteps
{
    /**
     * @return list<array{key: string, route: string, number: int, label: string, icon: string, badge: ?string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'identity', 'route' => 'audits.identity', 'number' => 1, 'label' => 'Identitas Audit', 'icon' => 'identification-card', 'badge' => null],
            ['key' => 'checklist', 'route' => 'audits.checklist', 'number' => 2, 'label' => 'Checklist Audit', 'icon' => 'list-checks', 'badge' => 'items'],
            ['key' => 'inspection', 'route' => 'audits.inspection', 'number' => 3, 'label' => 'Daftar Periksa', 'icon' => 'file-text', 'badge' => 'departments'],
            ['key' => 'car', 'route' => 'audits.car', 'number' => 4, 'label' => 'CAR', 'icon' => 'warning-octagon', 'badge' => 'findings'],
            ['key' => 'recap', 'route' => 'audits.recap', 'number' => 5, 'label' => 'Rekap Audit', 'icon' => 'chart-bar', 'badge' => null],
            ['key' => 'conclusion', 'route' => 'audits.conclusion', 'number' => 6, 'label' => 'Kesimpulan Audit', 'icon' => 'seal-check', 'badge' => null],
        ];
    }

    /**
     * @return array{key: string, route: string, number: int, label: string, icon: string, badge: ?string}|null
     */
    public static function neighbour(string $key, int $offset): ?array
    {
        $steps = self::all();
        $index = array_search($key, array_column($steps, 'key'), true);

        return $index === false ? null : ($steps[$index + $offset] ?? null);
    }
}
