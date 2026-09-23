<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRule extends Model
{
    protected $fillable = [
        'badge_id',
        'rule_type',
        'rule_value',
        'operator',
        'threshold',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
