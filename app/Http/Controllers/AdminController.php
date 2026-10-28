<?php

namespace App\Http\Controllers;

use App\Actions\Announcements\CreateAnnouncement;
use App\Actions\Announcements\DeleteAnnouncement;
use App\Actions\Announcements\UpdateAnnouncement;
use App\Mail\ComplaintReplyMail;
use App\Models\AcademicCreditRecognition;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\FacultyCode;
use App\Models\LessonActivitySubmission;
use App\Models\Pathway;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStackingProgress;
use App\Services\AdminPageAccessService;
use App\Services\AdminUserManagementService;
use App\Services\CourseModerationService;
use App\Services\CourseReadinessService;
use App\Services\CreditEvidenceReportService;
use App\Services\MicrocredentialCompletionService;
use App\Services\UserCodeService;
use App\Services\UserNotificationService;
use App\Support\CertificateBuilder;
use App\Support\CompletionStatusPresenter;
use App\Support\RichTextSanitizer;
use App\Support\UserPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AdminController — the whole Admin_* surface (dashboard, profile, user
 * management, courses & badges, analytics report) fed from the database.
 */
class AdminController extends Controller
{
    /** Display administrator actions for the selected date range. */
    public function auditLogs(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'actor_role' => ['nullable', 'in:all,admin,faculty,student'],
            'action' => ['nullable', 'string', 'max:120'],
        ]);
        $filters = [
            'start_date' => $validated['start_date'] ?? now()->subDays(29)->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->toDateString(),
            'actor_role' => $validated['actor_role'] ?? 'all',
            'action' => $validated['action'] ?? 'all',
        ];
        $query = $this->auditLogQuery($filters);
        $totalLogs = (clone $query)->count();
        $actions = AuditLog::query()
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when($filters['actor_role'] !== 'all', function (Builder $builder) use ($filters): void {
                $roles = $filters['actor_role'] === 'admin' ? ['admin', 'Administrator'] : [$filters['actor_role']];
                $builder->whereIn('actor_role', $roles);
            })
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('admin.audit-logs', [
            'user' => UserPresenter::admin(Auth::user()),
            'logs' => $query->paginate(25)->withQueryString(),
            'totalLogs' => $totalLogs,
            'actions' => $actions,
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
            'actorRole' => $filters['actor_role'],
            'selectedAction' => $filters['action'],
        ]);
    }

    /** Download the filtered system activity range as a CSV report. */
    public function downloadAuditLogs(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'actor_role' => ['nullable', 'in:all,admin,faculty,student'],
            'action' => ['nullable', 'string', 'max:120'],
        ]);
        $filters = [
            'start_date' => $validated['start_date'] ?? now()->subDays(29)->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->toDateString(),
            'actor_role' => $validated['actor_role'] ?? 'all',
            'action' => $validated['action'] ?? 'all',
        ];
        $logs = $this->auditLogQuery($filters)->reorder()->orderBy('created_at')->orderBy('id');
        $filename = 'admin-audit-log-'.$filters['start_date'].'-to-'.$filters['end_date'].'.csv';

        return response()->streamDownload(function () use ($logs): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Timestamp', 'User', 'Role', 'Action', 'Target', 'Old and New Values', 'Details', 'Method', 'Route', 'IP Address', 'User Agent']);

            foreach ($logs->cursor() as $log) {
                $details = (string) $log->description;
                if (Str::startsWith($details, $log->event.' via '.$log->method.' ')) {
                    $details = '';
                }

                fputcsv($output, [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $this->safeCsvValue($log->actor_name),
                    $this->safeCsvValue($log->actor_role),
                    $this->safeCsvValue($log->event),
                    $this->safeCsvValue($log->target_label),
                    $this->safeCsvValue($log->changes === null ? null : json_encode($log->changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                    $this->safeCsvValue($details),
                    $this->safeCsvValue($log->method),
                    $this->safeCsvValue($log->route),
                    $this->safeCsvValue($log->ip_address),
                    $this->safeCsvValue($log->user_agent),
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @param array{start_date: string, end_date: string, actor_role?: string, action?: string} $filters */
    private function auditLogQuery(array $filters): Builder
    {
        return AuditLog::query()
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(($filters['actor_role'] ?? 'all') !== 'all', function (Builder $builder) use ($filters): void {
                $roles = $filters['actor_role'] === 'admin' ? ['admin', 'Administrator'] : [$filters['actor_role']];
                $builder->whereIn('actor_role', $roles);
            })
            ->when(($filters['action'] ?? 'all') !== 'all', fn (Builder $builder) => $builder->where('event', $filters['action']))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function safeCsvValue(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[\s]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }

    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $currentUser = Auth::user();
        $dashboardAccess = [
            'user-management' => $currentUser->canAccessAdminPage('user-management'),
            'courses' => $currentUser->canAccessAdminPage('courses'),
            'completion-verification' => $currentUser->canAccessAdminPage('completion-verification'),
            'credentials' => $currentUser->canAccessAdminPage('credentials'),
            'reports' => $currentUser->canAccessAdminPage('reports'),
            'announcements' => $currentUser->canAccessAdminPage('announcements'),
            'audit-logs' => $currentUser->canAccessAdminPage('audit-logs'),
        ];
        $weekStart = now()->startOfWeek();
        $previousWeekStart = $weekStart->copy()->subWeek();
        $monthStart = now()->startOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonth();
        $publishedCourses = fn () => Course::where('is_published', true)
            ->where(fn (Builder $query) => $query->where('approval_status', 'approved')->orWhere('is_approved', true));
        $learnersThisWeek = User::where('role_id', User::ROLE_STUDENT)
            ->where('created_at', '>=', $weekStart)->count();
        $learnersLastWeek = User::where('role_id', User::ROLE_STUDENT)
            ->where('created_at', '>=', $previousWeekStart)->where('created_at', '<', $weekStart)->count();
        $coursesPublishedThisWeek = $publishedCourses()->where('approved_at', '>=', $weekStart)->count();
        $coursesPublishedLastWeek = $publishedCourses()->where('approved_at', '>=', $previousWeekStart)
            ->where('approved_at', '<', $weekStart)->count();
        $pendingCourseReviewsThisWeek = Course::where('approval_status', 'pending')
            ->where('created_at', '>=', $weekStart)->count();
        $pendingCourseReviewsLastWeek = Course::where('approval_status', 'pending')
            ->where('created_at', '>=', $previousWeekStart)->where('created_at', '<', $weekStart)->count();
        $pendingCompletionReviewsThisWeek = Enrollment::where(
            'completion_status',
            MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION
        )->where('academic_unit_confirmation_status', 'pending')
            ->whereHas('course.reviews', fn (Builder $query) => $query->whereColumn('course_reviews.user_id', 'enrollments.user_id'))
            ->where('updated_at', '>=', $weekStart)->count();
        $pendingCompletionReviewsLastWeek = Enrollment::where(
            'completion_status',
            MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION
        )->where('academic_unit_confirmation_status', 'pending')->where('updated_at', '>=', $previousWeekStart)
            ->whereHas('course.reviews', fn (Builder $query) => $query->whereColumn('course_reviews.user_id', 'enrollments.user_id'))
            ->where('updated_at', '<', $weekStart)->count();
        $credentialsThisMonth = DB::table('user_badges')->where('status', 'active')
            ->where('earned_at', '>=', $monthStart)->count()
            + Certificate::where('status', 'active')->where('issued_at', '>=', $monthStart)->count();
        $credentialsLastMonth = DB::table('user_badges')->where('status', 'active')
            ->where('earned_at', '>=', $previousMonthStart)->where('earned_at', '<', $monthStart)->count()
            + Certificate::where('status', 'active')->where('issued_at', '>=', $previousMonthStart)
                ->where('issued_at', '<', $monthStart)->count();
        $pendingCourseReviews = Course::where('approval_status', 'pending')->count();
        $pendingCompletionVerifications = Enrollment::where(
            'completion_status',
            MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION
        )
            ->where('academic_unit_confirmation_status', 'pending')
            ->whereHas('course.reviews', fn (Builder $query) => $query->whereColumn('course_reviews.user_id', 'enrollments.user_id'))
            ->count();

        $stats = [
            'total_students' => User::where('role_id', User::ROLE_STUDENT)->count(),
            'learners_weekly_change' => $learnersThisWeek - $learnersLastWeek,
            'published_courses' => Course::where('is_published', true)
                ->where(fn (Builder $query) => $query->where('approval_status', 'approved')->orWhere('is_approved', true))
                ->count(),
            'courses_weekly_change' => $coursesPublishedThisWeek - $coursesPublishedLastWeek,
            'pending_reviews' => $pendingCourseReviews + $pendingCompletionVerifications,
            'pending_course_reviews' => $pendingCourseReviews,
            'pending_completion_verifications' => $pendingCompletionVerifications,
            'pending_reviews_weekly_change' => ($pendingCourseReviewsThisWeek - $pendingCourseReviewsLastWeek)
                + ($pendingCompletionReviewsThisWeek - $pendingCompletionReviewsLastWeek),
            'credentials_earned' => DB::table('user_badges')->where('status', 'active')->count()
                + Certificate::where('status', 'active')->count(),
            'badges_issued' => DB::table('user_badges')->count(),
            'course_score_avg' => round((float) Course::avg('passing_score'), 1),
            'students_enrolled' => DB::table('enrollments')->count(),
            'credentials_monthly_change' => $credentialsThisMonth - $credentialsLastMonth,
        ];

        $quickModules = [
            [
                'title' => 'Courses & Badges',
                'description' => 'Review course submissions, manage publishing, and maintain badges.',
                'info' => $pendingCourseReviews.' courses awaiting review',
                'route' => 'admin.courses',
                'button' => 'Go to Courses & Badges',
                'page' => 'courses',
            ],
            [
                'title' => 'Completion Verification',
                'description' => 'Verify completed learner work and confirm credential eligibility.',
                'info' => $pendingCompletionVerifications.' learners awaiting verification',
                'route' => 'admin.enrollments',
                'button' => 'Go to Completion Verification',
                'page' => 'completion-verification',
            ],
            [
                'title' => 'User Management',
                'description' => 'Manage student, faculty, and administrator accounts and access.',
                'info' => User::count().' user accounts',
                'route' => 'admin.usermanagement',
                'button' => 'Go to User Management',
                'page' => 'user-management',
            ],
            [
                'title' => 'Certificates',
                'description' => 'Review issued certificates and inspect revocation records.',
                'info' => Certificate::where('status', 'active')->count().' active certificates',
                'route' => 'admin.certificates',
                'button' => 'Go to Certificates',
                'page' => 'credentials',
            ],
            [
                'title' => 'Announcements',
                'description' => 'Create and manage updates shared with the learning community.',
                'info' => Announcement::count().' announcements',
                'route' => 'admin.announcements',
                'button' => 'Go to Announcements',
                'page' => 'announcements',
            ],
            [
                'title' => 'Audit Logs',
                'description' => 'Review platform activity and download reports for a date range.',
                'info' => AuditLog::whereDate('created_at', today())->count().' events recorded today',
                'route' => 'admin.audit-logs',
                'button' => 'Go to Audit Logs',
                'page' => 'audit-logs',
            ],
        ];
        $quickModules = array_filter(
            $quickModules,
            fn (array $module): bool => $dashboardAccess[$module['page']]
        );
        $stats['pending_course_reviews'] = $dashboardAccess['courses'] ? $pendingCourseReviews : 0;
        $stats['pending_completion_verifications'] = $dashboardAccess['completion-verification']
            ? $pendingCompletionVerifications
            : 0;
        $stats['pending_reviews'] = $stats['pending_course_reviews'] + $stats['pending_completion_verifications'];
        $stats['pending_reviews_weekly_change'] = ($dashboardAccess['courses'] ? $pendingCourseReviewsThisWeek - $pendingCourseReviewsLastWeek : 0)
            + ($dashboardAccess['completion-verification'] ? $pendingCompletionReviewsThisWeek - $pendingCompletionReviewsLastWeek : 0);

        $coursePerformance = $this->courseCharts();
        $courseCount = $coursePerformance->count();
        $chartMaximumEnrollments = max(1, (int) $coursePerformance->max('enrollments'));
        $chartPoints = $coursePerformance->values()->map(function ($course, $index) use ($courseCount, $chartMaximumEnrollments) {
            $x = $courseCount > 1 ? 104 + ($index * 432 / ($courseCount - 1)) : 320;
            $enrollmentY = 202 - ($course->enrollments / $chartMaximumEnrollments * 160);
            $completionY = 202 - (max(0, min(100, $course->completion_rate)) / 100 * 160);

            return (object) [
                'course' => $course,
                'x' => round($x, 2),
                'enrollment_x' => round($x - 28, 2),
                'completion_x' => round($x + 4, 2),
                'enrollment_y' => round($enrollmentY, 2),
                'completion_y' => round($completionY, 2),
            ];
        });
        $chartTicks = collect(range(0, 4))->map(fn ($tick) => (object) [
            'y' => 202 - ($tick * 40),
            'enrollments' => (int) round($chartMaximumEnrollments * $tick / 4),
            'completion_rate' => $tick * 25,
        ]);
        $activeCourseCount = Course::query()->where('approval_status', '!=', 'draft')->count();

        // All badges are listed — even ones nobody has earned yet — so the
        // panel shows "0 Earned" counters that climb in real time.
        $recentBadges = DB::table('badges')
            ->leftJoin('user_badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.name', DB::raw('COUNT(user_badges.id) as earned_count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderByDesc('earned_count')
            ->orderBy('badges.name')
            ->limit(4)
            ->get()
            ->map(fn ($row) => (object) ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values();

        return view('admin.dashboard', [
            'stats' => $stats,
            'activeCourseCount' => $activeCourseCount,
            'recentBadges' => $recentBadges,
            'coursePerformance' => $coursePerformance,
            'courseCount' => $courseCount,
            'chartMaximumEnrollments' => $chartMaximumEnrollments,
            'chartPoints' => $chartPoints,
            'chartTicks' => $chartTicks,
            'quickModules' => $quickModules,
            'dashboardAccess' => $dashboardAccess,
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        return view('admin.profile', [
            'user' => UserPresenter::admin(Auth::user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:users,username,'.$auth->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],
            'about' => ['nullable', 'string', 'max:600'],
            'bio' => ['nullable', 'string', 'max:600'],

            // Avatar arrives as a data URL already resized in the browser, so
            // it bypasses upload_max_filesize / post_max_size entirely.
            'avatar_base64' => ['nullable', 'string'],
            'remove_avatar' => ['nullable', 'boolean'],

            // Optional. Leave blank to keep the current password.
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // ── Password change ──────────────────────────────────────────────
        // Done first: if the current password is wrong nothing at all is
        // saved, so the admin never sees a half-applied update.
        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'] ?? '', $auth->password)) {
                return back()
                    ->withErrors(['current_password' => 'Your current password is incorrect.'])
                    ->withInput();
            }

            $auth->password = Hash::make($data['password']);
        }

        // Name parts are edited individually. Splitting the combined display
        // name on whitespace silently folded middle_name and suffix into
        // last_name, and every save degraded it further.
        $auth->first_name = $data['first_name'];
        $auth->middle_name = $data['middle_name'] ?? null;
        $auth->last_name = $data['last_name'];
        $auth->suffix = $data['suffix'] ?? null;
        $auth->username = $data['username'];
        $auth->phone = $data['phone'] ?? null;
        $auth->location = $data['location'] ?? null;
        $auth->about = $data['about'] ?? null;
        $auth->bio = $data['bio'] ?? null;

        // ── Profile photo ────────────────────────────────────────────────
        // users.avatar_url is LONGTEXT (see 2026_07_25_000001), so the data
        // URL is stored inline — no filesystem writes, nothing to clean up.
        if (! empty($data['remove_avatar'])) {
            $auth->avatar_url = null;
        } elseif (! empty($data['avatar_base64']) && str_starts_with($data['avatar_base64'], 'data:image/')) {
            $auth->avatar_url = $data['avatar_base64'];
        }

        $auth->save();

        $message = ! empty($data['password'])
            ? 'Admin profile updated. Your new password is now active.'
            : 'Admin profile updated successfully.';

        return redirect()->route('admin.profile')->with('success', $message);
    }

    // ── User Management ───────────────────────────────────────────────────

    public function userManagement(Request $request, AdminPageAccessService $adminPageAccess)
    {
        $query = trim((string) $request->input('q', ''));
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $roleTab = $request->input('role', 'students');

        $usersQuery = User::query()
            ->when($roleTab === 'faculty', fn ($q) => $q->where('role_id', User::ROLE_FACULTY))
            ->when($roleTab === 'students', fn ($q) => $q->where('role_id', User::ROLE_STUDENT))
            ->when($roleTab === 'administrators', fn ($q) => $q->whereIn('role_id', [
                User::ROLE_ADMIN,
                User::ROLE_SUPERADMIN,
                User::ROLE_SUBADMIN,
            ]))
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('user_code', 'like', "%{$query}%");
                });
            });

        if ($sort === 'user_code') {
            $usersQuery->orderBy('user_code', $direction);
        } elseif ($sort === 'role') {
            $usersQuery->orderBy('role_id', $direction);
        } elseif ($sort === 'name') {
            $usersQuery->orderBy('first_name', $direction)->orderBy('last_name', $direction);
        } else {
            $usersQuery->orderBy('created_at', $direction);
        }

        // Scalability: only the columns the table renders, and paginated.
        // Previously this loaded EVERY user row — including avatar_url, a
        // LONGTEXT column holding base64 photos — into memory on each visit.
        $users = $usersQuery
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role_id', 'user_code', 'is_active', 'admin_page_access', 'created_at'])
            ->paginate(20)
            ->withQueryString();

        $accountRoles = [
            User::ROLE_FACULTY => 'Faculty',
            User::ROLE_STUDENT => 'Learner',
        ];

        if (Auth::user()->isSuperAdmin()) {
            $accountRoles[User::ROLE_SUPERADMIN] = 'Superadmin';
            $accountRoles[User::ROLE_SUBADMIN] = 'Sub-admin';
        }

        return view('admin.users.index', [
            'users' => $users,
            'q' => $query,
            'sort' => $sort,
            'direction' => $direction,
            'roleTab' => $roleTab,
            'accountRoles' => $accountRoles,
            'adminPageAccessOptions' => $adminPageAccess->selectablePages(),
            'isSuperAdmin' => Auth::user()->isSuperAdmin(),
        ]);
    }

    /**
     * Admin › view a single user's full details (student or faculty).
     */
    public function showUser(int $id, AdminUserManagementService $userManagement)
    {
        $u = User::withCount(['enrollments'])->findOrFail($id);
        $detail = $userManagement->detail($u);

        return view('admin.users.show', ['u' => $detail]);
    }

    public function storeUser(
        Request $request,
        AdminUserManagementService $userManagement,
        UserCodeService $userCodes,
        AdminPageAccessService $adminPageAccess,
    ) {
        $isSuperAdmin = Auth::user()->isSuperAdmin();
        $allowedRoleIds = $isSuperAdmin
            ? [User::ROLE_FACULTY, User::ROLE_STUDENT, User::ROLE_SUPERADMIN, User::ROLE_SUBADMIN]
            : [User::ROLE_FACULTY, User::ROLE_STUDENT];
        $isCreatingSubAdmin = (int) $request->input('role_id') === User::ROLE_SUBADMIN;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'integer', Rule::in($allowedRoleIds)],
            'admin_page_access' => $isCreatingSubAdmin
                ? ['sometimes', 'array']
                : ['prohibited'],
            'admin_page_access.*' => [
                'string',
                Rule::in(array_keys($adminPageAccess->selectablePages())),
            ],
        ]);

        $userManagement->create($validated, $userCodes);

        $roleTab = match ((int) $validated['role_id']) {
            User::ROLE_ADMIN, User::ROLE_SUPERADMIN, User::ROLE_SUBADMIN => 'administrators',
            User::ROLE_FACULTY => 'faculty',
            default => 'students',
        };

        return redirect()->route('admin.usermanagement', ['role' => $roleTab])
            ->with('success', 'User created successfully.');
    }

    public function updateSubAdminAccess(Request $request, int $id, AdminPageAccessService $adminPageAccess)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);
        abort_unless($user->isSubAdmin(), 404);

        $validated = $request->validateWithBag('access', [
            'page_access' => ['sometimes', 'array'],
            'page_access.*' => [
                'string',
                Rule::in(array_keys($adminPageAccess->selectablePages())),
            ],
        ]);

        $user->update([
            'admin_page_access' => $adminPageAccess->subAdminPages($validated['page_access'] ?? []),
        ]);

        return redirect()->route('admin.usermanagement', ['role' => 'administrators'])
            ->with('success', 'Sub-admin access updated successfully.');
    }

    /**
     * Delete a student or faculty account from User Management.
     *
     * Admins are never deletable here, and an admin cannot delete their own
     * account — both would be one click away from locking everyone out.
     */
    public function destroyUser(int $id, AdminUserManagementService $userManagement)
    {
        $user = User::findOrFail($id);

        $result = $userManagement->delete($user, (int) Auth::id());
        if (! $result['deleted']) {
            return back()->withErrors(['user' => $result['message']]);
        }

        return redirect()->route('admin.usermanagement')
            ->with('success', $result['message'].' has been deleted.');
    }

    // ── Announcements ─────────────────────────────────────────────────────

    /**
     * Admin > Announcements. Announcements may be public or targeted to roles.
     */
    public function announcements()
    {
        $announcements = Announcement::with('author')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get()
            ->map(fn (Announcement $a) => (object) [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'audience' => empty($a->audience) || count($a->audience) >= 2 || in_array('public', $a->audience, true)
                    ? ['public']
                    : $a->audience,
                'is_published' => (bool) $a->is_published,
                'is_pinned' => (bool) $a->is_pinned,
                'author' => $a->author->name ?? 'Administrator',
                'created_at' => $a->created_at,
            ])
            ->values();

        return view('admin.announcements', compact('announcements'));
    }

    public function storeAnnouncement(Request $request, CreateAnnouncement $createAnnouncement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'audience' => ['required', 'array', 'min:1'],
            'audience.*' => ['in:student,faculty,public'],
        ], [
            'audience.required' => 'Choose who can see this announcement.',
        ]);
        $data['body'] = RichTextSanitizer::sanitize($data['body']);
        if (in_array('public', $data['audience'], true)) {
            $data['audience'] = ['public'];
        }

        $createAnnouncement->execute($data, Auth::user());

        return redirect()->route('admin.announcements')
            ->with('success', 'Announcement posted.');
    }

    public function updateAnnouncement(Request $request, int $id, UpdateAnnouncement $updateAnnouncement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'audience' => ['required', 'array', 'min:1'],
            'audience.*' => ['in:student,faculty,public'],
        ]);
        $data['body'] = RichTextSanitizer::sanitize($data['body']);
        if (in_array('public', $data['audience'], true)) {
            $data['audience'] = ['public'];
        }

        $updateAnnouncement->execute(Announcement::findOrFail($id), $data);

        return redirect()->route('admin.announcements')
            ->with('success', 'Announcement updated.');
    }

    public function destroyAnnouncement(int $id, DeleteAnnouncement $deleteAnnouncement)
    {
        $title = $deleteAnnouncement->execute(Announcement::findOrFail($id));

        return redirect()->route('admin.announcements')
            ->with('success', '"'.$title.'" was deleted.');
    }

    public function toggleAnnouncementPin(int $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->is_pinned = ! $announcement->is_pinned;
        $announcement->save();

        return redirect()->route('admin.announcements')
            ->with('success', $announcement->is_pinned ? 'Announcement pinned.' : 'Announcement unpinned.');
    }

    // ── Complaint inbox ───────────────────────────────────────────────────

    /**
     * Admin > Complaint Inbox — Help Centre messages raised by students,
     * newest activity first.
     */
    public function complaints(Request $request)
    {
        $threads = Complaint::with(['user', 'replies.author'])
            ->get()
            ->sortByDesc(fn (Complaint $c) => $c->lastActivityAt())
            ->values();

        $selectedId = (int) $request->query('thread', $threads->first()->id ?? 0);
        $selected = $threads->firstWhere('id', $selectedId);

        // Opening a thread marks it read for the admin side.
        if ($selected && $selected->unreadForAdmin()) {
            $selected->admin_read_at = now();
            $selected->save();
        }

        return view('admin.complaints', [
            'threads' => $threads,
            'selected' => $selected,
            'unreadCount' => $threads->filter->unreadForAdmin()->count(),
        ]);
    }

    public function replyComplaint(Request $request, int $id)
    {
        $complaint = Complaint::findOrFail($id);
        $isVisitor = $complaint->isFromVisitor();
        $data = $request->validate([
            'body' => ['required', 'string', 'max:'.($isVisitor ? 20000 : 5000)],
            'attachment' => [
                $isVisitor ? 'prohibited' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:10240',
            ],
        ]);

        $replyBody = $isVisitor
            ? RichTextSanitizer::sanitize($data['body'])
            : trim($data['body']);
        $attachmentUrl = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $directory = public_path('uploads/complaints');

            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $filename = Str::random(40).'.'.strtolower($file->getClientOriginalExtension());
            $file->move($directory, $filename);
            $attachmentUrl = 'uploads/complaints/'.$filename;
            $attachmentName = $file->getClientOriginalName();
        }

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'body' => $replyBody,
            'is_admin' => true,
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $attachmentName,
        ]);

        $complaint->last_reply_at = now();
        $complaint->admin_read_at = now();   // the admin has obviously read it
        $complaint->status = 'open';
        $complaint->save();

        // A visitor has no inbox, so the reply is emailed to them.
        // The send happens AFTER the reply is stored and is wrapped in a
        // try/catch: a mail outage must never lose the admin's message, and
        // the admin needs to be told delivery failed rather than seeing a
        // success flash for an email that never left.
        if ($complaint->isFromVisitor() && $complaint->guest_email) {
            try {
                Mail::to($complaint->guest_email)->send(new ComplaintReplyMail(
                    complaint: $complaint,
                    replyBody: $replyBody,
                    adminName: Auth::user()->name ?? 'UPSKILL Administrator',
                ));

                return redirect()->route('admin.complaints', ['thread' => $complaint->id])
                    ->with('success', 'Reply sent and emailed to '.$complaint->guest_email.'.');
            } catch (\Throwable $e) {
                report($e);

                // No green "sent" flash here — showing success beside a
                // failure warning is contradictory. The warning says the
                // reply was saved.
                return redirect()->route('admin.complaints', ['thread' => $complaint->id])
                    ->withErrors([
                        'mail' => 'Your reply was saved, but the email could not be delivered to '
                            .$complaint->guest_email.'. Reason: '.$e->getMessage(),
                    ]);
            }
        }

        return redirect()->route('admin.complaints', ['thread' => $complaint->id])
            ->with('success', 'Reply sent.');
    }

    public function resolveComplaint(int $id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->status = $complaint->status === 'resolved' ? 'open' : 'resolved';
        $complaint->save();

        return back()->with('success', 'Message thread marked as '.$complaint->status.'.');
    }

    // ── Faculty Codes ─────────────────────────────────────────────────────

    /**
     * Admin › Faculty Codes — lists every shareable faculty registration
     * code with a green (available) / red (used) indicator.
     */
    public function facultyCodes()
    {
        $totalCodes = FacultyCode::count();
        $usedCount = FacultyCode::whereNotNull('used_by')->count();
        $availableCount = $totalCodes - $usedCount;
        $codes = FacultyCode::with('usedBy')
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $codes->getCollection()->transform(fn (FacultyCode $c) => (object) [
            'id' => $c->id,
            'code' => $c->code,
            'is_used' => $c->isUsed(),
            'used_by_name' => $c->usedBy?->name,
            'used_at' => $c->used_at,
            'created_at' => $c->created_at,
        ]);

        return view('admin.faculty-codes', compact('codes', 'totalCodes', 'usedCount', 'availableCount'));
    }

    /**
     * Generate a new shareable faculty code.
     */
    public function generateFacultyCode()
    {
        $code = FacultyCode::create([
            'code' => FacultyCode::generateCode(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.facultycodes')
            ->with('success', 'New faculty code generated: '.$code->code);
    }

    /**
     * Delete an UNUSED code (used codes are kept as an audit trail).
     */
    public function deleteFacultyCode(int $id)
    {
        $code = FacultyCode::findOrFail($id);

        if ($code->isUsed()) {
            return redirect()->route('admin.facultycodes')
                ->with('success', 'Code '.$code->code.' has already been used and cannot be deleted.');
        }

        $code->delete();

        return redirect()->route('admin.facultycodes')
            ->with('success', 'Faculty code '.$code->code.' deleted.');
    }

    // ── Courses & Badges ──────────────────────────────────────────────────

    public function courses()
    {
        // Pending submissions first so the admin always sees what needs
        // review, then everything else alphabetically.
        // Drafts are the faculty member's private workspace — they have not
        // been submitted for review, so the admin never sees them.
        $courses = Course::query()
            ->where('approval_status', '!=', 'draft')
            ->withCount('enrollments')
            ->with(['badge', 'creator'])
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->get()
            ->map(fn (Course $c) => (object) [
                'id' => $c->id,
                'title' => $c->title,
                'thumbnail_url' => $c->thumbnail_url,
                'students' => (int) $c->enrollments_count,
                'faculty' => 1,
                'badge' => $c->badge->name ?? '—',
                // Small badge thumbnail and a certificate flag for the card.
                'badge_icon' => $c->badge->icon_url ?? null,
                'certificate_enabled' => (bool) $c->certificate_enabled,
                'certificate_mode' => $c->certificate_mode,
                'instructor' => $c->creator->name ?? $c->instructor,
                'status' => $c->statusLabel(),
                'status_key' => $this->statusKey($c),
                'percent' => (int) round((float) DB::table('enrollments')->where('course_id', $c->id)->avg('progress_percent')),
                'is_published' => (bool) $c->is_published,
                'can_toggle_publish' => $c->approval_status === 'approved' && (bool) $c->is_approved,
                'is_featured' => (bool) $c->is_featured,
                // "Subject is Related on" — faculty/admin only, never shown
                // to students. Lets an admin see what a submitted course
                // will be recommended against before approving it.
                'related_skills' => FacultyController::relatedSkillsOf($c),
            ])
            ->values();

        $featuredCourseCount = Course::where('is_featured', true)->count();

        return view('admin.courses.index', compact('courses', 'featuredCourseCount'));
    }

    /**
     * Admin › Course Detail — full information about a single course,
     * reached by clicking its card on the Courses & Badges page.
     */
    public function showCourse(CourseReadinessService $courseReadiness, int $id)
    {
        $c = Course::with(['badge', 'creator', 'modules.lessons.quizzes.questions', 'modules.lessons.activities', 'modules.activities', 'modules.quizzes.questions', 'modules.quiz.questions', 'learningOutcomes'])
            ->withCount('enrollments')
            ->findOrFail($id);

        $readinessChecklist = $courseReadiness->checklist($c);
        $readinessIssues = collect($readinessChecklist)
            ->reject(fn (array $item): bool => $item['complete'])
            ->pluck('label')
            ->values()
            ->all();

        $course = (object) [
            'id' => $c->id,
            'title' => $c->title,
            'short_description' => $c->short_description,
            'description' => $c->description,
            'category' => $c->category,
            'program' => $c->program,
            'term' => $c->term,
            'level' => $c->level,
            'pqf_level' => $c->pqf_level,
            'target_learners' => $c->target_learners,
            'delivery_mode' => $c->delivery_mode,
            'learning_hours' => $c->learning_hours,
            'assessment_strategy' => $c->assessment_strategy,
            'grading_rubric' => $c->grading_rubric,
            'learning_outcomes' => $c->learningOutcomes->pluck('description')->all(),
            'duration' => $c->duration,
            'instructor' => $c->creator->name ?? $c->instructor,
            'skills' => $c->skills ?? [],
            // Internal recommendation tags — admin/faculty only.
            'related_skills' => FacultyController::relatedSkillsOf($c),
            'badge' => $c->badge->name ?? '—',
            // Badge artwork and certificate configuration, so the admin can
            // see what a completing student actually receives rather than
            // just the badge's name.
            'badge_icon' => $c->badge->icon_url ?? null,
            'badge_level' => $c->badge->badge_level ?? null,
            'certificate_enabled' => (bool) $c->certificate_enabled,
            'certificate_mode' => $c->certificate_mode,
            'certificate_file' => $c->certificate_file,
            'certificate' => CertificateBuilder::data($c),
            'status' => $c->statusLabel(),
            'status_key' => $this->statusKey($c),
            'change_note' => $c->change_note,
            'denial_feedback' => $c->denial_feedback,
            'readiness_checklist' => $readinessChecklist,
            'can_approve' => $readinessIssues === [],
            'readiness_issues' => $readinessIssues,
            'is_published' => (bool) $c->is_published,
            'can_toggle_publish' => $c->approval_status === 'approved' && (bool) $c->is_approved,
            'students' => (int) $c->enrollments_count,
            'faculty' => 1,
            'percent' => (int) round((float) DB::table('enrollments')->where('course_id', $c->id)->avg('progress_percent')),
            'modules_count' => $c->modules->count(),
            'lessons_count' => $c->modules->sum(fn ($m) => $m->lessons->count()),
            'thumbnail_url' => $c->thumbnail_url,
            'created_at' => $c->created_at,
            'approved_at' => $c->approved_at,
        ];

        $modules = $c->modules->map(fn ($m) => (object) [
            'title' => $m->title,
            'description' => $m->description,
            'is_final' => $m->is_final,
            'lessons' => $m->lessons->map(fn ($l) => (object) [
                'title' => $l->title,
                'type' => $l->type,
                'duration' => $l->duration,
                'content' => $l->content,
                'file_url' => ! empty($l->file_url) ? asset($l->file_url) : null,
                'file_name' => ! empty($l->file_url) ? basename($l->file_url) : null,
                'lesson_quizzes' => $l->quizzes->map(fn ($quiz) => (object) [
                    'title' => $quiz->title,
                    'questions_count' => $quiz->questions->count(),
                    'passing_score' => $quiz->passing_score,
                    'time_limit' => $quiz->time_limit,
                    'instructions' => $quiz->instructions,
                    'questions' => $quiz->questions->map(fn ($question) => (object) [
                        'question' => $question->question,
                        'type' => $question->type ?? 'Multiple Choice',
                        'points' => (int) $question->points,
                        'options' => $question->options ?? [],
                        'correct_answer' => $question->correct_answer,
                    ])->values(),
                ])->values(),
                'lesson_activities' => $l->activities,
            ])->values(),
            'activities' => $m->activities->where('is_active', true)->values(),
            'learning_quizzes' => $m->quizzes->where('is_active', true)->map(fn ($quiz) => (object) [
                'title' => $quiz->title,
                'questions_count' => $quiz->questions->count(),
                'passing_score' => $quiz->passing_score,
                'time_limit' => $quiz->time_limit,
                'instructions' => $quiz->instructions ?? '',
                'questions' => $quiz->questions->map(fn ($question) => (object) [
                    'question' => $question->question,
                    'type' => $question->type ?? 'Multiple Choice',
                    'points' => (int) $question->points,
                    'options' => $question->options ?? [],
                    'correct_answer' => $question->correct_answer,
                ])->values(),
            ])->values(),
            'quiz' => $m->quiz ? (object) [
                'title' => $m->quiz->title,
                'questions_count' => $m->quiz->questions->count(),
                'passing_score' => $m->quiz->passing_score,
                'time_limit' => $m->quiz->time_limit,
                'instructions' => $m->quiz->instructions ?? '',
                'questions' => $m->quiz->questions->map(fn ($q) => (object) [
                    'question' => $q->question,
                    'type' => $q->type ?? 'Multiple Choice',
                    'points' => (int) $q->points,
                    'options' => $q->options ?? [],
                    'correct_answer' => $q->correct_answer,
                ])->values(),
            ] : null,
        ])->values();

        return view('admin.courses.show', compact('course', 'modules'));
    }

    /** Show the learner completion verification queue separately from course content review. */
    public function courseEnrollmentReviews(int $id, Request $request): View
    {
        $courseModel = Course::findOrFail($id);
        $course = (object) [
            'id' => $courseModel->id,
            'title' => $courseModel->title,
            'category' => $courseModel->category,
            'level' => $courseModel->level,
        ];

        return $this->enrollmentReviewPage($course, $courseModel->id, $request);
    }

    /** Show the full learner completion verification queue from the admin sidebar. */
    public function enrollmentReviews(Request $request): View
    {
        return $this->enrollmentReviewPage(null, null, $request);
    }

    /** Show the learning record and submitted work for one enrollment. */
    public function showEnrollmentReview(Enrollment $enrollment): View
    {
        $enrollment->load(['user', 'course.modules.lessons.activities', 'course.modules.activities', 'course.modules.quizzes']);
        $course = $enrollment->course;
        abort_if(! $course, 404);

        $institutionalLabel = CompletionStatusPresenter::institutionalLabel(
            $enrollment->completion_status,
            $enrollment->faculty_verification_status,
            $enrollment->academic_unit_confirmation_status,
        );
        $enrollment->setAttribute('institutional_label', $institutionalLabel);
        $enrollment->setAttribute('review_status', match (true) {
            $institutionalLabel === 'Rejected' => 'denied',
            $institutionalLabel === 'Completed' => 'approved',
            $enrollment->academic_unit_confirmation_status === 'pending'
                && $enrollment->completion_status === MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION => 'pending',
            default => 'other',
        });
        $enrollment->setAttribute('academic_confirmation_actionable',
            $enrollment->academic_unit_confirmation_status === 'pending'
            && $enrollment->completion_status === MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION
        );

        $modules = $course->modules;
        $lessonIds = $modules->flatMap(fn ($module) => $module->lessons->pluck('id'))->values();
        $lessonCompletions = DB::table('lesson_completions')
            ->where('user_id', $enrollment->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');
        $activitySubmissions = LessonActivitySubmission::with('reviewer')
            ->where('user_id', $enrollment->user_id)
            ->whereHas('activity', function ($activity) use ($course): void {
                $activity->whereHas('lesson', fn ($lesson) => $lesson->where('course_id', $course->id))
                    ->orWhereHas('module', fn ($module) => $module->where('course_id', $course->id));
            })
            ->latest('submitted_at')
            ->get()
            ->groupBy('lesson_activity_id');
        $quizzes = Quiz::where('course_id', $course->id)
            ->where(function ($query) use ($course) {
                $query->whereHas('module', fn ($moduleQuery) => $moduleQuery->where('course_id', $course->id))
                    ->orWhereHas('lesson', fn ($lessonQuery) => $lessonQuery->where('course_id', $course->id));
            })
            ->get();
        $quizAttempts = QuizAttempt::with(['quiz', 'answers.question'])
            ->where('user_id', $enrollment->user_id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->latest('submitted_at')
            ->get()
            ->groupBy('quiz_id');
        $lessonQuizzes = $quizzes->whereNotNull('lesson_id')->groupBy('lesson_id');
        $moduleQuizzes = $quizzes->whereNotNull('module_id')->where('is_summative', true)->keyBy('module_id');
        $moduleLearningQuizzes = $quizzes->whereNotNull('module_id')->where('is_summative', false)->groupBy('module_id');
        $activityCount = $modules->sum(fn ($module) => $module->lessons->sum(fn ($lesson) => $lesson->activities->count()) + $module->activities->count());
        $completedActivities = $activitySubmissions->keys()->count();
        $passedQuizzes = $quizAttempts->flatten(1)->where('passed', true)->pluck('quiz_id')->unique()->count();

        return view('admin.enrollments.show', [
            'enrollment' => $enrollment,
            'course' => $course,
            'student' => $enrollment->user,
            'modules' => $modules,
            'lessonCompletions' => $lessonCompletions,
            'activitySubmissions' => $activitySubmissions,
            'quizzes' => $quizzes,
            'lessonQuizzes' => $lessonQuizzes,
            'moduleQuizzes' => $moduleQuizzes,
            'moduleLearningQuizzes' => $moduleLearningQuizzes,
            'quizAttempts' => $quizAttempts,
            'completedLessons' => $lessonCompletions->count(),
            'totalLessons' => $lessonIds->count(),
            'completedActivities' => $completedActivities,
            'totalActivities' => $activityCount,
            'passedQuizzes' => $passedQuizzes,
            'totalQuizzes' => $quizzes->count(),
            'reviewRole' => 'admin',
        ]);
    }

    public function downloadEnrollmentSubmission(LessonActivitySubmission $submission): StreamedResponse
    {
        $submission->load(['activity.lesson.course', 'activity.module.course']);
        abort_unless($submission->activity?->lesson?->course || $submission->activity?->module?->course, 404);
        abort_unless($submission->submission_path && Storage::disk('local')->exists($submission->submission_path), 404);

        return Storage::disk('local')->download($submission->submission_path, basename($submission->submission_path));
    }

    public function viewEnrollmentSubmission(LessonActivitySubmission $submission): BinaryFileResponse
    {
        $submission->load(['activity.lesson.course', 'activity.module.course']);
        abort_unless($submission->activity?->lesson?->course || $submission->activity?->module?->course, 404);
        abort_unless($submission->submission_path && Storage::disk('local')->exists($submission->submission_path), 404);

        $path = Storage::disk('local')->path($submission->submission_path);
        $mimeType = Storage::disk('local')->mimeType($submission->submission_path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.addslashes(basename($submission->submission_path)).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function enrollmentReviewPage(?object $course, ?int $courseId, ?Request $request = null): View
    {
        $filter = $request?->query('filter', 'all') ?? 'all';
        if (! in_array($filter, ['all', 'pending', 'approved', 'denied'], true)) {
            $filter = 'all';
        }

        $courses = collect();
        $selectedCourseId = $courseId;
        if ($courseId === null) {
            $validated = $request?->validate([
                'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            ]) ?? [];
            $selectedCourseId = isset($validated['course_id']) ? (int) $validated['course_id'] : null;
            $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        }

        $baseQuery = $this->enrollmentReviewQuery($selectedCourseId);
        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => $this->filterEnrollmentReviewQuery(clone $baseQuery, 'pending')->count(),
            'approved' => $this->filterEnrollmentReviewQuery(clone $baseQuery, 'approved')->count(),
            'denied' => $this->filterEnrollmentReviewQuery(clone $baseQuery, 'denied')->count(),
        ];
        $enrollments = $this->filterEnrollmentReviewQuery($baseQuery, $filter)
            ->latest('enrolled_at')
            ->paginate(20)
            ->withQueryString();
        $enrollments->getCollection()->transform(fn (Enrollment $enrollment): object => $this->presentEnrollmentReview($enrollment));
        $pendingConfirmations = $statusCounts['pending'];

        return view('admin.courses.enrollments', compact('course', 'courses', 'selectedCourseId', 'enrollments', 'pendingConfirmations', 'statusCounts', 'filter'));
    }

    private function enrollmentReviewQuery(?int $courseId = null): Builder
    {
        return Enrollment::with(['user', 'facultyVerifier', 'academicUnitConfirmer', 'course'])
            ->when($courseId !== null, fn (Builder $query) => $query->where('course_id', $courseId));
    }

    private function filterEnrollmentReviewQuery(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            'pending' => $query
                ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
                ->where('academic_unit_confirmation_status', 'pending')
                ->whereHas('course.reviews', fn (Builder $reviewQuery) => $reviewQuery->whereColumn('course_reviews.user_id', 'enrollments.user_id')),
            'approved' => $query
                ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->where('academic_unit_confirmation_status', 'confirmed'),
            'denied' => $query->where(function (Builder $query): void {
                $query->where('completion_status', MicrocredentialCompletionService::STATUS_REJECTED)
                    ->orWhere('faculty_verification_status', 'rejected')
                    ->orWhere('academic_unit_confirmation_status', 'rejected');
            }),
            default => $query,
        };
    }

    private function presentEnrollmentReview(Enrollment $enrollment): object
    {
        $institutionalLabel = CompletionStatusPresenter::institutionalLabel(
            $enrollment->completion_status,
            $enrollment->faculty_verification_status,
            $enrollment->academic_unit_confirmation_status,
        );
        $actionable = $enrollment->academic_unit_confirmation_status === 'pending'
            && $enrollment->completion_status === MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION;

        return (object) [
            'id' => $enrollment->id,
            'course_id' => $enrollment->course_id,
            'course_title' => $enrollment->course->title ?? 'Course',
            'student_name' => $enrollment->user->name ?? 'Student',
            'completion_status' => $enrollment->completion_status,
            'faculty_verification_status' => $enrollment->faculty_verification_status,
            'academic_unit_confirmation_status' => $enrollment->academic_unit_confirmation_status,
            'completed_at' => $enrollment->completed_at,
            'faculty_verified_by' => $enrollment->facultyVerifier->name ?? null,
            'academic_unit_confirmed_by' => $enrollment->academicUnitConfirmer->name ?? null,
            'academic_confirmation_actionable' => $actionable,
            'grant_redo_actionable' => $enrollment->completion_status === MicrocredentialCompletionService::STATUS_REJECTED
                || $enrollment->faculty_verification_status === 'rejected'
                || $enrollment->academic_unit_confirmation_status === 'rejected',
            'institutional_label' => $institutionalLabel,
        ];
    }

    public function grantEnrollmentRedo(
        Request $request,
        Enrollment $enrollment,
        UserNotificationService $userNotifications,
    ): RedirectResponse {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $submissionPaths = [];
        $courseTitle = '';
        $enrollment = DB::transaction(function () use ($enrollment, &$submissionPaths, &$courseTitle): Enrollment {
            $lockedEnrollment = Enrollment::query()
                ->with('course')
                ->lockForUpdate()
                ->findOrFail($enrollment->id);
            $isRejected = $lockedEnrollment->completion_status === MicrocredentialCompletionService::STATUS_REJECTED
                || $lockedEnrollment->faculty_verification_status === 'rejected'
                || $lockedEnrollment->academic_unit_confirmation_status === 'rejected';

            abort_unless($isRejected && $lockedEnrollment->course, 404);

            $course = $lockedEnrollment->course;
            $courseTitle = $course->title;
            $lessonIds = DB::table('course_lessons')->where('course_id', $course->id)->pluck('id');
            $moduleIds = DB::table('course_modules')->where('course_id', $course->id)->pluck('id');
            $activityIds = DB::table('lesson_activities')
                ->where(function ($query) use ($lessonIds, $moduleIds): void {
                    $query->whereIn('lesson_id', $lessonIds)
                        ->orWhereIn('module_id', $moduleIds);
                })
                ->pluck('id');
            $quizIds = DB::table('quizzes')->where('course_id', $course->id)->pluck('id');

            $submissions = DB::table('lesson_activity_submissions')
                ->where('user_id', $lockedEnrollment->user_id)
                ->whereIn('lesson_activity_id', $activityIds);
            $submissionPaths = (clone $submissions)->whereNotNull('submission_path')->pluck('submission_path')->all();
            $submissions->delete();

            DB::table('lesson_completions')
                ->where('user_id', $lockedEnrollment->user_id)
                ->whereIn('lesson_id', $lessonIds)
                ->delete();
            DB::table('quiz_attempts')
                ->where('user_id', $lockedEnrollment->user_id)
                ->whereIn('quiz_id', $quizIds)
                ->delete();
            DB::table('course_reviews')
                ->where('user_id', $lockedEnrollment->user_id)
                ->where('course_id', $course->id)
                ->delete();

            $lockedEnrollment->fill([
                'enrolled_at' => now(),
                'is_completed' => false,
                'progress_percent' => 0,
                'progress_state' => [],
                'completion_status' => MicrocredentialCompletionService::STATUS_IN_PROGRESS,
                'lessons_completed' => false,
                'quizzes_completed' => false,
                'quiz_mastery_met' => false,
                'competency_mastery_met' => false,
                'manual_assessment_status' => $course->manual_assessment_required ? 'pending' : 'not_required',
                'attendance_status' => $course->attendance_requirement_enabled ? 'pending' : 'not_required',
                'performance_status' => $course->performance_requirement_enabled ? 'pending' : 'not_required',
                'administrative_status' => $course->admin_requirement_enabled ? 'pending' : 'not_required',
                'faculty_verification_status' => $course->requires_faculty_verification ? 'pending' : 'not_required',
                'faculty_verified_by' => null,
                'faculty_verified_at' => null,
                'academic_unit_confirmation_status' => 'pending',
                'academic_unit_confirmed_by' => null,
                'academic_unit_confirmed_at' => null,
                'completed_at' => null,
            ])->save();

            return $lockedEnrollment;
        });

        foreach ($submissionPaths as $submissionPath) {
            Storage::disk('local')->delete($submissionPath);
        }

        $userNotifications->createForUser(
            (int) $enrollment->user_id,
            'Another course attempt was granted',
            'You may now retake "'.$courseTitle.'" from the beginning. Your previous course progress was reset. Reason: '.$data['reason'],
            'enrollment',
            'enrollment',
            (int) $enrollment->id,
        );

        return redirect()->back()->with('success', 'Another course attempt was granted and progress was reset.');
    }

    /**
     * Academic-unit confirmation action (Phase 3, Step 11).
     *
     * Credential-access-layer action only: records the admin's decision
     * and re-runs MicrocredentialCompletionService::evaluate() via
     * recordAcademicUnitConfirmation(). It never sets completion_status,
     * completed_at, issues a badge/certificate, or creates an academic
     * credit recognition record itself.
     *
     * The route carries BOTH the course id and the enrollment id
     * ({id}/enrollments/{enrollment}), and this method verifies the
     * enrollment actually belongs to that course — so an admin cannot
     * accidentally confirm an enrollment from a different course than
     * the one currently being viewed, even if a stale/tampered form is
     * submitted.
     */
    public function academicConfirmEnrollment(
        Request $request,
        int $id,
        Enrollment $enrollment,
        MicrocredentialCompletionService $completionService,
        UserNotificationService $userNotifications,
    ) {
        abort_if($enrollment->course_id !== $id, 404);

        $data = $request->validate([
            'decision' => 'required|string|in:confirmed,rejected',
        ]);
        $alreadyRecorded = $enrollment->academic_unit_confirmation_status === $data['decision'];

        try {
            $updatedEnrollment = $completionService->recordAcademicUnitConfirmation($enrollment, Auth::id(), $data['decision']);
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['decision' => $e->getMessage()]);
        }

        if (! $alreadyRecorded) {
            $courseTitle = $updatedEnrollment->course?->title ?? 'your course';
            $decisionMessage = $data['decision'] === 'confirmed'
                ? 'Institutional confirmation is complete for "'.$courseTitle.'".'
                : 'Institutional confirmation was not approved for "'.$courseTitle.'". Contact your institution for more information.';

            $userNotifications->createForUser(
                $enrollment->user_id,
                'Institutional review update',
                $decisionMessage,
                'enrollment',
                'enrollment',
                $enrollment->id,
            );
            $userNotifications->createForUser(
                $updatedEnrollment->course?->created_by,
                'Institutional review completed',
                'Institutional confirmation was '.$data['decision'].' for a learner in "'.$courseTitle.'".',
                'enrollment',
                'enrollment',
                $enrollment->id,
            );
        }

        return redirect()->route('admin.enrollments')
            ->with('success', $data['decision'] === 'confirmed'
                ? 'Academic-unit confirmation recorded.'
                : 'Enrollment marked as rejected.');
    }

    /**
     * Approve a pending course → publishes it for students to browse
     * and enroll in.
     */
    public function approveCourse(int $id, CourseModerationService $moderation)
    {
        $course = Course::findOrFail($id);
        try {
            $moderation->approve($course, (int) Auth::id());
        } catch (\DomainException $exception) {
            return back()->withErrors(['approval' => $exception->getMessage()]);
        }

        return redirect()->route('admin.courses')
            ->with('success', '"'.$course->title.'" has been approved and is now published.');
    }

    /**
     * Deny a pending course → it stays hidden from students.
     */
    public function denyCourse(Request $request, int $id, CourseModerationService $moderation)
    {
        $data = $request->validate([
            'denial_feedback' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'denial_feedback.required' => 'Please tell the faculty member why the course was denied.',
        ]);

        $course = Course::findOrFail($id);

        $moderation->deny($course, $data['denial_feedback'], (int) Auth::id());

        return redirect()->route('admin.courses')
            ->with('success', '"'.$course->title.'" has been denied and the feedback was sent to the author.');
    }

    /**
     * Publish / unpublish a course — admin-only. Unpublished courses are
     * hidden from students even if they were previously approved.
     */
    public function togglePublishCourse(int $id, CourseModerationService $moderation)
    {
        $course = Course::findOrFail($id);
        try {
            $published = $moderation->togglePublish($course);
        } catch (\DomainException $e) {
            return back()->withErrors(['publish' => $e->getMessage()]);
        }

        return back()->with('success', '"'.$course->title.'" is now '
            .($published ? 'published.' : 'unpublished — hidden from students.'));
    }

    /**
     * Feature / unfeature a course on the homepage — admin-only.
     */
    public function toggleFeatureCourse(int $id, CourseModerationService $moderation)
    {
        $course = Course::findOrFail($id);
        try {
            $featured = $moderation->toggleFeature($course);
        } catch (\DomainException $e) {
            return back()->withErrors(['feature' => $e->getMessage()]);
        }

        if ($featured && ! $course->is_approved) {
            return back()->with('success', '"'.$course->title.'" is marked as featured. '
                .'It will appear on the homepage once the course is approved.');
        }

        return back()->with('success', '"'.$course->title.'" '
            .($featured
                ? 'is now featured on the homepage.'
                : 'was removed from the homepage features.'));
    }

    /**
     * Permanently deletes a course and everything attached to it
     * (modules, lessons, quizzes, enrollments cascade via FK).
     */
    public function destroyCourse(int $id, CourseModerationService $moderation)
    {
        $course = Course::findOrFail($id);
        $title = $moderation->delete($course);

        return redirect()->route('admin.courses')
            ->with('success', '"'.$title.'" has been deleted.');
    }

    /**
     * Lowercase status key used for the coloured pills
     * (pending / approved / denied / draft).
     */
    private function statusKey(Course $c): string
    {
        if ($c->approval_status === 'approved' || $c->is_approved) {
            return 'approved';
        }

        return in_array($c->approval_status, ['pending', 'denied', 'draft'], true)
            ? $c->approval_status
            : 'pending';
    }

    // ── Analytics Report ──────────────────────────────────────────────────

    public function report(Request $request)
    {
        $studentRole = User::ROLE_STUDENT;
        $period = $request->query('period', 'all');
        $activityMode = $request->query('activity', 'enrollments');
        $period = in_array($period, ['all', 'year', 'month', 'week'], true) ? $period : 'all';
        $periodStart = match ($period) {
            'year' => now()->startOfYear(),
            'month' => now()->startOfMonth(),
            'week' => now()->startOfWeek(),
            default => null,
        };
        $activityMode = in_array($activityMode, ['enrollments', 'active', 'completions'], true) ? $activityMode : 'enrollments';
        $filters = [
            'category' => $request->query('category'),
            'course' => $request->query('course'),
        ];
        $applyCourseFilters = function ($query) use ($filters): void {
            $query->when($filters['category'], fn ($builder, $value) => $builder->where('courses.category', $value))
                ->when($filters['course'], fn ($builder, $value) => $builder->where('courses.id', $value));
        };
        $enrollmentTable = DB::table('enrollments')->join('courses', 'courses.id', '=', 'enrollments.course_id');
        $applyEnrollmentFilters = function ($query) use ($applyCourseFilters, $periodStart): void {
            $applyCourseFilters($query);
            if ($periodStart) {
                $query->where('enrollments.enrolled_at', '>=', $periodStart);
            }
        };
        $applyEnrollmentFilters($enrollmentTable);
        $applyAnalyticsEventFilters = function ($query) use ($applyCourseFilters): void {
            $query->where(function ($events) use ($applyCourseFilters) {
                $events->where(function ($courseEvents) use ($applyCourseFilters) {
                    $courseEvents->where('analytics_events.entity_type', 'course')->whereExists(function ($courses) use ($applyCourseFilters) {
                        $courses->selectRaw('1')->from('courses')
                            ->whereColumn('courses.id', 'analytics_events.entity_id');
                        $applyCourseFilters($courses);
                    });
                })->orWhere(function ($lessonEvents) use ($applyCourseFilters) {
                    $lessonEvents->where('analytics_events.entity_type', 'lesson')->whereExists(function ($lessons) use ($applyCourseFilters) {
                        $lessons->selectRaw('1')->from('course_lessons')
                            ->join('courses', 'courses.id', '=', 'course_lessons.course_id')
                            ->whereColumn('course_lessons.id', 'analytics_events.entity_id');
                        $applyCourseFilters($lessons);
                    });
                })->orWhere(function ($quizEvents) use ($applyCourseFilters) {
                    $quizEvents->where('analytics_events.entity_type', 'quiz')->whereExists(function ($quizzes) use ($applyCourseFilters) {
                        $quizzes->selectRaw('1')->from('quizzes')
                            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
                            ->whereColumn('quizzes.id', 'analytics_events.entity_id');
                        $applyCourseFilters($quizzes);
                    });
                });
            });
        };
        $quizScores = DB::table('quiz_attempts')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
            ->whereNotNull('quiz_attempts.score');
        $applyCourseFilters($quizScores);
        if ($periodStart) {
            $quizScores->whereRaw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.created_at) >= ?', [$periodStart]);
        }
        $certificateQuery = DB::table('certificates')->join('courses', 'courses.id', '=', 'certificates.course_id');
        $applyCourseFilters($certificateQuery);
        if ($periodStart) {
            $certificateQuery->where('certificates.issued_at', '>=', $periodStart);
        }
        $badgeQuery = DB::table('user_badges')->join('badges', 'badges.id', '=', 'user_badges.badge_id');
        $badgeQuery->where('user_badges.status', 'active')
            ->when($periodStart, fn ($query) => $query->where('user_badges.earned_at', '>=', $periodStart))
            ->whereExists(function ($courses) use ($applyCourseFilters) {
                $courses->selectRaw('1')->from('courses')
                    ->whereColumn('courses.badge_id', 'badges.id');
                $applyCourseFilters($courses);
            });

        $stats = [
            'total_students' => User::where('role_id', $studentRole)
                ->whereIn('id', (clone $enrollmentTable)->select('enrollments.user_id')->distinct())
                ->count(),
            'active_students' => (function () use ($studentRole, $periodStart, $applyAnalyticsEventFilters): int {
                $activeEvents = DB::table('analytics_events')
                    ->whereIn('analytics_events.user_id', User::query()->select('id')->where('role_id', $studentRole))
                    ->where('analytics_events.occurred_at', '>=', $periodStart ?: now()->subDays(30));
                $applyAnalyticsEventFilters($activeEvents);

                return (int) $activeEvents->distinct()->count('analytics_events.user_id');
            })(),
            'total_courses' => Course::query()->tap($applyCourseFilters)->count(),
            'completions' => (function () use ($applyCourseFilters, $periodStart): int {
                $completedEnrollments = DB::table('enrollments')
                    ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                    ->where('enrollments.completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
                    ->when($periodStart, fn ($query) => $query->where('enrollments.completed_at', '>=', $periodStart));
                $applyCourseFilters($completedEnrollments);

                return $completedEnrollments->count();
            })(),
            'credentials' => (clone $certificateQuery)->where('certificates.status', 'active')->count(),
            'course_score_avg' => $quizScores->avg('score') !== null ? round((float) $quizScores->avg('score'), 1) : null,
            'average_completion' => round((float) (clone $enrollmentTable)->avg('enrollments.progress_percent')),
        ];

        $trendCurrentStart = $periodStart ?: now()->copy()->subDays(30);
        $trendCurrentEnd = now();
        $trendWindowSeconds = $trendCurrentStart->diffInSeconds($trendCurrentEnd);
        $trendPreviousStart = $trendCurrentStart->copy()->subSeconds($trendWindowSeconds);
        $trendPreviousEnd = $trendCurrentStart->copy();
        $getTrendCounts = function (callable $query, string $dateColumn, ?string $distinctColumn = null) use ($trendCurrentStart, $trendCurrentEnd, $trendPreviousStart, $trendPreviousEnd): array {
            $countInRange = function ($start, $end) use ($query, $dateColumn, $distinctColumn): int {
                $builder = $query()->where($dateColumn, '>=', $start)->where($dateColumn, '<', $end);

                return (int) ($distinctColumn ? $builder->distinct()->count($distinctColumn) : $builder->count());
            };

            return [
                'current' => $countInRange($trendCurrentStart, $trendCurrentEnd),
                'previous' => $countInRange($trendPreviousStart, $trendPreviousEnd),
            ];
        };
        $learnerTrend = $getTrendCounts(function () use ($applyCourseFilters) {
            $query = DB::table('enrollments')->join('courses', 'courses.id', '=', 'enrollments.course_id');
            $applyCourseFilters($query);

            return $query;
        }, 'enrollments.enrolled_at', 'enrollments.user_id');
        $courseTrend = $getTrendCounts(function () use ($applyCourseFilters) {
            $query = Course::query();
            $applyCourseFilters($query);

            return $query;
        }, 'courses.created_at');
        $completionTrend = $getTrendCounts(function () use ($applyCourseFilters) {
            $query = DB::table('enrollments')->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.completion_status', MicrocredentialCompletionService::STATUS_COMPLETED);
            $applyCourseFilters($query);

            return $query;
        }, 'enrollments.completed_at');
        $credentialTrend = $getTrendCounts(function () use ($applyCourseFilters) {
            $query = DB::table('certificates')->join('courses', 'courses.id', '=', 'certificates.course_id')
                ->where('certificates.status', 'active');
            $applyCourseFilters($query);

            return $query;
        }, 'certificates.issued_at');
        $activeLearnerTrend = $getTrendCounts(function () use ($studentRole, $applyAnalyticsEventFilters) {
            $query = DB::table('analytics_events')
                ->whereIn('analytics_events.user_id', User::query()->select('id')->where('role_id', $studentRole));
            $applyAnalyticsEventFilters($query);

            return $query;
        }, 'analytics_events.occurred_at', 'analytics_events.user_id');
        $assessmentTrend = function () use ($applyCourseFilters, $trendCurrentStart, $trendCurrentEnd, $trendPreviousStart, $trendPreviousEnd): array {
            $averageInRange = function ($start, $end) use ($applyCourseFilters): ?float {
                $query = DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->join('courses', 'courses.id', '=', 'quizzes.course_id')
                    ->whereNotNull('quiz_attempts.score')
                    ->whereRaw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.created_at) >= ?', [$start])
                    ->whereRaw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.created_at) < ?', [$end]);
                $applyCourseFilters($query);

                $average = $query->avg('quiz_attempts.score');

                return $average === null ? null : (float) $average;
            };

            return [
                'current' => $averageInRange($trendCurrentStart, $trendCurrentEnd),
                'previous' => $averageInRange($trendPreviousStart, $trendPreviousEnd),
            ];
        };
        $stats['trends'] = [
            'learners' => $learnerTrend,
            'active_learners' => $activeLearnerTrend,
            'courses' => $courseTrend,
            'completions' => $completionTrend,
            'credentials' => $credentialTrend,
            'assessment_score' => $assessmentTrend(),
        ];

        $coursePerformanceQuery = Course::query()
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoinSub(
                DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->select('quizzes.course_id', DB::raw('AVG(quiz_attempts.score) as average_score'))
                    ->whereNotNull('quiz_attempts.score')
                    ->when($periodStart, fn ($query) => $query->whereRaw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.created_at) >= ?', [$periodStart]))
                    ->groupBy('quizzes.course_id'),
                'course_scores',
                'course_scores.course_id',
                '=',
                'courses.id'
            )
            ->when($filters['category'], fn ($query, $value) => $query->where('courses.category', $value))
            ->when($filters['course'], fn ($query, $value) => $query->where('courses.id', $value))
            ->when($periodStart, fn ($query) => $query->where('enrollments.enrolled_at', '>=', $periodStart))
            ->select(
                'courses.id',
                'courses.title',
                DB::raw('COUNT(enrollments.id) as enrolled'),
                DB::raw('COALESCE(AVG(enrollments.progress_percent), 0) as completion'),
                'course_scores.average_score'
            )
            ->groupBy('courses.id', 'courses.title', 'course_scores.average_score')
            ->orderByDesc('enrolled')
            ->limit(5)
            ->get();
        $coursePerformance = $coursePerformanceQuery
            ->map(fn ($course) => (object) [
                'title' => $course->title,
                'enrolled' => (int) $course->enrolled,
                'completion' => (int) round($course->completion),
                'score' => $course->average_score !== null ? (int) round($course->average_score) : null,
            ]);
        $lowProgressCourseCount = Course::query()
            ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->whereNotNull('enrollments.id')
            ->when($periodStart, fn ($query) => $query->where('enrollments.enrolled_at', '>=', $periodStart))
            ->tap($applyCourseFilters)
            ->groupBy('courses.id')
            ->havingRaw('AVG(enrollments.progress_percent) < ?', [50])
            ->get(['courses.id'])
            ->count();

        $progressRows = (clone $enrollmentTable)
            ->select('enrollments.user_id', DB::raw('MAX(enrollments.progress_percent) as progress_percent'), DB::raw("MAX(CASE WHEN enrollments.completion_status = 'completed' THEN 1 ELSE 0 END) as completed"))
            ->groupBy('enrollments.user_id')
            ->get();
        $progressCounts = [
            'completed' => $progressRows->where('completed', 1)->count(),
            'in_progress' => $progressRows->where('completed', 0)->where('progress_percent', '>', 0)->count(),
            'not_started' => $progressRows->where('completed', 0)->where('progress_percent', 0)->count(),
        ];
        $progressTotal = max(1, array_sum($progressCounts));
        $progress = collect($progressCounts)->map(fn (int $count) => (int) round($count / $progressTotal * 100));

        $activityScope = function ($query) use ($applyAnalyticsEventFilters, $studentRole, $activityMode, $applyCourseFilters, $periodStart): void {
            if ($activityMode === 'active') {
                $query->whereIn('analytics_events.user_id', User::query()->select('id')->where('role_id', $studentRole));
                $applyAnalyticsEventFilters($query);
                if ($periodStart) {
                    $query->where('analytics_events.occurred_at', '>=', $periodStart);
                }

                return;
            }

            $query->join('courses', 'courses.id', '=', 'enrollments.course_id');
            $applyCourseFilters($query);
            if ($activityMode === 'completions') {
                $query->where('enrollments.completion_status', MicrocredentialCompletionService::STATUS_COMPLETED);
            } elseif ($periodStart) {
                $query->where('enrollments.enrolled_at', '>=', $periodStart);
            }
        };
        $activity = match ($activityMode) {
            'active' => $this->monthlySeries('analytics_events', 'occurred_at', $activityScope, true),
            'completions' => $this->monthlySeries('enrollments', 'completed_at', $activityScope),
            default => $this->monthlySeries('enrollments', 'enrolled_at', $activityScope),
        };
        $credentialActivity = $this->monthlySeries('certificates', 'issued_at', function ($query) use ($applyCourseFilters): void {
            $query->join('courses', 'courses.id', '=', 'certificates.course_id');
            $applyCourseFilters($query);
            $query->where('certificates.status', 'active');
        });
        $assessmentScores = (clone $quizScores)->pluck('score');
        $finalExamAverage = (clone $quizScores)
            ->join('course_modules', 'course_modules.id', '=', 'quizzes.module_id')
            ->where('course_modules.is_final', true)
            ->avg('quiz_attempts.score');
        $assessmentCount = $assessmentScores->count();
        $passedAttempts = (clone $quizScores)->where('quiz_attempts.passed', true)->count();
        $passRate = $assessmentScores->count() > 0
            ? (int) round($passedAttempts / $assessmentScores->count() * 100)
            : null;
        $competencies = DB::table('competency_progresses')
            ->join('competency_units', 'competency_units.id', '=', 'competency_progresses.competency_unit_id')
            ->join('competency_categories', 'competency_categories.id', '=', 'competency_units.competency_category_id')
            ->whereExists(function ($outcomes) use ($applyCourseFilters) {
                $outcomes->selectRaw('1')->from('learning_outcomes')
                    ->join('courses', 'courses.id', '=', 'learning_outcomes.course_id')
                    ->whereColumn('learning_outcomes.competency_unit_id', 'competency_units.id');
                $applyCourseFilters($outcomes);
            })
            ->select('competency_categories.name', DB::raw('AVG(competency_progresses.mastery_score) as mastery'))
            ->groupBy('competency_categories.id', 'competency_categories.name')
            ->orderByDesc('mastery')
            ->get()
            ->map(fn ($competency) => (object) ['name' => $competency->name, 'mastery' => (int) round($competency->mastery)]);
        $lowMasteryCompetencyCount = $competencies->filter(fn ($competency) => $competency->mastery < 40)->count();
        $competencies = $competencies->take(4)->values();
        $lowPassQuizCount = DB::table('quiz_attempts')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
            ->whereNotNull('quiz_attempts.score')
            ->when($periodStart, fn ($query) => $query->whereRaw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.created_at) >= ?', [$periodStart]))
            ->tap($applyCourseFilters)
            ->groupBy('quizzes.id')
            ->havingRaw('AVG(CASE WHEN quiz_attempts.passed = ? THEN 1.0 ELSE 0.0 END) < ?', [true, 0.6])
            ->get(['quizzes.id'])
            ->count();
        $courseOptionQuery = Course::query()
            ->when($filters['category'], fn ($query, $value) => $query->where('category', $value));
        $courseOptions = $courseOptionQuery->orderBy('title')->get(['id', 'title']);
        $ageBands = ['Under 18', '18–24', '25–34', '35–44', '45–54', '55+', 'Not specified'];
        $genderGroups = ['Female', 'Male', 'Other', 'Prefer not to say', 'Not specified'];
        $demographics = [
            'total' => 0,
            'age' => array_fill_keys($ageBands, 0),
            'gender' => array_fill_keys($genderGroups, 0),
        ];
        $demographicEnrollments = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users', 'users.id', '=', 'enrollments.user_id')
            ->where('users.role_id', $studentRole)
            ->tap($applyCourseFilters)
            ->when($periodStart, fn ($query) => $query->where('enrollments.enrolled_at', '>=', $periodStart))
            ->select('courses.id as course_id', 'users.id as learner_id', 'users.date_of_birth', 'users.gender')
            ->distinct()
            ->get();

        foreach ($demographicEnrollments as $enrollment) {
            $demographics['total']++;
            $ageBand = 'Not specified';
            $birthDateValue = trim((string) $enrollment->date_of_birth);

            if ($birthDateValue !== '') {
                $birthDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDateValue);
                $dateErrors = \DateTimeImmutable::getLastErrors();

                if ($birthDate !== false
                    && $birthDate->format('Y-m-d') === $birthDateValue
                    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
                    && $birthDate <= new \DateTimeImmutable('today')) {
                    $age = $birthDate->diff(new \DateTimeImmutable('today'))->y;
                    $ageBand = match (true) {
                        $age < 18 => 'Under 18',
                        $age <= 24 => '18–24',
                        $age <= 34 => '25–34',
                        $age <= 44 => '35–44',
                        $age <= 54 => '45–54',
                        default => '55+',
                    };
                }
            }

            $demographics['age'][$ageBand]++;
            $gender = strtolower(trim((string) $enrollment->gender));
            $genderGroup = match ($gender) {
                'female' => 'Female',
                'male' => 'Male',
                'prefer not to say' => 'Prefer not to say',
                '' => 'Not specified',
                default => 'Other',
            };
            $demographics['gender'][$genderGroup]++;
        }

        return view('admin.analytics-report', [
            'stats' => $stats,
            'trendPeriodLabel' => $period === 'all' ? 'vs prior 30 days' : 'vs preceding period',
            'coursePerformance' => $coursePerformance,
            'progress' => $progress,
            'activity' => $activity,
            'credentialActivity' => $credentialActivity,
            'competencies' => $competencies,
            'assessment' => [
                'total' => $assessmentCount,
                'average' => $assessmentScores->count() ? (int) round((float) $assessmentScores->avg()) : null,
                'final_exam_average' => $finalExamAverage !== null ? (int) round((float) $finalExamAverage) : null,
                'pass_rate' => $passRate,
                'highest' => $assessmentScores->max() !== null ? (int) $assessmentScores->max() : null,
                'lowest' => $assessmentScores->min() !== null ? (int) $assessmentScores->min() : null,
            ],
            'demographics' => $demographics,
            'ageBands' => $ageBands,
            'genderGroups' => $genderGroups,
            'categoryNames' => CourseCategory::activeNames(),
            'courseOptions' => $courseOptions,
            'filters' => $filters + ['period' => $period, 'activity' => $activityMode],
            'period' => $period,
            'activityMode' => $activityMode,
            'credentialMetrics' => [
                'certificates' => (clone $certificateQuery)->where('certificates.status', 'active')->count(),
                'badges' => (clone $badgeQuery)->count(),
                'verified' => (clone $certificateQuery)->where('certificates.status', 'active')->whereNotNull('certificates.serial')->count(),
                'pending' => Course::query()->tap($applyCourseFilters)->where('approval_status', 'pending')->count(),
            ],
            'attention' => [
                $lowProgressCourseCount,
                $lowMasteryCompetencyCount,
                (clone $enrollmentTable)->where('enrollments.updated_at', '<', now()->subDays(14))->distinct('enrollments.user_id')->count('enrollments.user_id'),
                $lowPassQuizCount,
            ],
        ]);
    }

    public function downloadAnalyticsReport(Request $request)
    {
        $data = $this->report($request)->getData();
        $data['generatedAt'] = now();
        $data['preparedBy'] = Auth::user();

        return Pdf::loadView('admin.analytics-report-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('Platform-Analytics-Report-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>, points: string, scale: array{max: int, high: int, mid: int, low: int}}
     */
    private function monthlySeries(string $table, string $dateColumn, ?\Closure $scope = null, bool $distinctUsers = false): array
    {
        $labels = [];
        $values = [];

        for ($month = 8; $month >= 0; $month--) {
            $start = now()->startOfMonth()->subMonths($month);
            $end = $start->copy()->addMonth();
            $labels[] = $start->format('M');
            $query = DB::table($table)->where($dateColumn, '>=', $start)->where($dateColumn, '<', $end);
            if ($scope) {
                $scope($query);
            }
            $values[] = $distinctUsers ? $query->distinct('user_id')->count('user_id') : $query->count();
        }

        $max = max(1, ...$values);
        $points = collect($values)->map(function (int $value, int $index) use ($max): string {
            $x = 48 + $index * 87;
            $y = 177 - (int) round($value / $max * 126);

            return $x.','.$y;
        })->implode(' ');

        return [
            'labels' => $labels,
            'values' => $values,
            'points' => $points,
            'scale' => [
                'max' => $max,
                'high' => (int) round($max * .75),
                'mid' => (int) round($max * .5),
                'low' => (int) round($max * .25),
            ],
        ];
    }

    // ── Shared chart data ─────────────────────────────────────────────────

    /**
     * @return Collection<int, object{label: string, enrollments: int, completion_rate: int}>
     */
    private function courseCharts(): Collection
    {
        $rows = DB::table('enrollments')
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

        return $rows->map(fn ($row) => (object) [
            'label' => Str::limit($row->title, 14, ''),
            'enrollments' => (int) $row->learners,
            'completion_rate' => (int) round($row->avg_progress),
        ])->values();
    }

    // ══════════════════════════════════════════════════════════════════
    public function academicCreditRecognitionRequests()
    {
        $recognitions = AcademicCreditRecognition::with(['user', 'framework'])
            ->orderByDesc('updated_at')
            ->get();
        $eligibleReports = UserStackingProgress::with(['user', 'framework'])
            ->where('status', 'requirements_met')
            ->orderByDesc('requirements_met_at')
            ->get();

        return view('admin.academic-credit-recognition', [
            'user' => UserPresenter::admin(Auth::user()),
            'recognitions' => $recognitions,
            'eligibleReports' => $eligibleReports,
        ]);
    }

    public function creditEvidenceReport(int $userId, int $frameworkId, CreditEvidenceReportService $reports)
    {
        $student = User::query()->where('role_id', User::ROLE_STUDENT)->findOrFail($userId);
        $framework = StackingFramework::query()->findOrFail($frameworkId);

        return view('credit-evidence-report', $reports->build($student, $framework));
    }

    // PROGRAM CATEGORIES  (Admin › Management › Program Categories)
    // ══════════════════════════════════════════════════════════════════

    /** List every category with the number of courses using it. */
    public function programCategories()
    {
        $categories = CourseCategory::orderBy('sort_order')->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $categories->getCollection()->transform(function (CourseCategory $c) {
            $c->courses_using = $c->courseCount();

            return $c;
        });

        return view('admin.program-categories', [
            'user' => UserPresenter::admin(Auth::user()),
            'categories' => $categories,
            'totalCategories' => $categories->total(),
        ]);
    }

    public function storeProgramCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:course_categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        CourseCategory::create([
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => (int) CourseCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category added.');
    }

    public function updateProgramCategory(Request $request, int $id)
    {
        $category = CourseCategory::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:course_categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $previousName = $category->name;
        $newName = trim($data['name']);

        $category->update([
            'name' => $newName,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // courses.category holds the label, so a rename has to carry across
        // or those courses would silently fall out of the category.
        if ($previousName !== $newName) {
            Course::where('category', $previousName)->update(['category' => $newName]);
        }

        return back()->with('success', 'Category updated.');
    }

    public function destroyProgramCategory(int $id)
    {
        $category = CourseCategory::findOrFail($id);

        // Courses keep their label; they just stop matching a live category.
        // Warn rather than block, so an admin can retire an old programme.
        $inUse = $category->courseCount();

        $category->delete();

        $message = $inUse > 0
            ? "Category deleted. {$inUse} course(s) still carry this label — edit them to reassign."
            : 'Category deleted.';

        return back()->with('success', $message);
    }

    /** Show/hide a category on the faculty course form without deleting it. */
    public function toggleProgramCategory(int $id)
    {
        $category = CourseCategory::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        return back()->with('success', $category->is_active
            ? 'Category is now available to faculty.'
            : 'Category hidden from the course form.');
    }

    public function stackingFrameworks()
    {
        return view('admin.stacking-frameworks', [
            'user' => UserPresenter::admin(Auth::user()),
            'frameworks' => StackingFramework::with(['requirements.course', 'approver'])
                ->withCount('requirements')
                ->orderBy('name')
                ->get(),
            'courses' => $this->microcredentialCourses()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeStackingFramework(Request $request)
    {
        $data = $this->validateStackingFramework($request);
        $requirements = $this->validateStackingRequirements($data['requirements'] ?? []);
        $this->validateCompletionRule($data['completion_mode'], $data['required_count'] ?? null, $requirements);

        DB::transaction(function () use ($data, $requirements): void {
            $framework = StackingFramework::create([
                'name' => trim($data['name']),
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'approving_academic_unit' => $data['approving_academic_unit'] ?? null,
                'target_recognition' => $data['target_recognition'] ?? null,
                'recognition_target_type' => $data['recognition_target_type'],
                'completion_mode' => $data['completion_mode'],
                'required_count' => $this->resolveRequiredCount($data['completion_mode'], $requirements, $data['required_count'] ?? null),
                'cumulative_outcomes' => $this->normalizeCumulativeOutcomes($data['cumulative_outcomes'] ?? null),
                'equivalent_course' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['equivalent_course'] ?? null) : null,
                'equivalent_units' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['equivalent_units'] ?? null) : null,
                'sequence_required' => $data['sequence_required'] ?? false,
                'credit_recognition_conditions' => $data['credit_recognition_conditions'] ?? null,
                'pqf_level' => $data['pqf_level'] ?? null,
                'credit_equivalency' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['credit_equivalency'] ?? null) : null,
                'is_active' => false,
            ]);

            $this->replaceStackingRequirements($framework, $requirements);
        });

        return redirect()->route('admin.stacking-frameworks')->with('success', 'Stacking framework saved as a draft.');
    }

    public function updateStackingFramework(Request $request, int $id)
    {
        $framework = StackingFramework::findOrFail($id);

        if ($framework->status === 'approved') {
            return redirect()->route('admin.stacking-frameworks')
                ->withErrors(['framework' => 'Deactivate an approved framework before editing its structure.']);
        }

        $data = $this->validateStackingFramework($request);
        $requirements = $request->has('requirements')
            ? $this->validateStackingRequirements($data['requirements'] ?? [])
            : $framework->requirements->map(fn (StackingFrameworkRequirement $requirement): array => [
                'course_id' => $requirement->course_id,
                'order' => $requirement->order,
                'is_required' => $requirement->is_required,
            ])->all();
        $this->validateCompletionRule($data['completion_mode'], $data['required_count'] ?? null, $requirements);

        DB::transaction(function () use ($data, $framework, $requirements): void {
            $framework->update([
                'name' => trim($data['name']),
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'approving_academic_unit' => $data['approving_academic_unit'] ?? null,
                'target_recognition' => $data['target_recognition'] ?? null,
                'recognition_target_type' => $data['recognition_target_type'],
                'completion_mode' => $data['completion_mode'],
                'required_count' => $this->resolveRequiredCount($data['completion_mode'], $requirements, $data['required_count'] ?? null),
                'cumulative_outcomes' => $this->normalizeCumulativeOutcomes($data['cumulative_outcomes'] ?? null),
                'equivalent_course' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['equivalent_course'] ?? null) : null,
                'equivalent_units' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['equivalent_units'] ?? null) : null,
                'sequence_required' => $data['sequence_required'] ?? false,
                'credit_recognition_conditions' => $data['credit_recognition_conditions'] ?? null,
                'pqf_level' => $data['pqf_level'] ?? null,
                'credit_equivalency' => $data['recognition_target_type'] === 'course_equivalency' ? ($data['credit_equivalency'] ?? null) : null,
                'is_active' => false,
            ]);

            $this->replaceStackingRequirements($framework, $requirements);
        });

        return redirect()->route('admin.stacking-frameworks')->with('success', 'Stacking framework updated as a draft.');
    }

    public function submitStackingFramework(int $id)
    {
        $framework = StackingFramework::with('requirements')->findOrFail($id);
        $this->validateFrameworkForApproval($framework);

        $framework->update(['status' => 'pending_approval', 'is_active' => false]);

        return redirect()->route('admin.stacking-frameworks')->with('success', 'Stacking framework submitted for approval.');
    }

    public function approveStackingFramework(int $id)
    {
        $framework = StackingFramework::with('requirements')->findOrFail($id);

        if (! in_array($framework->status, ['draft', 'pending_approval', 'inactive'], true)) {
            return redirect()->route('admin.stacking-frameworks')
                ->withErrors(['framework' => 'Only draft, submitted, or inactive stacking frameworks can be activated.']);
        }

        $this->validateFrameworkForApproval($framework);

        $framework->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'is_active' => true,
        ]);

        return redirect()->route('admin.stacking-frameworks')->with('success', 'Stacking framework approved and activated.');
    }

    public function deactivateStackingFramework(int $id)
    {
        $framework = StackingFramework::findOrFail($id);
        $framework->update(['status' => 'inactive', 'is_active' => false]);

        return redirect()->route('admin.stacking-frameworks')->with('success', 'Stacking framework deactivated.');
    }

    private function validateStackingFramework(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'pqf_level' => ['nullable', 'regex:/^[1-8]$/'],
            'cumulative_outcomes' => ['nullable', 'string', 'max:4000'],
            'target_recognition' => ['nullable', 'string', 'max:255'],
            'recognition_target_type' => ['required', 'in:course_equivalency,certificate,specialization,degree_requirement,other_institutional_recognition'],
            'equivalent_course' => ['nullable', 'string', 'max:255'],
            'equivalent_units' => ['nullable', 'numeric', 'min:0'],
            'credit_equivalency' => ['nullable', 'string', 'max:1000'],
            'credit_recognition_conditions' => ['nullable', 'string', 'max:2000'],
            'approving_academic_unit' => ['nullable', 'string', 'max:255'],
            'completion_mode' => ['required', 'in:all_required,minimum_required'],
            'required_count' => ['nullable', 'integer', 'min:1'],
            'sequence_required' => ['nullable', 'boolean'],
            'requirements' => ['nullable', 'array'],
            'requirements.*.id' => ['nullable', 'integer'],
            'requirements.*.course_id' => ['required', 'integer', 'exists:courses,id'],
            'requirements.*.order' => ['required', 'integer', 'min:1'],
            'requirements.*.is_required' => ['nullable', 'boolean'],
        ]);
    }

    /** @return list<array{course_id: int, order: int, is_required: bool}> */
    private function validateStackingRequirements(array $requirements): array
    {
        $normalized = collect($requirements)->map(fn (array $requirement): array => [
            'course_id' => (int) $requirement['course_id'],
            'order' => (int) $requirement['order'],
            'is_required' => (bool) ($requirement['is_required'] ?? false),
        ])->values()->all();

        $courseIds = collect($normalized)->pluck('course_id');
        if ($courseIds->isEmpty()) {
            return $normalized;
        }

        if ($courseIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['requirements' => 'A course may appear only once in a framework.']);
        }

        $validCourseIds = $this->microcredentialCourses()
            ->whereIn('id', $courseIds->all())
            ->pluck('id');
        if ($validCourseIds->count() !== $courseIds->count()) {
            throw ValidationException::withMessages(['requirements' => 'Every requirement must be an approved, published microcredential course.']);
        }

        $orders = collect($normalized)->pluck('order')->sort()->values()->all();
        if ($orders !== range(1, count($orders))) {
            throw ValidationException::withMessages(['requirements' => 'Requirement order must be unique and sequential starting at 1.']);
        }

        return $normalized;
    }

    private function validateCompletionRule(string $completionMode, $requiredCount, array $requirements, bool $forApproval = false): void
    {
        $requiredItems = collect($requirements)->where('is_required', true)->count();

        if ($requiredItems === 0) {
            throw ValidationException::withMessages([
                'requirements' => 'Add at least one microcredential marked as required for completion.',
            ]);
        }

        if ($completionMode === 'all_required') {
            return;
        }

        $count = (int) ($requiredCount ?? 0);
        if ($count < 1 || $count > $requiredItems) {
            throw ValidationException::withMessages([
                'required_count' => 'The minimum required count must be between 1 and the number of required microcredentials.',
            ]);
        }
    }

    private function resolveRequiredCount(string $completionMode, array $requirements, $requestedCount): int
    {
        $requiredItems = collect($requirements)->where('is_required', true)->count();

        return $completionMode === 'all_required'
            ? $requiredItems
            : (int) $requestedCount;
    }

    /** @return list<string> */
    private function normalizeCumulativeOutcomes(?string $outcomes): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $outcomes) ?: [])
            ->map(fn (string $outcome): string => trim(preg_replace('/^[-•*]\s*/', '', $outcome) ?? $outcome))
            ->filter(fn (string $outcome): bool => $outcome !== '')
            ->values()
            ->all();
    }

    private function validateFrameworkForApproval(StackingFramework $framework): void
    {
        $requirements = $framework->requirements->map(fn (StackingFrameworkRequirement $requirement): array => [
            'course_id' => $requirement->course_id,
            'order' => $requirement->order,
            'is_required' => $requirement->is_required,
        ])->all();

        $requirements = $this->validateStackingRequirements($requirements);
        $this->validateCompletionRule((string) $framework->completion_mode, $framework->required_count, $requirements, true);
    }

    /** @param list<array{course_id: int, order: int, is_required: bool}> $requirements */
    private function replaceStackingRequirements(StackingFramework $framework, array $requirements): void
    {
        $framework->requirements()->delete();
        foreach ($requirements as $requirement) {
            $framework->requirements()->create($requirement);
        }
    }

    private function microcredentialCourses()
    {
        return Course::query()
            ->where('is_published', true)
            ->where(function ($query): void {
                $query->where('approval_status', 'approved')
                    ->orWhere('is_approved', true);
            })
            ->where(function ($query): void {
                $query->whereNotNull('badge_id')
                    ->orWhere('certificate_enabled', true)
                    ->orWhereNotNull('pqf_level')
                    ->orWhereNotNull('mastery_passing_percent')
                    ->orWhereHas('learningOutcomes')
                    ->orWhere('requires_faculty_verification', true)
                    ->orWhere('requires_academic_unit_confirmation', true);
            });
    }

    public function certificates(Request $request)
    {
        $credentialType = $request->query('type', 'certificates');
        abort_unless(in_array($credentialType, ['certificates', 'badges'], true), 404);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');
        abort_unless(in_array($status, ['all', 'active', 'revoked'], true), 404);

        $credentialsQuery = $credentialType === 'badges'
            ? UserBadge::query()->with(['user', 'badge', 'revoker'])
            : Certificate::query()->with(['user', 'course', 'revoker']);

        $credentialsQuery
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($credentialType, $search): void {
                $query->where(function ($query) use ($credentialType, $search): void {
                    $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));

                    if ($credentialType === 'badges') {
                        $query->orWhere('credential_uid', 'like', '%'.$search.'%')
                            ->orWhereHas('badge', fn ($badgeQuery) => $badgeQuery->where('name', 'like', '%'.$search.'%'));

                        return;
                    }

                    $query->orWhere('serial', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('microcredential_title_snapshot', 'like', '%'.$search.'%')
                        ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('title', 'like', '%'.$search.'%'));
                });
            });

        $credentials = $credentialsQuery
            ->when($credentialType === 'badges', fn ($query) => $query->orderByDesc('earned_at'), fn ($query) => $query->orderByDesc('issued_at'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.certificates', [
            'user' => UserPresenter::admin(Auth::user()),
            'credentialType' => $credentialType,
            'credentials' => $credentials,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function revokeCertificate(Request $request, int $id, UserNotificationService $userNotifications)
    {
        $request->merge([
            'revocation_reason' => trim((string) $request->input('revocation_reason', '')),
        ]);

        $data = $request->validate([
            'revocation_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $certificate = Certificate::findOrFail($id);
        $pdfPath = DB::transaction(function () use ($id, $data): string {
            $certificate = Certificate::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($certificate->status === 'revoked') {
                throw ValidationException::withMessages([
                    'certificate' => 'This certificate has already been revoked.',
                ]);
            }

            $certificate->status = 'revoked';
            $certificate->revoked_at = now();
            $certificate->revoked_by = Auth::id();
            $certificate->revocation_reason = trim($data['revocation_reason']);
            // Remove the pre-revocation PDF from the public disk. The next
            // authenticated download regenerates it with the revoked mark.
            $pdfPath = 'certificates/'.$certificate->serial.'.pdf';
            $certificate->file_path = null;
            $certificate->save();

            return $pdfPath;
        });
        Storage::disk('public')->delete($pdfPath);
        $userNotifications->createForUser(
            $certificate->user_id,
            'Certificate revoked',
            'Your certificate for "'.($certificate->microcredential_title_snapshot ?: $certificate->title ?: 'a course').'" has been revoked. Contact your institution for more information.',
            'certificate',
            'certificate',
            $certificate->id,
        );

        return back()->with('success', 'Certificate revoked.');
    }

    public function revokeBadge(Request $request, int $id, UserNotificationService $userNotifications)
    {
        $request->merge([
            'revocation_reason' => trim((string) $request->input('revocation_reason', '')),
        ]);

        $data = $request->validate([
            'revocation_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($id, $data): void {
            $badge = UserBadge::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($badge->status === 'revoked') {
                throw ValidationException::withMessages([
                    'credential' => 'This badge has already been revoked.',
                ]);
            }

            $badge->status = 'revoked';
            $badge->revoked_at = now();
            $badge->revoked_by = Auth::id();
            $badge->revocation_reason = trim($data['revocation_reason']);
            $badge->save();
        });
        $badge = UserBadge::with('badge')->findOrFail($id);
        $userNotifications->createForUser(
            $badge->user_id,
            'Badge revoked',
            'Your "'.($badge->badge?->name ?? 'badge').'" badge has been revoked. Contact your institution for more information.',
            'badge',
            'badge',
            $badge->id,
        );

        return back()->with('success', 'Badge revoked.');
    }

    public function pathways()
    {
        return view('admin.pathways', [
            'user' => UserPresenter::admin(Auth::user()),
            'pathways' => Pathway::with('courses')->orderBy('name')->paginate(20)->withQueryString(),
            'courses' => Course::where('is_published', true)->orderBy('title')->get(),
        ]);
    }

    public function storePathway(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:pathways,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'distinct', 'exists:courses,id'],
        ]);
        $pathway = Pathway::create([
            'name' => trim($data['name']), 'description' => $data['description'] ?? null, 'is_active' => true,
        ]);
        $this->syncPathwayCourses($pathway, $data['course_ids'] ?? []);

        return back()->with('success', 'Pathway created.');
    }

    public function updatePathway(Request $request, int $id)
    {
        $pathway = Pathway::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:pathways,name,'.$pathway->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'distinct', 'exists:courses,id'],
        ]);
        $pathway->update([
            'name' => trim($data['name']), 'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);
        $this->syncPathwayCourses($pathway, $data['course_ids'] ?? []);

        return back()->with('success', 'Pathway updated.');
    }

    public function destroyPathway(int $id)
    {
        $pathway = Pathway::findOrFail($id);
        User::where('pathway_id', $pathway->id)->update(['pathway_id' => null]);
        $pathway->delete();

        return back()->with('success', 'Pathway deleted.');
    }

    /** Persist courses in the exact sequence selected by the administrator. */
    private function syncPathwayCourses(Pathway $pathway, array $courseIds): void
    {
        $orderedCourses = [];
        foreach (array_values($courseIds) as $index => $courseId) {
            $orderedCourses[(int) $courseId] = ['order' => $index + 1];
        }

        $pathway->courses()->sync($orderedCourses);
    }
}
