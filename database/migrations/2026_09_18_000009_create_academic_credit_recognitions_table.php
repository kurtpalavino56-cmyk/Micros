<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — academic credit recognition workflow record.
 *
 * Completely separate object from `user_stacking_progress`. Nothing in the
 * completion or stacking code path (Phase 2 or later phases) is permitted
 * to write to this table automatically. A row here is only ever created by
 * an explicit action in the future academic-recognition workflow (Phase 7),
 * which does not exist yet. `stacking_status = requirements_met` must never
 * by itself create or advance a row in this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_credit_recognitions')) {
            Schema::create('academic_credit_recognitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('stacking_framework_id')->constrained('stacking_frameworks')->cascadeOnDelete();
                // pending | unit_recommended | dean_endorsed | registrar_recorded | denied
                $table->string('status')->default('pending');
                $table->foreignId('unit_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('unit_reviewed_at')->nullable();
                $table->text('unit_recommendation')->nullable();
                $table->foreignId('dean_endorsed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('dean_endorsed_at')->nullable();
                $table->foreignId('registrar_recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('registrar_recorded_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'stacking_framework_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_credit_recognitions');
    }
};
