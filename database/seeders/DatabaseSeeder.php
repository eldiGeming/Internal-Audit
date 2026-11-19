<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Local/staging: demo admin + auditor (same credentials as the reference prototype) and sample audits.
     * Production: only an admin account with a random password, see seedProductionAdmin().
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->seedProductionAdmin();

            return;
        }

        User::query()->updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator Sistem',
            'email' => 'admin@auditinternal.co.id',
            'password' => 'admin123',
            'role' => UserRole::Admin,
            'team' => 'Manajemen & Pengawasan Mutu',
            'is_active' => true,
        ]);

        User::query()->updateOrCreate(['username' => 'auditor'], [
            'name' => 'Ziban Robani',
            'email' => 'auditor@auditinternal.co.id',
            'password' => 'auditor123',
            'role' => UserRole::Auditor,
            'team' => 'Tim Auditor Internal',
            'is_active' => true,
        ]);

        $this->call(DemoAuditSeeder::class);
    }

    /**
     * Production: only the admin account, with a random password shown once. No demo data.
     */
    private function seedProductionAdmin(): void
    {
        if (User::query()->where('username', 'admin')->exists()) {
            $this->command?->info('Akun admin sudah ada, tidak diubah.');

            return;
        }

        $password = Str::password(16, symbols: false);

        User::query()->create([
            'name' => 'Administrator Sistem',
            'username' => 'admin',
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->command?->warn("Akun admin dibuat. Username: admin, password: {$password} (catat sekarang, tidak ditampilkan lagi).");
    }
}
