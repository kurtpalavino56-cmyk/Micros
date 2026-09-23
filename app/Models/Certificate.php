<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'serial',
        'user_id',
        'course_id',
        'title',
        'file_path',
        'issued_at',
        // Phase 2 — revocation status + issuance-time snapshots, so an
        // already-issued certificate never silently changes if the course
        // is edited or versioned afterward.
        'status',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
        'microcredential_title_snapshot',
        'learning_outcomes_snapshot',
        'competencies_snapshot',
        'pqf_level_snapshot',
        'credit_equivalency_snapshot',
        'learning_hours_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
            'learning_outcomes_snapshot' => 'array',
            'competencies_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
