<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'heading',
        'subheading',
        'slug',
        'description',
        'skills',
        'related_skills',
        'objectives',
        'category',
        'program',
        'term',
        'level',
        'duration',
        'instructor',
        'created_by',
        'badge_id',
        'lessons_count',
        'enrolled_count',
        'passing_score',
        'is_featured',
        'is_published',
        'thumbnail_url',
        'approval_status',
        'is_approved',
        'approved_by',
        'approved_at',
        'denial_feedback',
        'change_note',
        'prerequisite_ids',
        'certificate_enabled',
        'certificate_title',
        'certificate_mode',
        'certificate_file',
        'certificate_signature',
        'certificate_signature_name',
        // Phase 2 — microcredential configuration metadata.
        'pqf_level',
        'target_learners',
        'delivery_mode',
        'learning_hours',
        'mastery_passing_percent',
        'credit_bearing',
        'credit_equivalency',
        'equivalent_course',
        'manual_assessment_required',
        'attendance_requirement_enabled',
        'attendance_requirement_note',
        'performance_requirement_enabled',
        'performance_requirement_note',
        'admin_requirement_enabled',
        'admin_requirement_note',
        // NOTE: 'admin_requirement_enabled' / 'admin_requirement_note' above
        // are institutional/administrative sign-off ONLY — never a payment,
        // tuition, or fee requirement. UPSKILL microcredentials are free of
        // charge; the `payments` table/model is not part of this workflow.
        'requires_faculty_verification',
        'requires_academic_unit_confirmation',
        // Configuration/UI flag only — NOT the authoritative stacking
        // relationship. See stacking_framework_requirements / requirements().
        'is_stackable',
        'version_number',
        'previous_version_id',
        'version_change_note',
        'version_approved_by',
        'version_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'related_skills' => 'array',
            'objectives' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'prerequisite_ids' => 'array',
            'certificate_enabled' => 'boolean',
            'credit_bearing' => 'boolean',
            'manual_assessment_required' => 'boolean',
            'attendance_requirement_enabled' => 'boolean',
            'performance_requirement_enabled' => 'boolean',
            'admin_requirement_enabled' => 'boolean',
            'requires_faculty_verification' => 'boolean',
            'requires_academic_unit_confirmation' => 'boolean',
            'is_stackable' => 'boolean',
            'version_number' => 'integer',
            'version_approved_at' => 'datetime',
        ];
    }

    /**
     * Display status used by the Faculty / Admin course cards.
     */
    public function statusLabel(): string
    {
        if ($this->approval_status === 'approved' || $this->is_approved) {
            return 'Published';
        }

        return match ($this->approval_status) {
            'pending' => 'Pending',
            'denied' => 'Denied',
            default => 'Draft',
        };
    }

    /** Courses that must be completed before enrolling in this one. */
    public function prerequisites()
    {
        $ids = $this->prerequisite_ids ?? [];

        return empty($ids)
            ? collect()
            : static::whereIn('id', $ids)->orderBy('title')->get();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['is_completed', 'progress_percent', 'enrolled_at'])
            ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(LearningOutcome::class)->orderBy('order');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'previous_version_id');
    }

    public function versionApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'version_approved_by');
    }

    /**
     * The stacking framework(s) this microcredential is actually a
     * requirement of. `is_stackable` is only a config flag — this
     * relationship (via stacking_framework_requirements) is authoritative.
     */
    public function stackingFrameworkRequirements(): HasMany
    {
        return $this->hasMany(StackingFrameworkRequirement::class);
    }
}
