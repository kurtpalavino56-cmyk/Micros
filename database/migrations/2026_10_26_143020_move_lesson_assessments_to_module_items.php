<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_item_placement_backups', function (Blueprint $table): void {
            $table->id();
            $table->string('item_type', 24);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unique(['item_type', 'item_id']);
        });

        DB::table('lesson_activities')->whereNotNull('lesson_id')->whereNotNull('module_id')
            ->select(['id', 'lesson_id'])->orderBy('id')->chunk(500, function ($activities): void {
                DB::table('lesson_item_placement_backups')->insert($activities->map(fn ($activity) => [
                    'item_type' => 'activity',
                    'item_id' => $activity->id,
                    'lesson_id' => $activity->lesson_id,
                ])->all());
            });

        DB::table('quizzes')->whereNotNull('lesson_id')->whereNotNull('module_id')->where('is_summative', false)
            ->select(['id', 'lesson_id'])->orderBy('id')->chunk(500, function ($quizzes): void {
                DB::table('lesson_item_placement_backups')->insert($quizzes->map(fn ($quiz) => [
                    'item_type' => 'quiz',
                    'item_id' => $quiz->id,
                    'lesson_id' => $quiz->lesson_id,
                ])->all());
            });

        DB::table('lesson_activities')->whereNotNull('lesson_id')->whereNotNull('module_id')->update(['lesson_id' => null]);
        DB::table('quizzes')->whereNotNull('lesson_id')->whereNotNull('module_id')->where('is_summative', false)->update(['lesson_id' => null]);
    }

    public function down(): void
    {
        DB::table('lesson_item_placement_backups')->orderBy('id')->get()->each(function ($placement): void {
            $table = $placement->item_type === 'activity' ? 'lesson_activities' : 'quizzes';
            DB::table($table)->where('id', $placement->item_id)->update(['lesson_id' => $placement->lesson_id]);
        });

        Schema::dropIfExists('lesson_item_placement_backups');
    }
};
