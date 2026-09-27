<?php

namespace App\Http\Controllers;

use App\Actions\Announcements\CreateAnnouncement;
use App\Actions\Announcements\DeleteAnnouncement;
use App\Actions\Announcements\UpdateAnnouncement;
use App\Mail\ComplaintReplyMail;
use App\Models\AcademicCreditRecognition;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\FacultyCode;
use App\Models\Pathway;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\User;
use App\Services\AcademicCreditRecognitionService;
use App\Services\AdminUserManagementService;
use App\Services\CourseModerationService;
use App\Services\MicrocredentialCompletionService;
use App\Services\UserCodeService;
use App\Support\CertificateBuilder;
use App\Support\CompletionStatusPresenter;
use App\Support\UserPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * AdminController — the whole Admin_* surface (dashboard, profile, user
 * management, courses & badges, analytics report) fed from the database.
 */
class AdminController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_students' => User::where('role_id', User::ROLE_STUDENT)->count(),
            'badges_issued' => DB::table('user_badges')->count(),
            'course_score_avg' => round((float) Course::avg('passing_score'), 1),
            'students_enrolled' => DB::table('enrollments')->count(),
        ];

        [$enrollmentByCourse, $completionRate] = $this->courseCharts();

        $activeCourseModels = Course::query()
            ->where('approval_status', '!=', 'draft')   // drafts are faculty-private
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(4)
            ->get();

        // N+1 fix: average progress was queried per course. One grouped
        // query covers all four rows.
        $avgProgressByCourse = DB::table('enrollments')
            ->whereIn('course_id', $activeCourseModels->pluck('id')->all() ?: [0])
            ->select('course_id', DB::raw('AVG(progress_percent) as avg_progress'))
            ->groupBy('course_id')
            ->pluck('avg_progress', 'course_id');

        $activeCourses = $activeCourseModels
            ->map(fn (Course $c) => (object) [
                'title' => $c->title,
                'meta' => $c->enrollments_count.' Students · 1 Faculty',
                'thumbnail_url' => $c->thumbnail_url,
                'percent' => (int) round((float) ($avgProgressByCourse[$c->id] ?? 0)),
            ])
            ->values();

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
            'activeCourses' => $activeCourses,
            'recentBadges' => $recentBadges,
            'enrollmentByCourse' => $enrollmentByCourse,
            'completionRate' => $completionRate,
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
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$auth->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:80'],
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
        $auth->email = $data['email'];
        $auth->phone = $data['phone'] ?? null;
        $auth->location = $data['location'] ?? null;
        $auth->about = $data['about'] ?? null;
        $auth->bio = $data['bio'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }

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

    public function userManagement(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $roleTab = $request->input('role', 'students');   // students | faculty

        $usersQuery = User::query()
            ->when($roleTab === 'faculty', fn ($q) => $q->where('role_id', User::ROLE_FACULTY))
            ->when($roleTab === 'students', fn ($q) => $q->where('role_id', User::ROLE_STUDENT))
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
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role_id', 'user_code', 'is_active', 'created_at'])
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $query,
            'sort' => $sort,
            'direction' => $direction,
            'roleTab' => $roleTab,
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

    public function storeUser(Request $request, AdminUserManagementService $userManagement, UserCodeService $userCodes)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $userManagement->create($validated, $userCodes);

        return redirect()->route('admin.usermanagement')->with('success', 'User created successfully.');
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
     * Admin > Announcements. Each announcement carries an audience so it can
     * be aimed at students, faculty, or both.
     */
    public function announcements()
    {
        $announcements = Announcement::with('author')
            ->latest()
            ->get()
            ->map(fn (Announcement $a) => (object) [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'audience' => $a->audience ?? ['student', 'faculty'],
                'is_published' => (bool) $a->is_published,
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
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'array', 'min:1'],
            'audience.*' => ['in:student,faculty'],
        ], [
            'audience.required' => 'Choose who can see this announcement.',
        ]);

        $createAnnouncement->execute($data, Auth::user());

        return redirect()->route('admin.announcements')
            ->with('success', 'Announcement posted.');
    }

    public function updateAnnouncement(Request $request, int $id, UpdateAnnouncement $updateAnnouncement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'array', 'min:1'],
            'audience.*' => ['in:student,faculty'],
        ]);

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
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $complaint = Complaint::findOrFail($id);

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'body' => trim($data['body']),
            'is_admin' => true,
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
                    replyBody: trim($data['body']),
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

        return back()->with('success', 'Complaint marked as '.$complaint->status.'.');
    }

    // ── Faculty Codes ─────────────────────────────────────────────────────

    /**
     * Admin › Faculty Codes — lists every shareable faculty registration
     * code with a green (available) / red (used) indicator.
     */
    public function facultyCodes()
    {
        $codes = FacultyCode::with('usedBy')
            ->latest()
            ->get()
            ->map(fn (FacultyCode $c) => (object) [
                'id' => $c->id,
                'code' => $c->code,
                'is_used' => $c->isUsed(),
                'used_by_name' => $c->usedBy?->name,
                'used_at' => $c->used_at,
                'created_at' => $c->created_at,
            ])
            ->values();

        return view('admin.faculty-codes', compact('codes'));
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
                'is_featured' => (bool) $c->is_featured,
                // "Subject is Related on" — faculty/admin only, never shown
                // to students. Lets an admin see what a submitted course
                // will be recommended against before approving it.
                'related_skills' => FacultyController::relatedSkillsOf($c),
            ])
            ->values();

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Admin › Course Detail — full information about a single course,
     * reached by clicking its card on the Courses & Badges page.
     */
    public function showCourse(int $id)
    {
        $c = Course::with(['badge', 'creator', 'modules.lessons', 'modules.quiz.questions'])
            ->withCount('enrollments')
            ->findOrFail($id);

        $course = (object) [
            'id' => $c->id,
            'title' => $c->title,
            'description' => $c->description,
            'category' => $c->category,
            'program' => $c->program,
            'term' => $c->term,
            'level' => $c->level,
            'duration' => $c->duration,
            'instructor' => $c->creator->name ?? $c->instructor,
            'skills' => $c->skills ?? [],
            // Internal recommendation tags — admin/faculty only.
            'related_skills' => FacultyController::relatedSkillsOf($c),
            'objectives' => $c->objectives ?? [],
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
            'lessons' => $m->lessons->map(fn ($l) => (object) [
                'title' => $l->title,
                'type' => $l->type,
                'duration' => $l->duration,
                'content' => $l->content,
                'file_url' => ! empty($l->file_url) ? asset($l->file_url) : null,
                'file_name' => ! empty($l->file_url) ? basename($l->file_url) : null,
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

        // Per-enrollment institutional completion review (Phase 3, Step 11).
        // Additive to the existing aggregate stats above — $course's
        // 'students'/'percent' fields are unchanged.
        $enrollments = Enrollment::with(['user', 'facultyVerifier', 'academicUnitConfirmer'])
            ->where('course_id', $c->id)
            ->latest('enrolled_at')
            ->get()
            ->map(fn (Enrollment $e) => (object) [
                'id' => $e->id,
                'student_name' => $e->user->name ?? 'Student',
                'completion_status' => $e->completion_status,
                'faculty_verification_status' => $e->faculty_verification_status,
                'academic_unit_confirmation_status' => $e->academic_unit_confirmation_status,
                'completed_at' => $e->completed_at,
                'faculty_verified_by' => $e->facultyVerifier->name ?? null,
                'academic_unit_confirmed_by' => $e->academicUnitConfirmer->name ?? null,
                // Confirm/Reject only makes sense once mastery has been
                // reached and the enrollment is genuinely awaiting this
                // institutional step — mirrors evaluate()'s own gate
                // rather than introducing a new rule in the view.
                // Mirrors the server-side rule in MicrocredentialCompletionService:
                // a decision is only accepted once the enrollment is awaiting
                // institutional sign-off, so don't offer the buttons earlier.
                'academic_confirmation_actionable' => $e->academic_unit_confirmation_status === 'pending'
                    && $e->completion_status === MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION,
                'institutional_label' => CompletionStatusPresenter::institutionalLabel(
                    $e->completion_status,
                    $e->faculty_verification_status,
                    $e->academic_unit_confirmation_status,
                ),
            ])
            ->values();

        return view('admin.courses.show', compact('course', 'modules', 'enrollments'));
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
    public function academicConfirmEnrollment(Request $request, int $id, Enrollment $enrollment, MicrocredentialCompletionService $completionService)
    {
        abort_if($enrollment->course_id !== $id, 404);

        $data = $request->validate([
            'decision' => 'required|string|in:confirmed,rejected',
        ]);

        try {
            $completionService->recordAcademicUnitConfirmation($enrollment, Auth::id(), $data['decision']);
        } catch (\DomainException $e) {
            // The enrollment is not awaiting institutional sign-off (not
            // yet at mastery, or already completed/rejected/revoked).
            // Enforced by the service; the Blade button is only a courtesy.
            abort(422, $e->getMessage());
        }

        return back()->with('success', $data['decision'] === 'confirmed'
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
        $moderation->approve($course, (int) Auth::id());

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
        $published = $moderation->togglePublish($course, (int) Auth::id());

        return back()->with('success', '"'.$course->title.'" is now '
            .($published ? 'published.' : 'unpublished — hidden from students.'));
    }

    /**
     * Feature / unfeature a course on the homepage — admin-only.
     */
    public function toggleFeatureCourse(int $id, CourseModerationService $moderation)
    {
        $course = Course::findOrFail($id);
        $featured = $moderation->toggleFeature($course);

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
        $periodStart = match ($period) {
            'year' => now()->startOfYear(),
            'month' => now()->startOfMonth(),
            'week' => now()->startOfWeek(),
            default => null,
        };
        $period = in_array($period, ['all', 'year', 'month', 'week'], true) ? $period : 'all';
        $activityMode = in_array($activityMode, ['enrollments', 'active', 'completions'], true) ? $activityMode : 'enrollments';
        $filters = [
            'category' => $request->query('category'),
            'department' => $request->query('department'),
            'course' => $request->query('course'),
            'faculty' => $request->query('faculty'),
        ];
        $applyCourseFilters = function ($query) use ($filters): void {
            $query->when($filters['category'], fn ($builder, $value) => $builder->where('courses.category', $value))
                ->when($filters['department'], fn ($builder, $value) => $builder->where('courses.program', $value))
                ->when($filters['course'], fn ($builder, $value) => $builder->where('courses.id', $value))
                ->when($filters['faculty'], fn ($builder, $value) => $builder->where('courses.created_by', $value));
        };
        $enrollmentTable = DB::table('enrollments')->join('courses', 'courses.id', '=', 'enrollments.course_id');
        $applyEnrollmentFilters = function ($query) use ($applyCourseFilters, $periodStart): void {
            $applyCourseFilters($query);
            if ($periodStart) {
                $query->where('enrollments.enrolled_at', '>=', $periodStart);
            }
        };
        $applyEnrollmentFilters($enrollmentTable);
        $quizScores = DB::table('quiz_attempts')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
            ->whereNotNull('quiz_attempts.score');
        $applyCourseFilters($quizScores);
        if ($periodStart) {
            $quizScores->where('quiz_attempts.created_at', '>=', $periodStart);
        }
        $certificateQuery = DB::table('certificates')->join('courses', 'courses.id', '=', 'certificates.course_id');
        $applyCourseFilters($certificateQuery);
        if ($periodStart) {
            $certificateQuery->where('certificates.issued_at', '>=', $periodStart);
        }

        $stats = [
            'total_students' => User::where('role_id', $studentRole)
                ->whereIn('id', (clone $enrollmentTable)->select('enrollments.user_id')->distinct())
                ->count(),
            'active_students' => (clone $enrollmentTable)
                ->join('users', 'users.id', '=', 'enrollments.user_id')
                ->where('users.role_id', $studentRole)
                ->where('enrollments.updated_at', '>=', $periodStart ?: now()->subDays(30))
                ->distinct('enrollments.user_id')
                ->count('enrollments.user_id'),
            'total_courses' => Course::query()->tap($applyCourseFilters)->count(),
            'completions' => (clone $enrollmentTable)
                ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->count(),
            'credentials' => (clone $certificateQuery)->where('certificates.status', 'active')->count(),
            'badges_issued' => DB::table('user_badges')->where('user_badges.status', 'active')->when($periodStart, fn ($query) => $query->where('user_badges.earned_at', '>=', $periodStart))->count(),
            'faculty_total' => User::where('role_id', User::ROLE_FACULTY)->count(),
            'course_score_avg' => round((float) $quizScores->avg('score'), 1),
            'average_completion' => round((float) (clone $enrollmentTable)->where('enrollments.completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)->avg('enrollments.progress_percent')),
        ];

        $coursePerformanceQuery = Course::query()
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoinSub(
                DB::table('quiz_attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->select('quizzes.course_id', DB::raw('AVG(quiz_attempts.score) as average_score'))
                    ->whereNotNull('quiz_attempts.score')
                    ->groupBy('quizzes.course_id'),
                'course_scores',
                'course_scores.course_id',
                '=',
                'courses.id'
            )
            ->when($filters['category'], fn ($query, $value) => $query->where('courses.category', $value))
            ->when($filters['department'], fn ($query, $value) => $query->where('courses.program', $value))
            ->when($filters['course'], fn ($query, $value) => $query->where('courses.id', $value))
            ->when($filters['faculty'], fn ($query, $value) => $query->where('courses.created_by', $value))
            ->when($periodStart, fn ($query) => $query->where('enrollments.enrolled_at', '>=', $periodStart))
            ->select(
                'courses.id',
                'courses.title',
                DB::raw('COUNT(enrollments.id) as enrolled'),
                DB::raw('COALESCE(AVG(enrollments.progress_percent), 0) as completion'),
                DB::raw('COALESCE(course_scores.average_score, 0) as average_score')
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
                'score' => (int) round($course->average_score),
            ]);

        $progressCounts = [
            'completed' => (clone $enrollmentTable)
                ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->count(),
            'in_progress' => (clone $enrollmentTable)
                ->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->whereBetween('progress_percent', [1, 100])
                ->count(),
            'not_started' => (clone $enrollmentTable)
                ->where('completion_status', '!=', MicrocredentialCompletionService::STATUS_COMPLETED)
                ->where('progress_percent', 0)
                ->count(),
        ];
        $progressTotal = max(1, array_sum($progressCounts));
        $progress = collect($progressCounts)->map(fn (int $count) => (int) round($count / $progressTotal * 100));

        $activityScope = function ($query) use ($applyEnrollmentFilters, $activityMode): void {
            $query->join('courses', 'courses.id', '=', 'enrollments.course_id');
            $applyEnrollmentFilters($query);
            if ($activityMode === 'completions') {
                $query->where('enrollments.completion_status', MicrocredentialCompletionService::STATUS_COMPLETED);
            }
        };
        $activity = $this->monthlySeries('enrollments', 'enrolled_at', $activityScope, $activityMode === 'active');
        $credentialActivity = $this->monthlySeries('certificates', 'issued_at', function ($query) use ($applyCourseFilters): void {
            $query->join('courses', 'courses.id', '=', 'certificates.course_id');
            $applyCourseFilters($query);
            $query->where('certificates.status', 'active');
        });
        $assessmentScores = (clone $quizScores)->pluck('score');
        $assessmentCount = DB::table('assessments')->count() + $assessmentScores->count();
        $passRate = $assessmentScores->count() > 0
            ? (int) round($assessmentScores->filter(fn ($score) => $score >= 70)->count() / $assessmentScores->count() * 100)
            : 0;
        $competencies = DB::table('competency_progresses')
            ->join('competency_units', 'competency_units.id', '=', 'competency_progresses.competency_unit_id')
            ->join('competency_categories', 'competency_categories.id', '=', 'competency_units.competency_category_id')
            ->select('competency_categories.name', DB::raw('AVG(competency_progresses.mastery_score) as mastery'))
            ->groupBy('competency_categories.id', 'competency_categories.name')
            ->orderByDesc('mastery')
            ->limit(4)
            ->get()
            ->map(fn ($competency) => (object) ['name' => $competency->name, 'mastery' => (int) round($competency->mastery)]);
        $courseOptionQuery = Course::query()
            ->when($filters['category'], fn ($query, $value) => $query->where('category', $value))
            ->when($filters['department'], fn ($query, $value) => $query->where('program', $value))
            ->when($filters['faculty'], fn ($query, $value) => $query->where('created_by', $value));

        return view('admin.analytics-report', [
            'stats' => $stats,
            'coursePerformance' => $coursePerformance,
            'progress' => $progress,
            'activity' => $activity,
            'credentialActivity' => $credentialActivity,
            'assessment' => [
                'total' => $assessmentCount,
                'average' => (int) round((float) $assessmentScores->avg()),
                'pass_rate' => $passRate,
                'highest' => (int) ($assessmentScores->max() ?? 0),
                'lowest' => (int) ($assessmentScores->min() ?? 0),
            ],
            'competencies' => $competencies,
            'categoryNames' => CourseCategory::activeNames(),
            'departmentNames' => Course::whereNotNull('program')->where('program', '!=', '')->distinct()->orderBy('program')->pluck('program'),
            'courseOptions' => $courseOptionQuery->orderBy('title')->get(['id', 'title']),
            'facultyOptions' => User::where('role_id', User::ROLE_FACULTY)->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'filters' => $filters + ['period' => $period, 'activity' => $activityMode],
            'period' => $period,
            'activityMode' => $activityMode,
            'credentialMetrics' => [
                'certificates' => (clone $certificateQuery)->where('certificates.status', 'active')->count(),
                'badges' => DB::table('user_badges')->where('user_badges.status', 'active')->when($periodStart, fn ($query) => $query->where('user_badges.earned_at', '>=', $periodStart))->count(),
                'verified' => (clone $certificateQuery)->whereNotNull('certificates.serial')->count(),
                'pending' => DB::table('assessments')->where('status', 'pending')->count(),
            ],
            'totalCompetencies' => DB::table('competency_units')->count(),
            'attention' => [
                $coursePerformance->filter(fn ($course) => $course->completion < 50)->count(),
                $competencies->filter(fn ($competency) => $competency->mastery < 40)->count(),
                (clone $enrollmentTable)->where('enrollments.updated_at', '<', now()->subDays(14))->distinct('enrollments.user_id')->count('enrollments.user_id'),
                DB::table('quiz_attempts')->where('score', '<', 60)->distinct('quiz_id')->count('quiz_id'),
            ],
        ]);
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
     * @return array{0: Collection, 1: Collection}
     */
    private function courseCharts(): array
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

        $max = max(1, (int) $rows->max('learners'));

        $enrollmentByCourse = $rows->map(fn ($row) => (object) [
            'label' => Str::limit($row->title, 14, ''),
            'value' => (int) $row->learners,
            'percent' => (int) round($row->learners / $max * 100),
        ])->values();

        $completionRate = $rows->map(fn ($row) => (object) [
            'label' => Str::limit($row->title, 14, ''),
            'value' => (int) round($row->avg_progress),
            'percent' => (int) round($row->avg_progress),
        ])->values();

        return [$enrollmentByCourse, $completionRate];
    }

    // ══════════════════════════════════════════════════════════════════
    public function academicCreditRecognitionRequests()
    {
        $recognitions = AcademicCreditRecognition::with(['user', 'framework'])
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.academic-credit-recognition', [
            'user' => UserPresenter::admin(Auth::user()),
            'recognitions' => $recognitions,
        ]);
    }

    public function recommendAcademicCreditRecognition(int $id)
    {
        $service = app(AcademicCreditRecognitionService::class);
        $record = $service->recommend($id, Auth::id(), 'Recommended for academic credit.');

        return redirect()->route('admin.academic-credit-recognition')->with('success', 'Academic credit recognition recommended.');
    }

    public function endorseAcademicCreditRecognition(int $id)
    {
        $service = app(AcademicCreditRecognitionService::class);
        $service->endorse($id, Auth::id(), 'Dean endorsed academic credit recognition.');

        return redirect()->route('admin.academic-credit-recognition')->with('success', 'Academic credit recognition endorsed.');
    }

    public function recordAcademicCreditRecognition(int $id)
    {
        $service = app(AcademicCreditRecognitionService::class);
        $service->recordRegistrar($id, Auth::id(), 'Recorded in registrar.');

        return redirect()->route('admin.academic-credit-recognition')->with('success', 'Academic credit recognition recorded in registrar.');
    }

    public function denyAcademicCreditRecognition(int $id)
    {
        $service = app(AcademicCreditRecognitionService::class);
        $service->deny($id, Auth::id(), 'Denied by institutional review.');

        return redirect()->route('admin.academic-credit-recognition')->with('success', 'Academic credit recognition denied.');
    }

    // PROGRAM CATEGORIES  (Admin › Management › Program Categories)
    // ══════════════════════════════════════════════════════════════════

    /** List every category with the number of courses using it. */
    public function programCategories()
    {
        $categories = CourseCategory::orderBy('sort_order')->orderBy('name')->get()
            ->map(function (CourseCategory $c) {
                $c->courses_using = $c->courseCount();

                return $c;
            });

        return view('admin.program-categories', [
            'user' => UserPresenter::admin(Auth::user()),
            'categories' => $categories,
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
                'completion_mode' => $data['completion_mode'],
                'required_count' => $this->resolveRequiredCount($data['completion_mode'], $requirements, $data['required_count'] ?? null),
                'cumulative_outcomes' => $this->normalizeCumulativeOutcomes($data['cumulative_outcomes'] ?? null),
                'equivalent_course' => $data['equivalent_course'] ?? null,
                'equivalent_units' => $data['equivalent_units'] ?? null,
                'sequence_required' => $data['sequence_required'] ?? false,
                'credit_recognition_conditions' => $data['credit_recognition_conditions'] ?? null,
                'pqf_level' => $data['pqf_level'] ?? null,
                'credit_equivalency' => $data['credit_equivalency'] ?? null,
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
                'completion_mode' => $data['completion_mode'],
                'required_count' => $this->resolveRequiredCount($data['completion_mode'], $requirements, $data['required_count'] ?? null),
                'cumulative_outcomes' => $this->normalizeCumulativeOutcomes($data['cumulative_outcomes'] ?? null),
                'equivalent_course' => $data['equivalent_course'] ?? null,
                'equivalent_units' => $data['equivalent_units'] ?? null,
                'sequence_required' => $data['sequence_required'] ?? false,
                'credit_recognition_conditions' => $data['credit_recognition_conditions'] ?? null,
                'pqf_level' => $data['pqf_level'] ?? null,
                'credit_equivalency' => $data['credit_equivalency'] ?? null,
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

    public function pathways()
    {
        return view('admin.pathways', [
            'user' => UserPresenter::admin(Auth::user()),
            'pathways' => Pathway::with('courses')->orderBy('name')->get(),
            'courses' => Course::where('is_published', true)->orderBy('title')->get(),
        ]);
    }

    public function storePathway(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:pathways,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);
        $pathway = Pathway::create([
            'name' => trim($data['name']), 'description' => $data['description'] ?? null, 'is_active' => true,
        ]);
        $pathway->courses()->sync($data['course_ids'] ?? []);

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
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);
        $pathway->update([
            'name' => trim($data['name']), 'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);
        $pathway->courses()->sync($data['course_ids'] ?? []);

        return back()->with('success', 'Pathway updated.');
    }

    public function destroyPathway(int $id)
    {
        $pathway = Pathway::findOrFail($id);
        User::where('pathway_id', $pathway->id)->update(['pathway_id' => null]);
        $pathway->delete();

        return back()->with('success', 'Pathway deleted.');
    }
}
