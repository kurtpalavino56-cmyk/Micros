<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'competency_unit_id',
        'competency_level_id',
        'type',
        'title',
        'description',
        'passing_score',
        'status',
        'score',
        'feedback',
        // Phase 2 — manual/overall assessment grading trail.
        'submission_path',
        'reviewed_by',
        'reviewed_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompetencyUnit::class, 'competency_unit_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CompetencyLevel::class, 'competency_level_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
