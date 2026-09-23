<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extension migration — adds every column the Blade views and controllers
 * need on top of the base micro-credentials / competency schema:
 *
 *   users          : avatar_url → longText (base64 avatars), role_label
 *   courses        : created_by, badge_id, program, term, skills, objectives
 *   course_lessons : file_url, file_name
 *   quizzes        : module_id, attempts, time_limit, instructions
 *   quiz_questions : type (Multiple Choice / True or False / Identification)
 *   pathways       : steps / destination / readiness display data
 *   lesson_completions : per-student lesson progress tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ─────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_label')) {
                // Optional free-text job/role label shown on profile pages
                // (defaults to the role's display name when null).
                $table->string('role_label')->nullable()->after('role_id');
            }
        });

        // Avatars are stored as client-resized base64 data-URLs, which
        // easily exceed VARCHAR(255) — widen to LONGTEXT.
        if (Schema::hasColumn('users', 'avatar_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->longText('avatar_url')->nullable()->change();
            });
        }

        // ── courses ───────────────────────────────────────────────────────
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('instructor')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'badge_id')) {
                // Badge awarded when the course is completed
                $table->foreignId('badge_id')->nullable()->after('created_by')
                    ->constrained('badges')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'program')) {
                $table->string('program')->nullable()->after('category');
            }
            if (! Schema::hasColumn('courses', 'term')) {
                $table->string('term')->nullable()->after('program');
            }
            if (! Schema::hasColumn('courses', 'skills')) {
                $table->json('skills')->nullable()->after('description');
            }
            if (! Schema::hasColumn('courses', 'objectives')) {
                $table->json('objectives')->nullable()->after('skills');
            }
        });

        // ── course_lessons ────────────────────────────────────────────────
        Schema::table('course_lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('course_lessons', 'file_url')) {
                $table->string('file_url')->nullable()->after('content');
            }
            if (! Schema::hasColumn('course_lessons', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_url');
            }
        });

        // ── quizzes ───────────────────────────────────────────────────────
        Schema::table('quizzes', function (Blueprint $table) {
            if (! Schema::hasColumn('quizzes', 'module_id')) {
                $table->foreignId('module_id')->nullable()->after('course_id')
                    ->constrained('course_modules')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('quizzes', 'attempts')) {
                $table->string('attempts')->nullable()->after('passing_score');
            }
            if (! Schema::hasColumn('quizzes', 'time_limit')) {
                $table->integer('time_limit')->default(0)->after('attempts');
            }
            if (! Schema::hasColumn('quizzes', 'instructions')) {
                $table->text('instructions')->nullable()->after('time_limit');
            }
        });

        // ── quiz_questions ────────────────────────────────────────────────
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_questions', 'type')) {
                $table->string('type')->default('Multiple Choice')->after('question');
            }
        });

        // ── pathways ──────────────────────────────────────────────────────
        Schema::table('pathways', function (Blueprint $table) {
            if (! Schema::hasColumn('pathways', 'steps')) {
                $table->json('steps')->nullable()->after('description');
            }
            if (! Schema::hasColumn('pathways', 'destination')) {
                $table->string('destination')->nullable()->after('steps');
            }
            if (! Schema::hasColumn('pathways', 'destination_color')) {
                $table->string('destination_color')->nullable()->after('destination');
            }
            if (! Schema::hasColumn('pathways', 'connector_color')) {
                $table->string('connector_color')->nullable()->after('destination_color');
            }
            if (! Schema::hasColumn('pathways', 'recommendations')) {
                $table->json('recommendations')->nullable()->after('connector_color');
            }
            if (! Schema::hasColumn('pathways', 'desired_title')) {
                $table->string('desired_title')->nullable()->after('recommendations');
            }
            if (! Schema::hasColumn('pathways', 'current_competencies')) {
                $table->json('current_competencies')->nullable()->after('desired_title');
            }
            if (! Schema::hasColumn('pathways', 'missing_competencies')) {
                $table->json('missing_competencies')->nullable()->after('current_competencies');
            }
            if (! Schema::hasColumn('pathways', 'readiness_percent')) {
                $table->integer('readiness_percent')->default(0)->after('missing_competencies');
            }
            if (! Schema::hasColumn('pathways', 'readiness_label')) {
                $table->string('readiness_label')->nullable()->after('readiness_percent');
            }
        });

        // ── lesson_completions ────────────────────────────────────────────
        if (! Schema::hasTable('lesson_completions')) {
            Schema::create('lesson_completions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained('course_lessons')->cascadeOnDelete();
                $table->timestamp('completed_at')->useCurrent();
                $table->timestamps();
                $table->unique(['user_id', 'lesson_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');

        Schema::table('pathways', function (Blueprint $table) {
            foreach (['steps', 'destination', 'destination_color', 'connector_color', 'recommendations', 'desired_title', 'current_competencies', 'missing_competencies', 'readiness_percent', 'readiness_label'] as $column) {
                if (Schema::hasColumn('pathways', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'module_id')) {
                $table->dropConstrainedForeignId('module_id');
            }
            foreach (['attempts', 'time_limit', 'instructions'] as $column) {
                if (Schema::hasColumn('quizzes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            foreach (['file_url', 'file_name'] as $column) {
                if (Schema::hasColumn('course_lessons', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('courses', 'badge_id')) {
                $table->dropConstrainedForeignId('badge_id');
            }
            foreach (['program', 'term', 'skills', 'objectives'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_label')) {
                $table->dropColumn('role_label');
            }
            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->change();
            }
        });
    }
};
