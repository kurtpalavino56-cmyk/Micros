<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 (Step 1 correction) — a dedicated, independent gate for
 * competency-unit mastery, separate from `quiz_mastery_met`.
 *
 * `quiz_mastery_met`       = quiz/assessment mastery only.
 * `competency_mastery_met` = mastery of every competency unit linked via
 *                            `learning_outcomes.competency_unit_id`.
 *
 * Purely additive: no existing column, table, or relationship is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'competency_mastery_met')) {
                $table->boolean('competency_mastery_met')->default(false)->after('quiz_mastery_met');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'competency_mastery_met')) {
                $table->dropColumn('competency_mastery_met');
            }
        });
    }
};
