<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Certificate;
use App\Models\CompetencyProgress;
use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\LessonActivity;
use App\Models\LessonActivitySubmission;
use App\Models\LessonCompletion;
use App\Models\Pathway;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StackingFramework;
use App\Models\UserBadge;
use App\Services\CourseCompletionService;
use App\Services\CourseEvaluationService;
use App\Services\CreditEvidenceReportService;
use App\Services\MicrocredentialCompletionService;
use App\Services\QuizAttemptService;
use App\Services\StackingProgressService;
use App\Services\StudentProgressService;
use App\Services\UserNotificationService;
use App\Support\CertificateBuilder;
use App\Support\CourseEvaluationTemplate;
use App\Support\SchoolCatalog;
use App\Support\SkillCatalog;
use App\Support\UserPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
            'schools' => SchoolCatalog::all(),
            'skill_options' => SkillCatalog::all(),
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
        $enrollments = Enrollment::with([
            'course' => fn ($query) => $query->withAvg('reviews', 'rating')->withCount('reviews'),
            'course.modules.quiz.questions',
            'course.modules.lessons.quizzes.questions',
            'course.modules.lessons.activities',
        ])
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
            'rating_average' => (float) ($e->course->reviews_avg_rating ?? 0),
            'review_count' => (int) ($e->course->reviews_count ?? 0),
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

        $enrollments = Enrollment::with([
            'course' => fn ($query) => $query->withAvg('reviews', 'rating')->withCount('reviews'),
            'course.modules.quiz.questions',
            'course.modules.lessons.quizzes.questions',
            'course.modules.lessons.activities',
        ])
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
            'rating_average' => (float) ($e->course->reviews_avg_rating ?? 0),
            'review_count' => (int) ($e->course->reviews_count ?? 0),
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
        return view('student.stacking-progress', [
            'user' => UserPresenter::student($auth),
            'frameworks' => $frameworks,
        ]);
    }

    public function creditEvidenceReport(int $frameworkId, CreditEvidenceReportService $reports)
    {
        $student = Auth::user();
        $framework = StackingFramework::query()->findOrFail($frameworkId);
        $item = $this->stackingProgress->progressForUser((int) $student->id)
            ->first(fn (array $progress): bool => (int) $progress['framework']->id === $frameworkId);
        abort_unless($item && $item['status'] === 'requirements_met', 403);

        return view('credit-evidence-report', $reports->build($student, $framework));
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
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
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
            'description' => filled($c->short_description)
                ? $c->short_description
                : trim(html_entity_decode(strip_tags((string) $c->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'category' => $c->category,
            'level' => $c->level,
            'instructor' => $c->instructor,
            'duration' => $c->duration,
            'learning_hours' => $c->learning_hours,
            'lessons_count' => (int) $c->lessons_count,
            'rating_average' => (float) ($c->reviews_avg_rating ?? 0),
            'review_count' => (int) $c->reviews_count,
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
        $course = Course::with([
            'modules.lessons.quizzes.questions',
            'modules.quiz.questions',
            'creator',
            'learningOutcomes',
            'badge',
        ])->findOrFail($id);
        $enrollment = Enrollment::where('user_id', $auth->id)
            ->where('course_id', $course->id)
            ->first();
        abort_unless(
            ($course->is_published && $course->is_approved) || $enrollment,
            404
        );

        $instructor = $course->creator;

        $quizSummary = fn (Quiz $quiz): object => (object) [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'questions_count' => $quiz->questions->count(),
            'passing_score' => $quiz->passing_score,
        ];
        $finalExamModule = $course->modules->firstWhere('is_final', true);
        $finalExamQuiz = $finalExamModule?->quiz;
        $finalExam = $finalExamModule ? (object) [
            'title' => $finalExamModule->title ?: 'Final Exam',
            'quiz' => $finalExamQuiz && $finalExamQuiz->is_active ? $quizSummary($finalExamQuiz) : null,
        ] : null;

        $modules = $course->modules->reject(fn (CourseModule $module): bool => $module->is_final)->map(fn (CourseModule $module) => (object) [
            'title' => $module->title,
            'description' => trim(strip_tags((string) $module->description)),
            'lessons' => $module->lessons->map(fn (CourseLesson $lesson) => (object) [
                'title' => $lesson->title,
                'type' => $lesson->type,
                'duration' => $lesson->duration,
                'quizzes' => $lesson->quizzes
                    ->where('is_active', true)
                    ->map($quizSummary)
                    ->values(),
            ])->values(),
            'quiz' => $module->quiz && $module->quiz->is_active ? $quizSummary($module->quiz) : null,
        ])->values();

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
                'short_description' => $course->short_description,
                'description' => $course->description,
                'badge_name' => $course->badge?->is_active ? $course->badge->name : null,
                'badge_description' => $course->badge?->is_active ? $course->badge->description : null,
                'category' => $course->category,
                'level' => $course->level,
                'pqf_level' => $course->pqf_level,
                'target_learners' => $course->target_learners,
                'delivery_mode' => $course->delivery_mode,
                'learning_hours' => $course->learning_hours,
                'assessment_strategy' => $course->assessment_strategy,
                'grading_rubric' => $course->grading_rubric,
                'credit_bearing' => (bool) $course->credit_bearing,
                'credit_equivalency' => $course->credit_equivalency,
                'equivalent_course' => $course->equivalent_course,
                'learning_outcomes' => $course->learningOutcomes->isNotEmpty()
                    ? $course->learningOutcomes->pluck('description')->values()->all()
                    : ($course->objectives ?? []),
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
            'finalExam' => $finalExam,
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

    public function enroll(int $id, UserNotificationService $userNotifications)
    {
        $auth = Auth::user();
        $course = Course::findOrFail($id);
        $existingEnrollment = Enrollment::where('user_id', $auth->id)
            ->where('course_id', $course->id)
            ->first();
        if ($existingEnrollment) {
            return redirect()->route('courses.learn', $course->id);
        }
        abort_unless($course->is_published && $course->is_approved, 404);

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
            $userNotifications->createForUser(
                $auth->id,
                'Enrollment confirmed: '.$course->title,
                'You are enrolled in this course and can begin learning now.',
                'enrollment',
                'enrollment',
                $enrollment->id,
            );
            $userNotifications->createForUser(
                $course->created_by,
                'New learner enrolled',
                $auth->name.' enrolled in "'.$course->title.'".',
                'enrollment',
                'enrollment',
                $enrollment->id,
            );
        }

        $course->enrolled_count = $course->enrollments()->count();
        $course->save();

        return redirect()->route('courses.learn', $course->id);
    }

    /**
     * Learning / enrollment screen (Student_Course_Enrollment).
     */
    public function learn(int $id, CourseEvaluationService $courseEvaluation)
    {
        $auth = Auth::user();
        $course = Course::with(['modules.lessons.quizzes.questions', 'modules.lessons.activities.submissions', 'modules.quiz.questions'])->findOrFail($id);
        $enrollment = Enrollment::where('user_id', $auth->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return redirect()->route('courses.show', $course->id)
                ->withErrors(['enrollment' => 'Enroll in this course before opening its lessons.']);
        }

        $modules = $course->modules->map(fn ($m) => (object) [
            'id' => $m->id,
            'title' => $m->title,
            'is_final' => (bool) $m->is_final,
            'description' => $m->description ?? '',
            'assessment_count' => $m->lessons->sum(fn (CourseLesson $lesson) => $lesson->activities->where('is_active', true)->count()
                + $lesson->quizzes->where('is_active', true)->count()
            ) + (int) ($m->quiz?->is_active ?? false),
            'lessons' => $m->lessons->map(fn (CourseLesson $l) => (object) [
                'id' => $l->id,
                'title' => $l->title,
                'type' => 'Text',
                'duration' => $l->duration,
                'description' => $l->content ?: $l->title,
                'content' => $l->content ?? '',
                'thumbnail_url' => null,
                'lesson_quiz' => $l->quizzes->where('is_active', true)->first() ? (object) [
                    'id' => $l->quizzes->where('is_active', true)->first()->id,
                    'title' => $l->quizzes->where('is_active', true)->first()->title,
                    'passing_score' => $l->quizzes->where('is_active', true)->first()->passing_score,
                    'sort_order' => $l->quizzes->where('is_active', true)->first()->sort_order,
                ] : null,
                'activities' => $l->activities->where('is_active', true)->map(fn (LessonActivity $activity) => (object) [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'activity_type' => $activity->activity_type,
                    'is_required' => $activity->is_required,
                    'sort_order' => $activity->sort_order,
                ])->values(),
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

        $progressBreakdown = $this->progressService->progressBreakdown(
            $course,
            (int) $auth->id,
            $enrollment->enrolled_at
        );
        $livePercent = $progressBreakdown['percent'];
        $lessonIds = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->pluck('id')
            ->unique()
            ->values();
        $liveLessons = $lessonIds->isEmpty()
            ? []
            : DB::table('lesson_completions')
                ->where('user_id', $auth->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('server_verified_at')
                ->pluck('lesson_id')
                ->map(fn ($lessonId) => (string) $lessonId)
                ->all();

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

        $courseReview = CourseReview::query()->where('course_id', $course->id)->where('user_id', $auth->id)->first();
        $courseEvaluationEligible = $courseEvaluation->isEligible($course, $auth, $enrollment);
        $recapQuizIds = $course->modules
            ->reject(fn ($module) => $module->is_final)
            ->flatMap(fn ($module) => collect([$module->quiz?->id])->merge($module->lessons->flatMap(fn ($lesson) => $lesson->quizzes->pluck('id'))))
            ->filter()
            ->unique()
            ->values();
        $recapAttempts = $recapQuizIds->isEmpty()
            ? collect()
            : QuizAttempt::query()
                ->with('quiz')
                ->where('user_id', $auth->id)
                ->whereIn('quiz_id', $recapQuizIds)
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->get()
                ->unique('quiz_id')
                ->keyBy('quiz_id');
        $courseRecap = $course->modules
            ->reject(fn ($module) => $module->is_final)
            ->map(function ($module) use ($recapAttempts) {
                $lessons = $module->lessons->map(function ($lesson) use ($recapAttempts) {
                    return [
                        'title' => $lesson->title,
                        'activities' => $lesson->activities->where('is_active', true)->map(function ($activity) {
                            $submission = $activity->submissions->where('user_id', Auth::id())->sortByDesc('attempt_number')->first();

                            return [
                                'title' => $activity->title,
                                'answer' => $submission?->response_text,
                                'file_submitted' => (bool) $submission?->submission_path,
                                'file_url' => $submission ? route('lesson-activities.download', $submission->id) : null,
                                'status' => $submission?->status,
                            ];
                        })->values()->all(),
                        'quizzes' => $lesson->quizzes->where('is_active', true)->map(function ($quiz) use ($recapAttempts) {
                            $attempt = $recapAttempts->get($quiz->id);
                            $editedAt = $this->quizLastEditedAt($quiz);
                            if ($attempt && $editedAt && $attempt->submitted_at->lt($editedAt)) {
                                $attempt = null;
                            }

                            return [
                                'title' => $quiz->title,
                                'score' => $attempt?->score,
                                'passed' => $attempt?->passed,
                            ];
                        })->values()->all(),
                    ];
                })->values()->all();
                $moduleAttempt = $module->quiz ? $recapAttempts->get($module->quiz->id) : null;
                $moduleQuizEditedAt = $module->quiz ? $this->quizLastEditedAt($module->quiz) : null;
                if ($moduleAttempt && $moduleQuizEditedAt && $moduleAttempt->submitted_at->lt($moduleQuizEditedAt)) {
                    $moduleAttempt = null;
                }

                return [
                    'title' => $module->title,
                    'lessons' => $lessons,
                    'summative' => $module->quiz ? [
                        'title' => $module->quiz->title,
                        'score' => $moduleAttempt?->score,
                        'passed' => $moduleAttempt?->passed,
                    ] : null,
                ];
            })->values()->all();

        return view('student.courses.enrollment', [
            'user' => UserPresenter::student($auth),
            'course' => (object) [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category,
                'thumbnail_url' => $course->thumbnail_url ? asset($course->thumbnail_url) : null,
                'progress_percent' => $livePercent,
                'certificate_enabled' => (bool) $course->certificate_enabled,
                'badge_id' => $course->badge_id,
            ],
            'modules' => $modules,
            'courseReview' => $courseReview,
            'courseEvaluationEligible' => $courseEvaluationEligible,
            'courseEvaluationExhaustedFinalExam' => false,
            'courseRecap' => $courseRecap,
            'courseCompletionStatus' => $enrollment->completion_status,
            'courseEvaluationQuestions' => CourseEvaluationTemplate::questions(),
            'current_lesson' => $modules->first()?->lessons->first(),
            'current_module' => $modules->first(),
            'total_lessons' => $modules->sum(fn ($m) => $m->lessons->count()),
            'progress_percent' => $livePercent,
            'progress_breakdown' => $progressBreakdown,
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

    public function submitCourseEvaluation(int $id, Request $request, CourseEvaluationService $courseEvaluation)
    {
        $student = Auth::user();
        $course = Course::with(['modules.quiz'])->findOrFail($id);
        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->first();
        abort_unless($enrollment, 403);
        abort_unless($courseEvaluation->isEligible($course, $student, $enrollment), 403);
        abort_unless(! CourseReview::query()->where('course_id', $course->id)->where('user_id', $student->id)->exists(), 409);

        $questions = CourseEvaluationTemplate::questions();
        $rules = [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ];
        foreach (array_keys($questions['course']) as $item) {
            $rules['answers.course.'.$item] = ['required', 'integer', 'between:1,5'];
        }
        foreach (array_keys($questions['platform']) as $item) {
            $rules['answers.platform.'.$item] = ['required', 'integer', 'between:1,5'];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The submitted course review is invalid.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        CourseReview::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => $data['rating'],
            'answers' => $data['answers'],
            'comment' => trim((string) ($data['comment'] ?? '')) ?: null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'review_submitted' => true,
                'evaluation_eligible' => true,
                'message' => 'Thank you for evaluating this course.',
            ]);
        }

        return redirect()->route('courses.learn', $course->id)->with('success', 'Thank you for evaluating this course.');
    }

    /**
     * Record when an enrolled student opens a lesson. Completion is recorded
     * separately after a minimum server-measured reading time.
     */
    public function startLesson(int $courseId, int $lessonId, Request $request)
    {
        $student = Auth::user();
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $lesson = CourseLesson::query()
            ->where('course_id', $courseId)
            ->findOrFail($lessonId);
        $course = Course::with(['modules.quiz.questions', 'modules.lessons.quizzes.questions', 'modules.lessons.activities'])
            ->findOrFail($courseId);

        abort_unless(
            $this->lessonIsUnlockedForStudent($course, $lesson, (int) $student->id, $enrollment),
            403,
            'Complete the previous lessons and module assessment before opening this lesson.'
        );

        $request->session()->put(
            $this->lessonTrackingSessionKey((int) $student->id, $courseId, $lessonId),
            now()->timestamp
        );

        return response()->json(['ok' => true]);
    }

    /** Continue from lesson content into its first outstanding requirement. */
    public function completeLesson(int $courseId, int $lessonId, Request $request, CourseEvaluationService $courseEvaluation)
    {
        $student = Auth::user();
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $course = Course::with(['modules.lessons', 'modules.quiz.questions'])->findOrFail($courseId);
        $lesson = CourseLesson::query()
            ->where('course_id', $courseId)
            ->findOrFail($lessonId);
        $lesson->load(['quizzes.questions', 'activities']);

        if (! $this->lessonIsUnlockedForStudent($course, $lesson, (int) $student->id, $enrollment)) {
            return response()->json([
                'ok' => false,
                'message' => 'Complete the previous lessons and module assessment before continuing.',
                'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
            ], 422);
        }

        $alreadyVerified = DB::table('lesson_completions')
            ->where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('server_verified_at')
            ->exists();

        $sessionKey = $this->lessonTrackingSessionKey((int) $student->id, $courseId, $lessonId);
        $openedAt = (int) $request->session()->get($sessionKey, 0);
        if (! $alreadyVerified && (! $openedAt || now()->timestamp - $openedAt < 5)) {
            return response()->json([
                'ok' => false,
                'message' => 'Spend a few seconds in the lesson before continuing to its assessments.',
                'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
            ], 422);
        }

        $request->session()->put($this->lessonContinuedSessionKey((int) $student->id, $courseId, $lessonId), now()->timestamp);
        $completed = $this->progressService->completeLessonIfRequirementsSatisfied($course, $lesson, $enrollment);

        if ($completed) {
            $this->completionService->evaluate($enrollment);
            $request->session()->forget($sessionKey);
        }

        $nextRequirement = $completed ? ['url' => null, 'pending_review' => false] : $this->nextRequiredLessonAssessment($lesson, $enrollment);

        return response()->json([
            'ok' => true,
            'completed' => $completed,
            'next_url' => $nextRequirement['url'],
            'pending_review' => $nextRequirement['pending_review'],
            'progress_percent' => (int) $enrollment->fresh()->progress_percent,
            'progress_breakdown' => $this->progressService->progressBreakdown($course, (int) $student->id, $enrollment->enrolled_at),
            'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment->fresh()),
            'message' => $nextRequirement['pending_review'] && ! $nextRequirement['url']
                ? 'Your assignment is waiting for faculty review.'
                : null,
        ]);
    }

    private function lessonTrackingSessionKey(int $userId, int $courseId, int $lessonId): string
    {
        return 'lesson_tracking.'.$userId.'.'.$courseId.'.'.$lessonId;
    }

    private function lessonContinuedSessionKey(int $userId, int $courseId, int $lessonId): string
    {
        return 'lesson_continued.'.$userId.'.'.$courseId.'.'.$lessonId;
    }

    /** @return array{url: ?string, pending_review: bool} */
    private function nextRequiredLessonAssessment(CourseLesson $lesson, Enrollment $enrollment): array
    {
        $lesson->loadMissing(['activities', 'quizzes.questions']);
        $requirements = collect();

        foreach ($lesson->activities->where('is_active', true)->where('is_required', true) as $activity) {
            $requirements->push(['kind' => 'activity', 'sort_order' => (int) $activity->sort_order, 'item' => $activity]);
        }

        foreach ($lesson->quizzes->where('is_active', true) as $quiz) {
            if ($quiz->questions->isEmpty()) {
                continue;
            }

            $requirements->push(['kind' => 'quiz', 'sort_order' => (int) $quiz->sort_order, 'item' => $quiz]);
        }

        $pendingReview = false;
        foreach ($requirements->sortBy('sort_order') as $requirement) {
            $item = $requirement['item'];

            if ($requirement['kind'] === 'activity') {
                $acceptedStatuses = $item->activity_type === 'assignment' ? ['passed'] : ['completed', 'passed'];
                if ($item->submissions()->where('user_id', $enrollment->user_id)->whereIn('status', $acceptedStatuses)->exists()) {
                    continue;
                }

                $latestSubmission = $item->submissions()->where('user_id', $enrollment->user_id)->latest('attempt_number')->first();
                if ($item->activity_type === 'assignment' && $latestSubmission?->status === 'submitted') {
                    return ['url' => null, 'pending_review' => true];
                }

                return ['url' => route('lesson-activities.show', $item->id), 'pending_review' => $pendingReview];
            }

            if (QuizAttempt::query()
                ->where('quiz_id', $item->id)
                ->where('user_id', $enrollment->user_id)
                ->where('passed', true)
                ->whereNotNull('submitted_at')
                ->exists()) {
                continue;
            }

            return ['url' => route('quiz.show', $item->id), 'pending_review' => $pendingReview];
        }

        return ['url' => null, 'pending_review' => $pendingReview];
    }

    private function lessonActivitiesSatisfied(CourseLesson $lesson, int $userId): bool
    {
        foreach ($lesson->activities->where('is_active', true)->where('is_required', true) as $activity) {
            $accepted = $activity->activity_type === 'assignment' ? ['passed'] : ['completed', 'passed'];
            if (! $activity->submissions()->where('user_id', $userId)->whereIn('status', $accepted)->exists()) {
                return false;
            }
        }

        return true;
    }

    private function lessonRequirementsSatisfied(CourseLesson $lesson, int $userId): bool
    {
        if (! $this->lessonActivitiesSatisfied($lesson, $userId)) {
            return false;
        }

        foreach ($lesson->quizzes->where('is_active', true) as $quiz) {
            if ($quiz->questions->isEmpty()) {
                continue;
            }

            if (! QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $userId)->where('passed', true)->whereNotNull('submitted_at')->exists()) {
                return false;
            }
        }

        return true;
    }

    private function lessonIsServerVerified(int $lessonId, int $userId): bool
    {
        return DB::table('lesson_completions')
            ->where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->whereNotNull('server_verified_at')
            ->exists();
    }

    private function lessonIsUnlockedForStudent(Course $course, CourseLesson $targetLesson, int $userId, Enrollment $enrollment): bool
    {
        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                if ((int) $lesson->id === (int) $targetLesson->id) {
                    return true;
                }

                if (! $this->lessonIsServerVerified((int) $lesson->id, $userId)
                    || ! $this->lessonRequirementsSatisfied($lesson, $userId)) {
                    return false;
                }
            }

            if ($module->quiz && $module->quiz->questions->isNotEmpty()
                && ! QuizAttempt::query()
                    ->where('quiz_id', $module->quiz->id)
                    ->where('user_id', $userId)
                    ->where('passed', true)
                    ->whereNotNull('submitted_at')
                    ->exists()) {
                return false;
            }
        }

        return false;
    }

    private function quizIsUnlockedForStudent(Quiz $quiz, int $userId): bool
    {
        $course = Course::with(['modules.quiz', 'modules.lessons.quizzes.questions', 'modules.lessons.activities'])
            ->findOrFail($quiz->course_id);
        $enrollment = Enrollment::query()->where('user_id', $userId)->where('course_id', $quiz->course_id)->first();
        if (! $enrollment) {
            return false;
        }
        $targetModuleId = $quiz->module_id ?: $quiz->lesson?->module_id;
        $targetModuleIndex = $course->modules->search(fn ($module): bool => (int) $module->id === (int) $targetModuleId);
        if ($targetModuleIndex === false) {
            return false;
        }

        foreach ($course->modules->take($targetModuleIndex + 1) as $moduleIndex => $module) {
            foreach ($module->lessons as $lesson) {
                if ((int) $lesson->id === (int) $quiz->lesson_id) {
                    return ($this->lessonIsServerVerified((int) $lesson->id, $userId)
                            || session()->has($this->lessonContinuedSessionKey($userId, (int) $course->id, (int) $lesson->id)))
                        && $this->lessonActivitiesSatisfied($lesson, $userId);
                }

                if (! $this->lessonIsServerVerified((int) $lesson->id, $userId)
                    || ! $this->lessonRequirementsSatisfied($lesson, $userId)) {
                    return false;
                }
            }

            if ($moduleIndex < $targetModuleIndex && $module->quiz && $module->quiz->questions()->exists()) {
                if (! QuizAttempt::query()->where('quiz_id', $module->quiz->id)->where('user_id', $userId)->where('passed', true)->whereNotNull('submitted_at')->exists()) {
                    return false;
                }
            }
        }

        return $quiz->module_id !== null;
    }

    /**
     * Individual lesson deep-link — the learning screen handles playback,
     * so this stays a redirect (as in the original routes).
     */
    public function lesson(int $courseId, int $lessonId)
    {
        return redirect()->route('courses.learn', $courseId);
    }

    public function quiz(int $id, Request $request)
    {
        $student = Auth::user();
        $quiz = Quiz::with(['course', 'questions'])->where('is_active', true)->findOrFail($id);
        $enrolled = Enrollment::query()->where('user_id', $student->id)->where('course_id', $quiz->course_id)->exists();
        abort_unless($enrolled, 403);
        abort_unless($this->quizIsUnlockedForStudent($quiz, (int) $student->id), 403, 'Complete the required course content before taking this quiz.');
        $status = $this->quizAttempts->retakeStatus($quiz, (int) $student->id);
        $latestAttempt = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $student->id)->latest('submitted_at')->first();
        $timeLimitSeconds = max(0, (int) $quiz->time_limit * 60);
        $sessionKey = $this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id);
        if ($status['exhausted'] || $status['unlock']) {
            session()->forget($sessionKey);
        } elseif ($timeLimitSeconds > 0 && ! session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);
        }
        $quizStartedAt = $timeLimitSeconds > 0 ? (int) session()->get($sessionKey, now()->timestamp) : null;

        $viewData = [
            'user' => UserPresenter::student($student),
            'quiz' => $quiz,
            'status' => $status,
            'latestAttempt' => $latestAttempt,
            'time_limit_seconds' => $timeLimitSeconds,
            'timer_remaining_seconds' => $quizStartedAt === null
                ? null
                : max(0, $timeLimitSeconds - (now()->timestamp - $quizStartedAt)),
        ];

        if ($request->expectsJson() && $quiz->lesson_id) {
            return response()->json([
                'html' => view('student.courses.partials.lesson-quiz', $viewData)->render(),
            ]);
        }

        return view('student.quiz', $viewData);
    }

    public function startQuiz(int $id, Request $request)
    {
        $student = Auth::user();
        $quiz = Quiz::with(['course', 'questions'])->where('is_active', true)->findOrFail($id);
        abort_unless(Enrollment::query()->where('user_id', $student->id)->where('course_id', $quiz->course_id)->exists(), 403);
        abort_unless($this->quizIsUnlockedForStudent($quiz, (int) $student->id), 403, 'Complete the required course content before taking this quiz.');

        $status = $this->quizAttempts->retakeStatus($quiz, (int) $student->id);
        if ($status['exhausted'] || $status['unlock'] !== null) {
            $request->session()->forget($this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id));

            return response()->json(['ok' => false, 'message' => 'This quiz is not currently available.'], 409);
        }

        $timeLimitSeconds = max(0, (int) $quiz->time_limit * 60);
        $startedAt = null;
        $deadline = null;
        if ($timeLimitSeconds > 0) {
            $sessionKey = $this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id);
            $startedAt = (int) $request->session()->get($sessionKey, 0);
            if ($startedAt <= 0) {
                $startedAt = now()->timestamp;
                $request->session()->put($sessionKey, $startedAt);
            }
            $deadline = ($startedAt + $timeLimitSeconds) * 1000;
        }

        return response()->json(['ok' => true, 'deadline' => $deadline]);
    }

    private function quizTrackingSessionKey(int $userId, int $quizId): string
    {
        return 'quiz_tracking.'.$userId.'.'.$quizId;
    }

    public function submitStandaloneQuiz(int $id, Request $request, CourseEvaluationService $courseEvaluation)
    {
        $student = Auth::user();
        $quiz = Quiz::with(['course', 'questions'])->where('is_active', true)->findOrFail($id);
        abort_unless(Enrollment::query()->where('user_id', $student->id)->where('course_id', $quiz->course_id)->exists(), 403);
        abort_unless($this->quizIsUnlockedForStudent($quiz, (int) $student->id), 403, 'Complete the required course content before taking this quiz.');
        $startedAt = null;
        if ((int) $quiz->time_limit > 0) {
            $sessionKey = $this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id);
            $startedAt = (int) $request->session()->get($sessionKey, 0);
            abort_if($startedAt <= 0, 422, 'Start the quiz before submitting your answers.');
        }

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $answers = (array) ($data['answers'] ?? []);
        $allowedIds = $quiz->questions->pluck('id')->map(fn ($questionId) => (string) $questionId)->all();
        abort_if(array_diff(array_keys($answers), $allowedIds) !== [], 422, 'The submitted quiz contains an invalid question.');
        if ((int) $quiz->time_limit > 0) {
            if (now()->timestamp - $startedAt > ((int) $quiz->time_limit * 60) + 5) {
                $answers = [];
            }
        }

        $result = $this->quizAttempts->submit($student, $quiz, ['answers' => $answers], $startedAt > 0 ? Carbon::createFromTimestamp($startedAt) : null);
        if ($result['blocked']) {
            if ($request->expectsJson()) {
                $course = Course::with('modules.quiz.questions')->findOrFail($quiz->course_id);
                $enrollment = Enrollment::query()
                    ->where('user_id', $student->id)
                    ->where('course_id', $quiz->course_id)
                    ->firstOrFail();

                return response()->json([
                    'ok' => false,
                    'message' => 'No attempt is currently available. Check the attempt limit and retake lockout.',
                    'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
                ], 409);
            }

            return back()->withErrors(['quiz' => 'No attempt is currently available. Check the attempt limit and retake lockout.']);
        }
        if ((int) $quiz->time_limit > 0) {
            $request->session()->forget($this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id));
        }

        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $quiz->course_id)->firstOrFail();
        $course = Course::with(['modules.quiz.questions', 'modules.lessons.quizzes.questions', 'modules.lessons.activities'])->findOrFail($quiz->course_id);
        $lessonCompleted = false;
        $nextRequirement = ['url' => null, 'pending_review' => false];

        if ($quiz->lesson_id && $result['passed']) {
            $lesson = CourseLesson::query()->findOrFail($quiz->lesson_id);
            $lessonCompleted = $this->progressService->completeLessonIfRequirementsSatisfied($course, $lesson, $enrollment);
            if ($lessonCompleted) {
                $request->session()->forget($this->lessonTrackingSessionKey((int) $student->id, (int) $course->id, (int) $lesson->id));
            } else {
                $nextRequirement = $this->nextRequiredLessonAssessment($lesson, $enrollment);
            }
        }

        $this->progressService->syncEnrollmentProgress($course, $enrollment);
        $this->completionService->evaluate($enrollment);

        if ($request->expectsJson()) {
            $nextRequirement = $quiz->lesson_id && $result['passed'] && ! $lessonCompleted
                ? $this->nextRequiredLessonAssessment(CourseLesson::query()->findOrFail($quiz->lesson_id), $enrollment)
                : ['url' => null, 'pending_review' => false];

            return response()->json([
                'ok' => true,
                'score' => $result['score'],
                'passed' => $result['passed'],
                'retake_unlock_at' => $result['unlock'],
                'lesson_completed' => $lessonCompleted,
                'next_url' => $nextRequirement['url'],
                'pending_review' => $nextRequirement['pending_review'],
                'progress_percent' => (int) $enrollment->fresh()->progress_percent,
                'progress_breakdown' => $this->progressService->progressBreakdown($course, (int) $student->id, $enrollment->enrolled_at),
                'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment->fresh()),
            ]);
        }

        return redirect()->route('quiz.show', $quiz->id)->with('quiz_result', [
            'score' => $result['score'],
            'passed' => $result['passed'],
            'lesson_completed' => $lessonCompleted,
            'next_requirement_url' => $nextRequirement['url'],
            'pending_review' => $nextRequirement['pending_review'],
        ]);
    }

    public function showLessonActivity(int $id, Request $request)
    {
        $student = Auth::user();
        $activity = LessonActivity::with(['lesson.course'])->where('is_active', true)->findOrFail($id);
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $activity->lesson->course_id)
            ->firstOrFail();
        $course = Course::with(['modules.quiz.questions', 'modules.lessons.quizzes.questions', 'modules.lessons.activities'])
            ->findOrFail($activity->lesson->course_id);
        abort_unless(
            $this->lessonIsUnlockedForStudent($course, $activity->lesson, (int) $student->id, $enrollment),
            403,
            'Complete the previous lessons and module assessment before opening this activity.'
        );
        abort_unless(
            $this->lessonIsServerVerified((int) $activity->lesson_id, (int) $student->id)
                || session()->has($this->lessonContinuedSessionKey((int) $student->id, (int) $activity->lesson->course_id, (int) $activity->lesson_id)),
            403,
            'Open the lesson and select Continue before starting its activities.'
        );
        $submissions = $activity->submissions()->where('user_id', $student->id)->latest('attempt_number')->get();

        $viewData = [
            'user' => UserPresenter::student($student),
            'activity' => $activity,
            'submissions' => $submissions,
            'lesson_completed' => $this->lessonIsServerVerified((int) $activity->lesson_id, (int) $student->id),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('student.courses.partials.lesson-activity', $viewData)->render(),
            ]);
        }

        return view('student.lesson-activity', $viewData);
    }

    public function submitLessonActivity(int $id, Request $request, UserNotificationService $userNotifications)
    {
        $student = Auth::user();
        $activity = LessonActivity::with('lesson')->where('is_active', true)->findOrFail($id);
        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $activity->lesson->course_id)->firstOrFail();
        $course = Course::with(['modules.quiz.questions', 'modules.lessons.quizzes.questions', 'modules.lessons.activities'])
            ->findOrFail($activity->lesson->course_id);
        abort_unless(
            $this->lessonIsUnlockedForStudent($course, $activity->lesson, (int) $student->id, $enrollment),
            403,
            'Complete the previous lessons and module assessment before submitting this activity.'
        );
        abort_unless(
            $this->lessonIsServerVerified((int) $activity->lesson_id, (int) $student->id)
                || $request->session()->has($this->lessonContinuedSessionKey((int) $student->id, (int) $activity->lesson->course_id, (int) $activity->lesson_id)),
            403,
            'Open the lesson and select Continue before submitting its activities.'
        );
        $data = $request->validate([
            'response_text' => ['nullable', 'string', 'max:20000', 'required_without:submission_file'],
            'submission_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,png,jpg,jpeg,zip', 'max:10240'],
        ]);
        $path = null;
        if ($request->hasFile('submission_file')) {
            $path = Storage::disk('local')->putFile('lesson-activity-submissions', $request->file('submission_file'));
        }

        $attemptNumber = ((int) $activity->submissions()->where('user_id', $student->id)->max('attempt_number')) + 1;
        $submission = $activity->submissions()->create([
            'user_id' => $student->id,
            'attempt_number' => $attemptNumber,
            'response_text' => trim((string) ($data['response_text'] ?? '')) ?: null,
            'submission_path' => $path,
            'status' => $activity->activity_type === 'assignment' ? 'submitted' : 'completed',
            'submitted_at' => now(),
        ]);
        if ($submission->status === 'submitted') {
            $userNotifications->createForUser(
                $course->created_by,
                'Assignment awaiting review',
                $student->name.' submitted "'.$activity->title.'" in "'.$course->title.'".',
                'assessment',
                'activity_submission',
                $submission->id,
            );
        }

        $lessonCompleted = $this->progressService->completeLessonIfRequirementsSatisfied($course, $activity->lesson, $enrollment);
        if (! $lessonCompleted) {
            $this->syncEnrollmentProgress($course, $enrollment);
        } else {
            $request->session()->forget($this->lessonTrackingSessionKey((int) $student->id, (int) $course->id, (int) $activity->lesson_id));
        }

        $this->completionService->evaluate($enrollment);
        $nextRequirement = $lessonCompleted
            ? ['url' => null, 'pending_review' => false]
            : $this->nextRequiredLessonAssessment($activity->lesson, $enrollment);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $submission->status,
                'lesson_completed' => $lessonCompleted,
                'next_url' => $nextRequirement['url'],
                'pending_review' => $nextRequirement['pending_review'],
                'message' => $submission->status === 'submitted'
                    ? 'Your assignment was submitted and is waiting for faculty review.'
                    : 'Your activity was submitted.',
                'progress_percent' => (int) $enrollment->fresh()->progress_percent,
                'progress_breakdown' => $this->progressService->progressBreakdown($course, (int) $student->id, $enrollment->enrolled_at),
            ]);
        }

        return redirect()->route('lesson-activities.show', $activity->id)->with('activity_result', [
            'message' => 'Your activity response was submitted.',
            'lesson_completed' => $lessonCompleted,
            'next_requirement_url' => $nextRequirement['url'],
            'pending_review' => $nextRequirement['pending_review'],
        ]);
    }

    public function downloadLessonActivitySubmission(int $submissionId)
    {
        $student = Auth::user();
        $submission = LessonActivitySubmission::with(['activity.lesson.course'])->findOrFail($submissionId);
        $isStudentOwner = (int) $submission->user_id === (int) $student->id;
        $isFacultyOwner = $student->isFaculty()
            && (int) $submission->activity->lesson->course->created_by === (int) $student->id;
        abort_unless($isStudentOwner || $isFacultyOwner, 403);
        abort_unless($submission->submission_path && Storage::disk('local')->exists($submission->submission_path), 404);

        return Storage::disk('local')->download($submission->submission_path, basename($submission->submission_path));
    }

    // ── Real-time progress saving ───────────────────────────────────────

    /**
     * Called via fetch() from the course player as the student continues
     * from lesson content or submits a quiz. Persists the exact player
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
        $userId = (int) Auth::id();
        $enrollment = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->first();

        return $this->progressService->calculateProgressPercent(
            $course,
            $moduleScores,
            $userId,
            $enrollment?->enrolled_at
        );
    }

    private function quizLastEditedAt($quiz)
    {
        return $this->quizAttempts->lastEditedAt($quiz);
    }

    public function saveProgress(int $id, Request $request, CourseEvaluationService $courseEvaluation)
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
        $course = Course::with(['modules.lessons.quizzes.questions', 'modules.lessons.activities', 'modules.quiz.questions'])->findOrFail($id);
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (isset($data['retake_check'])) {
            $module = $course->modules->get((int) $data['retake_check']);
            $quiz = $module?->quiz;
            if (! $quiz) {
                return response()->json([
                    'ok' => true,
                    'quiz_unlocks' => (object) [],
                    'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
                ]);
            }

            $status = $this->quizAttempts->retakeStatus($quiz, $student->id);
            if ($status['exhausted']) {
                return response()->json([
                    'ok' => false,
                    'exhausted' => true,
                    'allowed' => $status['allowed'],
                    'used' => $status['used'],
                    'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
                ]);
            }

            return response()->json([
                'ok' => true,
                'quiz_unlocks' => $status['unlock']
                    ? [(string) $data['retake_check'] => $status['unlock']]
                    : (object) [],
                'evaluation_eligible' => $courseEvaluation->isEligible($course, $student, $enrollment),
            ]);
        }

        $moduleScores = $this->progressService->earnedModuleScores(
            $course,
            (array) ($data['module_scores'] ?? []),
            (int) $student->id
        );
        $percent = $this->progressService->calculateProgressPercent(
            $course,
            $moduleScores,
            (int) $student->id,
            $enrollment->enrolled_at
        );
        $courseLessonIds = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->pluck('id');
        $completedKeys = DB::table('lesson_completions')
            ->where('user_id', $student->id)
            ->whereIn('lesson_id', $courseLessonIds)
            ->whereNotNull('server_verified_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (string) $lessonId)
            ->all();
        $enrollment->progress_percent = $percent;
        $enrollment->progress_state = [
            'completed_lessons' => $this->progressService->resolveCompletedLessons($course, $completedKeys, $enrollment),
            'module_scores' => $moduleScores,
        ];
        $enrollment->save();
        $this->progressService->syncLessonsCompletedFlag($course, $enrollment);

        $quizUnlocks = [];
        $quizSubmissions = [];
        foreach (($data['quiz_results'] ?? []) as $moduleIndex => $result) {
            $module = $course->modules->get((int) $moduleIndex);
            $quiz = $module?->quiz;
            if (! $quiz || $quiz->questions->count() === 0) {
                continue;
            }

            if (! $this->quizIsUnlockedForStudent($quiz, (int) $student->id)) {
                $quizSubmissions[(string) $moduleIndex] = [
                    'blocked' => true,
                    'score' => null,
                    'passed' => false,
                    'unlock' => null,
                    'message' => 'Complete the required course content before taking this quiz.',
                ];

                continue;
            }

            $startedAt = null;
            if ((int) $quiz->time_limit > 0) {
                $sessionKey = $this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id);
                $startedAt = (int) $request->session()->get($sessionKey, 0);
                if ($startedAt <= 0) {
                    $quizSubmissions[(string) $moduleIndex] = [
                        'blocked' => true,
                        'score' => null,
                        'passed' => false,
                        'message' => 'Start the quiz before submitting your answers.',
                    ];

                    continue;
                }
                if (now()->timestamp - $startedAt > ((int) $quiz->time_limit * 60) + 5) {
                    $result['answers'] = [];
                }
            }

            $submission = $this->quizAttempts->submit(
                $student,
                $quiz,
                $result,
                $startedAt > 0 ? Carbon::createFromTimestamp($startedAt) : null
            );
            if ($submission['blocked']) {
                $submission['message'] = $submission['unlock'] !== null
                    ? 'This quiz is in its retake lockout period.'
                    : 'No attempt is currently available. Check the attempt limit and retake lockout.';
            }
            $quizSubmissions[(string) $moduleIndex] = $submission;
            if (! $submission['blocked'] && (int) $quiz->time_limit > 0) {
                $request->session()->forget($this->quizTrackingSessionKey((int) $student->id, (int) $quiz->id));
            }
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
        $progressBreakdown = $this->progressService->progressBreakdown(
            $course,
            (int) $student->id,
            $enrollment->enrolled_at
        );
        $percent = $progressBreakdown['percent'];
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
        $enrollment = $enrollment->fresh();
        $evaluationEligible = $courseEvaluation->isEligible($course, $student, $enrollment);

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
            'progress_breakdown' => $progressBreakdown,
            'badge_awarded' => $badgeAwarded,
            'quiz_unlocks' => (object) $quizUnlocks,
            'quiz_attempts' => (object) $quizAttemptSummary,
            'quiz_submissions' => (object) $quizSubmissions,
            'evaluation_eligible' => $evaluationEligible,
        ]);
    }

    // ── Course completion -> badge award ────────────────────────────────

    /**
     * Called via fetch() from the course player when all learning
     * requirements are complete. Marks the enrollment complete and awards
     * eligible credentials only after the required institutional gates pass.
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

        if (! CourseReview::query()->where('course_id', $course->id)->where('user_id', $student->id)->exists()) {
            return response()->json([
                'ok' => false,
                'completed' => false,
                'message' => 'Submit the course review before requesting course completion.',
            ], 422);
        }

        $enrollment = $this->completionService->evaluate($enrollment);
        $learningRequirementsComplete = $enrollment->lessons_completed
            && $enrollment->quizzes_completed
            && $enrollment->quiz_mastery_met
            && $enrollment->competency_mastery_met;

        if (! $learningRequirementsComplete) {
            return response()->json([
                'ok' => false,
                'completed' => $this->completionService->isOfficiallyCompleted($enrollment),
                'progress_percent' => (int) $enrollment->progress_percent,
                'message' => 'Finish all required lessons, activities, quizzes, and competency requirements before requesting course completion.',
            ], 422);
        }

        // finalizeCompletion() runs the real institutional gate chain
        // via MicrocredentialCompletionService and only issues credentials
        // once completion_status genuinely reaches 'completed'.
        $badgeAwarded = $this->finalizeCompletion($student, $course, $enrollment);
        $enrollment = $enrollment->fresh();
        $officiallyCompleted = $this->completionService->isOfficiallyCompleted($enrollment);
        $awaitingInstitutionalReview = $enrollment->completion_status === MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION;

        return response()->json([
            'ok' => true,
            'completed' => $officiallyCompleted,
            'progress_percent' => (int) $enrollment->progress_percent,
            'pending_review' => $awaitingInstitutionalReview,
            'message' => $officiallyCompleted
                ? null
                : ($awaitingInstitutionalReview
                    ? 'Your learning requirements are complete. Official completion is pending institutional review.'
                    : 'Your learning requirements are complete. Additional configured course requirements are still pending.'),
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

        $badges = UserBadge::with(['badge.pathway', 'badge.courses:id,title,badge_id'])
            ->where('user_id', $auth->id)
            ->latest('earned_at')
            ->get()
            ->map(fn (UserBadge $ub) => (object) [
                'id' => $ub->id,
                'name' => $ub->badge->name ?? 'Badge',
                'description' => $ub->badge->description ?? '',
                'icon_url' => $ub->badge->icon_url ?? null,
                'earned_at' => $ub->earned_at,
                'badge_level' => $ub->badge->badge_level ?? null,
                'pqf_level' => $ub->pqf_level_snapshot ?: ($ub->badge->pqf_level ?? null),
                'issuing_institution' => $ub->badge->issuing_institution ?? null,
                'pathway_name' => $ub->badge?->pathway?->name,
                'course_names' => $ub->badge?->courses?->pluck('title')->filter()->values() ?? collect(),
                'credential_uid' => $ub->credential_uid,
                'status' => $ub->status ?: 'active',
                'revoked_at' => $ub->revoked_at,
                'revocation_reason' => $ub->revocation_reason,
                'competencies' => $ub->competencies_snapshot ?? [],
                'learning_outcomes' => $ub->learning_outcomes_snapshot ?? [],
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
            ->paginate(20)
            ->withQueryString();
        $certificates->getCollection()->transform(fn ($cert) => (object) [
            'id' => $cert->id,
            'course_name' => $cert->course?->title ?: $cert->title ?: 'Microcredential Certificate',
            'issued_at' => $cert->issued_at,
            'issued_date' => $cert->issued_at?->format('F j, Y') ?? 'Date unavailable',
            'view_url' => route('certificates.view', $cert->serial),
            'download_url' => route('certificates.download', $cert->serial),
        ]);

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
     * writes. The PDF is refreshed from the certificate's immutable
     * snapshot data so downloads use the current certificate layout.
     */
    public function downloadCertificate(string $serial)
    {
        $certificate = Certificate::where('serial', $serial)->firstOrFail();

        abort_if($certificate->user_id !== Auth::id(), 403);

        $certificate = CertificateBuilder::ensureFileGenerated($certificate, refresh: true);

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
        $enrollments = Enrollment::with([
            'course.modules.quiz.questions',
            'course.modules.lessons.quizzes.questions',
            'course.modules.lessons.activities',
        ])->where('user_id', $auth->id)->get();
        $enrollments->each(fn (Enrollment $enrollment) => $this->syncEnrollmentProgress($enrollment->course, $enrollment));
        $completed = $enrollments->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED);
        $completedTitles = $completed->map(fn ($e) => $e->course->title ?? '')->filter()->values();
        $competencies = $completed
            ->flatMap(fn ($e) => collect(array_merge((array) ($e->course->related_skills ?? []), (array) ($e->course->skills ?? [])))
                ->concat([$e->course->category])->filter())
            ->merge($auth->skills_have ?? [])
            ->filter()
            ->unique()
            ->values();

        $availablePathways = Pathway::query()
            ->where('is_active', true)
            ->with(['courses' => fn ($query) => $query->where('is_published', true)])
            ->orderBy('name')
            ->get();
        $selectedPathway = $availablePathways->firstWhere('id', (int) $auth->pathway_id);

        $pathwayCourses = $selectedPathway?->courses ?? collect();
        $desiredComps = collect($selectedPathway?->desired_competencies ?? [])
            ->merge($selectedPathway?->current_competencies ?? [])
            ->merge($selectedPathway?->missing_competencies ?? [])
            ->merge($pathwayCourses->flatMap(fn ($course) => collect(array_merge((array) ($course->related_skills ?? []), (array) ($course->skills ?? [])))
                ->concat([$course->category])->filter()))
            ->filter()
            ->unique()
            ->values();
        $currentComps = $desiredComps
            ->filter(fn ($item) => $this->studentHasCompetency((string) $item, $competencies, $completedTitles))
            ->values();
        $missingComps = $desiredComps
            ->reject(fn ($item) => $this->studentHasCompetency((string) $item, $competencies, $completedTitles))
            ->values();
        $readiness = $desiredComps->isNotEmpty()
            ? (int) round(($currentComps->count() / $desiredComps->count()) * 100)
            : 0;

        $enrollmentsByCourse = $enrollments->keyBy('course_id');
        $completedIds = $completed->pluck('course_id')->map(fn ($id) => (int) $id);
        $currentCourse = $pathwayCourses->first(function ($course) use ($enrollmentsByCourse, $completedIds) {
            $enrollment = $enrollmentsByCourse->get($course->id);

            return $enrollment && ! $completedIds->contains((int) $course->id);
        }) ?? $pathwayCourses->first(fn ($course) => ! $completedIds->contains((int) $course->id));

        $steps = [];
        if ($selectedPathway) {
            $steps = $pathwayCourses->values()->map(function ($course, $index) use ($completedIds, $currentCourse) {
                $status = $completedIds->contains((int) $course->id)
                    ? 'completed'
                    : (($currentCourse && (int) $currentCourse->id === (int) $course->id) ? 'current' : 'locked');

                return [
                    'label' => 'Goal '.($index + 1),
                    'title' => $course->title,
                    'status' => $status,
                ];
            })->all();
        }

        $currentEnrollment = $currentCourse ? $enrollmentsByCourse->get($currentCourse->id) : null;
        $currentCourseSkills = collect($currentCourse ? array_merge((array) ($currentCourse->related_skills ?? []), (array) ($currentCourse->skills ?? [])) : [])
            ->filter()->unique()->values();
        $skillsDone = $currentCourseSkills->filter(fn ($skill) => $this->studentHasCompetency((string) $skill, $competencies, $completedTitles))->count();
        $destination = $selectedPathway?->destination ?: ($selectedPathway?->desired_title ?: $selectedPathway?->name);
        $recommendations = $this->buildRecommendations(
            $auth,
            $enrollments,
            $missingComps,
            $selectedPathway ?? (object) [],
            $auth->skills_want ?? []
        );

        return view('student.pathways', [
            'user' => UserPresenter::student($auth),
            'availablePathways' => $availablePathways,
            'selectedPathway' => $selectedPathway,
            'pathwaySteps' => $steps,
            'pathwayDestination' => $destination,
            'pathwayCourseCount' => $pathwayCourses->count(),
            'pathwayCompleteCount' => $pathwayCourses->filter(fn ($course) => $completedIds->contains((int) $course->id))->count(),
            'currentGoal' => $currentCourse ? [
                'id' => $currentCourse->id,
                'title' => $currentCourse->title,
                'description' => Str::limit(trim(strip_tags((string) ($currentCourse->short_description ?? $currentCourse->description ?? 'Continue along your selected pathway.'))), 180),
                'pct' => (int) ($currentEnrollment->progress_percent ?? 0),
                'skillsDone' => $skillsDone,
                'skillsTotal' => $currentCourseSkills->count(),
                'url' => route('courses.show', $currentCourse->id),
            ] : null,
            'currentCompetencies' => $competencies->take(12)->values()->all(),
            'nextCompetencies' => $currentCourseSkills->reject(fn ($skill) => $this->studentHasCompetency((string) $skill, $competencies, $completedTitles))->take(12)->values()->all(),
            'pathwayMatch' => [
                'percent' => $pathwayCourses->count() > 0 ? (int) round(($completedIds->intersect($pathwayCourses->pluck('id'))->count() / $pathwayCourses->count()) * 100) : 0,
                'complete' => $pathwayCourses->filter(fn ($course) => $completedIds->contains((int) $course->id))->count(),
                'total' => $pathwayCourses->count(),
                'next' => $currentCourse?->title ?? ($destination ?: 'Choose a pathway'),
            ],
            'recommendations' => $recommendations,
            'desiredPathway' => [
                'title' => $selectedPathway?->desired_title ?? $selectedPathway?->name,
                'current_competencies' => $currentComps->all(),
                'missing_competencies' => $missingComps->all(),
            ],
            'readinessPercent' => $readiness,
            'readinessLabel' => $selectedPathway?->readiness_label ?? $destination,
        ]);
    }

    public function selectPathway(Request $request)
    {
        $data = $request->validate([
            'pathway_id' => ['required', 'integer', 'exists:pathways,id'],
        ]);

        $pathway = Pathway::query()
            ->where('is_active', true)
            ->findOrFail($data['pathway_id']);

        Auth::user()->forceFill(['pathway_id' => $pathway->id])->save();

        return redirect()->route('pathways.index')->with('success', 'Your pathway has been selected.');
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
            $courseSkills = array_merge((array) ($course->related_skills ?? []), (array) ($course->skills ?? []));
            $text = strtolower($course->title.' '.($course->category ?? '').' '.implode(' ', $courseSkills));

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
                'course_id' => $row['course']->id,
                'title' => ($enrollment ? 'Finish: ' : 'Take: ').Str::limit($row['course']->title, 32, ''),
                'completion' => (int) ($enrollment->progress_percent ?? 0),
            ];
        })->all();
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics()
    {
        $auth = Auth::user();

        $enrollments = Enrollment::with([
            'course.modules.quiz.questions',
            'course.modules.lessons.quizzes.questions',
            'course.modules.lessons.activities',
        ])->where('user_id', $auth->id)->get();
        $enrollments->each(fn (Enrollment $enrollment) => $this->syncEnrollmentProgress($enrollment->course, $enrollment));

        $avgScore = QuizAttempt::where('user_id', $auth->id)->avg('score');
        $recentQuizAttempts = QuizAttempt::query()
            ->with('quiz.course')
            ->where('user_id', $auth->id)
            ->latest('submitted_at')
            ->limit(20)
            ->get();

        // Analytics progress rows: each enrolled course and its stored status.
        $completionCourses = $enrollments
            ->sortBy('course.title')
            ->map(fn (Enrollment $e) => (object) [
                'title' => $e->course->title ?? 'Course',
                'percent' => (int) round((float) $e->progress_percent),
                'status' => match ($e->completion_status) {
                    MicrocredentialCompletionService::STATUS_COMPLETED => 'Complete',
                    MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION => 'Awaiting verification',
                    default => 'In progress',
                },
            ])->values();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $completionCoursesPage = new LengthAwarePaginator(
            $completionCourses->forPage($currentPage, 20)->values(),
            $completionCourses->count(),
            20,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()],
        );

        return view('student.analytics', [
            'user' => UserPresenter::student($auth),
            'stats' => [
                'completed_courses' => $enrollments->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)->count(),
                'badges_earned' => $auth->badges()->wherePivot('status', 'active')->count(),
                'certificates' => $auth->certificates()->where('status', 'active')->count(),
                'quiz_attempts_count' => QuizAttempt::where('user_id', $auth->id)->count(),
                'score_avg' => $avgScore !== null ? round((float) $avgScore, 1) : null,
                'competencies_mastered' => CompetencyProgress::where('user_id', $auth->id)
                    ->where('status', 'completed')
                    ->count(),
            ],
            'completionCourses' => $completionCourses,
            'completionCoursesPage' => $completionCoursesPage,
            'recentQuizAttempts' => $recentQuizAttempts,
            'analyticsSeries' => $this->studentAnalyticsSeries($auth->id, $enrollments),
        ]);
    }

    public function downloadAnalyticsReport()
    {
        $data = $this->analytics()->getData();
        $data['generatedAt'] = now();
        $data['preparedBy'] = Auth::user();

        return Pdf::loadView('student.analytics-report-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('Student-Learning-Report-'.now()->format('Y-m-d').'.pdf');
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
                $labels = collect(range(3, 0))->map(fn (int $weeks) => $now->copy()->subWeeks($weeks)->startOfWeek()->format('M j'))->all();
                $bucketStart = fn (int $index) => $now->copy()->subWeeks(4 - $index)->startOfWeek();
                $bucketEnd = fn (int $index) => $bucketStart($index)->copy()->addWeek();
            }

            $progress = [];
            $hours = [];
            $assessment = [];
            $streak = [];
            $active = [];
            $lessons = [];
            $completedLessons = 0;
            $runningStreak = 0;

            foreach (array_keys($labels) as $index) {
                $start = $bucketStart($index);
                $end = $bucketEnd($index);
                $completed = $lessonCompletions->filter(fn (LessonCompletion $item) => $item->completed_at >= $start && $item->completed_at < $end);
                $completedLessons += $completed->count();
                $lessons[] = $completed->count();
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
                'lessons' => $lessons,
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
        preg_match_all('/(\d+(?:\.\d+)?)\s*(hours?|hrs?|h|minutes?|mins?|m)?/i', (string) $duration, $matches, PREG_SET_ORDER);
        $hours = 0.0;

        foreach ($matches as $match) {
            $value = (float) $match[1];
            $unit = strtolower($match[2] ?? 'minutes');
            $hours += in_array($unit, ['h', 'hr', 'hrs', 'hour', 'hours'], true) ? $value : $value / 60;
        }

        return $hours;
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
            'source' => Auth::user()->isFaculty() ? 'faculty' : 'student',
            'subject' => $data['subject'],
            'message' => trim($data['message']),
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $attachmentName,
            'category' => $data['category'] ?? 'general',
            'status' => 'open',
            'last_reply_at' => now(),
            'student_read_at' => now(),
        ]);

        $inboxRoute = Auth::user()->isFaculty() ? 'faculty.inbox' : 'inbox.index';

        return redirect()->route($inboxRoute, ['thread' => $complaint->id])
            ->with('success', 'Your message was sent to the administrators.');
    }

    /** Student replies within an existing thread. */
    public function replyComplaint(Request $request, int $id)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = Auth::user();
        $complaint = Complaint::where('user_id', $user->id)->findOrFail($id);

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'body' => trim($data['body']),
            'is_admin' => false,
        ]);

        $complaint->last_reply_at = now();
        $complaint->student_read_at = now();
        $complaint->save();

        $inboxRoute = $user->isFaculty() ? 'faculty.inbox' : 'inbox.index';

        return redirect()->route($inboxRoute, ['thread' => $complaint->id]);
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
