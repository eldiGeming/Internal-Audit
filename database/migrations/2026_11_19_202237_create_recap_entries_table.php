<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recap_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->cascadeOnDelete();
            $table->string('department')->default('');
            $table->string('auditee')->nullable();
            $table->string('auditor')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sesuai')->default(0);
            $table->unsignedInteger('major')->default(0);
            $table->unsignedInteger('minor')->default(0);
            $table->unsignedInteger('observasi')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recap_entries');
    }
};
