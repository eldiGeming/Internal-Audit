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
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('correction')->nullable();
            $table->string('correction_pic')->nullable();
            $table->date('correction_due_date')->nullable();
            $table->text('prevention')->nullable();
            $table->string('prevention_pic')->nullable();
            $table->date('prevention_due_date')->nullable();
            $table->text('verification_note')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};
