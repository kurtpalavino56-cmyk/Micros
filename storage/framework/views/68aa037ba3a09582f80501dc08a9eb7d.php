
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Badges | Upskill</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --badge-bg:#f7ecc4;
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
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-icon-box svg{width:26px;height:26px;color:#fff;transition:color .15s ease;}
    .side-link.active .side-icon-box svg{color:var(--navy);}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0 0 28px;color:var(--muted);font-size:15px;}

    /* Stats (shared with dashboard) */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:40px;}
    /* Gradient stat cards (same design as course Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:38px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Recently Earned */
    .section-head{font-size:24px;margin:0 0 18px;color:var(--navy);}
    .badge-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:24px;}
    .badge-card{background:var(--badge-bg);border-radius:20px;padding:20px;text-align:center;position:relative;box-shadow:var(--shadow);}
    .badge-time{position:absolute;top:16px;right:18px;font-size:12px;color:var(--muted);}
    .badge-icon{width:90px;height:90px;border-radius:16px;background:var(--thumb);margin:28px auto 18px;background-size:cover;background-position:center;}
    .badge-card h4{margin:0 0 10px;color:var(--navy);font-size:18px;font-weight:800;line-height:1.3;}
    .badge-card .desc{color:var(--muted);font-size:13px;margin:0 0 18px;line-height:1.45;}
    .badge-earned-pill{display:inline-block;background:var(--gold);color:var(--navy);font-weight:800;font-size:13px;padding:8px 22px;border-radius:999px;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:40px;text-align:center;color:var(--muted);grid-column:1/-1;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .badge-grid{grid-template-columns:repeat(2,1fr);}
    }
</style>
</head>
<body>

<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">

    
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">

        <div class="page-head">
            <h2>My Badge Collection</h2>
            <p>Badges earned through course completion</p>
        </div>

        
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg>
                    <span class="num"><?php echo e($stats['active_courses'] ?? 0); ?></span>
                </div>
                <div class="label">Active Courses</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    <span class="num"><?php echo e($stats['completed'] ?? 0); ?></span>
                </div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num"><?php echo e($stats['badges_earned'] ?? 0); ?></span>
                </div>
                <div class="label">Badges Earned</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z" fill="#fff"/><path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num"><?php echo e($stats['certificates'] ?? 0); ?></span>
                </div>
                <div class="label">Certificates</div>
            </div>
        </section>

        
        <h3 class="section-head">Recently Earned</h3>
        <div class="badge-grid">
            <?php $__empty_1 = true; $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="badge-card">
                    <span class="badge-time"><?php echo e($badge->earned_at?->diffForHumans() ?? ''); ?></span>
                    <div class="badge-icon" <?php if($badge->icon_url): ?> style="background-image:url('<?php echo e($badge->icon_url); ?>')" <?php endif; ?>></div>
                    <h4><?php echo e($badge->name); ?></h4>
                    <?php if($badge->description): ?>
                        <p class="desc"><?php echo e($badge->description); ?></p>
                    <?php endif; ?>
                    <span class="badge-earned-pill">Earned</span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    No badges earned yet. Complete a course to start collecting them.
                </div>
            <?php endif; ?>
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

<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/badges.blade.php ENDPATH**/ ?>