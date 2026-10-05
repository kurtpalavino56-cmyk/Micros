<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Analytics Report</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        /* ─── Tokens (shared with the other admin pages) ─────────── */
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
        .layout {
            display: flex;
            min-height: calc(100vh - var(--topbar-h));
        }

        /* ════════════════════════════════════════════════════════════
           SIDEBAR — identical to the other admin pages.
           Active pill sits on "Report".
           ════════════════════════════════════════════════════════════ */
       

        /* ─── ANALYTICS CONTENT ─────────────────────────────────── */
        .main { margin-left: var(--sidebar-w); flex: 1; padding: 28px 30px 44px; }
        .analytics-page { max-width: 1480px; margin: 0 auto; }
        .page-header { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; margin-bottom:20px; }
        .page-title { color:#101d42; font-size:1.55rem; line-height:1.2; font-weight:800; letter-spacing:-.02em; }
        .page-subtitle { color:#69758c; font-size:.86rem; margin-top:7px; }
        .header-actions { display:flex; flex-direction:column; align-items:flex-end; gap:10px; width:min(100%, 583px); }
        .download-report { display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid #1b317f;border-radius:7px;background:#fff;color:#173b99;font:700 .74rem 'Segoe UI',sans-serif;text-decoration:none; }
        .download-report:hover { background:#edf2ff; }
        .segmented, .filter-row { display:flex; gap:5px; }
        .filter-row { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); width:100%; }
        .segmented { background:#e9eef9; border:1px solid #dce4f3; border-radius:8px; padding:3px; }
        .segment, .filter-select { border:0; background:transparent; color:#5c6880; font:600 .73rem 'Segoe UI',sans-serif; cursor:pointer; }
        .segment { padding:7px 11px; border-radius:6px; }
        .segment.active { background:#fff; color:#173b99; box-shadow:0 1px 3px rgba(21,47,112,.1); }
        .filter-select { width:100%; min-width:0; max-width:100%; padding:7px 25px 7px 10px; border:1px solid #dce3f0; border-radius:7px; background:#fff; appearance:auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .stats-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:13px; margin-bottom:18px; }
        .stat-card, .card { background:#fff; border:1px solid #e0e6f0; border-radius:12px; box-shadow:0 2px 8px rgba(25,47,96,.04); }
        .stat-card { padding:15px; min-width:0; }
        .stat-top { display:flex; justify-content:space-between; align-items:center; gap:6px; margin-bottom:14px; }
        .stat-icon { width:30px; height:30px; border-radius:8px; display:grid; place-items:center; color:#1f4bb7; background:#e9efff; }
        .stat-icon.yellow { color:#866508; background:#fff5cf; }
        .stat-icon svg { width:16px; height:16px; }
        .stat-label { color:#68758c; font-size:.73rem; line-height:1.25; min-height:30px; }
        .stat-value { color:#142653; font-size:1.42rem; line-height:1.1; font-weight:800; margin:5px 0 6px; }
        .stat-trend { color:#26815a; font-size:.68rem; font-weight:700; white-space:nowrap; }
        .dashboard-grid { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(300px,.85fr); gap:16px; margin-bottom:16px; }
        .dashboard-grid.equal { grid-template-columns:minmax(0,1fr) minmax(0,1fr); }
        .card { padding:18px 19px; min-width:0; }
        .card-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px; }
        .card-title { color:#142653; font-size:.96rem; font-weight:800; }
        .card-subtitle { color:#7b879a; font-size:.72rem; margin-top:4px; }
        .activity-tabs { display:flex; gap:4px; }
        .activity-tab { padding:6px 9px; border:1px solid transparent; border-radius:6px; background:transparent; color:#7a8698; font:600 .68rem 'Segoe UI',sans-serif; cursor:pointer; }
        .activity-tab.active { color:#1c49ae; background:#edf2ff; border-color:#dce6ff; }
        .activity-chart { width:100%; height:205px; display:block; }
        .axis-label { fill:#99a4b5; font:10px 'Segoe UI',sans-serif; }
        .grid-line { stroke:#edf1f7; stroke-width:1; }
        .chart-line { fill:none; stroke:#2858c5; stroke-width:3; stroke-linecap:round; stroke-linejoin:round; }
        .chart-area { fill:#e9efff; opacity:.8; }
        .donut-wrap { display:flex; align-items:center; gap:24px; min-height:185px; }
        .donut { width:148px; height:148px; flex:none; transform:rotate(-90deg); }
        .donut-track { fill:none; stroke:#edf0f5; stroke-width:17; }
        .donut-complete { fill:none; stroke:#2858c5; stroke-width:17; stroke-dasharray:127 327; }
        .donut-progress { fill:none; stroke:#d9b72c; stroke-width:17; stroke-dasharray:167 327; stroke-dashoffset:-127; }
        .donut-legend { display:grid; gap:13px; width:100%; }
        .legend-row { display:flex; align-items:center; gap:8px; color:#58657b; font-size:.75rem; }
        .legend-row strong { margin-left:auto; color:#1b2e58; }
        .legend-dot { width:8px; height:8px; border-radius:50%; background:#2858c5; }
        .legend-dot.yellow { background:#d9b72c; } .legend-dot.gray { background:#dfe4ec; }
        .mini-stats { display:grid; grid-template-columns:1fr 1fr; border-top:1px solid #edf0f5; margin-top:4px; padding-top:14px; gap:12px; }
        .mini-label { color:#7a8698; font-size:.69rem; } .mini-value { color:#162b59; font-size:1.15rem; font-weight:800; margin-top:3px; }
        .table-card { margin-bottom:16px; }
        .data-table { width:100%; border-collapse:collapse; font-size:.76rem; }
        .data-table th { text-align:left; color:#8994a5; font-size:.67rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:0 8px 10px; border-bottom:1px solid #e8edf5; }
        .data-table td { color:#334466; padding:11px 8px; border-bottom:1px solid #f0f3f8; vertical-align:middle; }
        .data-table tr:last-child td { border-bottom:0; } .course-name { color:#1c315e; font-weight:700; }
        .progress-cell { display:flex; align-items:center; gap:9px; min-width:130px; }
        .progress-track { height:6px; flex:1; background:#edf1f7; border-radius:4px; overflow:hidden; }
        .progress-fill { height:100%; background:#2d5cc6; border-radius:4px; }
        .score { font-weight:700; color:#1d3f96; }
        .assessment-metric { background:#fff9df; border:1px solid #f2e7ab; border-radius:8px; padding:8px 11px; text-align:right; }
        .assessment-metric small { display:block; color:#89721e; font-size:.64rem; } .assessment-metric strong { display:block; color:#6e5a0d; font-size:1.15rem; margin-top:2px; }
        .assessment-bar { margin:23px 0 31px; } .assessment-bar-head { display:flex; justify-content:space-between; color:#344567; font-size:.75rem; font-weight:700; margin-bottom:8px; }
        .assessment-bar-head strong { color:#244da9; } .assessment-bar .progress-track { height:9px; }
        .metric-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
        .metric-box { border-left:2px solid #dfe7f6; padding-left:10px; } .metric-box strong { display:block; color:#172d5a; font-size:1.1rem; } .metric-box span { color:#8590a0; font-size:.67rem; }
        .competency-list { display:grid; gap:15px; margin:5px 0 18px; } .competency-row { display:grid; grid-template-columns:145px 1fr 35px; align-items:center; gap:12px; color:#50617d; font-size:.74rem; } .competency-row strong { color:#294da5; text-align:right; }
        .card-footer { border-top:1px solid #edf0f5; padding-top:12px; display:flex; justify-content:space-between; gap:12px; color:#78859a; font-size:.71rem; } .card-footer strong { color:#344b78; }
        .credential-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:18px; } .credential-metric { padding:8px 5px; text-align:center; border-right:1px solid #edf0f5; } .credential-metric:last-child { border-right:0; } .credential-metric strong { display:block; color:#18346e; font-size:1.2rem; } .credential-metric span { color:#7e8999; font-size:.67rem; }
        .credential-chart { width:100%; height:90px; display:block; } .credential-heading { color:#59677f; font-size:.73rem; font-weight:700; margin-bottom:3px; }
        .attention { background:#fffbea; border:1px solid #f3e7b1; border-radius:12px; padding:18px 19px; } .attention .card-title { color:#3d3821; } .attention-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:15px; } .attention-item { display:flex; gap:10px; align-items:center; border-left:1px solid #eee2ad; padding-left:13px; } .warning-icon { width:28px; height:28px; flex:none; border-radius:7px; display:grid; place-items:center; color:#886a08; background:#fff1b7; } .warning-icon svg { width:15px; height:15px; } .attention-number { color:#26385e; font-size:1.3rem; font-weight:800; line-height:1; } .attention-label { color:#6c684f; font-size:.69rem; line-height:1.25; margin-top:3px; }
        @media (max-width:1200px) { .stats-grid { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:900px) { .main { padding:22px 18px 35px; } .page-header { flex-direction:column; } .header-actions { align-items:flex-start; width:100%; } .dashboard-grid, .dashboard-grid.equal { grid-template-columns:1fr; } .attention-grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:600px) { .stats-grid { grid-template-columns:repeat(2,1fr); } .filter-row, .segmented { flex-wrap:wrap; } .filter-row { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); } .card-heading { flex-direction:column; } .activity-tabs { align-self:flex-start; } .donut-wrap { gap:12px; } .donut { width:125px; height:125px; } .data-table { min-width:620px; } .table-card { overflow-x:auto; } .attention-grid { grid-template-columns:1fr; } .credential-grid { grid-template-columns:repeat(2,1fr); } .credential-metric:nth-child(2) { border-right:0; } }
    </style>
    @include('components.analytics-design')
</head>
<body class="analytics-shell">
{{-- ════════════════════════════════════ TOP NAV ══════════════════════════════════ --}}
@include('components.authenticated-topbar', ['user' => auth()->user()])
<div class="layout">

{{-- ════════════════════════════════════ SIDEBAR ══════════════════════════════════ --}}
{{-- Identical to the other admin pages — active pill sits on "Report" --}}
{{-- ════════════════════════════════════ MAIN CONTENT ═════════════════════════════ --}}
<main class="main">
    <div class="analytics-page">
        <header class="page-header">
            <div>
                <h1 class="page-title">Platform Analytics</h1>
                <p class="page-subtitle">Monitor learner engagement, course performance, competencies, and credential outcomes.</p>
            </div>
            <form class="header-actions analytics-filters" action="{{ route('admin.report') }}" method="GET">
                <div class="segmented" role="group" aria-label="Time period">
                    <button class="segment {{ $period === 'all' ? 'active' : '' }}" type="submit" name="period" value="all">All Time</button>
                    <button class="segment {{ $period === 'year' ? 'active' : '' }}" type="submit" name="period" value="year">This Year</button>
                    <button class="segment {{ $period === 'month' ? 'active' : '' }}" type="submit" name="period" value="month">This Month</button>
                    <button class="segment {{ $period === 'week' ? 'active' : '' }}" type="submit" name="period" value="week">This Week</button>
                </div>
                <div class="filter-row">
                    <select class="filter-select" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All Categories</option>@foreach ($categoryNames as $categoryName)<option value="{{ $categoryName }}" @selected($filters['category'] === $categoryName)>{{ $categoryName }}</option>@endforeach</select>
                    <select class="filter-select" name="course" aria-label="Course" onchange="this.form.submit()"><option value="">All Courses</option>@foreach ($courseOptions as $courseOption)<option value="{{ $courseOption->id }}" @selected((string) $filters['course'] === (string) $courseOption->id)>{{ $courseOption->title }}</option>@endforeach</select>
                </div>
                <a class="download-report" href="{{ route('admin.report.download', request()->only(['period', 'activity', 'category', 'course'])) }}" title="Download this filtered platform analytics report as a PDF">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 21h16"/></svg>
                    Download Report (PDF)
                </a>
            </form>
        </header>

        <section class="stats-grid" aria-label="Key performance indicators">
            @php
                $kpis = [
                    ['label' => 'Total Learners', 'value' => number_format($stats['total_students']), 'trend' => 'Filtered scope', 'icon' => 'users'],
                    ['label' => 'Active Learners', 'value' => number_format($stats['active_students']), 'trend' => $period === 'all' ? 'Last 30 days' : 'Selected period', 'icon' => 'activity', 'yellow' => true],
                    ['label' => 'Total Courses', 'value' => number_format($stats['total_courses']), 'trend' => 'Filtered scope', 'icon' => 'book'],
                    ['label' => 'Course Completions', 'value' => number_format($stats['completions']), 'trend' => $period === 'all' ? 'All time' : 'Selected period', 'icon' => 'check', 'yellow' => true],
                    ['label' => 'Credentials Issued', 'value' => number_format($stats['credentials']), 'trend' => $period === 'all' ? 'All time' : 'Selected period', 'icon' => 'award'],
                    ['label' => 'Average Assessment Score', 'value' => $stats['course_score_avg'] === null ? '—' : $stats['course_score_avg'] . '%', 'trend' => 'Scored quiz attempts', 'icon' => 'chart', 'yellow' => true],
                ];
            @endphp
            @foreach ($kpis as $kpi)
                <article class="stat-card">
                    <div class="stat-top">
                    <div class="stat-icon {{ !empty($kpi['yellow']) ? 'yellow' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                @if ($kpi['icon'] === 'users')
                                    <circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.4-3.4 2.7-5.4 6.5-5.4s6.1 2 6.5 5.4M16 5.2a3.5 3.5 0 0 1 0 6.8M17 14.8c2.6.5 4 2.2 4.5 5.2"/>
                                @elseif ($kpi['icon'] === 'activity')
                                    <path d="M3 12h4l3-7 4 14 3-7h4"/>
                                @elseif ($kpi['icon'] === 'book')
                                    <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 0 4 22zM4 4.5v15A2.5 2.5 0 0 1 6.5 17H20"/>
                                @elseif ($kpi['icon'] === 'check')
                                    <circle cx="12" cy="12" r="9"/><path d="m7.5 12 3 3 6-6"/>
                                @elseif ($kpi['icon'] === 'award')
                                    <circle cx="12" cy="8" r="5"/><path d="m8.5 12-1 9 4.5-2.5 4.5 2.5-1-9"/>
                                @else
                                    <path d="M4 19V5m0 14h16M7 15l4-4 3 2 6-7"/>
                                @endif
                            </svg>
                        </div>
                        <span class="stat-trend">{{ $kpi['trend'] }}</span>
                    </div>
                    <div class="stat-label">{{ $kpi['label'] }}</div>
                    <div class="stat-value">{{ $kpi['value'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-grid" aria-label="Learner analytics">
            <article class="card">
                <div class="card-heading">
                    <div><h2 class="card-title">Learner Activity &amp; Engagement</h2><p class="card-subtitle">Total activity over time</p></div>
                    <div class="activity-tabs" role="group" aria-label="Activity metric">
                        <button class="activity-tab {{ $activityMode === 'enrollments' ? 'active' : '' }}" type="button" data-activity="enrollments">Enrollments</button><button class="activity-tab {{ $activityMode === 'active' ? 'active' : '' }}" type="button" data-activity="active">Active Learners</button><button class="activity-tab {{ $activityMode === 'completions' ? 'active' : '' }}" type="button" data-activity="completions">Completions</button>
                    </div>
                </div>
                <svg class="activity-chart" viewBox="0 0 760 230" preserveAspectRatio="none" role="img" aria-label="Learner activity line chart">
                    <path class="grid-line" d="M48 25H744M48 70H744M48 115H744M48 160H744M48 205H744"/><polygon class="chart-area" points="48,205 {{ $activity['points'] }} 744,205"/><polyline class="chart-line" points="{{ $activity['points'] }}"/>
                    <g class="axis-label"><text x="12" y="29">{{ $activity['scale']['max'] }}</text><text x="12" y="74">{{ $activity['scale']['high'] }}</text><text x="12" y="119">{{ $activity['scale']['mid'] }}</text><text x="12" y="164">{{ $activity['scale']['low'] }}</text><text x="20" y="209">0</text>@foreach ($activity['labels'] as $index => $label)<text x="{{ 40 + $index * 87 }}" y="224">{{ $label }}</text>@endforeach</g>
                </svg>
            </article>
            <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Learner Progress Distribution</h2><p class="card-subtitle">Unique learners across the selected courses</p></div></div>
                <div class="donut-wrap">
                    <svg class="donut" viewBox="0 0 120 120" aria-label="Progress distribution donut chart"><circle class="donut-track" cx="60" cy="60" r="52"/><circle class="donut-complete" cx="60" cy="60" r="52" style="stroke-dasharray:{{ $progress['completed'] * 3.27 }} 327"/><circle class="donut-progress" cx="60" cy="60" r="52" style="stroke-dasharray:{{ $progress['in_progress'] * 3.27 }} 327;stroke-dashoffset:-{{ $progress['completed'] * 3.27 }}"/></svg>
                    <div class="donut-legend"><div class="legend-row"><i class="legend-dot"></i>Completed <strong>{{ $progress['completed'] }}%</strong></div><div class="legend-row"><i class="legend-dot yellow"></i>In Progress <strong>{{ $progress['in_progress'] }}%</strong></div><div class="legend-row"><i class="legend-dot gray"></i>Not Started <strong>{{ $progress['not_started'] }}%</strong></div></div>
                </div>
                <div class="mini-stats"><div><div class="mini-label">Unique Learners</div><div class="mini-value">{{ number_format($stats['total_students']) }}</div></div><div><div class="mini-label">Average Learner Progress</div><div class="mini-value">{{ $stats['average_completion'] }}%</div></div></div>
            </article>
        </section>

        <section class="dashboard-grid equal" aria-label="Performance analytics">
            <article class="card table-card">
                <div class="card-heading"><div><h2 class="card-title">Course Performance</h2><p class="card-subtitle">Average learner progress and quiz score by course</p></div></div>
                <table class="data-table"><thead><tr><th>Course</th><th>Enrolled</th><th>Avg. Progress</th><th>Avg. Score</th></tr></thead><tbody>
                    @foreach ($coursePerformance as $course)
                        <tr><td class="course-name">{{ $course->title }}</td><td>{{ $course->enrolled }}</td><td><div class="progress-cell"><div class="progress-track"><div class="progress-fill" style="width:{{ $course->completion }}%"></div></div><span>{{ $course->completion }}%</span></div></td><td class="score">{{ $course->score === null ? '—' : $course->score . '%' }}</td></tr>
                    @endforeach
                </tbody></table>
            </article>
            <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Assessment Performance</h2><p class="card-subtitle">Quiz attempt scores in the selected scope</p></div><div class="assessment-metric"><small>Pass Rate</small><strong>{{ $assessment['pass_rate'] === null ? '—' : $assessment['pass_rate'] . '%' }}</strong></div></div>
                <div class="assessment-bar"><div class="assessment-bar-head"><span>Average quiz score</span><strong>{{ $assessment['average'] === null ? 'No attempts' : $assessment['average'] . '%' }}</strong></div><div class="progress-track"><div class="progress-fill" style="width:{{ $assessment['average'] ?? 0 }}%"></div></div></div>
                <div class="metric-grid"><div class="metric-box"><strong>{{ number_format($assessment['total']) }}</strong><span>Scored Attempts</span></div><div class="metric-box"><strong>{{ $assessment['highest'] === null ? '—' : $assessment['highest'] . '%' }}</strong><span>Highest Score</span></div><div class="metric-box"><strong>{{ $assessment['lowest'] === null ? '—' : $assessment['lowest'] . '%' }}</strong><span>Lowest Score</span></div></div>
            </article>
        </section>

        <section class="dashboard-grid equal" aria-label="Competency and credential analytics">
            <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Competency Achievement</h2><p class="card-subtitle">Mastery rate by competency area</p></div></div>
                <div class="competency-list">@foreach ($competencies as $competency)<div class="competency-row"><span>{{ $competency->name }}</span><div class="progress-track"><div class="progress-fill" style="width:{{ $competency->mastery }}%"></div></div><strong>{{ $competency->mastery }}%</strong></div>@endforeach</div>
                <div class="card-footer"><span>Linked Competencies: <strong>{{ $totalCompetencies }}</strong></span><span>Highest mastery: <strong>{{ optional($competencies->first())->name ?? 'No data' }}{{ $competencies->isNotEmpty() ? ' (' . $competencies->first()->mastery . '%)' : '' }}</strong></span></div>
            </article>
            <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Credential &amp; Badge Analytics</h2><p class="card-subtitle">Credential outcomes across the platform</p></div></div>
                <div class="credential-grid"><div class="credential-metric"><strong>{{ number_format($credentialMetrics['certificates']) }}</strong><span>Active Certificates</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['badges']) }}</strong><span>Badges Issued</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['verified']) }}</strong><span>Verifiable Certificates</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['pending']) }}</strong><span>Courses Awaiting Approval</span></div></div>
                <div class="credential-heading">Credentials Issued Over Time</div><svg class="credential-chart" viewBox="0 0 760 230" preserveAspectRatio="none" aria-label="Credentials issued line chart"><path class="grid-line" d="M48 72H744M48 42H744"/><polygon class="chart-area" points="48,90 {{ $credentialActivity['points'] }} 744,90"/><polyline class="chart-line" points="{{ $credentialActivity['points'] }}"/></svg>
            </article>
        </section>

        <section class="attention" aria-labelledby="attention-title"><div class="card-heading"><div><h2 class="card-title" id="attention-title">Requires Attention</h2><p class="card-subtitle">Areas that may need immediate focus</p></div></div><div class="attention-grid">@foreach ($attention as $index => $attentionCount)<div class="attention-item"><span class="warning-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2.5 20h19L12 3zM12 9v5m0 3h.01"/></svg></span><div><div class="attention-number">{{ $attentionCount }}</div><div class="attention-label">{{ ['Courses with average progress below 50%', 'Competency categories below 40% mastery', 'Learners with enrollment updates older than 14 days', 'Quizzes with pass rates below 60%'][$index] }}</div></div></div>@endforeach</div></section>
    </div>

</main>
</div>{{-- /layout --}}

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────── --}}
<script>
    document.querySelectorAll('.activity-tab').forEach(function (button) {
        button.addEventListener('click', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('activity', button.dataset.activity);
            window.location.assign(url.toString());
        });
    });
</script>


{{-- ── Back to top (appears on long pages) ── --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    #back-to-top-btn svg{width:22px;height:22px;}
</style>
<script>
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

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
    @include('components.admin-sidebar')
    
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
