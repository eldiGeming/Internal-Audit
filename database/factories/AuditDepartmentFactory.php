<?php

namespace Database\Factories;

use App\Models\Audit;
use App\Models\AuditDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditDepartment>
 */
class AuditDepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'audit_id' => Audit::factory(),
            'position' => 0,
            'name' => fake()->randomElement(['Produksi', 'Quality Control', 'Gudang', 'Purchasing', 'HRD', 'Maintenance']),
            'auditee' => fake()->name(),
            'auditor' => fake()->name(),
            'criteria' => ['ISO 9001:2015 Klausul 8.5'],
            'recap_note' => null,
        ];
    }
}
