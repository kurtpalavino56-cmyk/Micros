<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Admin Dashboard</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        /* ─── Tokens ─────────────────────────────────────────────── */
        :root {
            --navy:        #0d1b6e;
            --navy-light:  #1a2fa0;
            --navy-pale:   #e8eaf6;
            --teal:        #1a6b5a;
            --teal-bar:    #1f8a6e;
            --white:       #ffffff;
            --surface:     #f4f6fb;
            --border:      #e2e5f0;
            --text-main:   #0d1b6e;
            --text-muted:  #6b7280;
            --text-sub:    #9ca3af;
            --shadow-sm:   0 1px 4px rgba(13,27,110,.07);
            --shadow-md:   0 3px 10px rgba(13,27,110,.10);
            --radius-card: 12px;
            --radius-sm:   8px;
            --sidebar-w:   225px;
            --topbar-h:    58px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* ─── TOP NAV ─────────────────────────────────────────────── */
        

        /* ─── LAYOUT ─────────────────────────────────────────────── */
    

        /* ════════════════════════════════════════════════════════════
           SIDEBAR
           Structure → Image 1 (bold header + count subtitle + chevron
                                + left accent border on open section
                                + thin row dividers between items)
           Active item → Image 2 (teal rounded pill, no icons, plain text)
           ════════════════════════════════════════════════════════════ */
        

        /* ─── MAIN CONTENT ─────────────────────────────────────── */
        .layout {
            min-height: calc(100vh - 66px);
        }

        .main {
            margin-left: 238px;
            width: calc(100% - 238px);
            min-width: 0;
            padding: 30px 28px 40px;
        }

        .page-heading { font-size: 1.25rem; font-weight: 800; color: var(--navy); margin-bottom: 3px; }
        .page-sub { font-size: 0.825rem; color: var(--text-muted); margin-bottom: 26px; }

        /* ─── STAT CARDS ─────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            padding: 22px 18px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: box-shadow .2s, transform .2s;
        }

        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .stat-val {
            font-size: 1.95rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
            margin-bottom: 7px;
        }

        .stat-lbl {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        /* ─── TWO-COLUMN GRID ─────────────────────────────────────── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        /* ─── CARD SHELL ─────────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .card-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .card-title { font-size: 0.9rem; font-weight: 800; color: var(--navy); }

        .view-all {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--navy-light);
            text-decoration: none;
            transition: opacity .2s;
        }

        .view-all:hover { opacity: .7; }

        /* ─── COURSE LIST ─────────────────────────────────────────── */
        .course-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #f1f3fa;
        }

        .course-item:last-child { border-bottom: none; }

        .course-thumb {
            width: 42px;
            height: 42px;
            background: var(--navy-pale);
            border-radius: var(--radius-sm);
            border: 1px solid #c5cae9;
            flex-shrink: 0;
            overflow: hidden;
        }

        .course-body { flex: 1; min-width: 0; }

        .course-name {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .course-meta { font-size: 0.7rem; color: var(--text-sub); margin-top: 2px; }

        .course-pct {
            border: 1.5px solid var(--navy);
            border-radius: var(--radius-sm);
            padding: 4px 9px;
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--navy);
            min-width: 46px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ─── BADGES GRID ─────────────────────────────────────────── */
        .badges-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .badge-card {
            border: 1.5px solid #c5cae9;
            border-radius: 10px;
            padding: 13px 12px;
            text-align: center;
            transition: border-color .2s;
        }

        .badge-card:hover { border-color: var(--navy-light); }

        .badge-name { font-size: 0.8rem; font-weight: 700; color: var(--navy); }
        .badge-count { font-size: 0.72rem; color: var(--text-sub); margin-top: 3px; }

        /* ─── BAR CHARTS ─────────────────────────────────────────── */
        .chart-row { margin-bottom: 13px; }
        .chart-row:last-child { margin-bottom: 0; }

        .chart-lbl {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 5px;
        }

        .bar-track {
            height: 8px;
            background: #e4e8f5;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--teal-bar);
            border-radius: 4px;
            transition: width .6s ease;
        }
    </style>
</head>
<body>

@include('components.authenticated-topbar', ['user' => auth()->user()])

    <div class="layout">

        @include('components.admin-sidebar')

        <main class="main">

    <div class="page-heading">Welcome back, Admin</div>
    <div class="page-sub">Here's what's happening on your platform today.</div>

    {{-- ── STAT CARDS ──────────────────────────────────────────────────── --}}
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

    <div class="two-col" style="margin-bottom: 18px;">
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
            <div class="chart-row">
                <div class="chart-lbl">Students on platform &ndash; {{ $stats['total_students'] }}</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Badges issued &ndash; {{ $stats['badges_issued'] }}</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Average quiz score &ndash; {{ $stats['course_score_avg'] }}%</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Enrollments recorded &ndash; {{ $stats['students_enrolled'] }}</div>
            </div>
        </div>
    </div>

    {{-- ── ACTIVE COURSES + RECENT BADGES ─────────────────────────────── --}}
    <div class="two-col">

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
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}"
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

    {{-- ── ENROLLMENT + COMPLETION CHARTS ─────────────────────────────── --}}
    <div class="two-col">

        {{-- Enrollment by Courses --}}
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Enrollment by Courses</span>
            </div>
            @foreach ($enrollmentByCourse as $row)
            <div class="chart-row">
                <div class="chart-lbl">{{ $row->label }} &ndash; {{ $row->value }}</div>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $row->percent }}%"></div></div>
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
                <div class="chart-lbl">{{ $row->label }} &ndash; {{ $row->value }}%</div>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $row->percent }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>

</main>
</div>{{-- /layout --}}

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────── --}}
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
                document.getElementById('live-active-users').textContent = data.stats.active_users;
                document.getElementById('live-events-today').textContent = data.stats.events_today;
                document.getElementById('live-enrollments-today').textContent = data.stats.enrollments_today;
                document.getElementById('live-badges-today').textContent = data.stats.badges_today;

                // Recent Badges — refresh the Earned counters in real time
                const badgeGrid = document.getElementById('recent-badges-grid');
                if (badgeGrid && Array.isArray(data.recentBadges)) {
                    badgeGrid.innerHTML = '';
                    data.recentBadges.forEach(badge => {
                        const card = document.createElement('div');
                        card.className = 'badge-card';
                        card.innerHTML = `<div class="badge-name">${badge.name}</div><div class="badge-count">${badge.earned_count} Earned</div>`;
                        badgeGrid.appendChild(card);
                    });
                }

                const list = document.getElementById('live-activity-list');
                if (!list) return;

                list.innerHTML = '';
                data.activity.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'badge-card';
                    card.innerHTML = `<div class="badge-name">${item.title}</div><div class="badge-count">${item.detail} · ${item.time}</div>`;
                    list.appendChild(card);
                });
            })
            .catch(() => {
                const list = document.getElementById('live-activity-list');
                if (list) {
                    list.innerHTML = '<div class="badge-card"><div class="badge-name">Monitoring unavailable</div><div class="badge-count">The live feed could not be loaded.</div></div>';
                }
            });
    }

    refreshLiveMonitoring();
    setInterval(refreshLiveMonitoring, 15000);

    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>
</body>
</html>