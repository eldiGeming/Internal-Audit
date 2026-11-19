<?php

namespace Database\Factories;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    /**
     * Define the model's default state: a prepared question that has not been checked yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'audit_department_id' => AuditDepartment::factory(),
            'position' => 0,
            'question' => 'Apakah '.fake()->sentence(8, false),
            'criteria' => null,
            'evidence' => null,
            'result' => null,
            'severity' => null,
        ];
    }

    public function sesuai(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => CheckResult::Sesuai,
            'severity' => null,
            'evidence' => 'Dokumen tersedia dan sesuai.',
        ]);
    }

    public function finding(FindingSeverity $severity = FindingSeverity::Major): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => CheckResult::TidakSesuai,
            'severity' => $severity,
            'evidence' => 'Ditemukan ketidaksesuaian pada saat pemeriksaan.',
        ]);
    }
}
