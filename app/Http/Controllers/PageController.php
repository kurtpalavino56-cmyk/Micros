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
use App\Services\UserNotificationService;
use App\Support\CertificateBuilder;
use App\Support\RichTextSanitizer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * PageController — public / shared pages: Homepage, credential lookup,
 * the notifications feed, global search, and the JSON
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
            'description' => filled($c->short_description)
                ? $c->short_description
                : Str::limit(trim(html_entity_decode(strip_tags((string) $c->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 110),
            'professor' => $c->instructor ?? 'Faculty',
            'hours' => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
            'rating' => (float) ($c->reviews_avg_rating ?? 0),
            'review_count' => (int) ($c->reviews_count ?? 0),
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
            ->withAvg('reviews', 'rating')->withCount('reviews')
            ->latest('approved_at')->limit(3)->get();

        // ── Hero counters — real, live site-wide numbers ─────────────
        $stats = [
            'courses' => Course::where('is_published', true)->where('is_approved', true)->count(),
            // Count every active student account, including students who
            // have not enrolled in a course yet.
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
                Course::where('is_published', true)->where('is_approved', true)->withAvg('reviews', 'rating')->withCount('reviews')->latest('created_at')->limit(3)->get()
            ),
            'stats' => $stats,
            'announcements' => $announcements,
        ]);
    }

    /** Build the homepage feed from published, audience-visible announcements. */
    private function buildAnnouncements()
    {
        // 1) Manually published announcements (if any exist).
        //    Each announcement carries an audience (student / faculty), set by
        //    the admin on Admin › Announcements. Guests on the public homepage
        //    see only announcements addressed to everyone.
        $viewerRole = Auth::check() ? Auth::user()->roleName() : null;

        $manual = Announcement::where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->get()
            ->filter(function (Announcement $a) use ($viewerRole) {
                $audience = $a->audience;

                // Rows created before the audience column existed or marked
                // Public are visible to everyone.
                if (empty($audience) || in_array('public', $audience, true)) {
                    return true;
                }

                // Targeted announcements are only for that role — a guest on
                // the public homepage is not one of them.
                return $viewerRole !== null && in_array($viewerRole, $audience, true);
            })
            ->take(3)
            ->map(function (Announcement $a): array {
                $plainBody = trim(html_entity_decode(strip_tags(preg_replace('/<\/(?:p|h[1-6]|li|blockquote)>/i', ' ', (string) $a->body) ?? '')));

                return [
                    'id' => $a->id,
                    'type' => 'general',
                    'label' => 'Announcement',
                    'date' => ($a->published_at ?? $a->created_at)?->format('F j, Y') ?? '',
                    'title' => $a->title,
                    'desc' => Str::limit($plainBody, 160),
                    'full_body' => RichTextSanitizer::sanitize((string) $a->body),
                    'has_more' => Str::length($plainBody) > 160,
                ];
            })
            ->values();

        return $manual;
    }

    /**
     * Help Center — a public contact form. Anyone can use it, signed in or
     * not; a visitor supplies their name and email so the admin can reply by
     * mail, while a signed-in student's or faculty member's reply lands in Inbox.
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
            'source' => $auth?->isFaculty() ? 'faculty' : ($auth ? 'student' : 'visitor'),
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

        // Signed-in learners and faculty can follow their thread in Inbox.
        if ($auth && $auth->isStudent()) {
            return redirect()->route('inbox.index', ['thread' => $complaint->id])
                ->with('success', 'Your message was sent to the administrators.');
        }

        if ($auth && $auth->isFaculty()) {
            return redirect()->route('faculty.inbox', ['thread' => $complaint->id])
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

    /** Serve a support-thread attachment only to its sender or an administrator. */
    public function complaintAttachment(int $id): BinaryFileResponse
    {
        $complaint = Complaint::findOrFail($id);
        $viewer = Auth::user();

        abort_unless($viewer->isAdmin() || (int) $complaint->user_id === (int) $viewer->id, 403);

        $filename = basename((string) $complaint->attachment_url);
        abort_if($filename === '' || $filename === '.', 404);

        $path = public_path('uploads/complaints/'.$filename);
        abort_unless(is_file($path), 404);

        return response()->file($path);
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
            ->withAvg('reviews', 'rating')->withCount('reviews')
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $cards->setCollection($cards->getCollection()->map(fn (Course $c) => [
            'title' => $c->title,
            'description' => filled($c->short_description)
                ? $c->short_description
                : Str::limit(trim(html_entity_decode(strip_tags((string) $c->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 110),
            'professor' => $c->instructor ?? 'Faculty',
            'hours' => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
            'rating' => (float) ($c->reviews_avg_rating ?? 0),
            'review_count' => (int) ($c->reviews_count ?? 0),
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
        $course = Course::with(['modules.lessons', 'quizzes.questions', 'creator', 'learningOutcomes', 'badge'])
            ->where('is_published', true)
            ->where('is_approved', true)
            ->findOrFail($id);

        return view('public.course-detail', [
            'course' => $course,
            'loginUrl' => route('login', ['redirect' => route('courses.show', $course->id)]),
        ]);
    }

    /** Public certificate lookup by the credential ID printed on each certificate. */
    public function students(Request $request)
    {
        $credentialId = strtoupper(trim((string) $request->query('credential_id', '')));
        $credential = null;

        if ($credentialId !== '') {
            $certificate = Certificate::with(['user', 'course'])
                ->where('serial', $credentialId)
                ->first();

            if ($certificate && $certificate->course) {
                $credential = CertificateBuilder::pdfData($certificate);
                $credential['credential_id'] = $credential['serial'];
                $credential['credential_title'] = $credential['course_title'];
                $credential['issued_at'] = $credential['date_completed'];
            }
        }

        return view('public.students', [
            'credentialId' => $credentialId,
            'credential' => $credential,
            'hasSearched' => $credentialId !== '',
        ]);
    }

    /** Show the shared, role-filtered notification feed. */
    public function notifications(Request $request, UserNotificationService $userNotifications)
    {
        $auth = Auth::user();

        return view('public.notifications', [
            'notifications' => $userNotifications->feed($auth),
            'filter' => (string) $request->query('filter', 'all'),
        ]);
    }

    /** Return the compact notification feed used by the authenticated bell. */
    public function notificationPreview(UserNotificationService $userNotifications): JsonResponse
    {
        return response()->json([
            'notifications' => $userNotifications->feed(Auth::user(), 8)
                ->map(fn (object $notification): array => [
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->time,
                    'unread' => $notification->unread,
                    'url' => $notification->url,
                ])
                ->take(8)
                ->values(),
        ]);
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
