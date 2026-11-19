<?php

namespace Tests\Feature;

use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Livewire\Audits\Checklist;
use App\Livewire\Audits\Inspection;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Support\ChecklistSpreadsheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChecklistEditingTest extends TestCase
{
    use RefreshDatabase;

    private Audit $audit;

    private AuditDepartment $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->audit = Audit::factory()->create();
        $this->department = AuditDepartment::factory()->for($this->audit)->create(['name' => 'Produksi', 'criteria' => ['ISO 9001:2015 Klausul 8.5']]);
    }

    private function checklist()
    {
        return Livewire::actingAs($this->audit->user)->test(Checklist::class, ['audit' => $this->audit]);
    }

    #[Test]
    public function marking_an_item_tidak_sesuai_defaults_the_category_to_major(): void
    {
        $item = ChecklistItem::factory()->for($this->department, 'department')->create();

        $this->checklist()
            ->set("items.{$item->id}.result", 'tidak_sesuai')
            ->assertSet("items.{$item->id}.severity", 'major');

        $this->assertSame(FindingSeverity::Major, $item->fresh()->severity);
    }

    #[Test]
    public function switching_back_to_sesuai_clears_the_category(): void
    {
        $item = ChecklistItem::factory()->finding(FindingSeverity::Minor)->for($this->department, 'department')->create();

        $this->checklist()->set("items.{$item->id}.result", 'sesuai');

        $this->assertSame(CheckResult::Sesuai, $item->fresh()->result);
        $this->assertNull($item->fresh()->severity);
    }

    #[Test]
    public function an_unknown_result_value_is_rejected(): void
    {
        $item = ChecklistItem::factory()->for($this->department, 'department')->create();

        $this->checklist()
            ->set("items.{$item->id}.result", 'mungkin')
            ->assertHasErrors("items.{$item->id}.result");

        $this->assertNull($item->fresh()->result);
    }

    #[Test]
    public function edits_on_the_inspection_page_change_the_same_item(): void
    {
        $item = ChecklistItem::factory()->for($this->department, 'department')->create();

        Livewire::actingAs($this->audit->user)
            ->test(Inspection::class, ['audit' => $this->audit])
            ->set("items.{$item->id}.criteria", 'SOP-PRD-01')
            ->set("items.{$item->id}.evidence", 'Logsheet lengkap');

        $item->refresh();
        $this->assertSame('SOP-PRD-01', $item->criteria);
        $this->assertSame('Logsheet lengkap', $item->evidence);
    }

    #[Test]
    public function items_can_be_added_in_batches_and_empty_rows_cleaned_up(): void
    {
        ChecklistItem::factory()->for($this->department, 'department')->create(['question' => 'Terisi']);

        $component = $this->checklist()->call('addItems', 5);
        $this->assertSame(6, $this->department->items()->count());

        $component->call('clearEmptyItems');
        $this->assertSame(['Terisi'], $this->department->items()->pluck('question')->all());
    }

    #[Test]
    public function criteria_can_hold_several_entries(): void
    {
        $this->checklist()
            ->call('addCriterion')
            ->set('criteria.1', 'SOP-PRD-01 Pengendalian Proses')
            ->call('addCriterion')
            ->call('removeCriterion', 2);

        $this->assertSame('ISO 9001:2015 Klausul 8.5; SOP-PRD-01 Pengendalian Proses', $this->department->fresh()->criteriaText());
    }

    #[Test]
    public function a_new_department_can_be_created_from_the_checklist_page(): void
    {
        $this->checklist()
            ->call('openDepartmentModal')
            ->set('newDepartmentName', 'Logistik')
            ->call('createDepartment')
            ->assertHasNoErrors();

        $created = $this->audit->departments()->where('name', 'Logistik')->first();
        $this->assertNotNull($created);
        $this->assertSame(1, $created->position);
    }

    #[Test]
    public function an_xlsx_template_imports_questions_results_and_categories(): void
    {
        $path = ChecklistSpreadsheet::writeTemporary([
            [1, 'Apakah SOP tersedia?', 'SOP terpasang', 'SESUAI', ''],
            [2, 'Apakah alat ukur terkalibrasi?', 'Lewat jadwal', 'TIDAK SESUAI', 'Minor'],
            [3, '', '', '', ''],
        ]);
        ChecklistItem::factory()->for($this->department, 'department')->create(['question' => null]); // empty placeholder row

        $this->checklist()
            ->set('importFile', UploadedFile::fake()->createWithContent('checklist.xlsx', file_get_contents($path)))
            ->assertHasNoErrors();

        $items = $this->department->items()->get();
        $this->assertSame(['Apakah SOP tersedia?', 'Apakah alat ukur terkalibrasi?'], $items->pluck('question')->all());
        $this->assertSame(FindingSeverity::Minor, $items[1]->severity);
        unlink($path);
    }

    #[Test]
    public function a_corrupt_xlsx_file_shows_an_error_and_imports_nothing(): void
    {
        $this->checklist()
            ->set('importFile', UploadedFile::fake()->createWithContent('checklist.xlsx', 'bukan file excel'))
            ->assertHasErrors('importFile');

        $this->assertSame(0, $this->department->items()->count());
    }

    #[Test]
    #[DataProvider('nonExcelFiles')]
    public function only_xlsx_files_can_be_imported(string $filename, string $content): void
    {
        $this->checklist()
            ->set('importFile', UploadedFile::fake()->createWithContent($filename, $content))
            ->assertHasErrors(['importFile' => 'extensions']);

        $this->assertSame(0, $this->department->items()->count());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function nonExcelFiles(): array
    {
        return [
            'csv' => ['checklist.csv', "No;Pertanyaan;Catatan;Hasil;Kategori\n1;Apakah APAR siap pakai?;;Tidak Sesuai;Observasi\n"],
            'json' => ['checklist.json', '{"items":[{"question":"Apakah form terisi?","result":"SESUAI"}]}'],
            'old xls' => ['checklist.xls', 'excel 97-2003'],
            'pdf' => ['checklist.pdf', '%PDF-1.4'],
        ];
    }
}
