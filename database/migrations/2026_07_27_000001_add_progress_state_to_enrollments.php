<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Adds enrollments.progress_state (JSON): the course player's exact state
 *   {
 *     "completed_lessons": ["0-0", "0-1", ...],   // "<moduleIdx>-<lessonIdx>"
 *     "module_scores":     {"0": 3, "1": 2, ...}  // moduleIdx -> correct answers
 *   }
 * Saved in real time from the course player so a student's progress percent
 * (and their completed lessons / quiz scores) survives page changes and is
 * shown consistently on the dashboard, browse and course pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'progress_state')) {
                $table->json('progress_state')->nullable()->after('progress_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'progress_state')) {
                $table->dropColumn('progress_state');
            }
        });
    }
};
