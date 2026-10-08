<x-layout title="My Analytics | Upskill" shell="student" page="analytics">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <div class="analytics-shell">
    @php
        $courseCollection = collect($completionCourses ?? []);
        $weekSeries = $analyticsSeries['week'] ?? [
            'labels' => [],
            'hours' => [],
            'active' => [],
            'streak' => [],
        ];
        $learningProgress = $courseCollection->count()
            ? (int) round($courseCollection->avg(fn ($course) => (int) ($course->percent ?? 0)))
            : 0;
        $weeklyHours = round(collect($weekSeries['hours'] ?? [])->sum(), 1);
        $activeDays = collect($weekSeries['active'] ?? [])->filter()->count();
        $currentStreak = collect($weekSeries['active'] ?? [])->reverse()->takeWhile(fn ($isActive) => (bool) $isActive)->count();
        $assessmentAverage = isset($stats['score_avg']) ? round((float) $stats['score_avg'], 1) : null;
        $summaryStats = [
            ['label' => 'Average Course Progress', 'value' => $learningProgress . '%', 'icon' => 'trend'],
            ['label' => 'Courses Completed', 'value' => (int) ($stats['completed_courses'] ?? 0), 'icon' => 'check'],
            ['label' => 'Assessment Average', 'value' => $assessmentAverage === null ? '—' : number_format($assessmentAverage, 1) . '%', 'icon' => 'award'],
            ['label' => 'Est. Study Time / Week', 'value' => $weeklyHours . 'h', 'icon' => 'clock'],
        ];
        $courseRows = $courseCollection->map(fn ($course) => [
            'name' => $course->title ?? 'Course',
            'percent' => (int) ($course->percent ?? 0),
            'status' => $course->status ?? 'In progress',
        ]);
    @endphp
            <div class="analytics-page">
        <header class="analytics-header ui-page-header student-page-heading">
            <div class="ui-page-header__content">
                <h1 class="ui-page-title">My Analytics</h1>
                <p class="subtitle ui-page-subtitle">Track progress, activity, and assessment outcomes.</p>
            </div>
            <div class="analytics-header-actions">
                <div class="period-switch ui-segmented" role="group" aria-label="Learning trend period">
                    <button class="ui-segmented__button" type="button" data-period="day">Day</button>                    <button class="ui-segmented__button active" type="button" data-period="week">Week</button><button class="ui-segmented__button" type="button" data-period="month">Month</button>
                </div>
                <a href="{{ route('analytics.report') }}" class="download-report-btn ui-button ui-button--primary" title="Download your learning analytics report as a PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 21h16"/></svg>
                    Download Report
                </a>
            </div>
        </header>
        <section class="stats-card ui-stat-card" aria-label="Summary statistics">
            @foreach ($summaryStats as $stat)
                <div class="stat">
                    <span class="stat-icon" aria-hidden="true">
                        @if ($stat['icon'] === 'trend')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/><path d="M16 7h5v5"/></svg>
                        @elseif ($stat['icon'] === 'check')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        @elseif ($stat['icon'] === 'award')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="m9 12-1 9 4-2 4 2-1-9"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        @endif
                    </span>
                    <span><strong class="stat-value">{{ $stat['value'] }}</strong><small class="stat-label">{{ $stat['label'] }}</small></span>
                </div>
            @endforeach
        </section>
        <section class="analytics-grid" aria-label="Analytics details">
            <article class="analytics-card ui-card-surface">
                <div class="card-header ui-card-header"><div class="ui-card-header__content"><h2 class="ui-section-title" id="learning-trend-title">Learning Trend</h2><p>Completed lesson time estimate</p></div><span class="period-badge ui-badge" id="learning-trend-period">WEEK</span></div>
                <div class="chart-wrap"><canvas id="learning-trend-chart"></canvas></div>
            </article>
            <article class="analytics-card ui-card-surface">
                <div class="card-header ui-card-header"><div class="ui-card-header__content"><h2 class="ui-section-title">Assessment performance</h2><p>Average score across your quiz attempts</p></div><span class="assessment-percent">%</span></div>
                <div class="assessment-row"><div class="assessment-label"><span>Quizzes</span><span>{{ $assessmentAverage === null ? 'No attempts' : number_format($assessmentAverage, 1) . '%' }}</span></div><div class="progress-track ui-progress-track"><div class="progress-fill ui-progress-fill" style="width:{{ min(100, max(0, $assessmentAverage ?? 0)) }}%"></div></div></div>
                <div class="assessment-stats">
                    <div class="mini-stat"><label>Competencies mastered</label><strong>{{ $stats['competencies_mastered'] ?? 0 }}</strong></div><div class="mini-stat"><label>Active days this week</label><strong>{{ $activeDays }}</strong></div>
                    <div class="mini-stat"><label>Current weekly streak</label><strong>{{ $currentStreak }}d</strong></div><div class="mini-stat"><label>Estimated lesson time this week</label><strong>{{ $weeklyHours }}h</strong></div>
                </div>
            </article>
        </section>
        <section class="course-card ui-card-surface" aria-labelledby="course-progress-title">
            <div class="course-header ui-card-header"><div class="ui-card-header__content"><h2 class="ui-section-title" id="course-progress-title">Course progress</h2><p>Progress across your enrolled courses</p></div><span class="course-count ui-badge">{{ $courseRows->count() }} courses</span></div>
            <table class="course-table ui-table"><thead><tr><th>Course</th><th>Progress</th><th>Status</th></tr></thead><tbody>
                @forelse ($courseRows as $course)
                    <tr>
                        <td>{{ $course['name'] }}</td>
                        <td><div class="course-progress"><span class="progress-track ui-progress-track"><span class="progress-fill ui-progress-fill" style="width:{{ $course['percent'] }}%"></span></span><span class="course-percent">{{ $course['percent'] }}%</span></div></td>
                        <td class="course-status-cell"><span class="ui-badge {{ $course['status'] === 'Complete' ? 'status-complete ui-badge--success' : ($course['status'] === 'Awaiting verification' ? 'status-review ui-badge--warning' : 'status-progress') }}">{{ $course['status'] }}</span></td>
                    </tr>
                @empty
                    <tr><td class="empty-courses ui-empty-state" colspan="3">Enroll in a course to see your progress here.</td></tr>
                @endforelse
            </tbody></table>
        </section>
            </div>
    </div>
    <script>
        var analyticsSeries = @json($analyticsSeries ?? []);
        var analyticsTokens = getComputedStyle(document.documentElement);
        var analyticsFontSize = parseFloat(document.documentElement.style.fontSize || getComputedStyle(document.documentElement).fontSize)
            * parseFloat(analyticsTokens.getPropertyValue('--text-xs'));
        var learningTrendChart = new Chart(document.getElementById('learning-trend-chart'), {
            type: 'line',
            data: { labels: @json($weekSeries['labels'] ?? []), datasets: [{ label: 'Estimated hours', data: @json($weekSeries['hours'] ?? []), borderColor: analyticsTokens.getPropertyValue('--ui-brand-blue').trim(), backgroundColor: analyticsTokens.getPropertyValue('--ui-brand-blue-soft').trim(), fill: true, tension: .38, pointRadius: 4, pointHoverRadius: 5, pointBackgroundColor: analyticsTokens.getPropertyValue('--ui-brand-blue').trim(), pointBorderColor: analyticsTokens.getPropertyValue('--ui-surface').trim(), pointBorderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { displayColors: false } }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: analyticsTokens.getPropertyValue('--ui-text-muted').trim(), font: { size: analyticsFontSize } } }, y: { min: 0, suggestedMax: 2, ticks: { stepSize: 1, color: analyticsTokens.getPropertyValue('--ui-text-muted').trim(), font: { size: analyticsFontSize } }, grid: { color: analyticsTokens.getPropertyValue('--ui-border').trim() }, border: { display: false } } } }
        });
        document.querySelectorAll('[data-period]').forEach(function (button) {
            button.addEventListener('click', function () {
                var series = analyticsSeries[button.dataset.period];
                if (!series) return;
                document.querySelectorAll('[data-period]').forEach(function (item) { item.classList.remove('active'); });
                button.classList.add('active');
                learningTrendChart.data.labels = series.labels;
                learningTrendChart.data.datasets[0].data = series.hours;
                learningTrendChart.options.scales.y.suggestedMax = Math.max(1, ...series.hours);
                document.getElementById('learning-trend-period').textContent = button.dataset.period.toUpperCase();
                learningTrendChart.update();
            });
        });
    </script>
</x-layout>
