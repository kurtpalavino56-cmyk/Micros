<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — framework-level progress record per student.
 *
 * This table stores ONLY the outcome (in_progress / requirements_met) per
 * user+framework. It deliberately does NOT store a row per requirement —
 * completed/required counts are always calculated live from
 * `stacking_framework_requirements` joined against the student's
 * `enrollments.completion_status = 'completed'`, so there is nothing here
 * to fall out of sync with the actual requirement list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_stacking_progress')) {
            Schema::create('user_stacking_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('stacking_framework_id')->constrained('stacking_frameworks')->cascadeOnDelete();
                $table->string('status')->default('in_progress'); // in_progress | requirements_met
                $table->timestamp('requirements_met_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'stacking_framework_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stacking_progress');
    }
};
