<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptGrant extends Model
{
    protected $fillable = [
        'student_id',
        'quiz_id',
        'extra_attempts',
        'granted_by',
        'reason',
    ];

    protected function casts(): array
    {
        return ['extra_attempts' => 'integer'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
