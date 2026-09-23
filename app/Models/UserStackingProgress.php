<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores ONLY the outcome of a student's progress toward a
 * StackingFramework (in_progress / requirements_met). It intentionally
 * does not store a row per requirement — completed/required counts are
 * always computed live (see the Phase 6 stacking progress service) from
 * `stacking_framework_requirements` joined against the student's
 * `enrollments.completion_status = 'completed'`.
 */
class UserStackingProgress extends Model
{
    protected $fillable = [
        'user_id',
        'stacking_framework_id',
        'status',
        'requirements_met_at',
    ];

    protected function casts(): array
    {
        return [
            'requirements_met_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function framework(): BelongsTo
    {
        return $this->belongsTo(StackingFramework::class, 'stacking_framework_id');
    }
}
