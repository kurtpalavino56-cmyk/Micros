<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Announcement;
use App\Models\Certificate;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\MicrocredentialCompletionService;
use App\Support\CertificateBuilder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PageController — public / shared pages: Homepage, the Students
 * directory, the notifications feed, global search, and the JSON
 * endpoint that powers the live-monitoring widgets.
 */
class PageController extends Controller
{
    /**
     * Public landing page (all of its content lives in the Blade itself).
     */
    public function homepage(Request $request)
    {
        // A scanned certificate QR lands here as /?certificate=SERIAL. The
        // homepage renders normally and floats the certificate over it, so
        // the visitor sees the real site rather than a bare document.
        $scanned = null;
        if ($serial = $request->query('certificate')) {
            $scanned = $this->lookupCertificate($serial);
        }

        // Homepage cards — fed from the database. Only admin-approved,
        // published courses appear; "Feature on Homepage" controls the
        // Featured strip, latest published fill the Latest strip.
        $cards = fn ($courses) => $courses->map(fn (Course $c) => [
            'title' => $c->title,
            'description' => Str::limit((string) $c->description, 110),
            'professor' => $c->instructor ?? 'Faculty',
            'hours' => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
            'rating' => 0,
            'category' => $c->category ?? 'General',
            'level' => $c->level ?? 'Beginner',
            'image' => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
            'slug' => route('public.courses.show', $c->id),
        ])->values()->all();

        // Featured means EXACTLY that: only courses an admin has explicitly
        // starred with "Feature on Homepage".
        //
        // This used to top the list up to three by concatenating unfeatured
        // courses whenever fewer than three were starred, so every approved
        // course appeared on the homepage on its own — which looked like the
        // feature toggle switching itself on. The homepage already has a
        // separate "Latest Courses" section for new arrivals, so there is no
        // need to pad this one.
        $featured = Course::where('is_published', true)->where('is_approved', true)
            ->where('is_featured', true)
            ->latest('approved_at')->limit(3)->get();

        // ── Hero counters — real, live site-wide numbers ─────────────
        $stats = [
            'courses' => Course::where('is_published', true)->where('is_approved', true)->count(),
            // Same rule as the Student Directory: every active student
            // account. Deactivated accounts are excluded, but a student who
            // has not joined a course yet still counts.
            'learners' => User::where('role_id', User::ROLE_STUDENT)
                ->where('is_active', true)
                ->count(),
            'certificates' => Certificate::count(),
            'badges' => UserBadge::count(),
        ];

        // ── Announcements — real site activity (new courses, enrollment
        //    milestones, recent badges) plus any published announcements ──
        $announcements = $this->buildAnnouncements();

        return view('public.home', [
            'scannedCertificate' => $scanned,
            'featuredCourses' => $cards($featured),
            'latestCourses' => $cards(
                Course::where('is_published', true)->where('is_approved', true)->latest('created_at')->limit(3)->get()
            ),
            'stats' => $stats,
            'announcements' => $announcements,
        ]);
    }

    /**
     * Build the homepage announcement feed from real site activity:
     * published admin announcements first, then auto-generated items for
     * newly published courses, this month's enrollment count, and the
     * latest badge awards.
     */
    /**
     * Admin announcements as notification entries, filtered to the audience
     * the admin chose (student / faculty). Guests see only announcements
     * addressed to everyone.
     *
     * Unread state uses the same notifications_read_at watermark as the rest
     * of the live feed, so the page's "Mark all as read" clears these too.
     */
    private function announcementFeed($auth)
    {
        $role = $auth ? $auth->roleName() : null;

        return Announcement::with('author')
            ->where('is_published', true)
            ->latest('published_at')
            ->get()
            ->filter(function (Announcement $a) use ($role) {
                $audience = $a->audience;

                // No audience recorded (older rows) or both roles = everyone.
                if (empty($audience) || count($audience) >= 2) {
                    return true;
                }

                return $role !== null && in_array($role, $audience, true);
            })
            ->take(5)
            ->map(function (Announcement $a) use ($auth) {
                $postedAt = $a->published_at ?? $a->created_at;

                return (object) [
                    'title' => $a->title,
                    'message' => $a->body,
                    'time' => $postedAt?->diffForHumans() ?? '',
                    'type' => 'announcement',
                    'unread' => $this->feedIsUnread($auth, $postedAt),
                    'url' => null,
                ];
            })
            ->values();
    }

    private function buildAnnouncements()
    {
        $items = collect();

        // 1) Manually published announcements (if any exist).
        //    Each announcement carries an audience (student / faculty), set by
        //    the admin on Admin › Announcements. Guests on the public homepage
        //    see only announcements addressed to everyone.
        $viewerRole = Auth::check() ? Auth::user()->roleName() : null;

        $manual = Announcement::where('is_published', true)
            ->latest('published_at')
            ->get()
            ->filter(function (Announcement $a) use ($viewerRole) {
                $audience = $a->audience;

                // Rows created before the audience column existed, or aimed at
                // both roles, are public.
                if (empty($audience) || count($audience) >= 2) {
                    return true;
                }

                // Targeted announcements are only for that role — a guest on
                // the public homepage is not one of them.
                return $viewerRole !== null && in_array($viewerRole, $audience, true);
            })
            ->take(3)
            ->map(fn (Announcement $a) => [
                'type' => 'general',
                'label' => 'Announcement',
                'date' => ($a->published_at ?? $a->created_at)?->format('F j, Y') ?? '',
                'title' => $a->title,
                'desc' => Str::limit((string) $a->body, 160),
            ])
            ->values();
        $items = $items->concat($manual);

        // New-course activity is intentionally NOT surfaced here. It is a
        // per-student notification (see the "Student / guest" branch of
        // notificationsFeed()/notificationPreview() below), not a public
        // landing-page announcement — a course going live shouldn't be
        // broadcast to guests, faculty, or the admin dashboard.

        // 2) Enrollment activity this month.
        $monthEnroll = Enrollment::where('created_at', '>=', now()->startOfMonth())->count();
        if ($monthEnroll > 0) {
            $items->push([
                'type' => 'event',
                'label' => 'Enrollment',
                'date' => now()->format('F Y'),
                'title' => $monthEnroll.' new '.Str::plural('enrollment', $monthEnroll).' this month',
                'desc' => 'Students are actively enrolling across our microcredential courses this month.',
            ]);
        }

        // 3) Latest badge award.
        $latestBadge = UserBadge::with('badge')->latest('earned_at')->first();
        if ($latestBadge) {
            $items->push([
                'type' => 'general',
                'label' => 'Achievement',
                'date' => $latestBadge->earned_at?->format('F j, Y') ?? '',
                'title' => 'Badge Awarded: '.($latestBadge->badge->name ?? 'Badge'),
                'desc' => 'A learner just earned the "'.($latestBadge->badge->name ?? 'Badge').'" badge.',
            ]);
        }

        return $items->take(5)->values();
    }

    /**
     * Help Center — a public contact form. Anyone can use it, signed in or
     * not; a visitor supplies their name and email so the admin can reply by
     * mail, while a signed-in student's reply lands in their inbox.
     */
    public function help()
    {
        return view('public.help', [
            'auth' => Auth::user(),
        ]);
    }

    /**
     * Store a Help Center message. Attachments are optional and limited to
     * images so the admin can preview them inline.
     */
    public function storeHelpMessage(Request $request)
    {
        $auth = Auth::user();

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],  // 10 MB
        ];

        // A visitor has no account, so we need somewhere to send the reply.
        if (! $auth) {
            $rules['guest_name'] = ['required', 'string', 'max:120'];
            $rules['guest_email'] = ['required', 'email', 'max:190'];
        }

        $data = $request->validate($rules, [
            'attachment.mimes' => 'The attachment must be an image (JPG, PNG, GIF or WEBP).',
            'attachment.max' => 'The image may not be larger than 10 MB.',
        ]);

        $attachment = $this->storeComplaintAttachment($request);

        $complaint = Complaint::create([
            'user_id' => $auth?->id,
            'source' => $auth ? 'student' : 'visitor',
            'guest_name' => $auth ? null : $data['guest_name'],
            'guest_email' => $auth ? null : $data['guest_email'],
            'subject' => $data['subject'],
            'message' => trim($data['message']),
            'attachment_url' => $attachment['url'],
            'attachment_name' => $attachment['name'],
            'category' => 'general',
            'status' => 'open',
            'last_reply_at' => now(),
            'student_read_at' => $auth ? now() : null,
        ]);

        // A signed-in student can follow the thread in their inbox.
        if ($auth && $auth->isStudent()) {
            return redirect()->route('inbox.index', ['thread' => $complaint->id])
                ->with('success', 'Your message was sent to the administrators.');
        }

        return redirect()->route('help')
            ->with('success', 'Thanks! Your message was sent. We will reply to '
                .($auth?->email ?? $data['guest_email']).'.');
    }

    /**
     * Move an uploaded Help Center image into public/uploads/complaints.
     *
     * @return array{url: ?string, name: ?string}
     */
    private function storeComplaintAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment') || ! $request->file('attachment')->isValid()) {
            return ['url' => null, 'name' => null];
        }

        $file = $request->file('attachment');

        $dir = public_path('uploads/complaints');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $name = uniqid('help_').'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($dir, $name);

        return [
            'url' => 'uploads/complaints/'.$name,
            'name' => $file->getClientOriginalName(),
        ];
    }

    /** Navbar section link → smooth-scroll target on the homepage. */
    public function announcementsRedirect()
    {
        return redirect('/#announcements');
    }

    /** Navbar section link → smooth-scroll target on the homepage. */
    public function microcredentialsRedirect()
    {
        return redirect('/#featured');
    }

    /**
     * Public catalog — every published course ("View all Courses").
     */
    public function explore(Request $request)
    {
        $category = trim((string) $request->query('category', '')) ?: null;

        // Paginated: the catalog previously loaded EVERY published course into
        // memory (and rendered them all) on each visit — fine at 10 courses,
        // a real problem at 500.
        $cards = Course::where('is_published', true)->where('is_approved', true)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $cards->setCollection($cards->getCollection()->map(fn (Course $c) => [
                'title' => $c->title,
                'description' => Str::limit((string) $c->description, 110),
                'professor' => $c->instructor ?? 'Faculty',
                'hours' => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
                'rating' => 0,
                'category' => $c->category ?? 'General',
                'level' => $c->level ?? 'Beginner',
                'image' => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
                'slug' => route('public.courses.show', $c->id),
            ])->values());

        return view('public.explore-courses', [
            'courses' => $cards,
            'category' => $category,
            'categories' => Course::where('is_published', true)->where('is_approved', true)
                ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all(),
        ]);
    }

    /** Public description page; enrollment remains protected behind login. */
    public function publicCourse(int $id)
    {
        $course = Course::with(['modules.lessons', 'quizzes.questions', 'creator'])
            ->where('is_published', true)
            ->where('is_approved', true)
            ->findOrFail($id);

        return view('public.course-detail', [
            'course' => $course,
            'loginUrl' => route('login', ['redirect' => route('courses.show', $course->id)]),
        ]);
    }

    /**
     * Students directory — real students (role_id = 3) with the courses
     * each one is enrolled in, replacing the Blade's built-in dummy list.
     */
    public function students()
    {
        $students = User::query()
            ->where('role_id', User::ROLE_STUDENT)
            ->where('is_active', true)
            // Every active student account appears, whether or not they have
            // joined a course yet. Their course list simply shows as empty.
            ->with(['enrollments.course'])
            ->orderBy('first_name')
            ->get();

        // N+1 fix: certificate serials were looked up one query PER completed
        // enrollment PER student. Fetch every certificate for the listed
        // students in a single query and key it by "user_id-course_id".
        $serials = Certificate::query()
            ->whereIn('user_id', $students->pluck('id')->all() ?: [0])
            ->get(['user_id', 'course_id', 'serial'])
            ->keyBy(fn (Certificate $c) => $c->user_id.'-'.$c->course_id);

        $students = $students
            ->map(function (User $u) use ($serials) {
                return (object) [
                    'name' => $u->name,
                    'student_id' => $u->student_id ?? $u->user_code,
                    'avatar_url' => $u->avatar_url,
                    // A completed course carries its certificate serial so
                    // the QR can encode a real verification URL. Courses
                    // still in progress carry none, and the Blade shows no
                    // QR for them.
                    'courses' => $u->enrollments->map(function ($e) use ($u, $serials) {
                        $serial = null;

                        if ($e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED) {
                            $serial = $serials->get($u->id.'-'.$e->course_id)?->serial;
                        }

                        return [
                            'title' => $e->course->title ?? 'Course',
                            'completed' => $e->completion_status === MicrocredentialCompletionService::STATUS_COMPLETED,
                            'verify_url' => $serial
                                ? CertificateBuilder::verifyUrl($serial)
                                : null,
                        ];
                    })->values()->all(),
                ];
            });

        return view('public.students', ['students' => $students]);
    }

    /**
     * Notifications feed — pulled from the notifications table for the
     * signed-in user; falls back to the sample items for guests so the
     * page always renders.
     */
    public function notifications(Request $request)
    {
        $auth = Auth::user();

        // 0) Announcements the admin addressed to this viewer's role. These
        //    always appear, whether or not the user has stored notifications,
        //    which is why they are built before the early return below.
        $announcements = $this->announcementFeed($auth);

        // 1) Stored notifications for this user (highest priority).
        if ($auth) {
            $stored = Notification::query()
                ->where('user_id', $auth->id)
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (Notification $n) => (object) [
                    'title' => $n->title,
                    'message' => $n->message,
                    'time' => $n->created_at?->diffForHumans() ?? '',
                    'type' => $n->type,
                    'unread' => ! $n->is_read,
                    'url' => $this->notificationUrl($auth, $n->type),
                ]);
            if ($stored->isNotEmpty()) {
                return view('public.notifications', [
                    // Announcements first — they are the admin talking to
                    // everyone, not per-user activity.
                    'notifications' => $announcements->concat($stored)->values(),
                    'filter' => (string) $request->query('filter', 'all'),
                ]);
            }
        }

        // 2) Otherwise build a live activity feed so the bell is never empty.
        $notifications = collect($announcements);

        if ($auth && (int) $auth->role_id === User::ROLE_FACULTY) {
            // Faculty: what is happening with THEIR courses.
            $courseIds = Course::where('created_by', $auth->id)->pluck('id');

            Enrollment::with(['user', 'course'])->whereIn('course_id', $courseIds)
                ->latest()->limit(5)->get()
                ->each(fn ($e) => $notifications->push((object) [
                    'title' => 'New student enrolled',
                    'message' => ($e->user->name ?? 'A student').' enrolled in "'.($e->course->title ?? 'your course').'".',
                    'time' => $e->created_at?->diffForHumans() ?? '',
                    'type' => 'enrollment',
                    'unread' => $this->feedIsUnread($auth, $e->created_at),
                    'url' => $this->notificationUrl($auth, 'enrollment', $e->course_id),
                ]));

            UserBadge::with(['user', 'badge'])->whereHas('user.enrollments', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->latest('earned_at')->limit(3)->get()
                ->each(fn ($ub) => $notifications->push((object) [
                    'title' => 'Badge awarded',
                    'message' => ($ub->user->name ?? 'A student').' earned the "'.($ub->badge->name ?? 'Badge').'" badge.',
                    'time' => $ub->earned_at?->diffForHumans() ?? '',
                    'type' => 'badge',
                    'unread' => $this->feedIsUnread($auth, $ub->earned_at),
                    'url' => $this->notificationUrl($auth, 'badge'),
                ]));
        } else {
            // Student / guest: their own badges + new courses on the site.
            if ($auth) {
                UserBadge::with('badge')->where('user_id', $auth->id)
                    ->latest('earned_at')->limit(4)->get()
                    ->each(fn ($ub) => $notifications->push((object) [
                        'title' => 'Badge earned',
                        'message' => 'You earned the "'.($ub->badge->name ?? 'Badge').'" badge. Congratulations!',
                        'time' => $ub->earned_at?->diffForHumans() ?? '',
                        'type' => 'badge',
                        'unread' => $this->feedIsUnread($auth, $ub->earned_at),
                        'url' => $this->notificationUrl($auth, 'badge'),
                    ]));
            }

            Course::where('is_published', true)->where('is_approved', true)->latest('approved_at')->limit(3)->get()
                ->each(fn ($c) => $notifications->push((object) [
                    'title' => 'New course available',
                    'message' => '"'.$c->title.'" was just published and is open for enrollment.',
                    'time' => ($c->approved_at ?? $c->created_at)?->diffForHumans() ?? '',
                    'type' => 'course',
                    'unread' => $this->feedIsUnread($auth, $c->approved_at ?? $c->created_at),
                    'url' => $this->notificationUrl($auth, 'course', $c->id),
                ]));
        }

        if ($notifications->isEmpty()) {
            $notifications->push((object) [
                'title' => 'All caught up',
                'message' => 'There is no new activity right now. Check back later for updates.',
                'time' => '',
                'type' => 'system',
                'unread' => false,
                'url' => null,
            ]);
        }

        return view('public.notifications', [
            // take() is generous enough to keep the announcements that were
            // seeded at the front of the collection.
            'notifications' => $notifications->take(15)->values(),
            'filter' => (string) $request->query('filter', 'all'),
        ]);
    }

    /** Return the compact notification feed used by the authenticated bell. */
    public function notificationPreview(): JsonResponse
    {
        $auth = Auth::user();
        $announcements = $this->announcementFeed($auth);
        $stored = Notification::query()
            ->where('user_id', $auth->id)
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Notification $notification) => [
                'title' => $notification->title,
                'message' => $notification->message,
                'time' => $notification->created_at?->diffForHumans() ?? '',
                'unread' => ! $notification->is_read,
            ]);

        $items = $announcements->take(4)->map(fn ($notification) => [
            'title' => $notification->title,
            'message' => $notification->message,
            'time' => $notification->time ?? '',
            'unread' => (bool) ($notification->unread ?? false),
        ])->concat($stored)->take(8)->values();

        return response()->json(['notifications' => $items]);
    }

    /**
     * Live-feed entries have no is_read column of their own, so they count as
     * unread only when they happened after the user last cleared everything.
     */
    private function feedIsUnread(?User $auth, $occurredAt): bool
    {
        if (! $auth || ! $occurredAt) {
            return true;
        }

        $readAt = $auth->notifications_read_at;

        return ! $readAt || $occurredAt->gt($readAt);
    }

    /**
     * Mark everything as read: the stored notifications, and — via the
     * watermark — every live-feed entry up to this moment.
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $auth = Auth::user();
        $auth->notifications_read_at = now();
        $auth->save();

        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Resolve a notification's "View" target.
     *
     * The notifications page is shared by every role, but most destination
     * routes sit behind RoleBasedAccess — sending a faculty member to
     * courses.show would just bounce them back to their own dashboard.
     * So the target is picked per role, and deep-linked with $entityId
     * whenever the caller knows which record the notification is about.
     *
     * Returns null when there is nothing useful to open (e.g. the
     * "All caught up" placeholder), and the Blade hides the link.
     */
    private function notificationUrl(?User $auth, ?string $type, ?int $entityId = null): ?string
    {
        // Guests can't enter any role-guarded area; send them somewhere public.
        if (! $auth) {
            return route('explore');
        }

        $type = (string) $type;

        if ((int) $auth->role_id === User::ROLE_FACULTY) {
            return match ($type) {
                'enrollment' => route('faculty.students'),
                'badge' => route('faculty.students'),
                'course' => $entityId
                    ? route('faculty.courses.manage', $entityId)
                    : route('faculty.courses'),
                'system' => null,
                'announcement' => route('faculty.dashboard'),
                default => route('faculty.dashboard'),
            };
        }

        if ((int) $auth->role_id === User::ROLE_ADMIN) {
            return match ($type) {
                'course' => $entityId
                    ? route('admin.courses.show', $entityId)
                    : route('admin.courses'),
                'enrollment' => route('admin.usermanagement'),
                'system' => null,
                'announcement' => route('admin.dashboard'),
                default => route('admin.dashboard'),
            };
        }

        // Student.
        return match ($type) {
            'course' => $entityId
                ? route('courses.show', $entityId)
                : route('courses.browse'),
            'badge' => route('badges.index'),
            'enrollment' => route('courses.enrolled'),
            'certificate' => route('certificates.index'),
            'system' => null,
            'announcement' => route('dashboard'),
            default => route('dashboard'),
        };
    }

    /**
     * Global navbar search → forwards to the course browser.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        return redirect()->route('courses.browse', $query ? ['q' => $query] : []);
    }

    public function forgotPassword()
    {
        return view('auth.login');
    }

    /**
     * Live-monitoring JSON feed used by the admin/faculty dashboards.
     */
    public function monitoringLive()
    {
        $activeUsers = (int) DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->distinct()
            ->count('user_id');

        $today = Carbon::today();

        $stats = [
            'active_users' => $activeUsers,
            'events_today' => AnalyticsEvent::whereDate('occurred_at', $today)->count(),
            'enrollments_today' => DB::table('enrollments')->whereDate('enrolled_at', $today)->count(),
            'badges_today' => DB::table('user_badges')->whereDate('earned_at', $today)->count(),
        ];

        $labels = [
            'enrollment' => 'New student enrolled',
            'badge_issued' => 'Badge issued',
            'course_completed' => 'Course completed',
            'lesson_completed' => 'Lesson completed',
            'quiz_passed' => 'Quiz passed',
        ];

        // Build the live feed from REAL activity so it always updates:
        // recent enrollments, badge awards, and course completions — merged
        // with any recorded analytics events, newest first.
        $feed = collect();

        Enrollment::with(['user', 'course'])->latest()->limit(6)->get()
            ->each(fn ($e) => $feed->push([
                'title' => 'New student enrolled',
                'detail' => ($e->user->name ?? 'A student').' · '.($e->course->title ?? 'a course'),
                'time' => ($e->enrolled_at ?? $e->created_at)?->diffForHumans() ?? '',
                'ts' => ($e->enrolled_at ?? $e->created_at)?->getTimestamp() ?? 0,
            ]));

        UserBadge::with(['user', 'badge'])->latest('earned_at')->limit(6)->get()
            ->each(fn ($ub) => $feed->push([
                'title' => 'Badge issued',
                'detail' => ($ub->user->name ?? 'A student').' earned the '.($ub->badge->name ?? 'Badge').' badge',
                'time' => $ub->earned_at?->diffForHumans() ?? '',
                'ts' => $ub->earned_at?->getTimestamp() ?? 0,
            ]));

        Enrollment::with(['user', 'course'])
            ->where('completion_status', MicrocredentialCompletionService::STATUS_COMPLETED)
            ->latest('updated_at')->limit(4)->get()
            ->each(fn ($e) => $feed->push([
                'title' => 'Course completed',
                'detail' => ($e->user->name ?? 'A student').' completed '.($e->course->title ?? 'a course'),
                'time' => $e->updated_at?->diffForHumans() ?? '',
                'ts' => $e->updated_at?->getTimestamp() ?? 0,
            ]));

        AnalyticsEvent::query()->with('user')->latest('occurred_at')->limit(6)->get()
            ->each(function (AnalyticsEvent $event) use ($labels, $feed) {
                $actor = $event->user->name ?? 'A user';
                $feed->push([
                    'title' => $labels[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type)),
                    'detail' => $event->metadata['detail'] ?? ($actor.' · '.str_replace('_', ' ', $event->event_type)),
                    'time' => $event->occurred_at?->diffForHumans() ?? '',
                    'ts' => $event->occurred_at?->getTimestamp() ?? 0,
                ]);
            });

        $activity = $feed->sortByDesc('ts')->take(8)->values()
            ->map(fn ($row) => ['title' => $row['title'], 'detail' => $row['detail'], 'time' => $row['time']])
            ->all();

        // Same zero-inclusive badge counters the dashboard renders, so the
        // 15-second polling can refresh the Recent Badges panel live.
        $recentBadges = DB::table('badges')
            ->leftJoin('user_badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.name', DB::raw('COUNT(user_badges.id) as earned_count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderByDesc('earned_count')
            ->orderBy('badges.name')
            ->limit(4)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values()
            ->all();

        return response()->json(compact('stats', 'activity', 'recentBadges'));
    }

    /**
     * Look up a certificate by its public serial and build the data the
     * floating card renders. Returns null for anything unknown, so a
     * mistyped or forged code simply shows the homepage.
     *
     * Phase 3, Step 5: switched from CertificateBuilder::data($course, ...)
     * to CertificateBuilder::pdfData($certificate), so verification is
     * built from the certificate's OWN immutable snapshot columns
     * (title/outcomes/competencies/PQF/credit equivalency/hours) rather
     * than the live, possibly-since-edited course — satisfying "keep
     * verification based on the certificate's stable serial/credential
     * data, not the current course version." This is a data-source fix
     * inside the existing verification method; no route was added or
     * changed.
     */
    protected function lookupCertificate(string $serial): ?array
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('serial', $serial)
            ->first();

        if (! $certificate || ! $certificate->course) {
            return null;
        }

        return CertificateBuilder::pdfData($certificate);
    }

    /** /verify/{serial} — kept as a friendly alias for the QR target. */
    public function verifyCertificate(string $serial)
    {
        return redirect()->route('Homepage', ['certificate' => $serial]);
    }
}
