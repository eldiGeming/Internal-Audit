<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Audit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function local_seeding_creates_the_demo_accounts_and_sample_audits(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check('admin123', User::query()->where('username', 'admin')->sole()->password));
        $this->assertSame(UserRole::Auditor, User::query()->where('username', 'auditor')->sole()->role);
        $this->assertSame(2, Audit::query()->count());
    }

    #[Test]
    public function production_seeding_creates_only_an_admin_with_a_random_password(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $admin = User::query()->sole();
        $this->assertSame('admin', $admin->username);
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertFalse(Hash::check('admin123', $admin->password));
        $this->assertSame(0, Audit::query()->count());
    }
}
