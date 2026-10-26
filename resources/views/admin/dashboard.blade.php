
<x-admin.layout title="Upskill – Admin Dashboard" page="admin-dashboard">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>
<div class="layout">
        <main class="main admin-dashboard">

            <div class="page-top">
                <div>
                    <h1 class="page-heading">Welcome back, Admin</h1>
                    <p class="page-sub">Here's what's happening on your platform today.</p>
                </div>
                <span class="today-chip">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
                    {{ now()->format('l, F j') }}
                </span>
            </div>

            {{-- STAT CARDS --}}
            <div class="stats-grid">
                @if ($dashboardAccess['user-management'])
                <div class="stat-card">
                    <div class="stat-heading">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3.5 20v-1.5A4.5 4.5 0 0 1 8 14h2a4.5 4.5 0 0 1 4.5 4.5V20zM16 5.5a3 3 0 0 1 0 5.8M17 14h1a3 3 0 0 1 3 3v3h-4"/></svg></span>
                        <div class="stat-lbl">Total learners</div>
                    </div>
                    <div class="stat-val">{{ number_format($stats['total_students']) }}</div>
                    @php($trend = $stats['learners_weekly_change'])
                    <div class="stat-footer"><div class="stat-context"><span class="stat-trend {{ $trend > 0 ? 'stat-trend-up' : ($trend < 0 ? 'stat-trend-down' : 'stat-trend-neutral') }}">@if($trend !== 0)<svg viewBox="0 0 16 16" aria-hidden="true"><path d="{{ $trend > 0 ? 'M8 13V3m-4 4 4-4 4 4' : 'M8 3v10m-4-4 4 4 4-4' }}"/></svg>@endif{{ $trend > 0 ? '+' : '' }}{{ number_format($trend) }}</span> vs last week</div></div>
                </div>
                @endif

                @if ($dashboardAccess['courses'])
                <div class="stat-card">
                    <div class="stat-heading">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v13A2.5 2.5 0 0 1 6.5 16H20"/></svg></span>
                        <div class="stat-lbl">Published courses</div>
                    </div>
                    <div class="stat-val">{{ number_format($stats['published_courses']) }}</div>
                    @php($trend = $stats['courses_weekly_change'])
                    <div class="stat-footer"><div class="stat-context"><span class="stat-trend {{ $trend > 0 ? 'stat-trend-up' : ($trend < 0 ? 'stat-trend-down' : 'stat-trend-neutral') }}">@if($trend !== 0)<svg viewBox="0 0 16 16" aria-hidden="true"><path d="{{ $trend > 0 ? 'M8 13V3m-4 4 4-4 4 4' : 'M8 3v10m-4-4 4 4 4-4' }}"/></svg>@endif{{ $trend > 0 ? '+' : '' }}{{ number_format($trend) }}</span> vs last week</div></div>
                </div>
                @endif

                @if ($dashboardAccess['courses'] || $dashboardAccess['completion-verification'])
                <div class="stat-card">
                    <div class="stat-heading">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                        <div class="stat-lbl">Pending reviews</div>
                    </div>
                    <div class="stat-val">{{ number_format($stats['pending_reviews']) }}</div>
                    @php($trend = $stats['pending_reviews_weekly_change'])
                    <div class="stat-footer"><div class="stat-context"><span class="stat-trend {{ $trend > 0 ? 'stat-trend-up' : ($trend < 0 ? 'stat-trend-down' : 'stat-trend-neutral') }}">@if($trend !== 0)<svg viewBox="0 0 16 16" aria-hidden="true"><path d="{{ $trend > 0 ? 'M8 13V3m-4 4 4-4 4 4' : 'M8 3v10m-4-4 4 4 4-4' }}"/></svg>@endif{{ $trend > 0 ? '+' : '' }}{{ number_format($trend) }}</span> vs last week</div></div>
                </div>
                @endif

                @if ($dashboardAccess['credentials'])
                <div class="stat-card">
                    <div class="stat-heading">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="5"/><path d="m8.5 12-1 9 4.5-2.5 4.5 2.5-1-9M9.5 8l1.6 1.6L14.5 6"/></svg></span>
                        <div class="stat-lbl">Credentials earned</div>
                    </div>
                    <div class="stat-val">{{ number_format($stats['credentials_earned']) }}</div>
                    @php($trend = $stats['credentials_monthly_change'])
                    <div class="stat-footer"><div class="stat-context"><span class="stat-trend {{ $trend > 0 ? 'stat-trend-up' : ($trend < 0 ? 'stat-trend-down' : 'stat-trend-neutral') }}">@if($trend !== 0)<svg viewBox="0 0 16 16" aria-hidden="true"><path d="{{ $trend > 0 ? 'M8 13V3m-4 4 4-4 4 4' : 'M8 3v10m-4-4 4 4 4-4' }}"/></svg>@endif{{ $trend > 0 ? '+' : '' }}{{ number_format($trend) }}</span> vs last month</div></div>
                </div>
                @endif
            </div>

            @php($pendingReviews = $stats['pending_reviews'])
            @if ($dashboardAccess['courses'] || $dashboardAccess['completion-verification'])
            <section class="attention-strip {{ $pendingReviews === 0 ? 'is-clear' : '' }}" aria-labelledby="attention-title">
                <div class="attention-copy">
                    <span class="attention-indicator" aria-hidden="true"></span>
                    <div>
                        <h2 class="attention-title" id="attention-title">Required attention</h2>
                        <p class="attention-message">
                            {{ $pendingReviews > 0 ? $pendingReviews.' items are waiting for review.' : 'You are all caught up on reviews.' }}
                        </p>
                    </div>
                </div>
                <div class="attention-actions">
                    @if ($dashboardAccess['courses'] && $stats['pending_course_reviews'] > 0)
                        <a class="attention-link" href="{{ route('admin.courses') }}">
                            <strong>{{ $stats['pending_course_reviews'] }}</strong> course submissions
                        </a>
                    @endif
                    @if ($dashboardAccess['completion-verification'] && $stats['pending_completion_verifications'] > 0)
                        <a class="attention-link" href="{{ route('admin.enrollments') }}">
                            <strong>{{ $stats['pending_completion_verifications'] }}</strong> completion verifications
                        </a>
                    @endif
                    @if ($pendingReviews === 0)
                        <span class="attention-clear-label">No pending items</span>
                    @endif
                </div>
            </section>
            @endif

            {{-- QUICK ACCESS --}}
            @if (count($quickModules) > 0)
            <section class="quick-modules" aria-labelledby="quick-modules-title">
                <header class="section-hd">
                    <h2 id="quick-modules-title">Quick access</h2>
                    <p>Common administration tasks, with current counts to help you decide where to go next.</p>
                </header>
                <div class="quick-modules-grid">
                    @foreach($quickModules as $index => $module)
                        <article class="quick-module-card">
                            <div class="quick-module-head">
                                <span class="quick-module-mark" aria-hidden="true">
                                    @switch($index)
                                        @case(0)
                                            <svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v13A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h8"/></svg>
                                            @break
                                        @case(1)
                                            <svg viewBox="0 0 24 24"><path d="M12 3 20 6v5c0 5-3.4 8-8 10-4.6-2-8-5-8-10V6z"/><path d="m8.5 12 2.3 2.3 4.8-5"/></svg>
                                            @break
                                        @case(2)
                                            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3.5 20v-1.5A4.5 4.5 0 0 1 8 14h2a4.5 4.5 0 0 1 4.5 4.5V20zM16 5.5a3 3 0 0 1 0 5.8M17 14h1a3 3 0 0 1 3 3v3h-4"/></svg>
                                            @break
                                        @case(3)
                                            <svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h6"/></svg>
                                            @break
                                        @case(4)
                                            <svg viewBox="0 0 24 24"><path d="M4 10v4h4l8 5V5l-8 5zM8 14l2 6h4l-2-4M19 9l2-2M19 15l2 2"/></svg>
                                            @break
                                        @default
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M4 4l2 2M20 4l-2 2"/></svg>
                                    @endswitch
                                </span>
                                <div class="quick-module-copy">
                                    <h3>{{ $module['title'] }}</h3>
                                    <p>{{ $module['description'] }}</p>
                                </div>
                                <div class="quick-module-info">{{ $module['info'] }}</div>
                            </div>
                            <a class="quick-module-link" href="{{ route($module['route']) }}">{{ $module['button'] }}</a>
                            <span class="quick-module-chevron" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></span>
                        </article>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- LIVE MONITORING + PLATFORM SNAPSHOT --}}
            <div class="two-col monitor-grid">
                @if ($dashboardAccess['audit-logs'])
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Live monitoring feed</span>
                        <span class="live-dot">Live</span>
                    </div>
                    <div id="live-activity-list" aria-live="polite"></div>
                </div>
                @endif

                @if ($dashboardAccess['user-management'] || $dashboardAccess['courses'] || $dashboardAccess['credentials'] || $dashboardAccess['reports'] || $dashboardAccess['completion-verification'])
                <div class="card snapshot-card">
                    <div class="card-hd">
                        <span class="card-title">Current platform snapshot</span>
                    </div>

                    <div class="snapshot-metrics">
                        @if ($dashboardAccess['user-management'])
                        <div class="snapshot-metric">
                            <span>Students on platform</span>
                            <strong>{{ $stats['total_students'] }}</strong>
                        </div>
                        @endif
                        @if ($dashboardAccess['courses'])
                        <div class="snapshot-metric">
                            <span>Active courses</span>
                            <strong>{{ $activeCourseCount }}</strong>
                        </div>
                        @endif
                        @if ($dashboardAccess['credentials'])
                        <div class="snapshot-metric">
                            <span>Badges issued</span>
                            <strong>{{ $stats['badges_issued'] }}</strong>
                        </div>
                        @endif
                        @if ($dashboardAccess['reports'])
                        <div class="snapshot-metric">
                            <span>Average quiz score</span>
                            <strong>{{ $stats['course_score_avg'] }}%</strong>
                        </div>
                        @endif
                        @if ($dashboardAccess['completion-verification'])
                        <div class="snapshot-metric">
                            <span>Enrollments</span>
                            <strong>{{ $stats['students_enrolled'] }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- COURSE PERFORMANCE + RECENT BADGES --}}
            <div class="two-col insights-grid">
                @if ($dashboardAccess['courses'])
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Enrollment and completion by course</span>
                    </div>

                    @if ($courseCount > 0)
                        <div class="performance-legend" aria-hidden="true">
                            <span class="performance-legend-item"><span class="performance-legend-swatch"></span>Enrollments</span>
                            <span class="performance-legend-item"><span class="performance-legend-swatch is-completion"></span>Completion rate</span>
                        </div>

                        <svg class="performance-chart" viewBox="0 0 640 250" role="img" aria-labelledby="performance-chart-title performance-chart-description">
                            <title id="performance-chart-title">Enrollments and completion rate by course</title>
                            <desc id="performance-chart-description">Grouped bars compare enrollment counts using the left axis and average completion percentages using the right axis.</desc>
                            @foreach ($chartTicks as $tick)
                                <line class="performance-gridline" x1="62" y1="{{ $tick->y }}" x2="578" y2="{{ $tick->y }}" />
                                <text class="performance-axis-label" x="54" y="{{ $tick->y + 4 }}" text-anchor="end">{{ $tick->enrollments }}</text>
                                <text class="performance-axis-label" x="586" y="{{ $tick->y + 4 }}">{{ $tick->completion_rate }}%</text>
                            @endforeach
                            @foreach ($chartPoints as $point)
                                <rect class="performance-bar is-enrollment" x="{{ $point->enrollment_x }}" y="{{ $point->enrollment_y }}" width="24" height="{{ 202 - $point->enrollment_y }}" rx="3">
                                    <title>{{ $point->course->label }}: {{ $point->course->enrollments }} enrollments</title>
                                </rect>
                                <rect class="performance-bar is-completion" x="{{ $point->completion_x }}" y="{{ $point->completion_y }}" width="24" height="{{ 202 - $point->completion_y }}" rx="3">
                                    <title>{{ $point->course->label }}: {{ $point->course->completion_rate }}% completion</title>
                                </rect>
                                <text class="performance-course-label" x="{{ $point->x }}" y="228" text-anchor="middle">{{ $point->course->label }}</text>
                            @endforeach
                        </svg>
                    @else
                        <p class="performance-empty">No course enrollment data is available yet.</p>
                    @endif
                </div>
                @endif

                @if ($dashboardAccess['credentials'])
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Recent badges</span>
                    </div>

                    <div class="badges-grid" id="recent-badges-grid">
                        @foreach ($recentBadges as $badge)
                            <div class="badge-card">
                                <div class="badge-name">{{ $badge->name }}</div>
                                <div class="badge-count">{{ $badge->earned_count }} earned</div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </main>
    </div>

    {{-- SCRIPTS --}}
    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            if (!section || section.classList.contains('locked')) return;
            section.classList.toggle('open');
        }

        @if ($dashboardAccess['audit-logs'])
        function esc(value) {
            const el = document.createElement('div');
            el.textContent = value ?? '';
            return el.innerHTML;
        }

        function refreshLiveMonitoring() {
            const liveUrl = '{{ route('monitoring.live') }}';

            fetch(liveUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Monitoring endpoint unavailable');
                    return response.json();
                })
                .then(data => {
                    const badgeGrid = document.getElementById('recent-badges-grid');

                    if (badgeGrid && Array.isArray(data.recentBadges)) {
                        badgeGrid.innerHTML = data.recentBadges.map(badge =>
                            `<div class="badge-card">
                                <div class="badge-name">${esc(badge.name)}</div>
                                <div class="badge-count">${esc(badge.earned_count)} earned</div>
                             </div>`
                        ).join('');
                    }

                    const list = document.getElementById('live-activity-list');
                    if (!list) return;

                    list.innerHTML = data.activity.map(item =>
                        `<div class="feed-item">
                            <div class="feed-title">${esc(item.title)}</div>
                            <div class="feed-detail">${esc(item.detail)} <span class="feed-time">&middot; ${esc(item.time)}</span></div>
                         </div>`
                    ).join('');
                })
                .catch(() => {
                    const list = document.getElementById('live-activity-list');
                    if (list) {
                        list.innerHTML = '<div class="feed-empty">The live feed could not be loaded. It will retry automatically.</div>';
                    }
                });
        }

        refreshLiveMonitoring();
        setInterval(refreshLiveMonitoring, 15000);
        @endif
        (function () {
            var btn = document.getElementById('back-to-top-btn');
            if (!btn) return;

            function toggleBackToTop() {
                btn.style.display =
                    (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
            }

            window.addEventListener('scroll', toggleBackToTop, { passive: true });
            toggleBackToTop();
        })();
    </script>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-admin.layout>