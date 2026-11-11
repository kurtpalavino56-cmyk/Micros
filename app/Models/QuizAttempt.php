<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_set_id',
        'shuffle_seed',
        'presentation',
        'question_snapshot',
        'score',
        'passed',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'shuffle_seed' => 'integer',
            'presentation' => 'array',
            'question_snapshot' => 'array',
        ];
    }

    protected $hidden = [
        'question_snapshot',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(QuizSet::class, 'quiz_set_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
