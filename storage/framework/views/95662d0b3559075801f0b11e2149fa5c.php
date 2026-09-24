
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Admin Dashboard</title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">

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
</head>

<body>

    <?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="layout">
        <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main">

            <div class="page-heading">Welcome back, Admin</div>
            <div class="page-sub">Here's what's happening on your platform today.</div>

            
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
                        <div class="chart-lbl">
                            Students on platform &ndash; <?php echo e($stats['total_students']); ?>

                        </div>
                    </div>

                    <div class="chart-row">
                        <div class="chart-lbl">
                            Badges issued &ndash; <?php echo e($stats['badges_issued']); ?>

                        </div>
                    </div>

                    <div class="chart-row">
                        <div class="chart-lbl">
                            Average quiz score &ndash; <?php echo e($stats['course_score_avg']); ?>%
                        </div>
                    </div>

                    <div class="chart-row">
                        <div class="chart-lbl">
                            Enrollments recorded &ndash; <?php echo e($stats['students_enrolled']); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="two-col">

                
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Active Courses</span>
                        <a href="<?php echo e(route('courses.browse')); ?>" class="view-all">View All</a>
                    </div>

                    <?php $__currentLoopData = $activeCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="course-item">
                            <div class="course-thumb">
                                <?php if($course->thumbnail_url): ?>
                                    <img src="<?php echo e($course->thumbnail_url); ?>"
                                         alt="<?php echo e($course->title); ?>"
                                         style="width:100%;height:100%;object-fit:cover;">
                                <?php endif; ?>
                            </div>

                            <div class="course-body">
                                <div class="course-name"><?php echo e($course->title); ?></div>
                                <div class="course-meta"><?php echo e($course->meta); ?></div>
                            </div>

                            <div class="course-pct"><?php echo e($course->percent); ?>%</div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Recent Badges</span>
                    </div>

                    <div class="badges-grid" id="recent-badges-grid">
                        <?php $__currentLoopData = $recentBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="badge-card">
                                <div class="badge-name"><?php echo e($badge->name); ?></div>
                                <div class="badge-count"><?php echo e($badge->earned_count); ?> Earned</div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <div class="two-col">

                
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Enrollment by Courses</span>
                    </div>

                    <?php $__currentLoopData = $enrollmentByCourse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="chart-row">
                            <div class="chart-lbl">
                                <?php echo e($row->label); ?> &ndash; <?php echo e($row->value); ?>

                            </div>

                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?php echo e($row->percent); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Completion Rate</span>
                    </div>

                    <?php $__currentLoopData = $completionRate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="chart-row">
                            <div class="chart-lbl">
                                <?php echo e($row->label); ?> &ndash; <?php echo e($row->value); ?>%
                            </div>

                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?php echo e($row->percent); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

        </main>
    </div>

    
    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            if (!section || section.classList.contains('locked')) return;
            section.classList.toggle('open');
        }

        function refreshLiveMonitoring() {
            const liveUrl = '<?php echo e(route('monitoring.live')); ?>';

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

</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>