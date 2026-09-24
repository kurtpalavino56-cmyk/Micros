
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inbox | Upskill Faculty</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{--navy:#13176b;--navy-deep:#0c0f4d;--gold:#dba617;--cyan:#7fe9e3;--muted:#6b7280;
          --line:#e5e7eb;--green:#15803d;--red:#ef4444;--shadow:0 10px 25px rgba(19,23,107,.08);}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;
        background:linear-gradient(135deg,#f8faff 0%,#f7f8fc 100%);}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;
        padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{position:relative;width:42px;height:42px;border-radius:50%;background:#fff;
        display:flex;align-items:center;justify-content:center;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}
    .icon-badge{position:absolute;top:-3px;right:-3px;background:var(--red);color:#fff;
        border-radius:999px;font-size:10px;font-weight:800;padding:2px 6px;line-height:1.3;
        z-index:5;box-shadow:0 0 0 2px var(--navy);}
    .btn-logout{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;
        padding:8px 14px;font-weight:700;font-size:12.5px;}

    .wrap{max-width:980px;margin:0 auto;padding:0 0 60px;width:100%;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;
        flex-wrap:wrap;margin-bottom:22px;}
    .page-head h2{font-size:28px;margin:0 0 6px;}
    .page-head p{margin:0;color:var(--muted);font-size:14.5px;}
    .btn-readall{background:var(--navy);color:#fff;border:none;border-radius:999px;
        padding:11px 22px;font-weight:800;font-size:13.5px;}
    .btn-readall:disabled{background:#e5e7eb;color:#9ca3af;cursor:not-allowed;}
    .btn-outline{background:#fff;border:2px solid var(--navy);color:var(--navy);font-weight:800;
        padding:10px 20px;border-radius:12px;font-size:13.5px;}

    .panel{background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);
        overflow:hidden;}
    .panel-hd{padding:16px 22px;border-bottom:1px solid var(--line);font-weight:800;font-size:14px;
        display:flex;justify-content:space-between;align-items:center;}
    .count-pill{background:var(--red);color:#fff;border-radius:999px;font-size:11px;
        font-weight:800;padding:3px 9px;}

    .ann{display:flex;gap:16px;padding:20px 22px;border-bottom:1px solid var(--line);}
    .ann:last-child{border-bottom:none;}
    .ann.unread{background:#f9fbff;}
    .ann-icon{width:44px;height:44px;border-radius:14px;flex-shrink:0;
        background:linear-gradient(135deg,#eef4ff 0%,#dbeafe 100%);
        display:flex;align-items:center;justify-content:center;}
    .ann-icon svg{width:22px;height:22px;color:var(--navy);}
    .ann-body{flex:1;min-width:0;}
    .ann-title{display:flex;align-items:center;gap:9px;margin-bottom:5px;}
    .ann-title strong{font-size:15.5px;}
    .unread-dot{width:8px;height:8px;border-radius:50%;background:var(--red);
        display:inline-block;flex-shrink:0;}
    .ann-text{color:#4b5563;font-size:14px;line-height:1.55;white-space:pre-wrap;margin:0 0 8px;}
    .ann-meta{font-size:12.5px;color:var(--muted);}

    .alert{padding:11px 14px;border-radius:12px;margin-bottom:18px;font-size:14px;
        background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .empty{color:var(--muted);text-align:center;padding:46px 14px;font-size:14px;}

    @media(max-width:640px){.ann{flex-direction:column;}.page-head{flex-direction:column;}}
</style>
</head>
<body>
<?php echo $__env->make('components.authenticated-topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout faculty-sidebar-layout">
    <?php echo $__env->make('components.faculty-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="main">
<div class="wrap">
    <?php echo $__env->make('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs faculty-breadcrumbs--standalone', 'items' => [
        ['label' => 'Faculty Dashboard', 'url' => route('faculty.dashboard')],
        ['label' => 'Inbox'],
    ]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="page-head">
        <div>
            <h2>Inbox</h2>
            <p>Announcements sent to faculty by the administrators.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo e(route('faculty.dashboard')); ?>"><button type="button" class="btn-outline">Back to Dashboard</button></a>
            <form method="POST" action="<?php echo e(route('faculty.inbox.readAll')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-readall" <?php if($unreadCount === 0): echo 'disabled'; endif; ?>>
                    Mark all as read
                </button>
            </form>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-hd">
            <span>Announcements</span>
            <?php if($unreadCount > 0): ?><span class="count-pill"><?php echo e($unreadCount); ?> unread</span><?php endif; ?>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ann <?php echo e($a->unread ? 'unread' : ''); ?>">
                <div class="ann-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 5h14v10H8l-3 3V5z"/>
                    </svg>
                </div>
                <div class="ann-body">
                    <div class="ann-title">
                        <strong><?php echo e($a->title); ?></strong>
                        <?php if($a->unread): ?><span class="unread-dot" aria-hidden="true"></span><?php endif; ?>
                    </div>
                    <p class="ann-text"><?php echo e($a->body); ?></p>
                    <div class="ann-meta">
                        By <?php echo e($a->author); ?> - <?php echo e($a->posted_at?->format('M j, Y g:i A')); ?>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">No announcements yet.</div>
        <?php endif; ?>
    </div>
</div>
    </main>
</div>


<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>

<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/faculty/inbox.blade.php ENDPATH**/ ?>