<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'quiz_set_id',
        'position',
        'question',
        'type',
        'options',
        'correct_answer',
        'points',
        'lock_choice_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'lock_choice_order' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * Questions created without a set (seeders, older code paths, tests) join
     * the quiz's first set, creating "Set A" when the quiz has none, so every
     * question always belongs to exactly one set.
     */
    protected static function booted(): void
    {
        static::creating(function (QuizQuestion $question): void {
            if ($question->quiz_set_id || ! $question->quiz_id) {
                return;
            }

            $set = QuizSet::query()->where('quiz_id', $question->quiz_id)->orderBy('position')->orderBy('id')->first()
                ?? QuizSet::query()->create(['quiz_id' => $question->quiz_id, 'name' => 'Set A', 'position' => 0]);
            $question->quiz_set_id = $set->id;

            if (! $question->position) {
                $question->position = (int) static::query()->where('quiz_set_id', $set->id)->count();
            }
        });
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(QuizSet::class, 'quiz_set_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
