{{-- resources/views/student/Course_Enrollment.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $course->title ?? 'Course' }} | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b;--navy-deep:#0c0f4d;--gold:#dba617;
        --cyan:#7fe9e3;--thumb:#d8e3f8;--ink:#13176b;
        --muted:#6b7280;--line:#e5e7eb;
        --shadow:0 10px 25px rgba(19,23,107,0.08);
        --green:#22c55e;--red:#ef4444;
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,sans-serif;color:var(--ink);margin:0;background:#f4f5f9;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* â”€â”€ Topbar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;position:sticky;top:0;z-index:100;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* â”€â”€ Layout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .layout{display:grid;grid-template-columns:294px 1fr;height:calc(100vh - 74px);}

    /* â”€â”€ Left: Course Nav â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .course-nav{background:#fff;display:flex;flex-direction:column;overflow:hidden;margin:24px 10px 24px 24px;border-radius:22px;border:1px solid var(--line);}
    .course-nav-header{background:var(--navy);color:#fff;padding:20px 18px 18px;flex-shrink:0;border-radius:22px 22px 0 0;}
    .nav-category{font-size:12px;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;}
    .nav-title{font-size:15px;font-weight:800;line-height:1.3;margin-bottom:16px;}
    .nav-progress-row{display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:6px;}
    .nav-progress-track{width:100%;height:6px;background:rgba(255,255,255,.25);border-radius:999px;overflow:hidden;}
    .nav-progress-fill{height:100%;background:var(--cyan);border-radius:999px;transition:width .5s ease;}

    .modules-scroll{flex:1;overflow-y:auto;}
    .modules-scroll::-webkit-scrollbar{width:4px;}
    .modules-scroll::-webkit-scrollbar-thumb{background:#c9cce0;border-radius:4px;}

    .module-group{border-bottom:1px solid var(--line);}
    .module-toggle{width:100%;background:#f1f2f8;border:none;padding:0;display:flex;align-items:stretch;text-align:left;}
    .module-toggle:hover{background:#e8e9f4;}
    .module-title-area{flex:1;padding:14px 16px;display:flex;flex-direction:column;gap:2px;cursor:pointer;}
    .module-num{font-size:13px;font-weight:800;color:var(--navy);}
    .module-sub{font-size:12px;font-weight:600;color:var(--muted);}
    .module-chevron-btn{background:transparent;border:none;border-left:1px solid var(--line);padding:0 14px;display:flex;align-items:center;cursor:pointer;}
    .chevron{width:18px;height:18px;color:var(--navy);transition:transform .2s;}
    .chevron.open{transform:rotate(180deg);}

    /* Module states */
    .module-title-area.active{background:var(--navy);border-left:4px solid var(--cyan);}
    .module-title-area.active .module-num,.module-title-area.active .module-sub{color:#fff;}
    .module-title-area.completed{border-left:4px solid var(--green);}
    .module-title-area.completed .module-num{color:#166534;}

    .lesson-list{display:none;}
    .lesson-list.open{display:block;}

    /* Lesson items */
    .lesson-item{display:flex;align-items:center;padding:12px 16px 12px 20px;border-bottom:1px solid #f0f0f5;cursor:pointer;transition:background .15s;gap:8px;}
    .lesson-item:hover{background:#f0f1f8;}
    .lesson-item.active{background:#e8e9f4;border-left:3px solid var(--navy);}
    .lesson-item.lesson-correct{border-left:4px solid var(--green)!important;background:#f0fdf4;}
    .lesson-item.lesson-wrong{border-left:4px solid var(--red)!important;background:#fef2f2;}
    .lesson-title-text{font-size:13px;font-weight:600;color:var(--navy);line-height:1.3;flex:1;}
    .lesson-meta{font-size:11px;color:var(--muted);white-space:nowrap;flex-shrink:0;}
    .status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;display:none;}
    .lesson-correct .status-dot{display:inline-block;background:var(--green);box-shadow:0 0 0 3px rgba(34,197,94,.2);}
    .lesson-wrong   .status-dot{display:inline-block;background:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,.2);}

    /* Quiz row */
    .quiz-row{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#fef9e7;gap:10px;}
    /* Last block in the panel â€” round it so the corner stays clean even
       if a parent's overflow clipping is disabled. */
    .modules-scroll > .module-group:last-child .quiz-row{border-radius:0 0 22px 22px;}
    .modules-scroll{border-radius:0 0 22px 22px;}
    .quiz-info-title{font-size:13px;font-weight:800;color:var(--navy);margin-bottom:2px;}
    .quiz-info-sub{font-size:11px;color:var(--muted);}
    /* LOCKED quiz button */
    .btn-quiz-sm{font-weight:800;font-size:12px;padding:7px 16px;border-radius:6px;flex-shrink:0;transition:all .2s;}
    .btn-quiz-sm.locked{background:#e5e7eb;color:#9ca3af;border:1.5px solid #e5e7eb;cursor:not-allowed;}
    .btn-quiz-sm.unlocked{background:#fff;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.unlocked:hover{background:var(--navy);color:#fff;}
    .btn-quiz-sm.viewed{background:#e8e9f4;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.viewed:hover{background:#d0d2ea;}

    /* â”€â”€ Right: Content Area â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .lesson-content{display:flex;flex-direction:column;overflow-y:auto;padding:28px 36px 40px;gap:20px;background:#f4f5f9;}
    .lesson-content.welcome-mode{padding:24px;gap:0;background:#f4f5f9;}
    .lesson-header-btn{display:flex;align-items:center;justify-content:space-between;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;text-align:left;gap:14px;}
    /* Countdown shown beside the quiz title while a timed quiz runs */
    .quiz-timer{display:inline-flex;align-items:center;gap:7px;flex-shrink:0;background:rgba(255,255,255,.14);
        border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:6px 14px;
        font-size:14px;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:.3px;}
    .quiz-timer svg{width:15px;height:15px;}
    .quiz-timer.warning{background:rgba(245,158,11,.9);border-color:transparent;color:#3b2400;}
    .quiz-timer.danger{background:#ef4444;border-color:transparent;color:#fff;animation:timerPulse 1s ease-in-out infinite;}
    @keyframes timerPulse{0%,100%{opacity:1;}50%{opacity:.6;}}

    /* â”€â”€ VIEW 1: Welcome â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    #view-welcome{display:flex;flex-direction:column;gap:0;background:#fff;border-radius:20px;overflow:hidden;align-self:stretch;margin:0;box-shadow:0 4px 20px rgba(19,23,107,0.1);}
    /* Hero banner */
    .welcome-hero{background:linear-gradient(135deg,var(--navy) 0%,#1e2480 60%,#2a3199 100%);padding:48px 48px 52px;position:relative;overflow:hidden;flex-shrink:0;}
    .welcome-hero::before{content:'';position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(127,233,227,.08);}
    .welcome-hero::after{content:'';position:absolute;bottom:-60px;right:60px;width:140px;height:140px;border-radius:50%;background:rgba(127,233,227,.06);}
    .welcome-hero-badge{display:inline-flex;align-items:center;background:rgba(127,233,227,.15);border:1px solid rgba(127,233,227,.35);border-radius:999px;padding:5px 14px;font-size:12px;font-weight:700;color:var(--cyan);letter-spacing:.04em;text-transform:uppercase;margin-bottom:18px;}
    .welcome-hero-title{font-size:30px;font-weight:900;color:#fff;margin:0 0 10px;line-height:1.2;position:relative;z-index:1;}
    .welcome-hero-sub{font-size:14px;color:rgba(255,255,255,.65);margin:0;line-height:1.5;position:relative;z-index:1;}
    /* Stats strip â€” no icons */
    .welcome-stats-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:0;background:#fff;border-bottom:1px solid var(--line);flex-shrink:0;}
    .wss-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:22px 16px;border-right:1px solid var(--line);}
    .wss-item:last-child{border-right:none;}
    .wss-num{font-size:26px;font-weight:900;color:var(--navy);line-height:1;}
    .wss-lbl{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
    /* Info card */
    .welcome-info-card{margin:24px 24px 0;background:#fff;border-radius:16px;box-shadow:var(--shadow);padding:22px 24px;border-left:4px solid var(--cyan);}
    .welcome-info-card h4{margin:0 0 8px;color:var(--navy);font-size:15px;font-weight:800;}
    .welcome-info-card p{margin:0;color:var(--muted);font-size:13px;line-height:1.6;}
    /* Continue */
    .welcome-continue-wrap{display:flex;justify-content:center;padding:24px;}
    .btn-welcome-continue{display:inline-flex;align-items:center;padding:15px 52px;background:var(--navy);color:#fff;font-weight:800;font-size:16px;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 14px rgba(19,23,107,.3);transition:all .2s;}
    .btn-welcome-continue:hover{background:var(--navy-deep);}

    /* â”€â”€ VIEW 2: Lesson content â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    #view-lesson{display:none;flex-direction:column;gap:20px;}
    .lesson-card{background:#fff;border-radius:20px;box-shadow:var(--shadow);overflow:hidden;}
    .lesson-card-topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 24px;border-bottom:1px solid var(--line);}
    .lesson-card-label{font-size:18px;font-weight:800;color:var(--navy);line-height:1.3;}
    .btn-mark-complete{background:var(--navy);color:#fff;font-weight:700;font-size:14px;padding:10px 22px;border:none;border-radius:8px;transition:background .2s;white-space:nowrap;}
    .btn-mark-complete:hover{background:var(--navy-deep);}
    .btn-mark-complete.done{background:var(--green);}

    /* The lesson itself is the sanitized CKEditor output saved by faculty. */
    .lesson-editor-body{
        padding:32px 34px 38px;
        background:#fff;
    }
    #lb-desc{
        margin:0;
        color:#1f2937;
        font-size:16px;
        line-height:1.75;
    }
    #lb-desc > :first-child{margin-top:0;}
    #lb-desc > :last-child{margin-bottom:0;}
    #lb-desc img{max-width:100%;height:auto;}
    #lb-desc figure.image{margin:24px 0;}
    #lb-desc figure.image img{display:block;margin-left:auto;margin-right:auto;}
    #lb-desc figure.table{width:100%;overflow-x:auto;margin:22px 0;}
    #lb-desc table{width:100%;border-collapse:collapse;}
    #lb-desc th,#lb-desc td{padding:10px 12px;border:1px solid #dfe3ec;text-align:left;}
    #lb-desc th{background:#f3f5fa;color:var(--navy);font-weight:700;}
    #lb-desc a{color:#155eef;text-decoration:underline;}
    #lb-desc blockquote{margin:20px 0;padding:14px 18px;border-left:4px solid #155eef;background:#f5f7fa;border-radius:6px;}
    #lb-desc pre{display:block;margin:20px 0;padding:16px;overflow-x:auto;border-radius:10px;background:#f3f4f6;border:1px solid #d1d5db;font-family:Consolas,Monaco,"Courier New",monospace;font-size:14px;line-height:1.6;white-space:pre;}
    #lb-desc pre code{display:block;padding:0;background:transparent;border:0;font-family:inherit;font-size:inherit;}
    #lb-desc code{padding:.15rem .35rem;border-radius:4px;background:#f1f3f5;color:#374151;font-family:Consolas,Monaco,"Courier New",monospace;font-size:.9em;}
    #lb-desc .lesson-embedded-media{width:100%;margin:24px 0;}
    #lb-desc .lesson-embedded-media__frame{position:relative;width:100%;aspect-ratio:16 / 9;overflow:hidden;border-radius:12px;background:#111827;}
    #lb-desc .lesson-embedded-media__frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0;}
    #lb-desc .lesson-pdf-embed{margin:24px 0;border:1px solid #dfe3ec;border-radius:12px;overflow:hidden;background:#fff;}
    #lb-desc .lesson-pdf-embed__header{display:flex;align-items:center;gap:8px;padding:12px 14px;background:#f7f8fb;color:var(--navy);font-size:13px;font-weight:800;}
    #lb-desc .lesson-pdf-embed__frame{width:100%;height:70vh;min-height:420px;background:#fff;}
    #lb-desc .lesson-pdf-embed__frame iframe{display:block;width:100%;height:100%;border:0;background:#fff;}
    #lb-desc .lesson-pdf-embed__footer{padding:10px 14px;border-top:1px solid #dfe3ec;}
    #lb-desc .lesson-pdf-embed__footer a{font-size:12px;font-weight:700;}
    .btn-lesson-continue{display:flex;align-items:center;justify-content:center;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-lesson-continue:hover{background:var(--navy-deep);}
    @media (max-width:700px){
        .lesson-card-topbar{padding:16px 18px;}
        .lesson-editor-body{padding:24px 20px 30px;}
        .lesson-card-label{font-size:16px;}
        .btn-mark-complete{font-size:13px;padding:9px 14px;}
        #lb-desc{font-size:15px;}
        #lb-desc .lesson-pdf-embed__frame{height:55vh;min-height:320px;}
    }

    /* â”€â”€ VIEW 3: Quiz (3 questions) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    #view-quiz{display:none;flex-direction:column;gap:20px;}
    .quiz-card{background:#fff;border-radius:20px;box-shadow:var(--shadow);padding:26px 28px 30px;display:flex;flex-direction:column;gap:24px;}
    .quiz-question-block{}
    .quiz-q-label{font-size:15px;font-weight:800;color:var(--navy);margin:0 0 10px;}
    .quiz-q-box{background:#f1f2f8;border-radius:10px;padding:13px 18px;font-size:14px;font-weight:600;color:var(--navy);margin-bottom:12px;}
    .quiz-options{display:flex;flex-direction:column;gap:10px;}
    .quiz-opt{display:flex;align-items:center;gap:12px;background:#f8f9fb;border:1.5px solid var(--line);border-radius:10px;padding:12px 16px;cursor:pointer;transition:all .15s;}
    .quiz-opt:hover{border-color:var(--navy);background:#eef0fa;}
    .quiz-opt.selected{border-color:var(--navy);background:#e8e9f4;}
    .quiz-opt.correct-ans{border-color:var(--green);background:#f0fdf4;}
    .quiz-opt.wrong-ans{border-color:var(--red);background:#fef2f2;}
    .quiz-opt-letter{width:28px;height:28px;border-radius:50%;background:var(--navy);color:#fff;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .quiz-opt-text{font-size:14px;font-weight:600;color:var(--navy);}
    .quiz-divider{border:none;border-top:1px solid var(--line);margin:0;}
    .quiz-score-result{display:none;padding:14px 18px;border-radius:10px;font-weight:700;font-size:15px;text-align:center;}
    .quiz-score-result.pass{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
    .quiz-score-result.fail{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .btn-quiz-submit{display:flex;align-items:center;justify-content:center;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-quiz-submit:hover{background:var(--navy-deep);}
    /* Next-module button after quiz */
    .btn-next-module{display:none;align-items:center;justify-content:center;width:100%;background:var(--green);color:#fff;font-weight:800;font-size:17px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-next-module:hover{background:#16a34a;}

    /* â”€â”€ Locked module styles â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .module-locked .module-title-area{opacity:.5;cursor:not-allowed;pointer-events:none;}
    .module-locked .module-chevron-btn{opacity:.5;cursor:not-allowed;pointer-events:none;}
    .module-locked .lesson-item{pointer-events:none;opacity:.4;cursor:not-allowed;}
    .module-locked .btn-quiz-sm{pointer-events:none;opacity:.4;}
    .module-lock-badge{font-size:11px;color:var(--muted);font-weight:700;
        display:flex;align-items:center;gap:4px;margin-top:3px;}
    /* Retake quiz button */
    .btn-retake{display:none;width:100%;padding:12px 20px;border:2px solid var(--navy);
        background:transparent;color:var(--navy);font-weight:800;font-size:15px;
        border-radius:14px;cursor:pointer;margin-top:0;}
    .btn-retake:hover{background:var(--navy);color:#fff;}
    /* Retake button while in 24-hour cooldown */
    .btn-retake.cooldown{border-color:#d1d5db;background:#f3f4f6;color:#9ca3af;cursor:not-allowed;}
    .btn-retake.cooldown:hover{background:#f3f4f6;color:#9ca3af;}
    .btn-retake .cooldown-timer{display:block;font-size:12px;font-weight:700;margin-top:3px;font-variant-numeric:tabular-nums;}

    @media(max-width:980px){
        .layout{grid-template-columns:1fr;height:auto;}
        .course-nav{max-height:340px;margin:14px;border-radius:16px;}
        .lesson-content{padding:20px 18px 40px;}
    }


    /* New Style */
    /* =========================================================
   RICH LESSON CONTENT
   Content produced by CKEditor 5
   ========================================================= */
.lesson-rich-content {
    color: #26324a;
    font-size: 16px;
    line-height: 1.75;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Paragraphs */
.lesson-rich-content p {
    margin: 0 0 1rem;
}

/* Headings */
.lesson-rich-content h1 {
    margin: 1.8rem 0 1rem;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 800;
    color: #13176b;
}

.lesson-rich-content h2 {
    margin: 1.6rem 0 .85rem;
    font-size: 1.6rem;
    line-height: 1.25;
    font-weight: 750;
    color: #13176b;
}

.lesson-rich-content h3 {
    margin: 1.4rem 0 .7rem;
    font-size: 1.3rem;
    line-height: 1.3;
    font-weight: 700;
    color: #13176b;
}

.lesson-rich-content h4 {
    margin: 1.2rem 0 .6rem;
    font-size: 1.1rem;
    line-height: 1.35;
    font-weight: 700;
    color: #13176b;
}

/* Bold / Italic / Underline */
.lesson-rich-content strong,
.lesson-rich-content b {
    font-weight: 700;
}

.lesson-rich-content em,
.lesson-rich-content i {
    font-style: italic;
}

.lesson-rich-content u {
    text-decoration: underline;
}

/* Subscript / Superscript */
.lesson-rich-content sub {
    vertical-align: sub;
    font-size: 0.75em;
    line-height: 0;
}

.lesson-rich-content sup {
    vertical-align: super;
    font-size: 0.75em;
    line-height: 0;
}

/* Text alignment from CKEditor */
.lesson-rich-content [style*="text-align:center"] {
    text-align: center !important;
}

.lesson-rich-content [style*="text-align:right"] {
    text-align: right !important;
}

.lesson-rich-content [style*="text-align:left"] {
    text-align: left !important;
}

.lesson-rich-content [style*="text-align:justify"] {
    text-align: justify !important;
}

/* Links */
.lesson-rich-content a {
    color: #1357b8;
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: 2px;
    cursor: pointer;
}

.lesson-rich-content a:hover {
    color: #0b3d91;
}

/* Images */
.lesson-rich-content figure.image {
    margin: 1.5rem auto;
    max-width: 100%;
    text-align: center;
}

.lesson-rich-content figure.image img {
    display: inline-block;
    max-width: 100%;
    height: auto;
    border-radius: 10px;
}

.lesson-rich-content img {
    max-width: 100%;
    height: auto;
}

/* Image captions */
.lesson-rich-content figure.image figcaption {
    margin-top: .5rem;
    color: #667085;
    font-size: .9rem;
    font-style: italic;
    text-align: center;
}

/* Lists */
.lesson-rich-content ul,
.lesson-rich-content ol {
    margin: 0 0 1rem;
    padding-left: 2rem;
}

.lesson-rich-content ul {
    list-style-type: disc;
}

.lesson-rich-content ol {
    list-style-type: decimal;
}

.lesson-rich-content li {
    margin: .35rem 0;
}

.lesson-rich-content li > p {
    margin-bottom: .35rem;
}

/* Blockquotes */
.lesson-rich-content blockquote {
    margin: 1.25rem 0;
    padding: 1rem 1.25rem;
    border-left: 4px solid #dba617;
    background: #f7f8fc;
    color: #4b5563;
    border-radius: 0 8px 8px 0;
}

.lesson-rich-content blockquote p:last-child {
    margin-bottom: 0;
}

/* Code blocks */
.lesson-rich-content pre {
    display: block;
    margin: 1.25rem 0;
    padding: 1rem 1.15rem;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #f3f4f6;
    color: #1f2937;
    font-family: Consolas, Monaco, "Courier New", monospace;
    font-size: .9rem;
    line-height: 1.6;
    white-space: pre;
}

.lesson-rich-content pre code {
    display: block;
    padding: 0;
    background: transparent;
    border: 0;
    color: inherit;
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    white-space: inherit;
}

/* Inline code */
.lesson-rich-content code {
    padding: .15rem .35rem;
    border-radius: 4px;
    background: #f1f3f5;
    color: #374151;
    font-family: Consolas, Monaco, "Courier New", monospace;
    font-size: .9em;
}

/* Tables */
.lesson-rich-content figure.table {
    width: 100%;
    overflow-x: auto;
    margin: 1.25rem 0;
}

.lesson-rich-content table {
    width: 100%;
    border-collapse: collapse;
}

.lesson-rich-content th,
.lesson-rich-content td {
    padding: .7rem .8rem;
    border: 1px solid #dfe3ec;
    text-align: left;
}

.lesson-rich-content th {
    background: #f3f5fa;
    font-weight: 700;
    color: #13176b;
}

/* Horizontal rule */
.lesson-rich-content hr {
    margin: 2rem 0;
    border: 0;
    border-top: 1px solid #dfe3ec;
}

/* CKEditor media blocks */
.lesson-rich-content figure.media {
    width: 100%;
    margin: 1.5rem 0;
}

/* Embedded videos */
.lesson-rich-content figure.media iframe {
    display: block;
    width: 100%;
    max-width: 900px;
    aspect-ratio: 16 / 9;
    margin: 0 auto;
    border: 0;
    border-radius: 12px;
}

/* Embedded media fallback */
.lesson-rich-content .lesson-embedded-media {
    width: 100%;
    margin: 1.5rem 0;
}

.lesson-rich-content .lesson-embedded-media__frame {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    border-radius: 12px;
    background: #111827;
}

.lesson-rich-content .lesson-embedded-media__frame iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Empty paragraphs created by CKEditor */
.lesson-rich-content p:empty {
    min-height: 1rem;
}

/* Responsive */
@media (max-width: 700px) {
    .lesson-rich-content {
        font-size: 15px;
    }

    .lesson-rich-content h1 {
        font-size: 1.7rem;
    }

    .lesson-rich-content h2 {
        font-size: 1.4rem;
    }

    .lesson-rich-content h3 {
        font-size: 1.2rem;
    }

    .lesson-rich-content pre {
        font-size: .8rem;
        padding: .85rem;
    }

    .lesson-rich-content table {
        min-width: 600px;
    }
}

.lesson-embedded-media {
    width: 100%;
    margin: 20px 0;
}

.lesson-embedded-media__frame {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    border-radius: 12px;
    background: #111827;
}

.lesson-embedded-media__frame iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.lesson-content pre,
#lb-desc pre {
    display: block;
    margin: 18px 0;
    padding: 16px;
    overflow-x: auto;
    border-radius: 10px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    font-family: Consolas, Monaco, "Courier New", monospace;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre;
}

.lesson-content pre code,
#lb-desc pre code {
    display: block;
    padding: 0;
    background: transparent;
    border: 0;
    font-family: inherit;
    font-size: inherit;
}


#lb-desc blockquote {
    margin: 18px 0;
    padding: 12px 18px;
    border-left: 4px solid #155eef;
    background: #f5f7fa;
    border-radius: 6px;
    font-style: italic;
}

#lb-desc blockquote p:last-child {
    margin-bottom: 0;
}


#lb-desc .text-align-left {
    text-align: left;
}

#lb-desc .text-align-center {
    text-align: center;
}

#lb-desc .text-align-right {
    text-align: right;
}

#lb-desc .text-align-justify {
    text-align: justify;
}

#lb-desc [style*="text-align:left"] {
    text-align: left;
}

#lb-desc [style*="text-align:center"] {
    text-align: center;
}

#lb-desc [style*="text-align:right"] {
    text-align: right;
}

#lb-desc [style*="text-align:justify"] {
    text-align: justify;
}

/*image alignment */
#lb-desc figure.image {
    margin: 18px 0;
}

#lb-desc figure.image.image-style-align-left {
    text-align: left;
}

#lb-desc figure.image.image-style-align-center {
    text-align: center;
}

#lb-desc figure.image.image-style-align-right {
    text-align: right;
}

#lb-desc figure.image.image-style-align-left img,
#lb-desc figure.image.image-style-align-center img,
#lb-desc figure.image.image-style-align-right img {
    display: inline-block;
}

/*student page styling link */
#lb-desc a {
    color: #155eef;
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: 2px;
}

#lb-desc a:hover {
    color: #0b4acb;
}


.up-ckeditor-wrapper .ck-content .text-align-left {
    text-align: left;
}

.up-ckeditor-wrapper .ck-content .text-align-center {
    text-align: center;
}

.up-ckeditor-wrapper .ck-content .text-align-right {
    text-align: right;
}

.up-ckeditor-wrapper .ck-content .text-align-justify {
    text-align: justify;
}

.up-ckeditor-wrapper .ck-content blockquote {
    margin: 12px 0;
    padding: 10px 16px;
    border-left: 4px solid #155eef;
    background: #f5f7fa;
}

.lesson-pdf-embed {
    width: 100%;
    margin: 20px 0;
    border: 1px solid #d9dee7;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

.lesson-pdf-embed__header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f1f5f9;
    border-bottom: 1px solid #d9dee7;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.lesson-pdf-embed iframe {
    display: block;
    width: 100%;
    height: 720px;
    border: 0;
    background: #ffffff;
}

.lesson-pdf-embed__footer {
    padding: 10px 16px;
    border-top: 1px solid #d9dee7;
    background: #f8fafc;
}

.lesson-pdf-embed__footer a {
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.module-description-content {
    color: #4b5563;
    font-size: 15px;
    line-height: 1.75;
}
.module-description-content > :first-child { margin-top: 0; }
.module-description-content > :last-child { margin-bottom: 0; }
.module-description-content h1,
.module-description-content h2,
.module-description-content h3,
.module-description-content h4 {
    color: var(--navy);
    line-height: 1.3;
    margin: 18px 0 8px;
}
.module-description-content p { margin: 0 0 12px; }
.module-description-content ul,
.module-description-content ol { padding-left: 22px; margin: 10px 0 14px; }
.module-description-content img { max-width: 100%; height: auto; border-radius: 10px; }
.module-description-content a { color: #155eef; font-weight: 600; }
.module-description-empty { color: #6b7280; font-style: italic; }
</style>
</head>
<body>

@include('components.student-navigation')

<div class="layout">

    {{-- â”€â”€ LEFT: Course Navigation â”€â”€â”€ --}}
    <aside class="course-nav">
        <div class="course-nav-header">
            @if($course->category ?? false)<div class="nav-category">{{ $course->category }}</div>@endif
            <div class="nav-title">{{ $course->title ?? 'Course' }}</div>
            <div class="nav-progress-row">
                <span>Your Progress</span>
                <span id="nav-pct">0%</span>
            </div>
            <div class="nav-progress-track">
                <div class="nav-progress-fill" id="nav-fill" style="width:0%"></div>
            </div>
        </div>

        <div class="modules-scroll">
            @foreach($modules as $mIndex => $module)
            <div class="module-group {{ $mIndex > 0 ? 'module-locked' : '' }}" id="mg-{{ $mIndex }}">

                <div class="module-toggle">
                    <div class="module-title-area" id="mta-{{ $mIndex }}"
                         onclick="showModuleWelcome(@js($module->title),{{ $module->lessons->count() }},{{ $modules->count() }},{{ $badge_count ?? 1 }},{{ $mIndex }},@js($module->description ?? ''))">
                        <span class="module-num">Module {{ $mIndex + 1 }}: {{ $module->title }}</span>
                        <span class="module-sub" id="msub-{{ $mIndex }}">{{ $module->lessons->count() }} lessons</span>
                        @if($mIndex > 0)
                        <span class="module-lock-badge" id="mlock-{{ $mIndex }}">
                            Complete previous quiz to unlock
                        </span>
                        @endif
                    </div>
                    <button class="module-chevron-btn" type="button"
                            onclick="toggleModule('mod-{{ $mIndex }}','chev-{{ $mIndex }}')">
                        <svg class="chevron {{ $mIndex===0?'open':'' }}" id="chev-{{ $mIndex }}"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                </div>

                <div class="lesson-list {{ $mIndex===0?'open':'' }}" id="mod-{{ $mIndex }}">
                    @foreach($module->lessons ?? [] as $lIndex => $lesson)
                        @php $isVid = strtolower($lesson->type??'')  === 'video'; @endphp
                        <div class="lesson-item"
                             id="li-{{ $mIndex }}-{{ $lIndex }}"
                             data-module="{{ $mIndex }}"
                             data-lid="{{ $lesson->id }}"
                             onclick="loadLesson(
                                 @js($lesson->type ?? 'Text'),
                                 @js($lesson->title),
                                 @js($lesson->description ?? $lesson->title),
                                 @js($lesson->duration ?? '15m'),
                                 {{ $mIndex }}, {{ $lIndex }},
                                 @js($lesson->content ?? $lesson->description ?? '')
                             )">
                            <span class="lesson-title-text">{{ $lesson->title }}</span>
                            <span class="lesson-meta">{{ $lesson->type }} - {{ $lesson->duration }}</span>
                            <span class="status-dot"></span>
                        </div>
                    @endforeach

                    @if($module->quiz ?? false)
                    <div class="quiz-row">
                        <div>
                            <div class="quiz-info-title">{{ $module->quiz->title }}</div>
                            <div class="quiz-info-sub">
                                {{ $module->quiz->questions_count ?? 3 }} questions -
                                Pass {{ $module->quiz->passing_score ?? 75 }}%
                                @if (($module->quiz->time_limit ?? 0) > 0)
                                    - {{ $module->quiz->time_limit }} min
                                @else
                                    - No time limit
                                @endif
                            </div>
                        </div>
                        {{-- Starts LOCKED; JS unlocks after all lessons marked complete --}}
                        <button class="btn-quiz-sm locked" id="qbtn-{{ $mIndex }}"
                                data-module="{{ $mIndex }}"
                                onclick="handleQuizClick({{ $mIndex }})"
                                title="Complete all lessons first">Take Quiz</button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </aside>

    {{-- â”€â”€ RIGHT: Content â”€â”€â”€ --}}
    <main class="lesson-content lesson-content lesson-rich-content">

        @include('components.breadcrumbs', ['items' => [
            ['label' => 'Browse Courses', 'url' => route('courses.browse')],
            ['label' => $course->title ?? 'Course', 'url' => route('courses.show', $course->id)],
            ['label' => 'Learning'],
        ]])

        {{-- VIEW 1: Welcome --}}
        <div id="view-welcome">

            {{-- Hero banner --}}
            <div class="welcome-hero">
                <div class="welcome-hero-badge">Course Module</div>
                <h1 class="welcome-hero-title" id="wh-mod-title">{{ $modules->first()->title ?? 'the Course' }}</h1>
                <p class="welcome-hero-sub">Complete all lessons and pass the quiz to unlock the next module.</p>
            </div>

            {{-- Stats strip (no icons) --}}
            <div class="welcome-stats-strip">
                <div class="wss-item">
                    <span class="wss-num" id="ws-mod">{{ $modules->count() }}</span>
                    <span class="wss-lbl">Modules</span>
                </div>
                <div class="wss-item">
                    <span class="wss-num" id="ws-les">{{ $total_lessons ?? 0 }}</span>
                    <span class="wss-lbl">Lessons</span>
                </div>
                <div class="wss-item">
                    <span class="wss-num" id="ws-bdg">{{ $badge_count ?? 1 }}</span>
                    <span class="wss-lbl">Badge</span>
                </div>
            </div>

            {{-- Info card --}}
            <div class="welcome-info-card module-description-card">
                <h4>About this module</h4>
                <div id="wm-description" class="module-description-content">
                    <p>Module description will appear here.</p>
                </div>
            </div>

            {{-- Continue button --}}
            <div class="welcome-continue-wrap">
                <button class="btn-welcome-continue" onclick="continueFromWelcome()" type="button">
                    Start Learning
                </button>
            </div>

        </div>

        {{-- VIEW 2: Lesson Content --}}
        <div id="view-lesson">
            <div class="lesson-card">
                <div class="lesson-card-topbar">
                    <span class="lesson-card-label" id="lc-label">Lesson</span>
                    <button class="btn-mark-complete" id="btn-mark" onclick="markComplete()" type="button">Mark Complete</button>
                </div>
                <article class="lesson-editor-body">
                    <div id="lb-desc">
                        <p>Lesson content will appear here.</p>
                    </div>
                </article>
            </div>

            <button class="btn-lesson-continue" onclick="nextLesson()" type="button">Continue</button>
        </div>

        {{-- VIEW 3: Quiz (3 questions) --}}
        <div id="view-quiz">
            <div class="lesson-header-btn">
                <span id="qh-title">Quiz</span>
                <span id="qh-timer" class="quiz-timer" style="display:none;"></span>
                
            </div>
            <div class="quiz-card" id="quiz-questions-container">
                {{-- Populated by JS --}}
            </div>
            <div class="quiz-score-result" id="quiz-score-result"></div>
            <button class="btn-quiz-submit" id="btn-quiz-submit" onclick="submitQuiz(false)" type="button">Submit</button>
            <button class="btn-next-module" id="btn-next-module" onclick="goToNextModule()" type="button">
                Continue to Next Module &#x2192;
            </button>
            <button class="btn-retake" id="btn-retake" onclick="retakeQuiz()" type="button">
                Retake Quiz
            </button>
        </div>

    </main>
</div>

<script>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   QUIZ DATA â€” one per module, 3 questions each
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
/* Quizzes come straight from the database â€” exactly what the faculty
   built on the manage screen. Modules without a quiz get an empty set. */
var MODULE_DESCRIPTIONS = {!! $modules->mapWithKeys(fn ($m, $i) => [
    $i => (string) ($m->description ?? ''),
])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

var QUIZ_DATA = {!! $modules->mapWithKeys(fn ($m, $i) => [
    $i => [
        'title'        => $m->quiz->title ?? '',
        'passingScore' => (int) ($m->quiz->passing_score ?? 75),
        'timeLimit'    => (int) ($m->quiz->time_limit ?? 0),   // minutes; 0 = untimed
        'questions'    => $m->quiz->questions ?? [],
    ],
])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   STATE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
var _curLessonEl   = null;
var _curModIdx     = 0;
var _curLesIdx     = 0;
var _totalQuizQ    = 0;          // total quiz questions across all modules
var _moduleScores  = {};         // { modIdx: correctCount } â€” replaced on retake
var _moduleAnswers = {};         // { modIdx: { questionId: letter } }
var _moduleReview  = {};         // { modIdx: { questionId: {answer, correct, correct_answer} } } â€” saved on submit for view-only replay
var _retakeQMap    = [];         // maps rendered question index â†’ original question index on retake
var _lastSentPercent = 0;        // most recent percent computed by saveProgress (reused by the unload beacon)

// Count total quiz questions on load
document.addEventListener('DOMContentLoaded', function () {
    Object.values(QUIZ_DATA).forEach(function(m){ _totalQuizQ += m.questions.length; });

    var initialDescription = document.getElementById('wm-description');
    if (initialDescription) {
        var firstModuleDescription = (MODULE_DESCRIPTIONS[0] || '').trim();
        initialDescription.innerHTML = firstModuleDescription
            ? renderLessonContent(firstModuleDescription)
            : '<p class="module-description-empty">No module description has been provided yet.</p>';
    }

    restoreProgress(_savedProgress);
});

/* â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function hideAllViews() {
    document.getElementById('view-welcome').style.display = 'none';
    document.getElementById('view-lesson').style.display  = 'none';
    document.getElementById('view-quiz').style.display    = 'none';
    document.querySelector('.lesson-content').classList.remove('welcome-mode');

    // Navigating away hides the badge, but the DEADLINE is deliberately kept:
    // the clock keeps running, so leaving mid-quiz is not a way to pause it.
    if (_quizTimerId) { clearInterval(_quizTimerId); _quizTimerId = null; }
    var badge = document.getElementById('qh-timer');
    if (badge) badge.style.display = 'none';
}
function toggleModule(listId, chevId) {
    const l = document.getElementById(listId), c = document.getElementById(chevId);
    const o = l.classList.toggle('open'); c.classList.toggle('open', o);
}

/* â”€â”€ Real-time progress persistence â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Every lesson marked complete and every quiz submit is saved to the
   server, and the saved state is restored on load â€” so the student's
   percentage is the same on every page, every visit. */
var _savedProgress = {!! json_encode($saved_progress ?? ['completed_lessons' => [], 'module_scores' => new \stdClass()]) !!};
var LESSON_TRACKING_BASE = @json(url('/courses/'.$course->id.'/lessons'));
var _lessonOpenedAt = 0;
var _lessonStartRequest = Promise.resolve({ok:false});
var _serverUnlocks = {!! json_encode($quiz_unlocks ?? new \stdClass()) !!};   // moduleIdx â†’ retake unlock timestamp (ms), from the server
var _serverUnlocksAuthoritative = true;   // this list is complete: anything absent from it is unlocked
var _quizAttempts = {!! json_encode($quiz_attempts ?? new \stdClass()) !!};   // moduleIdx â†’ {allowed, used, remaining, exhausted}
var _pendingQuizSubmit = null;   // last quiz result awaiting server confirmation
var _saveTimer = null;

function saveProgress(immediate) {
    if (_saveTimer) clearTimeout(_saveTimer);
    var _run = function () {
        var completed = Array.from(document.querySelectorAll('.lesson-item.lesson-correct'))
            .map(function (el) { return el.dataset.lid; });
        var totalCorrect = 0;
        Object.keys(_moduleScores).forEach(function (k) {
            var qd = QUIZ_DATA[parseInt(k, 10)];
            if (!qd || !qd.questions) return;
            totalCorrect += Math.min(Number(_moduleScores[k]) || 0, qd.questions.length);
        });
        var pct = _totalQuizQ > 0 ? Math.min(100, Math.round((totalCorrect / _totalQuizQ) * 100)) : 0;
        _lastSentPercent = pct;

        var payload = {
            percent: pct,
            completed_lessons: completed,
            module_scores: _moduleScores
        };
        if (_pendingQuizSubmit) {
            payload.quiz_results = {};
            payload.quiz_results[_pendingQuizSubmit.modIdx] = {
                answers: _pendingQuizSubmit.answers
            };
        }

        return fetch('{{ route('courses.progress', $course->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return null;

            if (_pendingQuizSubmit && data.quiz_submissions) {
                var pending = data.quiz_submissions[String(_pendingQuizSubmit.modIdx)];
                if (pending) {
                    var submittedModIdx = _pendingQuizSubmit.modIdx;
                    _pendingQuizSubmit = null;
                    if (!pending.blocked) {
                        applyServerQuizSubmission(submittedModIdx, pending);
                    }
                }
            }

            if (data.quiz_attempts) {
                Object.keys(data.quiz_attempts).forEach(function (k) {
                    _quizAttempts[k] = data.quiz_attempts[k];
                });

                var budget = attemptBudget(_curModIdx);
                if (budget.exhausted) {
                    clearRetakeCooldown(_curModIdx);
                    delete _serverUnlocks[String(_curModIdx)];
                    if (document.getElementById('view-quiz').style.display !== 'none') {
                        showRetakeButton(_curModIdx);
                    }
                    return;
                }
            }

            if (data.quiz_unlocks) {
                Object.keys(data.quiz_unlocks).forEach(function (k) {
                    _serverUnlocks[k] = data.quiz_unlocks[k];
                    try { localStorage.setItem(retakeKey(k), String(data.quiz_unlocks[k])); } catch (e) {}
                    if (String(k) === String(_curModIdx)
                        && document.getElementById('view-quiz').style.display !== 'none') {
                        showRetakeButton(_curModIdx);
                    }
                });
            }

            return data;
        });
    };

    if (immediate) return _run();
    _saveTimer = setTimeout(_run, 600);
    return null;
}

window.addEventListener('pagehide', function () {
    if (!_pendingQuizSubmit || !navigator.sendBeacon) return;

    var payload = {
        _token: '{{ csrf_token() }}',
        percent: _lastSentPercent,
        completed_lessons: Array.from(document.querySelectorAll('.lesson-item.lesson-correct'))
            .map(function (el) { return el.dataset.lid; }),
        module_scores: _moduleScores,
        quiz_results: {}
    };
    payload.quiz_results[_pendingQuizSubmit.modIdx] = { answers: _pendingQuizSubmit.answers };

    try {
        navigator.sendBeacon(
            '{{ route('courses.progress', $course->id) }}',
            new Blob([JSON.stringify(payload)], { type: 'application/json' })
        );
        _pendingQuizSubmit = null;
    } catch (e) { /* nothing more we can do */ }
});

function restoreProgress(saved) {
    if (!saved) return;

    (saved.completed_lessons || []).forEach(function (lid) {
        var el = document.querySelector('.lesson-item[data-lid="' + lid + '"]');
        if (el) el.classList.add('lesson-correct');
    });

    _moduleScores = saved.module_scores || {};

    // Quiz buttons + module locks follow the saved quiz results
    Object.keys(QUIZ_DATA).forEach(function (k) {
        var i     = parseInt(k, 10);
        var data  = QUIZ_DATA[i];
        var score = _moduleScores[i];
        if (score === undefined) return;
        var pct = Math.round((score / data.questions.length) * 100);
        var doneBtn = document.getElementById('qbtn-' + i);
        if (pct >= (data.passingScore || 75)) {
            if (doneBtn) {
                doneBtn.classList.remove('unlocked', 'locked');
                doneBtn.classList.add('viewed');
                doneBtn.textContent = 'VIEW';
                doneBtn.title = 'View your quiz results';
            }
            unlockModule(i + 1);
        } else if (doneBtn) {
            // Failed attempt â€” read-only review; retake is gated by the cooldown
            doneBtn.classList.remove('locked');
            doneBtn.classList.add('viewed');
            doneBtn.textContent = 'VIEW';
            doneBtn.title = 'View your quiz results';
        }
    });

    // Lesson-driven states last, so "Completed" labels win
    Object.keys(QUIZ_DATA).forEach(function (k) {
        var i = parseInt(k, 10);
        checkQuizUnlock(i);
        checkModuleComplete(i);
    });

    // Reconcile browser cooldowns with the server's list. Entries the server
    // still reports are refreshed; everything else is cleared, which is how a
    // quiz edited by faculty becomes retakeable straight away.
    // NOTE: this used to iterate `course.modules`, but no `course` object is
    // defined in JS â€” the ReferenceError aborted the rest of this function,
    // so updateProgress() below never ran and the bar stayed at 0%.
    Object.keys(QUIZ_DATA).forEach(function (k) {
        var key = String(k);
        if (_serverUnlocks && _serverUnlocks[key] !== undefined) {
            try { localStorage.setItem(retakeKey(key), String(_serverUnlocks[key])); } catch (e) {}
        } else {
            clearRetakeCooldown(key);
        }
    });

    updateProgress();
}

/* â”€â”€ Report course completion to the server (awards the badge) â”€â”€
   Fires once, the moment the course reaches 100%. The Admin
   "Recent Badges" panel picks the new badge up within 15 seconds. */
var _completionReported = false;
function reportCourseCompletion() {
    if (_completionReported) return;
    _completionReported = true;
    fetch('{{ route('courses.complete', $course->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: '{}'
    }).then(function(r){ return r.json(); })
      .then(function(data){
          if (data && data.badge_awarded) {
              try { console.log('Badge earned: ' + data.badge_awarded); } catch (e) {}
          }
      })
      .catch(function(){ _completionReported = false; });
}

/* â”€â”€ Progress bar â€” based on quiz correct answers ONLY â”€â”€ */
function updateProgress() {
    /* Sum correct answers per module (retakes REPLACE, not add).
       Only correct answers count â€” wrong answers contribute 0.
       Each module is capped at its CURRENT question count: if faculty
       trimmed a 5-question quiz to 2, an old score of 5 must not keep
       claiming 5 correct answers. Mirrors calculateProgressPercent(). */
    let totalCorrect = 0;
    Object.keys(_moduleScores).forEach(function (k) {
        const data = QUIZ_DATA[parseInt(k, 10)];
        if (!data || !data.questions) return;
        totalCorrect += Math.min(_moduleScores[k], data.questions.length);
    });
    const raw = _totalQuizQ > 0 ? Math.round((totalCorrect / _totalQuizQ) * 100) : 0;
    const pct = Math.min(100, raw);
    document.getElementById('nav-fill').style.width = pct + '%';
    document.getElementById('nav-pct').textContent  = pct + '%';
    if (pct >= 100) reportCourseCompletion();
}

/* â”€â”€ Module Welcome â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function showModuleWelcome(title, lessonCount, modCount, badgeCount, modIdx, description) {
    document.querySelectorAll('.module-title-area').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.lesson-item').forEach(el => el.classList.remove('active'));
    const ta = document.getElementById('mta-' + modIdx);
    if (ta && !ta.classList.contains('completed')) ta.classList.add('active');
    _curModIdx = modIdx;

    document.getElementById('wh-mod-title').textContent = title;
    document.getElementById('ws-mod').textContent       = modCount;
    document.getElementById('ws-les').textContent       = lessonCount;
    document.getElementById('ws-bdg').textContent       = badgeCount;

    const descriptionEl = document.getElementById('wm-description');
    if (descriptionEl) {
        const html = (description || '').trim();
        descriptionEl.innerHTML = html
            ? renderLessonContent(html)
            : '<p class="module-description-empty">No module description has been provided yet.</p>';
    }

    hideAllViews();
    document.getElementById('view-welcome').style.display = 'flex';
    document.querySelector('.lesson-content').classList.add('welcome-mode');
}
function continueFromWelcome() {
    const first = document.querySelector('#mg-' + _curModIdx + ' .lesson-item');
    if (first) first.click();
}

/* â”€â”€ Load lesson helper â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function renderLessonContent(html) {
    var template = document.createElement('template');
    template.innerHTML = html || '';

    template.content.querySelectorAll('figure.media oembed, figure.upskill-pdf oembed').forEach(function (oembed) {
        var url = oembed.getAttribute('url') || '';
        if (!url) return;

        var figure = oembed.closest('figure');
        if (!figure) return;

        var normalized = url.trim();
        var iframeUrl = null;
        var title = 'Embedded lesson media';

        try {
            var parsed = new URL(normalized, window.location.origin);
            var host = parsed.hostname.toLowerCase();

            // YouTube watch / short links / embed links.
            if (host === 'youtube.com' || host === 'www.youtube.com' || host === 'm.youtube.com' || host === 'youtube-nocookie.com' || host === 'www.youtube-nocookie.com') {
                var videoId = parsed.searchParams.get('v');
                if (!videoId && parsed.pathname.indexOf('/embed/') === 0) {
                    videoId = parsed.pathname.split('/')[2] || '';
                }
                if (!videoId && parsed.pathname.indexOf('/shorts/') === 0) {
                    videoId = parsed.pathname.split('/')[2] || '';
                }
                if (videoId) {
                    iframeUrl = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId);
                    title = 'YouTube lesson video';
                }
            } else if (host === 'youtu.be') {
                var shortId = parsed.pathname.split('/').filter(Boolean)[0] || '';
                if (shortId) {
                    iframeUrl = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(shortId);
                    title = 'YouTube lesson video';
                }
            } else if (host === 'vimeo.com' || host === 'www.vimeo.com') {
                var parts = parsed.pathname.split('/').filter(Boolean);
                var vimeoId = parts.length ? parts[parts.length - 1] : '';
                if (/^\d+$/.test(vimeoId)) {
                    iframeUrl = 'https://player.vimeo.com/video/' + vimeoId;
                    title = 'Vimeo lesson video';
                }
            }

            // CKEditor PDF attachments use the custom upskill-pdf figure.
            if (!iframeUrl && figure.classList.contains('upskill-pdf')) {
                iframeUrl = parsed.href;
                title = 'PDF lesson attachment';
            }
        } catch (e) {
            iframeUrl = null;
        }

        if (!iframeUrl) {
            var link = document.createElement('a');
            link.href = normalized;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Open embedded content';
            oembed.replaceWith(link);
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = figure.classList.contains('upskill-pdf')
            ? 'lesson-pdf-embed'
            : 'lesson-embedded-media';

        if (figure.classList.contains('upskill-pdf')) {
            var pdfHeader = document.createElement('div');
            pdfHeader.className = 'lesson-pdf-embed__header';
            pdfHeader.textContent = title;
            wrapper.appendChild(pdfHeader);
        }

        var frame = document.createElement('div');
        frame.className = figure.classList.contains('upskill-pdf')
            ? 'lesson-pdf-embed__frame'
            : 'lesson-embedded-media__frame';

        var iframe = document.createElement('iframe');
        iframe.src = iframeUrl;
        iframe.title = title;
        iframe.loading = 'lazy';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        frame.appendChild(iframe);
        wrapper.appendChild(frame);

        if (figure.classList.contains('upskill-pdf')) {
            var footer = document.createElement('div');
            footer.className = 'lesson-pdf-embed__footer';
            var link = document.createElement('a');
            link.href = normalized;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Open PDF in new tab';
            footer.appendChild(link);
            wrapper.appendChild(footer);
        }

        figure.replaceWith(wrapper);
    });

    return template.innerHTML;
}

function _prepLesson(title, desc, duration, modIdx, lesIdx, content) {
    document.querySelectorAll('.lesson-item').forEach(function (el) {
        el.classList.remove('active');
    });
    document.querySelectorAll('.module-title-area').forEach(function (el) {
        el.classList.remove('active');
    });

    var el = document.getElementById('li-' + modIdx + '-' + lesIdx);
    if (el) el.classList.add('active');

    _curLessonEl = el;
    _curModIdx   = modIdx;
    _curLesIdx   = lesIdx;

    document.getElementById('lc-label').textContent = title;

    // The student sees exactly the lesson content authored in CKEditor.
    // The old standalone attachment preview and generated "What is..." text
    // are intentionally not rendered here.
    var lessonContent = content || '<p>' + escapeHtml(title) + '</p>';
    document.getElementById('lb-desc').innerHTML = renderLessonContent(lessonContent);

    var btn = document.getElementById('btn-mark');

    if (_curLessonEl && _curLessonEl.classList.contains('lesson-correct')) {
        btn.textContent = '✓ Completed';
        btn.classList.add('done');
        btn.disabled = true;
    } else {
        btn.textContent = 'Mark Complete';
        btn.classList.remove('done');
        btn.disabled = false;
    }
}

function escapeHtml(value) {
    var div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

function loadVideoLesson(title, desc, duration, modIdx, lesIdx, content) {
    loadLesson('Text', title, desc, duration, modIdx, lesIdx, content || '');
}

function loadTextLesson(title, desc, duration, modIdx, lesIdx, content) {
    loadLesson('Text', title, desc, duration, modIdx, lesIdx, content || '');
}

/* Editor-first lesson loader. The standalone CourseLesson file upload
   is no longer part of the student learning experience. */
function startLessonTracking() {
    if (!_curLessonEl) return;
    var lessonId = _curLessonEl.dataset.lid;
    _lessonOpenedAt = Date.now();
    _lessonStartRequest = fetch(LESSON_TRACKING_BASE + '/' + encodeURIComponent(lessonId) + '/start', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(function (response) {
        return response.json().then(function (data) {
            return { ok: response.ok && !!data.ok, message: data.message || '' };
        });
    }).catch(function () {
        return { ok: false, message: 'Could not start lesson tracking. Reload the course and try again.' };
    });
}

function loadLesson(type, title, desc, duration, modIdx, lesIdx, content) {
    _prepLesson(title, desc, duration, modIdx, lesIdx, content);
    startLessonTracking();
    hideAllViews();
    document.getElementById('view-lesson').style.display = 'flex';
    scrollLearningTop();
}

/* â”€â”€ Mark Complete â†’ green dot + unlock quiz check â”€â”€ */
function markComplete() {
    const btn = document.getElementById('btn-mark');
    const lessonEl = _curLessonEl;
    if (!lessonEl || lessonEl.classList.contains('lesson-correct') || btn.disabled) return;

    const lessonId = lessonEl.dataset.lid;
    const openedAt = _lessonOpenedAt;
    const startRequest = _lessonStartRequest;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const waitMs = Math.max(0, 5500 - (Date.now() - openedAt));
    window.setTimeout(async function () {
        if (_curLessonEl !== lessonEl) return;

        try {
            const started = await startRequest;
            if (!started.ok) throw new Error(started.message || 'Could not verify this lesson.');

            const response = await fetch(LESSON_TRACKING_BASE + '/' + encodeURIComponent(lessonId) + '/complete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Could not save this lesson.');

            lessonEl.classList.add('lesson-correct');
            lessonEl.classList.remove('lesson-wrong', 'active');
            btn.textContent = '✓ Completed';
            btn.classList.add('done');
            checkQuizUnlock(_curModIdx);
            checkModuleComplete(_curModIdx);
            saveProgress(true);
        } catch (error) {
            btn.disabled = false;
            btn.textContent = 'Mark Complete';
            alert(error.message || 'Could not save lesson progress.');
        }
    }, waitMs);
}

/* â”€â”€ Unlock QUIZ when all lessons are marked complete â”€ */
function checkQuizUnlock(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.length > 0 && lessons.every(l => l.classList.contains('lesson-correct'));
    const qbtn    = document.getElementById('qbtn-' + modIdx);
    if (!qbtn) return;
    if (allDone) {
        qbtn.classList.replace('locked', 'unlocked');
        qbtn.title = 'Take the quiz!';
    } else {
        qbtn.classList.replace('unlocked', 'locked');
        qbtn.title = 'Complete all lessons first';
    }
}

/* â”€â”€ Module completion marker â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function checkModuleComplete(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.every(l => l.classList.contains('lesson-correct'));
    const ta      = document.getElementById('mta-' + modIdx);
    const sub     = document.getElementById('msub-' + modIdx);
    if (allDone && lessons.length > 0) {
        ta.classList.add('completed');
        ta.classList.remove('active');
        sub.innerHTML = '<span style="color:var(--green);font-weight:800;">\u2713 Completed</span>';
    }
}

/* â”€â”€ Next lesson (Continue button) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function scrollLearningTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextLesson() {
    const all  = Array.from(document.querySelectorAll('.lesson-item'));
    const idx  = all.indexOf(_curLessonEl);
    const next = all[idx + 1];

    // Continue to the next lesson in the same module.
    if (next && parseInt(next.dataset.module, 10) === _curModIdx) {
        next.click();
        scrollLearningTop();
        return;
    }

    // The module quiz is the next learning activity after the final lesson.
    // Do not send the student back to the module welcome screen first.
    const quizData = QUIZ_DATA[_curModIdx];
    if (quizData && quizData.questions && quizData.questions.length > 0) {
        checkQuizUnlock(_curModIdx);
        const quizButton = document.getElementById('qbtn-' + _curModIdx);
        if (quizButton && !quizButton.classList.contains('locked')) {
            handleQuizClick(_curModIdx);
            scrollLearningTop();
            return;
        }
    }

    // If there is no quiz, continue directly into the next unlocked module.
    const nextModIdx = _curModIdx + 1;
    const nextGroup = document.getElementById('mg-' + nextModIdx);
    const nextFirst = document.querySelector('#mg-' + nextModIdx + ' .lesson-item');
    if (nextGroup && nextFirst && !nextGroup.classList.contains('module-locked')) {
        nextFirst.click();
        scrollLearningTop();
        return;
    }

    // Only fall back to the module welcome when there is genuinely no next
    // learning activity available.
    const ta       = document.getElementById('mta-' + _curModIdx);
    const modTitle = ta ? ta.querySelector('.module-num').textContent.replace(/^Module \d+:\s*/, '') : 'the Module';
    const group    = document.getElementById('mg-' + _curModIdx);
    const lesCount = group ? group.querySelectorAll('.lesson-item').length : 0;

    showModuleWelcome(
        modTitle,
        lesCount,
        document.querySelectorAll('.module-group').length,
        {{ $badge_count ?? 1 }},
        _curModIdx,
        ''
    );
    scrollLearningTop();
}

function handleQuizClick(modIdx) {
    const qbtn = document.getElementById('qbtn-' + modIdx);
    if (qbtn && qbtn.classList.contains('locked')) {
        alert('Please mark all lessons as complete before taking the quiz.');
        return;
    }
    if (!QUIZ_DATA[modIdx] || !QUIZ_DATA[modIdx].questions || QUIZ_DATA[modIdx].questions.length === 0) {
        alert('No quiz has been added to this module yet.');
        return;
    }
    // Already answered (passed OR failed) â†’ open read-only review.
    // A score is the only proof the student sat THIS version of the quiz.
    // When faculty edits a quiz the server drops the saved score, so the
    // quiz must open fresh â€” never in review, which reveals the answers.
    const hasScore = _moduleScores[modIdx] !== undefined;
    const viewOnly = hasScore;

    if (!hasScore && qbtn) {
        qbtn.classList.remove('viewed');
    }
    showQuizView(modIdx, viewOnly);
}

/* â”€â”€ Build quiz view â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   viewOnly  = true  â†’ read-only review of past attempt
   retakeOnly = true â†’ only show questions answered wrong
â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function showQuizView(modIdx, viewOnly, retakeOnly) {
    _curModIdx = modIdx;
    const data = QUIZ_DATA[modIdx];
    if (!data) return;

    viewOnly = viewOnly || false;
    retakeOnly = retakeOnly || false;
    const savedAnswers = _moduleAnswers[modIdx] || {};
    const review = _moduleReview[modIdx] || {};

    var questionsToShow;
    if (retakeOnly) {
        questionsToShow = [];
        data.questions.forEach(function(q) {
            const result = review[String(q.id)];
            if (result && result.correct === false) {
                questionsToShow.push({ q: q });
            }
        });
        if (questionsToShow.length === 0) {
            questionsToShow = data.questions.map(function(q){ return { q: q }; });
        }
    } else {
        questionsToShow = data.questions.map(function(q){ return { q: q }; });
    }

    _retakeQMap = questionsToShow.map(function(item){ return data.questions.indexOf(item.q); });

    const suffix = viewOnly ? ' - Review' : (retakeOnly ? ' - Retake (Wrong Answers)' : '');
    document.getElementById('qh-title').textContent = data.title + suffix;

    const container = document.getElementById('quiz-questions-container');
    container.innerHTML = '';

    questionsToShow.forEach(function(item, ri) {
        const q = item.q;
        const qi = data.questions.indexOf(q);

        if (ri > 0) {
            const hr = document.createElement('hr');
            hr.className = 'quiz-divider';
            container.appendChild(hr);
        }

        const block = document.createElement('div');
        block.className = 'quiz-question-block';
        block.id = 'qblock-' + ri;

        const label = document.createElement('div');
        label.className = 'quiz-q-label';
        label.textContent = q.label;
        block.appendChild(label);

        const qbox = document.createElement('div');
        qbox.className = 'quiz-q-box';
        qbox.textContent = q.question;
        block.appendChild(qbox);

        const optWrap = document.createElement('div');
        optWrap.className = 'quiz-options';
        optWrap.id = 'opts-' + ri;

        q.options.forEach(function(opt) {
            const div = document.createElement('div');
            div.className = 'quiz-opt';
            div.dataset.letter = opt.letter;
            div.innerHTML = '<span class="quiz-opt-letter">' + opt.letter + '</span>'
                          + '<span class="quiz-opt-text">' + opt.text + '</span>';

            const result = review[String(q.id)];
            const selected = result?.answer || savedAnswers[String(q.id)] || savedAnswers[qi];

            if (viewOnly) {
                if (result?.correct_answer && opt.letter === result.correct_answer) {
                    div.classList.add('correct-ans');
                }
                if (selected && selected === opt.letter) {
                    div.classList.add(result?.correct ? 'correct-ans' : 'wrong-ans');
                }
                div.style.cursor = 'default';
                div.style.pointerEvents = 'none';
            } else {
                div.onclick = function() {
                    optWrap.querySelectorAll('.quiz-opt').forEach(function(el) { el.classList.remove('selected'); });
                    div.classList.add('selected');
                };
            }
            optWrap.appendChild(div);
        });
        block.appendChild(optWrap);
        container.appendChild(block);
    });

    if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
    document.getElementById('btn-next-module').style.display = 'none';
    document.getElementById('btn-retake').style.display = 'none';

    if (viewOnly) {
        document.getElementById('btn-quiz-submit').style.display = 'none';
        const scoreEl = document.getElementById('quiz-score-result');
        const correct = _moduleScores[modIdx] !== undefined ? _moduleScores[modIdx] : '?';
        const total = data.questions.length;
        const pct = _moduleScores[modIdx] !== undefined ? Math.round((_moduleScores[modIdx] / total) * 100) : 0;
        const pass = pct >= (data.passingScore || 75);
        scoreEl.innerHTML = pass
            ? '\u2713 Passed - Score: ' + correct + '/' + total
            : 'Score: ' + correct + '/' + total + ' - Keep practicing!';
        scoreEl.className = 'quiz-score-result ' + (pass ? 'pass' : 'fail');
        scoreEl.style.display = 'block';
        if (!pass && _moduleScores[modIdx] !== undefined) showRetakeButton(modIdx);
    } else {
        document.getElementById('quiz-score-result').style.display = 'none';
        document.getElementById('btn-quiz-submit').style.display = 'flex';
    }

    hideAllViews();
    document.getElementById('view-quiz').style.display = 'flex';

    if (viewOnly) stopQuizTimer();
    else startQuizTimer(modIdx, retakeOnly === true);
}

var _quizTimerId  = null;
var _quizDeadline = 0;

function quizDeadlineKey(modIdx) {
    return 'upskill_quiz_deadline_c{{ $course->id }}_m' + modIdx;
}

function startQuizTimer(modIdx, restartClock) {
    stopQuizTimer();

    var data    = QUIZ_DATA[modIdx];
    var minutes = data ? (parseInt(data.timeLimit, 10) || 0) : 0;
    var badge   = document.getElementById('qh-timer');

    // 0 minutes means the faculty left this quiz untimed.
    if (!minutes || minutes <= 0) {
        if (badge) badge.style.display = 'none';
        return;
    }

    var stored = 0;
    try { stored = parseInt(localStorage.getItem(quizDeadlineKey(modIdx)) || '0', 10); } catch (e) {}

    if (restartClock || !stored || stored <= Date.now()) {
        _quizDeadline = Date.now() + minutes * 60 * 1000;
        try { localStorage.setItem(quizDeadlineKey(modIdx), String(_quizDeadline)); } catch (e) {}
    } else {
        _quizDeadline = stored;   // resuming an attempt already in progress
    }

    badge.style.display = 'inline-flex';
    renderQuizTimer();
    _quizTimerId = setInterval(renderQuizTimer, 250);
}

function renderQuizTimer() {
    var badge = document.getElementById('qh-timer');
    if (!badge) return;

    var remaining = Math.max(0, _quizDeadline - Date.now());
    var totalSec  = Math.ceil(remaining / 1000);
    var mm        = Math.floor(totalSec / 60);
    var ss        = totalSec % 60;

    badge.className = 'quiz-timer' +
        (totalSec <= 10 ? ' danger' : (totalSec <= 60 ? ' warning' : ''));
    badge.innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">' +
        '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2"/><path d="M9 2h6"/></svg>' +
        mm + ':' + (ss < 10 ? '0' : '') + ss;

    if (remaining <= 0) {
        stopQuizTimer();
        autoSubmitQuiz();
    }
}

function stopQuizTimer() {
    if (_quizTimerId) { clearInterval(_quizTimerId); _quizTimerId = null; }
    var badge = document.getElementById('qh-timer');
    if (badge) { badge.style.display = 'none'; badge.className = 'quiz-timer'; }
}

function clearQuizDeadline(modIdx) {
    try { localStorage.removeItem(quizDeadlineKey(modIdx)); } catch (e) {}
}

/* Time is up: grade whatever was answered. Unanswered questions score
   nothing, exactly as if they had been answered incorrectly. */
function autoSubmitQuiz() {
    if (document.getElementById('view-quiz').style.display === 'none') return;
    clearQuizDeadline(_curModIdx);
    submitQuiz(true);
}


function submitQuiz(timeExpired) {
    const data = QUIZ_DATA[_curModIdx];
    if (!data) return;

    let answered = true;
    var answers = {};
    var questionsInView = data.questions.map(function(q, qi){ return qi; });
    if (_retakeQMap.length > 0 && _retakeQMap.length <= data.questions.length) {
        questionsInView = _retakeQMap;
    }

    questionsInView.forEach(function(origQi, ri) {
        const q = data.questions[origQi];
        const selected = document.querySelector('#opts-' + ri + ' .quiz-opt.selected');
        if (!selected) {
            answered = false;
            return;
        }
        answers[String(q.id)] = selected.dataset.letter;
    });

    var prevAnswers = _moduleAnswers[_curModIdx] || {};
    Object.keys(answers).forEach(function(k){ prevAnswers[k] = answers[k]; });
    answers = prevAnswers;

    if (!answered && !timeExpired) {
        alert('Please answer all questions before submitting.');
        return;
    }

    stopQuizTimer();
    clearQuizDeadline(_curModIdx);

    var b = attemptBudget(_curModIdx);
    var usedNow = (b.used || 0) + 1;
    _quizAttempts[String(_curModIdx)] = {
        allowed: b.allowed,
        used: usedNow,
        remaining: b.allowed > 0 ? Math.max(0, b.allowed - usedNow) : null,
        exhausted: b.allowed > 0 && usedNow >= b.allowed
    };

    _moduleAnswers[_curModIdx] = answers;
    _pendingQuizSubmit = { modIdx: _curModIdx, answers: answers };

    document.getElementById('btn-quiz-submit').style.display = 'none';
    const scoreEl = document.getElementById('quiz-score-result');
    scoreEl.textContent = 'Submitting your answers…';
    scoreEl.className = 'quiz-score-result';
    scoreEl.style.display = 'block';

    saveProgress(true);
}

function applyServerQuizSubmission(modIdx, submission) {
    if (!submission || submission.blocked) return;

    _moduleScores[modIdx] = Number(submission.correct || 0);
    _moduleReview[modIdx] = submission.answers || {};
    updateProgress();

    const data = QUIZ_DATA[modIdx];
    const correctCount = _moduleScores[modIdx];
    const pass = !!submission.passed;
    const scoreEl = document.getElementById('quiz-score-result');
    scoreEl.innerHTML = pass
        ? 'You passed! Score: ' + correctCount + '/' + data.questions.length
        : 'Score: ' + correctCount + '/' + data.questions.length + ' — Keep practicing!';
    scoreEl.className = 'quiz-score-result ' + (pass ? 'pass' : 'fail');
    scoreEl.style.display = 'block';

    const moduleCount = Object.keys(QUIZ_DATA).length;
    const hasNextMod = modIdx < moduleCount - 1;

    if (pass) {
        clearRetakeCooldown(modIdx);
        const doneBtn = document.getElementById('qbtn-' + modIdx);
        if (doneBtn) {
            doneBtn.classList.remove('unlocked');
            doneBtn.classList.add('viewed');
            doneBtn.textContent = 'VIEW';
            doneBtn.title = 'View your quiz results';
        }
        if (hasNextMod) {
            unlockModule(modIdx + 1);
            const nb = document.getElementById('btn-next-module');
            nb.textContent = 'Continue to Module ' + (modIdx + 2) + ' ->';
            nb.style.display = 'flex';
        } else {
            reportCourseCompletion();
        }
    } else {
        const failBtn = document.getElementById('qbtn-' + modIdx);
        if (failBtn) {
            failBtn.classList.remove('unlocked');
            failBtn.classList.add('viewed');
            failBtn.textContent = 'VIEW';
            failBtn.title = 'View your quiz results';
        }
        if (!attemptBudget(modIdx).exhausted) startRetakeCooldown(modIdx);
        showRetakeButton(modIdx);
    }
}

function unlockModule(modIdx) {
    const group = document.getElementById('mg-' + modIdx);
    if (!group) return;
    group.classList.remove('module-locked');
    // Hide lock badge
    const lockBadge = document.getElementById('mlock-' + modIdx);
    if (lockBadge) lockBadge.style.display = 'none';
    // Update module sub text
    const sub = document.getElementById('msub-' + modIdx);
    if (sub) sub.innerHTML = '<span style="color:var(--green);font-size:11px;font-weight:700;">Unlocked</span>';
}

/* â”€â”€ Retake quiz 24-hour cooldown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
const RETAKE_COOLDOWN_MS = 24 * 60 * 60 * 1000; // 24 hours
const RETAKE_KEY_PREFIX  = 'quizRetakeUnlockAt_{{ $course->id ?? ($course->title ?? "course") }}_';
var _cooldownTimerId = null;

function retakeKey(modIdx) {
    return RETAKE_KEY_PREFIX + modIdx;
}

/* Called when the student FAILS â†’ start (or restart) the 24h clock */
function startRetakeCooldown(modIdx) {
    try {
        localStorage.setItem(retakeKey(modIdx), String(Date.now() + RETAKE_COOLDOWN_MS));
    } catch (e) { /* storage unavailable â†’ button just stays enabled */ }
}

function clearRetakeCooldown(modIdx) {
    try { localStorage.removeItem(retakeKey(modIdx)); } catch (e) {}
}

/* Milliseconds remaining until retake is allowed (0 = allowed now) */
function retakeMsRemaining(modIdx) {
    // The server is authoritative in BOTH directions. If it lists the module,
    // use its timestamp; if it does not, the quiz is unlocked â€” even when the
    // browser still holds an older countdown. Taking Math.max() of the two
    // meant a lock the server had lifted (because faculty edited the quiz)
    // kept ticking locally until it expired on its own.
    if (_serverUnlocks && _serverUnlocks[modIdx] !== undefined) {
        return Math.max(0, (parseInt(_serverUnlocks[modIdx], 10) || 0) - Date.now());
    }

    if (_serverUnlocksAuthoritative) {
        clearRetakeCooldown(modIdx);
        return 0;
    }

    let unlockAt = 0;
    try { unlockAt = parseInt(localStorage.getItem(retakeKey(modIdx)) || '0', 10); } catch (e) {}
    return Math.max(0, unlockAt - Date.now());
}

function formatCooldown(ms) {
    const totalSec = Math.ceil(ms / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    return (h > 0 ? h + 'h ' : '') + m + 'm ' + String(s).padStart(2, '0') + 's';
}

/* Show the retake button in the correct state (locked countdown or clickable),
   and keep the countdown ticking until it unlocks. */
function attemptBudget(modIdx) {
    var b = _quizAttempts ? _quizAttempts[String(modIdx)] : null;
    return b || { allowed: 1, used: 0, remaining: 0, exhausted: false };
}

function showRetakeButton(modIdx) {
    const btn = document.getElementById('btn-retake');
    if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }

    // Attempts spent â†’ no retake, no countdown. Faculty allowed a fixed
    // number of tries (1 by default), so the quiz is now review-only.
    const budget = attemptBudget(modIdx);
    if (budget.exhausted) {
        btn.classList.add('cooldown');
        btn.disabled  = true;
        btn.innerHTML = 'No Attempts Remaining'
                      + '<span class="cooldown-timer">'
                      + 'You used ' + budget.used + ' of ' + budget.allowed
                      + (budget.allowed === 1 ? ' attempt' : ' attempts')
                      + ' - review only</span>';
        btn.style.display = 'block';
        return;
    }

    function render() {
        const remaining = retakeMsRemaining(modIdx);
        if (remaining > 0) {
            btn.classList.add('cooldown');
            btn.disabled  = true;
            btn.innerHTML = 'Retake Quiz'
                          + '<span class="cooldown-timer">Available in ' + formatCooldown(remaining) + '</span>';
        } else {
            btn.classList.remove('cooldown');
            btn.disabled  = false;
            btn.innerHTML = 'Retake Quiz';
            if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
        }
    }

    render();
    if (retakeMsRemaining(modIdx) > 0) {
        _cooldownTimerId = setInterval(render, 1000);
    }
    btn.style.display = 'block';
}

/* â”€â”€ Retake quiz (wrong questions only) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function retakeQuiz() {
    // Hard guard: no attempts left means the quiz is closed for good.
    if (attemptBudget(_curModIdx).exhausted) {
        showRetakeButton(_curModIdx);
        return;
    }

    // Hard guard: ignore clicks while the 24h cooldown is still running
    if (retakeMsRemaining(_curModIdx) > 0) {
        showRetakeButton(_curModIdx);   // re-render the countdown
        return;
    }

    // Verify with the server before allowing the retake â€” the local clock
    // alone can be bypassed, the quiz_attempts table cannot.
    const modIdx = _curModIdx;
    const btn = document.getElementById('btn-retake');
    if (btn) btn.disabled = true;

    fetch('{{ route('courses.progress', $course->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ retake_check: modIdx })
    })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (data) {
        if (btn) btn.disabled = false;
        if (!data) return;

        // Server says the attempt budget is spent â†’ close the quiz.
        if (data.exhausted) {
            _quizAttempts[String(modIdx)] = {
                allowed: data.allowed, used: data.used,
                remaining: 0, exhausted: true
            };
            showRetakeButton(modIdx);
            return;
        }

        // Server says it's still locked â†’ restore the countdown and stop
        if (data.quiz_unlocks && data.quiz_unlocks[modIdx] && data.quiz_unlocks[modIdx] > Date.now()) {
            _serverUnlocks[modIdx] = data.quiz_unlocks[modIdx];
            try { localStorage.setItem(retakeKey(modIdx), String(data.quiz_unlocks[modIdx])); } catch (e) {}
            showRetakeButton(modIdx);
            return;
        }

        // Cooldown over (or lifted because the quiz was edited) â†’ open the
        // retake and drop any stale local countdown for this module.
        delete _serverUnlocks[modIdx];
        clearRetakeCooldown(modIdx);
        if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
        document.getElementById('btn-retake').style.display        = 'none';
        document.getElementById('quiz-score-result').style.display = 'none';
        showQuizView(modIdx, false, true);   // retakeOnly=true â†’ only wrong questions shown
    })
    .catch(function () {
        if (btn) btn.disabled = false;
    });
}

/* ── Go to next module ───────────────────────────────────── */
function goToNextModule() {
    const nextIdx = _curModIdx + 1;
    const group   = document.getElementById('mg-' + nextIdx);

    if (!group) {
        // No such module in the DOM — nothing to navigate to.
        console.warn('goToNextModule: module ' + nextIdx + ' not found.');
        return;
    }

    // Defensive: the module should already be unlocked by
    // applyServerQuizSubmission(), but make sure before navigating.
    unlockModule(nextIdx);

    const list = document.getElementById('mod-' + nextIdx);
    const chev = document.getElementById('chev-' + nextIdx);
    if (list) { list.classList.add('open'); if (chev) chev.classList.add('open'); }

    const first = group.querySelector('.lesson-item');
    if (first) {
        first.click();
    } else {
        // Module has no lessons (quiz-only, or lessons still empty) —
        // show its welcome screen instead of doing nothing.
        const titleArea = document.getElementById('mta-' + nextIdx);
        if (titleArea) {
            titleArea.click();
        }
    }
    scrollLearningTop();
}

</script>

{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
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

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>

