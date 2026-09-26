<?php

namespace App\Http\Controllers;

use App\Models\AcademicCreditRecognition;
use App\Models\AnalyticsEvent;
use App\Models\Certificate;
use App\Models\CompetencyProgress;
use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\Pathway;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StackingFramework;
use App\Models\UserBadge;
use App\Services\AcademicCreditRecognitionService;
use App\Services\CourseCompletionService;
use App\Services\MicrocredentialCompletionService;
use App\Services\QuizAttemptService;
use App\Services\StackingProgressService;
use App\Services\StudentProgressService;
use App\Support\CertificateBuilder;
use App\Support\SchoolCatalog;
use App\Support\SkillCatalog;
use App\Support\UserPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * StudentController — every Student_* page, now fed entirely from the
 * database. Every method passes the same variable shapes the Blade views
 * were built against (plain objects / collections), so no view changes
 * were needed.
 */
class StudentController extends Controller
{
    public function __construct(
        private StudentProgressService $progressService,
        private QuizAttemptService $quizAttempts,
        private CourseCompletionService $courseCompletion,
        private MicrocredentialCompletionService $completionService,
        private StackingProgressService $stackingProgress,
        private AcademicCreditRecognitionService $academicCreditRecognition,
    ) {}

    /**
     * Selectable skills shown as checkbox lists in the About Me form
     * ("Skills you already have" / "Skills you want to learn").
     */
    // Skills now come from App\Support\SkillCatalog so that this list and
    // the faculty "Subject is Related on" picker stay identical — matching
    // is by string, so any divergence breaks recommendations.

    // ── Dashboard ─────────────────────────────────────────────────────────

    public function onboarding()
    {
        $auth = Auth::user();

        if ($auth->profile_completed) {
            return redirect()->route('dashboard');
        }

        return view('student.onboarding', [
            'user' => UserPresenter::student($auth),
            'pathways' => Pathway::query()->where('is_active', true)->with('courses')->orderBy('name')->get(),
            'skill_options' => SkillCatalog::all(),
            'skill_groups' => SkillCatalog::groups(),
        ]);
    }

    public function dashboard()
    {
        $auth = Auth::user();

        if (! $auth->profile_completed) {
            return redirect()->route('onboarding.show');
        }

        // Eager-load quiz questions as well: the progress sync below reads
        // per-quiz question counts and newest-question timestamps, and
        // lazy-loading those cost several queries per module per course.
        $enrollments = Enrollment::with('course.modules.quiz.questions')
            ->where('user_id', $auth->id)
            ->get();

        // A quiz or module deleted by faculty changes the denominator, so a
        // stored percentage can describe content that no longer exists.
        $enrollments->each(fn (Enrollment $e) => $this->syncEnrollmentProgress($e->course, $e));
        $stackingFrameworks = $this->stackingProgress->progressForUser((int) $auth->id);

        $courses = $enrollments->map(fn (Enrollment $e) => (object) [
            'id' => $e->course_id,
            'title' => $e->course->title ?? 'Course',
            'category' => $e->course->category ?? null,
            'thumbnail_url' => ! empty($e->course->thumbnail_url) ? asset($e->course->thumbnail_url) : null,
            'progress_percent' => (int) $e->progress_percent,
            'is_completed' => $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED,
        ])->values();

        // Organized course lists for the dashboard.
        $inProgressCourses = $courses->where('is_completed', false)->values();
        $completedCourses = $courses->where('is_completed', true)->values();

        $badges = UserBadge::with('badge')
            ->where('user_id', $auth->id)
            ->latest('earned_at')
            ->get()
            ->map(fn (UserBadge $ub) => (object) ['name' => $ub->badge->name ?? 'Badge'])
            ->values();

        $progress = $enrollments
            ->sortByDesc('updated_at')
            ->map(fn (Enrollment $e) => (object) [
                'title' => $e->course->title ?? 'Course',
                'progress_percent' => (int) $e->progress_percent,
                // Official completion only: completion_status is the
                // authority; the legacy is_completed column is not read.
                'is_completed' => $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED,
                'completed_lessons' => count((array) ($e->progress_state['completed_lessons'] ?? [])),
            ])->values();

        return view('student.dashboard', [
            'user' => UserPresenter::student($auth),
            'stats' => [
                'active_courses' => $enrollments->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'completed' => $enrollments->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'badges_earned' => $badges->count(),
                'certificates' => $auth->certificates()->count(),
            ],
            'courses' => $courses,
            'inProgressCourses' => $inProgressCourses,
            'completedCourses' => $completedCourses,
            'progress' => $progress,
            'badges' => $badges,
            'show_about_form' => ! (bool) ($auth->profile_completed ?? false),
            'skill_options' => SkillCatalog::all(),
            'skill_groups' => SkillCatalog::groups(),
            'stackingFrameworks' => $stackingFrameworks,
        ]);
    }

    // ── Enrolled Courses page ────────────────────────────────────────────

    public function enrolledCourses()
    {
        $auth = Auth::user();

        $enrollments = Enrollment::with('course.modules.quiz.questions')
            ->where('user_id', $auth->id)
            ->get();

        // A quiz or module deleted by faculty changes the denominator, so a
        // stored percentage can describe content that no longer exists.
        $enrollments->each(fn (Enrollment $e) => $this->syncEnrollmentProgress($e->course, $e));

        $courses = $enrollments->map(fn (Enrollment $e) => (object) [
            'id' => $e->course_id,
            'title' => $e->course->title ?? 'Course',
            'category' => $e->course->category ?? null,
            'thumbnail_url' => ! empty($e->course->thumbnail_url) ? asset($e->course->thumbnail_url) : null,
            'progress_percent' => (int) $e->progress_percent,
            'is_completed' => $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED,
        ])->values();

        return view('student.courses.enrolled', [
            'user' => UserPresenter::student($auth),
            'inProgressCourses' => $courses->where('is_completed', false)->values(),
            'completedCourses' => $courses->where('is_completed', true)->values(),
        ]);
    }

    public function stackingProgress()
    {
        $auth = Auth::user();
        $frameworks = $this->stackingProgress->progressForUser((int) $auth->id);

        // N+1 fix: recognition records were fetched one query per framework.
        $recognitions = AcademicCreditRecognition::query()
            ->where('user_id', $auth->id)
            ->whereIn('stacking_framework_id', $frameworks->pluck('framework.id')->all() ?: [0])
            ->get()
            ->keyBy('stacking_framework_id');

        $frameworks = $frameworks->map(function (array $item) use ($auth, $recognitions) {
            $framework = $item['framework'];
            $record = $recognitions->get($framework->id);

            $item['recognition'] = $record;
            $item['can_request_recognition'] = $this->academicCreditRecognition->canRequestRecognition($auth, $framework);
            $item['is_recognition_pending'] = $record && $record->status === 'pending';

            return $item;
        });

        return view('student.stacking-progress', [
            'user' => UserPresenter::student($auth),
            'frameworks' => $frameworks,
        ]);
    }

    public function requestAcademicCreditRecognition(int $frameworkId)
    {
        $auth = Auth::user();
        $framework = StackingFramework::query()->findOrFail($frameworkId);

        if (! $this->academicCreditRecognition->canRequestRecognition($auth, $framework)) {
            return back()->withErrors(['recognition' => 'This framework is not eligible for academic credit recognition yet.']);
        }

        $this->academicCreditRecognition->requestRecognition($auth, $framework, 'Student initiated recognition request.');

        return back()->with('success', 'Academic credit recognition request submitted.');
    }

    // ── First-login About Me ─────────────────────────────────────────────

    public function completeProfile(Request $request)
    {
        $auth = Auth::user();

        // Onboarding runs once. If the profile is already complete, send the
        // student on rather than re-processing the form.
        if ($auth->profile_completed) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', 'max:50'],
            'education' => ['required', 'string', 'max:255'],
            'school' => ['required', 'string', 'max:255'],
            'school_other' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'career_goal' => ['required', 'string', 'max:255'],
            'pathway_id' => ['nullable', 'integer', 'exists:pathways,id'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'skills_have' => ['nullable'],
            'skills_have.*' => ['string', 'max:60'],
            'skills_want' => ['nullable'],
            'skills_want.*' => ['string', 'max:60'],
        ]);

        // Accepts either the checkbox array (About Me form) or a
        // comma-separated string (Edit Profile form).
        $splitSkills = function ($raw): array {
            $items = is_array($raw) ? $raw : preg_split('/[,;\n]+/', (string) $raw);

            return collect($items)
                ->map(fn ($s) => trim((string) $s))
                ->filter()
                ->unique()
                ->values()
                ->all();
        };

        $skillsHave = $splitSkills($data['skills_have'] ?? null);
        $skillsWant = $splitSkills($data['skills_want'] ?? null);
        $bio = $data['bio'] ?? null;

        $attrs = [
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'education' => $data['education'],
            'school' => $this->resolveSchool($data),
            'address' => $data['address'],
            'career_goal' => $data['career_goal'],
            // nullable fields are dropped from validated data when absent —
            // read with a fallback so onboarding can't 500 here.
            'pathway_id' => $data['pathway_id'] ?? null,
            'bio' => $bio,
            'profile_completed' => true,
        ];

        if (Schema::hasColumn('users', 'skills_have') && Schema::hasColumn('users', 'skills_want')) {
            // Preferred storage: dedicated JSON columns (run php artisan migrate).
            $attrs['skills_have'] = $skillsHave;
            $attrs['skills_want'] = $skillsWant;
        } else {
            // Fallback so onboarding never fails before the migration is run:
            // keep the skills inside the existing bio text.
            $extras = [];
            if ($skillsHave !== []) {
                $extras[] = 'Skills I have: '.implode(', ', $skillsHave);
            }
            if ($skillsWant !== []) {
                $extras[] = 'Skills I want to learn: '.implode(', ', $skillsWant);
            }
            if ($extras !== []) {
                $attrs['bio'] = trim(($bio ?? '').($bio ? "\n\n" : '').implode("\n", $extras));
            }
        }

        $auth->forceFill($attrs)->save();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Profile completed — your recommendations are now personalized.');
    }

    // ── Browse Courses ────────────────────────────────────────────────────

    public function browse(Request $request)
    {
        $auth = Auth::user();

        $filters = [
            'q' => trim((string) $request->input('q', '')) ?: null,
            'category' => $request->input('category') ?: null,
            'level' => $request->input('level') ?: null,
            'recommended' => $request->boolean('recommended'),
        ];

        // Skills the student listed on their profile, both directions.
        $skills = collect(array_merge(
            (array) ($auth->skills_have ?? []),
            (array) ($auth->skills_want ?? [])
        ))->filter()->map(fn ($s) => trim((string) $s))->filter()->unique()->values();

        $query = Course::query()
            ->where('is_published', true)
            // N+1 fix: the card mapper fell back to lessons()->count() per
            // course. Count them all in the main query instead.
            ->withCount('lessons')
            ->when($filters['q'], function ($q) use ($filters) {
                $term = '%'.$filters['q'].'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('instructor', 'like', $term);
                });
            })
            ->when($filters['category'], fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['level'], fn ($q) => $q->where('level', $filters['level']))
            ->orderByDesc('is_featured')
            ->orderBy('title');

        $matched = $query->get();

        // Recommended is matched in PHP, not SQL. related_skills is a JSON
        // column, and a LIKE against it is unreliable across drivers (and
        // matches substrings — "Syntax" inside "Syntaxes"). Decoding the
        // array and comparing normalised values is exact and portable.
        if ($filters['recommended'] && $skills->isNotEmpty()) {
            $wanted = $skills->map(fn ($s) => $this->normalizeSkill($s))->filter()->all();

            $matched = $matched->filter(function (Course $c) use ($wanted) {
                $courseSkills = collect(array_merge(
                    FacultyController::relatedSkillsOf($c),
                    (array) ($c->skills ?? [])
                ))->map(fn ($s) => $this->normalizeSkill($s))->filter()->all();

                // Exact skill match is the primary signal.
                if (array_intersect($wanted, $courseSkills)) {
                    return true;
                }

                // Fallback for courses saved before "Subject is Related on"
                // existed: look for the skill in the course's own text.
                $haystack = $this->normalizeSkill(
                    ($c->title ?? '').' '.($c->description ?? '').' '.($c->category ?? '')
                );

                foreach ($wanted as $skill) {
                    if ($skill !== '' && str_contains($haystack, $skill)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $courses = $matched->map(fn (Course $c) => (object) [
            'id' => $c->id,
            'title' => $c->title,
            'description' => $c->description,
            'category' => $c->category,
            'level' => $c->level,
            'instructor' => $c->instructor,
            'duration' => $c->duration,
            'lessons_count' => (int) $c->lessons_count,
            'thumbnail_url' => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
        ])->values();

        $categories = Course::query()->where('is_published', true)
            ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
        $levels = Course::query()->where('is_published', true)
            ->whereNotNull('level')->distinct()->orderBy('level')->pluck('level')->all();

        return view('student.courses.browse', [
            'user' => UserPresenter::student(Auth::user()),
            'courses' => $courses,
            'categories' => $categories,
            'levels' => $levels ?: ['Beginner', 'Intermediate', 'Advanced'],
            'filters' => $filters,
        ]);
    }

    // ── Course Description ────────────────────────────────────────────────

    public function show(int $id)
    {
        $auth = Auth::user();
        // modules.quiz.questions added: syncEnrollmentProgress() walks every
        // module quiz and would otherwise lazy-load quiz + questions per
        // module on each visit.
        $course = Course::with(['modules.lessons', 'modules.quiz.questions', 'quizzes.questions', 'creator'])->findOrFail($id);

        $instructor = $course->creator;

        // The description page lists the course's lesson rows.
        $modules = $course->lessons->map(fn (CourseLesson $l) => (object) [
            'title' => $l->title,
            'description' => $l->type.': '.trim((string) $l->duration),
            'type' => $l->type,
            'duration' => $l->duration,
        ])->values();

        $quizModel = $course->quizzes->first();
        $quiz = $quizModel ? (object) [
            'id' => $quizModel->id,
            'title' => $quizModel->title,
            'questions_count' => $quizModel->questions->count(),
            'passing_score' => $quizModel->passing_score,
        ] : null;

        $enrollment = Enrollment::where('user_id', $auth->id)->where('course_id', $course->id)->first();
        $progressPercent = $this->syncEnrollmentProgress($course, $enrollment);
        $completionReady = $enrollment
            ? $this->courseCompletion->isReady($course, $enrollment)
            : false;

        $prerequisiteIds = collect($course->prerequisite_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $completedPrerequisiteIds = Enrollment::query()
            ->where('user_id', $auth->id)
            ->whereIn('course_id', $prerequisiteIds)
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id);
        $prerequisitesMet = $prerequisiteIds->diff($completedPrerequisiteIds)->isEmpty();
        $prerequisites = Course::query()
            ->whereIn('id', $prerequisiteIds)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Course $prerequisite) => (object) [
                'id' => $prerequisite->id,
                'title' => $prerequisite->title,
                'completed' => $completedPrerequisiteIds->contains((int) $prerequisite->id),
            ]);
        $missingPrerequisiteTitles = $prerequisiteIds
            ->diff($completedPrerequisiteIds)
            ->map(function ($missingId) use ($prerequisites) {
                return $prerequisites->firstWhere('id', (int) $missingId)?->title
                    ?? 'Unavailable prerequisite course';
            })
            ->values();

        return view('student.courses.description', [
            'user' => UserPresenter::student($auth),
            'course' => (object) [
                'id' => $course->id,
                'title' => $course->title,
                'heading' => $course->heading,
                'subheading' => $course->subheading,
                'description' => $course->description,
                'category' => $course->category,
                'level' => $course->level,
                'is_featured' => (bool) $course->is_featured,
                'instructor' => $course->instructor,
                'duration' => $course->duration,
                'lessons_count' => (int) ($course->lessons_count ?: $course->lessons->count()),
                'enrolled_count' => (int) ($course->enrolled_count ?: $course->enrollments()->count()),
                'thumbnail_url' => $course->thumbnail_url ? asset($course->thumbnail_url) : null,
                'passing_score' => (int) $course->passing_score,
                'skills' => $course->skills ?? [],
                'objectives' => $course->objectives ?? [],
            ],
            'instructor_detail' => (object) [
                'name' => $instructor?->name ?? $course->instructor ?? 'Faculty',
                'department' => 'College of Information Technology',
                'bio' => $instructor?->bio ?? 'Faculty Member of the UPSKILL platform.',
                'avatar_url' => $instructor?->avatar_url,
            ],
            'modules' => $modules,
            'quiz' => $quiz,
            'is_enrolled' => (bool) $enrollment,
            'is_completed' => $enrollment?->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED,
            'completion_ready' => $completionReady,
            'progress_percent' => $progressPercent,
            'prerequisites' => $prerequisites,
            'prerequisites_met' => $prerequisitesMet,
            'missing_prerequisite_titles' => $missingPrerequisiteTitles,
        ]);
    }

    // ── Enrollment ────────────────────────────────────────────────────────

    public function enroll(int $id)
    {
        $auth = Auth::user();
        $course = Course::findOrFail($id);
        $requiredPrerequisiteIds = collect($course->prerequisite_ids ?? [])
            ->map(fn ($prerequisiteId) => (int) $prerequisiteId)
            ->filter(fn ($prerequisiteId) => $prerequisiteId > 0)
            ->unique()
            ->values();

        if ($requiredPrerequisiteIds->isNotEmpty()) {
            $completedPrerequisiteIds = Enrollment::query()
                ->where('user_id', $auth->id)
                ->whereIn('course_id', $requiredPrerequisiteIds)
                ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->pluck('course_id')
                ->map(fn ($prerequisiteId) => (int) $prerequisiteId);
            $missingPrerequisiteIds = $requiredPrerequisiteIds->diff($completedPrerequisiteIds);

            if ($missingPrerequisiteIds->isNotEmpty()) {
                $missingTitles = Course::query()
                    ->whereIn('id', $missingPrerequisiteIds)
                    ->orderBy('title')
                    ->pluck('title')
                    ->all();
                if (count($missingTitles) < $missingPrerequisiteIds->count()) {
                    $missingTitles[] = 'an unavailable prerequisite course';
                }

                return back()->withErrors([
                    'prerequisites' => 'Complete these prerequisite courses before enrolling: '
                        .implode(', ', $missingTitles).'.',
                ]);
            }
        }

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $auth->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percent' => 0, 'is_completed' => false]
        );

        if ($enrollment->wasRecentlyCreated) {
            AnalyticsEvent::create([
                'user_id' => $auth->id,
                'event_type' => 'enrollment',
                'entity_type' => 'course',
                'entity_id' => $course->id,
                'metadata' => ['detail' => $auth->name.' enrolled in '.$course->title],
                'occurred_at' => now(),
            ]);
        }

        $course->enrolled_count = $course->enrollments()->count();
        $course->save();

        return redirect()->route('courses.learn', $course->id);
    }

    /**
     * Learning / enrollment screen (Student_Course_Enrollment).
     */
    public function learn(int $id)
    {
        $auth = Auth::user();
        $course = Course::with(['modules.lessons', 'modules.quiz.questions'])->findOrFail($id);

        $modules = $course->modules->map(fn ($m) => (object) [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description ?? '',
            'lessons' => $m->lessons->map(fn (CourseLesson $l) => (object) [
                'id' => $l->id,
                'title' => $l->title,
                'type' => 'Text',
                'duration' => $l->duration,
                'description' => $l->content ?: $l->title,
                'content' => $l->content ?? '',
                'thumbnail_url' => null,
            ])->values(),
            'quiz' => $m->quiz ? (object) [
                'id' => $m->quiz->id,
                'title' => $m->quiz->title,
                'questions_count' => $m->quiz->questions->count(),
                'passing_score' => $m->quiz->passing_score,
                // Needed by the countdown in the player. Without this the
                // view only ever saw null, so every quiz looked untimed.
                'time_limit' => (int) ($m->quiz->time_limit ?? 0),
                'attempts' => $m->quiz->attempts,
                'instructions' => $m->quiz->instructions,
                'questions' => $m->quiz->questions->map(function ($q) {
                    $opts = collect($q->options ?? [])->values()->map(fn ($text, $i) => [
                        'letter' => chr(65 + $i),
                        'text' => (string) $text,
                    ])->all();
                    return [
                        'id' => (int) $q->id,
                        'label' => Str::limit((string) $q->question, 40, ''),
                        'question' => (string) $q->question,
                        'options' => $opts,
                    ];
                })->values()->all(),
            ] : null,
        ])->values();

        $enrollment = Enrollment::where('user_id', $auth->id)->where('course_id', $course->id)->first();

        // Previously saved player state, so returning students pick up
        // exactly where they left off (completed lessons + quiz scores).
        $state = $enrollment?->progress_state ?? [];

        // Server-side retake cooldowns: a failed quiz locks for 24 hours
        // based on quiz_attempts, so it survives page changes, new devices
        // and cleared browser storage.
        $quizUnlocks = [];
        $quizAttempts = [];   // moduleIdx => attempt budget for this student
        $staleScores = [];   // module indexes whose saved score predates a quiz edit
        foreach ($course->modules as $mIdx => $m) {
            if (! $m->quiz) {
                continue;
            }

            // If faculty edited the quiz after this student's most recent
            // attempt, the saved score belongs to questions that no longer
            // exist. Drop it so the player opens a fresh quiz instead of a
            // read-only review with the answers revealed.
            $editedAt = $this->quizLastEditedAt($m->quiz);
            $lastAny = QuizAttempt::where('user_id', $auth->id)
                ->where('quiz_id', $m->quiz->id)
                ->latest('submitted_at')
                ->first();
            $lastAnyAt = $lastAny ? ($lastAny->submitted_at ?? $lastAny->created_at) : null;

            if ($editedAt && (! $lastAnyAt || $lastAnyAt->lt($editedAt))) {
                $staleScores[] = (string) $mIdx;
            }
            $failed = QuizAttempt::where('user_id', $auth->id)
                ->where('quiz_id', $m->quiz->id)
                ->where('passed', false)
                ->latest('submitted_at')
                ->first();
            $passed = QuizAttempt::where('user_id', $auth->id)
                ->where('quiz_id', $m->quiz->id)
                ->where('passed', true)
                ->exists();

            // Attempt budget. 0 = unlimited. Once it is used up the quiz is
            // closed for good: review only, no countdown.
            $allowed = $this->attemptsAllowed($m->quiz);
            $used = $this->attemptsUsed($m->quiz, $auth->id);
            $exhausted = $allowed > 0 && $used >= $allowed;

            $quizAttempts[(string) $mIdx] = [
                'allowed' => $allowed,
                'used' => $used,
                'remaining' => $allowed > 0 ? max(0, $allowed - $used) : null,
                'exhausted' => $exhausted,
            ];

            if ($failed && ! $passed && ! $exhausted) {
                $at = $failed->submitted_at ?? $failed->created_at;
                // Attempt predates the quiz's last edit → questions changed,
                // so the lock no longer applies.
                $editedAt = $this->quizLastEditedAt($m->quiz);
                $stale = $editedAt && $at && $at->lt($editedAt);
                if (! $stale && $at && $at->copy()->addHours(24)->isFuture()) {
                    $quizUnlocks[(string) $mIdx] = $at->copy()->addHours(24)->getTimestamp() * 1000;
                }
            }
        }

        // Faculty may have added or removed questions since the student last
        // played. Recompute against the CURRENT quizzes (minus any scores
        // invalidated by an edit) and persist it, so the course cards, the
        // dashboard and this player all quote the same number.
        $liveScores = array_diff_key($state['module_scores'] ?? [], array_flip($staleScores));

        // Only keep scores backed by an actual attempt on the quiz sitting
        // in that slot now — module_scores is keyed by index, so a newly
        // added module would otherwise inherit a deleted one's score.
        $liveScores = $this->earnedModuleScores($course, $liveScores, $auth->id);

        $livePercent = $this->calculateProgressPercent($course, $liveScores);
        $liveLessons = $this->resolveCompletedLessons(
            $course,
            array_values($state['completed_lessons'] ?? []),
            $enrollment
        );

        $stateChanged = $liveLessons !== array_values($state['completed_lessons'] ?? []);

        if ((int) $enrollment->progress_percent !== $livePercent || $stateChanged) {
            $enrollment->progress_percent = $livePercent;
            $enrollment->progress_state = [
                'completed_lessons' => $liveLessons,
                'module_scores' => $liveScores,
            ];
            // Progress never sets is_completed / completion_status (see
            // MicrocredentialCompletionService::evaluate()).
            $enrollment->save();
        }

        return view('student.courses.enrollment', [
            'user' => UserPresenter::student($auth),
            'course' => (object) [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category,
                'thumbnail_url' => $course->thumbnail_url ? asset($course->thumbnail_url) : null,
                'progress_percent' => $livePercent,
            ],
            'modules' => $modules,
            'current_lesson' => $modules->first()?->lessons->first(),
            'current_module' => $modules->first(),
            'total_lessons' => $modules->sum(fn ($m) => $m->lessons->count()),
            'badge_count' => $auth->badges()->count(),
            'progress_percent' => $livePercent,
            'saved_progress' => [
                'completed_lessons' => $liveLessons,
                'module_scores' => (object) array_diff_key(
                    $state['module_scores'] ?? [],
                    array_flip($staleScores)
                ),
            ],
            'quiz_unlocks' => (object) $quizUnlocks,
            'quiz_attempts' => (object) $quizAttempts,
        ]);
    }

    /**
     * Individual lesson deep-link — the learning screen handles playback,
     * so this stays a redirect (as in the original routes).
     */
    public function lesson(int $courseId, int $lessonId)
    {
        return redirect()->route('courses.learn', $courseId);
    }

    public function quiz(int $id)
    {
        return redirect()->route('dashboard');
    }

    // ── Real-time progress saving ───────────────────────────────────────

    /**
     * Called via fetch() from the course player every time the student
     * marks a lesson complete or submits a quiz. Persists the exact player
     * state (completed lessons + per-module quiz scores) and keeps
     * enrollments.progress_percent current, so the dashboard, browse and
     * course-description pages always show the real percentage.
     */
    /**
     * Drop module scores the student did not actually earn on the quiz that
     * currently occupies that slot.
     *
     * progress_state keys module_scores by module INDEX. When faculty
     * deletes a module or inserts a new one, the indexes shift and a fresh
     * module silently inherits the previous occupant's score — a brand new
     * quiz would show as already answered. A score only counts when the
     * student has a real QuizAttempt on that quiz since it was last edited.
     *
     * @param  array<string,int>  $scores
     * @return array<string,int>
     */
    private function earnedModuleScores($course, array $scores, int $userId): array
    {
        return $this->progressService->earnedModuleScores($course, $scores, $userId);
    }

    /**
     * Recompute and persist an enrollment's progress against the course as
     * it exists NOW, returning the fresh percentage.
     *
     * Progress is (correct answers / current questions). When faculty
     * deletes a quiz or a module the stored percentage describes content
     * that no longer exists, so it has to be recalculated wherever it is
     * displayed — not only inside the course player.
     */
    private function syncEnrollmentProgress($course, ?Enrollment $enrollment): int
    {
        return $this->progressService->syncEnrollmentProgress($course, $enrollment);
    }

    /**
     * How many attempts a quiz allows.
     *
     * quizzes.attempts is free text set by faculty ("1 Attempt", "3",
     * "Unlimited", blank...). Anything unparseable means a single attempt,
     * which is the safer default: a one-shot quiz closes after submission.
     *
     * @return int attempts allowed; 0 means unlimited
     */
    private function attemptsAllowed($quiz): int
    {
        return $this->quizAttempts->attemptsAllowed($quiz);
    }

    /**
     * Attempts a student has already used on a quiz, counting only those
     * made since faculty last edited it — an edit starts the count over.
     */
    private function attemptsUsed($quiz, int $userId): int
    {
        return $this->quizAttempts->attemptsUsed($quiz, $userId);
    }

    /**
     * When was this quiz last changed by its faculty owner?
     *
     * storeQuiz() deletes and recreates every question on save, so the newest
     * question timestamp is the most reliable signal — and unlike the quiz
     * row's updated_at it also covers quizzes edited before storeQuiz()
     * started touching the parent row.
     */
    /**
     * Recalculate a course's progress from the student's quiz scores and the
     * quiz questions that exist *right now*.
     *
     * Progress is (correct answers / total questions), so it has to be
     * recomputed whenever faculty adds or removes questions — otherwise the
     * stored percentage describes a version of the course that no longer
     * exists. Each module's score is capped at its current question count so
     * a score of 5 on a quiz trimmed to 2 questions can't report 250%.
     *
     * @param  array<string,int>  $moduleScores  moduleIndex => correct answers
     */
    /**
     * Translate saved completed-lesson keys into current lesson IDs.
     *
     * Progress used to record lessons by POSITION ("moduleIdx-lessonIdx"),
     * so deleting a lesson and adding a new one in its place made the new
     * lesson inherit the old one's completed tick. Completion is now keyed
     * by lesson ID; this converts legacy positional keys so existing
     * students don't lose genuine progress, and drops anything pointing at
     * a lesson that no longer exists or was created after the student last
     * saved (which they cannot have completed).
     *
     * @param  array<int,string>  $saved
     * @return list<string>
     */
    private function resolveCompletedLessons($course, array $saved, $enrollment): array
    {
        return $this->progressService->resolveCompletedLessons($course, $saved, $enrollment);
    }

    /**
     * Total study hours across a student's enrolled courses.
     *
     * This used to be enrollments * 7 — a flat seven hours per course
     * regardless of its actual length, so one enrolment always read "7".
     * Uses the course's own duration where it is expressed in hours, and
     * otherwise falls back to summing its lesson durations (minutes).
     */
    private function enrolledHours($enrollments): int
    {
        $hours = 0.0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) {
                continue;
            }

            $duration = trim((string) $course->duration);

            // "6h", "6 h", "6 hours" or a bare "6"
            if (preg_match('/^([\d.]+)\s*(h|hr|hour)?s?$/i', $duration, $m)) {
                $hours += (float) $m[1];

                continue;
            }

            // Anything else ("4 weeks", "" ...) → add up the lesson minutes.
            $minutes = 0.0;
            foreach ($course->lessons as $lesson) {
                $minutes += (float) preg_replace('/[^\d.]/', '', (string) $lesson->duration);
            }
            $hours += $minutes / 60;
        }

        return (int) round($hours);
    }

    /**
     * Normalise a skill for comparison: lowercase, collapse whitespace and
     * strip punctuation, so "Web Development", "web development" and
     * "Web-Development" all match.
     */
    private function normalizeSkill($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function calculateProgressPercent($course, array $moduleScores): int
    {
        return $this->progressService->calculateProgressPercent($course, $moduleScores);
    }

    private function quizLastEditedAt($quiz)
    {
        return $this->quizAttempts->lastEditedAt($quiz);
    }

    public function saveProgress(int $id, Request $request)
    {
        $data = $request->validate([
            'percent' => 'nullable|integer|min:0|max:100',
            'completed_lessons' => 'nullable|array',
            'completed_lessons.*' => 'string|max:20',
            'module_scores' => 'nullable|array',
            'module_scores.*' => 'integer|min:0',
            'quiz_results' => 'nullable|array',
            'quiz_results.*.answers' => 'nullable|array',
            'quiz_results.*.answers.*' => 'nullable|string|size:1',
            'quiz_results.*.score' => 'prohibited',
            'retake_check' => 'nullable|integer|min:0',
        ]);

        $student = Auth::user();
        $course = Course::with('modules.lessons')->findOrFail($id);
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percent' => 0, 'is_completed' => false]
        );

        if (isset($data['retake_check'])) {
            $module = $course->modules->get((int) $data['retake_check']);
            $quiz = $module?->quiz;
            if (! $quiz) {
                return response()->json(['ok' => true, 'quiz_unlocks' => (object) []]);
            }

            $status = $this->quizAttempts->retakeStatus($quiz, $student->id);
            if ($status['exhausted']) {
                return response()->json([
                    'ok' => false,
                    'exhausted' => true,
                    'allowed' => $status['allowed'],
                    'used' => $status['used'],
                ]);
            }

            return response()->json([
                'ok' => true,
                'quiz_unlocks' => $status['unlock']
                    ? [(string) $data['retake_check'] => $status['unlock']]
                    : (object) [],
            ]);
        }

        $moduleScores = $this->progressService->earnedModuleScores(
            $course,
            (array) ($data['module_scores'] ?? []),
            (int) $student->id
        );
        $percent = $this->progressService->calculateProgressPercent($course, $moduleScores);
        $completedKeys = array_values($data['completed_lessons'] ?? []);
        $enrollment->progress_percent = $percent;
        $enrollment->progress_state = [
            'completed_lessons' => $this->progressService->resolveCompletedLessons($course, $completedKeys, $enrollment),
            'module_scores' => $moduleScores,
        ];
        $enrollment->save();
        $this->progressService->persistLessonCompletions($course, $student, $completedKeys);
        $this->progressService->syncLessonsCompletedFlag($course, $enrollment);

        $quizUnlocks = [];
        $quizSubmissions = [];
        foreach (($data['quiz_results'] ?? []) as $moduleIndex => $result) {
            $module = $course->modules->get((int) $moduleIndex);
            $quiz = $module?->quiz;
            if (! $quiz || $quiz->questions->count() === 0) {
                continue;
            }

            $submission = $this->quizAttempts->submit($student, $quiz, $result);
            $quizSubmissions[(string) $moduleIndex] = $submission;
            if ($submission['blocked']) {
                unset($moduleScores[(string) $moduleIndex], $moduleScores[(int) $moduleIndex]);
                if ($submission['unlock'] !== null) {
                    $quizUnlocks[(string) $moduleIndex] = $submission['unlock'];
                }
            } elseif ($submission['unlock'] !== null) {
                $quizUnlocks[(string) $moduleIndex] = $submission['unlock'];
            } else {
                // The score comes from the server-side graded attempt. Never
                // copy a score from the browser into progress state.
                $moduleScores[(string) $moduleIndex] = (int) ($submission['correct'] ?? 0);
            }
        }

        $moduleScores = $this->progressService->earnedModuleScores(
            $course,
            $moduleScores,
            (int) $student->id
        );
        $percent = $this->progressService->calculateProgressPercent($course, $moduleScores);
        $progressState = (array) $enrollment->progress_state;
        $progressState['module_scores'] = $moduleScores;
        $enrollment->progress_percent = $percent;
        $enrollment->progress_state = $progressState;
        $enrollment->save();

        $badgeAwarded = null;
        // Runs AFTER quiz submission above (not gated on the earlier,
        // now-stale $percent snapshot) so the just-created QuizAttempt is
        // always reflected. MicrocredentialCompletionService::evaluate()
        // is itself the authoritative gate check — it re-derives
        // lessons/quiz/competency mastery from persisted data every time,
        // so calling it unconditionally here is safe and idempotent: a
        // failed quiz, incomplete lessons, or incomplete competency
        // mastery simply leaves completion_status unadvanced, and a
        // course requiring faculty verification stops there until that
        // action is recorded. This never issues a badge/certificate
        // itself unless completion_status has genuinely reached
        // 'completed' (including the unconditional academic-unit gate).
        $badgeAwarded = $this->finalizeCompletion($student, $course, $enrollment);

        $quizAttemptSummary = [];
        foreach ($course->modules as $moduleIndex => $module) {
            if (! $module->quiz) {
                continue;
            }

            $status = $this->quizAttempts->retakeStatus($module->quiz, $student->id);
            $quizAttemptSummary[(string) $moduleIndex] = [
                'allowed' => $status['allowed'],
                'used' => $status['used'],
                'remaining' => $status['allowed'] > 0 ? max(0, $status['allowed'] - $status['used']) : null,
                'exhausted' => $status['exhausted'],
            ];
        }

        return response()->json([
            'ok' => true,
            'percent' => (int) $enrollment->progress_percent,
            'badge_awarded' => $badgeAwarded,
            'quiz_unlocks' => (object) $quizUnlocks,
            'quiz_attempts' => (object) $quizAttemptSummary,
            'quiz_submissions' => (object) $quizSubmissions,
        ]);
    }

    // ── Course completion -> badge award ────────────────────────────────

    /**
     * Called via fetch() from the course player the moment a student
     * finishes the last module quiz. Marks the enrollment complete and
     * awards the badge linked to the course (courses.badge_id).
     *
     * The Admin "Recent Badges" panel counts rows in user_badges, so it
     * climbs in real time as soon as a student earns a badge. Awards are
     * idempotent: one badge per student per course, no matter how many
     * times this endpoint is hit.
     */
    public function completeCourse(int $id)
    {
        $student = Auth::user();
        $course = Course::with(['modules.quiz.questions', 'lessons'])->findOrFail($id);
        $enrollment = Enrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return response()->json([
                'ok' => false,
                'completed' => false,
                'message' => 'Enroll in this course before marking it complete.',
            ], 422);
        }

        if (! $this->courseCompletion->isReady($course, $enrollment)) {
            return response()->json([
                'ok' => false,
                'completed' => $this->completionService->isOfficiallyCompleted($enrollment),
                'progress_percent' => (int) $enrollment->progress_percent,
                'message' => 'Finish the required lessons and quizzes before completing this course.',
            ], 422);
        }

        // Phase 3 fix: isReady() above remains only the existing
        // lessons/quizzes readiness check (kept for this 422 response's
        // shape/UX) — it no longer directly awards anything.
        // finalizeCompletion() now runs the real institutional gate chain
        // via MicrocredentialCompletionService and only issues credentials
        // once completion_status genuinely reaches 'completed'.
        $badgeAwarded = $this->finalizeCompletion($student, $course, $enrollment);
        $enrollment = $enrollment->fresh();
        $officiallyCompleted = $this->completionService->isOfficiallyCompleted($enrollment);

        return response()->json([
            'ok' => true,
            'completed' => $officiallyCompleted,
            'progress_percent' => 100,
            'pending_review' => ! $officiallyCompleted,
            'message' => $officiallyCompleted
                ? null
                : 'Your course requirements are complete. Official completion is pending institutional review.',
            'badge_awarded' => $badgeAwarded,
            'certificate_available' => $course->certificate_enabled
                && $student->certificates()->where('course_id', $course->id)->exists(),
        ]);
    }

    /**
     * Marks the enrollment complete and awards the course's linked badge
     * (courses.badge_id) — but ONLY once it is OFFICIALLY complete.
     *
     * Phase 3 fix: no longer delegates to CourseCompletionService::complete(),
     * which created badges/certificates unconditionally off progress alone,
     * bypassing completion_status, faculty_verification_status, and
     * academic_unit_confirmation_status entirely. This now runs the
     * enrollment through MicrocredentialCompletionService::evaluate() —
     * the sole authoritative completion path — and only calls
     * issueBadgeIfEligible()/issueCertificateIfEligible() once
     * completion_status has genuinely reached 'completed' (every gate
     * satisfied, including the unconditional academic-unit confirmation
     * requirement). If institutional review is still pending, no
     * credential is created by this call — CourseCompletionService is no
     * longer a source of official badge/certificate issuance, though it
     * remains in use elsewhere for readiness/progress display (isReady()
     * above, and the description-page "ready to complete" flag).
     *
     * Idempotent: issueBadgeIfEligible()/issueCertificateIfEligible() are
     * themselves idempotent (Phase 3), so calling this repeatedly never
     * creates duplicates. Returns the awarded badge's name only the first
     * time it is actually issued (preserving the previous return
     * contract so the existing frontend's `badge_awarded` handling is
     * unaffected), or null otherwise — including when completion is
     * still pending institutional review.
     */
    private function finalizeCompletion($auth, Course $course, Enrollment $enrollment): ?string
    {
        $enrollment = $this->completionService->evaluate($enrollment);

        if (! $this->completionService->isOfficiallyCompleted($enrollment)) {
            return null;
        }

        $userBadge = $this->completionService->issueBadgeIfEligible($enrollment);
        $this->completionService->issueCertificateIfEligible($enrollment);

        return ($userBadge && $userBadge->wasRecentlyCreated)
            ? optional($userBadge->badge)->name
            : null;
    }

    // ── Badges & Certificates ─────────────────────────────────────────────

    public function badges()
    {
        $auth = Auth::user();

        $badges = UserBadge::with('badge')
            ->where('user_id', $auth->id)
            ->latest('earned_at')
            ->get()
            ->map(fn (UserBadge $ub) => (object) [
                'name' => $ub->badge->name ?? 'Badge',
                'description' => $ub->badge->description ?? '',
                'icon_url' => $ub->badge->icon_url ?? null,
                'earned_at' => $ub->earned_at,
            ])
            ->values();

        $enrollments = Enrollment::where('user_id', $auth->id)->get();

        return view('student.badges', [
            'user' => UserPresenter::student($auth),
            'stats' => [
                'active_courses' => $enrollments->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'completed' => $enrollments->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'badges_earned' => $badges->count(),
                'certificates' => $auth->certificates()->count(),
            ],
            'badges' => $badges,
        ]);
    }

    public function certificates()
    {
        $auth = Auth::user();

        $certificates = $auth->certificates()
            ->with('course:id,title')
            ->latest('issued_at')
            ->get()
            ->map(fn ($cert) => (object) [
                'course_name' => $cert->course?->title ?: $cert->title ?: 'Microcredential Certificate',
                'issued_at' => $cert->issued_at,
                'issued_date' => $cert->issued_at?->format('F j, Y') ?? 'Date unavailable',
                'view_url' => route('certificates.view', $cert->serial),
                'download_url' => route('certificates.download', $cert->serial),
            ])
            ->values();

        return view('student.certificates', [
            'user' => UserPresenter::student($auth),
            'certificates' => $certificates,
        ]);
    }

    /**
     * Browser view of an already-issued certificate (Phase 3, Step 6).
     *
     * This is a credential ACCESS endpoint only. It never issues a
     * certificate or badge, never creates a Certificate/UserBadge row,
     * and never touches an enrollment's completion_status, mastery
     * flags, or any snapshot column — it only reads a certificate that
     * MicrocredentialCompletionService already issued.
     */
    public function viewCertificate(string $serial)
    {
        $certificate = Certificate::where('serial', $serial)->firstOrFail();

        // Ownership check: the serial is an identifier, not authorization.
        // A student may only ever view their own certificate here, even if
        // they know or guess another student's valid serial.
        abort_if($certificate->user_id !== Auth::id(), 403);

        return view('student.certificate-view', [
            'cert' => CertificateBuilder::pdfData($certificate),
        ]);
    }

    /**
     * Download the PDF for an already-issued certificate (Phase 3, Step 6).
     *
     * Same access-layer guarantees as viewCertificate(): no issuance, no
     * new Certificate/UserBadge row, no enrollment/mastery/snapshot
     * writes. The only side effect possible here is
     * CertificateBuilder::ensureFileGenerated() writing the PDF file
     * itself the first time it's requested — idempotent, from Step 5 —
     * never regenerating a file that already exists.
     */
    public function downloadCertificate(string $serial)
    {
        $certificate = Certificate::where('serial', $serial)->firstOrFail();

        abort_if($certificate->user_id !== Auth::id(), 403);

        $certificate = CertificateBuilder::ensureFileGenerated($certificate);

        $relativePath = 'certificates/'.$certificate->serial.'.pdf';

        return Storage::disk('public')->download(
            $relativePath,
            Str::slug($certificate->title ?: 'certificate').'-'.$certificate->serial.'.pdf'
        );
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        $auth = Auth::user();

        $enrolled = Enrollment::where('user_id', $auth->id)->count();
        $completed = Enrollment::where('user_id', $auth->id)
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->count();

        return view('student.profile', [
            'user' => UserPresenter::student($auth),
            // Same catalog the About Me form uses, so Edit Profile offers
            // exactly the skills courses can be tagged with.
            'skill_options' => SkillCatalog::all(),
            'skill_groups' => SkillCatalog::groups(),
            'stats' => [
                'courses_enrolled' => $enrolled,
                'badges_earned' => $auth->badges()->count(),
                'certificates' => $auth->certificates()->count(),
                'hours_learned' => $auth->lessonCompletions()->count(),
            ],
            'progress' => ['completed' => $completed, 'total' => $enrolled],
            'achievements' => [],
            'activities' => [],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],
            'about' => ['nullable', 'string', 'max:600'],
            'date_of_birth' => ['nullable', 'string', 'max:60'],
            'gender' => ['nullable', 'string', 'max:30'],
            'education' => ['nullable', 'string', 'max:120'],
            'school' => ['nullable', 'string', 'max:255'],
            'school_other' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:600'],
            // Edit Profile now posts checkbox arrays, like the About Me form.
            // These were 'string' rules, so an array silently failed the
            // rule and the field never reached the update.
            'skills_have' => ['nullable', 'array'],
            'skills_have.*' => ['string', 'max:60'],
            'skills_want' => ['nullable', 'array'],
            'skills_want.*' => ['string', 'max:60'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$auth->id],
            'language' => ['nullable', 'string', 'max:40'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'avatar_base64' => ['nullable', 'string'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Optional password change — requires the current password.
        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'] ?? '', $auth->password)) {
                return back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
            $auth->password = Hash::make($data['password']);
        }

        // Split the single display-name field back into first/last name.
        $parts = preg_split('/\s+/', trim($data['name']), 2);
        $auth->first_name = $parts[0];
        $auth->last_name = $parts[1] ?? $auth->last_name;
        $auth->email = $data['email'];
        $auth->phone = $data['phone'] ?? null;
        $auth->location = $data['location'] ?? null;
        $auth->about = $data['about'] ?? null;
        $auth->gender = $data['gender'] ?? null;
        $auth->education = $data['education'] ?? null;
        $auth->school = $this->resolveSchool($data);
        $auth->bio = $data['bio'] ?? null;
        // Accepts the checkbox array from the picker, and still handles a
        // comma-separated string for anything posting the older format.
        $splitSkills = function ($raw): array {
            $items = is_array($raw) ? $raw : preg_split('/[,;\n]+/', (string) $raw);

            return collect($items)
                ->map(fn ($s) => trim((string) $s))
                ->filter()
                ->unique()
                ->values()
                ->all();
        };
        // Checkboxes send nothing when none are ticked, so a cleared list
        // would otherwise keep its old values. The hidden companion input
        // in the form guarantees the key is always present.
        if ($request->has('skills_have_submitted')) {
            $auth->skills_have = $splitSkills($data['skills_have'] ?? []);
        } elseif (array_key_exists('skills_have', $data)) {
            $auth->skills_have = $splitSkills($data['skills_have']);
        }

        if ($request->has('skills_want_submitted')) {
            $auth->skills_want = $splitSkills($data['skills_want'] ?? []);
        } elseif (array_key_exists('skills_want', $data)) {
            $auth->skills_want = $splitSkills($data['skills_want']);
        }
        $auth->language = $data['language'] ?? null;
        $auth->timezone = $data['timezone'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }

        if (! empty($data['date_of_birth'])) {
            try {
                $auth->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            } catch (\Throwable $e) {
                $auth->date_of_birth = $data['date_of_birth'];
            }
        }

        // Avatar arrives as a client-resized base64 data-URL (~<50 KB).
        if (! empty($data['avatar_base64']) && str_starts_with($data['avatar_base64'], 'data:image/')) {
            $auth->avatar_url = $data['avatar_base64'];
        }

        $auth->profile_completed = true;
        $auth->save();

        // Nothing left to merge from the session now that the DB is the
        // source of truth.
        session()->forget('profile_data');

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    // ── Pathways ──────────────────────────────────────────────────────────

    public function pathways()
    {
        $auth = Auth::user();

        // ── The student's real learning footprint ────────────────────────
        $enrollments = Enrollment::with('course')->where('user_id', $auth->id)->get();
        $completed = $enrollments->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED);
        $inProgress = $enrollments->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->where('progress_percent', '>', 0);

        $completedTitles = $completed->map(fn ($e) => $e->course->title ?? '')->filter()->values();

        // Skills/competencies earned from completed courses (skills + category + title keywords)
        $competencies = $completed
            ->flatMap(fn ($e) => collect($e->course->skills ?? [])->concat([$e->course->category])->filter())
            ->merge($auth->skills_have ?? [])   // self-declared skills from About Me
            ->filter()
            ->unique()
            ->values();

        // ── Pick the student's desired pathway from what they're learning ─
        $pathways = DB::table('pathways')->where('is_active', true)->get();
        $selectedPathway = $auth->pathway_id
            ? $pathways->firstWhere('id', (int) $auth->pathway_id)
            : null;
        $pathway = $selectedPathway ?: $this->bestPathwayFor($pathways, $enrollments, $competencies);

        $desiredComps = collect(json_decode($pathway->desired_competencies ?? '[]', true) ?: [])
            ->merge(json_decode($pathway->missing_competencies ?? '[]', true) ?: [])
            ->merge(json_decode($pathway->current_competencies ?? '[]', true) ?: [])
            ->filter()->unique()->values();

        // Which desired competencies the student already has / still misses
        $currentComps = $desiredComps->filter(fn ($c) => $this->studentHasCompetency($c, $competencies, $completedTitles))->values();
        $missingComps = $desiredComps->reject(fn ($c) => $this->studentHasCompetency($c, $competencies, $completedTitles))->values();

        // Readiness = share of desired competencies already earned
        $readiness = $desiredComps->count() > 0
            ? (int) round(($currentComps->count() / $desiredComps->count()) * 100)
            : 0;

        // ── Roadmap steps from the student's actual course progress ──────
        $palette = ['#2DD4CF', '#D8C84A', '#E5483D', '#8B5CF6', '#F59E0B'];
        $steps = $enrollments->take(4)->values()->map(function ($e, $i) use ($palette) {
            $status = $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED
                ? 'completed'
                : ($e->progress_percent > 0 ? 'current' : 'locked');

            return [
                'label' => 'Goal '.($i + 1),
                'title' => Str::limit($e->course->title ?? 'Course', 18, ''),
                'color' => $status === 'locked' ? '#9CA3AF' : $palette[$i % count($palette)],
                'status' => $status,
            ];
        })->all();
        if ($steps === []) {
            $steps = json_decode($pathway->steps ?? '[]', true) ?: [];
        }

        // ── Smart recommendations ────────────────────────────────────────
        $recommendations = $this->buildRecommendations($auth, $enrollments, $missingComps, $pathway, $auth->skills_want ?? []);

        return view('student.pathways', [
            'user' => UserPresenter::student($auth),
            'pathway' => [
                'steps' => $steps,
                'destination' => $pathway->destination ?? 'Full Stack Web Developer',
                'destination_color' => $pathway->destination_color ?? '#5FD93D',
                'connector_to_destination' => $pathway->connector_color ?? '#2563EB',
            ],
            'recommendations' => $recommendations,
            'desiredPathway' => [
                'title' => $pathway->desired_title ?? $pathway->name ?? 'Career Pathway',
                'current_competencies' => $currentComps->all() ?: ['—'],
                'missing_competencies' => $missingComps->all() ?: ['—'],
            ],
            'readinessPercent' => $readiness,
            'readinessLabel' => $pathway->readiness_label ?? ($pathway->desired_title ?? 'this Pathway'),
        ]);
    }

    /**
     * Chooses the pathway that best matches what the student is actually
     * studying (course titles/categories vs. pathway names/destinations),
     * falling back to the first active pathway.
     */
    private function bestPathwayFor($pathways, $enrollments, $competencies)
    {
        if ($pathways->isEmpty()) {
            return (object) [];
        }

        $skillsWantText = collect(Auth::user()->skills_want ?? [])->implode(' ');
        $haystack = strtolower($enrollments->map(fn ($e) => ($e->course->title ?? '').' '.($e->course->category ?? '').' '.implode(' ', $e->course->skills ?? []))->implode(' ').' '.$skillsWantText);

        $best = $pathways->first();
        $score = -1;
        foreach ($pathways as $p) {
            $s = 0;
            foreach (array_filter([$p->name ?? null, $p->destination ?? null, $p->desired_title ?? null]) as $kw) {
                foreach (preg_split('/\s+/', strtolower($kw)) as $word) {
                    if (strlen($word) > 3 && str_contains($haystack, $word)) {
                        $s++;
                    }
                }
            }
            if ($s > $score) {
                $score = $s;
                $best = $p;
            }
        }

        return $best;
    }

    /** Loose competency match: skill text, category, or a completed course title containing it. */
    private function studentHasCompetency(string $competency, $competencies, $completedTitles): bool
    {
        $needle = strtolower(trim($competency));
        if ($needle === '') {
            return false;
        }
        foreach ($competencies as $c) {
            if (str_contains(strtolower($c), $needle) || str_contains($needle, strtolower($c))) {
                return true;
            }
        }
        foreach ($completedTitles as $t) {
            if (str_contains(strtolower($t), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recommendation engine ("AI"-style scoring): ranks every published
     * course the student hasn't finished by how well it closes their
     * competency gaps, how popular it is, and how relevant its skills are —
     * and reports the student's real completion for in-progress picks.
     */
    private function buildRecommendations($auth, $enrollments, $missingComps, $pathway, array $skillsWant = []): array
    {
        $doneIds = $enrollments
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->pluck('course_id');
        $activeMap = $enrollments
            ->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->keyBy('course_id');

        $candidates = Course::where('is_published', true)
            ->whereNotIn('id', $doneIds)
            ->get();

        $scored = $candidates->map(function ($course) use ($missingComps, $activeMap, $pathway, $skillsWant) {
            $text = strtolower($course->title.' '.($course->category ?? '').' '.implode(' ', $course->skills ?? []));

            $score = 0;
            foreach ($missingComps as $comp) {
                foreach (preg_split('/\s+/', strtolower($comp)) as $word) {
                    if (strlen($word) > 3 && str_contains($text, $word)) {
                        $score += 3;   // directly closes a missing competency
                    }
                }
            }
            foreach (array_filter([$pathway->desired_title ?? null, $pathway->destination ?? null]) as $kw) {
                foreach (preg_split('/\s+/', strtolower($kw)) as $word) {
                    if (strlen($word) > 3 && str_contains($text, $word)) {
                        $score += 2;   // aligned with the desired pathway
                    }
                }
            }
            foreach ($skillsWant as $skill) {
                foreach (preg_split('/\s+/', strtolower((string) $skill)) as $word) {
                    if (strlen($word) > 3 && str_contains($text, $word)) {
                        $score += 4;   // student explicitly wants to learn this
                    }
                }
            }
            $score += min(2, (int) floor(($course->enrolled_count ?? 0) / 5));  // popularity nudge
            if ($activeMap->has($course->id)) {
                $score += 1;           // already started — encourage finishing
            }

            return ['course' => $course, 'score' => $score];
        })
            ->sortByDesc('score')
            ->take(4)
            ->values();

        return $scored->map(function ($row) use ($activeMap) {
            $enrollment = $activeMap->get($row['course']->id);

            return [
                'title' => ($enrollment ? 'Finish: ' : 'Take: ').Str::limit($row['course']->title, 32, ''),
                'completion' => (int) ($enrollment->progress_percent ?? 0),
            ];
        })->all();
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics()
    {
        $auth = Auth::user();

        $enrollments = Enrollment::with('course.lessons')->where('user_id', $auth->id)->get();

        $avgScore = QuizAttempt::where('user_id', $auth->id)->avg('score');

        // Only courses the student is still taking — a course that reaches
        // 100% leaves this list and moves into "Enrolled Courses".
        $activeCourses = $enrollments
            ->filter(fn (Enrollment $e) => $e->completion_status !== MicrocredentialCompletionService::STATUS_COMPLETED)
            ->map(function (Enrollment $e) {
                $students = $e->course ? $e->course->enrollments()->count() : 0;
                $faculty = 1;

                return (object) [
                    'title' => $e->course->title ?? 'Course',
                    'meta' => $students.' Students · '.$faculty.' Faculty',
                    'thumbnail_url' => $e->course->thumbnail_url ?? null,
                    'percent' => (int) $e->progress_percent,
                ];
            })->values();

        // Completed courses — shown under "Enrolled Courses".
        $enrolledCourses = $enrollments
            ->filter(fn (Enrollment $e) => $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED)
            ->sortByDesc('updated_at')
            ->map(function (Enrollment $e) {
                $students = $e->course ? $e->course->enrollments()->count() : 0;

                return (object) [
                    'title' => $e->course->title ?? 'Course',
                    'meta' => $students.' Students · 1 Faculty',
                    'thumbnail_url' => $e->course->thumbnail_url ?? null,
                    'percent' => (int) $e->progress_percent,
                ];
            })->values();

        // Completion Rate dropdown data: every enrolled course + its own %.
        $completionCourses = $enrollments
            ->sortBy('course.title')
            ->map(fn (Enrollment $e) => (object) [
                'title' => $e->course->title ?? 'Course',
                'percent' => (int) round((float) $e->progress_percent),
            ])->values();

        [$enrollmentByCourse, $completionRate] = $this->courseCharts();

        $recentBadges = UserBadge::query()
            ->join('badges', 'badges.id', '=', 'user_badges.badge_id')
            ->where('user_badges.user_id', $auth->id)
            ->where('user_badges.status', 'active')
            ->select('badges.name', DB::raw('COUNT(*) as earned_count'))
            ->groupBy('badges.name')
            ->orderByDesc('earned_count')
            ->limit(4)
            ->get()
            ->map(fn ($row) => (object) ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values();

        return view('student.analytics', [
            'user' => UserPresenter::student($auth),
            'stats' => [
                'active_courses' => $enrollments->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'badges_earned' => $auth->badges()->count(),
                'score_avg' => $avgScore !== null ? round((float) $avgScore, 1) : 0,
                'hours_enrolled' => $this->enrolledHours($enrollments),
                'competencies_mastered' => CompetencyProgress::where('user_id', $auth->id)
                    ->where('status', 'completed')
                    ->count(),
            ],
            'activeCourses' => $activeCourses,
            'enrolledCourses' => $enrolledCourses,
            'completionCourses' => $completionCourses,
            'recentBadges' => $recentBadges,
            'enrollmentByCourse' => $enrollmentByCourse,
            'completionRate' => $completionRate,
            'analyticsSeries' => $this->studentAnalyticsSeries($auth->id, $enrollments),
        ]);
    }

    /**
     * Build the chart series from persisted lesson completions and quiz attempts.
     * The view only formats these values; no demo activity is generated client-side.
     */
    private function studentAnalyticsSeries(int $userId, $enrollments): array
    {
        $now = Carbon::now();
        $lessonCompletions = LessonCompletion::with('lesson')
            ->where('user_id', $userId)
            ->where('completed_at', '>=', $now->copy()->subDays(30))
            ->get();
        $attempts = QuizAttempt::where('user_id', $userId)
            ->where(function ($query) use ($now) {
                $query->where('submitted_at', '>=', $now->copy()->subDays(30))
                    ->orWhere(function ($fallback) use ($now) {
                        $fallback->whereNull('submitted_at')
                            ->where('created_at', '>=', $now->copy()->subDays(30));
                    });
            })
            ->get();

        $totalLessons = max(1, $enrollments->sum(fn (Enrollment $enrollment) => $enrollment->course?->lessons?->count() ?? 0));
        $series = [];

        foreach (['day', 'week', 'month'] as $range) {
            if ($range === 'day') {
                $labels = collect(range(0, 5))->map(fn (int $index) => $now->copy()->startOfDay()->addHours($index * 4)->format('g A'))->all();
                $bucketStart = fn (int $index) => $now->copy()->startOfDay()->addHours($index * 4);
                $bucketEnd = fn (int $index) => $bucketStart($index)->copy()->addHours(4);
            } elseif ($range === 'week') {
                $labels = collect(range(6, 0))->map(fn (int $days) => $now->copy()->subDays($days)->format('D'))->all();
                $bucketStart = fn (int $index) => $now->copy()->subDays(6 - $index)->startOfDay();
                $bucketEnd = fn (int $index) => $bucketStart($index)->copy()->addDay();
            } else {
                $labels = collect(range(3, 0))->map(fn (int $weeks) => 'Week '.(5 - $weeks))->all();
                $bucketStart = fn (int $index) => $now->copy()->subWeeks(4 - $index)->startOfWeek();
                $bucketEnd = fn (int $index) => $bucketStart($index)->copy()->addWeek();
            }

            $progress = [];
            $hours = [];
            $assessment = [];
            $streak = [];
            $active = [];
            $completedLessons = 0;
            $runningStreak = 0;

            foreach (array_keys($labels) as $index) {
                $start = $bucketStart($index);
                $end = $bucketEnd($index);
                $completed = $lessonCompletions->filter(fn (LessonCompletion $item) => $item->completed_at >= $start && $item->completed_at < $end);
                $completedLessons += $completed->count();
                $hours[] = round($completed->sum(fn (LessonCompletion $item) => $this->lessonDurationHours($item->lesson?->duration)), 2);
                $scores = $attempts->filter(function (QuizAttempt $attempt) use ($start, $end) {
                    $date = $attempt->submitted_at ?? $attempt->created_at;

                    return $date >= $start && $date < $end;
                })->pluck('score');
                $assessment[] = $scores->count() ? round((float) $scores->avg(), 1) : null;
                $activeToday = $completed->isNotEmpty() || $scores->isNotEmpty();
                $active[] = $activeToday ? 1 : 0;
                $runningStreak = $activeToday ? $runningStreak + 1 : 0;
                $streak[] = $runningStreak;
                $progress[] = min(100, (int) round(($completedLessons / $totalLessons) * 100));
            }

            $series[$range] = [
                'labels' => $labels,
                'progress' => $progress,
                'hours' => $hours,
                'assessment' => $assessment,
                'streak' => $streak,
                'active' => $active,
                'time' => $hours,
            ];
        }

        return $series;
    }

    private function lessonDurationHours(?string $duration): float
    {
        $minutes = (float) preg_replace('/[^\d.]/', '', (string) $duration);

        return $minutes > 0 ? $minutes / 60 : 0.0;
    }

    /**
     * Shared chart rows: top courses by enrollment, plus average
     * completion (progress) per course.
     *
     * @return array{0: Collection, 1: Collection}
     */
    private function courseCharts(): array
    {
        $rows = Enrollment::query()
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->select(
                'courses.title',
                DB::raw('COUNT(*) as learners'),
                DB::raw('AVG(enrollments.progress_percent) as avg_progress')
            )
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('learners')
            ->limit(4)
            ->get();

        $max = max(1, (int) $rows->max('learners'));

        $enrollmentByCourse = $rows->map(fn ($row) => (object) [
            'label' => $this->shortTitle($row->title),
            'value' => (int) $row->learners,
            'percent' => (int) round($row->learners / $max * 100),
        ])->values();

        $completionRate = $rows->map(fn ($row) => (object) [
            'label' => $this->shortTitle($row->title),
            'value' => (int) round($row->avg_progress),
            'percent' => (int) round($row->avg_progress),
        ])->values();

        return [$enrollmentByCourse, $completionRate];
    }

    private function shortTitle(string $title): string
    {
        return Str::limit($title, 14, '');
    }

    /** /courses → the browse screen. */
    public function coursesIndexRedirect()
    {
        return redirect()->route('courses.browse');
    }

    // ── Help Centre inbox ─────────────────────────────────────────────────

    /**
     * Student inbox (the envelope beside the notification bell): the
     * student's own Help Centre complaints and the admin's replies.
     */
    public function inbox(Request $request)
    {
        $auth = Auth::user();

        $threads = Complaint::with('replies.author')
            ->where('user_id', $auth->id)
            ->get()
            ->sortByDesc(fn (Complaint $c) => $c->lastActivityAt())
            ->values();

        $selectedId = (int) $request->query('thread', $threads->first()->id ?? 0);
        $selected = $threads->firstWhere('id', $selectedId);

        if ($selected && $selected->unreadForStudent()) {
            $selected->student_read_at = now();
            $selected->save();
        }

        return view('student.inbox', [
            'user' => UserPresenter::student($auth),
            'threads' => $threads,
            'selected' => $selected,
            'unreadCount' => $threads->filter->unreadForStudent()->count(),
        ]);
    }

    /** Raise a new complaint from the Help Centre. */
    public function storeComplaint(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:60'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ], [
            'attachment.mimes' => 'The attachment must be an image (JPG, PNG, GIF or WEBP).',
            'attachment.max' => 'The image may not be larger than 10 MB.',
        ]);

        // Optional image, stored alongside the Help Center uploads so the
        // admin sees them from one place.
        $attachmentUrl = $attachmentName = null;

        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $dir = public_path('uploads/complaints');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $stored = uniqid('help_').'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dir, $stored);

            $attachmentUrl = 'uploads/complaints/'.$stored;
            $attachmentName = $file->getClientOriginalName();
        }

        $complaint = Complaint::create([
            'user_id' => Auth::id(),
            'source' => 'student',
            'subject' => $data['subject'],
            'message' => trim($data['message']),
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $attachmentName,
            'category' => $data['category'] ?? 'general',
            'status' => 'open',
            'last_reply_at' => now(),
            'student_read_at' => now(),
        ]);

        return redirect()->route('inbox.index', ['thread' => $complaint->id])
            ->with('success', 'Your message was sent to the administrators.');
    }

    /** Student replies within an existing thread. */
    public function replyComplaint(Request $request, int $id)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $complaint = Complaint::where('user_id', Auth::id())->findOrFail($id);

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'body' => trim($data['body']),
            'is_admin' => false,
        ]);

        $complaint->last_reply_at = now();
        $complaint->student_read_at = now();
        $complaint->save();

        return redirect()->route('inbox.index', ['thread' => $complaint->id]);
    }

    /**
     * Resolve the school picker into the single value stored in
     * users.school.
     *
     * The dropdown offers a fixed list plus "Other". When "Other" is chosen
     * the learner types the institution in a separate box, and that text is
     * what gets stored — so the column always holds a real name and nothing
     * downstream has to interpret the literal word "Other".
     */
    protected function resolveSchool(array $data): ?string
    {
        $choice = trim((string) ($data['school'] ?? ''));

        if ($choice === '') {
            return null;
        }

        if ($choice === SchoolCatalog::OTHER) {
            $other = trim((string) ($data['school_other'] ?? ''));

            return $other !== '' ? $other : null;
        }

        return $choice;
    }
}

