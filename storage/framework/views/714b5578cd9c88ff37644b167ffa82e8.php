
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Announcements | UPSKILL Admin</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;
          --shadow:0 10px 25px rgba(19,23,107,.08);--topbar-h:60px;}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f5f7ff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .topbar{position:sticky;top:0;height:var(--topbar-h);background:var(--navy);display:flex;
        align-items:center;justify-content:space-between;padding:0 22px;z-index:200;
        box-shadow:0 2px 8px rgba(0,0,0,.25);}
    .topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:1.05rem;
        font-weight:800;letter-spacing:2px;text-transform:uppercase;}
    .brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;overflow:hidden;}
    .brand-logo img{width:100%;height:100%;object-fit:contain;}
    .topbar-right{display:flex;align-items:center;gap:10px;}
    .avatar-btn{width:40px;height:40px;border-radius:50%;background:#fff;color:var(--navy);
        display:inline-flex;align-items:center;justify-content:center;}
    /* Matches the padding the other admin pages use (30px 34px 44px). */
    .wrap{max-width:1080px;margin:0 auto;padding:34px 40px 52px;}
    .page-heading{font-size:1.6rem;font-weight:800;margin:0 0 4px;}
    .page-sub{color:var(--muted);font-size:.92rem;margin:0 0 22px;}
    .card{background:#fff;border:1px solid var(--line);border-radius:18px;
        box-shadow:var(--shadow);padding:24px 26px;margin-bottom:22px;}
    .card h2{margin:0 0 16px;font-size:1.05rem;}
    label{display:block;font-size:12px;font-weight:800;letter-spacing:.4px;
        text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    input[type=text],textarea{width:100%;border:1.5px solid #c9ccdb;border-radius:10px;
        padding:11px 13px;font-size:14px;font-family:inherit;color:var(--navy);background:#fff;}
    textarea{min-height:110px;resize:vertical;}
    input[type=text]:focus,textarea:focus{outline:none;border-color:var(--navy);}
    .field{margin-bottom:16px;}
    /* "Who Can See Your Announcement" — audience checkboxes */
    .audience-row{display:flex;align-items:center;gap:18px;flex-wrap:wrap;
        background:#f7f9ff;border:1px solid #dfe4fb;border-radius:12px;padding:14px 16px;}
    .audience-label{font-weight:800;font-size:13.5px;color:var(--navy);}
    .audience-opts{display:flex;gap:16px;flex-wrap:wrap;}
    .audience-opts label{display:inline-flex;align-items:center;gap:7px;margin:0;
        font-size:13.5px;font-weight:700;text-transform:none;letter-spacing:0;color:var(--navy);cursor:pointer;}
    .audience-opts input{width:16px;height:16px;accent-color:var(--navy);cursor:pointer;}
    .btn{border:none;border-radius:999px;padding:11px 22px;font-weight:800;font-size:13.5px;}
    .btn-navy{background:var(--navy);color:#fff;}
    /* Home pill — same treatment as the Complaint Inbox page. */
    .btn-home{display:inline-flex;align-items:center;gap:7px;background:#fff;
        border:1px solid var(--line);color:var(--navy);font-weight:700;font-size:13.5px;
        padding:9px 18px 9px 14px;border-radius:999px;box-shadow:var(--shadow);
        margin-bottom:20px;transition:background .18s ease,color .18s ease,
        border-color .18s ease,transform .18s ease;}
    .btn-home:hover{background:var(--navy);color:#fff;border-color:var(--navy);transform:translateY(-1px);}
    .btn-home svg{width:16px;height:16px;}
    .btn-ghost{background:#fff;border:1.5px solid var(--line);color:var(--navy);padding:8px 16px;
        border-radius:999px;font-weight:700;font-size:12.5px;}
    .btn-danger{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;padding:8px 16px;
        border-radius:999px;font-weight:700;font-size:12.5px;}
    .ann{border:1px solid var(--line);border-radius:14px;padding:18px 20px;margin-bottom:14px;}
    .ann:last-child{margin-bottom:0;}
    .ann-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;}
    .ann-title{font-size:16px;font-weight:800;margin:0 0 6px;}
    .ann-body{color:#4b5563;font-size:14px;line-height:1.55;margin:0 0 10px;white-space:pre-wrap;}
    .ann-meta{color:var(--muted);font-size:12px;}
    .pill{display:inline-block;background:#eef1fb;color:var(--navy);font-size:11.5px;
        font-weight:800;padding:4px 11px;border-radius:999px;margin-right:6px;}
    .pill.faculty{background:#fdf3d7;color:#c4930f;}
    .alert{padding:11px 14px;border-radius:12px;margin-bottom:18px;font-size:14px;}
    .alert-ok{background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .alert-err{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;}
    .empty{color:var(--muted);text-align:center;padding:34px 8px;font-size:14px;}
    .actions{display:flex;gap:8px;flex-shrink:0;}
    @media(max-width:640px){.ann-head{flex-direction:column;}}
</style>
</head>
<body>
<?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>.topbar{display:none!important;}.wrap{margin-top:0!important;}</style>
<?php if(false): ?>
<nav class="topbar" hidden aria-hidden="true">
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="topbar-brand">
        <span class="brand-logo"><img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="PSU Logo"></span>
        UPSKILL
    </a>
    <div class="topbar-right">
        <a href="<?php echo e(route('notifications.index')); ?>" class="avatar-btn" title="Notifications">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
        </a>
        <a href="<?php echo e(route('admin.complaints')); ?>" class="avatar-btn" title="Complaint Inbox">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/>
            </svg>
        </a>
        <a href="<?php echo e(route('admin.profile')); ?>" class="avatar-btn" title="My Profile"
           <?php if(auth()->user()?->avatar_url): ?>
               style="background-image:url('<?php echo e(auth()->user()->avatar_url); ?>');background-size:cover;background-position:center;overflow:hidden;"
           <?php endif; ?>>
            <?php if (! (auth()->user()?->avatar_url)): ?>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V21.6h19.2V19.2c0-3.2-6.4-4.8-9.6-4.8z"/>
            </svg>
            <?php endif; ?>
        </a>
        <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline-flex;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn-danger">Logout</button>
        </form>
    </div>
</nav>
<?php endif; ?>

<div class="wrap">
    <h1 class="page-heading">Announcements</h1>
    <p class="page-sub">Post an announcement and choose who is allowed to see it.</p>

    <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn-home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.8V20h14V9.8"/>
        </svg>
        Home
    </a>

    <?php if(session('success')): ?>
        <div class="alert alert-ok"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-err">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <div class="card">
        <h2>New Announcement</h2>
        <form method="POST" action="<?php echo e(route('admin.announcements.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo e(old('title')); ?>"
                       placeholder="Announcement title" required>
            </div>
            <div class="field">
                <label for="body">Description</label>
                <textarea id="body" name="body" placeholder="What do you want to announce?" required><?php echo e(old('body')); ?></textarea>
            </div>
            <div class="field">
                <div class="audience-row">
                    <span class="audience-label">Who Can See Your Announcement</span>
                    <div class="audience-opts">
                        <label><input type="checkbox" name="audience[]" value="student" checked> Student</label>
                        <label><input type="checkbox" name="audience[]" value="faculty" checked> Faculty</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-navy">Post Announcement</button>
        </form>
    </div>

    
    <div class="card">
        <h2>Posted Announcements</h2>

        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ann">
                <div class="ann-head">
                    <div style="min-width:0;">
                        <h3 class="ann-title"><?php echo e($a->title); ?></h3>
                        <p class="ann-body"><?php echo e($a->body); ?></p>
                        <div>
                            <?php $__currentLoopData = $a->audience; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="pill <?php echo e($role); ?>"><?php echo e(ucfirst($role)); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="ann-meta" style="margin-top:8px;">
                            By <?php echo e($a->author); ?> · <?php echo e($a->created_at?->format('M j, Y g:i A')); ?>

                        </div>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn-ghost"
                                onclick="toggleEdit(<?php echo e($a->id); ?>)">Edit</button>
                        <form method="POST" action="<?php echo e(route('admin.announcements.destroy', $a->id)); ?>"
                              onsubmit="return confirm('Delete this announcement?');">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </div>
                </div>

                
                <form method="POST" action="<?php echo e(route('admin.announcements.update', $a->id)); ?>"
                      id="edit-<?php echo e($a->id); ?>" style="display:none;margin-top:16px;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="field">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo e($a->title); ?>" required>
                    </div>
                    <div class="field">
                        <label>Description</label>
                        <textarea name="body" required><?php echo e($a->body); ?></textarea>
                    </div>
                    <div class="field">
                        <div class="audience-row">
                            <span class="audience-label">Who Can See Your Announcement</span>
                            <div class="audience-opts">
                                <label><input type="checkbox" name="audience[]" value="student"
                                    <?php if(in_array('student', $a->audience, true)): echo 'checked'; endif; ?>> Student</label>
                                <label><input type="checkbox" name="audience[]" value="faculty"
                                    <?php if(in_array('faculty', $a->audience, true)): echo 'checked'; endif; ?>> Faculty</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-navy">Save Changes</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">No announcements yet.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleEdit(id) {
        var f = document.getElementById('edit-' + id);
        if (f) f.style.display = f.style.display === 'none' ? 'block' : 'none';
    }
</script>


<?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/announcements.blade.php ENDPATH**/ ?>