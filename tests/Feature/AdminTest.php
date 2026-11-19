<?php

namespace Tests\Feature;

use App\Enums\CarStatus;
use App\Enums\CheckResult;
use App\Enums\UserRole;
use App\Livewire\Admin\AuditorManager;
use App\Livewire\Admin\CorrectiveActionRegister;
use App\Livewire\Admin\Dashboard;
use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Models\Company;
use App\Models\CorrectiveAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    #[Test]
    public function the_dashboard_shows_the_company_with_the_best_latest_audit(): void
    {
        $good = Company::factory()->create(['name' => 'PT Patuh Selalu']);
        $weak = Company::factory()->create(['name' => 'PT Masih Belajar']);

        $this->auditWithResults($good, sesuai: 3, findings: 1, date: '2026-11-10');
        $this->auditWithResults($weak, sesuai: 9, findings: 1, date: '2026-10-01'); // older, better audit
        $this->auditWithResults($weak, sesuai: 1, findings: 3, date: '2026-11-15'); // latest audit counts

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['PT hasil audit terbaik', 'PT Patuh Selalu', '75'])
            ->assertSeeInOrder(['Peringkat kepatuhan PT', 'PT Patuh Selalu', 'PT Masih Belajar']);
    }

    #[Test]
    public function the_dashboard_tracks_car_follow_up_against_target_dates(): void
    {
        $this->travelTo('2026-11-20 09:00');
        $department = AuditDepartment::factory()->for(Audit::factory())->create();
        $finding = fn () => ChecklistItem::factory()->finding()->for($department, 'department')->create();

        $overdue = CorrectiveAction::factory()->for($finding(), 'item')->create(['correction_due_date' => '2026-11-18', 'prevention_due_date' => '2026-12-30']);
        $dueToday = CorrectiveAction::factory()->for($finding(), 'item')->create(['correction_due_date' => '2026-11-20', 'prevention_due_date' => null]);
        CorrectiveAction::factory()->for($finding(), 'item')->create(['correction_due_date' => '2026-11-01', 'status' => CarStatus::Closed]);
        $finding(); // no CAR sheet filled in yet

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertViewHas('findingsCount', 4)
            ->assertViewHas('openCount', 3)
            ->assertViewHas('overdueCount', 1)
            ->assertViewHas('closedCount', 1)
            ->assertViewHas('progress', fn (Collection $progress): bool => $progress->mapWithKeys(fn (array $row): array => [$row['state']->value => $row['count']])->all()
                === ['overdue' => 1, 'not_planned' => 1, 'in_progress' => 1, 'closed' => 1])
            ->assertViewHas('followUps', fn (Collection $followUps): bool => $followUps->pluck('action.id')->all() === [$overdue->id, $dueToday->id]);
    }

    private function auditWithResults(Company $company, int $sesuai, int $findings, string $date): Audit
    {
        $audit = Audit::factory()->for($company)->create(['audit_date' => $date]);
        $department = AuditDepartment::factory()->for($audit)->create();
        ChecklistItem::factory()->count($sesuai)->sesuai()->for($department, 'department')->create();
        ChecklistItem::factory()->count($findings)->finding()->for($department, 'department')->create();

        return $audit;
    }

    #[Test]
    public function an_admin_creates_an_auditor_account_that_can_log_in(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('create')
            ->set('name', 'Andi Pratama')
            ->set('username', 'Andi')
            ->set('password', 'rahasia-123')
            ->call('save')
            ->assertHasNoErrors();

        $auditor = User::query()->where('username', 'andi')->sole();
        $this->assertSame(UserRole::Auditor, $auditor->role);
        $this->assertTrue(Hash::check('rahasia-123', $auditor->password));
    }

    #[Test]
    public function after_setting_a_password_the_login_info_is_offered_once_for_copying(): void
    {
        $manager = Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('create')
            ->set('name', 'Andi Pratama')
            ->set('username', 'andi')
            ->set('password', 'rahasia-123')
            ->call('save')
            ->assertSet('showShare', true)
            ->assertSet('password', '')
            ->assertSet('shareText', fn (string $text): bool => str_contains($text, 'Username: andi')
                && str_contains($text, 'Password: rahasia-123')
                && str_contains($text, 'Link login: '.route('login')));

        $manager->set('showShare', false)->assertSet('shareText', '');
    }

    #[Test]
    public function saving_without_a_new_password_does_not_offer_login_info(): void
    {
        $auditor = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('edit', $auditor->id)
            ->set('team', 'Tim Audit Mutu')
            ->call('save')
            ->assertSet('showShare', false)
            ->assertSet('shareText', '');
    }

    #[Test]
    public function the_issued_password_is_stored_encrypted_and_copied_on_request_only(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('create')
            ->set('name', 'Rina Marlina')
            ->set('username', 'rina_qc')
            ->set('password', 'Rahasia-777')
            ->call('save');

        $auditor = User::query()->where('username', 'rina_qc')->sole();
        $this->assertSame('Rahasia-777', $auditor->issued_password);
        $this->assertNotSame('Rahasia-777', $auditor->getRawOriginal('issued_password'));

        $this->actingAs($this->admin)
            ->get(route('admin.auditors'))
            ->assertOk()
            ->assertSee('Salin akun')
            ->assertDontSee('Rahasia-777');

        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('loginInfoFor', $auditor->id)
            ->assertReturned(fn (string $text): bool => str_contains($text, 'Username: rina_qc')
                && str_contains($text, 'Password: Rahasia-777')
                && str_contains($text, 'Link login: '.route('login')));
    }

    #[Test]
    public function an_account_without_an_issued_password_copies_without_one(): void
    {
        $auditor = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('loginInfoFor', $auditor->id)
            ->assertReturned(fn (string $text): bool => str_contains($text, 'Password: hubungi admin'));
    }

    #[Test]
    public function only_admins_can_fetch_login_info(): void
    {
        $auditor = User::factory()->create(['issued_password' => 'Rahasia-777']);

        Livewire::actingAs(User::factory()->create())
            ->test(AuditorManager::class)
            ->call('loginInfoFor', $auditor->id)
            ->assertForbidden();
    }

    #[Test]
    public function new_accounts_need_a_unique_username_and_an_8_character_password(): void
    {
        User::factory()->create(['username' => 'andi']);

        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('create')
            ->set('name', 'Andi Kedua')
            ->set('username', 'andi')
            ->set('password', 'pendek')
            ->call('save')
            ->assertHasErrors(['username', 'password']);
    }

    #[Test]
    public function editing_without_a_password_keeps_the_current_password(): void
    {
        $auditor = User::factory()->create();
        $hash = $auditor->password;

        Livewire::actingAs($this->admin)
            ->test(AuditorManager::class)
            ->call('edit', $auditor->id)
            ->set('team', 'Tim Audit Mutu')
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors();

        $auditor->refresh();
        $this->assertSame($hash, $auditor->password);
        $this->assertSame('Tim Audit Mutu', $auditor->team);
        $this->assertFalse($auditor->is_active);
    }

    #[Test]
    public function admin_accounts_cannot_be_edited_or_deleted_through_the_auditor_manager(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        Livewire::actingAs($this->admin)->test(AuditorManager::class)->call('delete', $otherAdmin->id)->assertNotFound();

        $this->assertModelExists($otherAdmin);
    }

    #[Test]
    public function deleting_an_auditor_keeps_their_audits(): void
    {
        $audit = Audit::factory()->create();
        $auditor = $audit->user;

        Livewire::actingAs($this->admin)->test(AuditorManager::class)->call('delete', $auditor->id);

        $this->assertModelMissing($auditor);
        $this->assertNull($audit->fresh()->user_id);
    }

    #[Test]
    public function deleting_a_car_removes_it_and_marks_the_finding_sesuai(): void
    {
        $action = CorrectiveAction::factory()->create();
        $item = $action->item;

        Livewire::actingAs($this->admin)->test(CorrectiveActionRegister::class)->call('deleteCorrectiveAction', $item->id);

        $this->assertModelMissing($action);
        $this->assertSame(CheckResult::Sesuai, $item->fresh()->result);
        $this->assertNull($item->fresh()->severity);
    }

    #[Test]
    public function the_car_register_can_be_filtered_by_company(): void
    {
        $shown = $this->auditWithResults(Company::factory()->create(['name' => 'PT Ditampilkan']), sesuai: 0, findings: 1, date: '2026-11-01');
        $this->auditWithResults(Company::factory()->create(['name' => 'PT Disembunyikan']), sesuai: 0, findings: 1, date: '2026-11-01');

        $component = Livewire::actingAs($this->admin)->test(CorrectiveActionRegister::class);
        $component->assertViewHas('groups', fn ($groups) => $groups->count() === 2);

        $component->set('companyId', $shown->company_id)
            ->assertViewHas('groups', fn ($groups) => $groups->keys()->all() === [$shown->company_id]);
    }
}
