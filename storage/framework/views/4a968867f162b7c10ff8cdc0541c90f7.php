
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Pathways | Upskill</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --pill-bg:#f3e2a6;
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
    .page-head h2{font-size:30px;margin:0 0 28px;color:var(--navy);}

    /* Pathway roadmap */
    .pathway-card{border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:34px 36px 30px;margin-bottom:28px;overflow-x:auto;}
    .pathway-track{display:flex;align-items:flex-start;min-width:760px;}
    .pathway-col{display:flex;flex-direction:column;align-items:center;gap:10px;flex-shrink:0;width:108px;}
    .goal-label{font-weight:700;color:var(--navy);font-size:13px;height:16px;line-height:16px;}
    .pathway-box{width:100px;height:80px;border-radius:10px;display:flex;align-items:center;justify-content:center;text-align:center;font-weight:800;color:#fff;font-size:13px;padding:6px;line-height:1.3;}
    .pathway-box.is-destination{width:130px;height:96px;font-size:14px;}
    .pathway-line{flex:1;height:4px;min-width:24px;margin-top:66px;}
    .pathway-col.is-destination-col{width:140px;}

    /* Bottom panels */
    .path-panels{display:grid;grid-template-columns:1fr 1fr 300px;gap:24px;align-items:stretch;}
    .path-panel{border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:22px 24px;}
    .path-pill{display:inline-block;background:var(--pill-bg);color:var(--navy);font-weight:700;padding:8px 22px;border-radius:999px;font-size:13px;margin-bottom:16px;}
    .path-pill-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
    .path-pill-row .desired-title{color:#2563eb;font-weight:800;font-size:16px;}

    .rec-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-top:1px solid var(--line);font-size:14px;}
    .rec-row:first-of-type{border-top:none;}
    .rec-row .title{color:var(--navy);font-weight:700;}
    .rec-row .completion{color:var(--muted);}

    .comp-columns{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    .comp-col h5{margin:0 0 10px;color:var(--muted);font-size:13px;font-weight:700;text-align:center;}
    .comp-col ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;align-items:center;}
    .comp-col li{color:var(--navy);font-weight:700;font-size:14px;text-align:center;}

    .path-oval{border:1px solid var(--line);border-radius:50%/38%;box-shadow:var(--shadow);padding:40px 26px;display:flex;align-items:center;justify-content:center;text-align:center;height:100%;}
    .path-oval p{margin:0;color:var(--navy);font-weight:700;font-style:italic;font-size:14px;line-height:1.5;}

    .empty-state{color:var(--muted);font-size:14px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .path-panels{grid-template-columns:1fr;}
        .path-oval{border-radius:24px;}
    }

    /* Phones: the roadmap becomes a vertical, centered timeline of cards â€”
       the same single-column, comfortably-padded card look used on
       My Profile â€” instead of a horizontally-scrolling strip. */
    @media (max-width:768px){
        .main{padding:20px 16px 48px;}
        .page-head h2{font-size:24px;}

        .pathway-card{overflow-x:visible;padding:24px 18px 20px;}
        .pathway-track{flex-direction:column;align-items:center;min-width:0;width:100%;}
        .pathway-col{width:100%;max-width:220px;}
        .pathway-col.is-destination-col{width:100%;max-width:220px;margin-top:4px;}
        .pathway-box{width:100%;max-width:200px;height:auto;min-height:64px;font-size:14px;}
        .pathway-box.is-destination{width:100%;max-width:220px;height:auto;min-height:76px;}
        .pathway-line{width:4px;height:26px;min-width:0;margin:2px 0;}

        .path-panel{padding:20px 18px;}
        .path-oval{padding:26px 20px;}
        .path-pill-row{justify-content:flex-start;}
    }
</style>
</head>
<body>

<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">

    
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">

        <div class="page-head">
            <h2>My Pathways</h2>
        </div>

        
        <div class="pathway-card">
            <div class="pathway-track">
                <?php $__currentLoopData = ($pathway['steps'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="pathway-col">
                        <span class="goal-label"><?php echo e($step['label']); ?></span>
                        <div class="pathway-box" style="background:<?php echo e($step['color']); ?>"><?php echo e($step['title']); ?></div>
                    </div>
                    <div class="pathway-line" style="background:<?php echo e($loop->last ? ($pathway['connector_to_destination'] ?? $step['color']) : $step['color']); ?>"></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="pathway-col is-destination-col">
                    <span class="goal-label"></span>
                    <div class="pathway-box is-destination" style="background:<?php echo e($pathway['destination_color'] ?? '#5FD93D'); ?>"><?php echo e($pathway['destination'] ?? 'Career Goal'); ?></div>
                </div>
            </div>
        </div>

        
        <div class="path-panels">

            <div class="path-panel">
                <span class="path-pill">Recommendations</span>
                <?php $__empty_1 = true; $__currentLoopData = ($recommendations ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rec-row">
                        <span class="title"><?php echo e($rec['title']); ?></span>
                        <span class="completion"><?php echo e($rec['completion']); ?>% Completion</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="empty-state">No recommendations right now.</p>
                <?php endif; ?>
            </div>

            <div class="path-panel">
                <div class="path-pill-row">
                    <span class="path-pill">Desired Pathway</span>
                    <span class="desired-title"><?php echo e($desiredPathway['title'] ?? ''); ?></span>
                </div>
                <div class="comp-columns">
                    <div class="comp-col">
                        <h5>Current Competencies</h5>
                        <ul>
                            <?php $__empty_1 = true; $__currentLoopData = ($desiredPathway['current_competencies'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li><?php echo e($item); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="empty-state">None yet</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="comp-col">
                        <h5>Missing Competencies</h5>
                        <ul>
                            <?php $__empty_1 = true; $__currentLoopData = ($desiredPathway['missing_competencies'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li><?php echo e($item); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="empty-state">None</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="path-oval">
                <p>You Currently meet <?php echo e($readinessPercent ?? 0); ?>% of the required Competencies for <?php echo e($readinessLabel ?? 'this Pathway'); ?>.</p>
            </div>

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

<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/pathways.blade.php ENDPATH**/ ?>