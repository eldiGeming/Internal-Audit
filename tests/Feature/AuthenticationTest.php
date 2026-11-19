<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('audits.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function an_auditor_logs_in_with_username_and_lands_on_the_audit_list(): void
    {
        $auditor = User::factory()->create(['username' => 'ziban']);

        Livewire::test(Login::class)
            ->set('login', 'ziban')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($auditor);
        $this->get(route('home'))->assertRedirect(route('audits.index'));
    }

    #[Test]
    public function a_user_can_log_in_with_email(): void
    {
        $auditor = User::factory()->create(['email' => 'ziban@auditinternal.co.id']);

        Livewire::test(Login::class)
            ->set('login', 'ziban@auditinternal.co.id')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($auditor);
    }

    #[Test]
    public function an_admin_lands_on_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function a_wrong_password_is_rejected(): void
    {
        User::factory()->create(['username' => 'ziban']);

        Livewire::test(Login::class)
            ->set('login', 'ziban')
            ->set('password', 'salah-sekali')
            ->call('authenticate')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    #[Test]
    public function an_inactive_account_cannot_log_in(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif']);

        Livewire::test(Login::class)
            ->set('login', 'nonaktif')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    #[Test]
    public function login_is_throttled_after_five_failed_attempts(): void
    {
        User::factory()->create(['username' => 'ziban']);

        $component = Livewire::test(Login::class)->set('login', 'ziban')->set('password', 'salah');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $component->call('authenticate');
        }

        $component->set('password', 'password')->call('authenticate')->assertHasErrors('login');
        $this->assertGuest();
    }

    #[Test]
    public function a_deactivated_user_is_logged_out_on_the_next_request(): void
    {
        $auditor = User::factory()->create();
        $this->actingAs($auditor);
        $auditor->update(['is_active' => false]);

        $this->get(route('audits.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function demo_quick_fill_is_not_available_outside_local(): void
    {
        Livewire::test(Login::class)->call('fillDemo', 'admin')->assertNotFound();
    }

    #[Test]
    public function logging_out_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
