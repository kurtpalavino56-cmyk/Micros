
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($cert['certificate_title'] ?: $cert['course_title']); ?> — Certificate | Upskill</title>
<link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{ --navy:#13176b; --gold:#dba617; }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;margin:0;background:#f4f6fb;color:#13176b;}
    .bar{background:var(--navy);padding:16px 24px;display:flex;align-items:center;justify-content:space-between;}
    .bar a{color:#fff;text-decoration:none;font-weight:700;}
    .bar .actions{display:flex;gap:10px;}
    .btn{display:inline-block;padding:10px 18px;border-radius:10px;font-weight:700;text-decoration:none;}
    .btn-gold{background:var(--gold);color:#13176b;}
    .btn-outline{border:1px solid rgba(255,255,255,.5);color:#fff;}
    .wrap{max-width:1080px;margin:32px auto;padding:0 20px;}
    .status-pill{display:inline-block;margin-bottom:14px;padding:4px 14px;border-radius:999px;font-size:12px;font-weight:700;}
    .status-active{background:#e5f7ec;color:#0f7a3d;}
    .status-revoked{background:#fdeaea;color:#a11d1d;}
</style>
</head>
<body>
<div class="bar">
    <a href="<?php echo e(route('certificates.index')); ?>">&larr; My Certificates</a>
    <div class="actions">
        <a class="btn btn-outline" href="<?php echo e(route('certificates.index')); ?>">Back</a>
        <a class="btn btn-gold" href="<?php echo e(route('certificates.download', $cert['serial'])); ?>">Download PDF</a>
    </div>
</div>
<div class="wrap">
    <span class="status-pill <?php echo e(($cert['status'] ?? 'active') === 'revoked' ? 'status-revoked' : 'status-active'); ?>">
        <?php echo e(($cert['status'] ?? 'active') === 'revoked' ? 'Revoked' : 'Active'); ?>

    </span>

    <?php echo $__env->make('components.certificate', ['cert' => $cert], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(!empty($cert['learning_outcomes'])): ?>
    <div style="margin-top:24px;background:#fff;border-radius:16px;padding:20px 26px;">
        <h3 style="margin:0 0 8px;font-size:15px;color:#13176b;">Learning Outcomes Achieved</h3>
        <ul style="margin:0;padding-left:18px;font-size:14px;color:#22304f;">
            <?php $__currentLoopData = $cert['learning_outcomes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php if(!empty($lo['code'])): ?><?php echo e($lo['code']); ?>: <?php endif; ?><?php echo e($lo['description'] ?? ''); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if(!empty($cert['competencies'])): ?>
    <div style="margin-top:16px;background:#fff;border-radius:16px;padding:20px 26px;">
        <h3 style="margin:0 0 8px;font-size:15px;color:#13176b;">Competencies Achieved</h3>
        <ul style="margin:0;padding-left:18px;font-size:14px;color:#22304f;">
            <?php $__currentLoopData = $cert['competencies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($unit['title'] ?? ''); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/certificate-view.blade.php ENDPATH**/ ?>