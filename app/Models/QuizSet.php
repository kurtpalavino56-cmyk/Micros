<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One version of a quiz's questions (Set A, Set B, ...). Every set of a quiz
 * has the same number of questions; each attempt is served exactly one set.
 */
class QuizSet extends Model
{
    protected $fillable = [
        'quiz_id',
        'name',
        'position',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
