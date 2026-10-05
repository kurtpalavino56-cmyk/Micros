<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            if (! Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->foreignId('lesson_id')->nullable()->after('module_id')
                    ->constrained('course_lessons')->cascadeOnDelete();
            }
        });

        Schema::table('courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('courses', 'assessment_strategy')) {
                $table->text('assessment_strategy')->nullable()->after('learning_hours');
            }
            if (! Schema::hasColumn('courses', 'grading_rubric')) {
                $table->text('grading_rubric')->nullable()->after('assessment_strategy');
            }
        });

        if (! Schema::hasTable('lesson_activities')) {
            Schema::create('lesson_activities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lesson_id')->constrained('course_lessons')->cascadeOnDelete();
                $table->string('title');
                $table->string('activity_type', 32)->default('practice');
                $table->longText('instructions')->nullable();
                $table->boolean('is_required')->default(true);
                $table->unsignedInteger('max_points')->nullable();
                $table->unsignedTinyInteger('passing_percent')->nullable();
                $table->json('rubric')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['lesson_id', 'is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('lesson_activity_submissions')) {
            Schema::create('lesson_activity_submissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lesson_activity_id')->constrained('lesson_activities')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('attempt_number')->default(1);
                $table->longText('response_text')->nullable();
                $table->string('submission_path')->nullable();
                $table->string('status', 24)->default('submitted');
                $table->unsignedInteger('score')->nullable();
                $table->text('feedback')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['lesson_activity_id', 'user_id', 'submitted_at'], 'lesson_activity_submission_lookup');
            });
        }

        DB::table('courses')->select(['id', 'objectives'])->orderBy('id')->chunkById(100, function ($courses): void {
            foreach ($courses as $course) {
                if (DB::table('learning_outcomes')->where('course_id', $course->id)->exists()) {
                    continue;
                }

                $objectives = json_decode((string) $course->objectives, true);
                if (! is_array($objectives)) {
                    continue;
                }

                foreach (array_values(array_filter(array_map('trim', $objectives))) as $index => $objective) {
                    DB::table('learning_outcomes')->insert([
                        'course_id' => $course->id,
                        'competency_unit_id' => null,
                        'code' => 'LO'.($index + 1),
                        'description' => $objective,
                        'order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_activity_submissions');
        Schema::dropIfExists('lesson_activities');

        Schema::table('courses', function (Blueprint $table): void {
            foreach (['assessment_strategy', 'grading_rubric'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            if (Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->dropConstrainedForeignId('lesson_id');
            }
        });
    }
};
