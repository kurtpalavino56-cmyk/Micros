<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pathway extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'steps',
        'destination',
        'destination_color',
        'connector_color',
        'recommendations',
        'desired_title',
        'desired_competencies',
        'current_competencies',
        'missing_competencies',
        'readiness_percent',
        'readiness_label',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'steps' => 'array',
            'recommendations' => 'array',
            'desired_competencies' => 'array',
            'current_competencies' => 'array',
            'missing_competencies' => 'array',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'pathway_courses')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('pathway_courses.order');
    }

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class);
    }
}
