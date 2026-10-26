
<x-admin.layout title="Upskill – Analytics Report" page="admin-analytics-report" body-class="analytics-shell">
    <x-slot:head>
        {{-- Browser tab icon (favicon) --}}
            <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">

            @include('components.analytics-design')
    </x-slot:head>
{{-- ════════════════════════════════════ TOP NAV ══════════════════════════════════ --}}
<div class="layout">

{{-- ════════════════════════════════════ SIDEBAR ══════════════════════════════════ --}}
{{-- Identical to the other admin pages — active pill sits on "Report" --}}
{{-- ════════════════════════════════════ MAIN CONTENT ═════════════════════════════ --}}
<main class="main">
    <div class="analytics-page">
        <header class="page-header">
            <div>
                <h1 class="page-title">Platform Analytics</h1>
                <p class="page-subtitle">Monitor learner engagement, course performance, demographics, and credential outcomes.</p>
            </div>
            <form class="header-actions analytics-filters" action="{{ route('admin.report') }}" method="GET">
                <div class="segmented" role="group" aria-label="Time period">
                    <button class="segment {{ $period === 'all' ? 'active' : '' }}" type="submit" name="period" value="all">All Time</button>
                    <button class="segment {{ $period === 'year' ? 'active' : '' }}" type="submit" name="period" value="year">This Year</button>
                    <button class="segment {{ $period === 'month' ? 'active' : '' }}" type="submit" name="period" value="month">This Month</button>
                    <button class="segment {{ $period === 'week' ? 'active' : '' }}" type="submit" name="period" value="week">This Week</button>
                </div>
                <div class="filter-actions">
                    <select class="filter-select" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All Categories</option>@foreach ($categoryNames as $categoryName)<option value="{{ $categoryName }}" @selected($filters['category'] === $categoryName)>{{ $categoryName }}</option>@endforeach</select>
                    <select class="filter-select" name="course" aria-label="Course" onchange="this.form.submit()"><option value="">All Courses</option>@foreach ($courseOptions as $courseOption)<option value="{{ $courseOption->id }}" @selected((string) $filters['course'] === (string) $courseOption->id)>{{ $courseOption->title }}</option>@endforeach</select>
                    <a class="download-report" href="{{ route('admin.report.download', request()->only(['period', 'activity', 'category', 'course'])) }}" title="Download this filtered platform analytics report as a PDF">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 21h16"/></svg>
                        Download Report (PDF)
                    </a>
                </div>
            </form>
        </header>

        <section class="stats-grid" aria-label="Key performance indicators">
            @php
                $kpis = [
                    ['label' => 'Total Learners', 'value' => number_format($stats['total_students']), 'trendKey' => 'learners', 'icon' => 'users'],
                    ['label' => 'Active Learners', 'value' => number_format($stats['active_students']), 'trendKey' => 'active_learners', 'icon' => 'activity'],
                    ['label' => 'Total Courses', 'value' => number_format($stats['total_courses']), 'trendKey' => 'courses', 'icon' => 'book'],
                    ['label' => 'Course Completions', 'value' => number_format($stats['completions']), 'trendKey' => 'completions', 'icon' => 'check'],
                    ['label' => 'Credentials Issued', 'value' => number_format($stats['credentials']), 'trendKey' => 'credentials', 'icon' => 'award'],
                    ['label' => 'Average Score', 'value' => $stats['course_score_avg'] === null ? '—' : $stats['course_score_avg'] . '%', 'trendKey' => 'assessment_score', 'icon' => 'chart'],
                ];
            @endphp
            @foreach ($kpis as $kpi)
                <article class="stat-card">
                    <div class="stat-heading">
                    <div class="stat-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                @if ($kpi['icon'] === 'users')
                                    <circle cx="8" cy="7.5" r="3.5"/><path d="M1.5 20c.35-3.7 2.65-5.7 6.5-5.7s6.15 2 6.5 5.7zM16 4.4a3.5 3.5 0 0 1 0 6.2M17 14.5c2.75.5 4.25 2.3 4.6 5.5h-5.3c-.12-2.2-.85-4-2.2-5.2.85-.3 1.8-.4 2.9-.3z"/>
                                @elseif ($kpi['icon'] === 'activity')
                                    <path d="M2 13h4.2l2.2-5.8a1 1 0 0 1 1.9.05l3 8 2-5.1a1 1 0 0 1 1-.63H22v2h-5l-2.8 7.1a1 1 0 0 1-1.9-.04l-3-8-1.8 4.5a1 1 0 0 1-.93.63H2z"/>
                                @elseif ($kpi['icon'] === 'book')
                                    <path d="M5 3h14a2 2 0 0 1 2 2v15H7a3 3 0 0 0-3 3V5a2 2 0 0 1 1-2zm1 2a1 1 0 0 0-1 1v12.2A4.9 4.9 0 0 1 7 17h12V5z"/>
                                @elseif ($kpi['icon'] === 'check')
                                    <circle cx="12" cy="12" r="10"/><path d="m7 12.2 3.2 3.2L17.5 8"/>
                                @elseif ($kpi['icon'] === 'award')
                                    <path d="m7.1 13.5-1 8 5.9-3.1 5.9 3.1-1-8A7 7 0 1 0 7.1 13.5zM12 3.5a4 4 0 1 1 0 8 4 4 0 0 1 0-8z"/>
                                @else
                                    <path d="M3 19h4v-6H3zm7 0h4V8h-4zm7 0h4V4h-4z"/>
                                @endif
                            </svg>
                        </div>
                        <div class="stat-lbl">{{ $kpi['label'] }}</div>
                    </div>
                    <div class="stat-val">{{ $kpi['value'] }}</div>
                    @php
                        $trend = $stats['trends'][$kpi['trendKey']];
                        $trendValue = $trend['current'] !== null && $trend['previous'] !== null
                            ? round($trend['current'] - $trend['previous'], 1)
                            : null;
                        $trendClass = $trendValue === null ? 'stat-trend-neutral' : ($trendValue > 0 ? 'stat-trend-up' : ($trendValue < 0 ? 'stat-trend-down' : 'stat-trend-neutral'));
                    @endphp
                    <div class="stat-context">
                        @if ($trendValue === null)
                            <span class="stat-trend stat-trend-neutral">—</span> No previous data
                        @else
                            <span class="stat-trend {{ $trendClass }}">
                                @if ($trendValue !== 0)
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="{{ $trendValue > 0 ? 'M8 13V3m-4 4 4-4 4 4' : 'M8 3v10m-4-4 4 4 4-4' }}"/></svg>
                                @endif
                                {{ $trendValue > 0 ? '+' : '' }}{{ number_format($trendValue, $kpi['trendKey'] === 'assessment_score' ? 1 : 0) }}
                            </span>
                            {{ $trendPeriodLabel }}
                        @endif
                    </div>
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
                <svg class="activity-chart" viewBox="0 0 760 230" preserveAspectRatio="none" role="img" aria-label="Learner activity bar chart">
                    <path class="grid-line" d="M48 25H744M48 70H744M48 115H744M48 160H744M48 205H744"/>
                    @foreach($activity['values'] as $index => $value)
                        @php
                            $barHeight = $value / $activity['scale']['max'] * 180;
                        @endphp
                        <rect class="activity-bar" x="{{ 63 + $index * 77 }}" y="{{ 205 - $barHeight }}" width="44" height="{{ $barHeight }}" rx="4"><title>{{ $activity['labels'][$index] }}: {{ number_format($value) }}</title></rect>
                    @endforeach
                    <g class="axis-label"><text x="12" y="29">{{ $activity['scale']['max'] }}</text><text x="12" y="74">{{ $activity['scale']['high'] }}</text><text x="12" y="119">{{ $activity['scale']['mid'] }}</text><text x="12" y="164">{{ $activity['scale']['low'] }}</text><text x="20" y="209">0</text>@foreach ($activity['labels'] as $index => $label)<text x="{{ 85 + $index * 77 }}" y="224" text-anchor="middle">{{ $label }}</text>@endforeach</g>
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

        <section class="analytics-overview" aria-label="Demographics and performance analytics">
            <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Learner Demographics</h2><p class="card-subtitle">Age and gender distribution for the selected filters</p></div><p class="demographics-total">Course enrollments in scope: <strong>{{ number_format($demographics['total']) }}</strong></p></div>
                @if($demographics['total'] > 0)
                    @php
                        $demographicCharts = [
                            ['title' => 'Age distribution', 'values' => $demographics['age'], 'colors' => ['#e07048', '#2c9a68', '#f0bd3b', '#9b59b6', '#4b9ca8', '#d7785b', '#8994a5']],
                            ['title' => 'Gender distribution', 'values' => $demographics['gender'], 'colors' => ['#e07048', '#2c9a68', '#d9b72c', '#9b59b6', '#e07048']],
                        ];
                    @endphp
                    <div class="demographics-grid">
                        @foreach($demographicCharts as $chart)
                            @php
                                $chartTotal = array_sum($chart['values']);
                                $chartAngle = 0;
                                $chartSlices = [];
                                foreach (array_values($chart['values']) as $index => $count) {
                                    if ($count === 0) {
                                        continue;
                                    }
                                    $nextAngle = $chartAngle + ($count / $chartTotal * 360);
                                    $chartSlices[] = $chart['colors'][$index]." {$chartAngle}deg {$nextAngle}deg";
                                    $chartAngle = $nextAngle;
                                }
                                $pieBackground = implode(', ', $chartSlices);
                                $chartDescription = collect($chart['values'])
                                    ->filter(fn (int $count): bool => $count > 0)
                                    ->map(fn (int $count, string $label): string => $label.': '.number_format($count).' ('.(int) round($count / $chartTotal * 100).'%)')
                                    ->implode(', ');
                            @endphp
                            <section class="demographics-group" aria-label="{{ $chart['title'] }}">
                                <h3>{{ $chart['title'] }}</h3>
                                <div class="demographics-visual">
                                    <div class="demographics-pie" role="img" aria-label="{{ $chart['title'] }}: {{ $chartDescription }}" style="background:conic-gradient({{ $pieBackground }})"></div>
                                    <div class="demographics-legend">
                                        @foreach($chart['values'] as $label => $count)
                                            @if($count > 0)
                                                <div class="demographics-row">
                                                    <span class="demographics-label"><span class="demographics-swatch" style="background:{{ $chart['colors'][$loop->index] }}"></span>{{ $label }}</span>
                                                    <span class="demographics-value">{{ number_format($count) }}</span>
                                                    <span class="demographics-percent">{{ (int) round($count / $chartTotal * 100) }}%</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <p class="demographics-empty">No enrolled learner demographics are available for the selected filters.</p>
                @endif
            </article>
            <div class="analytics-side-stack">
                <article class="card">
                    <div class="card-heading"><div><h2 class="card-title">Assessment Performance</h2><p class="card-subtitle">Quiz and final exam scores in the selected scope</p></div><div class="assessment-metric"><small>Pass Rate</small><strong>{{ $assessment['pass_rate'] === null ? '—' : $assessment['pass_rate'] . '%' }}</strong></div></div>
                    <div class="assessment-bar"><div class="assessment-bar-head"><span>Average quiz score</span><strong>{{ $assessment['average'] === null ? 'No attempts' : $assessment['average'] . '%' }}</strong></div><div class="progress-track"><div class="progress-fill" style="width:{{ $assessment['average'] ?? 0 }}%"></div></div></div>
                    <div class="assessment-bar"><div class="assessment-bar-head"><span>Average final exam score</span><strong>{{ $assessment['final_exam_average'] === null ? 'No attempts' : $assessment['final_exam_average'] . '%' }}</strong></div><div class="progress-track"><div class="progress-fill" style="width:{{ $assessment['final_exam_average'] ?? 0 }}%"></div></div></div>
                    <div class="metric-grid"><div class="metric-box"><strong>{{ number_format($assessment['total']) }}</strong><span>Scored Attempts</span></div><div class="metric-box"><strong>{{ $assessment['highest'] === null ? '—' : $assessment['highest'] . '%' }}</strong><span>Highest Score</span></div><div class="metric-box"><strong>{{ $assessment['lowest'] === null ? '—' : $assessment['lowest'] . '%' }}</strong><span>Lowest Score</span></div></div>
                </article>
                <article class="card">
                <div class="card-heading"><div><h2 class="card-title">Credential &amp; Badge Analytics</h2><p class="card-subtitle">Credential outcomes across the platform</p></div></div>
                <div class="credential-grid"><div class="credential-metric"><strong>{{ number_format($credentialMetrics['certificates']) }}</strong><span>Active Certificates</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['badges']) }}</strong><span>Badges Issued</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['verified']) }}</strong><span>Verifiable Certificates</span></div><div class="credential-metric"><strong>{{ number_format($credentialMetrics['pending']) }}</strong><span>Courses Awaiting Approval</span></div></div>
                <div class="credential-heading">Credentials Issued Over Time</div><svg class="credential-chart" viewBox="0 0 760 230" preserveAspectRatio="none" aria-label="Credentials issued line chart"><path class="grid-line" d="M48 72H744M48 42H744"/><polygon class="chart-area" points="48,90 {{ $credentialActivity['points'] }} 744,90"/><polyline class="chart-line" points="{{ $credentialActivity['points'] }}"/></svg>
                </article>
            </div>
        </section>

        <section class="dashboard-grid course-performance-grid" aria-label="Course performance">
            <article class="card table-card">
                <div class="card-heading"><div><h2 class="card-title">Course Performance</h2><p class="card-subtitle">Average learner progress and quiz score by course</p></div></div>
                <table class="data-table"><thead><tr><th>Course</th><th>Enrolled</th><th>Avg. Progress</th><th>Avg. Score</th></tr></thead><tbody>
                    @foreach ($coursePerformance as $course)
                        <tr><td class="course-name">{{ $course->title }}</td><td>{{ $course->enrolled }}</td><td><div class="progress-cell"><div class="progress-track"><div class="progress-fill" style="width:{{ $course->completion }}%"></div></div><span>{{ $course->completion }}%</span></div></td><td class="score">{{ $course->score === null ? '—' : $course->score . '%' }}</td></tr>
                    @endforeach
                </tbody></table>
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
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-admin.layout>