<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quiz_sets')
            || ! Schema::hasTable('quiz_questions')
            || ! Schema::hasColumn('quiz_questions', 'quiz_set_id')
            || ! Schema::hasColumn('quiz_questions', 'position')) {
            return;
        }

        DB::table('quizzes')->select('id')->orderBy('id')->chunkById(200, function ($quizzes): void {
            foreach ($quizzes as $quiz) {
                DB::transaction(function () use ($quiz): void {
                    $sets = DB::table('quiz_sets')
                        ->where('quiz_id', $quiz->id)
                        ->orderBy('position')
                        ->orderBy('id')
                        ->get(['id']);

                    if ($sets->count() <= 1) {
                        return;
                    }

                    $primarySetId = (int) $sets->first()->id;
                    $orderedQuestionIds = collect();

                    foreach ($sets as $index => $set) {
                        $query = DB::table('quiz_questions')->where('quiz_id', $quiz->id);
                        if ($index === 0) {
                            $query->where(function ($query) use ($set): void {
                                $query->where('quiz_set_id', $set->id)->orWhereNull('quiz_set_id');
                            });
                        } else {
                            $query->where('quiz_set_id', $set->id);
                        }

                        $orderedQuestionIds = $orderedQuestionIds->concat(
                            $query->orderBy('position')->orderBy('id')->pluck('id')
                        );
                    }

                    $knownSetIds = $sets->pluck('id')->all();
                    $orderedQuestionIds = $orderedQuestionIds->concat(
                        DB::table('quiz_questions')
                            ->where('quiz_id', $quiz->id)
                            ->whereNotNull('quiz_set_id')
                            ->whereNotIn('quiz_set_id', $knownSetIds)
                            ->orderBy('position')
                            ->orderBy('id')
                            ->pluck('id')
                    );

                    foreach ($orderedQuestionIds->values() as $position => $questionId) {
                        DB::table('quiz_questions')
                            ->where('id', $questionId)
                            ->update([
                                'quiz_set_id' => $primarySetId,
                                'position' => $position,
                            ]);
                    }
                });
            }
        });
    }

    public function down(): void {}
};
