<?php

namespace Tests\Feature;

use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentExportTest extends TestCase
{
    use RefreshDatabase;

    private Audit $audit;

    private AuditDepartment $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->audit = Audit::factory()->create();
        $this->department = AuditDepartment::factory()->for($this->audit)->create(['name' => 'Produksi']);
        ChecklistItem::factory()->sesuai()->for($this->department, 'department')->create(['question' => 'Apakah SOP tersedia?']);
        ChecklistItem::factory()->finding()->for($this->department, 'department')->create();
    }

    #[Test]
    public function every_pdf_document_renders(): void
    {
        $this->actingAs($this->audit->user);

        foreach ([
            route('audits.pdf.inspection', [$this->audit, $this->department]),
            route('audits.pdf.car', $this->audit),
            route('audits.pdf.recap', $this->audit),
            route('audits.pdf.conclusion', $this->audit),
        ] as $url) {
            $response = $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }
    }

    #[Test]
    public function the_checklist_exports_as_xlsx_and_csv(): void
    {
        $this->actingAs($this->audit->user);

        $this->get(route('audits.checklist.export', [$this->audit, $this->department, 'xlsx']))
            ->assertOk()
            ->assertDownload('Checklist_produksi.xlsx');

        $csv = $this->get(route('audits.checklist.export', [$this->audit, $this->department, 'csv']))->assertOk();
        $this->assertStringContainsString('Apakah SOP tersedia?', file_get_contents($csv->baseResponse->getFile()->getPathname()));
    }

    #[Test]
    public function the_json_export_contains_the_filled_questions(): void
    {
        $this->actingAs($this->audit->user)
            ->get(route('audits.checklist.export', [$this->audit, $this->department, 'json']))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="Checklist_produksi.json"')
            ->assertJsonPath('dept', 'Produksi')
            ->assertJsonPath('items.0.question', 'Apakah SOP tersedia?')
            ->assertJsonPath('items.0.result', 'SESUAI')
            ->assertJsonCount(2, 'items');
    }

    #[Test]
    public function the_template_is_downloadable_by_any_logged_in_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('checklist.template'))
            ->assertOk()
            ->assertDownload('Template_Checklist_Audit.xlsx');
    }

    #[Test]
    public function another_auditor_cannot_export_this_checklist(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('audits.checklist.export', [$this->audit, $this->department, 'xlsx']))
            ->assertForbidden();
    }
}
