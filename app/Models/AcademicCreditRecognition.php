<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The separate institutional academic-credit recognition workflow.
 *
 * Completely independent of `UserStackingProgress`. A framework reaching
 * `requirements_met` must never automatically create or advance a row
 * here — that remains a distinct, explicit process (Academic Unit
 * recommendation -> Dean endorsement -> Registrar recording), implemented
 * in Phase 7. No Phase 2 or later completion/stacking code is permitted
 * to write to this table automatically.
 */
class AcademicCreditRecognition extends Model
{
    protected $fillable = [
        'user_id',
        'stacking_framework_id',
        'status',
        'unit_reviewed_by',
        'unit_reviewed_at',
        'unit_recommendation',
        'dean_endorsed_by',
        'dean_endorsed_at',
        'registrar_recorded_by',
        'registrar_recorded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_reviewed_at' => 'datetime',
            'dean_endorsed_at' => 'datetime',
            'registrar_recorded_at' => 'datetime',
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

    public function unitReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unit_reviewed_by');
    }

    public function deanEndorser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_endorsed_by');
    }

    public function registrarRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrar_recorded_by');
    }
}
