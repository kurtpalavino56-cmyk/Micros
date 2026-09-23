<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        // Phase 2 — verifiable issuance metadata. Deliberately no
        // stacking_framework_id: a badge may contribute to more than one
        // approved framework, computed rather than stored.
        'credential_uid',
        'status',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
        'competencies_snapshot',
        'learning_outcomes_snapshot',
        'pqf_level_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'revoked_at' => 'datetime',
            'competencies_snapshot' => 'array',
            'learning_outcomes_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
