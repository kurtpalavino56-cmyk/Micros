<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Analytics | Upskill</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --blue: #1232D4;
            --navy: #13176b;
            --gold: #dba617;
            --text: #17233C;
            --muted: #718096;
            --page: #F7F9FC;
            --card: #FFFFFF;
            --border: #E2E8F0;
            --track: #EDF1F7;
            --green: #159A68;
            --yellow: #FFF4B8;
            --soft-blue: #EEF1FF;
        }
        * { box-sizing: border-box; }
        body { margin: 0; overflow-x: hidden; background: var(--page); color: var(--text); font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        button { font: inherit; }
        a { color: inherit; text-decoration: none; }
        .layout { display: grid; min-height: calc(100vh - 70px); align-items: stretch; }
        .analytics-page { width: 100%; max-width: none; margin: 0; padding: 0; }
        .analytics-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
        .eyebrow { margin: 0 0 8px; color: var(--blue); font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--text); font-size: 32px; line-height: 1.15; letter-spacing: -.02em; }
        .subtitle { margin: 8px 0 0; color: var(--muted); font-size: 14px; }
        .period-switch { display: flex; gap: 4px; padding: 4px; background: var(--card); border: 1px solid var(--border); border-radius: 999px; }
        .period-switch button { border: 0; border-radius: 999px; padding: 8px 16px; background: #fff; color: var(--muted); cursor: pointer; font-size: 12px; font-weight: 700; }
        .period-switch button.active { background: var(--blue); color: #fff; }
        .stats-card, .analytics-card, .course-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 8px rgba(23, 35, 60, .03); }
        .stats-card { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-top: 16px; overflow: hidden; }
        .stat { display: flex; align-items: center; gap: 10px; min-width: 0; padding: 14px clamp(12px, 1.5vw, 18px); }
        .stat + .stat { border-left: 1px solid var(--border); }
        .stat-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border-radius: 9px; background: var(--yellow); color: var(--blue); }
        .stat:nth-child(even) .stat-icon { background: var(--soft-blue); }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-value { display: block; color: var(--text); font-size: 23px; font-weight: 800; line-height: 1.1; }
        .stat-label { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; white-space: normal; }
        .analytics-grid { display: grid; grid-template-columns: minmax(0, 1.62fr) minmax(260px, 1fr); align-items: stretch; gap: clamp(12px, 1.5vw, 16px); margin-top: 14px; }
        .analytics-card { min-width: 0; padding: 16px 18px; }
        .card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .card-header h2 { margin: 0; color: var(--text); font-size: 16px; }
        .card-header p { margin: 5px 0 0; color: var(--muted); font-size: 11px; }
        .period-badge { padding: 5px 9px; border-radius: 6px; background: var(--soft-blue); color: var(--blue); font-size: 10px; font-weight: 800; letter-spacing: .06em; }
        .chart-wrap { position: relative; height: clamp(190px, 25vw, 276px); }
        .assessment-percent { color: var(--blue); font-size: 15px; font-weight: 800; }
        .assessment-row { display: flex; min-height: 82px; flex-direction: column; justify-content: center; padding: 10px 0 20px; }
        .assessment-row + .assessment-row { padding-top: 14px; border-top: 1px solid var(--border); }
        .assessment-label { display: flex; justify-content: space-between; gap: 16px; color: var(--text); font-size: 12px; font-weight: 700; }
        .assessment-label span:last-child { color: var(--muted); font-weight: 800; }
        .progress-track { display: block; height: 7px; margin-top: 9px; overflow: hidden; border-radius: 999px; background: var(--track); }
        .progress-fill { display: block; height: 100%; border-radius: inherit; background: var(--gold); }
        .analytics-card:nth-child(2) { display: flex; flex-direction: column; }
        .assessment-stats { display: grid; min-height: 144px; grid-template-columns: repeat(2, 1fr); align-content: space-around; gap: 18px 12px; margin-top: auto; padding-top: 17px; border-top: 1px solid var(--border); }
        .mini-stat label { display: block; color: var(--muted); font-size: 11px; line-height: 1.35; }
        .mini-stat strong { display: block; margin-top: 5px; color: var(--text); font-size: 20px; }
        .course-card { margin-top: 14px; padding: 16px 18px 6px; }
        .course-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 10px; }
        .course-header h2 { margin: 0; font-size: 16px; }
        .course-header p { margin: 5px 0 0; color: var(--muted); font-size: 11px; }
        .course-count { color: var(--muted); font-size: 11px; }
        .course-table { width: 100%; border-collapse: collapse; color: var(--text); font-size: 12px; }
        .course-table th { padding: 11px 0; border-top: 1px solid var(--border); color: var(--muted); font-size: 10px; font-weight: 800; letter-spacing: .06em; text-align: left; text-transform: uppercase; }
        .course-table th:nth-child(2) { width: 43%; }
        .course-table th:last-child, .course-table td:last-child { text-align: right; }
        .course-table td { padding: 10px 0; border-top: 1px solid var(--border); }
        .course-table td:first-child { max-width: 280px; font-weight: 700; }
        .course-progress { display: flex; align-items: center; gap: 10px; max-width: 300px; }
        .course-progress .progress-track { flex: 1; min-width: 80px; margin: 0; }
        .course-percent { width: 34px; color: var(--text); font-size: 11px; font-weight: 800; text-align: right; }
        .status-complete { color: var(--green); font-weight: 700; }
        .status-progress { color: var(--muted); font-weight: 700; }
        .student-sidebar-layout .student-sidebar {
            top: 0;
            margin: 0 10px 24px 0;
        }
        @media (max-width: 800px) {
            .analytics-header { align-items: flex-start; flex-direction: column; }
            .period-switch { align-self: stretch; justify-content: space-between; }
            .period-switch button { flex: 1; }
            .stats-card { grid-template-columns: repeat(2, 1fr); }
            .stat:nth-child(3) { border-top: 1px solid var(--border); border-left: 0; }
            .stat:nth-child(4) { border-top: 1px solid var(--border); }
            .analytics-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) {
            h1 { font-size: 28px; }
            .stats-card { grid-template-columns: 1fr; }
            .stat, .stat:nth-child(3), .stat:nth-child(4) { border-top: 1px solid var(--border); border-left: 0; }
            .stat:first-child { border-top: 0; }
            .analytics-card { padding: 14px; }
            .chart-wrap { height: 220px; }
            .course-card { padding: 18px 16px 8px; overflow-x: auto; }
            .course-table { min-width: 540px; }
        }
    </style>
</head>
<body>
    <?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php
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
        $currentStreak = (int) (collect($weekSeries['streak'] ?? [])->max() ?? 0);
        $assessmentAverage = round((float) ($stats['score_avg'] ?? 0), 1);
        $summaryStats = [
            ['label' => 'Learning Progress', 'value' => $learningProgress . '%', 'icon' => 'trend'],
            ['label' => 'Completion Rate', 'value' => $learningProgress . '%', 'icon' => 'check'],
            ['label' => 'Assessment Average', 'value' => number_format($assessmentAverage, 1) . '%', 'icon' => 'award'],
            ['label' => 'Learning Time', 'value' => $weeklyHours . 'h', 'icon' => 'clock'],
        ];
        $courseRows = $courseCollection->map(fn ($course) => [
            'name' => $course->title ?? 'Course',
            'percent' => (int) ($course->percent ?? 0),
            'status' => (int) ($course->percent ?? 0) >= 100 ? 'Complete' : 'In progress',
        ]);
    ?>
    <div class="layout student-sidebar-layout">
        <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main">
            <div class="analytics-page">
        <header class="analytics-header">
            <div>
                <p class="eyebrow">Performance overview</p>
                <h1>My Analytics</h1>
                <p class="subtitle">Track progress, activity, and assessment outcomes.</p>
            </div>
            <div class="period-switch" role="group" aria-label="Analytics period">
                <button type="button" data-period="day">Day</button><button type="button" class="active" data-period="week">Week</button><button type="button" data-period="month">Month</button>
            </div>
        </header>
        <section class="stats-card" aria-label="Summary statistics">
            <?php $__currentLoopData = $summaryStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="stat">
                    <span class="stat-icon" aria-hidden="true">
                        <?php if($stat['icon'] === 'trend'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/><path d="M16 7h5v5"/></svg>
                        <?php elseif($stat['icon'] === 'check'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        <?php elseif($stat['icon'] === 'award'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="m9 12-1 9 4-2 4 2-1-9"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <?php endif; ?>
                    </span>
                    <span><strong class="stat-value"><?php echo e($stat['value']); ?></strong><small class="stat-label"><?php echo e($stat['label']); ?></small></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
        <section class="analytics-grid" aria-label="Analytics details">
            <article class="analytics-card">
                <div class="card-header"><div><h2>Learning Trend</h2><p>Weekly learning activity</p></div><span class="period-badge">WEEK</span></div>
                <div class="chart-wrap"><canvas id="learning-trend-chart"></canvas></div>
            </article>
            <article class="analytics-card">
                <div class="card-header"><div><h2>Assessment performance</h2><p>Average score by assessment type</p></div><span class="assessment-percent">%</span></div>
                <div class="assessment-row"><div class="assessment-label"><span>Quizzes</span><span><?php echo e(number_format($assessmentAverage, 1)); ?>%</span></div><div class="progress-track"><div class="progress-fill" style="width:<?php echo e(min(100, max(0, $assessmentAverage))); ?>%"></div></div></div>
                <div class="assessment-stats">
                    <div class="mini-stat"><label>Competencies mastered</label><strong><?php echo e($stats['competencies_mastered'] ?? 0); ?></strong></div><div class="mini-stat"><label>Active learning days</label><strong><?php echo e($activeDays); ?></strong></div>
                    <div class="mini-stat"><label>Current streak</label><strong><?php echo e($currentStreak); ?>d</strong></div><div class="mini-stat"><label>Weekly hours</label><strong><?php echo e($weeklyHours); ?>h</strong></div>
                </div>
            </article>
        </section>
        <section class="course-card" aria-labelledby="course-progress-title">
            <div class="course-header"><div><h2 id="course-progress-title">Course progress</h2><p>Progress across your enrolled courses</p></div><span class="course-count"><?php echo e($courseRows->count()); ?> courses</span></div>
            <table class="course-table"><thead><tr><th>Course</th><th>Progress</th><th>Status</th></tr></thead><tbody>
                <?php $__currentLoopData = $courseRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($course['name']); ?></td><td><div class="course-progress"><span class="progress-track"><span class="progress-fill" style="width:<?php echo e($course['percent']); ?>%"></span></span><span class="course-percent"><?php echo e($course['percent']); ?>%</span></div></td><td class="<?php echo e($course['percent'] === 100 ? 'status-complete' : 'status-progress'); ?>"><?php echo e($course['status']); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody></table>
        </section>
            </div>
        </main>
    </div>
    <script>
        var analyticsSeries = <?php echo json_encode($analyticsSeries ?? [], 15, 512) ?>;
        var learningTrendChart = new Chart(document.getElementById('learning-trend-chart'), {
            type: 'line',
            data: { labels: <?php echo json_encode($weekSeries['labels'] ?? [], 15, 512) ?>, datasets: [{ data: <?php echo json_encode($weekSeries['hours'] ?? [], 15, 512) ?>, borderColor: '#1232D4', backgroundColor: 'rgba(18, 50, 212, .12)', fill: true, tension: .38, pointRadius: 4, pointHoverRadius: 5, pointBackgroundColor: '#1232D4', pointBorderColor: '#fff', pointBorderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { displayColors: false } }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: '#718096', font: { size: 10 } } }, y: { min: 0, max: 12, ticks: { stepSize: 2, color: '#718096', font: { size: 10 } }, grid: { color: '#EDF1F7' }, border: { display: false } } } }
        });
        document.querySelectorAll('[data-period]').forEach(function (button) {
            button.addEventListener('click', function () {
                var series = analyticsSeries[button.dataset.period];
                if (!series) return;
                document.querySelectorAll('[data-period]').forEach(function (item) { item.classList.remove('active'); });
                button.classList.add('active');
                learningTrendChart.data.labels = series.labels;
                learningTrendChart.data.datasets[0].data = series.hours;
                learningTrendChart.update();
            });
        });
    </script>
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/analytics.blade.php ENDPATH**/ ?>