<?php

namespace Tests\Feature;

use App\Enums\CheckResult;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\User;
use App\Support\ChecklistSpreadsheet;
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
    public function a_single_car_exports_as_its_own_pdf(): void
    {
        $finding = $this->department->items()->where('result', CheckResult::TidakSesuai)->sole();

        $response = $this->actingAs($this->audit->user)
            ->get(route('audits.pdf.car.item', [$this->audit, $finding]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('filename=car_001_', $response->headers->get('content-disposition'));
    }

    #[Test]
    public function only_a_finding_of_this_audit_has_a_car_pdf(): void
    {
        $compliantItem = $this->department->items()->where('result', CheckResult::Sesuai)->sole();
        $findingOfAnotherAudit = ChecklistItem::factory()->finding()->create();

        $this->actingAs($this->audit->user);

        $this->get(route('audits.pdf.car.item', [$this->audit, $compliantItem]))->assertNotFound();
        $this->get(route('audits.pdf.car.item', [$this->audit, $findingOfAnotherAudit]))->assertNotFound();
    }

    #[Test]
    public function the_checklist_exports_as_xlsx_that_imports_back_unchanged(): void
    {
        $response = $this->actingAs($this->audit->user)
            ->get(route('audits.checklist.export', [$this->audit, $this->department]))
            ->assertOk()
            ->assertDownload('Checklist_produksi.xlsx');

        $rows = ChecklistSpreadsheet::parse($response->baseResponse->getFile()->getPathname());

        $this->assertCount(2, $rows);
        $this->assertSame('Apakah SOP tersedia?', $rows[0]['question']);
        $this->assertSame(CheckResult::Sesuai, $rows[0]['result']);
    }

    #[Test]
    public function the_checklist_is_not_exported_as_csv_or_json(): void
    {
        $this->actingAs($this->audit->user);
        $base = "/audits/{$this->audit->id}/departemen/{$this->department->id}/checklist";

        $this->get($base.'.csv')->assertNotFound();
        $this->get($base.'.json')->assertNotFound();
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
            ->get(route('audits.checklist.export', [$this->audit, $this->department]))
            ->assertForbidden();
    }
}
