<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyLevel extends Model
{
    protected $fillable = [
        'competency_unit_id',
        'title',
        'description',
        'level_number',
        'points',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompetencyUnit::class, 'competency_unit_id');
    }
}
