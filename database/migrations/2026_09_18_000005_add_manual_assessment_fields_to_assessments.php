<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — supports a course-level "Overall Practical Assessment" that
 * requires faculty grading, reusing the existing `assessments` table
 * instead of creating a parallel one. `course_id` is nullable because
 * existing rows are competency-unit assessments, not course-level ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('user_id')
                    ->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('assessments', 'submission_path')) {
                $table->string('submission_path')->nullable()->after('feedback');
            }
            if (! Schema::hasColumn('assessments', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('submission_path')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('assessments', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('assessments', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }
            if (Schema::hasColumn('assessments', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }
            foreach (['submission_path', 'reviewed_at', 'released_at'] as $column) {
                if (Schema::hasColumn('assessments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
