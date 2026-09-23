<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Credit Recognition | UPSKILL</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <style>
        body { margin:0; background:#f6f8fc; color:#17233c; font-family:"Segoe UI",system-ui,-apple-system,sans-serif; }
        .recognition-main { padding:30px 32px 48px !important; box-sizing:border-box; }
        .recognition-content { max-width:1180px; margin:0 auto; }
        .recognition-head { margin-bottom:22px; }
        .recognition-eyebrow { color:#a06f00; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        .recognition-head h1 { margin:5px 0; color:#0d1b6e; font-size:32px; line-height:1.15; }
        .recognition-head p { margin:0; color:#68758c; font-size:14px; }
        .notice { padding:12px 14px; border-radius:10px; margin-bottom:16px; font-size:13px; }
        .ok { background:#ecfdf3; color:#067647; }
        .err { background:#fff1f2; color:#b42318; }
        .recognition-list { display:grid; gap:12px; }
        .recognition-card { background:#fff; border:1px solid #e2e8f3; border-radius:14px; padding:18px 20px; box-shadow:0 6px 18px rgba(13,27,110,.05); }
        .recognition-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
        .meta { background:#f8fafc; border-radius:9px; padding:10px; min-width:0; }
        .meta span { display:block; color:#718096; font-size:11px; }
        .meta strong { display:block; margin-top:3px; color:#0d1b6e; font-size:13px; overflow-wrap:anywhere; }
        .status { display:inline-block; padding:5px 9px; border-radius:999px; background:#fff7ed; color:#9a3412; font-size:11px; font-weight:800; }
        .status.done { background:#ecfdf3; color:#067647; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
        .actions button { border:0; border-radius:9px; padding:9px 14px; font:inherit; font-size:12px; font-weight:800; cursor:pointer; background:#0d1b6e; color:#fff; }
        .actions button:hover { background:#1232d4; }
        .actions button.danger { background:#fff1f2; color:#b42318; border:1px solid #fecdd3; }
        .empty-card { background:#fff; border:1px solid #e2e8f3; border-radius:14px; padding:22px; color:#68758c; box-shadow:0 6px 18px rgba(13,27,110,.05); }
        @media(max-width:900px){ .recognition-main{padding:24px 18px 40px !important;} .recognition-grid{grid-template-columns:1fr 1fr;} }
        @media(max-width:600px){ .recognition-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
    <?php echo $__env->make('components.authenticated-topbar', ['user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="layout admin-layout">
        <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main recognition-main">
            <div class="recognition-content">
                <header class="recognition-head">
                    <div class="recognition-eyebrow">Academic recognition</div>
                    <h1>Academic Credit Recognition</h1>
                    <p>Review completed stacking frameworks before academic credit is formally recorded.</p>
                </header>

                <?php if(session('success')): ?><div class="notice ok"><?php echo e(session('success')); ?></div><?php endif; ?>
                <?php if($errors->any()): ?><div class="notice err"><?php echo e($errors->first()); ?></div><?php endif; ?>

                <section class="recognition-list">
                    <?php $__empty_1 = true; $__currentLoopData = $recognitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="recognition-card">
                            <div class="recognition-grid">
                                <div class="meta"><span>Learner</span><strong><?php echo e($record->user?->name ?? '—'); ?></strong></div>
                                <div class="meta"><span>Framework</span><strong><?php echo e($record->framework?->name ?? '—'); ?></strong></div>
                                <div class="meta"><span>Equivalent course</span><strong><?php echo e($record->framework?->equivalent_course ?? '—'); ?></strong></div>
                                <div class="meta"><span>Status</span><strong><span class="status <?php echo e($record->status === 'registrar_recorded' ? 'done' : ''); ?>"><?php echo e(str_replace('_',' ',ucfirst($record->status))); ?></span></strong></div>
                            </div>
                            <div class="actions">
                                <?php if($record->status==='pending'): ?><form method="POST" action="<?php echo e(route('admin.academic-credit-recognition.recommend',$record->id)); ?>"><?php echo csrf_field(); ?><button>Recommend</button></form><?php endif; ?>
                                <?php if($record->status==='unit_recommended'): ?><form method="POST" action="<?php echo e(route('admin.academic-credit-recognition.endorse',$record->id)); ?>"><?php echo csrf_field(); ?><button>Endorse</button></form><?php endif; ?>
                                <?php if($record->status==='dean_endorsed'): ?><form method="POST" action="<?php echo e(route('admin.academic-credit-recognition.record',$record->id)); ?>"><?php echo csrf_field(); ?><button>Record as Registrar</button></form><?php endif; ?>
                                <?php if(in_array($record->status,['pending','unit_recommended','dean_endorsed'],true)): ?><form method="POST" action="<?php echo e(route('admin.academic-credit-recognition.deny',$record->id)); ?>"><?php echo csrf_field(); ?><button class="danger">Deny</button></form><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-card">No academic credit recognition requests are currently available.</div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/academic-credit-recognition.blade.php ENDPATH**/ ?>