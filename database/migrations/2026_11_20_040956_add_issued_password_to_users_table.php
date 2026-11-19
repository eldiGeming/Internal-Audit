<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The password an admin issued to an auditor, encrypted with APP_KEY (not hashed) so the admin can
     * copy the login details again later. The login itself still checks the hashed `password` column.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('issued_password')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('issued_password');
        });
    }
};
