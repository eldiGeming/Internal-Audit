<?php

namespace Database\Factories;

use App\Models\Audit;
use App\Models\RecapEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecapEntry>
 */
class RecapEntryFactory extends Factory
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
            'department' => 'Logistik',
            'auditee' => fake()->name(),
            'auditor' => fake()->name(),
            'total' => 4,
            'sesuai' => 3,
            'major' => 0,
            'minor' => 1,
            'observasi' => 0,
            'note' => null,
        ];
    }
}
