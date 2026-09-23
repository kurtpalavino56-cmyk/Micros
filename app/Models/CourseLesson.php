<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'type',
        'duration',
        'content',
        'file_url',
        'file_name',
        'order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class, 'lesson_id');
    }

    /**
     * Short "Video · 15m" style label used by the Faculty manage screen.
     */
    public function metaLabel(): string
    {
        $duration = trim((string) $this->duration);

        return $this->type.($duration !== '' && $duration !== '0'
            ? ' · '.(str_ends_with($duration, 'm') ? $duration : $duration.'m')
            : '');
    }
}
