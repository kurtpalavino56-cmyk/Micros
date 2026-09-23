<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * courses.related_skills — the skills a course is related to, ticked by
 * faculty on the course form ("Subject is Related on").
 *
 * This is NOT courses.skills. That column is the public "Skills You'll
 * Gain" list shown on the course description and homepage cards. This one
 * is internal: it exists so the student Browse page's Recommended toggle
 * can match courses against a student's skills_have / skills_want, and it
 * is never rendered on any student-facing page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'related_skills')) {
                $table->json('related_skills')->nullable()->after('skills');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'related_skills')) {
                $table->dropColumn('related_skills');
            }
        });
    }
};
