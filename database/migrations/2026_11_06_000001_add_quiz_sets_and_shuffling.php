<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quiz sets (Set A, Set B, ...) and per-attempt shuffling.
 *
 * Every existing quiz gets one default "Set A" and its questions are moved
 * into it, so a one-set quiz behaves exactly as before. quiz_questions keeps
 * its quiz_id column: Quiz::questions() stays a direct HasMany, and sets are
 * an extra grouping on top of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['quiz_id', 'position']);
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            // nullOnDelete: a set is never deleted while its questions still
            // exist (QuizManagementService removes questions first), and a
            // cascade here would chain into quiz_attempt_answers.
            $table->foreignId('quiz_set_id')->nullable()->after('quiz_id')->constrained('quiz_sets')->nullOnDelete();
            $table->unsignedInteger('position')->default(0)->after('quiz_set_id');
            $table->boolean('lock_choice_order')->default(false)->after('points');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('quiz_set_id')->nullable()->after('quiz_id')->constrained('quiz_sets')->nullOnDelete();
            // Seeds are crc32 values (0 .. 4,294,967,295): unsigned BIGINT holds
            // that range on every driver; a signed INT would overflow.
            $table->unsignedBigInteger('shuffle_seed')->nullable()->after('quiz_set_id');
            $table->json('presentation')->nullable()->after('shuffle_seed');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('shuffle_questions')->default(false)->after('time_limit');
            $table->boolean('shuffle_choices')->default(false)->after('shuffle_questions');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['shuffle_questions', 'shuffle_choices']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quiz_set_id');
            $table->dropColumn(['shuffle_seed', 'presentation']);
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quiz_set_id');
            $table->dropColumn(['position', 'lock_choice_order']);
        });

        // Questions keep their quiz_id, so dropping the sets loses nothing a
        // pre-sets build could read. Rolling back a multi-set quiz merges its
        // sets into one flat list (the only shape the old code understands).
        Schema::dropIfExists('quiz_sets');
    }

    /** One "Set A" per existing quiz; existing questions move into it in id order. */
    private function backfill(): void
    {
        $now = now();

        DB::table('quizzes')->orderBy('id')->select('id')->chunkById(200, function ($quizzes) use ($now): void {
            foreach ($quizzes as $quiz) {
                $setId = DB::table('quiz_sets')->insertGetId([
                    'quiz_id' => $quiz->id,
                    'name' => 'Set A',
                    'position' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $position = 0;
                foreach (DB::table('quiz_questions')->where('quiz_id', $quiz->id)->orderBy('id')->pluck('id') as $questionId) {
                    DB::table('quiz_questions')->where('id', $questionId)->update([
                        'quiz_set_id' => $setId,
                        'position' => $position++,
                    ]);
                }
            }
        });
    }
};
