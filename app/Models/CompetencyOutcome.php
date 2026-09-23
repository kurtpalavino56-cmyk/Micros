<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyOutcome extends Model
{
    protected $fillable = ['competency_unit_id', 'description', 'order'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompetencyUnit::class, 'competency_unit_id');
    }
}
