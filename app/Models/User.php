<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 1;

    public const ROLE_FACULTY = 2;

    public const ROLE_STUDENT = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'username',
        'email',
        'email_verified_at',
        'password',
        'role_id',
        'role_label',
        'student_id',
        'user_code',
        'phone',
        'location',
        'avatar_url',
        'signature_image',
        'signature_text',
        'signature_font',
        'is_active',
        'date_of_birth',
        'gender',
        'education',
        'school',
        'about',
        'bio',
        'skills_have',
        'skills_want',
        'language',
        'timezone',
        'profile_completed',
        'notifications_read_at',
        'announcements_read_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'notifications_read_at' => 'datetime',
            'announcements_read_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'profile_completed' => 'boolean',
            'skills_have' => 'array',
            'skills_want' => 'array',
        ];
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    /**
     * Full display name — used by every Blade topbar as $user->name.
     */
    public function getNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }

    /**
     * Human-readable role name ('admin' | 'faculty' | 'student').
     */
    public function roleName(): string
    {
        return match ((int) $this->role_id) {
            self::ROLE_ADMIN => 'admin',
            self::ROLE_FACULTY => 'faculty',
            default => 'student',
        };
    }

    /**
     * Display label for profile pages (custom label wins, else role default).
     */
    public function displayRole(): string
    {
        if (! empty($this->role_label)) {
            return $this->role_label;
        }

        return match ((int) $this->role_id) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_FACULTY => 'Faculty',
            default => 'Student',
        };
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_ADMIN;
    }

    public function isFaculty(): bool
    {
        return (int) $this->role_id === self::ROLE_FACULTY;
    }

    public function isStudent(): bool
    {
        return (int) $this->role_id === self::ROLE_STUDENT;
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['is_completed', 'progress_percent', 'enrolled_at'])
            ->withTimestamps();
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function competencyProgresses(): HasMany
    {
        return $this->hasMany(CompetencyProgress::class);
    }

    public function stackingProgress(): HasMany
    {
        return $this->hasMany(UserStackingProgress::class);
    }

    public function academicCreditRecognitions(): HasMany
    {
        return $this->hasMany(AcademicCreditRecognition::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }
}
