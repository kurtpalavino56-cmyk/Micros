<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — authoritative completion tracking for `enrollments`.
 *
 * `progress_percent` (existing column) remains a PURE learning-progress
 * metric only. `completion_status` is the ONLY field the future
 * MicrocredentialCompletionService treats as credential-worthy. Reaching
 * 100% progress must never, by itself, move completion_status to
 * `completed` — that requires every applicable requirement flag below to
 * be satisfied (or not_required), including academic-unit confirmation
 * when the microcredential requires it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'completion_status')) {
                // in_progress | assessment_pending | mastered |
                // awaiting_faculty_verification | completed | rejected | revoked
                $table->string('completion_status')->default('in_progress')->after('progress_percent');
            }
            if (! Schema::hasColumn('enrollments', 'lessons_completed')) {
                $table->boolean('lessons_completed')->default(false)->after('completion_status');
            }
            if (! Schema::hasColumn('enrollments', 'quizzes_completed')) {
                $table->boolean('quizzes_completed')->default(false)->after('lessons_completed');
            }
            if (! Schema::hasColumn('enrollments', 'quiz_mastery_met')) {
                $table->boolean('quiz_mastery_met')->default(false)->after('quizzes_completed');
            }
            if (! Schema::hasColumn('enrollments', 'manual_assessment_status')) {
                // not_required | pending | passed | failed
                $table->string('manual_assessment_status')->default('not_required')->after('quiz_mastery_met');
            }
            if (! Schema::hasColumn('enrollments', 'attendance_status')) {
                $table->string('attendance_status')->default('not_required')->after('manual_assessment_status');
            }
            if (! Schema::hasColumn('enrollments', 'performance_status')) {
                $table->string('performance_status')->default('not_required')->after('attendance_status');
            }
            // `administrative_status` tracks INSTITUTIONAL/ADMINISTRATIVE
            // requirements only (clearance, documentation, etc.) — never a
            // payment/tuition/fee requirement. UPSKILL microcredentials are
            // free of charge; the existing `payments` table/model plays no
            // part in enrollment, completion, or credential issuance, and no
            // completion gate anywhere reads from it.
            if (! Schema::hasColumn('enrollments', 'administrative_status')) {
                $table->string('administrative_status')->default('not_required')->after('performance_status');
            }
            if (! Schema::hasColumn('enrollments', 'faculty_verification_status')) {
                // not_required | pending | verified | rejected
                $table->string('faculty_verification_status')->default('not_required')->after('administrative_status');
            }
            if (! Schema::hasColumn('enrollments', 'faculty_verified_by')) {
                $table->foreignId('faculty_verified_by')->nullable()->after('faculty_verification_status')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'faculty_verified_at')) {
                $table->timestamp('faculty_verified_at')->nullable()->after('faculty_verified_by');
            }
            if (! Schema::hasColumn('enrollments', 'academic_unit_confirmation_status')) {
                // not_required | pending | confirmed | rejected
                // Independently gated — never inferred from faculty verification.
                $table->string('academic_unit_confirmation_status')->default('not_required')->after('faculty_verified_at');
            }
            if (! Schema::hasColumn('enrollments', 'academic_unit_confirmed_by')) {
                $table->foreignId('academic_unit_confirmed_by')->nullable()->after('academic_unit_confirmation_status')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'academic_unit_confirmed_at')) {
                $table->timestamp('academic_unit_confirmed_at')->nullable()->after('academic_unit_confirmed_by');
            }
            if (! Schema::hasColumn('enrollments', 'completed_at')) {
                // Set only when completion_status transitions to 'completed'.
                $table->timestamp('completed_at')->nullable()->after('academic_unit_confirmed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'faculty_verified_by')) {
                $table->dropConstrainedForeignId('faculty_verified_by');
            }
            if (Schema::hasColumn('enrollments', 'academic_unit_confirmed_by')) {
                $table->dropConstrainedForeignId('academic_unit_confirmed_by');
            }
            foreach ([
                'completion_status', 'lessons_completed', 'quizzes_completed', 'quiz_mastery_met',
                'manual_assessment_status', 'attendance_status', 'performance_status', 'administrative_status',
                'faculty_verification_status', 'faculty_verified_at',
                'academic_unit_confirmation_status', 'academic_unit_confirmed_at', 'completed_at',
            ] as $column) {
                if (Schema::hasColumn('enrollments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
