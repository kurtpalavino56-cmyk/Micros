<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — microcredential configuration metadata for `courses`.
 *
 * Purely additive: no existing column is dropped, renamed, or altered.
 *
 * `is_stackable` is a configuration/UI flag ONLY — it does not create a
 * relationship to any framework by itself. The authoritative link between a
 * microcredential and the framework(s) it contributes to lives entirely in
 * `stacking_framework_requirements` (see the following migration), and a
 * course may appear in zero, one, or many frameworks there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'pqf_level')) {
                $table->string('pqf_level')->nullable()->after('level');
            }
            if (! Schema::hasColumn('courses', 'target_learners')) {
                $table->text('target_learners')->nullable()->after('description');
            }
            if (! Schema::hasColumn('courses', 'delivery_mode')) {
                $table->string('delivery_mode')->nullable()->after('term');
            }
            if (! Schema::hasColumn('courses', 'learning_hours')) {
                $table->unsignedInteger('learning_hours')->nullable()->after('delivery_mode');
            }
            if (! Schema::hasColumn('courses', 'mastery_passing_percent')) {
                // Credential-level mastery threshold. Deliberately separate
                // from `passing_score`, which remains the quiz-level default.
                $table->unsignedTinyInteger('mastery_passing_percent')->nullable()->after('passing_score');
            }
            if (! Schema::hasColumn('courses', 'credit_bearing')) {
                $table->boolean('credit_bearing')->default(false)->after('mastery_passing_percent');
            }
            if (! Schema::hasColumn('courses', 'credit_equivalency')) {
                // Admin-entered only. Never inferred or auto-populated anywhere
                // in the completion/stacking code path.
                $table->string('credit_equivalency')->nullable()->after('credit_bearing');
            }
            if (! Schema::hasColumn('courses', 'equivalent_course')) {
                $table->string('equivalent_course')->nullable()->after('credit_equivalency');
            }
            if (! Schema::hasColumn('courses', 'manual_assessment_required')) {
                $table->boolean('manual_assessment_required')->default(false)->after('equivalent_course');
            }
            if (! Schema::hasColumn('courses', 'attendance_requirement_enabled')) {
                $table->boolean('attendance_requirement_enabled')->default(false)->after('manual_assessment_required');
            }
            if (! Schema::hasColumn('courses', 'attendance_requirement_note')) {
                $table->text('attendance_requirement_note')->nullable()->after('attendance_requirement_enabled');
            }
            if (! Schema::hasColumn('courses', 'performance_requirement_enabled')) {
                $table->boolean('performance_requirement_enabled')->default(false)->after('attendance_requirement_note');
            }
            if (! Schema::hasColumn('courses', 'performance_requirement_note')) {
                $table->text('performance_requirement_note')->nullable()->after('performance_requirement_enabled');
            }
            // `admin_requirement_enabled` / `admin_requirement_note` cover
            // INSTITUTIONAL/ADMINISTRATIVE sign-off only (e.g. clearance,
            // required documentation, non-academic units process). This must
            // never be interpreted as, or wired to, a payment, tuition, fee,
            // or any other financial requirement. All UPSKILL microcredentials
            // are free of charge — the existing `payments` table/model is not
            // part of the enrollment, completion, or credential-awarding
            // workflow, and nothing in this migration touches it.
            if (! Schema::hasColumn('courses', 'admin_requirement_enabled')) {
                $table->boolean('admin_requirement_enabled')->default(false)->after('performance_requirement_note');
            }
            if (! Schema::hasColumn('courses', 'admin_requirement_note')) {
                $table->text('admin_requirement_note')->nullable()->after('admin_requirement_enabled');
            }
            if (! Schema::hasColumn('courses', 'requires_faculty_verification')) {
                $table->boolean('requires_faculty_verification')->default(false)->after('admin_requirement_note');
            }
            if (! Schema::hasColumn('courses', 'requires_academic_unit_confirmation')) {
                // Independent of requires_faculty_verification — satisfying one
                // must never be treated as satisfying the other.
                $table->boolean('requires_academic_unit_confirmation')->default(false)->after('requires_faculty_verification');
            }
            if (! Schema::hasColumn('courses', 'is_stackable')) {
                $table->boolean('is_stackable')->default(false)->after('requires_academic_unit_confirmation');
            }
            if (! Schema::hasColumn('courses', 'version_number')) {
                $table->unsignedInteger('version_number')->default(1)->after('is_stackable');
            }
            if (! Schema::hasColumn('courses', 'previous_version_id')) {
                $table->foreignId('previous_version_id')->nullable()->after('version_number')
                    ->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'version_change_note')) {
                $table->text('version_change_note')->nullable()->after('previous_version_id');
            }
            if (! Schema::hasColumn('courses', 'version_approved_by')) {
                $table->foreignId('version_approved_by')->nullable()->after('version_change_note')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'version_approved_at')) {
                $table->timestamp('version_approved_at')->nullable()->after('version_approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'previous_version_id')) {
                $table->dropConstrainedForeignId('previous_version_id');
            }
            if (Schema::hasColumn('courses', 'version_approved_by')) {
                $table->dropConstrainedForeignId('version_approved_by');
            }
            foreach ([
                'pqf_level', 'target_learners', 'delivery_mode', 'learning_hours',
                'mastery_passing_percent', 'credit_bearing', 'credit_equivalency', 'equivalent_course',
                'manual_assessment_required', 'attendance_requirement_enabled', 'attendance_requirement_note',
                'performance_requirement_enabled', 'performance_requirement_note',
                'admin_requirement_enabled', 'admin_requirement_note',
                'requires_faculty_verification', 'requires_academic_unit_confirmation', 'is_stackable',
                'version_number', 'version_change_note', 'version_approved_at',
            ] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
