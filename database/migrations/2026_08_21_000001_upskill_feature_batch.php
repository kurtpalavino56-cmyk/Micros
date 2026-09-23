<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature batch — everything the following need:
 *
 *   courses.denial_feedback        Admin's reason when denying a submission
 *   courses.change_note            Faculty's summary of what they changed
 *   courses.prerequisite_ids       Other courses required before enrolling
 *   courses.certificate_enabled    Issue a certificate on completion
 *   courses.certificate_title      Custom certificate title
 *   announcements.audience         Which roles may see an announcement
 *   quizzes.questions_changed_at   Set ONLY when the question set really
 *                                  changes, so editing the attempt limit no
 *                                  longer resets every student's used count
 *   complaints / complaint_replies Help-centre inbox shared by student+admin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'denial_feedback')) {
                $table->text('denial_feedback')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('courses', 'change_note')) {
                $table->text('change_note')->nullable()->after('denial_feedback');
            }
            if (! Schema::hasColumn('courses', 'prerequisite_ids')) {
                $table->json('prerequisite_ids')->nullable()->after('related_skills');
            }
            if (! Schema::hasColumn('courses', 'certificate_enabled')) {
                $table->boolean('certificate_enabled')->default(false)->after('badge_id');
            }
            if (! Schema::hasColumn('courses', 'certificate_title')) {
                $table->string('certificate_title')->nullable()->after('certificate_enabled');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'audience')) {
                // ["student"], ["faculty"] or ["student","faculty"]
                $table->json('audience')->nullable()->after('body');
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (! Schema::hasColumn('quizzes', 'questions_changed_at')) {
                $table->timestamp('questions_changed_at')->nullable()->after('is_active');
            }
        });

        if (! Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('subject');
                $table->text('message');
                $table->string('category')->default('general');
                $table->string('status')->default('open');   // open | resolved
                $table->timestamp('last_reply_at')->nullable();
                // Watermarks for the unread dot on each side.
                $table->timestamp('student_read_at')->nullable();
                $table->timestamp('admin_read_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('complaint_replies')) {
            Schema::create('complaint_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->boolean('is_admin')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_replies');
        Schema::dropIfExists('complaints');

        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'questions_changed_at')) {
                $table->dropColumn('questions_changed_at');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'audience')) {
                $table->dropColumn('audience');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            foreach ([
                'denial_feedback', 'change_note', 'prerequisite_ids',
                'certificate_enabled', 'certificate_title',
            ] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
