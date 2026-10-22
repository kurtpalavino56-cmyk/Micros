<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Admin Dashboard</title>

    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =========================================================
           UPSKILL ADMIN DASHBOARD – simple version
           White surfaces, thin borders, one blue accent.
        ========================================================= */
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --navy: #0f2557;
            --blue: #1d4ed8;
            --yellow: #f4c430;
            --canvas: #f6f7f9;
            --white: #fff;
            --line: #e5e8ee;
            --line-soft: #eef0f4;
            --ink: #1a2233;
            --muted: #667085;
            --soft: #98a2b3;
            --green: #1f7a58;
            --red: #b94040;
            --r: 10px;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--canvas);
            color: var(--ink);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .layout { min-height: calc(100vh - 66px); background: var(--canvas); }

        main.admin-dashboard {
            width: 100%;
            min-width: 0;
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px clamp(18px, 3vw, 40px) 56px;
        }
        .admin-dashboard :is(h1, h2, h3, .stat-val) { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        :focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }

        /* HEADER */
        .page-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 22px; }
        .page-heading { margin: 0; font-size: 30px; font-weight: 700; letter-spacing: -.03em; color: #000; }
        .page-sub { margin: 4px 0 0; font-size: .9rem; color: var(--muted); }
        .today-chip { display: inline-flex; align-items: center; gap: 7px; font-size: .82rem; color: var(--muted); }
        .today-chip svg { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .stat-card { position: relative; overflow: hidden; padding: 18px 20px; background: var(--white); border: 1px solid var(--line); border-radius: var(--r); }
        .stat-card::before { display: none; }
        .stat-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-direction: row-reverse; }
        .stat-icon { display: grid; width: 32px; height: 32px; flex: 0 0 32px; place-items: center; color: var(--muted); }
        .stat-icon svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .stat-lbl { font-size: .84rem; font-weight: 500; color: var(--muted); }
        .stat-val { margin: 12px 0 8px; font-size: 2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; color: var(--ink); font-variant-numeric: tabular-nums; }
        .stat-context { display: flex; align-items: center; gap: 6px; font-size: .78rem; color: #000; }
        .stat-trend { display: inline-flex; align-items: center; gap: 3px; font-weight: 600; }
        .stat-trend svg { width: 11px; height: 11px; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
        .stat-trend-up { color: var(--green); }
        .stat-trend-down { color: var(--red); }
        .stat-trend-neutral { color: #000; }

        /* REQUIRED ATTENTION */
        .attention-strip { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-top: 16px; padding: 16px 18px; background: #fff8db; border: 1px solid #f0dda0; border-radius: var(--r); }
        .attention-copy { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .attention-indicator { width: 10px; height: 10px; flex: 0 0 10px; border-radius: 50%; background: #c28a00; }
        .attention-strip.is-clear { background: var(--white); border-color: var(--line); }
        .attention-strip.is-clear .attention-indicator { background: var(--green); }
        .attention-title { margin: 0; font-size: .92rem; font-weight: 700; color: var(--ink); }
        .attention-message { margin: 3px 0 0; font-size: .8rem; color: var(--muted); }
        .attention-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .attention-link { display: inline-flex; align-items: center; gap: 6px; padding: 8px 11px; border: 1px solid rgba(15, 37, 87, .16); border-radius: 7px; background: var(--white); color: var(--navy); font-size: .78rem; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .attention-link:hover { background: #f6f7f9; }
        .attention-link strong { font-variant-numeric: tabular-nums; }
        .attention-clear-label { color: var(--green); font-size: .8rem; font-weight: 600; }

        /* SECTION HEADINGS */
        .section-hd { margin: 30px 0 12px; }
        .section-hd h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: #000; }
        .section-hd p { margin: 3px 0 0; font-size: .82rem; color: var(--muted); }

        /* QUICK ACCESS */
        .quick-modules-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .quick-module-card {
            position: relative;
            display: grid;
            grid-template-columns: 24px minmax(0, 1fr);
            column-gap: 14px;
            padding: 16px 18px;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--r);
            transition: border-color .15s ease;
        }
        .quick-module-card:hover { border-color: var(--blue); }
        .quick-module-head { display: contents; }
        .quick-module-mark { grid-row: 1 / span 2; color: var(--blue); padding-top: 1px; }
        .quick-module-mark svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .quick-module-copy { min-width: 0; }
        .quick-module-card h3 { margin: 0; font-size: .92rem; font-weight: 600; color: var(--ink); }
        .quick-module-card p { margin: 4px 0 0; font-size: .78rem; line-height: 1.45; color: var(--muted); }
        .quick-module-info { grid-column: 2; margin-top: 8px; font-size: .76rem; font-weight: 600; color: var(--navy); }
        .quick-module-link { position: absolute; inset: 0; border-radius: var(--r); font-size: 0; }

        /* CARDS + GRIDS */
        .two-col { display: grid; gap: 16px; margin-top: 16px; }
        .monitor-grid, .insights-grid { grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); }
        .card { min-width: 0; padding: 0 20px 12px; background: var(--white); border: 1px solid var(--line); border-radius: var(--r); }
        .card-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 54px; margin: 0 -20px 4px; padding: 0 20px; border-bottom: 1px solid var(--line-soft); }
        .card-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: .92rem; font-weight: 700; color: #000; }
        .view-all { font-size: .8rem; font-weight: 500; color: var(--blue); text-decoration: none; }
        .view-all:hover { text-decoration: underline; }

        .live-dot { display: inline-flex; align-items: center; gap: 6px; font-size: .76rem; color: var(--muted); }
        .live-dot::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--green); }

        /* LIVE FEED */
        .monitor-grid > .card { display: flex; height: 280px; flex-direction: column; }
        #live-activity-list { min-height: 0; flex: 1; overflow-y: auto; padding-left: 22px; }
        #live-activity-list:empty::before { content: "Waiting for live activity…"; display: block; padding: 28px 4px; text-align: center; font-size: .82rem; color: var(--soft); }
        .feed-item { position: relative; padding: 12px 0 12px 4px; }
        .feed-item::before { position: absolute; top: 0; bottom: -1px; left: -17px; width: 1px; background: var(--line); content: ""; }
        .feed-item::after { position: absolute; top: 17px; left: -22px; z-index: 1; width: 8px; height: 8px; border: 1.5px solid var(--yellow); border-radius: 50%; background: var(--white); content: ""; }
        .feed-item:first-child::after { background: var(--yellow); }
        .feed-item:last-child::before { bottom: 50%; }
        .feed-title { font-size: .86rem; font-weight: 600; color: var(--ink); }
        .feed-detail { margin-top: 3px; font-size: .78rem; color: #526078; }
        .feed-time { color: #526078; white-space: nowrap; }
        .feed-empty { padding: 22px 4px; font-size: .82rem; color: var(--red); }

        /* SNAPSHOT */
        .snapshot-card { min-height: 0; }
        .snapshot-metrics { display: grid; width: 100%; max-width: 380px; min-height: 0; flex: 1; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); margin: 0 auto; padding-top: 4px; }
        .snapshot-metric { display: flex; min-width: 0; min-height: 0; flex-direction: column; align-items: flex-start; justify-content: center; gap: 3px; padding: 12px; }
        .snapshot-metric:nth-child(odd) { border-right: 1px solid var(--line-soft); }
        .snapshot-metric:nth-child(-n+2) { border-bottom: 1px solid var(--line-soft); }
        .snapshot-metric span { font-size: .82rem; color: var(--muted); }
        .snapshot-metric strong { font: 700 1.75rem/1 'Plus Jakarta Sans', sans-serif; letter-spacing: -.03em; color: #000; font-variant-numeric: tabular-nums; }
        .snapshot-metric small { font-size: .72rem; line-height: 1.4; color: var(--muted); }

        /* BADGES */
        .badges-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; padding-top: 10px; }
        .badge-card { padding: 12px 14px; border: 1px solid var(--line-soft); border-radius: 8px; }
        .badge-name { font-size: .84rem; font-weight: 600; color: var(--ink); }
        .badge-count { margin-top: 3px; font-size: .76rem; color: var(--muted); }

        /* COURSE PERFORMANCE BAR CHART */
        .performance-legend { display: flex; flex-wrap: wrap; gap: 16px; padding: 12px 0 4px; }
        .performance-legend-item { display: inline-flex; align-items: center; gap: 7px; color: var(--muted); font-size: .76rem; }
        .performance-legend-swatch { width: 10px; height: 10px; border-radius: 2px; background: var(--blue); }
        .performance-legend-swatch.is-completion { background: var(--green); }
        .performance-chart { display: block; width: 100%; height: auto; overflow: visible; }
        .performance-gridline { stroke: var(--line-soft); stroke-width: 1; }
        .performance-axis-label, .performance-course-label { fill: var(--muted); font-family: 'Inter', sans-serif; font-size: 11px; }
        .performance-course-label { font-size: 10px; }
        .performance-bar.is-enrollment { fill: var(--blue); }
        .performance-bar.is-completion { fill: var(--green); }
        .performance-empty { padding: 38px 12px; text-align: center; color: var(--muted); font-size: .84rem; }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .quick-modules-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 900px) {
            .monitor-grid, .insights-grid { grid-template-columns: 1fr; }
            .attention-strip { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 600px) {
            main.admin-dashboard { padding: 22px 14px 40px; }
            .stats-grid, .quick-modules-grid, .badges-grid { grid-template-columns: 1fr; }
            .attention-actions { width: 100%; }
            .attention-link { flex: 1 1 100%; white-space: normal; }
            .stat-val { font-size: 1.7rem; }
            .snapshot-metric strong { font-size: 1.45rem; }
        }

    </style>
</head>

<body>

    @include('components.authenticated-topbar', ['user' => auth()->user()])

    <div class="layout">
        @include('components.admin-sidebar')

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
    @include('components.responsive')
</body>
</html>