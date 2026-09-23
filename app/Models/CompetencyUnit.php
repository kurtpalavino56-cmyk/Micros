<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyUnit extends Model
{
    protected $fillable = [
        'competency_category_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetencyCategory::class, 'competency_category_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(CompetencyLevel::class)->orderBy('level_number');
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(CompetencyOutcome::class)->orderBy('order');
    }
}
