
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($course->title); ?> – Courses &amp; Badges</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy: #071550;
            --title: #0d2b6b;
            --blue: #0b4ea2;
            --soft: #eaf2ff;
            --yellow: #f4c430;
            --yellow-dark: #e8b91f;
            --yellow-soft: #fff6d6;
            --yellow-text: #7a5b00;
            --surface: #f5f7fa;
            --white: #ffffff;
            --line: #e2e7ef;
            --text: #17212b;
            --muted: #687385;
            --danger: #c0352d;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        .ico { width: 18px; height: 18px; flex: 0 0 18px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        /* Layout (shared topbar + sidebar offset) */
        .layout { display: block; min-height: calc(100vh - 66px); }
        .main {
            margin-left: 238px !important;
            width: calc(100% - 238px) !important;
            min-width: 0;
            padding: 24px 30px 56px;
        }
        .page-wrap { max-width: 1180px; margin: 0 auto; }

        .back-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 14px; font-size: .84rem; font-weight: 600; color: var(--blue); }
        .back-link .ico { width: 16px; height: 16px; }
        .back-link:hover { color: var(--navy); }

        /* Cards */
        .card { background: var(--white); border: 1px solid var(--line); border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 3px rgba(9,37,95,.04); }
        .card-title { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; font-size: .95rem; font-weight: 700; color: var(--title); }
        .card-title .ico { color: var(--blue); }
        .card-title small { margin-left: auto; font-size: .74rem; font-weight: 400; color: var(--muted); }
        .card p { font-size: .88rem; line-height: 1.7; color: #4b5563; }
        .stack { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

        /* Banners */
        .banner { border-radius: 10px; padding: 13px 16px; margin-bottom: 14px; font-size: .86rem; line-height: 1.55; white-space: pre-wrap; }
        .banner strong { display: block; margin-bottom: 3px; font-size: .78rem; }
        .banner-note   { background: var(--yellow-soft); border: 1px solid var(--yellow); color: var(--yellow-text); }
        .banner-denied { background: #fff1f0; border: 1px solid #f3c8c4; color: var(--danger); }
        .alert-success { background: #eaf8ef; border: 1px solid #ccebd6; color: #166534; border-radius: 10px; padding: 11px 16px; font-size: .86rem; font-weight: 600; margin-bottom: 14px; }

        /* Hero */
        .hero { display: grid; grid-template-columns: 196px minmax(0, 1fr) 270px; gap: 0 24px; align-items: stretch; padding: 16px; margin-bottom: 16px; }
        .thumb { position: relative; width: 196px; height: 148px; border-radius: 10px; overflow: hidden; background: var(--soft); display: flex; align-items: center; justify-content: center; color: #9db6dc; }
        .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .thumb .ico { width: 42px; height: 42px; }
        .featured-tag { position: absolute; top: 8px; left: 8px; padding: 3px 10px; border-radius: 6px; background: var(--yellow); color: var(--navy); font-size: .72rem; font-weight: 700; }

        .hero-info { min-width: 0; padding: 4px 0; }
        .detail-title { font-size: 1.4rem; font-weight: 700; color: var(--title); line-height: 1.25; }
        .by { display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: .86rem; color: var(--muted); }
        .by .ico { width: 15px; height: 15px; color: var(--blue); }
        .meta-row { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 12px; font-size: .8rem; color: var(--muted); }
        .meta-row span { display: inline-flex; align-items: center; gap: 7px; }
        .meta-row .ico { width: 15px; height: 15px; color: var(--blue); }
        .hero-desc { margin-top: 12px; font-size: .86rem; line-height: 1.6; color: #4b5563; overflow-wrap: anywhere; }
        .detail-title, .by, .card-title { overflow-wrap: anywhere; }

        /* Faculty-authored HTML (descriptions, lesson content) */
        .rich { font-size: .88rem; line-height: 1.7; color: #4b5563; overflow-wrap: anywhere; word-break: break-word; min-width: 0; }
        .rich > *:first-child { margin-top: 0; }
        .rich p { margin: 0 0 .7em; }
        .rich h1, .rich h2, .rich h3, .rich h4 { margin: 1em 0 .4em; color: var(--title); line-height: 1.3; }
        .rich ul, .rich ol { margin: 0 0 .7em; padding-left: 1.4em; }
        .rich img, .rich video, .rich iframe { max-width: 100%; height: auto; border-radius: 8px; }
        .rich table { max-width: 100%; display: block; overflow-x: auto; border-collapse: collapse; }
        .rich a { color: var(--blue); text-decoration: underline; }

        .hero-actions { display: flex; flex-direction: column; gap: 10px; padding: 4px 0 4px 24px; border-left: 1px solid var(--line); }
        .hero-actions form { display: flex; }
        .hero-actions form .btn { flex: 1; }
        .status-pill { align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 999px; font-size: .8rem; font-weight: 600; }
        .status-pill .dot { width: 9px; height: 9px; border-radius: 50%; }
        .status-pending  { background: var(--yellow-soft); color: var(--yellow-text); }
        .status-pending .dot { background: var(--yellow); }
        .status-approved { background: var(--soft); color: var(--blue); }
        .status-approved .dot { background: var(--blue); }
        .status-denied   { background: #fff1f0; color: var(--danger); }
        .status-denied .dot { background: #d64545; }
        .status-draft    { background: #f1f3f7; color: #5b6678; }
        .status-draft .dot { background: #a3adbd; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 16px; border-radius: 8px; border: 1px solid var(--line);
            background: var(--white); color: var(--text); font: inherit; font-size: .84rem; font-weight: 600; cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }
        .btn .ico { width: 16px; height: 16px; }
        .btn:hover { background: #f5f7fa; }
        .btn-approve { background: var(--yellow); border-color: var(--yellow); color: var(--navy); }
        .btn-approve:hover { background: var(--yellow-dark); border-color: var(--yellow-dark); }
        .btn-outline { color: var(--blue); border-color: #bcd0ea; }
        .btn-outline:hover { background: var(--soft); }
        .btn-delete { color: var(--danger); border-color: #efb9b5; }
        .btn-delete:hover { background: #fff1f0; }
        .btn-blue { background: var(--blue); border-color: var(--blue); color: #fff; }
        .btn-blue:hover { background: #093f85; }

        /* Deny panel */
        #deny-panel { display: none; grid-column: 1 / -1; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line); }
        #deny-panel.open { display: block; }
        #deny-panel label { display: block; margin-bottom: 6px; font-size: .8rem; font-weight: 600; color: var(--muted); }
        #deny-panel textarea { width: 100%; min-height: 110px; padding: 12px 14px; resize: vertical; border: 1px solid var(--line); border-radius: 10px; font: inherit; font-size: .88rem; color: var(--text); outline: none; }
        #deny-panel textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(11,78,162,.1); }
        .field-error { margin-top: 6px; font-size: .8rem; color: var(--danger); }
        .deny-actions { display: flex; gap: 8px; margin-top: 10px; }

        /* Body columns */
        .cols { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 16px; align-items: start; }

        /* Quick facts */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px 8px; padding: 16px 12px; }
        .stat { display: flex; align-items: center; gap: 10px; padding: 0 8px; min-width: 0; }
        .stat-ico { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; background: var(--soft); color: var(--blue); display: flex; align-items: center; justify-content: center; }
        .stat-ico .ico { width: 17px; height: 17px; }
        .stat small { display: block; font-size: .72rem; color: var(--muted); }
        .stat b { display: block; font-size: .9rem; font-weight: 700; color: var(--title); overflow-wrap: break-word; }

        .two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }

        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { padding: 5px 13px; border-radius: 999px; background: var(--soft); color: var(--blue); font-size: .77rem; font-weight: 500; }
        .chip.rel { background: var(--yellow-soft); color: var(--yellow-text); }
        .related-head { margin: 18px 0 4px; font-size: .86rem; font-weight: 700; color: var(--title); }
        .related-hint { margin-bottom: 10px; font-size: .76rem; color: var(--muted); line-height: 1.5; }

        .obj-list { list-style: none; counter-reset: obj; }
        .obj-list li { counter-increment: obj; display: flex; align-items: flex-start; gap: 14px; padding: 7px 0; font-size: .86rem; color: #4b5563; }
        .obj-list li::before { content: counter(obj); flex: 0 0 24px; height: 24px; border-radius: 50%; background: var(--yellow); color: var(--navy); display: flex; align-items: center; justify-content: center; font-size: .74rem; font-weight: 700; }

        /* Course content accordion */
        .module-block { border: 1px solid var(--line); border-radius: 9px; margin-bottom: 8px; background: #f8fafd; overflow: hidden; }
        .module-block:last-child { margin-bottom: 0; }
        .module-head { display: flex; align-items: center; gap: 12px; width: 100%; padding: 11px 14px; border: 0; background: transparent; font: inherit; text-align: left; cursor: pointer; }
        .module-title { flex: 1; min-width: 0; font-size: .86rem; font-weight: 600; color: var(--title); }
        .module-count { font-size: .74rem; color: var(--muted); white-space: nowrap; }
        .module-head .ico { width: 16px; height: 16px; color: var(--muted); transition: transform .18s ease; }
        .module-block.open .module-head .ico { transform: rotate(180deg); }
        .module-body { display: none; background: var(--white); border-top: 1px solid var(--line); }
        .module-block.open .module-body { display: block; }
        .module-sub { padding: 10px 14px 4px; font-size: .8rem; color: var(--muted); }

        .lesson-row, .quiz-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 9px 14px; border-top: 1px solid var(--line); font-size: .83rem; cursor: pointer; }
        .lesson-row:first-child { border-top: 0; }
        .lesson-row:hover { background: #f8fafd; }
        .lesson-row::before { content: '▸'; margin-right: 8px; font-size: .72rem; color: var(--muted); }
        .lesson-row > span:first-of-type { flex: 1; }
        .lesson-meta { font-size: .75rem; color: var(--muted); }
        .quiz-row { background: var(--yellow-soft); color: var(--yellow-text); font-weight: 600; }
        .quiz-row span:last-child { font-size: .75rem; }
        .lesson-detail, .quiz-detail { display: none; padding: 10px 16px 12px 30px; border-top: 1px dashed var(--line); font-size: .82rem; color: var(--muted); background: #fbfcfe; }
        .lesson-detail.open, .quiz-detail.open { display: block; }
        .lesson-desc { margin: 0 0 6px; line-height: 1.5; }
        .lesson-empty { font-style: italic; }
        .lesson-file { color: var(--blue); font-weight: 600; text-decoration: underline; }
        .quiz-instructions { margin-bottom: 10px; font-style: italic; }
        .quiz-question { padding: 8px 0; border-top: 1px solid var(--line); }
        .quiz-question:first-of-type { border-top: 0; }
        .quiz-question-head { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
        .quiz-question-text { font-weight: 600; color: var(--text); }
        .quiz-question-meta { white-space: nowrap; font-size: .74rem; }
        .quiz-options { margin: 4px 0 0; padding-left: 4px; list-style: none; }
        .quiz-options li { padding: 2px 0; }
        .quiz-options li.is-correct { color: #166534; font-weight: 600; }
        .quiz-answer { margin-top: 4px; color: #166534; }
        .empty-note { font-size: .86rem; color: var(--muted); }

        /* Award */
        .award-badge { display: block; width: 112px; max-width: 100%; height: auto; margin: 0 auto 8px; }
        .award-name { text-align: center; font-size: 1rem; font-weight: 700; color: var(--title); word-break: break-word; }
        .award-sub { text-align: center; margin-top: 2px; font-size: .84rem; color: var(--muted); }
        .award-none { padding: 18px 12px; border: 1px dashed #d3dae6; border-radius: 10px; text-align: center; font-size: .8rem; color: #98a2b3; }
        .cert-head { display: flex; align-items: center; gap: 8px; margin: 16px 0 10px; font-size: .8rem; color: var(--muted); }
        .cert-head .ico { width: 16px; height: 16px; color: var(--blue); }
        .cert-tag { margin-left: auto; padding: 4px 14px; border-radius: 999px; background: var(--soft); color: var(--blue); font-size: .74rem; font-weight: 600; }
        .cert-box { padding: 10px; border-radius: 10px; background: #f5f8fc; overflow-x: auto; }
        .cert-box img { display: block; width: 100%; height: auto; border-radius: 6px; }
        .cert-box embed { display: block; width: 100%; height: 240px; }
        .cert-btn { width: 100%; margin-top: 10px; color: var(--blue); border-color: #bcd0ea; }

        /* Progress */
        .progress-line { display: flex; align-items: center; gap: 12px; }
        .progress-track { flex: 1; height: 8px; border-radius: 4px; background: #eef1f6; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--yellow); border-radius: 4px; }
        .pct-num { font-size: .86rem; font-weight: 700; color: var(--title); }
        .pct-sub { margin-top: 8px; font-size: .78rem; color: var(--muted); }

        /* Enrollment review */
        .enr-list { max-height: 360px; overflow-y: auto; }
        .enr-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; padding: 10px 0; border-top: 1px solid var(--line); }
        .enr-row:first-child { border-top: 0; padding-top: 0; }
        .enr-avatar { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; background: var(--soft); color: #7c98c4; display: flex; align-items: center; justify-content: center; }
        .enr-avatar .ico { width: 17px; height: 17px; }
        .enr-name { flex: 1; min-width: 100px; font-size: .84rem; font-weight: 600; color: var(--title); }
        .enr-name small { display: block; font-size: .72rem; font-weight: 400; color: var(--muted); }
        .pill { padding: 3px 10px; border-radius: 999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
        .pill-pending { background: var(--yellow-soft); color: var(--yellow-text); }
        .pill-ok { background: #e3f6ea; color: #166534; }
        .pill-bad { background: #fff1f0; color: var(--danger); }
        .pill-neutral { background: #f1f3f7; color: #5b6678; }
        .enr-actions { display: flex; gap: 6px; margin-left: auto; }
        .enr-actions .btn { padding: 5px 12px; font-size: .74rem; }

        @media (max-width: 1100px) {
            .cols { grid-template-columns: 1fr; }
        }
        @media (max-width: 1024px) {
            .main { margin-left: 0 !important; width: 100% !important; padding: 22px 18px 48px; }
        }
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; gap: 16px; }
            .thumb { width: 100%; height: 190px; }
            .hero-actions { flex-direction: row; flex-wrap: wrap; padding: 16px 0 0; border-left: 0; border-top: 1px solid var(--line); }
            .hero-actions .status-pill { flex-basis: 100%; }
            .hero-actions form, .hero-actions > .btn { flex: 1 1 140px; }
            .two { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .main { padding: 18px 12px 40px; }
            .card { padding: 18px 16px; }
            .stat { border-right: 0; }
        }
    </style>
</head>
<body>
<?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="i-user" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
    <symbol id="i-bars" viewBox="0 0 24 24"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></symbol>
    <symbol id="i-cap" viewBox="0 0 24 24"><path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-layers" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></symbol>
    <symbol id="i-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></symbol>
    <symbol id="i-award" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.48 12.89L17 22l-5-3-5 3 1.52-9.11"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></symbol>
    <symbol id="i-msg" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
    <symbol id="i-down" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></symbol>
    <symbol id="i-ext" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></symbol>
    <symbol id="i-left" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></symbol>
</svg>

<div class="layout admin-layout">

    
    <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">
    <div class="page-wrap">

        <a href="<?php echo e(route('admin.courses')); ?>" class="back-link">
            <svg class="ico"><use href="#i-left"/></svg> Back to courses and badges
        </a>

        <?php if(session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(! empty($course->change_note)): ?>
            <div class="banner banner-note"><strong>What the author changed</strong><?php echo e($course->change_note); ?></div>
        <?php endif; ?>

        <?php if(! empty($course->denial_feedback)): ?>
            <div class="banner banner-denied"><strong>Previous denial feedback</strong><?php echo e($course->denial_feedback); ?></div>
        <?php endif; ?>

        
        <?php
            $plainDesc = str_replace(['</p>', '</div>', '</li>', '<br>', '<br/>', '<br />'], ' ', (string) ($course->description ?? ''));
            $plainDesc = html_entity_decode(strip_tags($plainDesc), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plainDesc = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $plainDesc));
            $shortDesc = \Illuminate\Support\Str::limit($plainDesc, 170);
        ?>
        <section class="card hero">
            <div class="thumb">
                <?php if(! empty($course->thumbnail_url)): ?>
                    <img src="<?php echo e($course->thumbnail_url); ?>" alt="">
                <?php else: ?>
                    <svg class="ico"><use href="#i-cap"/></svg>
                <?php endif; ?>
                <?php if(! empty($course->is_featured)): ?>
                    <span class="featured-tag">Featured</span>
                <?php endif; ?>
            </div>

            <div class="hero-info">
                <div class="detail-title"><?php echo e($course->title); ?></div>
                <div class="by"><svg class="ico"><use href="#i-user"/></svg>By <?php echo e($course->instructor ?? 'Faculty'); ?></div>
                <div class="meta-row">
                    <?php if($course->level): ?><span><svg class="ico"><use href="#i-bars"/></svg><?php echo e($course->level); ?></span><?php endif; ?>
                    <?php if($course->duration): ?><span><svg class="ico"><use href="#i-clock"/></svg><?php echo e($course->duration); ?></span><?php endif; ?>
                    <?php if($course->category): ?><span><svg class="ico"><use href="#i-tag"/></svg><?php echo e($course->category); ?></span><?php endif; ?>
                    <?php if($course->created_at): ?><span><svg class="ico"><use href="#i-cal"/></svg>Created <?php echo e($course->created_at->format('M j, Y')); ?></span><?php endif; ?>
                </div>
                <?php if($shortDesc !== ''): ?>
                    <p class="hero-desc"><?php echo e($shortDesc); ?></p>
                <?php endif; ?>
            </div>

            <div class="hero-actions">
                <span class="status-pill status-<?php echo e($course->status_key); ?>"><span class="dot"></span><?php echo e($course->status); ?></span>

                <?php if($course->status_key === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('admin.courses.approve', $course->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-approve"><svg class="ico"><use href="#i-check"/></svg>Approve</button>
                    </form>
                    <button type="button" class="btn btn-outline" id="deny-open"><svg class="ico"><use href="#i-msg"/></svg>Deny with feedback</button>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.courses.destroy', $course->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-delete"
                            onclick="return confirm('Permanently delete \'<?php echo e(addslashes($course->title)); ?>\'? Its modules, lessons, quizzes and enrollments will also be removed. This cannot be undone.');">
                        <svg class="ico"><use href="#i-trash"/></svg>Delete course
                    </button>
                </form>
            </div>

            <?php if($course->status_key === 'pending'): ?>
                <div id="deny-panel" class="<?php echo e($errors->has('denial_feedback') ? 'open' : ''); ?>">
                    <form method="POST" action="<?php echo e(route('admin.courses.deny', $course->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <label for="denial_feedback">Feedback for the author (required)</label>
                        <textarea id="denial_feedback" name="denial_feedback" required minlength="5"
                                  placeholder="Explain what needs to change before this course can be approved."><?php echo e(old('denial_feedback')); ?></textarea>
                        <?php $__errorArgs = ['denial_feedback'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="field-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="deny-actions">
                            <button type="submit" class="btn btn-approve">Send denial and feedback</button>
                            <button type="button" class="btn" id="deny-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <div class="cols">

            
            <div class="stack">

                <section class="card stats">
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-bars"/></svg></span><div><small>Level</small><b><?php echo e($course->level ?? '—'); ?></b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-clock"/></svg></span><div><small>Duration</small><b><?php echo e($course->duration ?? '—'); ?></b></div></div>
                    <!-- <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-cap"/></svg></span><div><small>Program</small><b><?php echo e($course->program ?? '—'); ?></b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-cal"/></svg></span><div><small>Term</small><b><?php echo e($course->term ?? '—'); ?></b></div></div> -->
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-users"/></svg></span><div><small>Students</small><b><?php echo e($course->students); ?></b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-layers"/></svg></span><div><small>Modules</small><b><?php echo e($course->modules_count); ?></b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-play"/></svg></span><div><small>Lessons</small><b><?php echo e($course->lessons_count); ?></b></div></div>
                </section>

                <div class="stack">
                    <section class="card">
                        <div class="card-title"><svg class="ico"><use href="#i-file"/></svg>Description</div>
                        <div class="rich"><?php echo $course->description ?: 'No description provided.'; ?></div>
                    </section>

                    <section class="card">
                        <div class="card-title"><svg class="ico"><use href="#i-tag"/></svg>Skills covered</div>
                        <?php if(! empty($course->skills)): ?>
                            <div class="chip-row">
                                <?php $__currentLoopData = $course->skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="chip"><?php echo e($skill); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-note">No skills listed.</div>
                        <?php endif; ?>

                        
                        <?php if(! empty($course->related_skills)): ?>
                            <div class="related-head">Related on</div>
                            <div class="related-hint">Set by faculty. Used to recommend this course to learners, and not shown on any student page.</div>
                            <div class="chip-row">
                                <?php $__currentLoopData = $course->related_skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="chip rel"><?php echo e($skill); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <?php if(! empty($course->objectives)): ?>
                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-target"/></svg>Learning objectives</div>
                    <ul class="obj-list">
                        <?php $__currentLoopData = $course->objectives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $objective): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($objective); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </section>
                <?php endif; ?>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-file"/></svg>Course content</div>
                    <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $mTitle = preg_match('/^module\s*\d/i', $module->title)
                                ? $module->title
                                : 'Module ' . $loop->iteration . ': ' . $module->title;
                            $lCount = count($module->lessons);
                        ?>
                        <div class="module-block open">
                            <button type="button" class="module-head" onclick="this.parentElement.classList.toggle('open')">
                                <span class="module-title"><?php echo e($mTitle); ?></span>
                                <span class="module-count"><?php echo e($lCount); ?> <?php echo e($lCount === 1 ? 'lesson' : 'lessons'); ?><?php if($module->quiz): ?> · 1 quiz <?php endif; ?></span>
                                <svg class="ico"><use href="#i-down"/></svg>
                            </button>

                            <div class="module-body">
                                <?php if($module->description): ?>
                                    <div class="module-sub"><?php echo $module->description; ?></div>
                                <?php endif; ?>

                                <?php $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="lesson-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                        <span><?php echo e($lesson->title); ?></span>
                                        <span class="lesson-meta"><?php echo e($lesson->type); ?><?php echo e($lesson->duration ? ' · ' . $lesson->duration : ''); ?></span>
                                    </div>
                                    <?php
                                        $lBody  = $lesson->content ?? $lesson->body ?? null;
                                        $lVideo = $lesson->video_url ?? null;
                                    ?>
                                    <div class="lesson-detail open">
                                        <?php if($lesson->description): ?>
                                            <div class="rich"><?php echo $lesson->description; ?></div>
                                        <?php endif; ?>
                                        <?php if($lBody): ?>
                                            <div class="rich"><?php echo $lBody; ?></div>
                                        <?php endif; ?>
                                        <?php if($lVideo): ?>
                                            <p class="lesson-desc"><a class="lesson-file" href="<?php echo e($lVideo); ?>" target="_blank" rel="noopener">Open video</a></p>
                                        <?php endif; ?>
                                        <?php if($lesson->file_url): ?>
                                            <p class="lesson-desc"><a class="lesson-file" href="<?php echo e($lesson->file_url); ?>" target="_blank" rel="noopener">Open attached file (<?php echo e($lesson->file_name); ?>)</a></p>
                                        <?php endif; ?>
                                        <?php if(! $lesson->description && ! $lBody && ! $lVideo && ! $lesson->file_url): ?>
                                            <p class="lesson-desc lesson-empty">No description or file attached.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($module->quiz): ?>
                                    <div class="quiz-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                        <span>Quiz: <?php echo e($module->quiz->title); ?></span>
                                        <span><?php echo e($module->quiz->questions_count); ?> questions · Pass <?php echo e($module->quiz->passing_score); ?>%<?php echo e($module->quiz->time_limit ? ' · ' . $module->quiz->time_limit . ' min' : ''); ?></span>
                                    </div>
                                    <div class="quiz-detail">
                                        <?php if($module->quiz->instructions): ?>
                                            <p class="quiz-instructions"><?php echo e($module->quiz->instructions); ?></p>
                                        <?php endif; ?>
                                        <?php $__currentLoopData = $module->quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qi => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="quiz-question">
                                                <div class="quiz-question-head">
                                                    <span class="quiz-question-text">Q<?php echo e($qi + 1); ?>. <?php echo e($q->question); ?></span>
                                                    <span class="quiz-question-meta"><?php echo e($q->type); ?><?php echo e($q->points ? ' · ' . $q->points . ' pts' : ''); ?></span>
                                                </div>
                                                <?php if(! empty($q->options)): ?>
                                                    <ul class="quiz-options">
                                                        <?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li class="<?php echo e($opt === $q->correct_answer ? 'is-correct' : ''); ?>">
                                                                <?php echo e(chr(65 + $oi)); ?>. <?php echo e($opt); ?><?php if($opt === $q->correct_answer): ?> (correct) <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                <?php elseif($q->correct_answer): ?>
                                                    <div class="quiz-answer">Answer: <strong><?php echo e($q->correct_answer); ?></strong></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if($lCount === 0 && ! $module->quiz): ?>
                                    <div class="module-sub empty-note">No lessons in this module yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-note">No modules have been added to this course yet.</div>
                    <?php endif; ?>
                </section>
            </div>

            
            <div class="stack">

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-award"/></svg>Completion award</div>

                    <?php if($course->badge_icon): ?>
                        <img class="award-badge" src="<?php echo e($course->badge_icon); ?>" alt="<?php echo e($course->badge); ?>">
                    <?php else: ?>
                        <div class="award-none">No badge designed yet</div>
                    <?php endif; ?>
                    <div class="award-name" style="margin-top:8px;"><?php echo e($course->badge); ?></div>
                    <?php if($course->badge_level): ?>
                        <div class="award-sub"><?php echo e($course->badge_level); ?></div>
                    <?php endif; ?>

                    <div class="cert-head">
                        <svg class="ico"><use href="#i-award"/></svg>Certificate
                        <?php if($course->certificate_enabled): ?>
                            <span class="cert-tag"><?php echo e($course->certificate_mode === 'upload' ? 'Uploaded' : 'Auto-generated'); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if(! $course->certificate_enabled): ?>
                        <div class="award-none">No certificate configured yet</div>
                    <?php elseif($course->certificate_mode === 'upload' && $course->certificate_file): ?>
                        <?php $ext = strtolower(pathinfo($course->certificate_file, PATHINFO_EXTENSION)); ?>
                        <div class="cert-box">
                            <?php if($ext === 'pdf'): ?>
                                <embed src="<?php echo e(asset($course->certificate_file)); ?>" type="application/pdf">
                            <?php else: ?>
                                <img src="<?php echo e(asset($course->certificate_file)); ?>" alt="Uploaded certificate">
                            <?php endif; ?>
                        </div>
                        <a class="btn cert-btn" href="<?php echo e(asset($course->certificate_file)); ?>" target="_blank" rel="noopener">
                            View certificate <svg class="ico"><use href="#i-ext"/></svg>
                        </a>
                    <?php else: ?>
                        <div class="cert-box">
                            <?php echo $__env->make('components.certificate', ['cert' => $course->certificate], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-bars"/></svg>Course progress</div>
                    <div class="progress-line">
                        <div class="progress-track"><div class="progress-fill" style="width: <?php echo e($course->percent); ?>%"></div></div>
                        <span class="pct-num"><?php echo e($course->percent); ?>%</span>
                    </div>
                    <div class="pct-sub">Average completion · <?php echo e($course->students); ?> enrolled</div>
                </section>

                
                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-users"/></svg>Enrollment review</div>
                    <div class="enr-list">
                        <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $unit = $enrollment->academic_unit_confirmation_status;
                                $pillClass = $enrollment->academic_confirmation_actionable ? 'pill-pending'
                                    : ($unit === 'confirmed' ? 'pill-ok' : ($unit === 'rejected' ? 'pill-bad' : 'pill-neutral'));
                                $tip = 'Faculty verification: ' . ucfirst(str_replace('_', ' ', $enrollment->faculty_verification_status))
                                     . ' · Academic unit: ' . ucfirst(str_replace('_', ' ', $unit))
                                     . ' · Completed: ' . ($enrollment->completed_at?->format('M j, Y') ?? '—');
                            ?>
                            <div class="enr-row" title="<?php echo e($tip); ?>">
                                <span class="enr-avatar"><svg class="ico"><use href="#i-user"/></svg></span>
                                <div class="enr-name">
                                    <?php echo e($enrollment->student_name); ?>

                                    <small><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->completion_status))); ?></small>
                                </div>
                                <span class="pill <?php echo e($pillClass); ?>"><?php echo e($enrollment->institutional_label); ?></span>

                                <?php if($enrollment->academic_confirmation_actionable): ?>
                                    <form method="POST" action="<?php echo e(route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id])); ?>" class="enr-actions">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" name="decision" value="confirmed" class="btn btn-blue">Confirm</button>
                                        <button type="submit" name="decision" value="rejected" class="btn">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="empty-note">No enrollments for this course yet.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

    </div>
    </main>
</div>

<script>
    (function () {
        var open = document.getElementById('deny-open');
        var panel = document.getElementById('deny-panel');
        var cancel = document.getElementById('deny-cancel');
        if (!panel) return;

        function show() { panel.classList.add('open'); if (open) open.style.display = 'none'; }
        function hide() { panel.classList.remove('open'); if (open) open.style.display = ''; }

        if (open) open.addEventListener('click', show);
        if (cancel) cancel.addEventListener('click', hide);
        if (location.hash === '#deny-panel' || panel.classList.contains('open')) show();
    })();
</script>


<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:46px;height:46px;border-radius:50%;border:none;background:#071550;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(7,21,80,.28);transition:transform .15s ease,background .2s ease,color .2s ease;}
    #back-to-top-btn:hover{background:#f4c430;color:#071550;transform:translateY(-2px);}
    #back-to-top-btn svg{width:21px;height:21px;}
</style>
<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function t() { btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true });
        t();
    })();
</script>

<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/courses/show.blade.php ENDPATH**/ ?>