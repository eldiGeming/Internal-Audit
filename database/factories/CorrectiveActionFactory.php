<?php

namespace Database\Factories;

use App\Enums\CarStatus;
use App\Models\ChecklistItem;
use App\Models\CorrectiveAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorrectiveAction>
 */
class CorrectiveActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checklist_item_id' => ChecklistItem::factory()->finding(),
            'root_cause' => 'Belum ada jadwal pemeriksaan rutin yang ditetapkan.',
            'correction' => 'Melakukan perbaikan langsung pada temuan.',
            'correction_pic' => fake()->name(),
            'correction_due_date' => now()->addWeeks(2),
            'prevention' => 'Menyusun jadwal pemeriksaan berkala dan sosialisasi ke operator.',
            'prevention_pic' => fake()->name(),
            'prevention_due_date' => now()->addMonth(),
            'verification_note' => null,
            'status' => CarStatus::Open,
        ];
    }
}
