<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An institutionally approved arrangement under which multiple
 * microcredentials (courses) contribute to a single larger recognition.
 *
 * This is NOT the same thing as a `Pathway`. `Pathway` remains a
 * career/learning recommendation feature; `StackingFramework` is the
 * authoritative academic stacking structure.
 */
class StackingFramework extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'approving_academic_unit',
        'approved_by',
        'approved_at',
        'target_recognition',
        'completion_mode',
        'required_count',
        'cumulative_outcomes',
        'equivalent_course',
        'equivalent_units',
        'sequence_required',
        'credit_recognition_conditions',
        'pqf_level',
        'credit_equivalency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cumulative_outcomes' => 'array',
            'sequence_required' => 'boolean',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
            'completion_mode' => 'string',
            'required_count' => 'integer',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(StackingFrameworkRequirement::class)->orderBy('order');
    }

    public function courses()
    {
        return $this->requirements()->with('course');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserStackingProgress::class);
    }

    public function academicCreditRecognitions(): HasMany
    {
        return $this->hasMany(AcademicCreditRecognition::class);
    }
}
