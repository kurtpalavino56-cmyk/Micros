<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — approved microcredential stacking frameworks.
 *
 * Deliberately a brand-new, dedicated structure — NOT a reuse of `pathways`.
 * `Pathway` remains career/learning-pathway-only and is untouched by this
 * migration.
 *
 * `stacking_framework_requirements` is the ONLY authoritative place a
 * microcredential is linked to a framework. There is intentionally no
 * unique constraint on `course_id` alone: a single microcredential may
 * belong to more than one approved framework at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stacking_frameworks')) {
            Schema::create('stacking_frameworks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('status')->default('draft'); // draft | pending_approval | approved | inactive
                $table->string('approving_academic_unit')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->string('target_recognition')->nullable();
                $table->unsignedInteger('required_count')->default(0);
                $table->json('cumulative_outcomes')->nullable();
                $table->string('equivalent_course')->nullable();
                $table->string('equivalent_units')->nullable();
                $table->boolean('sequence_required')->default(false);
                $table->text('credit_recognition_conditions')->nullable();
                $table->string('pqf_level')->nullable();
                $table->string('credit_equivalency')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stacking_framework_requirements')) {
            Schema::create('stacking_framework_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stacking_framework_id')->constrained('stacking_frameworks')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->unsignedInteger('order')->default(1);
                $table->boolean('is_required')->default(true);
                $table->timestamps();

                $table->unique(['stacking_framework_id', 'course_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stacking_framework_requirements');
        Schema::dropIfExists('stacking_frameworks');
    }
};
