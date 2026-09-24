
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Dashboard | Upskill</title>
    
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
    .brand .logo svg{width:30px;height:30px;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,0.14);}
    .side-icon-box svg{color:#fff;width:22px;height:22px;transition:color .15s ease;}
    .side-link.active .side-icon-box{background:var(--navy);}
    .side-link.plain .side-icon-box{background:transparent;}
    .side-link.plain .side-icon-box svg{color:#fff;width:26px;height:26px;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 24px;border-radius:999px;font-size:15px;}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:36px;}
    /* Gradient stat cards (same design as Faculty Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:44px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Content grid */
    .content-grid{display:grid;grid-template-columns:1fr 360px;gap:28px;}
    .courses-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
    .courses-head h3{font-size:24px;margin:0;color:var(--navy);}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;align-items:center;gap:18px;margin-bottom:20px;}
    .thumb{width:78px;height:78px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h4{margin:0 0 10px;font-size:17px;color:var(--navy);}
    .course-meta{display:flex;align-items:center;gap:22px;flex-wrap:wrap;}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:6px 18px;border-radius:999px;}
    .status-pill.published{background:#86efac;color:#14532d;}
    .status-pill.draft{background:#fde68a;color:#713f12;}
    .meta-item{text-align:center;font-size:12px;color:var(--muted);line-height:1.3;}
    .meta-item .meta-num{display:block;font-weight:800;font-size:15px;color:var(--navy);}
    .btn-manage{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:10px;font-size:15px;flex-shrink:0;}
    .card-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .card-actions a{display:inline-flex;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);}

    /* Side panels */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:24px;min-height:200px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:22px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;}
    .panel-body{padding:18px 22px;color:var(--muted);font-size:14px;}
    .quick-actions{display:flex;gap:14px;flex-wrap:wrap;}
    .btn-quick{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:999px;font-size:14px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:column;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .content-grid{grid-template-columns:1fr;}
    }

    /* â”€â”€ Phone: stack the course card instead of letting it wrap â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width:640px){
        .main{padding:20px 14px 48px;}

        .course-card{flex-direction:column;align-items:stretch;gap:14px;padding:16px;}
        .thumb{width:100%;height:140px;}
        .course-info{width:100%;min-width:0;}
        .course-info h4{font-size:17px;line-height:1.3;}
        .course-meta{gap:10px 18px;}
        .meta-item{text-align:left;}

        /* Buttons share the full width instead of trailing off the edge */
        .card-actions{width:100%;gap:10px;}
        .card-actions a{flex:1;}
        .btn-manage{width:100%;padding:10px 12px;font-size:14px;}

        /* Stat tiles stay 2x2 on phones â€” see components/responsive.blade.php */
        .stats{grid-template-columns:repeat(2,1fr);}
        .quick-actions .btn-quick{flex:1 1 auto;}
    }
</style>
</head>
<body>

<?php echo $__env->make('components.authenticated-topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout faculty-sidebar-layout">

    
    <?php echo $__env->make('components.faculty-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">

        <div class="page-head">
            <div>
                <h2>Faculty Dashboard</h2>
                <p>Manage your Courses and Track student Performance</p>
                <p style="margin-top: 8px; color: var(--navy); font-weight: 700;">User Code: <?php echo e($user->user_code ?? '-'); ?></p>
            </div>
            
            <a href="<?php echo e(route('faculty.create')); ?>"><button class="btn-outline" type="button">Create Courses</button></a>
        </div>

        
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg>
                    <span class="num"><?php echo e($stats['total_courses'] ?? 0); ?></span>
                </div>
                <div class="label">Total Courses</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="#fff"/><path d="M8 12.5l2.5 2.5L16 9.5" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num"><?php echo e($stats['published'] ?? 0); ?></span>
                </div>
                <div class="label">Published</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num"><?php echo e($stats['total_students'] ?? 0); ?></span>
                </div>
                <div class="label">Total Students</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.4 2.4 3.4-.6.6 3.4L21.8 9l-1.4 3 1.4 3-3.4 1.8-.6 3.4-3.4-.6L12 22l-2.4-2.4-3.4.6-.6-3.4L2.2 15l1.4-3-1.4-3 3.4-1.8.6-3.4 3.4.6L12 2z" fill="#fff"/><path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num"><?php echo e($stats['enrollments'] ?? 0); ?></span>
                </div>
                <div class="label">Enrollments</div>
            </div>
        </section>

        
        <div class="content-grid">

            <section class="courses-col">
                <div class="courses-head">
                    <h3>My Courses</h3>
                </div>

                <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="course-card">
                        <div class="thumb" <?php if($course->thumbnail_url ?? null): ?> style="background-image:url('<?php echo e($course->thumbnail_url); ?>')" <?php endif; ?>></div>
                        <div class="course-info">
                            <h4><?php echo e($course->title); ?></h4>
                            <div class="course-meta">
                                <span class="status-pill <?php echo e(strtolower($course->status ?? 'draft') === 'published' ? 'published' : 'draft'); ?>">
                                    <?php echo e($course->status ?? 'Draft'); ?>

                                </span>
                                <span class="meta-item">
                                    <span class="meta-num"><?php echo e($course->students_count ?? 0); ?></span>
                                    Students
                                </span>
                                <span class="meta-item">
                                    <span class="meta-num"><?php echo e($course->modules_count ?? 0); ?></span>
                                    Modules
                                </span>
                            </div>
                        </div>
                        <div class="card-actions">
                            
                            <a href="<?php echo e(route('faculty.courses.manage', $course->id ?? 1)); ?>">
                                <button class="btn-manage" type="button">Manage</button>
                            </a>
                            
                            <a href="<?php echo e(route('faculty.analytics', ['course_id' => $course->id ?? null])); ?>">
                                <button class="btn-manage" type="button">Analytics</button>
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        You haven't created any courses yet.
                    </div>
                <?php endif; ?>
            </section>

            <aside class="side-col">
                <div class="panel">
                    <div class="panel-head">
                        <span>Monthly Enrollments</span>
                    </div>
                    <div class="panel-body">
                        <p>No enrollment data yet.</p>
                    </div>
                </div>

                <div class="panel" style="min-height:auto;">
                    <div class="panel-head">
                        <span>Quick Actions</span>
                    </div>
                    <div class="panel-body">
                        <div class="quick-actions">
                            <a class="btn-quick" href="<?php echo e(route('faculty.students')); ?>">View Students</a>
                            <a class="btn-quick" href="<?php echo e(route('faculty.courses')); ?>">View Courses</a>
                        </div>
                    </div>
                </div>
            </aside>

        </div>

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


<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/faculty/dashboard.blade.php ENDPATH**/ ?>