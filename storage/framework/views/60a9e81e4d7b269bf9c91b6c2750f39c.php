
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Inbox | Upskill</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root{--navy:#13176b;--gold:#dba617;--cyan:#7fe9e3;--muted:#6b7280;--line:#e5e7eb;
          --green:#15803d;--red:#ef4444;--shadow:0 10px 25px rgba(19,23,107,.08);}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;
        background:linear-gradient(135deg,#f8faff 0%,#f7f8fc 100%);}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;
        padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;overflow:hidden;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{position:relative;width:42px;height:42px;border-radius:50%;background:#fff;
        display:flex;align-items:center;justify-content:center;overflow:visible;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}
    .icon-badge{position:absolute;top:-3px;right:-3px;background:var(--red);color:#fff;
        border-radius:999px;font-size:10px;font-weight:800;padding:2px 6px;line-height:1.3;}
    .wrap{max-width:1120px;margin:28px auto;padding:0 22px 60px;}
    .page-head h2{font-size:28px;margin:0 0 6px;}
    .page-head p{margin:0 0 22px;color:var(--muted);font-size:14.5px;}
    .grid{display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);}
    .panel-hd{padding:16px 20px;border-bottom:1px solid var(--line);font-weight:800;font-size:14px;
        display:flex;justify-content:space-between;align-items:center;}
    .thread{display:block;padding:14px 20px;border-bottom:1px solid var(--line);}
    .thread:last-child{border-bottom:none;}
    .thread:hover{background:#f7f9ff;}
    .thread.active{background:#eef1fb;border-left:3px solid var(--navy);}
    .thread-top{display:flex;align-items:center;gap:8px;margin-bottom:3px;}
    .thread-subject{font-weight:800;font-size:14px;flex:1;min-width:0;overflow:hidden;
        text-overflow:ellipsis;white-space:nowrap;}
    .dot{width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;}
    .thread-meta{font-size:12px;color:var(--muted);}
    .status{font-size:10.5px;font-weight:800;padding:2px 9px;border-radius:999px;}
    .status.open{background:#fef3c7;color:#92400e;}
    .status.resolved{background:#e8f7ef;color:var(--green);}
    .msgs{padding:22px 24px;max-height:480px;overflow-y:auto;}
    .msg{margin-bottom:16px;display:flex;}
    .msg .bubble{max-width:76%;padding:12px 15px;border-radius:14px;font-size:14px;
        line-height:1.55;white-space:pre-wrap;}
    .msg.from-admin .bubble{background:#f1f3fb;color:#1f2937;border-bottom-left-radius:4px;}
    .msg.from-me{justify-content:flex-end;}
    .msg.from-me .bubble{background:var(--navy);color:#fff;border-bottom-right-radius:4px;}
    .msg-who{font-size:11.5px;color:var(--muted);margin-bottom:4px;font-weight:700;}
    .detail-hd{padding:18px 24px;border-bottom:1px solid var(--line);}
    .detail-hd h3{margin:0 0 5px;font-size:17px;}
    .detail-hd .who{font-size:12.5px;color:var(--muted);}
    .reply-box{border-top:1px solid var(--line);padding:18px 24px;}
    /* Field captions only. A bare label{} also hit the file picker's
       <label class="file-row">, forcing display:block (which stacked its two
       halves) and uppercasing its text. */
    .field > label{display:block;font-size:12px;font-weight:800;letter-spacing:.4px;
        text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    input[type=text],textarea{width:100%;border:1.5px solid #c9ccdb;border-radius:12px;
        padding:12px 14px;font-size:14px;font-family:inherit;color:var(--navy);background:#fff;}
    textarea{min-height:96px;resize:vertical;}
    input[type=text]:focus,textarea:focus{outline:none;border-color:var(--navy);}
    .field{margin-bottom:14px;}
    .btn-navy{background:var(--navy);color:#fff;border:none;border-radius:999px;
        padding:11px 24px;font-weight:800;font-size:13.5px;}
    .btn-ghost{background:#fff;border:1.5px solid var(--line);color:var(--navy);
        border-radius:999px;padding:9px 18px;font-weight:700;font-size:13px;}
    .alert{padding:11px 14px;border-radius:12px;margin-bottom:18px;font-size:14px;
        background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .alert-err{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;}
    .empty{color:var(--muted);text-align:center;padding:40px 12px;font-size:14px;}
    .new-card{margin-top:22px;}
    /* File type picker â€” same pattern as the faculty course form */
    /* One shared height keeps both controls identical. */
    .file-picker{--picker-h:46px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .file-picker > .file-type-select{flex:0 0 auto;}
    .file-type-select{border:1.5px solid #c9ccdb;border-radius:10px;background:#fff;color:var(--navy);
        font-size:13px;font-weight:700;height:var(--picker-h);padding:0 28px 0 15px;cursor:pointer;width:auto;
        -webkit-appearance:none;-moz-appearance:none;appearance:none;
        background-image:url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2313176b' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 9px center;background-size:11px 11px;}
    .file-type-select::-ms-expand{display:none;}
    .file-type-select:focus{outline:none;border-color:var(--navy);}
    /* Same box as .file-type-select, growing to fill the rest of the row. */
    .file-row{display:flex;align-items:stretch;border:1.5px solid #c9ccdb;border-radius:10px;
        overflow:hidden;background:#fff;cursor:pointer;flex:1 1 240px;min-width:0;
        height:var(--picker-h);margin:0;transition:border-color .18s ease;}
    .file-row:not(.is-disabled):hover{border-color:var(--navy);}
    .file-row .file-btn{display:flex;align-items:center;background:#fff;
        border-right:1.5px solid #dfe2ee;padding:0 19px;font-size:13px;font-weight:700;
        color:var(--navy);white-space:nowrap;text-transform:none;letter-spacing:0;margin:0;}
    .file-row .file-name{display:flex;align-items:center;padding:0 16px;font-size:13px;
        color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
        text-transform:none;letter-spacing:0;margin:0;}
    .file-row input[type=file]{display:none;}
    /* pointer-events:none is what actually blocks the click â€” a disabled
       <input> inside a <label> still lets it through in some browsers. */
    /* Locked: crisp, not dimmed. Only the placeholder is muted. */
    .file-row.is-disabled{cursor:not-allowed;pointer-events:none;}
    .file-row.is-disabled .file-name{color:#aab0c0;}
    .att-hint{font-size:11.5px;color:var(--muted);margin-top:7px;}
    .att-preview{margin-top:10px;display:none;}
    .att-preview img{max-width:200px;border-radius:10px;border:1px solid var(--line);}
    /* Attachment shown inside a sent message */
    .msg-attach{margin-top:8px;}
    .msg-attach img{max-width:230px;max-height:180px;border-radius:12px;
        border:1px solid var(--line);cursor:zoom-in;display:block;}
    #lightbox{position:fixed;inset:0;background:rgba(8,11,45,.88);display:none;
        align-items:center;justify-content:center;z-index:9999;padding:32px;}
    #lightbox.open{display:flex;}
    #lightbox img{max-width:92vw;max-height:88vh;border-radius:10px;}
    #lightbox .lb-close{position:absolute;top:20px;right:26px;background:#fff;border:none;
        border-radius:999px;width:40px;height:40px;font-size:20px;font-weight:800;
        color:var(--navy);cursor:pointer;line-height:1;}
    .count-pill{background:var(--red);color:#fff;border-radius:999px;font-size:11px;
        font-weight:800;padding:3px 9px;}
    @media(max-width:900px){.grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="main">
        <div class="wrap">
    <div class="page-head">
        <h2>My Inbox</h2>
        <p>Announcements from the administrators appear on your
           <a href="<?php echo e(route('notifications.index')); ?>" style="color:var(--navy);font-weight:700;text-decoration:underline;">notifications page</a>.</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-err">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        
        <div class="panel">
            <div class="panel-hd">
                <span>My Messages</span>
                <?php if(($unreadCount ?? 0) > 0): ?><span class="count-pill"><?php echo e($unreadCount); ?></span><?php endif; ?>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a class="thread <?php echo e($selected && $selected->id === $t->id ? 'active' : ''); ?>"
                   href="<?php echo e(route('inbox.index', ['thread' => $t->id])); ?>">
                    <div class="thread-top">
                        <span class="thread-subject"><?php echo e($t->subject); ?></span>
                        <?php if($t->unreadForStudent()): ?><span class="dot"></span><?php endif; ?>
                    </div>
                    <div class="thread-meta">
                        <?php echo e($t->lastActivityAt()?->diffForHumans()); ?>

                        <span class="status <?php echo e($t->status); ?>"><?php echo e(ucfirst($t->status)); ?></span>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty">You haven't sent any messages yet.</div>
            <?php endif; ?>
        </div>

        
        <div class="panel">
            <?php if($selected): ?>
                <div class="detail-hd">
                    <h3><?php echo e($selected->subject); ?></h3>
                    <div class="who">Sent <?php echo e($selected->created_at?->format('M j, Y g:i A')); ?></div>
                </div>

                <div class="msgs">
                    <div class="msg from-me">
                        <div>
                            <div class="msg-who" style="text-align:right;">You</div>
                            <div class="bubble"><?php echo e($selected->message); ?></div>
                            <?php if($selected->attachment_url): ?>
                                <div class="msg-attach">
                                    <?php if($selected->attachmentIsImage()): ?>
                                        <img src="<?php echo e(asset($selected->attachment_url)); ?>"
                                             alt="<?php echo e($selected->attachment_name); ?>"
                                             onclick="openLightbox(this.src)">
                                    <?php else: ?>
                                        <a href="<?php echo e(asset($selected->attachment_url)); ?>" target="_blank"
                                           style="text-decoration:underline;font-size:12.5px;">
                                            ðŸ“Ž <?php echo e($selected->attachment_name ?? 'Attachment'); ?>

                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php $__currentLoopData = $selected->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="msg <?php echo e($reply->is_admin ? 'from-admin' : 'from-me'); ?>">
                            <div>
                                <div class="msg-who" style="<?php echo e($reply->is_admin ? '' : 'text-align:right;'); ?>">
                                    <?php echo e($reply->is_admin ? 'Administrator' : 'You'); ?>

                                    - <?php echo e($reply->created_at?->diffForHumans()); ?>

                                </div>
                                <div class="bubble"><?php echo e($reply->body); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="reply-box">
                    <form method="POST" action="<?php echo e(route('inbox.reply', $selected->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <textarea name="body" placeholder="Write a reply..." required></textarea>
                        <div style="margin-top:12px;">
                            <button type="submit" class="btn-navy">Send Reply</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty">Select a message on the left, or send a new one below.</div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="panel new-card">
        <div class="panel-hd"><span>Send a New Message</span></div>
        <div style="padding:22px 24px;">
            <form method="POST" action="<?php echo e(route('inbox.store')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" value="<?php echo e(old('subject')); ?>"
                           placeholder="What is this about?" required>
                </div>
                <div class="field">
                    <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="Describe your concern..." required><?php echo e(old('message')); ?></textarea>
                </div>
                <div class="field">
                    <label>Attach an Image (optional)</label>
                    <div class="file-picker">
                        <select class="file-type-select" id="attType" onchange="attTypeChanged()">
                            <option value="">Select file type...</option>
                            <option value="JPG">JPG / JPEG</option>
                            <option value="PNG">PNG</option>
                            <option value="WEBP">WEBP</option>
                            <option value="GIF">GIF</option>
                        </select>
                        <label class="file-row is-disabled" id="attRow">
                            <span class="file-btn">Choose File</span>
                            <span class="file-name" id="attName">Select a file type first</span>
                            <input type="file" name="attachment" id="attInput" disabled
                                   onchange="attFileChanged(this)">
                        </label>
                    </div>
                    <p class="att-hint">A screenshot helps the administrators understand faster. Max 10 MB.</p>
                    <div class="att-preview" id="attPreview"><img id="attPreviewImg" alt="Selected attachment"></div>
                </div>

                <button type="submit" class="btn-navy">Send</button>
            </form>
        </div>
    </div>
</div>


<div id="lightbox" onclick="closeLightbox(event)">
    <button type="button" class="lb-close" onclick="closeLightbox(event, true)" aria-label="Close">&times;</button>
    <img id="lightbox-img" src="" alt="Attachment">
</div>

        </div>
    </main>
</div>

<script>
    var ATT_TYPES = { JPG: ['jpg','jpeg'], PNG: ['png'], WEBP: ['webp'], GIF: ['gif'] };

    function attTypeChanged() {
        var sel = document.getElementById('attType'), input = document.getElementById('attInput'),
            row = document.getElementById('attRow'),  span  = document.getElementById('attName');
        if (!sel || !input || !row || !span) return;

        var exts = ATT_TYPES[sel.value];
        input.value = '';
        document.getElementById('attPreview').style.display = 'none';

        if (!exts) {
            input.disabled = true;
            input.removeAttribute('accept');
            row.classList.add('is-disabled');
            span.textContent = 'Select a file type first';
            return;
        }

        input.disabled = false;
        input.setAttribute('accept', exts.map(function (e) { return '.' + e; }).join(','));
        row.classList.remove('is-disabled');
        span.textContent = 'No File Chosen';
    }

    function attFileChanged(input) {
        var span = document.getElementById('attName'), sel = document.getElementById('attType'),
            preview = document.getElementById('attPreview'), img = document.getElementById('attPreviewImg');
        if (!span) return;

        if (!input.files || !input.files.length) {
            span.textContent = sel && sel.value ? 'No File Chosen' : 'Select a file type first';
            preview.style.display = 'none';
            return;
        }

        var file = input.files[0];
        var ext  = (file.name.split('.').pop() || '').toLowerCase();
        var exts = sel ? ATT_TYPES[sel.value] : null;

        if (exts && exts.indexOf(ext) === -1) {
            alert('"' + file.name + '" is not a ' + sel.options[sel.selectedIndex].text + ' file.');
            input.value = '';
            span.textContent = 'No File Chosen';
            preview.style.display = 'none';
            return;
        }

        span.textContent = file.name;
        img.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox(e, force) {
        if (!force && e && e.target.id === 'lightbox-img') return;
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox(null, true);
    });
</script>


<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/inbox.blade.php ENDPATH**/ ?>