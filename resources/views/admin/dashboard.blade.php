
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Admin Dashboard</title>

    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =========================================================
           UPSKILL ADMIN DASHBOARD
           Blue / White / Yellow UI
           Design-only layer
        ========================================================= */

        /* Avoid resetting margins and padding on shared components */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            --navy: #09255f;
            --blue: #0b4ea2;
            --blue-light: #eaf2ff;
            --blue-soft: #f4f7fc;

            --yellow: #f4c430;
            --yellow-soft: #fff8dc;

            --white: #ffffff;
            --surface: #f5f7fa;

            --border: #e2e7ef;
            --border-light: #edf0f5;

            --text: #17212b;
            --text-muted: #687385;
            --text-soft: #929baa;

            --green: #247a5b;
            --green-soft: #eaf7f1;

            --red: #b54747;
            --red-soft: #fff0f0;

            --shadow-sm: 0 1px 3px rgba(9, 37, 95, .05);
            --shadow-md: 0 5px 18px rgba(9, 37, 95, .08);

            --radius: 10px;
            --radius-sm: 7px;
        }

        /* PAGE */
        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .layout {
            min-height: calc(100vh - 66px);
        }

        .main {
            margin-left: 0;
            width: 100%;
            min-width: 0;
            padding: 30px 30px 44px;
        }

        /* PAGE HEADER */
        .page-heading {
            font-size: 1.45rem;
            line-height: 1.25;
            font-weight: 700;
            letter-spacing: -.02em;
            color: var(--navy);
            margin-bottom: 5px;
        }

        .page-sub {
            font-size: .84rem;
            line-height: 1.5;
            font-weight: 400;
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            position: relative;
            min-height: 112px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 19px 20px;
            box-shadow: var(--shadow-sm);
            text-align: left;
            overflow: hidden;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--yellow);
        }

        .stat-card:hover {
            border-color: #cfd8e6;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .stat-val {
            font-size: 1.8rem;
            line-height: 1;
            font-weight: 700;
            letter-spacing: -.025em;
            color: var(--navy);
            margin-bottom: 9px;
        }

        .stat-lbl {
            font-size: .72rem;
            line-height: 1.3;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .055em;
        }

        /* TWO-COLUMN LAYOUT */
        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 16px;
            margin-bottom: 16px !important;
        }

        /* CARD */
        .card {
            min-width: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 19px 20px;
            box-shadow: var(--shadow-sm);
        }

        .card-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 25px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light);
        }

        .card-title {
            font-size: .88rem;
            line-height: 1.3;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -.01em;
        }

        .view-all {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 9px;
            border-radius: 6px;
            color: var(--blue);
            background: transparent;
            font-size: .73rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .18s ease, color .18s ease;
        }

        .view-all:hover {
            color: var(--navy);
            background: var(--blue-light);
            opacity: 1;
        }

        /* LIVE MONITORING */
        #live-activity-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        #live-activity-list .badge-card {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            border: 0;
            border-bottom: 1px solid var(--border-light);
            border-radius: 0;
            padding: 11px 2px;
        }

        #live-activity-list .badge-card:last-child {
            border-bottom: 0;
        }

        #live-activity-list .badge-card::before {
            content: "";
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: var(--yellow);
            box-shadow: 0 0 0 3px var(--yellow-soft);
        }

        /* PLATFORM SNAPSHOT */
        .chart-row {
            margin-bottom: 15px;
        }

        .chart-row:last-child {
            margin-bottom: 0;
        }

        .chart-lbl {
            font-size: .77rem;
            line-height: 1.4;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .card > .chart-row:not(:has(.bar-track)) {
            padding: 9px 11px;
            background: var(--blue-soft);
            border-left: 3px solid var(--blue);
            border-radius: 5px;
        }

        .card > .chart-row:not(:has(.bar-track)) .chart-lbl {
            margin-bottom: 0;
            color: var(--navy);
            font-weight: 600;
        }

        /* ACTIVE COURSES */
        .course-item {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 61px;
            padding: 10px 2px;
            border-bottom: 1px solid var(--border-light);
        }

        .course-item:last-child {
            border-bottom: 0;
        }

        .course-thumb {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            background: var(--blue-light);
            border: 1px solid #d7e3f7;
            border-radius: 7px;
            overflow: hidden;
        }

        .course-body {
            flex: 1;
            min-width: 0;
        }

        .course-name {
            font-size: .79rem;
            line-height: 1.35;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .course-meta {
            font-size: .68rem;
            line-height: 1.35;
            color: var(--text-soft);
            margin-top: 3px;
        }

        .course-pct {
            min-width: 48px;
            padding: 5px 7px;
            border: 1px solid #d5deeb;
            border-radius: 6px;
            background: var(--blue-soft);
            color: var(--navy);
            font-size: .72rem;
            line-height: 1;
            font-weight: 700;
            text-align: center;
            flex-shrink: 0;
        }

        /* BADGES */
        .badges-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .badge-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 12px 11px;
            text-align: left;
            transition: border-color .18s ease, background .18s ease;
        }

        .badge-card:hover {
            border-color: #c7d5e9;
            background: #fbfcfe;
        }

        .badge-name {
            font-size: .77rem;
            line-height: 1.35;
            font-weight: 600;
            color: var(--navy);
        }

        .badge-count {
            font-size: .68rem;
            line-height: 1.35;
            color: var(--text-soft);
            margin-top: 4px;
        }

        #recent-badges-grid .badge-card {
            position: relative;
            padding-left: 15px;
        }

        #recent-badges-grid .badge-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 12px;
            bottom: 12px;
            width: 3px;
            border-radius: 2px;
            background: var(--yellow);
        }

        /* BAR CHARTS */
        .bar-track {
            height: 7px;
            background: #e9edf3;
            border-radius: 3px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--blue);
            border-radius: 3px;
            transition: width .6s ease;
        }

        .card .bar-track + * {
            margin-top: 0;
        }

        /* EMPTY / LOADING STATES */
        #live-activity-list:empty::before {
            content: "Waiting for live activity...";
            display: block;
            padding: 22px 4px;
            color: var(--text-soft);
            font-size: .76rem;
            text-align: center;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .main {
                padding: 26px 22px 38px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 850px) {
            .main {
                margin-left: 0;
                width: 100%;
                padding: 24px 18px 34px;
            }

            .two-col {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-heading {
                font-size: 1.25rem;
            }

            .card {
                padding: 16px;
            }

            .badges-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<style>
:root {}
main.admin-dashboard {
  --dash-navy:#0B1B45; --dash-navy-deep:#071233; --dash-royal:#2447D4;
  --dash-gold:#F4C430; --dash-gold-soft:#FFF5D1; --dash-canvas:#F4F6FB;
  --dash-ink:#0E1A3A; --dash-muted:#64748B; --dash-line:#E6EAF2;
  color:var(--dash-ink); font-family:'Inter',ui-sans-serif,system-ui,sans-serif;
  padding:32px clamp(20px,3vw,44px) 56px; min-width:0;
}
main.admin-dashboard h1,main.admin-dashboard h2,main.admin-dashboard h3,main.admin-dashboard h4,
main.admin-dashboard .page-heading,main.admin-dashboard .stat-val {
  font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif;
}
.layout:has(main.admin-dashboard){background:var(--dash-canvas);}
main.admin-dashboard .page-heading{font-size:27px;font-weight:800;letter-spacing:-.04em;color:var(--dash-ink);line-height:1.2}
main.admin-dashboard .page-eyebrow{margin-bottom:7px;color:#C9960C;font-size:10px;font-weight:800;letter-spacing:.2em;text-transform:uppercase}
main.admin-dashboard .page-sub{margin-top:7px;color:var(--dash-muted);font-size:14px}
main.admin-dashboard .stats-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
main.admin-dashboard .stats-grid{gap:16px;margin:24px 0 0}
main.admin-dashboard .stat-card{min-height:126px;padding:21px 22px;border:1px solid var(--dash-line);border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(11,27,69,.045);text-align:left}
main.admin-dashboard .stat-val{font-size:32px;font-weight:800;line-height:1;color:var(--dash-navy)}
main.admin-dashboard .stat-lbl{margin-top:10px;font-size:12px;font-weight:600;letter-spacing:.01em;color:var(--dash-muted)}
main.admin-dashboard .two-col{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-top:22px}
main.admin-dashboard .two-col.monitor-grid,main.admin-dashboard .two-col.active-grid{grid-template-columns:minmax(0,3fr) minmax(280px,2fr)}
main.admin-dashboard .card{min-width:0;padding:0 20px 18px;border:1px solid var(--dash-line);border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(11,27,69,.045)}
main.admin-dashboard .stat-card{position:relative;min-height:152px;padding:25px 22px 22px 27px;border:1px solid var(--dash-line);border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(11,27,69,.045);text-align:left;overflow:hidden}
main.admin-dashboard .stat-card:before{content:"";position:absolute;left:0;top:22px;bottom:22px;width:4px;border-radius:0 5px 5px 0;background:var(--dash-gold)}
main.admin-dashboard .stat-val{font-size:40px;font-weight:800;line-height:1;color:var(--dash-ink)}
main.admin-dashboard .stat-lbl{margin-top:10px;font-size:13px;font-weight:500;letter-spacing:0;color:var(--dash-muted)}
main.admin-dashboard .two-col{display:grid;width:100%;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-top:22px}
main.admin-dashboard .two-col.monitor-grid{grid-template-columns:minmax(0,1.4fr) minmax(0,1fr)}
main.admin-dashboard .two-col.active-grid{grid-template-columns:minmax(0,1.4fr) minmax(320px,1fr)}
main.admin-dashboard .card{min-width:0;padding:0 20px 18px;border:1px solid var(--dash-line);border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(11,27,69,.045);transition:transform .2s ease,box-shadow .2s ease}
main.admin-dashboard .card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(11,27,69,.09)}
main.admin-dashboard .card-hd{min-height:58px;margin:0 -20px 14px;padding:0 20px;border-bottom:1px solid #EEF1F6;background:#fff;color:var(--dash-ink)}
main.admin-dashboard .card-title{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:700;color:var(--dash-ink)}
main.admin-dashboard .view-all{font-size:12px;font-weight:700;color:var(--dash-royal)}
main.admin-dashboard .chart-row{margin:13px 0}
main.admin-dashboard .chart-lbl{margin-bottom:8px;color:var(--dash-muted);font-size:12px;font-weight:500}
main.admin-dashboard .bar-track{height:8px;border-radius:999px;background:#EDF1F8;overflow:hidden}
main.admin-dashboard .bar-fill{height:100%;border-radius:999px;background:var(--dash-royal)}
main.admin-dashboard .course-item{gap:13px;padding:13px 0;border-bottom:1px solid #EEF1F6}
main.admin-dashboard .course-item:hover{background:var(--dash-canvas)}
main.admin-dashboard .course-thumb{width:42px;height:42px;border-radius:12px;background:var(--dash-gold-soft)}
main.admin-dashboard .course-name{font-size:13px;font-weight:700;color:var(--dash-ink)}
main.admin-dashboard .course-meta{margin-top:4px;color:var(--dash-muted);font-size:11px}
main.admin-dashboard .course-pct{font-family:'Plus Jakarta Sans',sans-serif;color:var(--dash-ink);font-size:13px;font-weight:700}
main.admin-dashboard .badge-card{border:1px solid var(--dash-line);border-radius:13px;background:#fff;padding:13px}
main.admin-dashboard .active-grid .badge-card{transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease}
main.admin-dashboard .active-grid .badge-card:hover{transform:translateY(-2px);border-color:rgba(244,196,48,.75);box-shadow:0 7px 18px rgba(11,27,69,.07)}
main.admin-dashboard .badge-name{font-size:12px;font-weight:700;color:var(--dash-ink)}
main.admin-dashboard .badge-count{margin-top:4px;font-size:11px;color:var(--dash-muted)}
main.admin-dashboard .chart-row:last-child{margin-bottom:0}
main.admin-dashboard .snapshot-hero{position:relative;overflow:hidden;margin-bottom:12px;padding:19px 20px;border-radius:16px;background:#0B1B45;color:#fff}
main.admin-dashboard .snapshot-hero:after{content:"";position:absolute;right:-25px;top:-50px;width:130px;height:130px;border-radius:50%;background:rgba(244,196,48,.16);filter:blur(3px)}
main.admin-dashboard .snapshot-hero span,main.admin-dashboard .snapshot-hero strong,main.admin-dashboard .snapshot-hero small{position:relative;display:block;z-index:1}
main.admin-dashboard .snapshot-hero span{font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.62)}
main.admin-dashboard .snapshot-hero strong{margin-top:10px;font:700 42px/1 'Plus Jakarta Sans',sans-serif;color:#fff}
main.admin-dashboard .snapshot-hero small{margin-top:8px;font-size:11px;color:rgba(255,255,255,.65)}
main.admin-dashboard .snapshot-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}
main.admin-dashboard .snapshot-metric{min-width:0;padding:12px;border-radius:12px;background:var(--dash-canvas)}
main.admin-dashboard .snapshot-metric strong,main.admin-dashboard .snapshot-metric span{display:block}
main.admin-dashboard .snapshot-metric strong{font:700 17px 'Plus Jakarta Sans',sans-serif;color:var(--dash-ink)}
main.admin-dashboard .snapshot-metric span{margin-top:4px;font-size:10px;line-height:1.3;color:var(--dash-muted)}
@media(max-width:900px){main.admin-dashboard .two-col.monitor-grid,main.admin-dashboard .two-col.active-grid{grid-template-columns:1fr 1fr}}
@media(max-width:900px){main.admin-dashboard .two-col.monitor-grid,main.admin-dashboard .two-col.active-grid{grid-template-columns:minmax(0,1.25fr) minmax(0,1fr)}}
@media(max-width:680px){main.admin-dashboard{padding:24px 16px 40px}main.admin-dashboard .two-col,main.admin-dashboard .two-col.monitor-grid,main.admin-dashboard .two-col.active-grid{grid-template-columns:1fr}main.admin-dashboard .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}main.admin-dashboard .stat-card{min-height:108px;padding:17px}}
@media(max-width:860px){main.admin-dashboard .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
</head>

<body>

    @include('components.authenticated-topbar', ['user' => auth()->user()])

    <div class="layout">
        @include('components.admin-sidebar')

        <main class="main admin-dashboard">

            <div class="page-eyebrow">ADMIN CONSOLE</div>
            <div class="page-heading">Welcome back, Admin</div>
            <div class="page-sub">Here's what's happening on your platform today.</div>

            {{-- STAT CARDS --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-val" id="live-active-users">0</div>
                    <div class="stat-lbl">Active Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-val" id="live-events-today">0</div>
                    <div class="stat-lbl">Events Today</div>
                </div>

                <div class="stat-card">
                    <div class="stat-val" id="live-enrollments-today">0</div>
                    <div class="stat-lbl">Enrollments Today</div>
                </div>

                <div class="stat-card">
                    <div class="stat-val" id="live-badges-today">0</div>
                    <div class="stat-lbl">Badges Today</div>
                </div>
            </div>

            {{-- LIVE MONITORING + PLATFORM SNAPSHOT --}}
            <div class="two-col monitor-grid" style="margin-bottom: 18px;">
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Live Monitoring Feed</span>
                    </div>
                    <div id="live-activity-list" class="badges-grid"></div>
                </div>

                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Current Platform Snapshot</span>
                    </div>

                    <div class="snapshot-hero">
                        <span>Students on platform</span>
                        <strong>{{ $stats['total_students'] }}</strong>
                        <small>Learning across {{ count($activeCourses ?? []) }} active courses</small>
                    </div>

                    <div class="snapshot-metrics">
                        <div class="snapshot-metric"><strong>{{ $stats['badges_issued'] }}</strong><span>Badges issued</span></div>
                        <div class="snapshot-metric"><strong>{{ $stats['course_score_avg'] }}%</strong><span>Avg. quiz score</span></div>
                        <div class="snapshot-metric"><strong>{{ $stats['students_enrolled'] }}</strong><span>Enrollments</span></div>
                    </div>
                </div>
            </div>

            {{-- ACTIVE COURSES + RECENT BADGES --}}
            <div class="two-col active-grid">

                {{-- Active Courses --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Active Courses</span>
                        <a href="{{ route('courses.browse') }}" class="view-all">View All</a>
                    </div>

                    @foreach ($activeCourses as $course)
                        <div class="course-item">
                            <div class="course-thumb">
                                @if ($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}"
                                         alt="{{ $course->title }}"
                                         style="width:100%;height:100%;object-fit:cover;">
                                @endif
                            </div>

                            <div class="course-body">
                                <div class="course-name">{{ $course->title }}</div>
                                <div class="course-meta">{{ $course->meta }}</div>
                            </div>

                            <div class="course-pct">{{ $course->percent }}%</div>
                        </div>
                    @endforeach
                </div>

                {{-- Recent Badges --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Recent Badges</span>
                    </div>

                    <div class="badges-grid" id="recent-badges-grid">
                        @foreach ($recentBadges as $badge)
                            <div class="badge-card">
                                <div class="badge-name">{{ $badge->name }}</div>
                                <div class="badge-count">{{ $badge->earned_count }} Earned</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ENROLLMENT + COMPLETION CHARTS --}}
            <div class="two-col analytics-grid">

                {{-- Enrollment by Courses --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Enrollment by Courses</span>
                    </div>

                    @foreach ($enrollmentByCourse as $row)
                        <div class="chart-row">
                            <div class="chart-lbl">
                                {{ $row->label }} &ndash; {{ $row->value }}
                            </div>

                            <div class="bar-track">
                                <div class="bar-fill" style="width:{{ $row->percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Completion Rate --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Completion Rate</span>
                    </div>

                    @foreach ($completionRate as $row)
                        <div class="chart-row">
                            <div class="chart-lbl">
                                {{ $row->label }} &ndash; {{ $row->value }}%
                            </div>

                            <div class="bar-track">
                                <div class="bar-fill" style="width:{{ $row->percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
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

        function refreshLiveMonitoring() {
            const liveUrl = '{{ route('monitoring.live') }}';

            fetch(liveUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Monitoring endpoint unavailable');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('live-active-users').textContent =
                        data.stats.active_users;

                    document.getElementById('live-events-today').textContent =
                        data.stats.events_today;

                    document.getElementById('live-enrollments-today').textContent =
                        data.stats.enrollments_today;

                    document.getElementById('live-badges-today').textContent =
                        data.stats.badges_today;

                    // Refresh recent badge counters
                    const badgeGrid = document.getElementById('recent-badges-grid');

                    if (badgeGrid && Array.isArray(data.recentBadges)) {
                        badgeGrid.innerHTML = '';

                        data.recentBadges.forEach(badge => {
                            const card = document.createElement('div');
                            card.className = 'badge-card';

                            card.innerHTML =
                                `<div class="badge-name">${badge.name}</div>
                                 <div class="badge-count">${badge.earned_count} Earned</div>`;

                            badgeGrid.appendChild(card);
                        });
                    }

                    // Refresh live activity feed
                    const list = document.getElementById('live-activity-list');
                    if (!list) return;

                    list.innerHTML = '';

                    data.activity.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'badge-card';

                        card.innerHTML =
                            `<div class="badge-name">${item.title}</div>
                             <div class="badge-count">${item.detail} · ${item.time}</div>`;

                        list.appendChild(card);
                    });
                })
                .catch(() => {
                    const list = document.getElementById('live-activity-list');

                    if (list) {
                        list.innerHTML =
                            '<div class="badge-card">' +
                                '<div class="badge-name">Monitoring unavailable</div>' +
                                '<div class="badge-count">The live feed could not be loaded.</div>' +
                            '</div>';
                    }
                });
        }

        refreshLiveMonitoring();
        setInterval(refreshLiveMonitoring, 15000);

        (function () {
            var btn = document.getElementById('back-to-top-btn');
            if (!btn) return;

            function toggleBackToTop() {
                btn.style.display =
                    (window.scrollY || document.documentElement.scrollTop) > 400
                        ? 'flex'
                        : 'none';
            }

            window.addEventListener('scroll', toggleBackToTop, { passive: true });
            toggleBackToTop();
        })();
    </script>


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>