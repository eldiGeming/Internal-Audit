<?php

namespace Tests\Feature;

use App\Livewire\Audits\AuditIndex;
use App\Livewire\Audits\Identity;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditIdentityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_auditor_creates_an_audit_for_an_existing_company(): void
    {
        $auditor = User::factory()->create();
        $company = Company::factory()->create(['name' => 'PT Sinar Logistik Andalan']);

        Livewire::actingAs($auditor)
            ->test(AuditIndex::class)
            ->set('companyName', 'PT Sinar Logistik Andalan')
            ->set('auditNumber', 'AUD-INT/2026/020')
            ->call('createAudit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $audit = Audit::query()->sole();
        $this->assertTrue($audit->company->is($company));
        $this->assertTrue($audit->user->is($auditor));
    }

    #[Test]
    public function the_company_name_is_required_when_creating_an_audit(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AuditIndex::class)
            ->call('createAudit')
            ->assertHasErrors('companyName');
    }

    #[Test]
    public function renaming_a_company_used_only_by_this_audit_keeps_the_same_company(): void
    {
        $audit = Audit::factory()->create();
        $companyId = $audit->company_id;

        Livewire::actingAs($audit->user)
            ->test(Identity::class, ['audit' => $audit])
            ->set('companyName', 'PT Nama Baru');

        $this->assertSame($companyId, $audit->fresh()->company_id);
        $this->assertSame('PT Nama Baru', Company::find($companyId)->name);
    }

    #[Test]
    public function renaming_a_shared_company_moves_only_this_audit(): void
    {
        $audit = Audit::factory()->create();
        $sibling = Audit::factory()->for($audit->company)->create();
        $original = $audit->company->name;

        Livewire::actingAs($audit->user)
            ->test(Identity::class, ['audit' => $audit])
            ->set('companyName', 'PT Lain Sejahtera');

        $this->assertSame('PT Lain Sejahtera', $audit->fresh()->company->name);
        $this->assertSame($original, $sibling->fresh()->company->name);
    }

    #[Test]
    public function choosing_an_existing_company_name_links_the_audit_and_removes_the_orphan(): void
    {
        $audit = Audit::factory()->create();
        $orphan = $audit->company;
        $existing = Company::factory()->create(['name' => 'PT Sudah Ada']);
        Audit::factory()->for($existing)->create();

        Livewire::actingAs($audit->user)
            ->test(Identity::class, ['audit' => $audit])
            ->set('companyName', 'PT Sudah Ada');

        $this->assertTrue($audit->fresh()->company->is($existing));
        $this->assertModelMissing($orphan);
    }

    #[Test]
    public function audit_fields_are_saved_and_validated(): void
    {
        $audit = Audit::factory()->create();

        Livewire::actingAs($audit->user)
            ->test(Identity::class, ['audit' => $audit])
            ->set('auditNumber', 'AUD-INT/2026/099')
            ->set('auditDate', '2026-11-20')
            ->set('objective', 'Verifikasi SOP gudang.')
            ->set('auditDate', 'bukan-tanggal')
            ->assertHasErrors('auditDate');

        $audit->refresh();
        $this->assertSame('AUD-INT/2026/099', $audit->audit_number);
        $this->assertSame('2026-11-20', $audit->audit_date->toDateString());
        $this->assertSame('Verifikasi SOP gudang.', $audit->objective);
    }

    #[Test]
    public function a_png_logo_is_stored_and_replaces_the_previous_file(): void
    {
        Storage::fake('public');
        $audit = Audit::factory()->create();

        $component = Livewire::actingAs($audit->user)->test(Identity::class, ['audit' => $audit])
            ->set('logo', UploadedFile::fake()->image('logo-lama.png'));
        $oldPath = $audit->company->fresh()->logo_path;

        $component->set('logo', UploadedFile::fake()->image('logo-baru.png'));
        $newPath = $audit->company->fresh()->logo_path;

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    #[Test]
    public function svg_logos_are_rejected(): void
    {
        Storage::fake('public');
        $audit = Audit::factory()->create();

        Livewire::actingAs($audit->user)
            ->test(Identity::class, ['audit' => $audit])
            ->set('logo', UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'))
            ->assertHasErrors('logo');

        $this->assertNull($audit->company->fresh()->logo_path);
    }

    #[Test]
    public function departments_can_be_added_edited_and_removed(): void
    {
        $audit = Audit::factory()->create();

        $component = Livewire::actingAs($audit->user)->test(Identity::class, ['audit' => $audit])->call('addDepartment');
        $department = AuditDepartment::query()->sole();

        $component->set("departments.{$department->id}.name", 'Quality Control')
            ->set("departments.{$department->id}.auditee", 'Rina Marlina');

        $this->assertSame(['Quality Control', 'Rina Marlina', $audit->user->name], [$department->fresh()->name, $department->fresh()->auditee, $department->fresh()->auditor]);

        $component->call('removeDepartment', $department->id);
        $this->assertModelMissing($department);
    }

    #[Test]
    public function deleting_the_last_audit_of_a_company_removes_the_company(): void
    {
        $audit = Audit::factory()->create();
        $company = $audit->company;

        Livewire::actingAs($audit->user)->test(AuditIndex::class)->call('deleteAudit', $audit->id);

        $this->assertModelMissing($audit);
        $this->assertModelMissing($company);
    }

    #[Test]
    public function an_auditor_cannot_delete_someone_elses_audit(): void
    {
        $audit = Audit::factory()->create();

        Livewire::actingAs(User::factory()->create())->test(AuditIndex::class)->call('deleteAudit', $audit->id)->assertNotFound();

        $this->assertModelExists($audit);
    }
}
