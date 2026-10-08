<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Certificate;
use App\Models\CompetencyUnit;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LessonActivity;
use App\Models\LessonActivitySubmission;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\CourseCreationService;
use App\Services\CourseLessonService;
use App\Services\CourseModuleService;
use App\Services\CourseReadinessService;
use App\Services\LearningOutcomeService;
use App\Services\MicrocredentialCompletionService;
use App\Services\QuizManagementService;
use App\Services\StudentProgressService;
use App\Support\CertificateBuilder;
use App\Support\SkillCatalog;
use App\Support\UserPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * FacultyController — all Faculty_* pages, backed by the database.
 *
 * Course ownership is courses.created_by; modules / lessons / quizzes are
 * real rows in course_modules, course_lessons, quizzes and quiz_questions.
 * The Faculty_My_Courses Blade drives its forms with $module->idx and
 * $lesson->key — we feed it the real record ids ('mod-#' / 'les-#' keys),
 * so the view works unchanged.
 */
class FacultyController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $auth = Auth::user();

        $courses = Course::where('created_by', $auth->id)
            ->withCount(['modules', 'enrollments'])
            ->latest()
            ->get();
        $monthStarts = collect(range(5, 0))
            ->map(fn ($offset) => now()->subMonths($offset)->startOfMonth());
        $enrollmentDates = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->where('enrolled_at', '>=', $monthStarts->first())
            ->pluck('enrolled_at');
        $monthlyCounts = $monthStarts->map(function (Carbon $month) use ($enrollmentDates) {
            $nextMonth = $month->copy()->addMonth();

            return (object) [
                'label' => $month->format('M'),
                'count' => $enrollmentDates->filter(function ($date) use ($month, $nextMonth) {
                    $enrolledAt = $date instanceof Carbon ? $date : Carbon::parse($date);

                    return $enrolledAt->gte($month) && $enrolledAt->lt($nextMonth);
                })->count(),
            ];
        });
        $monthlyMax = max(1, (int) $monthlyCounts->max('count'));
        $monthlyEnrollments = $monthlyCounts->map(fn ($month) => (object) [
            'label' => $month->label,
            'count' => $month->count,
            'percent' => (int) round(($month->count / $monthlyMax) * 100),
        ]);

        $stats = [
            'total_courses' => $courses->count(),
            'published' => $courses->filter(fn (Course $c) => $c->statusLabel() === 'Published')->count(),
            'total_students' => Enrollment::whereIn('course_id', $courses->pluck('id'))->distinct()->count('user_id'),
            'enrollments' => Enrollment::whereIn('course_id', $courses->pluck('id'))->count(),
        ];

        return view('faculty.dashboard', [
            'user' => UserPresenter::faculty($auth),
            'stats' => $stats,
            'monthlyEnrollments' => $monthlyEnrollments,
            'courses' => $courses->map(fn (Course $c) => (object) [
                'id' => $c->id,
                'title' => $c->title,
                'status' => $c->statusLabel(),
                'students_count' => (int) $c->enrollments_count,
                'modules_count' => (int) $c->modules_count,
                'thumbnail_url' => $c->thumbnail_url,
            ])->values(),
        ]);
    }

    // ── Enrolled Students (this faculty's courses only) ─────────────────

    public function students()
    {
        $auth = Auth::user();

        $courses = Course::where('created_by', $auth->id)
            ->withCount([
                'enrollments',
                'enrollments as faculty_reviews_count' => fn ($query) => $query
                    ->whereIn('faculty_verification_status', ['pending', 'not_required'])
                    ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION),
            ])
            ->latest()
            ->get()
            ->map(fn (Course $course) => (object) [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->statusLabel(),
                'students_count' => (int) $course->enrollments_count,
                'faculty_reviews_count' => $course->requires_faculty_verification ? (int) $course->faculty_reviews_count : 0,
                'requires_faculty_verification' => (bool) $course->requires_faculty_verification,
            ]);

        return view('faculty.students', [
            'user' => UserPresenter::faculty($auth),
            'courses' => $courses,
        ]);
    }

    /** Show an owned course's paginated enrollment roster and review queue. */
    public function courseStudents(Request $request, int $id)
    {
        $course = $this->ownedCourse($id);
        $filter = $request->query('filter', 'needs_review');
        if (! in_array($filter, ['needs_review', 'all'], true)) {
            $filter = 'needs_review';
        }

        $enrollments = Enrollment::query()
            ->where('course_id', $course->id)
            ->with('user')
            ->when($filter === 'needs_review', function ($query) use ($course) {
                if (! $course->requires_faculty_verification) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereIn('faculty_verification_status', ['pending', 'not_required'])
                    ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION);
            })
            ->orderByRaw("CASE WHEN faculty_verification_status IN ('pending', 'not_required') AND completion_status = ? THEN 0 ELSE 1 END", [MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION])
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $needsReviewCount = $course->requires_faculty_verification
            ? Enrollment::query()
                ->where('course_id', $course->id)
                ->whereIn('faculty_verification_status', ['pending', 'not_required'])
                ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
                ->count()
            : 0;

        return view('faculty.students.course', [
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => $course,
            'enrollments' => $enrollments,
            'filter' => $filter,
            'needsReviewCount' => $needsReviewCount,
            'totalStudents' => Enrollment::where('course_id', $course->id)->count(),
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        $auth = Auth::user();
        $courses = Course::where('created_by', $auth->id)->withCount('enrollments')->get();
        $courseIds = $courses->pluck('id')->all() ?: [0];

        // Teaching performance — monthly enrollments over the last 6
        // months, normalised to a percentage for the bar chart.
        // N+1 fix: this ran one COUNT query per month (6 total). A single
        // query now feeds every bucket, grouped in PHP so it stays portable
        // across SQLite and MySQL.
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $enrolledAts = Enrollment::whereIn('course_id', $courseIds)
            ->where('enrolled_at', '>=', $months->first())
            ->pluck('enrolled_at');

        $counts = $months->map(fn (Carbon $m) => $enrolledAts
            ->filter(fn ($ts) => Carbon::parse($ts)->isSameMonth($m))
            ->count());
        $peak = max(1, (int) $counts->max());

        $performance = $months->map(fn (Carbon $m) => [
            'label' => $m->format('M'),
            'percent' => (int) round($counts->shift() / $peak * 100),
        ])->all();

        // Weekly activity — analytics events on this faculty member's
        // courses, bucketed per weekday.
        $weekStart = now()->startOfWeek();
        $dowCounts = DB::table('analytics_events')
            ->where('occurred_at', '>=', $weekStart)
            ->pluck('occurred_at')
            ->map(fn ($ts) => (int) Carbon::parse($ts)->dayOfWeek) // 0=Sun … 6=Sat
            ->countBy();

        $activity = collect(range(0, 6))->map(function ($i) use ($dowCounts) {
            $dow = ($i + 1) % 7; // Mon=1 … Sun=0

            return ['label' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][$i], 'hours' => (int) ($dowCounts[$dow] ?? 0)];
        })->all();

        // N+1 fix: average progress per course was queried one course at a
        // time. A single grouped query covers the whole list.
        $avgProgressByCourse = Enrollment::whereIn('course_id', $courseIds)
            ->select('course_id', DB::raw('AVG(progress_percent) as avg_progress'))
            ->groupBy('course_id')
            ->pluck('avg_progress', 'course_id');

        return view('faculty.profile', [
            'user' => UserPresenter::faculty($auth),
            'languages' => ['English', 'Filipino'],
            'timezones' => [
                '(GMT + 8:00) Asia/Manila',
                '(GMT + 5:30) Asia/Kolkata',
                '(GMT + 0:00) UTC',
            ],
            'performance' => $performance,
            'activity' => $activity,
            'profileCourses' => $courses->map(fn (Course $c) => (object) [
                'title' => $c->title,
                'category' => $c->level ?? 'Course',
                'students' => (int) $c->enrollments_count,
                'rating' => null,
                'completion' => (int) round((float) ($avgProgressByCourse[$c->id] ?? 0)),
                'earnings' => '$0',
                'status' => $c->statusLabel(),
                'thumbnail_url' => $c->thumbnail_url,
            ])->values(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:60'],
            'about' => ['nullable', 'string', 'max:600'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:30'],
            'education' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:600'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$auth->id],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'language' => ['nullable', 'string', 'max:40'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'avatar_base64' => ['nullable', 'string'],
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

        $parts = preg_split('/\s+/', trim($data['name']), 2);
        $auth->first_name = $parts[0];
        $auth->last_name = $parts[1] ?? $auth->last_name;
        $auth->email = $data['email'];
        $auth->phone = $data['phone'] ?? null;
        $auth->location = $data['location'] ?? null;
        $auth->about = $data['about'] ?? null;
        $auth->gender = $data['gender'] ?? null;
        $auth->education = $data['education'] ?? null;
        $auth->bio = $data['bio'] ?? null;
        $auth->language = $data['language'] ?? null;
        $auth->timezone = $data['timezone'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }
        if (! empty($data['date_of_birth'])) {
            $auth->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
        }
        if (! empty($data['avatar_base64']) && str_starts_with($data['avatar_base64'], 'data:image/')) {
            $auth->avatar_url = $data['avatar_base64'];
        }

        $auth->profile_completed = true;
        $auth->save();

        return redirect()->route('faculty.profile')->with('success', 'Profile updated successfully!');
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics()
    {
        $auth = Auth::user();
        $courseIds = Course::where('created_by', $auth->id)->pluck('id')->all() ?: [0];
        $enrollBase = Enrollment::whereIn('course_id', $courseIds);
        $learnerIds = (clone $enrollBase)->select('user_id')->distinct();
        // Cumulative learner growth, one point per month for the last year.
        // N+1 fix: this ran 12 separate COUNT queries (one per month). The
        // cumulative total equals the number of learners whose FIRST
        // enrollment happened on or before that month-end, so one pass over
        // the enrollments feeds every point.
        $firstEnrollmentAt = Enrollment::whereIn('course_id', $courseIds)
            ->get(['user_id', 'enrolled_at'])
            ->groupBy('user_id')
            ->map(fn ($rows) => Carbon::parse($rows->min('enrolled_at')));

        $academyStats = collect(range(11, 0))->map(function ($i) use ($firstEnrollmentAt) {
            $month = now()->subMonths($i);
            $monthEnd = $month->copy()->endOfMonth();

            return [
                'label' => $month->format('M j,')."\n".$month->format('Y'),
                'value' => $firstEnrollmentAt->filter(fn (Carbon $first) => $first->lte($monthEnd))->count(),
            ];
        })->all();

        // Learner success breakdown from real progress values.
        $progressRows = (clone $enrollBase)
            ->select(
                'user_id',
                DB::raw('MAX(progress_percent) as best'),
                DB::raw("MAX(CASE WHEN completion_status = 'completed' THEN 1 ELSE 0 END) as done")
            )
            ->groupBy('user_id')
            ->get();

        $learnerSuccess = [
            ['label' => 'Completed one or more courses',         'count' => $progressRows->where('done', 1)->count()],
            ['label' => 'Got through at least half of a course', 'count' => $progressRows->where('done', 0)->where('best', '>=', 50)->count()],
            ['label' => 'Below 50% progress',                     'count' => $progressRows->where('done', 0)->where('best', '<', 50)->count()],
        ];
        $courseAnalytics = Course::query()
            ->where('created_by', $auth->id)
            ->withCount([
                'enrollments',
                'enrollments as completed_enrollments_count' => fn ($query) => $query
                    ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED),
            ])
            ->addSelect([
                'average_quiz_score' => DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->whereColumn('quizzes.course_id', 'courses.id')
                    ->whereNotNull('quiz_attempts.score')
                    ->selectRaw('AVG(quiz_attempts.score)'),
                'scored_attempts_count' => DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->whereColumn('quizzes.course_id', 'courses.id')
                    ->whereNotNull('quiz_attempts.score')
                    ->selectRaw('COUNT(*)'),
                'passed_attempts_count' => DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->whereColumn('quizzes.course_id', 'courses.id')
                    ->whereNotNull('quiz_attempts.score')
                    ->where('quiz_attempts.passed', true)
                    ->selectRaw('COUNT(*)'),
                'pending_reviews_count' => DB::table('lesson_activity_submissions')
                    ->join('lesson_activities', 'lesson_activities.id', '=', 'lesson_activity_submissions.lesson_activity_id')
                    ->join('course_lessons', 'course_lessons.id', '=', 'lesson_activities.lesson_id')
                    ->whereColumn('course_lessons.course_id', 'courses.id')
                    ->where('lesson_activity_submissions.status', 'submitted')
                    ->selectRaw('COUNT(*)'),
            ])
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course) => (object) [
                'title' => $course->title,
                'enrolled' => (int) $course->enrollments_count,
                'completed' => (int) $course->completed_enrollments_count,
                'completion_rate' => $course->enrollments_count > 0
                    ? (int) round($course->completed_enrollments_count / $course->enrollments_count * 100)
                    : null,
                'average_score' => $course->average_quiz_score !== null ? (int) round((float) $course->average_quiz_score) : null,
                'scored_attempts' => (int) $course->scored_attempts_count,
                'pass_rate' => $course->scored_attempts_count > 0
                    ? (int) round($course->passed_attempts_count / $course->scored_attempts_count * 100)
                    : null,
                'pending_reviews' => (int) $course->pending_reviews_count,
            ]);

        return view('faculty.analytics', [
            'user' => UserPresenter::faculty($auth),
            'onlineNow' => (int) DB::table('sessions')->whereIn('user_id', $learnerIds)
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->distinct()->count('user_id'),
            'stats' => [
                'total_learners' => (clone $enrollBase)->distinct()->count('user_id'),
                'certificates' => DB::table('certificates')->whereIn('course_id', $courseIds)->where('status', 'active')->count(),
                'lessons_done' => DB::table('lesson_completions')
                    ->join('course_lessons', 'course_lessons.id', '=', 'lesson_completions.lesson_id')
                    ->whereIn('course_lessons.course_id', $courseIds)->count(),
            ],
            'academyStats' => $academyStats,
            'learnerSuccess' => $learnerSuccess,
            'courseAnalytics' => $courseAnalytics,
        ]);
    }

    public function analyticsLive()
    {
        $courseIds = Course::query()->where('created_by', Auth::id())->pluck('id')->all();
        $learnerIds = Enrollment::query()->whereIn('course_id', $courseIds)->select('user_id')->distinct();
        $events = DB::table('analytics_events')->whereIn('user_id', $learnerIds)
            ->where(function ($query) use ($courseIds) {
                $query->where(function ($courseEvents) use ($courseIds) {
                    $courseEvents->where('entity_type', 'course')->whereIn('entity_id', $courseIds);
                })->orWhere(function ($lessonEvents) use ($courseIds) {
                    $lessonEvents->where('entity_type', 'lesson')->whereExists(function ($lessons) use ($courseIds) {
                        $lessons->selectRaw('1')->from('course_lessons')
                            ->whereColumn('course_lessons.id', 'analytics_events.entity_id')
                            ->whereIn('course_lessons.course_id', $courseIds);
                    });
                })->orWhere(function ($quizEvents) use ($courseIds) {
                    $quizEvents->where('entity_type', 'quiz')->whereExists(function ($quizzes) use ($courseIds) {
                        $quizzes->selectRaw('1')->from('quizzes')
                            ->whereColumn('quizzes.id', 'analytics_events.entity_id')
                            ->whereIn('quizzes.course_id', $courseIds);
                    });
                });
            })
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get(['event_type', 'metadata', 'occurred_at']);

        $labels = [
            'enrollment' => 'New student enrolled',
            'course_completed' => 'Course completed',
            'lesson_completed' => 'Lesson completed',
            'quiz_passed' => 'Quiz passed',
        ];

        return response()->json([
            'stats' => [
                'active_users' => (int) DB::table('sessions')->whereIn('user_id', $learnerIds)
                    ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                    ->distinct()->count('user_id'),
            ],
            'activity' => $events->map(fn ($event) => [
                'title' => $labels[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type)),
                'detail' => data_get(json_decode($event->metadata ?? 'null', true), 'detail', 'Course activity recorded'),
                'time' => Carbon::parse($event->occurred_at)->diffForHumans(),
                'type' => 'event',
            ])->values(),
        ]);
    }

    // ── My Courses (list) ─────────────────────────────────────────────────

    public function courses()
    {
        $auth = Auth::user();

        $courses = Course::where('created_by', $auth->id)
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->latest()
            ->get()
            ->map(fn (Course $c) => (object) [
                'id' => $c->id,
                'title' => $c->title,
                'short_description' => $c->short_description,
                'description' => $c->description,
                'status' => $c->statusLabel(),
                'level' => $c->level,
                'students_count' => (int) $c->enrollments_count,
                'modules_count' => (int) $c->modules_count,
                'lessons_count' => (int) $c->lessons_count,
                'thumbnail_url' => $c->thumbnail_url,
            ])->values();

        return view('faculty.courses.index', [
            'mode' => 'list',
            'user' => UserPresenter::faculty($auth),
            'courses' => $courses,
        ]);
    }

    // ── Manage Course ─────────────────────────────────────────────────────

    public function manage(CourseReadinessService $courseReadiness, ?int $id = null)
    {
        $auth = Auth::user();

        $course = $id !== null
            ? Course::where('created_by', $auth->id)->findOrFail($id)
            : Course::where('created_by', $auth->id)->oldest()->firstOrFail();

        $course->load([
            'modules.lessons.quizzes.questions',
            'modules.lessons.activities' => fn ($query) => $query->withCount([
                'submissions',
                'submissions as pending_submissions_count' => fn ($submissions) => $submissions->where('status', 'submitted'),
            ]),
            'modules.quiz.questions',
        ]);

        // Modules — idx/key carry the real ids so every Blade form
        // (add lesson, add quiz, delete) targets the right records.
        $modules = $course->modules->map(function (CourseModule $m) {
            return (object) [
                'idx' => $m->id,
                'key' => 'mod-'.$m->id,
                'title' => $m->title,
                // 'subtitle' is a short plain-text preview for the compact
                // header row; 'description' keeps the raw CKEditor HTML so
                // the edit form can load it back into the editor.
                'subtitle' => Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $m->description)))), 90),
                'description' => $m->description,
                'lessons' => $m->lessons->map(fn (CourseLesson $l) => (object) [
                    'id' => $l->id,
                    'key' => 'les-'.$l->id,
                    'title' => $l->title,
                    'meta' => 'Text'.($l->duration ? ' · '.$l->duration : ''),
                    'file_url' => $l->file_url,
                    'file_name' => $l->file_name,
                    // Raw values for the inline edit form: duration is stored
                    // as "15m", the form wants the number.
                    'content' => $l->content,
                    'duration_raw' => (int) filter_var((string) $l->duration, FILTER_SANITIZE_NUMBER_INT),
                    'thumbnail_url' => ($l->type === 'Image' && $l->file_url) ? asset($l->file_url) : null,
                    'quiz' => $l->quizzes->where('is_active', true)->first() ? (object) [
                        'id' => $l->quizzes->where('is_active', true)->first()->id,
                        'title' => $l->quizzes->where('is_active', true)->first()->title,
                        'questions_count' => $l->quizzes->where('is_active', true)->first()->questions->count(),
                        'passing_score' => $l->quizzes->where('is_active', true)->first()->passing_score,
                        'sort_order' => $l->quizzes->where('is_active', true)->first()->sort_order,
                    ] : null,
                    'activities' => $l->activities->where('is_active', true)->map(fn (LessonActivity $activity) => (object) [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'activity_type' => $activity->activity_type,
                        'instructions' => $activity->instructions,
                        'is_required' => $activity->is_required,
                        'max_points' => $activity->max_points,
                        'passing_percent' => $activity->passing_percent,
                        'sort_order' => $activity->sort_order,
                        'submissions_count' => $activity->submissions_count,
                        'pending_submissions_count' => $activity->pending_submissions_count,
                    ])->values(),
                ])->values(),
                'quiz' => $m->quiz ? (object) [
                    'title' => $m->quiz->title,
                    'questions_count' => $m->quiz->questions->count(),
                    'passing_score' => $m->quiz->passing_score,
                ] : null,
            ];
        })->values();

        // Average score per quiz in this course.
        $quizAverages = Quiz::where('course_id', $course->id)
            ->get()
            ->map(function (Quiz $q) {
                $avg = DB::table('quiz_attempts')->where('quiz_id', $q->id)->avg('score');

                return $avg !== null
                    ? (object) ['title' => $q->title, 'percent' => (int) round($avg)]
                    : null;
            })
            ->filter()
            ->values();

        return view('faculty.courses.index', [
            'mode' => 'manage',
            'user' => UserPresenter::faculty($auth),
            'course' => (object) [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'status' => $course->statusLabel(),
                'approval_status' => $course->approval_status,
                'level' => $course->level,
                'students_count' => Enrollment::where('course_id', $course->id)->count(),
                'modules_count' => $modules->count(),
                'lessons_count' => $modules->sum(fn ($m) => $m->lessons->count()),
                'thumbnail_url' => $course->thumbnail_url,
            ],
            'modules' => $modules,
            'quizAverages' => $quizAverages,
            'readinessChecklist' => $courseReadiness->checklist($course),
            'canSubmitForApproval' => $course->approval_status === 'draft' && $courseReadiness->isReady($course),
            'pendingActivityReviews' => LessonActivitySubmission::query()
                ->where('status', 'submitted')
                ->whereHas('activity', fn ($query) => $query
                    ->where('is_active', true)
                    ->where('activity_type', 'assignment')
                    ->whereHas('lesson', fn ($lessonQuery) => $lessonQuery->where('course_id', $course->id)))
                ->count(),
        ]);
    }

    /** Show the faculty review queue for assignments in an owned course. */
    public function activityReviews(Request $request, int $id)
    {
        $course = $this->ownedCourse($id);
        $status = $request->query('status', 'pending');
        if (! in_array($status, ['pending', 'reviewed', 'all'], true)) {
            $status = 'pending';
        }

        $activityId = filter_var($request->query('activity_id'), FILTER_VALIDATE_INT);
        if ($activityId !== false && $activityId > 0) {
            LessonActivity::query()
                ->where('id', $activityId)
                ->where('is_active', true)
                ->where('activity_type', 'assignment')
                ->whereHas('lesson', fn ($query) => $query->where('course_id', $course->id))
                ->firstOrFail();
        } else {
            $activityId = null;
        }

        $courseSubmissions = LessonActivitySubmission::query()
            ->whereHas('activity', fn ($query) => $query
                ->where('is_active', true)
                ->where('activity_type', 'assignment')
                ->whereHas('lesson', fn ($lessonQuery) => $lessonQuery->where('course_id', $course->id)));

        $pendingCount = (clone $courseSubmissions)->where('status', 'submitted')->count();
        $reviewedCount = (clone $courseSubmissions)->whereIn('status', ['passed', 'needs_revision'])->count();
        $allCount = (clone $courseSubmissions)->count();

        $submissions = (clone $courseSubmissions)
            ->with(['activity.lesson.module', 'student', 'reviewer'])
            ->when($activityId, fn ($query) => $query->where('lesson_activity_id', $activityId))
            ->when($status === 'pending', fn ($query) => $query->where('status', 'submitted'))
            ->when($status === 'reviewed', fn ($query) => $query->whereIn('status', ['passed', 'needs_revision']))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('faculty.courses.activity-reviews', [
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => $course,
            'submissions' => $submissions,
            'status' => $status,
            'activityId' => $activityId,
            'pendingCount' => $pendingCount,
            'reviewedCount' => $reviewedCount,
            'allCount' => $allCount,
        ]);
    }

    /** Submit a complete draft course for administrator review. */
    public function submitForApproval(CourseReadinessService $courseReadiness, int $id)
    {
        $course = $this->ownedCourse($id);
        abort_unless($course->approval_status === 'draft', 409);

        $missingRequirements = $courseReadiness->missingRequirements($course);
        if ($missingRequirements !== []) {
            return back()
                ->withErrors(['submission' => 'Complete the course readiness checklist before submitting it.']);
        }

        $course->update([
            'approval_status' => 'pending',
            'is_approved' => false,
            'is_published' => false,
            'denial_feedback' => null,
        ]);

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', 'Course submitted for administrator approval.');
    }

    /**
     * Faculty verification action (Phase 3, Step 11).
     *
     * A credential-access-layer action only: it records the faculty's
     * decision and re-runs MicrocredentialCompletionService::evaluate()
     * via recordFacultyVerification(). It never sets completion_status,
     * completed_at, issues a badge/certificate, or creates an academic
     * credit recognition record itself — that all remains exclusively
     * the completion service's responsibility.
     */
    public function verifyEnrollment(Request $request, Enrollment $enrollment, MicrocredentialCompletionService $completionService)
    {
        $enrollment->loadMissing('course');

        // Ownership check: a faculty member may only act on an enrollment
        // belonging to a course they own. Never trust the Blade button
        // being hidden — this is the actual, server-side authorization.
        abort_if(! $enrollment->course || $enrollment->course->created_by !== Auth::id(), 403);

        $data = $request->validate([
            'decision' => 'required|string|in:verified,rejected',
        ]);

        try {
            $completionService->recordFacultyVerification($enrollment, Auth::id(), $data['decision']);
        } catch (\DomainException $e) {
            // Not awaiting institutional sign-off (pre-mastery, or already
            // completed/rejected/revoked). Enforced by the service.
            abort(422, $e->getMessage());
        }

        return back()->with('success', $data['decision'] === 'verified'
            ? 'Faculty verification recorded.'
            : 'Enrollment marked as rejected.');
    }

    // ── Create Course ─────────────────────────────────────────────────────

    public function createForm()
    {
        return view('faculty.courses.create', [
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => null,
            'editing' => false,
            // Category now carries the programs (the separate Program
            // dropdown was removed).
            'categories' => $this->courseCategories(),
            'levels' => ['Beginner', 'Intermediate', 'Advanced'],
            'skillOptions' => self::skillOptions(),
            'skillGroups' => self::skillGroups(),
            // #7 Prerequisites are real courses already live on the site.
            'prereqOptions' => $this->prerequisiteOptions(),
            // #10 Badge / certificate options for the award section.
            'badgeOptions' => $this->badgeOptions(),
            'competencyUnitOptions' => CompetencyUnit::orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Edit an existing course — reuses the Create Course form, pre-filled.
     */
    public function editForm(int $id)
    {
        $course = $this->ownedCourse($id);
        $course->load('learningOutcomes');

        return view('faculty.courses.create', [
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => $course,
            'editing' => true,
            'categories' => $this->courseCategories(),
            'levels' => ['Beginner', 'Intermediate', 'Advanced'],
            'skillOptions' => self::skillOptions(),
            'skillGroups' => self::skillGroups(),
            // #7 Prerequisites are real courses already live on the site.
            'prereqOptions' => $this->prerequisiteOptions($course->id),
            // #10 Badge / certificate options for the award section.
            'badgeOptions' => $this->badgeOptions(),
            'competencyUnitOptions' => CompetencyUnit::orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Persist changes to an existing course.
     *
     * Editing an approved course sends it back to 'pending' so an admin
     * re-checks it — otherwise a faculty member could swap the content of
     * an already-published course without review.
     */
    public function updateCourse(Request $request, int $id, LearningOutcomeService $learningOutcomes)
    {
        $course = $this->ownedCourse($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:120'],
            'program' => ['nullable', 'string', 'max:120'],
            'level' => ['nullable', 'string', 'max:60'],
            'pqf_level' => ['nullable', 'regex:/^[5-8]$/'],
            'target_learners' => ['nullable', 'string', 'max:5000'],
            'delivery_mode' => ['nullable', 'string', 'in:online,blended,face_to_face,self_paced'],
            'learning_hours' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'assessment_strategy' => ['nullable', 'string', 'max:10000'],
            'grading_rubric' => ['nullable', 'string', 'max:10000'],
            'credit_bearing' => ['nullable'],
            'credit_equivalency' => ['nullable', 'string', 'max:255'],
            'equivalent_course' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'numeric', 'min:0'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'learning_objectives' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'array', 'max:50'],
            'learning_outcomes.*.description' => ['required_with:learning_outcomes', 'string', 'max:2000'],
            'learning_outcomes.*.competency_unit_id' => ['nullable', 'integer', 'exists:competency_units,id'],
            'competencies' => ['nullable', 'array'],
            'competencies.*' => ['string', 'max:120'],
            'prerequisites' => ['nullable', 'string', 'max:120'],
            'thumbnail' => ['nullable', 'image', 'max:51200'],
            'change_note' => ['nullable', 'string', 'max:2000'],
            'prerequisite_ids' => ['nullable', 'array'],
            'prerequisite_ids.*' => ['integer', 'exists:courses,id'],
            'badge_enabled' => ['nullable'],
            'badge_name' => ['nullable', 'string', 'max:120'],
            'badge_description' => ['nullable', 'string', 'max:400'],
            'badge_icon_base64' => ['nullable', 'string'],
            'requires_faculty_verification' => ['nullable', 'boolean'],
            'related_skills' => ['nullable', 'array'],
            'related_skills.*' => ['string', 'max:80'],
            'related_skills_csv' => ['nullable', 'string', 'max:4000'],
        ]);

        $selectedPrerequisiteIds = $this->prerequisiteIdsFrom($request, $course->id);
        if ($this->wouldCreatePrerequisiteCycle((int) $course->id, $selectedPrerequisiteIds)) {
            throw ValidationException::withMessages([
                'prerequisite_ids' => 'These prerequisites would create a course dependency cycle. Choose different prerequisite courses.',
            ]);
        }

        $title = trim($data['title']);
        $hours = (float) ($data['duration'] ?? 0);

        $attributes = [
            'title' => $title,
            'short_description' => trim($data['short_description']),
            'heading' => null,
            'subheading' => null,
            'description' => $this->sanitizeRichText($data['description'] ?? ''),
            'skills' => array_values($data['competencies'] ?? []),
            'related_skills' => $this->relatedSkillsFrom($request),
            'objectives' => $this->linesToArray($data['learning_objectives'] ?? null),
            'category' => $data['category'] ?? null,
            'program' => $data['program'] ?? null,
            'level' => $data['level'] ?? 'Beginner',
            'pqf_level' => $data['pqf_level'] ?? null,
            'target_learners' => trim((string) ($data['target_learners'] ?? '')) ?: null,
            'delivery_mode' => $data['delivery_mode'] ?? null,
            'learning_hours' => isset($data['learning_hours']) ? (int) $data['learning_hours'] : null,
            'assessment_strategy' => trim((string) ($data['assessment_strategy'] ?? '')) ?: null,
            'grading_rubric' => trim((string) ($data['grading_rubric'] ?? '')) ?: null,
            'credit_bearing' => $request->boolean('credit_bearing'),
            'credit_equivalency' => trim((string) ($data['credit_equivalency'] ?? '')) ?: null,
            'equivalent_course' => trim((string) ($data['equivalent_course'] ?? '')) ?: null,
            'duration' => $hours > 0 ? $hours.'h' : null,
            'passing_score' => (int) ($data['passing_score'] ?? 75),
        ];

        // Only regenerate the slug when the title actually changed, so
        // existing links to the course keep working.
        if ($title !== $course->title) {
            $attributes['slug'] = $this->uniqueSlug($title);
        }

        if ($request->hasFile('thumbnail')) {
            $attributes['thumbnail_url'] = $this->storeThumbnail($request);
        }

        // Saving any course details returns it to the private authoring state.
        // Faculty explicitly resubmit from Manage Course after readiness checks pass.
        $attributes['approval_status'] = 'draft';
        $attributes['is_approved'] = false;
        $attributes['is_published'] = false;
        $attributes['approved_at'] = null;
        $attributes['approved_by'] = null;

        $course->update($attributes);
        $outcomes = $data['learning_outcomes'] ?? [];
        if ($outcomes === []) {
            $outcomes = collect($this->linesToArray($data['learning_objectives'] ?? null) ?? [])
                ->map(fn (string $description): array => ['description' => $description])
                ->all();
        }
        $learningOutcomes->sync($course, $outcomes);

        // Written directly rather than through update(). If 'related_skills'
        // is missing from Course::$fillable, mass assignment discards it
        // SILENTLY — the save succeeds and the field is quietly dropped.
        // setAttribute + save() is not subject to that guard.
        $course->related_skills = $this->relatedSkillsFrom($request);

        // #7 / #10 — prerequisites and award settings.
        $course->prerequisite_ids = $this->prerequisiteIdsFrom($request, $course->id);
        $course->certificate_enabled = true;
        $course->certificate_title = 'Certificate of Completion';
        $course->certificate_mode = 'auto';
        $course->certificate_signature_name = $course->creator?->name ?: Auth::user()->name;
        $course->badge_id = $this->saveInlineBadge($request, $course);

        // Completion Requirements — see MicrocredentialCompletionService.
        // Independent of academic-unit confirmation, which is always
        // required and has no course-level toggle.
        $course->requires_faculty_verification = $request->boolean('requires_faculty_verification');

        // #12 — the author's summary of what changed, shown to the admin on
        // the review screen. Cleared once the course is approved again.
        $note = trim((string) $request->input('change_note'));
        if ($note !== '') {
            $course->change_note = $note;
        }

        $course->save();

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', 'Course updated.');
    }

    /**
     * Category options shared by the create and edit forms.
     *
     * Maintained by the Admin under Management › Program Categories. The
     * hardcoded fallback only applies if the table is empty, so the form is
     * never left without options.
     */
    private function courseCategories(): array
    {
        $categories = CourseCategory::activeNames();

        return $categories ?: [
            'BS Information Technology',
            'BS Computer Science',
            'BS Information Systems',
            'Web Development',
            'Artificial Intelligence',
            'Databases',
            'Networking',
            'Computer Fundamentals',
            'Project Management',
        ];
    }

    /**
     * Read a course's related skills as an array.
     *
     * Without the 'related_skills' => 'array' cast on the Course model the
     * attribute comes back as a raw JSON string, so decode defensively.
     */
    public static function relatedSkillsOf($course): array
    {
        $value = $course->related_skills ?? null;

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded));
            }
        }

        return [];
    }

    /**
     * Read the "Subject is Related on" selection from a request.
     *
     * The form posts both the checkbox array and a hidden text mirror. The
     * array is preferred; the mirror is the fallback, because a checkbox
     * group only posts the boxes that are ticked AND present at submit
     * time, whereas a plain field always arrives.
     */
    private function relatedSkillsFrom(Request $request): array
    {
        $skills = $request->input('related_skills');

        if (! is_array($skills) || ! count($skills)) {
            $csv = (string) $request->input('related_skills_csv', '');
            $skills = $csv === '' ? [] : explode('||', $csv);
        }

        return collect($skills)
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Skill vocabulary for "Subject is Related on".
     *
     * Deliberately identical to the list students pick from in the About Me
     * form — the Browse page's Recommended toggle compares the two, so any
     * divergence would silently stop courses matching.
     */
    public static function skillOptions(): array
    {
        return SkillCatalog::all();
    }

    /** Grouped form of the same catalog, for pickers with headings. */
    public static function skillGroups(): array
    {
        return SkillCatalog::groups();
    }

    public function uploadEditorAsset(Request $request)
    {
        $kind = $request->input('kind', 'file');
        if (! in_array($kind, ['image', 'file'], true)) {
            return response()->json(['message' => 'Unsupported upload type.'], 422);
        }

        $rules = $kind === 'image'
            ? ['asset' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240']]
            : ['asset' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar', 'max:20480']];

        $request->merge([
            'asset' => $request->file('upload') ?? $request->file('asset'),
        ]);

        $data = $request->validate($rules);

        $file = $data['asset'];
        $directory = public_path('uploads/editor');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $name = uniqid('editor_', true).'.'.$extension;
        $file->move($directory, $name);

        return response()->json([
            'url' => asset('uploads/editor/'.$name),
            'name' => $file->getClientOriginalName(),
            'kind' => $kind,
        ]);
    }

    private function sanitizeRichText(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        /*
        * CKEditor 5 content that UPSKILL currently supports.
        *
        * Keep this list aligned with the editor configuration.
        */
        $allowed = '<p><br>'
            .'<strong><b><em><i><u><s><del>'
            .'<sub><sup><span>'
            .'<h1><h2><h3><h4>'
            .'<ul><ol><li>'
            .'<blockquote><pre><code>'
            .'<a><img>'
            .'<figure><figcaption><oembed>';

        $html = strip_tags($html, $allowed);

        $html = preg_replace_callback(
            '/<([a-z0-9]+)\b([^>]*)>/i',
            function ($match) {
                $tag = strtolower($match[1]);
                $attrs = $match[2] ?? '';

                $allowedAttrs = match ($tag) {
                    'a' => [
                        'href',
                        'target',
                        'rel',
                        'title',
                        'class',
                        'data-file-name',
                        'data-file-type',
                    ],

                    'img' => [
                        'src',
                        'alt',
                        'title',
                        'width',
                        'height',
                    ],

                    'figure' => [
                        'class',
                        'data-file-name',
                    ],

                    'figcaption' => [
                        'class',
                    ],

                    'oembed' => [
                        'url',
                    ],

                    default => [],
                };

                /*
                * No attributes are allowed on normal text/formatting
                * elements such as p, h1, strong, sub, sup, etc.
                *
                * Alignment is handled separately below.
                */
                preg_match_all(
                    '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/u',
                    $attrs,
                    $parts,
                    PREG_SET_ORDER
                );

                $safe = [];

                foreach ($parts as $part) {
                    $name = strtolower($part[1]);

                    /*
                    * CKEditor uses inline style for alignment:
                    *
                    * style="text-align:center"
                    *
                    * We allow ONLY the text-align property and ONLY
                    * the four standard alignment values.
                    */
                    if ($name === 'style') {
                        if (! in_array($tag, ['p', 'h1', 'h2', 'h3', 'h4', 'span'], true)) {
                            continue;
                        }

                        $value = $part[2] !== ''
                            ? $part[2]
                            : ($part[3] !== '' ? $part[3] : $part[4]);

                        $value = trim($value);
                        $safeStyles = [];

                        foreach (explode(';', $value) as $declaration) {
                            $styleParts = explode(':', $declaration, 2);
                            if (count($styleParts) !== 2) {
                                continue;
                            }

                            $property = strtolower(trim($styleParts[0]));
                            $styleValue = trim($styleParts[1]);

                            if ($property === 'text-align'
                                && preg_match('/^(left|center|right|justify)$/i', $styleValue)) {
                                $safeStyles[] = 'text-align:'.strtolower($styleValue);

                                continue;
                            }

                            // CKEditor emits safe HSL, RGB, or hexadecimal color values.
                            // Restrict these properties and formats to prevent CSS injection.
                            if (in_array($property, ['color', 'background-color'], true)
                                && preg_match('/^(#[0-9a-f]{3,8}|(?:rgb|hsl)a?\([0-9.%+,\s-]+\))$/i', $styleValue)) {
                                $safeStyles[] = $property.':'.$styleValue;
                            }
                        }

                        if ($safeStyles) {
                            $safe[] = 'style="'.e(implode(';', $safeStyles)).'"';
                        }

                        continue;
                    }

                    if (! in_array($name, $allowedAttrs, true)) {
                        continue;
                    }

                    $value = $part[2] !== ''
                        ? $part[2]
                        : ($part[3] !== '' ? $part[3] : $part[4]);

                    /*
                    * URLs must never be allowed to execute JavaScript
                    * or other dangerous protocols.
                    */
                    if (in_array($name, ['href', 'src', 'url'], true)) {
                        $value = trim($value);

                        if (preg_match('/^(javascript|vbscript|data):/i', $value)) {
                            continue;
                        }

                        /*
                        * Relative URLs and HTTP/HTTPS URLs are allowed.
                        */
                        if (
                            in_array($name, ['src', 'href'], true)
                            && ! preg_match('/^(https?:\/\/|\/)/i', $value)
                        ) {
                            continue;
                        }
                    }

                    $safe[] = $name.'="'.e($value).'"';
                }

                return '<'.$tag
                    .($safe ? ' '.implode(' ', $safe) : '')
                    .'>';
            },
            $html
        );

        return trim($html);
    }

    private function saveInlineBadge(Request $request, Course $course): ?int
    {
        if (! $request->boolean('badge_enabled')) {
            return null;
        }
        $name = trim((string) $request->input('badge_name'));
        if ($name === '') {
            throw ValidationException::withMessages(['badge_name' => 'Badge name is required when badge issuance is enabled.']);
        }
        $badge = $course->badge ?: new Badge;
        $badge->name = $name;
        $badge->description = trim((string) $request->input('badge_description')) ?: null;
        $badge->badge_level = null;
        $badge->is_active = true;
        $icon = (string) $request->input('badge_icon_base64');
        if ($icon !== '' && str_starts_with($icon, 'data:image/')) {
            $badge->icon_url = $icon;
        }
        $badge->save();

        return (int) $badge->id;
    }

    public function createStore(Request $request, CourseCreationService $courseCreationService, LearningOutcomeService $learningOutcomes)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:120'],
            'program' => ['nullable', 'string', 'max:120'],
            'level' => ['nullable', 'string', 'max:60'],
            'pqf_level' => ['nullable', 'regex:/^[5-8]$/'],
            'target_learners' => ['nullable', 'string', 'max:5000'],
            'delivery_mode' => ['nullable', 'string', 'in:online,blended,face_to_face,self_paced'],
            'learning_hours' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'assessment_strategy' => ['nullable', 'string', 'max:10000'],
            'grading_rubric' => ['nullable', 'string', 'max:10000'],
            'credit_bearing' => ['nullable'],
            'credit_equivalency' => ['nullable', 'string', 'max:255'],
            'equivalent_course' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'numeric', 'min:0'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'learning_objectives' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'array', 'max:50'],
            'learning_outcomes.*.description' => ['required_with:learning_outcomes', 'string', 'max:2000'],
            'learning_outcomes.*.competency_unit_id' => ['nullable', 'integer', 'exists:competency_units,id'],
            'competencies' => ['nullable', 'array'],
            'competencies.*' => ['string', 'max:120'],
            'prerequisites' => ['nullable', 'string', 'max:120'],
            // NOTE: no "boolean" rule — a checked HTML checkbox sends the
            // string "on", which fails boolean validation and silently
            // bounced the form back. $request->boolean() handles it below.
            'feature_homepage' => ['nullable'],
            'thumbnail' => ['nullable', 'image', 'max:51200'],   // 50 MB
            'related_skills' => ['nullable', 'array'],
            'related_skills.*' => ['string', 'max:80'],
            'related_skills_csv' => ['nullable', 'string', 'max:4000'],
            'prerequisite_ids' => ['nullable', 'array'],
            'prerequisite_ids.*' => ['integer', 'exists:courses,id'],
            'badge_enabled' => ['nullable'],
            'badge_name' => ['nullable', 'string', 'max:120'],
            'badge_description' => ['nullable', 'string', 'max:400'],
            'badge_icon_base64' => ['nullable', 'string'],
            // See the NOTE above about checkbox values — validated as
            // present-or-absent only; CourseCreationService reads the
            // actual value via $request->boolean().
            'requires_faculty_verification' => ['nullable'],
        ]);

        try {
            $course = $courseCreationService->create($request, $data, $auth);
            $outcomes = $data['learning_outcomes'] ?? [];
            if ($outcomes === []) {
                $outcomes = collect($this->linesToArray($data['learning_objectives'] ?? null) ?? [])
                    ->map(fn (string $description): array => ['description' => $description])
                    ->all();
            }
            $learningOutcomes->sync($course, $outcomes);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'create' => 'The course could not be saved: '.$e->getMessage(),
            ]);
        }

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    // ── Modules / Lessons / Quizzes ───────────────────────────────────────

    public function storeModule(Request $request, int $id, CourseModuleService $courseModuleService)
    {
        $course = $this->ownedCourse($id);
        $data = $request->validate([
            'module_title' => ['nullable', 'string', 'max:255'],
            'module_description' => ['nullable', 'string', 'max:10000'],
        ]);
        $data['module_description'] = $this->sanitizeRichText($data['module_description'] ?? '');
        $courseModuleService->create($course, $data);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    public function updateModule(Request $request, int $id, int $moduleIndex, CourseModuleService $courseModuleService)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        try {
            $data = $request->validate([
                'module_title' => ['nullable', 'string', 'max:255'],
                'module_description' => ['nullable', 'string', 'max:10000'],
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('open_module_edit', $module->id);
        }

        $data['module_description'] = $this->sanitizeRichText($data['module_description'] ?? '');
        $courseModuleService->update($module, $data);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', 'Module updated.');
    }

    public function deleteModule(int $id, string $key, CourseModuleService $courseModuleService)
    {
        $course = $this->ownedCourse($id);
        $courseModuleService->deleteByKey($course, $key);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    public function storeLesson(Request $request, int $id, int $moduleIndex, CourseLessonService $courseLessonService)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        $data = $request->validate([
            'lesson_title' => ['nullable', 'string', 'max:255'],
            'lesson_content' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $data['lesson_content'] = $this->sanitizeRichText($data['lesson_content'] ?? '');
            $courseLessonService->create($request, $data, $course, $module);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()
                ->withErrors(['lesson_content' => 'The lesson could not be saved: '.$e->getMessage()])
                ->with('open_lesson_form', $module->id);
        }
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    public function updateLesson(Request $request, int $id, int $moduleIndex, int $lessonId, CourseLessonService $courseLessonService)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);
        $lesson = CourseLesson::where('course_id', $course->id)
            ->where('module_id', $module->id)
            ->findOrFail($lessonId);

        $data = $request->validate([
            'lesson_title' => ['nullable', 'string', 'max:255'],
            'lesson_content' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $data['lesson_content'] = $this->sanitizeRichText($data['lesson_content'] ?? '');
            $courseLessonService->update($request, $data, $lesson, $course);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()
                ->withErrors(['lesson_content' => 'The lesson could not be saved: '.$e->getMessage()])
                ->with('open_lesson_edit', $lesson->id);
        }
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', 'Lesson updated.');
    }

    public function deleteLesson(int $id, int $moduleIndex, string $key, CourseLessonService $courseLessonService)
    {
        $course = $this->ownedCourse($id);
        $courseLessonService->deleteByKey($course, $key);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    /**
     * Quiz builder screen (Faculty_My_Courses in 'quiz' mode), prefilled
     * with the module's existing quiz when there is one.
     */
    public function quizCreate(int $id, int $moduleIndex)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        $quiz = null;
        $quizModel = Quiz::with('questions')->where('module_id', $module->id)->first();
        if ($quizModel) {
            $quiz = (object) [
                'title' => $quizModel->title,
                'items' => $quizModel->questions->count(),
                'passing_score' => $quizModel->passing_score,
                'attempts' => $quizModel->attempts,
                'time_limit' => $quizModel->time_limit,
                'instructions' => $quizModel->instructions ?? '',
                'questions' => $quizModel->questions->map(function (QuizQuestion $q) {
                    $options = $q->options ?? [];
                    $correct = $q->correct_answer !== null ? array_search($q->correct_answer, $options, true) : null;

                    return [
                        'text' => $q->question,
                        'type' => $q->type ?? 'Multiple Choice',
                        'points' => (int) $q->points,
                        'choices' => $options,
                        'correct' => $correct === false ? null : $correct,
                        'answer' => ($q->type === 'Identification') ? $q->correct_answer : null,
                    ];
                })->values()->all(),
            ];
        }

        return view('faculty.courses.index', [
            'mode' => 'quiz',
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => (object) ['id' => $course->id, 'title' => $course->title],
            'moduleIdx' => $module->id,
            'moduleTitle' => $module->title,
            'quiz' => $quiz,
        ]);
    }

    public function storeQuiz(Request $request, int $id, int $moduleIndex, QuizManagementService $quizManagementService)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);
        $questionsChanged = $quizManagementService->save($request, $course, $module);
        $this->returnCourseToDraft($course);

        if ($request->boolean('inline')) {
            return redirect()->route('faculty.quiz.create', [$course->id, $module->id, 'inline' => 1])
                ->with('success', $questionsChanged
                    ? 'Quiz saved. The questions changed, so students may attempt it again.'
                    : 'Quiz saved. The new settings apply to students immediately.');
        }

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', $questionsChanged
                ? 'Quiz saved. The questions changed, so students may attempt it again.'
                : 'Quiz saved. The new settings apply to students immediately.');
    }

    public function lessonQuizCreate(int $id, int $lessonId)
    {
        $course = $this->ownedCourse($id);
        $lesson = CourseLesson::where('course_id', $course->id)->findOrFail($lessonId);
        $quizModel = Quiz::with('questions')->where('lesson_id', $lesson->id)->first();
        $quiz = $quizModel ? (object) [
            'title' => $quizModel->title,
            'items' => $quizModel->questions->count(),
            'passing_score' => $quizModel->passing_score,
            'attempts' => $quizModel->attempts,
            'time_limit' => $quizModel->time_limit,
            'instructions' => $quizModel->instructions ?? '',
            'questions' => $quizModel->questions->map(function (QuizQuestion $question): array {
                $options = $question->options ?? [];
                $correct = $question->correct_answer !== null ? array_search($question->correct_answer, $options, true) : null;

                return [
                    'text' => $question->question,
                    'type' => $question->type ?? 'Multiple Choice',
                    'points' => (int) $question->points,
                    'choices' => $options,
                    'correct' => $correct === false ? null : $correct,
                    'answer' => ($question->type === 'Identification') ? $question->correct_answer : null,
                ];
            })->values()->all(),
        ] : null;

        return view('faculty.courses.index', [
            'mode' => 'quiz',
            'user' => UserPresenter::faculty(Auth::user()),
            'course' => (object) ['id' => $course->id, 'title' => $course->title],
            'moduleIdx' => null,
            'lessonId' => $lesson->id,
            'moduleTitle' => $lesson->title.' (Lesson quiz)',
            'quiz' => $quiz,
        ]);
    }

    public function storeLessonQuiz(Request $request, int $id, int $lessonId, QuizManagementService $quizManagementService)
    {
        $course = $this->ownedCourse($id);
        $lesson = CourseLesson::where('course_id', $course->id)->findOrFail($lessonId);
        $questionsChanged = $quizManagementService->saveForLesson($request, $course, $lesson);
        $this->returnCourseToDraft($course);

        if ($request->boolean('inline')) {
            return redirect()->route('faculty.lesson-quiz.create', [$course->id, $lesson->id, 'inline' => 1])
                ->with('success', $questionsChanged
                    ? 'Lesson quiz saved. Students will need a new attempt for the revised questions.'
                    : 'Lesson quiz saved.');
        }

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', $questionsChanged
                ? 'Lesson quiz saved. Students will need a new attempt for the revised questions.'
                : 'Lesson quiz saved.');
    }

    public function storeLessonActivity(Request $request, int $id, int $lessonId)
    {
        $course = $this->ownedCourse($id);
        $lesson = CourseLesson::where('course_id', $course->id)->findOrFail($lessonId);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'activity_type' => ['required', 'string', 'in:practice,reflection,assignment'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'max_points' => ['nullable', 'required_if:activity_type,assignment', 'integer', 'min:1', 'max:10000'],
            'passing_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_activity_form', $lesson->id);
        }
        $data = $validator->validated();

        $lesson->activities()->create([
            ...$data,
            'is_required' => $request->boolean('is_required'),
            'max_points' => $data['max_points'] ?? null,
            'passing_percent' => $data['passing_percent'] ?? null,
            'sort_order' => max(
                (int) $lesson->activities()->max('sort_order'),
                (int) $lesson->quizzes()->max('sort_order'),
            ) + 1,
            'is_active' => true,
        ]);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)->with('success', 'Lesson activity added.');
    }

    public function updateLessonActivity(Request $request, int $id, int $activityId)
    {
        $course = $this->ownedCourse($id);
        $activity = LessonActivity::query()
            ->whereHas('lesson', fn ($query) => $query->where('course_id', $course->id))
            ->where('is_active', true)
            ->findOrFail($activityId);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'activity_type' => ['required', 'string', 'in:practice,reflection,assignment'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'max_points' => ['nullable', 'required_if:activity_type,assignment', 'integer', 'min:1', 'max:10000'],
            'passing_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_activity_edit', $activity->id);
        }
        $data = $validator->validated();
        $activity->update([
            ...$data,
            'is_required' => $request->boolean('is_required'),
            'max_points' => $data['max_points'] ?? null,
            'passing_percent' => $data['passing_percent'] ?? null,
        ]);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)->with('success', 'Lesson activity updated.');
    }

    public function destroyLessonActivity(int $id, int $activityId)
    {
        $course = $this->ownedCourse($id);
        $activity = LessonActivity::query()
            ->whereHas('lesson', fn ($query) => $query->where('course_id', $course->id))
            ->where('is_active', true)
            ->findOrFail($activityId);
        $activity->update(['is_active' => false]);
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', 'Activity removed from the lesson. Existing submissions were retained.');
    }

    public function reorderModules(Request $request, int $id)
    {
        $course = $this->ownedCourse($id);
        $items = $request->validate(['items' => ['required', 'array'], 'items.*' => ['required', 'integer', 'distinct']])['items'];
        $moduleIds = $course->modules()->pluck('id')->map(fn ($moduleId) => (int) $moduleId)->sort()->values();
        abort_unless(collect($items)->map(fn ($item) => (int) $item)->sort()->values()->all() === $moduleIds->all(), 422);

        DB::transaction(function () use ($course, $items): void {
            foreach ($items as $position => $moduleId) {
                CourseModule::where('course_id', $course->id)->whereKey($moduleId)->update(['order' => $position + 1]);
            }
        });
        $this->returnCourseToDraft($course);

        return response()->json(['saved' => true]);
    }

    public function reorderLessons(Request $request, int $id, int $moduleId)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleId);
        $items = $request->validate(['items' => ['required', 'array'], 'items.*' => ['required', 'integer', 'distinct']])['items'];
        $lessonIds = $module->lessons()->pluck('id')->map(fn ($lessonId) => (int) $lessonId)->sort()->values();
        abort_unless(collect($items)->map(fn ($item) => (int) $item)->sort()->values()->all() === $lessonIds->all(), 422);

        DB::transaction(function () use ($module, $items): void {
            foreach ($items as $position => $lessonId) {
                CourseLesson::where('module_id', $module->id)->whereKey($lessonId)->update(['order' => $position + 1]);
            }
        });
        $this->returnCourseToDraft($course);

        return response()->json(['saved' => true]);
    }

    public function reorderLessonAssessments(Request $request, int $id, int $lessonId)
    {
        $course = $this->ownedCourse($id);
        $lesson = CourseLesson::where('course_id', $course->id)->findOrFail($lessonId);
        $items = $request->validate([
            'items' => ['required', 'array'],
            'items.*.type' => ['required', 'in:activity,quiz'],
            'items.*.id' => ['required', 'integer'],
        ])['items'];
        $submittedKeys = collect($items)->map(fn ($item) => $item['type'].':'.(int) $item['id']);
        abort_unless($submittedKeys->unique()->count() === $submittedKeys->count(), 422);
        $expectedKeys = LessonActivity::where('lesson_id', $lesson->id)->where('is_active', true)->pluck('id')
            ->map(fn ($activityId) => 'activity:'.(int) $activityId)
            ->concat(Quiz::where('lesson_id', $lesson->id)->where('is_active', true)->pluck('id')->map(fn ($quizId) => 'quiz:'.(int) $quizId))
            ->sort()->values();
        abort_unless($submittedKeys->sort()->values()->all() === $expectedKeys->all(), 422);

        DB::transaction(function () use ($lesson, $items): void {
            foreach ($items as $position => $item) {
                $model = $item['type'] === 'activity'
                    ? LessonActivity::where('lesson_id', $lesson->id)->where('is_active', true)->findOrFail($item['id'])
                    : Quiz::where('lesson_id', $lesson->id)->where('is_active', true)->findOrFail($item['id']);
                $model->update(['sort_order' => $position + 1]);
            }
        });
        $this->returnCourseToDraft($course);

        return response()->json(['saved' => true]);
    }

    public function reviewLessonActivitySubmission(
        Request $request,
        int $id,
        int $activityId,
        int $submissionId,
        StudentProgressService $studentProgress
    ) {
        $course = $this->ownedCourse($id);
        $activity = LessonActivity::query()->whereHas('lesson', fn ($query) => $query->where('course_id', $course->id))->findOrFail($activityId);
        abort_unless($activity->activity_type === 'assignment', 404);
        $submission = LessonActivitySubmission::query()
            ->where('lesson_activity_id', $activity->id)
            ->where('status', 'submitted')
            ->findOrFail($submissionId);
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:'.(int) ($activity->max_points ?? 10000)],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);
        $percent = (int) round(((int) $data['score'] / max(1, (int) $activity->max_points)) * 100);
        $submission->update([
            'score' => (int) $data['score'],
            'feedback' => trim((string) ($data['feedback'] ?? '')) ?: null,
            'status' => $percent >= (int) ($activity->passing_percent ?? 70) ? 'passed' : 'needs_revision',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $enrollment = Enrollment::query()->where('user_id', $submission->user_id)->where('course_id', $course->id)->first();
        if ($enrollment) {
            if ($submission->status === 'passed') {
                $studentProgress->completeLessonIfRequirementsSatisfied($course, $activity->lesson, $enrollment);
            }

            $studentProgress->syncEnrollmentProgress($course, $enrollment);
            app(MicrocredentialCompletionService::class)->evaluate($enrollment);
        }

        return redirect()->route('faculty.activities.reviews', [
            'id' => $course->id,
            'status' => 'pending',
            'activity_id' => $activity->id,
        ])->with('success', 'Submission reviewed and feedback saved.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    // ── Inbox ─────────────────────────────────────────────────────────────

    /** Faculty inbox — the faculty member's message threads with administrators. */
    public function inbox(Request $request)
    {
        $auth = Auth::user();
        $threads = Complaint::with('replies.author')
            ->where('user_id', $auth->id)
            ->get()
            ->sortByDesc(fn (Complaint $complaint) => $complaint->lastActivityAt())
            ->values();

        $selectedId = (int) $request->query('thread', $threads->first()->id ?? 0);
        $selected = $threads->firstWhere('id', $selectedId);

        if ($selected && $selected->unreadForSender()) {
            $selected->student_read_at = now();
            $selected->save();
        }

        return view('faculty.inbox', [
            'user' => UserPresenter::faculty($auth),
            'threads' => $threads,
            'selected' => $selected,
            'unreadCount' => $threads->filter->unreadForSender()->count(),
            'isFacultyInbox' => true,
        ]);
    }

    /**
     * #8 — Delete a module's quiz (and its questions / attempts, which
     * cascade). Faculty only, and only on their own course.
     */
    public function destroyQuiz(int $id, int $moduleIndex, QuizManagementService $quizManagementService)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);
        $title = $quizManagementService->delete($module);
        if ($title === null) {
            return back()->withErrors(['quiz' => 'That module has no quiz to delete.']);
        }
        $this->returnCourseToDraft($course);

        return redirect()->route('faculty.courses.manage', $course->id)
            ->with('success', '"'.$title.'" was deleted.');
    }

    /**
     * #7 — Courses that may be picked as prerequisites: every course already
     * live on the site, excluding the one being edited (a course cannot
     * require itself).
     */
    private function prerequisiteOptions(?int $excludeId = null)
    {
        $graph = $this->prerequisiteGraph();

        return Course::query()
            ->where('is_approved', true)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('title')
            ->get(['id', 'title', 'category'])
            ->filter(function (Course $candidate) use ($excludeId, $graph) {
                if (! $excludeId) {
                    return true;
                }

                $visited = [];

                // A course that already depends on the edited course cannot
                // be selected as its prerequisite without making a cycle.
                return ! $this->courseDependsOn((int) $candidate->id, $excludeId, $graph, $visited);
            })
            ->map(fn (Course $c) => (object) [
                'id' => $c->id,
                'title' => $c->title,
                'category' => $c->category,
            ])
            ->values();
    }

    /** @return array<int, list<int>> */
    private function prerequisiteGraph(): array
    {
        return Course::query()
            ->get(['id', 'prerequisite_ids'])
            ->mapWithKeys(fn (Course $course) => [
                (int) $course->id => array_map('intval', (array) ($course->prerequisite_ids ?? [])),
            ])
            ->all();
    }

    private function courseDependsOn(int $courseId, int $targetId, array $graph, array &$visited): bool
    {
        if ($courseId === $targetId) {
            return true;
        }

        if (isset($visited[$courseId])) {
            return false;
        }
        $visited[$courseId] = true;

        foreach ($graph[$courseId] ?? [] as $prerequisiteId) {
            if ($this->courseDependsOn((int) $prerequisiteId, $targetId, $graph, $visited)) {
                return true;
            }
        }

        return false;
    }

    private function wouldCreatePrerequisiteCycle(int $courseId, array $prerequisiteIds): bool
    {
        $graph = $this->prerequisiteGraph();
        $graph[$courseId] = array_map('intval', $prerequisiteIds);

        foreach ($prerequisiteIds as $prerequisiteId) {
            $visited = [];
            if ($this->courseDependsOn((int) $prerequisiteId, $courseId, $graph, $visited)) {
                return true;
            }
        }

        return false;
    }

    /** Clean, de-duplicated prerequisite ids from the form. */
    private function prerequisiteIdsFrom(Request $request, ?int $excludeId): array
    {
        $ids = collect((array) $request->input('prerequisite_ids', []))
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0 && $v !== $excludeId)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    /** #10 — Badges an author can attach as the course award. */
    private function badgeOptions()
    {
        return Badge::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($b) => (object) ['id' => $b->id, 'name' => $b->name])
            ->values();
    }

    /**
     * Fetch a course owned by the current faculty member (or 404).
     */
    private function ownedCourse(int $id): Course
    {
        return Course::where('created_by', Auth::id())->findOrFail($id);
    }

    /** Keep submitted or published courses private when their content changes. */
    private function returnCourseToDraft(Course $course): void
    {
        if ($course->approval_status === 'draft' && ! $course->is_approved && ! $course->is_published) {
            return;
        }

        $course->update([
            'approval_status' => 'draft',
            'is_approved' => false,
            'is_published' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Split a textarea (one item per line, commas also ok) into a clean
     * array — used for skills / learning objectives.
     *
     * @return list<string>|null
     */
    /**
     * Move an uploaded thumbnail into public/uploads/thumbnails.
     * Returns the stored relative path, or null when no file was sent.
     */
    private function storeThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail') || ! $request->file('thumbnail')->isValid()) {
            return null;
        }

        $dir = public_path('uploads/thumbnails');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = uniqid('thumb_').'.'.strtolower($request->file('thumbnail')->getClientOriginalExtension());
        $request->file('thumbnail')->move($dir, $name);

        return 'uploads/thumbnails/'.$name;
    }

    private function linesToArray(?string $text): ?array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $text))));

        return $items === [] ? null : $items;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $i = 2;
        while (Course::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    // ══════════════════════════════════════════════════════════════════
    //  Certificate builder
    // ══════════════════════════════════════════════════════════════════

    /**
     * Certificate designer for one course.
     *
     * The signature lives on the USER, so it is written once and reused for
     * every course that faculty member owns. The course keeps a snapshot of
     * it (certificate_signature) taken when the certificate was configured,
     * so changing your signature later does not silently rewrite awards
     * that have already been issued.
     */
    public function certificate(int $id)
    {
        $me = Auth::user();
        $course = Course::where('created_by', $me->id)->findOrFail($id);

        // Preview the certificate against a REAL student who has completed
        // this course, so faculty see what will actually be issued. Passing
        // the signed-in faculty member here put their own name on the
        // "This is to certify that" line, which read as if they had taken
        // their own course.
        $sample = Enrollment::with('user')
            ->where('course_id', $course->id)
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->latest('updated_at')
            ->first()?->user;

        $completedCount = Enrollment::where('course_id', $course->id)
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->count();

        return view('faculty.certificates.create', [
            'user' => UserPresenter::faculty($me),
            'course' => $course,
            'fonts' => CertificateBuilder::SIGNATURE_FONTS,
            'preview' => CertificateBuilder::data($course, $sample),
            'sampleStudent' => $sample,
            'completedCount' => $completedCount,
            'hasSignature' => (bool) ($me->signature_image || $me->signature_text),
        ]);
    }

    /** Save the signature (drawn or typed) onto the faculty account. */
    public function storeSignature(Request $request, int $id)
    {
        $me = Auth::user();
        $course = Course::where('created_by', $me->id)->findOrFail($id);

        $data = $request->validate([
            'signature_mode' => ['required', 'in:draw,type'],
            'signature_image' => ['nullable', 'string'],
            'signature_text' => ['nullable', 'string', 'max:60'],
            'signature_font' => ['nullable', 'string', 'max:60'],
        ]);

        if ($data['signature_mode'] === 'draw') {
            if (empty($data['signature_image']) || ! str_starts_with($data['signature_image'], 'data:image/')) {
                return back()->withErrors(['signature' => 'Please draw your signature before saving.']);
            }

            $me->signature_image = $data['signature_image'];
            $me->signature_text = null;
        } else {
            if (empty($data['signature_text'])) {
                return back()->withErrors(['signature' => 'Please type your signature before saving.']);
            }

            $me->signature_text = $data['signature_text'];
            $me->signature_font = $data['signature_font'] ?? 'Great Vibes';
            $me->signature_image = null;
        }

        $me->save();

        // Snapshot onto the course so issued certificates stay stable.
        $course->certificate_signature = $me->signature_image;
        $course->certificate_signature_name = $me->signature_text ?: $me->name;
        $course->save();

        return back()->with('success', 'Signature saved. You can now generate certificates for this course.');
    }

    /** Store the certificate settings: auto-generated or uploaded. */
    public function storeCertificate(Request $request, int $id)
    {
        $me = Auth::user();
        $course = Course::where('created_by', $me->id)->findOrFail($id);

        $data = $request->validate([
            'certificate_mode' => ['required', 'in:auto,upload'],
            'certificate_title' => ['nullable', 'string', 'max:160'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:8192'],
        ]);

        // Auto-generation signs the certificate, so it cannot run before a
        // signature exists. Checked here as well as in the UI: hiding a
        // button does not stop a form being posted.
        if ($data['certificate_mode'] === 'auto' && ! $me->signature_image && ! $me->signature_text) {
            return back()->withErrors([
                'certificate' => 'Save your signature first — an auto-generated certificate has to be signed.',
            ]);
        }

        if ($data['certificate_mode'] === 'upload') {
            if (! $request->hasFile('certificate_file') && ! $course->certificate_file) {
                return back()->withErrors(['certificate' => 'Choose a certificate file to upload.']);
            }

            if ($request->hasFile('certificate_file')) {
                $path = $request->file('certificate_file')->store('certificates', 'public');
                $course->certificate_file = 'storage/'.$path;
            }
        }

        $course->certificate_mode = $data['certificate_mode'];
        // nullable + absent from the request = key missing from $data —
        // read with a fallback so saving certificate settings can't 500.
        $course->certificate_title = ($data['certificate_title'] ?? null) ?: 'Certificate of Completion';
        $course->certificate_enabled = true;

        if ($data['certificate_mode'] === 'auto') {
            $course->certificate_signature = $me->signature_image;
            $course->certificate_signature_name = $me->signature_text ?: $me->name;
        }

        $course->save();

        return back()->with('success', 'Certificate settings saved for this course.');
    }

    // ══════════════════════════════════════════════════════════════════
    //  Badge builder
    // ══════════════════════════════════════════════════════════════════

    public function badge(int $id)
    {
        $me = Auth::user();
        $course = Course::where('created_by', $me->id)->findOrFail($id);

        return view('faculty.badges.create', [
            'user' => UserPresenter::faculty($me),
            'course' => $course,
            'badge' => $course->badge,
        ]);
    }

    public function storeBadge(Request $request, int $id)
    {
        $me = Auth::user();
        $course = Course::where('created_by', $me->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:400'],
            'icon_base64' => ['nullable', 'string'],
        ]);

        $badge = $course->badge ?: new Badge;

        $badge->name = $data['name'];
        $badge->description = $data['description'] ?? null;
        $badge->badge_level = null;
        $badge->is_active = true;

        if (! empty($data['icon_base64']) && str_starts_with($data['icon_base64'], 'data:image/')) {
            $badge->icon_url = $data['icon_base64'];
        }

        $badge->save();

        $course->badge_id = $badge->id;
        $course->save();

        return back()->with('success', 'Badge saved and linked to this course.');
    }
}
