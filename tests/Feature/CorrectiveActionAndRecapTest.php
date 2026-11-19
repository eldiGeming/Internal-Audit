<?php

namespace Tests\Feature;

use App\Enums\CarStatus;
use App\Livewire\Audits\Conclusion;
use App\Livewire\Audits\CorrectiveActions;
use App\Livewire\Audits\Recap;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\CorrectiveAction;
use App\Models\RecapEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CorrectiveActionAndRecapTest extends TestCase
{
    use RefreshDatabase;

    private Audit $audit;

    private AuditDepartment $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->audit = Audit::factory()->create();
        $this->department = AuditDepartment::factory()->for($this->audit)->create(['auditee' => 'Budi Santoso']);
    }

    #[Test]
    public function the_first_edit_of_a_car_saves_it_with_the_auditee_as_default_pic(): void
    {
        $item = ChecklistItem::factory()->finding()->for($this->department, 'department')->create();

        Livewire::actingAs($this->audit->user)
            ->test(CorrectiveActions::class, ['audit' => $this->audit])
            ->assertSet("cars.{$item->id}.correction_pic", 'Budi Santoso')
            ->set("cars.{$item->id}.root_cause", 'Belum ada pengingat kalibrasi.');

        $action = CorrectiveAction::query()->sole();
        $this->assertSame('Belum ada pengingat kalibrasi.', $action->root_cause);
        $this->assertSame('Budi Santoso', $action->correction_pic);
        $this->assertSame('Budi Santoso', $action->prevention_pic);
        $this->assertSame(CarStatus::Open, $action->status);
    }

    #[Test]
    public function corrective_and_preventive_actions_are_saved_separately(): void
    {
        $item = ChecklistItem::factory()->finding()->for($this->department, 'department')->create();

        Livewire::actingAs($this->audit->user)
            ->test(CorrectiveActions::class, ['audit' => $this->audit])
            ->set("cars.{$item->id}.correction", 'Tarik alat ukur.')
            ->set("cars.{$item->id}.correction_due_date", '2026-11-26')
            ->set("cars.{$item->id}.prevention", 'Buat daftar induk alat ukur.')
            ->set("cars.{$item->id}.status", 'closed');

        $action = $item->correctiveAction()->sole();
        $this->assertSame(['Tarik alat ukur.', 'Buat daftar induk alat ukur.'], [$action->correction, $action->prevention]);
        $this->assertSame('2026-11-26', $action->correction_due_date->toDateString());
        $this->assertSame(CarStatus::Closed, $action->status);
    }

    #[Test]
    public function an_invalid_target_date_is_not_saved(): void
    {
        $item = ChecklistItem::factory()->finding()->for($this->department, 'department')->create();

        Livewire::actingAs($this->audit->user)
            ->test(CorrectiveActions::class, ['audit' => $this->audit])
            ->set("cars.{$item->id}.correction_due_date", 'besok')
            ->assertHasErrors("cars.{$item->id}.correction_due_date")
            ->set("cars.{$item->id}.root_cause", 'Penyebab')
            ->assertHasErrors("cars.{$item->id}.correction_due_date");

        $this->assertDatabaseCount('corrective_actions', 0);
    }

    #[Test]
    public function manual_recap_rows_can_be_added_edited_and_removed(): void
    {
        $component = Livewire::actingAs($this->audit->user)
            ->test(Recap::class, ['audit' => $this->audit])
            ->call('addManualEntry');

        $entry = RecapEntry::query()->sole();
        $component->set("manual.{$entry->id}.department", 'Logistik')
            ->set("manual.{$entry->id}.total", '4')
            ->set("manual.{$entry->id}.sesuai", '-1')
            ->assertHasErrors("manual.{$entry->id}.sesuai");

        $this->assertSame(['Logistik', 4, 0], [$entry->fresh()->department, $entry->fresh()->total, $entry->fresh()->sesuai]);

        $component->call('removeManualEntry', $entry->id);
        $this->assertModelMissing($entry);
    }

    #[Test]
    public function a_department_note_can_be_overridden_and_reset(): void
    {
        $component = Livewire::actingAs($this->audit->user)
            ->test(Recap::class, ['audit' => $this->audit])
            ->set("notes.{$this->department->id}", 'Perlu penertiban logsheet.');

        $this->assertSame('Perlu penertiban logsheet.', $this->department->fresh()->recap_note);

        $component->call('useAutomaticNote', $this->department->id);
        $this->assertNull($this->department->fresh()->recap_note);
    }

    #[Test]
    public function the_final_conclusion_is_saved(): void
    {
        Livewire::actingAs($this->audit->user)
            ->test(Conclusion::class, ['audit' => $this->audit])
            ->set('finalConclusion', 'Sistem manajemen mutu efektif dengan catatan kalibrasi.');

        $this->assertSame('Sistem manajemen mutu efektif dengan catatan kalibrasi.', $this->audit->fresh()->final_conclusion);
    }
}
