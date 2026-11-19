<?php

namespace Tests\Feature;

use App\Enums\FindingSeverity;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\RecapEntry;
use App\Support\AuditReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditReportTest extends TestCase
{
    use RefreshDatabase;

    private function department(Audit $audit, string $name, int $position): AuditDepartment
    {
        return AuditDepartment::factory()->for($audit)->create(['name' => $name, 'position' => $position]);
    }

    #[Test]
    public function it_counts_results_per_department_and_ignores_rows_without_a_question(): void
    {
        $audit = Audit::factory()->create();
        $production = $this->department($audit, 'Produksi', 0);

        ChecklistItem::factory()->sesuai()->for($production, 'department')->create();
        ChecklistItem::factory()->finding(FindingSeverity::Major)->for($production, 'department')->create();
        ChecklistItem::factory()->finding(FindingSeverity::Minor)->for($production, 'department')->create();
        ChecklistItem::factory()->for($production, 'department')->create(); // prepared, not checked yet
        ChecklistItem::factory()->sesuai()->for($production, 'department')->create(['question' => '']);

        $row = (new AuditReport($audit))->rows()->first();

        $this->assertSame(
            ['total' => 4, 'sesuai' => 1, 'major' => 1, 'minor' => 1, 'observasi' => 0, 'compliance' => 25],
            array_intersect_key($row, array_flip(['total', 'sesuai', 'major', 'minor', 'observasi', 'compliance'])),
        );
        $this->assertSame('Terdapat 1 temuan Major, perlu tindakan korektif segera.', $row['note']);
    }

    #[Test]
    public function a_custom_department_note_replaces_the_automatic_one(): void
    {
        $audit = Audit::factory()->create();
        $this->department($audit, 'Gudang', 0)->update(['recap_note' => 'Perlu penertiban pengisian logsheet.']);

        $row = (new AuditReport($audit))->rows()->first();

        $this->assertSame('Perlu penertiban pengisian logsheet.', $row['note']);
        $this->assertSame('Belum ada item pemeriksaan.', $row['auto_note']);
    }

    #[Test]
    public function car_numbers_run_across_departments_in_display_order(): void
    {
        $audit = Audit::factory()->create();
        $quality = $this->department($audit, 'Quality Control', 1);
        $production = $this->department($audit, 'Produksi', 0);

        ChecklistItem::factory()->finding(FindingSeverity::Observasi)->for($quality, 'department')->create();
        ChecklistItem::factory()->finding()->for($production, 'department')->create(['position' => 1]);
        ChecklistItem::factory()->sesuai()->for($production, 'department')->create(['position' => 0]);
        ChecklistItem::factory()->finding(FindingSeverity::Minor)->for($production, 'department')->create(['position' => 2]);

        $findings = (new AuditReport($audit))->findings();

        $this->assertSame(['CAR-001', 'CAR-002', 'CAR-003'], $findings->pluck('number')->all());
        $this->assertSame(['Produksi', 'Produksi', 'Quality Control'], $findings->map(fn ($f) => $f['department']->name)->all());
        $this->assertSame([FindingSeverity::Major, FindingSeverity::Minor, FindingSeverity::Observasi], $findings->pluck('severity')->all());
    }

    #[Test]
    public function totals_include_manual_recap_rows_and_compliance_is_capped_at_100(): void
    {
        $audit = Audit::factory()->create();
        $production = $this->department($audit, 'Produksi', 0);
        ChecklistItem::factory()->sesuai()->for($production, 'department')->create();
        ChecklistItem::factory()->finding()->for($production, 'department')->create();
        RecapEntry::factory()->for($audit)->create(['total' => 2, 'sesuai' => 5, 'major' => 0, 'minor' => 0, 'observasi' => 0]);

        $report = new AuditReport($audit);

        $this->assertSame(100, $report->rows()->last()['compliance']);
        $this->assertSame(
            ['departments' => 2, 'total' => 4, 'sesuai' => 6, 'major' => 1, 'findings' => 1],
            array_intersect_key($report->totals(), array_flip(['departments', 'total', 'sesuai', 'major', 'findings'])),
        );
    }

    #[Test]
    public function the_list_query_computes_the_same_compliance_as_the_report(): void
    {
        $audit = Audit::factory()->create();
        $production = $this->department($audit, 'Produksi', 0);
        ChecklistItem::factory()->count(2)->sesuai()->for($production, 'department')->create();
        ChecklistItem::factory()->finding()->for($production, 'department')->create();

        $listed = Audit::query()->withResultCounts()->find($audit->id);

        $this->assertSame(67, $listed->compliance());
        $this->assertSame((new AuditReport($audit))->totals()['compliance'], $listed->compliance());
        $this->assertSame(1, $listed->items_findings);
    }
}
