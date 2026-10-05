<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonActivity extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'activity_type',
        'instructions',
        'is_required',
        'max_points',
        'passing_percent',
        'rubric',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'rubric' => 'array',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LessonActivitySubmission::class);
    }
}
