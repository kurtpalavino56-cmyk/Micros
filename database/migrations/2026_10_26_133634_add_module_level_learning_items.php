<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->unsignedInteger('module_order')->nullable()->index();
        });

        Schema::table('lesson_activities', function (Blueprint $table): void {
            $table->foreignId('module_id')->nullable()->after('lesson_id')->constrained('course_modules')->cascadeOnDelete();
            $table->unsignedInteger('module_order')->nullable()->index();
            $table->foreignId('lesson_id')->nullable()->change();
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('is_summative')->default(false)->after('lesson_id')->index();
            $table->unsignedInteger('module_order')->nullable()->index();
        });

        DB::table('course_lessons')->orderBy('module_id')->orderBy('order')->orderBy('id')->get()
            ->groupBy('module_id')
            ->each(function ($lessons): void {
                $position = 0;

                foreach ($lessons as $lesson) {
                    $position += 1000000;
                    DB::table('course_lessons')->where('id', $lesson->id)->update(['module_order' => $position]);

                    $assessments = DB::table('lesson_activities')->where('lesson_id', $lesson->id)->select(['id', 'sort_order'])
                        ->get()->map(fn ($activity) => (object) ['type' => 'activity', ...((array) $activity)])
                        ->merge(DB::table('quizzes')->where('lesson_id', $lesson->id)->select(['id', 'sort_order'])
                            ->get()->map(fn ($quiz) => (object) ['type' => 'quiz', ...((array) $quiz)]))
                        ->sortBy(fn ($item) => [(int) $item->sort_order, $item->type === 'activity' ? 0 : 1, (int) $item->id]);

                    foreach ($assessments->values() as $index => $assessment) {
                        $moduleOrder = $position + (($index + 1) * 10);
                        $table = $assessment->type === 'activity' ? 'lesson_activities' : 'quizzes';
                        DB::table($table)->where('id', $assessment->id)->update([
                            'module_id' => $lesson->module_id,
                            'module_order' => $moduleOrder,
                        ]);
                    }
                }
            });

        DB::table('quizzes')->whereNotNull('module_id')->whereNull('lesson_id')->update(['is_summative' => true]);
        DB::table('quizzes')->where('is_summative', true)->get()->each(function ($quiz): void {
            $maxOrder = max(
                (int) DB::table('course_lessons')->where('module_id', $quiz->module_id)->max('module_order'),
                (int) DB::table('lesson_activities')->where('module_id', $quiz->module_id)->max('module_order'),
                (int) DB::table('quizzes')->where('module_id', $quiz->module_id)->where('is_summative', false)->max('module_order'),
            );

            DB::table('quizzes')->where('id', $quiz->id)->update(['module_order' => $maxOrder + 1000000]);
        });
    }

    public function down(): void
    {
        DB::table('lesson_activities')->whereNull('lesson_id')->get()->each(function ($activity): void {
            $lessonId = DB::table('course_lessons')->where('module_id', $activity->module_id)->orderBy('order')->value('id');
            if ($lessonId) {
                DB::table('lesson_activities')->where('id', $activity->id)->update(['lesson_id' => $lessonId]);
            }
        });

        Schema::table('lesson_activities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('module_id');
            $table->dropIndex(['module_order']);
            $table->dropColumn('module_order');
            $table->foreignId('lesson_id')->nullable(false)->change();
        });

        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->dropIndex(['module_order']);
            $table->dropColumn('module_order');
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropIndex(['is_summative']);
            $table->dropIndex(['module_order']);
            $table->dropColumn(['is_summative', 'module_order']);
        });
    }
};
