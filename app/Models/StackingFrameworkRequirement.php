<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The single authoritative link between a StackingFramework and a
 * microcredential (Course). A course may appear in many frameworks; a
 * framework may require many courses. Never hard-code stacking
 * relationships in PHP — they must always be read from this table.
 */
class StackingFrameworkRequirement extends Model
{
    protected $fillable = [
        'stacking_framework_id',
        'course_id',
        'order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function framework(): BelongsTo
    {
        return $this->belongsTo(StackingFramework::class, 'stacking_framework_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
