<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stacking Frameworks | UPSKILL</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <style>
        :root {
            --framework-navy: #0d1b6e;
            --framework-blue: #1232d4;
            --framework-gold: #dba617;
            --framework-text: #18213d;
            --framework-muted: #68758c;
            --framework-border: #dfe5ef;
            --framework-surface: #f6f8fc;
        }

        body { margin:0; background:var(--framework-surface); color:var(--framework-text); font-family:"Segoe UI",system-ui,-apple-system,sans-serif; }
        .framework-main { padding:30px 32px 48px !important; box-sizing:border-box; }
        .framework-content { max-width:1240px; margin:0 auto; }
        .framework-header { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:24px; }
        .framework-eyebrow { color:#a06f00; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:5px; }
        .framework-header h1 { margin:0 0 6px; color:var(--framework-navy); font-size:32px; line-height:1.15; }
        .framework-header p { margin:0; color:var(--framework-muted); font-size:14px; }
        .framework-header-actions { display:flex; gap:10px; flex-shrink:0; }

        .framework-button { border:1px solid transparent; border-radius:10px; padding:10px 15px; font:inherit; font-size:13px; font-weight:800; cursor:pointer; transition:.16s ease; }
        .framework-button.primary { background:var(--framework-navy); color:#fff; box-shadow:0 5px 12px rgba(13,27,110,.16); }
        .framework-button.primary:hover { background:var(--framework-blue); }
        .framework-button.secondary { background:#eef2ff; color:#24346f; border-color:#dbe2ee; }
        .framework-button.secondary:hover { background:#e4e9ff; }
        .framework-button.warning { background:#fff7ed; color:#9a3412; border-color:#fed7aa; }
        .framework-button.danger { background:#fff1f2; color:#b42318; border-color:#fecdd3; }

        .notice { border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:13px; }
        .notice-success { background:#ecfdf3; color:#067647; }
        .notice-error { background:#fff1f2; color:#b42318; }

        .framework-section-title { display:flex; align-items:center; justify-content:space-between; margin:28px 0 12px; }
        .framework-section-title h2 { margin:0; color:var(--framework-navy); font-size:20px; }
        .framework-section-title span { color:var(--framework-muted); font-size:12px; }

        .framework-list { display:grid; gap:12px; }
        .framework-card { background:#fff; border:1px solid var(--framework-border); border-radius:14px; padding:18px 20px; box-shadow:0 6px 20px rgba(13,27,110,.05); }
        .framework-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; }
        .framework-card-title { min-width:0; }
        .framework-card-title h3 { margin:0; color:var(--framework-navy); font-size:18px; line-height:1.25; }
        .framework-description { margin:6px 0 0; color:var(--framework-muted); font-size:13px; line-height:1.5; }
        .framework-badges { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
        .framework-pill { display:inline-flex; align-items:center; border-radius:999px; padding:4px 8px; font-size:11px; font-weight:800; background:#eef2ff; color:#344281; }
        .framework-pill.approved { background:#ecfdf3; color:#067647; }
        .framework-pill.inactive { background:#f1f5f9; color:#475569; }
        .framework-pill.pending { background:#fff7ed; color:#9a3412; }
        .framework-card-action { flex-shrink:0; }
        .framework-info { display:flex; flex-wrap:wrap; gap:16px 24px; margin-top:15px; padding-top:13px; border-top:1px solid #edf0f5; }
        .framework-info-item { min-width:120px; }
        .framework-info-item span { display:block; color:#8a94a6; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
        .framework-info-item strong { display:block; color:#27314f; font-size:12px; }
        .framework-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }
        .empty-state { background:#fff; border:1px dashed #cfd7e5; border-radius:14px; padding:28px; text-align:center; color:var(--framework-muted); }

        .framework-modal { position:fixed; inset:0; z-index:2000; display:none; align-items:center; justify-content:center; padding:24px; background:rgba(9,18,55,.52); backdrop-filter:blur(2px); }
        .framework-modal.is-open { display:flex; }
        .framework-modal-card { width:min(920px,100%); max-height:min(90vh,900px); overflow:auto; background:#fff; border-radius:16px; box-shadow:0 24px 70px rgba(5,15,60,.3); }
        .framework-modal-head { position:sticky; top:0; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:17px 20px; background:#fff; border-bottom:1px solid #e9edf4; }
        .framework-modal-head h2 { margin:0; color:var(--framework-navy); font-size:19px; }
        .framework-modal-close { width:34px; height:34px; border:0; border-radius:9px; background:#f3f5f9; color:#526078; font-size:22px; line-height:1; cursor:pointer; }
        .framework-modal-body { padding:20px; }
        .framework-modal .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .framework-modal .field { display:flex; flex-direction:column; gap:5px; }
        .framework-modal .field-wide { grid-column:1/-1; }
        .framework-modal .field label { color:#344054; font-size:12px; font-weight:800; }
        .framework-modal .field input, .framework-modal .field textarea, .framework-modal .field select { width:100%; box-sizing:border-box; border:1px solid #dce4f0; border-radius:9px; padding:10px 11px; background:#fff; color:#101b49; font:inherit; }
        .framework-modal .field textarea { min-height:74px; resize:vertical; }
        .framework-modal .requirement-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:22px 0 9px; }
        .framework-modal .requirement-header h3 { color:var(--framework-navy); font-size:15px; margin:0; }
        .framework-modal .requirement-row { display:grid; grid-template-columns:minmax(0,1fr) 100px 110px 34px; gap:8px; align-items:center; margin-bottom:8px; }
        .framework-modal .requirement-row input, .framework-modal .requirement-row select { border:1px solid #dce4f0; border-radius:8px; padding:9px; min-width:0; font:inherit; }
        .framework-modal .requirement-required { display:flex; gap:5px; align-items:center; color:#475467; font-size:12px; white-space:nowrap; }
        .framework-modal .remove-row { border:0; background:#fff1f2; color:#b42318; border-radius:8px; width:32px; height:32px; cursor:pointer; font-weight:900; }
        .framework-modal .actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:20px; padding-top:16px; border-top:1px solid #edf0f5; }
        .framework-modal .field small { color:#7a8699; font-size:11px; line-height:1.4; }
        .framework-modal .form-section-label { padding-top:8px; margin-top:4px; border-top:1px solid #edf0f5; }
        .framework-modal .form-section-label strong { color:var(--framework-navy); font-size:14px; }
        .framework-modal .form-section-label span { color:#7a8699; font-size:11px; line-height:1.4; }
        .completion-rule-summary { margin-top:14px; padding:10px 12px; border-left:3px solid #2c43d8; background:#f4f6ff; color:#25346f; font-size:12px; font-weight:800; }
        .requirement-table-head { display:grid; grid-template-columns:minmax(0,1fr) 100px 110px 34px; gap:8px; margin:4px 0 7px; color:#7a8699; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .requirement-header p { margin:4px 0 0; color:#7a8699; font-size:11px; line-height:1.4; }

        @media (max-width:800px) {
            .framework-main { padding:24px 18px 40px !important; }
            .framework-header { align-items:flex-start; flex-direction:column; }
            .framework-header-actions { width:100%; }
            .framework-header-actions .framework-button { flex:1; }
            .framework-card-top { flex-direction:column; }
            .framework-card-action { align-self:flex-start; }
            .framework-modal { padding:12px; }
            .framework-modal .form-grid { grid-template-columns:1fr; }
            .framework-modal .field-wide { grid-column:auto; }
            .requirement-table-head { display:none; }
        }
        @media (max-width:560px) {
            .framework-info { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
            .framework-modal .requirement-row { grid-template-columns:1fr 80px 100px 32px; }
        }
    </style>
</head>
<body>
    <?php echo $__env->make('components.authenticated-topbar', ['user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="layout admin-layout">
        <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main framework-main">
            <div class="framework-content">
                <header class="framework-header">
                    <div>
                        <div class="framework-eyebrow">Admin configuration</div>
                        <h1>Stacking frameworks</h1>
                        <p>Define approved microcredential combinations without creating academic credit automatically.</p>
                    </div>
                    <div class="framework-header-actions">
                        <button type="button" class="framework-button primary" data-open-framework-modal="create-framework-modal">+ Create Framework</button>
                    </div>
                </header>

                <?php if(session('success')): ?>
                    <div class="notice notice-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="notice notice-error"><?php echo e($errors->first()); ?></div>
                <?php endif; ?>

                <div class="framework-section-title">
                    <h2>Configured frameworks</h2>
                    <span><?php echo e($frameworks->count()); ?> configured</span>
                </div>

                <section class="framework-list">
                    <?php $__empty_1 = true; $__currentLoopData = $frameworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $framework): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = match ($framework->status) {
                                'approved' => 'approved',
                                'inactive' => 'inactive',
                                'pending_approval' => 'pending',
                                default => '',
                            };
                            $description = \Illuminate\Support\Str::limit(strip_tags((string) $framework->description), 150);
                        ?>

                        <article class="framework-card">
                            <div class="framework-card-top">
                                <div class="framework-card-title">
                                    <h3><?php echo e($framework->name); ?></h3>
                                    <p class="framework-description"><?php echo e($description !== '' ? $description : 'No description provided.'); ?></p>
                                    <div class="framework-badges">
                                        <span class="framework-pill <?php echo e($statusClass); ?>"><?php echo e(str_replace('_', ' ', ucfirst($framework->status))); ?></span>
                                        <span class="framework-pill"><?php echo e($framework->is_active ? 'Active' : 'Inactive'); ?></span>
                                        <?php if($framework->pqf_level): ?><span class="framework-pill">PQF <?php echo e($framework->pqf_level); ?></span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="framework-card-action">
                                    <button type="button" class="framework-button secondary" data-open-framework-modal="edit-framework-modal-<?php echo e($framework->id); ?>">
                                        <?php echo e($framework->status === 'approved' ? 'View' : 'Edit'); ?>

                                    </button>
                                </div>
                            </div>

                            <div class="framework-info">
                                <div class="framework-info-item"><span>Recognition</span><strong><?php echo e($framework->target_recognition ?: '—'); ?></strong></div>
                                <div class="framework-info-item"><span>Equivalent course</span><strong><?php echo e($framework->equivalent_course ?: '—'); ?></strong></div>
                                <div class="framework-info-item"><span>Completion rule</span><strong><?php echo e($framework->completion_mode === 'minimum_required' ? 'Minimum '.$framework->required_count.' required' : 'All required'); ?></strong></div>
                                <div class="framework-info-item"><span>Academic unit</span><strong><?php echo e($framework->approving_academic_unit ?: '—'); ?></strong></div>
                                <div class="framework-info-item"><span>Sequence</span><strong><?php echo e($framework->sequence_required ? 'Required' : 'Not required'); ?></strong></div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-state">No stacking frameworks have been configured yet.</div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    
    <div class="framework-modal" id="create-framework-modal" aria-hidden="true">
        <div class="framework-modal-card" role="dialog" aria-modal="true" aria-labelledby="create-framework-modal-title">
            <div class="framework-modal-head">
                <h2 id="create-framework-modal-title">Create framework draft</h2>
                <button type="button" class="framework-modal-close" data-close-framework-modal aria-label="Close">&times;</button>
            </div>
            <div class="framework-modal-body">
                <form method="POST" action="<?php echo e(route('admin.stacking-frameworks.store')); ?>" id="create-framework-form">
                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.stacking-framework-fields', ['framework' => null, 'formId' => 'create-framework-form'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="actions">
                        <button class="framework-button primary" type="submit">Save draft</button>
                        <button class="framework-button secondary" type="button" data-close-framework-modal>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <?php $__currentLoopData = $frameworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $framework): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="framework-modal" id="edit-framework-modal-<?php echo e($framework->id); ?>" aria-hidden="true">
            <div class="framework-modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-framework-modal-title-<?php echo e($framework->id); ?>">
                <div class="framework-modal-head">
                    <h2 id="edit-framework-modal-title-<?php echo e($framework->id); ?>"><?php echo e($framework->status === 'approved' ? 'View' : 'Edit'); ?> framework</h2>
                    <button type="button" class="framework-modal-close" data-close-framework-modal aria-label="Close">&times;</button>
                </div>
                <div class="framework-modal-body">
                    <form method="POST" action="<?php echo e(route('admin.stacking-frameworks.update', $framework->id)); ?>" id="framework-form-<?php echo e($framework->id); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <fieldset <?php if($framework->status === 'approved'): echo 'disabled'; endif; ?> style="border:0;padding:0;margin:0;min-width:0;">
                            <?php echo $__env->make('admin.stacking-framework-fields', ['framework' => $framework, 'formId' => 'framework-'.$framework->id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </fieldset>

                        <div class="actions">
                            <?php if($framework->status !== 'approved'): ?>
                                <button class="framework-button primary" type="submit">Save draft</button>
                            <?php endif; ?>
                            <?php if(in_array($framework->status, ['draft', 'pending_approval', 'inactive'], true)): ?>
                                <button class="framework-button primary" type="submit" form="activate-framework-form-<?php echo e($framework->id); ?>">Activate</button>
                            <?php endif; ?>
                            <?php if($framework->status === 'approved'): ?>
                                <button class="framework-button warning" type="submit" form="deactivate-framework-form-<?php echo e($framework->id); ?>">Deactivate</button>
                            <?php endif; ?>
                            <button class="framework-button secondary" type="button" data-close-framework-modal>Close</button>
                        </div>
                    </form>

                    <?php if(in_array($framework->status, ['draft', 'pending_approval', 'inactive'], true)): ?>
                        <form method="POST" action="<?php echo e(route('admin.stacking-frameworks.approve', $framework->id)); ?>" id="activate-framework-form-<?php echo e($framework->id); ?>" hidden>
                            <?php echo csrf_field(); ?>
                        </form>
                    <?php endif; ?>

                    <?php if($framework->status === 'approved'): ?>
                        <form method="POST" action="<?php echo e(route('admin.stacking-frameworks.deactivate', $framework->id)); ?>" id="deactivate-framework-form-<?php echo e($framework->id); ?>" hidden>
                            <?php echo csrf_field(); ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        (function () {
            function openModal(id) {
                var modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                if (!document.querySelector('.framework-modal.is-open')) document.body.style.overflow = '';
            }

            document.addEventListener('click', function (event) {
                var openButton = event.target.closest('[data-open-framework-modal]');
                if (openButton) {
                    openModal(openButton.getAttribute('data-open-framework-modal'));
                    return;
                }

                var closeButton = event.target.closest('[data-close-framework-modal]');
                if (closeButton) {
                    closeModal(closeButton.closest('.framework-modal'));
                    return;
                }

                if (event.target.classList.contains('framework-modal')) closeModal(event.target);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                var modal = document.querySelector('.framework-modal.is-open');
                if (modal) closeModal(modal);
            });
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/stacking-frameworks.blade.php ENDPATH**/ ?>