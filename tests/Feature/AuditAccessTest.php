<?php

namespace Tests\Feature;

use App\Livewire\Audits\Checklist;
use App\Livewire\Audits\CorrectiveActions;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function stepRoutes(): array
    {
        return [
            'identitas' => ['audits.identity'],
            'checklist' => ['audits.checklist'],
            'daftar periksa' => ['audits.inspection'],
            'car' => ['audits.car'],
            'rekap' => ['audits.recap'],
            'kesimpulan' => ['audits.conclusion'],
            'pdf car' => ['audits.pdf.car'],
            'pdf rekap' => ['audits.pdf.recap'],
            'pdf kesimpulan' => ['audits.pdf.conclusion'],
        ];
    }

    #[Test]
    #[DataProvider('stepRoutes')]
    public function the_owner_can_open_every_step(string $route): void
    {
        $audit = Audit::factory()->create();
        AuditDepartment::factory()->for($audit)->create();

        $this->actingAs($audit->user)->get(route($route, $audit))->assertOk();
    }

    #[Test]
    #[DataProvider('stepRoutes')]
    public function another_auditor_cannot_open_any_step(string $route): void
    {
        $audit = Audit::factory()->create();

        $this->actingAs(User::factory()->create())->get(route($route, $audit))->assertForbidden();
    }

    #[Test]
    public function an_admin_can_open_any_audit(): void
    {
        $audit = Audit::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('audits.identity', $audit))
            ->assertOk();
    }

    #[Test]
    public function the_audit_list_only_shows_the_auditors_own_audits(): void
    {
        $mine = Audit::factory()->create();
        $theirs = Audit::factory()->create();

        $this->actingAs($mine->user)
            ->get(route('audits.index'))
            ->assertSee($mine->company->name)
            ->assertDontSee($theirs->company->name);
    }

    #[Test]
    public function a_department_of_another_audit_is_not_reachable_through_this_audit(): void
    {
        $audit = Audit::factory()->create();
        $foreignDepartment = AuditDepartment::factory()->create();

        $this->actingAs($audit->user)
            ->get(route('audits.pdf.inspection', [$audit, $foreignDepartment]))
            ->assertNotFound();
    }

    #[Test]
    public function an_item_id_from_another_audit_cannot_be_edited(): void
    {
        $audit = Audit::factory()->create();
        AuditDepartment::factory()->for($audit)->create();
        $foreignItem = ChecklistItem::factory()->create(['question' => 'Asli']);

        Livewire::actingAs($audit->user)
            ->test(Checklist::class, ['audit' => $audit])
            ->set("items.{$foreignItem->id}.question", 'Diubah')
            ->assertNotFound();

        $this->assertSame('Asli', $foreignItem->fresh()->question);
    }

    #[Test]
    public function a_car_cannot_be_written_for_an_item_that_is_not_a_finding(): void
    {
        $audit = Audit::factory()->create();
        $item = ChecklistItem::factory()->sesuai()->for(AuditDepartment::factory()->for($audit), 'department')->create();

        Livewire::actingAs($audit->user)
            ->test(CorrectiveActions::class, ['audit' => $audit])
            ->set("cars.{$item->id}.root_cause", 'Tidak boleh tersimpan')
            ->assertNotFound();

        $this->assertDatabaseCount('corrective_actions', 0);
    }

    #[Test]
    public function auditors_cannot_open_admin_pages(): void
    {
        $auditor = User::factory()->create();

        foreach (['admin.dashboard', 'admin.auditors', 'admin.cars'] as $route) {
            $this->actingAs($auditor)->get(route($route))->assertForbidden();
        }
    }

    #[Test]
    public function admins_can_open_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();
        Audit::factory()->create();

        foreach (['admin.dashboard', 'admin.auditors', 'admin.cars'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }
}
