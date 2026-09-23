<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'is_completed',
        'progress_percent',
        'progress_state',
        // Phase 2 — authoritative completion tracking. `progress_percent`
        // above stays a pure learning-progress metric; `completion_status`
        // is the only field a credential-issuing service may act on.
        'completion_status',
        'lessons_completed',
        'quizzes_completed',
        'quiz_mastery_met',
        // Independent from quiz_mastery_met: mastery of every competency
        // unit linked via learning_outcomes.competency_unit_id.
        'competency_mastery_met',
        'manual_assessment_status',
        'attendance_status',
        'performance_status',
        // 'administrative_status' is institutional/administrative sign-off
        // ONLY (never payment/tuition/fee-related). UPSKILL microcredentials
        // are free of charge — no completion gate reads from `payments`.
        'administrative_status',
        'faculty_verification_status',
        'faculty_verified_by',
        'faculty_verified_at',
        'academic_unit_confirmation_status',
        'academic_unit_confirmed_by',
        'academic_unit_confirmed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'is_completed' => 'boolean',
            'progress_state' => 'array',
            'lessons_completed' => 'boolean',
            'quizzes_completed' => 'boolean',
            'quiz_mastery_met' => 'boolean',
            'competency_mastery_met' => 'boolean',
            'faculty_verified_at' => 'datetime',
            'academic_unit_confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function facultyVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_verified_by');
    }

    public function academicUnitConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_unit_confirmed_by');
    }
}
