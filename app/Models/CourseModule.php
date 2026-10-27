<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseModule extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'is_final',
    ];

    protected function casts(): array
    {
        return ['is_final' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'module_id')->orderBy('module_order')->orderBy('id');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class, 'module_id')->where('is_summative', true);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'module_id')->where('is_summative', false)->whereNull('lesson_id')->orderBy('module_order')->orderBy('id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LessonActivity::class, 'module_id')->whereNull('lesson_id')->orderBy('module_order')->orderBy('id');
    }

    public function nextContentOrder(): int
    {
        $lastOrder = max(
            (int) $this->lessons()->max('module_order'),
            (int) $this->activities()->where('is_active', true)->max('module_order'),
            (int) $this->quizzes()->where('is_active', true)->max('module_order'),
        );

        return $lastOrder + 1000000;
    }
}
