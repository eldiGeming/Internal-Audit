<?php

namespace Database\Factories;

use App\Models\Audit;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Audit>
 */
class AuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'audit_number' => sprintf('AUD-INT/%s/%03d', now()->year, fake()->unique()->numberBetween(1, 999)),
            'audit_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'objective' => 'Memverifikasi kesesuaian penerapan sistem manajemen mutu dan SOP operasional.',
            'final_conclusion' => null,
        ];
    }
}
