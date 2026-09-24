
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrolled Courses | Upskill</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --green:#15803d;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;background-size:cover;background-position:center;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-icon-box svg{width:26px;height:26px;color:#fff;transition:color .15s ease;}
    .side-link.active .side-icon-box svg{color:var(--navy);}
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0 0 28px;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:2px solid var(--navy);color:var(--navy);font-weight:800;padding:10px 22px;border-radius:12px;font-size:14px;transition:background .15s ease,color .15s ease;}
    .btn-outline:hover{background:var(--navy);color:#fff;}

    /* Course areas */
    .courses-area-title{margin:26px 0 14px;font-size:17px;font-weight:800;color:var(--navy);letter-spacing:.3px;display:flex;align-items:center;gap:10px;}
    .courses-area-title .count-chip{background:#eef1fb;color:var(--navy);font-size:12px;font-weight:800;padding:3px 12px;border-radius:999px;}
    .course-card{display:flex;align-items:center;gap:22px;border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:20px 24px;margin-bottom:16px;background:#fff;border-left:6px solid var(--navy);transition:transform .18s ease,box-shadow .18s ease;}
    .course-card:hover{transform:translateY(-3px);box-shadow:0 18px 38px rgba(19,23,107,.16);}
    .course-card.is-completed{border-left-color:var(--green);}
    .course-card .thumb{width:140px;height:96px;border-radius:14px;background:linear-gradient(135deg,var(--navy) 0%,#3b41c8 100%);background-size:cover;background-position:center;flex-shrink:0;box-shadow:inset 0 0 0 1px rgba(255,255,255,.12);}
    .course-info{flex:1;min-width:0;}
    .course-info h4{margin:0 0 4px;font-size:17px;color:var(--navy);}
    .course-info .cat{font-size:12.5px;color:var(--muted);margin-bottom:10px;}
    .progress-track{height:7px;border-radius:999px;background:#e8ecf7;overflow:hidden;margin-bottom:7px;max-width:420px;}
    .progress-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--navy),#3b41c8);}
    .course-card.is-completed .progress-fill{background:linear-gradient(90deg,var(--green),#34d399);}
    .pct{font-size:12px;color:var(--muted);font-weight:700;}
    .btn-start{background:var(--gold);color:#fff;border:none;border-radius:12px;padding:11px 26px;font-size:14px;font-weight:800;white-space:nowrap;transition:background .2s ease;flex-shrink:0;}
    .btn-start:hover{background:var(--gold-dark);}
    .btn-start.btn-completed{background:var(--green);}
    .btn-start.btn-completed:hover{background:#0f6b30;}
    .empty-state{color:var(--muted);font-size:14px;padding:14px 4px;}
    .enroll-more{color:var(--gold-dark);font-weight:700;text-decoration:underline;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .course-card{flex-direction:column;align-items:flex-start;}
        .course-card .thumb{width:100%;height:150px;}
    }
</style>
</head>
<body>

<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">

    
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">

        <div class="page-head">
            <div>
                <h2>Enrolled Courses</h2>
                <p>All the courses you're enrolled in, organized by your progress.</p>
            </div>
            <a href="<?php echo e(route('courses.browse')); ?>">
                <button class="btn-outline" type="button">+ Enroll More</button>
            </a>
        </div>

        <?php
            $inProgressCourses = $inProgressCourses ?? collect();
            $completedCourses  = $completedCourses ?? collect();
        ?>

        <?php if($inProgressCourses->isEmpty() && $completedCourses->isEmpty()): ?>
            <div class="empty-state">
                You haven't enrolled in any courses yet.
                <br>
                <a href="<?php echo e(route('courses.browse')); ?>" class="enroll-more">Browse courses to get started</a>
            </div>
        <?php else: ?>
            <h3 class="courses-area-title">
                Not Yet Completed
                <span class="count-chip"><?php echo e($inProgressCourses->count()); ?></span>
            </h3>
            <?php $__empty_1 = true; $__currentLoopData = $inProgressCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="course-card">
                    <div class="thumb" <?php if($course->thumbnail_url): ?> style="background-image:url('<?php echo e($course->thumbnail_url); ?>')" <?php endif; ?>></div>
                    <div class="course-info">
                        <h4><?php echo e($course->title); ?></h4>
                        <?php if($course->category): ?>
                            <div class="cat"><?php echo e($course->category); ?></div>
                        <?php endif; ?>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:<?php echo e($course->progress_percent ?? 0); ?>%"></div>
                        </div>
                        <div class="pct"><?php echo e($course->progress_percent ?? 0); ?>% Complete</div>
                    </div>
                    <a href="<?php echo e(route('courses.show', ['id' => $course->id, 'from' => 'enrollments'])); ?>">
                        <button class="btn-start" type="button">
                            <?php echo e(($course->progress_percent ?? 0) > 0 ? 'Continue' : 'Start'); ?>

                        </button>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">Nothing in progress right now.</div>
            <?php endif; ?>

            <h3 class="courses-area-title">
                Completed Courses
                <span class="count-chip"><?php echo e($completedCourses->count()); ?></span>
            </h3>
            <?php $__empty_1 = true; $__currentLoopData = $completedCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="course-card is-completed">
                    <div class="thumb" <?php if($course->thumbnail_url): ?> style="background-image:url('<?php echo e($course->thumbnail_url); ?>')" <?php endif; ?>></div>
                    <div class="course-info">
                        <h4><?php echo e($course->title); ?></h4>
                        <?php if($course->category): ?>
                            <div class="cat"><?php echo e($course->category); ?></div>
                        <?php endif; ?>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:<?php echo e($course->progress_percent ?? 0); ?>%"></div>
                        </div>
                        <div class="pct"><?php echo e($course->progress_percent ?? 0); ?>% Complete</div>
                    </div>
                    <a href="<?php echo e(route('courses.show', ['id' => $course->id, 'from' => 'enrollments'])); ?>">
                        <button class="btn-start btn-completed" type="button">Review</button>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">No completed courses yet - finish a course and it will appear here.</div>
            <?php endif; ?>
        <?php endif; ?>

    </main>
</div>


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

    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>

<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/courses/enrolled.blade.php ENDPATH**/ ?>