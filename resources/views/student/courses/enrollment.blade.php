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













    /* â”€â”€ Layout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .layout{display:grid;grid-template-columns:294px 1fr;height:calc(100vh - 74px);}

    /* â”€â”€ Left: Course Nav â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .course-nav{background:#fff;display:flex;flex-direction:column;overflow:hidden;margin:24px 10px 24px 24px;border-radius:22px;border:1px solid var(--line);}
    .course-nav-header{background:var(--navy);color:#fff;padding:20px 18px 18px;flex-shrink:0;border-radius:22px 22px 0 0;}
    .nav-category{font-size:11px;font-weight:600;color:var(--cyan);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px;}
    .nav-title{font-size:15px;font-weight:700;line-height:1.35;margin-bottom:16px;}
    .nav-progress-row{display:flex;justify-content:space-between;font-size:12px;font-weight:500;margin-bottom:7px;}
    .nav-progress-track{width:100%;height:6px;background:rgba(255,255,255,.25);border-radius:999px;overflow:hidden;}
    .nav-progress-fill{height:100%;background:var(--cyan);border-radius:999px;transition:width .5s ease;}
    .nav-progress-breakdown{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:10px;color:rgba(255,255,255,.8);font-size:10px;line-height:1.35;}
    .nav-progress-breakdown span{display:block;white-space:nowrap;}
    .nav-progress-breakdown strong{display:block;color:#fff;font-size:11px;font-weight:600;}
    .course-completion-dialog{width:min(92vw,520px);border:0;border-radius:22px;padding:0;color:var(--navy);box-shadow:0 24px 80px rgba(15,23,42,.3);}
    .course-completion-dialog::backdrop{background:rgba(15,23,42,.68);backdrop-filter:blur(3px);}
    .course-completion-content{padding:36px;text-align:center;}
    .course-completion-icon{width:68px;height:68px;display:grid;place-items:center;margin:0 auto 18px;border-radius:50%;background:#ecfdf3;color:#15803d;font-size:34px;font-weight:800;}
    .course-completion-content h2{margin:0 0 10px;font-size:25px;line-height:1.25;}
    .course-completion-content p{margin:0;color:#526078;font-size:15px;line-height:1.65;}
    .course-completion-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-top:26px;}
    .course-completion-actions a,.course-completion-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:800;cursor:pointer;}
    .course-completion-primary{background:var(--navy);color:#fff;}
    .course-completion-secondary{border:1px solid #cbd5e1;color:var(--navy);background:#fff;}
    .course-completion-tertiary{border:0;color:var(--navy);background:transparent;}
    #btn-finish-course-review:disabled{cursor:not-allowed;opacity:.55;}
    .course-recap-return{margin-left:auto;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:var(--navy);padding:8px 12px;font-weight:700;cursor:pointer;}
    .final-exam-instructions,.course-recap-view{padding:24px;border:1px solid #dce3ef;border-radius:14px;background:#fff;}
    .final-exam-instructions h2,.course-recap-view h2{margin:0 0 12px;color:var(--navy);}
    .final-exam-instructions-content{color:#526078;line-height:1.7;}
    .course-recap-module{margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;}
    .course-recap-lesson{margin:14px 0 14px 14px;padding-left:14px;border-left:2px solid #dce3ef;}
    .course-recap-entry{margin:8px 0;color:#526078;}
    @media(max-width:600px){.course-completion-content{padding:28px 22px;}.course-completion-actions{flex-direction:column;}.course-completion-actions a,.course-completion-actions button{width:100%;}}
    .course-status-card{margin:12px 10px;padding:12px 14px;border:1px solid #dce3ef;border-radius:12px;background:#f8fafc;color:var(--navy);font-size:12px;font-weight:700;line-height:1.5;}
    .course-status-card a{display:inline-block;margin-top:5px;color:var(--navy);text-decoration:underline;}
    .course-review-nav.is-locked{opacity:.55;}
    .course-review-nav.is-eligible .module-title-area{cursor:pointer;}
    .course-evaluation-confirmation{margin-top:12px;padding:12px 14px;border-radius:9px;background:#f0fdf4;color:#166534;font-weight:700;}
    .course-evaluation-error{margin:0 0 14px;padding:12px 14px;border-radius:9px;background:#fef2f2;color:#991b1b;}

    .modules-scroll{flex:1;overflow-y:auto;}
    .modules-scroll::-webkit-scrollbar{width:4px;}
    .modules-scroll::-webkit-scrollbar-thumb{background:#c9cce0;border-radius:4px;}

    .module-group{border-bottom:1px solid var(--line);}
    .module-toggle{width:100%;background:#f1f2f8;border:none;padding:0;display:flex;align-items:stretch;text-align:left;}
    .module-toggle:hover{background:#e8e9f4;}
    .module-title-area{flex:1;padding:15px 16px;display:flex;flex-direction:column;gap:4px;cursor:pointer;}
    .module-num{font-size:13px;font-weight:600;color:var(--navy);line-height:1.4;}
    .module-sub{font-size:11px;font-weight:400;color:var(--muted);}
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
    .lesson-title-text{font-size:13px;font-weight:500;color:var(--navy);line-height:1.4;flex:1;}
    .lesson-meta{font-size:11px;color:var(--muted);white-space:nowrap;flex-shrink:0;}
    .status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;display:none;}
    .lesson-correct .status-dot{display:inline-block;background:var(--green);box-shadow:0 0 0 3px rgba(34,197,94,.2);}
    .lesson-wrong   .status-dot{display:inline-block;background:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,.2);}

    /* Quiz row */
    .lesson-assessment-link{display:block;margin:3px 12px 8px 24px;padding:6px 8px;border-left:2px solid #d8deea;border-radius:0;color:#4b5872;font-size:12px;font-weight:500;line-height:1.4;transition:color .15s,border-color .15s;}
    .lesson-assessment-link:hover{color:var(--navy);border-left-color:var(--navy);}
    .lesson-quiz-link{border-left-color:#c5b36e;}
    .quiz-row{display:flex;align-items:center;justify-content:space-between;padding:14px 12px;margin:0 8px;border-top:1px solid var(--line);background:transparent;gap:10px;}
    /* Last block in the panel â€” round it so the corner stays clean even
       if a parent's overflow clipping is disabled. */
    .modules-scroll > .module-group:last-child .quiz-row{border-radius:0 0 22px 22px;}
    .modules-scroll{border-radius:0 0 22px 22px;}
    .quiz-info-title{font-size:13px;font-weight:600;color:var(--navy);margin-bottom:3px;}
    .quiz-info-sub{font-size:11px;color:var(--muted);}
    /* LOCKED quiz button */
    .btn-quiz-sm{font-weight:600;font-size:12px;padding:7px 16px;border-radius:6px;flex-shrink:0;transition:all .2s;}
    .btn-quiz-sm.locked{background:#e5e7eb;color:#9ca3af;border:1.5px solid #e5e7eb;cursor:not-allowed;}
    .btn-quiz-sm.unlocked{background:#fff;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.unlocked:hover{background:var(--navy);color:#fff;}
    .btn-quiz-sm.viewed{background:#e8e9f4;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.viewed:hover{background:#d0d2ea;}

    /* â”€â”€ Right: Content Area â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .lesson-content{display:flex;flex-direction:column;min-width:0;min-height:0;overflow-y:auto;padding:28px 36px 40px;gap:20px;background:#f4f5f9;}
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
    .inline-assessment-host{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;}
    .inline-assessment-panel{padding:24px 28px;}
    .inline-assessment-header{padding-bottom:16px;border-bottom:1px solid var(--line);margin-bottom:18px;}
    .inline-assessment-eyebrow{display:block;margin-bottom:7px;color:var(--muted);font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;}
    .inline-assessment-header h2{margin:0;color:var(--navy);font-size:22px;font-weight:800;line-height:1.3;}
    .inline-assessment-header p{margin:7px 0 0;color:var(--muted);font-size:14px;}
    .inline-assessment-instructions{margin:0 0 18px;color:#334155;font-size:15px;line-height:1.65;}
    .inline-assessment-form{display:grid;gap:12px;}
    .inline-assessment-form label,.inline-quiz-question legend{color:#172554;font-weight:700;}
    .inline-assessment-form textarea,.inline-assessment-form input[type="text"],.inline-assessment-form input[type="file"],.inline-quiz-input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:11px 12px;background:#fff;color:#172554;font:inherit;}
    .inline-assessment-form textarea{resize:vertical;}
    .inline-assessment-submit{justify-self:start;border:0;border-radius:8px;background:var(--navy);color:#fff;padding:11px 18px;font:inherit;font-weight:800;cursor:pointer;}
    .inline-assessment-submit:disabled{opacity:.65;cursor:wait;}
    .inline-assessment-feedback{padding:13px 15px;border-radius:9px;margin:16px 0;color:#1e293b;line-height:1.5;}
    .inline-assessment-feedback.is-success{background:#ecfdf3;color:#166534;}
    .inline-assessment-feedback.is-pending{background:#fff7ed;color:#9a3412;}
    .inline-assessment-feedback.is-warning{background:#fff7ed;color:#9a3412;}
    .inline-assessment-error{margin:0;color:#b42318;font-size:13px;}
    .inline-assessment-attempts{margin-top:18px;border-top:1px solid var(--line);padding-top:14px;color:#334155;}
    .inline-assessment-attempts summary{cursor:pointer;font-weight:700;}
    .inline-assessment-attempt{display:grid;gap:6px;padding:13px 0;border-bottom:1px solid var(--line);font-size:13px;}
    .inline-assessment-attempt p{margin:0;}
    .inline-quiz-question{margin:0;padding:17px 0;border:0;border-bottom:1px solid var(--line);}
    .inline-quiz-question legend{padding:0 0 12px;line-height:1.5;}
    .inline-quiz-options{display:grid;gap:9px;}
    .inline-quiz-options label{display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border-radius:9px;background:#f7f9fd;font-weight:500;}
    .inline-quiz-options input{margin-top:4px;}
    .inline-quiz-timer{color:#9a3412;font-weight:800;}
    @media (max-width:700px){
        .lesson-card-topbar{padding:16px 18px;}
        .lesson-editor-body{padding:24px 20px 30px;}
        .lesson-card-label{font-size:16px;}
        #lb-desc{font-size:15px;}
        #lb-desc .lesson-pdf-embed__frame{height:55vh;min-height:320px;}
        .inline-assessment-panel{padding:19px 17px;}
        .inline-assessment-header h2{font-size:19px;}
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
    .quiz-opt.selected{border:2px solid var(--navy);background:#dfe3fa;box-shadow:0 0 0 3px rgba(19,23,107,.14);}
    .quiz-opt.selected .quiz-opt-letter{background:var(--gold);color:var(--navy-deep);}
    .quiz-opt.selected .quiz-opt-text{font-weight:800;}
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
    .module-locked .lesson-assessment-link{pointer-events:none;opacity:.4;cursor:not-allowed;}
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
        .course-nav{max-height:none;margin:14px;border-radius:16px;overflow:visible;}
        .modules-scroll{flex:initial;overflow:visible;}
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

/* Keep the module welcome title legible against its navy hero. The page-wide
   rich-content heading rules also target h1 elements inside this view. */
.lesson-content .welcome-hero .welcome-hero-title { color: #fff; }

/* Keep the Start Learning action in the normal welcome content flow. */
#view-welcome { overflow: visible; }
.welcome-hero { border-radius: 20px 20px 0 0; }
.welcome-continue-wrap {
    position: static;
    padding: 24px;
    background: transparent;
}
.welcome-info-card { margin: 18px 20px 0; padding: 18px 20px; }
.welcome-hero { padding: 34px 40px 38px; }
.wss-item { padding: 16px 12px; }
.btn-welcome-continue { padding: 13px 40px; }

@media (max-height: 760px) and (min-width: 981px) {
    .lesson-content.welcome-mode { padding-top: 14px; padding-bottom: 18px; }
    .welcome-hero { padding: 24px 32px 28px; }
    .welcome-hero-badge { margin-bottom: 12px; }
    .welcome-hero-title { font-size: 26px; }
    .welcome-info-card { margin-top: 14px; padding: 14px 18px; }
}
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
            <div class="nav-progress-breakdown" aria-label="Progress by course requirement">
                <span>Lessons <strong id="nav-lessons-progress">0/0</strong></span>
                <span>Activities <strong id="nav-activities-progress">0/0</strong></span>
                <span>Quizzes <strong id="nav-quizzes-progress">0/0</strong></span>
                <span>Review <strong id="nav-review-progress">0/1</strong></span>
            </div>
        </div>

        <div class="modules-scroll">
            <div id="course-status-card" class="course-status-card" @unless($courseReview) hidden @endunless>
                <span id="course-status-message">
                    @if($courseCompletionStatus === 'completed')
                        Completed · View certificate
                    @elseif($courseCompletionStatus === 'awaiting_faculty_verification')
                        Review submitted · Awaiting institutional review
                    @else
                        Review submitted
                    @endif
                </span>
                <a id="course-status-certificate-link" href="{{ route('certificates.index') }}" @if($courseCompletionStatus !== 'completed' || !($course->certificate_enabled ?? false)) hidden @endif>View certificate</a>
            </div>
            @foreach($modules as $mIndex => $module)
            <div class="module-group {{ $mIndex > 0 ? 'module-locked' : '' }}" id="mg-{{ $mIndex }}" data-final-exam="{{ $module->is_final ? '1' : '0' }}" data-assessments="{{ $module->assessment_count }}">

                <div class="module-toggle">
                    <div class="module-title-area" id="mta-{{ $mIndex }}"
                         onclick="showModuleWelcome(@js($module->title),{{ $module->lessons->count() }},{{ $modules->count() }},{{ $module->assessment_count }},{{ $mIndex }},@js($module->description ?? ''))">
                        <span class="module-num">{{ $module->is_final ? 'Final Exam' : 'Module '.($mIndex + 1).': '.$module->title }}</span>
                        <span class="module-sub" id="msub-{{ $mIndex }}">{{ $module->is_final ? 'Course completion assessment' : $module->lessons->count().' lessons' }}</span>
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
                        @php
                            $assessmentItems = collect($lesson->activities ?? [])->map(fn ($activity) => (object) [
                                'kind' => 'activity', 'sort_order' => $activity->sort_order ?? 0, 'item' => $activity,
                            ]);
                            if ($lesson->lesson_quiz ?? null) {
                                $assessmentItems->push((object) ['kind' => 'quiz', 'sort_order' => $lesson->lesson_quiz->sort_order ?? 0, 'item' => $lesson->lesson_quiz]);
                            }
                            $assessmentItems = $assessmentItems->sortBy('sort_order')->values();
                        @endphp
                        @foreach($assessmentItems as $assessment)
                            @if($assessment->kind === 'quiz')
                                <a class="lesson-assessment-link lesson-quiz-link" href="{{ route('quiz.show', $assessment->item->id) }}"
                                   onclick="event.preventDefault(); event.stopPropagation(); openLessonAssessmentFromSidebar({{ $mIndex }}, {{ $lIndex }}, this.href);">
                                    Take lesson quiz: {{ $assessment->item->title }} · pass {{ $assessment->item->passing_score }}%
                                </a>
                            @else
                                <a class="lesson-assessment-link lesson-activity-link" href="{{ route('lesson-activities.show', $assessment->item->id) }}"
                                   onclick="event.preventDefault(); event.stopPropagation(); openLessonAssessmentFromSidebar({{ $mIndex }}, {{ $lIndex }}, this.href);">
                                    {{ Illuminate\Support\Str::headline($assessment->item->activity_type) }}: {{ $assessment->item->title }}{{ $assessment->item->is_required ? ' · Required' : ' · Optional' }}
                                </a>
                            @endif
                        @endforeach
                    @endforeach

                    @if(($module->quiz ?? false) && count($module->quiz->questions ?? []) > 0)
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
                                onclick="{{ $module->is_final ? 'showFinalExamInstructions('.$mIndex.')' : 'handleQuizClick('.$mIndex.')' }}"
                                title="Complete all lessons first">{{ $module->is_final ? 'Take Final Exam' : 'Take Quiz' }}</button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            <div class="module-group course-review-nav {{ $courseEvaluationEligible || $courseReview ? 'is-eligible' : 'is-locked' }}" id="course-review-nav">
                <div class="module-toggle">
                    <div class="module-title-area" role="button" tabindex="0" onclick="openCourseReview()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openCourseReview();}">
                        <span class="module-num">Course Review</span>
                        <span class="module-sub" id="course-review-sub">{{ $courseReview ? '✓ Submitted' : ($courseEvaluationEligible ? 'Ready to submit' : 'Unlocks after the final step') }}</span>
                    </div>
                </div>
            </div>
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
                <p class="welcome-hero-sub">Complete lessons, activities, and assessments in order to unlock the next step.</p>
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
                    <span class="wss-num" id="ws-assess">{{ $current_module->assessment_count ?? 0 }}</span>
                    <span class="wss-lbl">Assessments</span>
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
                <button class="btn-welcome-continue" id="btn-resume-course" onclick="resumeCourse()" type="button" hidden>
                    Resume learning
                </button>
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
                </div>
                <article class="lesson-editor-body">
                    <div id="lb-desc">
                        <p>Lesson content will appear here.</p>
                    </div>
                </article>
            </div>
            <div class="inline-assessment-host" id="inline-assessment-host" hidden>
                <div id="inline-assessment-content"></div>
            </div>

            <button class="btn-lesson-continue" id="btn-lesson-continue" onclick="continueLesson()" type="button">Continue</button>
            <p id="lesson-flow-status" role="status" style="display:none;margin:-8px 0 0;color:var(--muted);font-size:14px;text-align:right;"></p>
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

        <section id="view-final-exam-instructions" style="display:none;flex-direction:column;gap:20px;" aria-labelledby="final-exam-instructions-title">
            <header class="lesson-header-btn"><span id="final-exam-instructions-title">Final Exam Instructions</span></header>
            <article class="final-exam-instructions">
                <h2 id="final-exam-title"></h2>
                <div id="final-exam-instructions-content" class="final-exam-instructions-content"></div>
                <button class="btn-quiz-submit" id="btn-start-final-exam" type="button" onclick="startFinalExam()">Start the exam</button>
            </article>
        </section>

        <section id="view-course-recap" style="display:none;flex-direction:column;gap:20px;" aria-labelledby="course-recap-title">
            <header class="lesson-header-btn">
                <span id="course-recap-title">Course Review</span>
                <button class="course-recap-return" type="button" onclick="returnToCompletionDialog()">Back to completion summary</button>
            </header>
            <article class="course-recap-view" id="course-recap-content"></article>
        </section>

        <section id="view-course-evaluation" style="display:none;flex-direction:column;gap:20px;" aria-labelledby="course-evaluation-page-title">
            <header class="lesson-header-btn">
                <span id="course-evaluation-page-title">Course Review</span>
            </header>
            @include('student.courses.partials.course-evaluation')
        </section>

    </main>
</div>

<dialog class="course-completion-dialog" id="course-completion-dialog" aria-labelledby="course-completion-title">
    <div class="course-completion-content">
        <div class="course-completion-icon" aria-hidden="true">✓</div>
        <h2 id="course-completion-title">Congratulations!</h2>
        <p id="course-completion-message" role="status"></p>
        <div class="course-completion-actions">
            <a class="course-completion-primary" href="{{ route('courses.enrolled') }}">Return to My Courses</a>
            <a class="course-completion-secondary" href="{{ route('courses.browse') }}">Browse Courses</a>
            <button class="course-completion-tertiary" type="button" onclick="openCourseRecap()">Review</button>
        </div>
    </div>
</dialog>

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
        'quizId'       => (int) ($m->quiz->id ?? 0),
        'title'        => $m->quiz->title ?? '',
        'passingScore' => (int) ($m->quiz->passing_score ?? 75),
        'timeLimit'    => (int) ($m->quiz->time_limit ?? 0),   // minutes; 0 = untimed
        'instructions' => $m->quiz->instructions ?? '',
        'questions'    => $m->quiz->questions ?? [],
    ],
])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
var QUIZ_START_URL_TEMPLATE = @json(url('/quiz/__QUIZ_ID__/start'));
var HAS_FINAL_EXAM = @json($modules->contains('is_final', true));
var HAS_COURSE_REVIEW = @json((bool) $courseReview);
var COURSE_EVALUATION_ELIGIBLE = @json((bool) $courseEvaluationEligible);
var COURSE_EVALUATION_EXHAUSTED_FINAL_EXAM = @json((bool) ($courseEvaluationExhaustedFinalExam ?? false));
var HAS_CERTIFICATE = @json((bool) ($course->certificate_enabled ?? false));
var HAS_BADGE = @json((bool) ($course->badge_id ?? false));
var COURSE_COMPLETION_STATUS = @json($courseCompletionStatus ?? null);
var COURSE_RECAP = {!! json_encode($courseRecap ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   STATE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
var _curLessonEl   = null;
var _curModIdx     = 0;
var _curLesIdx     = 0;
var _moduleScores  = {};         // { modIdx: correctCount } â€” replaced on retake
var _moduleAnswers = {};         // { modIdx: { questionId: letter } }
var _moduleReview  = {};         // { modIdx: { questionId: {answer, correct, correct_answer} } } â€” saved on submit for view-only replay
var _retakeQMap    = [];         // maps rendered question index â†’ original question index on retake
var _lastSentPercent = {{ (int) ($progress_percent ?? 0) }};

// Count total quiz questions on load
document.addEventListener('DOMContentLoaded', function () {
    var initialDescription = document.getElementById('wm-description');
    if (initialDescription) {
        var firstModuleDescription = (MODULE_DESCRIPTIONS[0] || '').trim();
        initialDescription.innerHTML = firstModuleDescription
            ? renderLessonContent(firstModuleDescription)
            : '<p class="module-description-empty">No module description has been provided yet.</p>';
    }

    restoreProgress(_savedProgress);
    syncCourseEvaluationUnlock();
    initializeResumeButton();
    initializeCourseEvaluationForm();
    if (_serverProgressPercent >= 100 && HAS_COURSE_REVIEW) {
        reportCourseCompletion(false);
    }
});

/* â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function hideAllViews() {
    document.getElementById('view-welcome').style.display = 'none';
    document.getElementById('view-lesson').style.display  = 'none';
    document.getElementById('view-quiz').style.display    = 'none';
    document.getElementById('view-course-evaluation').style.display = 'none';
    document.getElementById('view-final-exam-instructions').style.display = 'none';
    document.getElementById('view-course-recap').style.display = 'none';
    document.querySelector('.lesson-content').classList.remove('welcome-mode');

    // Navigating away hides the badge, but the DEADLINE is deliberately kept:
    // the clock keeps running, so leaving mid-quiz is not a way to pause it.
    if (_quizTimerId) { clearInterval(_quizTimerId); _quizTimerId = null; }
    if (_inlineAssessmentTimer) { clearInterval(_inlineAssessmentTimer); _inlineAssessmentTimer = null; }
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
var _inlineAssessmentTimer = null;
var _serverUnlocks = {!! json_encode($quiz_unlocks ?? new \stdClass()) !!};   // moduleIdx â†’ retake unlock timestamp (ms), from the server
var _serverUnlocksAuthoritative = true;   // this list is complete: anything absent from it is unlocked
var _quizAttempts = {!! json_encode($quiz_attempts ?? new \stdClass()) !!};   // moduleIdx â†’ {allowed, used, remaining, exhausted}
var _pendingQuizSubmit = null;   // last quiz result awaiting server confirmation
var _saveTimer = null;
var _serverProgressPercent = {{ (int) ($progress_percent ?? 0) }};
var _progressBreakdown = {!! json_encode($progress_breakdown ?? ['lessons' => ['completed' => 0, 'total' => 0], 'activities' => ['completed' => 0, 'total' => 0], 'quizzes' => ['completed' => 0, 'total' => 0]]) !!};
var _serverQuizDeadlines = {};

function initializeCourseEvaluationForm() {
    const form = document.getElementById('course-evaluation-form');
    if (!form) return;
    const finishButton = document.getElementById('btn-finish-course-review');
    const updateFinishAvailability = function () {
        if (!finishButton) return;
        const requiredNames = Array.from(form.querySelectorAll('input[type="radio"][required]'))
            .map(function (input) { return input.name; })
            .filter(function (name, index, names) { return names.indexOf(name) === index; });
        finishButton.disabled = requiredNames.some(function (name) {
            return !form.querySelector('input[type="radio"][name="' + CSS.escape(name) + '"]:checked');
        });
    };
    form.addEventListener('change', updateFinishAvailability);
    updateFinishAvailability();
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const errorBox = document.getElementById('course-evaluation-submit-error');
        if (errorBox) {
            errorBox.hidden = true;
            errorBox.textContent = '';
        }
        form.querySelectorAll('[data-evaluation-error]').forEach(function (field) {
            field.textContent = '';
            field.style.display = 'none';
        });

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        }).then(function (response) {
            return response.json().then(function (data) {
                return { response: response, data: data };
            });
        }).then(function (result) {
            if (!result.response.ok || !result.data.ok) {
                if (result.response.status === 422 && result.data.errors) {
                    Object.keys(result.data.errors).forEach(function (key) {
                        const placeholder = form.querySelector('[data-evaluation-error="' + key + '"]');
                        if (placeholder) {
                            placeholder.textContent = result.data.errors[key][0];
                            placeholder.style.display = 'block';
                        }
                    });
                    return;
                }
                throw new Error(result.data.message || 'Could not submit your course review.');
            }

            HAS_COURSE_REVIEW = true;
            COURSE_EVALUATION_ELIGIBLE = true;
            const thankYou = document.createElement('p');
            thankYou.id = 'course-evaluation-thank-you';
            thankYou.className = 'course-evaluation-confirmation';
            thankYou.textContent = 'Thank you for sharing your feedback. Your evaluation has been recorded.';
            form.replaceWith(thankYou);
            const lockedMessage = document.getElementById('course-evaluation-locked');
            if (lockedMessage) lockedMessage.hidden = true;
            const exhaustedNote = document.getElementById('course-evaluation-exhausted-note');
            if (exhaustedNote) exhaustedNote.hidden = true;
            syncCourseEvaluationUnlock();
            updateProgressBreakdown(_progressBreakdown);
            setCourseStatus('Review submitted', false);

            if (_serverProgressPercent >= 100) {
                reportCourseCompletion(true);
            } else {
                showCourseCompletionDialog({ message: 'Thank you, your review was recorded.' }, 'neutral');
            }
        }).catch(function (error) {
            if (errorBox) {
                errorBox.textContent = error.message || 'Could not submit your course review.';
                errorBox.hidden = false;
            } else {
                alert(error.message || 'Could not submit your course review.');
            }
        });
    });
}

function saveProgress(immediate) {
    if (_saveTimer) clearTimeout(_saveTimer);
    var _run = function () {
        var completed = Array.from(document.querySelectorAll('.lesson-item.lesson-correct'))
            .map(function (el) { return el.dataset.lid; });
        _lastSentPercent = _serverProgressPercent;

        var payload = {
            percent: _serverProgressPercent,
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
        .then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) throw new Error(data.message || 'Could not save course progress.');
                return data;
            });
        })
        .then(function (data) {
            if (!data) return null;
            if (data.evaluation_eligible !== undefined) {
                COURSE_EVALUATION_ELIGIBLE = !!data.evaluation_eligible;
            }
            if (Number.isFinite(Number(data.percent))) {
                _serverProgressPercent = Math.max(0, Math.min(100, Number(data.percent)));
                updateProgress();
            }
            if (data.progress_breakdown) updateProgressBreakdown(data.progress_breakdown);

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

            const finalGroup = Array.from(document.querySelectorAll('.module-group[data-final-exam="1"]'))[0];
            if (COURSE_EVALUATION_ELIGIBLE && finalGroup) {
                const finalIndex = Array.from(document.querySelectorAll('.module-group:not(.course-review-nav)')).indexOf(finalGroup);
                const finalSubmission = data.quiz_submissions
                    ? data.quiz_submissions[String(finalIndex)]
                    : null;
                const finalPassed = finalGroup.dataset.finalPassed === '1'
                    || (finalSubmission && !finalSubmission.blocked && finalSubmission.passed);
                COURSE_EVALUATION_EXHAUSTED_FINAL_EXAM = !!(
                    data.quiz_attempts
                    && data.quiz_attempts[String(finalIndex)]
                    && data.quiz_attempts[String(finalIndex)].exhausted
                    && !finalPassed
                );
            }
            syncCourseEvaluationUnlock();

            if (_pendingQuizSubmit) {
                var submittedModIdx = _pendingQuizSubmit.modIdx;
                var pending = data.quiz_submissions
                    ? data.quiz_submissions[String(submittedModIdx)]
                    : null;
                _pendingQuizSubmit = null;
                if (!pending || pending.blocked) {
                    showBlockedQuizSubmission(submittedModIdx, pending);
                } else {
                    applyServerQuizSubmission(submittedModIdx, pending);
                }
            }

            return data;
        })
        .catch(function (error) {
            const pending = _pendingQuizSubmit;
            _pendingQuizSubmit = null;
            if (pending) {
                const scoreEl = document.getElementById('quiz-score-result');
                if (scoreEl) {
                    scoreEl.textContent = 'We could not confirm your quiz submission. Reload the course to check your result before trying again.';
                    scoreEl.className = 'quiz-score-result fail';
                    scoreEl.style.display = 'block';
                }
            } else {
                setCourseStatus('Course progress could not be saved. Reload the course and try again.', false);
            }
            console.error('Could not save course progress.', error);
            return null;
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
            var group = document.getElementById('mg-' + i);
            if (group && group.dataset.finalExam === '1') group.dataset.finalPassed = '1';
            if (doneBtn) {
                doneBtn.classList.remove('unlocked', 'locked');
                doneBtn.classList.add('viewed');
                doneBtn.textContent = 'VIEW';
                doneBtn.title = 'View your quiz results';
            }
        } else if (doneBtn) {
            // Failed attempt â€” read-only review; retake is gated by the cooldown
            doneBtn.classList.remove('locked');
            doneBtn.classList.add('viewed');
            doneBtn.textContent = 'VIEW';
            doneBtn.title = 'View your quiz results';
        }
    });

    // Restore access in order from verified lessons and passed module quizzes.
    // A score alone must not unlock later modules if current lesson requirements
    // are incomplete, and modules without quizzes still unlock sequentially.
    const moduleGroups = Array.from(document.querySelectorAll('.module-group:not(.course-review-nav)'));
    for (let i = 0; i < moduleGroups.length; i += 1) {
        const lessons = Array.from(moduleGroups[i].querySelectorAll('.lesson-item'));
        const allLessonsDone = lessons.every(function (lesson) {
            return lesson.classList.contains('lesson-correct');
        });

        checkQuizUnlock(i);
        checkModuleComplete(i);
        if (!allLessonsDone) break;

        const quiz = QUIZ_DATA[i];
        if (quiz && quiz.questions && quiz.questions.length > 0) {
            const score = _moduleScores[i];
            const scorePercent = score === undefined
                ? 0
                : Math.round((Number(score) / quiz.questions.length) * 100);
            if (scorePercent < Number(quiz.passingScore || 75)) break;
        }

        unlockModule(i + 1);
    }

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
var _completionDialogShown = false;
function showCourseCompletionDialog(data, variant) {
    if (_completionDialogShown) return;

    var dialog = document.getElementById('course-completion-dialog');
    var title = document.getElementById('course-completion-title');
    var message = document.getElementById('course-completion-message');
    if (!dialog || !title || !message) return;

    if (variant === 'error') {
        title.textContent = 'Course completion could not be confirmed';
        message.textContent = (data && data.message) || 'Your review was recorded, but the remaining course requirements could not be confirmed.';
    } else if (variant === 'neutral') {
        title.textContent = 'Thank you!';
        message.textContent = (data && data.message) || 'Thank you, your review was recorded.';
        } else if (variant === 'completed') {
        title.textContent = 'Congratulations!';
            var approvedCredentials = [];
            if (data.certificate_available) approvedCredentials.push('certificate');
            if (HAS_BADGE) approvedCredentials.push('badge');
            message.textContent = approvedCredentials.length
                ? 'You have completed the course. Your ' + approvedCredentials.join(' and ') + ' ' + (approvedCredentials.length > 1 ? 'have' : 'has') + ' passed institutional review.'
                : 'You have completed the course. Thank you for submitting your review.';
    } else {
        title.textContent = 'Congratulations!';
        var configuredCredentials = [];
        if (HAS_CERTIFICATE) configuredCredentials.push('certificate');
        if (HAS_BADGE) configuredCredentials.push('badge');
        message.textContent = 'You have completed the course. Please wait for institutional review.'
            + (configuredCredentials.length ? ' Any approved ' + configuredCredentials.join(' or ') + ' will be released afterward.' : '');
    }
    _completionDialogShown = true;
    dialog.showModal();
}

function setCourseStatus(message, completed, awaitingReview) {
    const card = document.getElementById('course-status-card');
    const statusMessage = document.getElementById('course-status-message');
    const certificateLink = document.getElementById('course-status-certificate-link');
    if (card) card.hidden = false;
    if (statusMessage) {
        statusMessage.textContent = completed
            ? 'Completed · View certificate'
            : (awaitingReview ? 'Review submitted · Awaiting institutional review' : message);
    }
    if (certificateLink) certificateLink.hidden = !(completed && HAS_CERTIFICATE);
    COURSE_COMPLETION_STATUS = completed ? 'completed' : (awaitingReview ? 'awaiting_faculty_verification' : COURSE_COMPLETION_STATUS);
}

function reportCourseCompletion(showDialog) {
    if (_completionReported || _serverProgressPercent < 100 || !HAS_COURSE_REVIEW) return;
    _completionReported = true;
    fetch('{{ route('courses.complete', $course->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: '{}'
    }).then(function(response) {
        return response.json().then(function(data) {
            return { ok: response.ok && !!data && !!data.ok, data: data };
        });
    }).then(function(result) {
        var data = result.data;
        if (!result.ok) {
            _completionReported = false;
            if (showDialog) showCourseCompletionDialog(data, 'error');
            return;
        }

        if (data.badge_awarded) {
            try { console.log('Badge earned: ' + data.badge_awarded); } catch (e) {}
        }
        setCourseStatus(data.completed ? 'Completed' : 'Review submitted', !!data.completed, !!data.pending_review);
        if (showDialog) {
            showCourseCompletionDialog(data, data.completed ? 'completed' : (data.pending_review ? 'pending' : 'neutral'));
        }
      })
      .catch(function(error) {
          console.error('Could not confirm course completion.', error);
          _completionReported = false;
          if (showDialog) showCourseCompletionDialog(null, 'error');
      });
}

/* The server owns overall progress across lessons, required activities, and passed quizzes. */
function updateProgress() {
    const pct = Math.max(0, Math.min(100, Number(_serverProgressPercent) || 0));
    document.getElementById('nav-fill').style.width = pct + '%';
    document.getElementById('nav-pct').textContent  = pct + '%';
    syncCourseEvaluationUnlock();
    if (pct >= 100) reportCourseCompletion(false);
}

function syncCourseEvaluationUnlock() {
    const form = document.getElementById('course-evaluation-form');
    const lockedMessage = document.getElementById('course-evaluation-locked');
    const finalGroup = Array.from(document.querySelectorAll('.module-group[data-final-exam="1"]'))[0];
    const eligible = COURSE_EVALUATION_ELIGIBLE || (HAS_FINAL_EXAM
        ? !!(finalGroup && finalGroup.dataset.finalPassed === '1')
        : Number(_serverProgressPercent) >= 100);
    COURSE_EVALUATION_ELIGIBLE = eligible;
    if (form) form.hidden = !eligible;
    if (lockedMessage) lockedMessage.hidden = eligible;
    const exhaustedNote = document.getElementById('course-evaluation-exhausted-note');
    if (exhaustedNote) exhaustedNote.hidden = !COURSE_EVALUATION_EXHAUSTED_FINAL_EXAM;
    const reviewNav = document.getElementById('course-review-nav');
    const reviewSub = document.getElementById('course-review-sub');
    if (reviewNav) {
        reviewNav.classList.toggle('is-eligible', eligible || HAS_COURSE_REVIEW);
        reviewNav.classList.toggle('is-locked', !eligible && !HAS_COURSE_REVIEW);
    }
    if (reviewSub) {
        reviewSub.textContent = HAS_COURSE_REVIEW
            ? '✓ Submitted'
            : (eligible ? 'Ready to submit' : 'Unlocks after the final step');
    }
}

function showCourseEvaluation() {
    hideAllViews();
    document.getElementById('view-course-evaluation').style.display = 'flex';
    scrollLearningTop();
}

function showFinalExamInstructions(modIdx) {
    const group = document.getElementById('mg-' + modIdx);
    const button = document.getElementById('qbtn-' + modIdx);
    const quiz = QUIZ_DATA[modIdx];
    if (!group || group.dataset.finalExam !== '1' || !quiz) return;
    if (button && button.classList.contains('locked')) {
        alert('Complete the previous modules and their assessments before starting the Final Exam.');
        return;
    }

    _curModIdx = modIdx;
    document.getElementById('final-exam-title').textContent = quiz.title || 'Final Exam';
    const instructions = document.getElementById('final-exam-instructions-content');
    instructions.innerHTML = (quiz.instructions || '').trim()
        ? renderLessonContent(quiz.instructions)
        : '<p>Read each question carefully before answering.</p>';
    hideAllViews();
    document.getElementById('view-final-exam-instructions').style.display = 'flex';
    scrollLearningTop();
}

function startFinalExam() {
    handleQuizClick(_curModIdx);
}

function openCourseRecap() {
    const dialog = document.getElementById('course-completion-dialog');
    if (dialog && dialog.open) dialog.close();
    renderCourseRecap();
    hideAllViews();
    document.getElementById('view-course-recap').style.display = 'flex';
    scrollLearningTop();
}

function returnToCompletionDialog() {
    showCourseEvaluation();
    const dialog = document.getElementById('course-completion-dialog');
    if (dialog && !dialog.open) dialog.showModal();
}

function renderCourseRecap() {
    const host = document.getElementById('course-recap-content');
    if (!host || host.dataset.rendered === '1') return;
    const heading = document.createElement('h2');
    heading.textContent = 'Your completed course work';
    host.appendChild(heading);

    const moduleScores = [];
    COURSE_RECAP.forEach(function (module) {
        const moduleSection = document.createElement('section');
        moduleSection.className = 'course-recap-module';
        const moduleHeading = document.createElement('h3');
        moduleHeading.textContent = module.title;
        moduleSection.appendChild(moduleHeading);

        (module.lessons || []).forEach(function (lesson) {
            const lessonSection = document.createElement('div');
            lessonSection.className = 'course-recap-lesson';
            const lessonHeading = document.createElement('h4');
            lessonHeading.textContent = lesson.title;
            lessonSection.appendChild(lessonHeading);
            (lesson.activities || []).forEach(function (activity) {
                const answer = document.createElement('p');
                answer.className = 'course-recap-entry';
                answer.textContent = activity.title + ' — ' + (activity.answer || (activity.file_submitted ? 'File submission received.' : 'No response recorded.'));
                if (activity.file_submitted && activity.file_url) {
                    const fileLink = document.createElement('a');
                    fileLink.href = activity.file_url;
                    fileLink.textContent = ' View submitted file';
                    fileLink.rel = 'noopener';
                    answer.appendChild(fileLink);
                }
                lessonSection.appendChild(answer);
            });
            (lesson.quizzes || []).forEach(function (quiz) {
                if (quiz.score === null || quiz.score === undefined) return;
                const score = document.createElement('p');
                score.className = 'course-recap-entry';
                score.textContent = quiz.title + ' — Score: ' + Number(quiz.score).toFixed(0) + '%';
                lessonSection.appendChild(score);
            });
            moduleSection.appendChild(lessonSection);
        });

        if (module.summative) {
            const score = document.createElement('p');
            score.className = 'course-recap-entry';
            score.textContent = 'Summative score — ' + module.summative.title + ': '
                + (module.summative.score === null || module.summative.score === undefined
                    ? 'Not attempted'
                    : Number(module.summative.score).toFixed(0) + '%');
            moduleSection.appendChild(score);
            if (module.summative.score !== null && module.summative.score !== undefined) {
                moduleScores.push(Number(module.summative.score));
            }
        }
        host.appendChild(moduleSection);
    });

    if (moduleScores.length) {
        const total = document.createElement('p');
        total.className = 'course-recap-entry';
        total.style.fontWeight = '700';
        total.textContent = 'Overall summative score: '
            + (moduleScores.reduce(function (sum, score) { return sum + score; }, 0) / moduleScores.length).toFixed(0) + '%';
        host.insertBefore(total, host.children[1] || null);
    }
    host.dataset.rendered = '1';
}

function openCourseReview() {
    if (!COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
        alert('The course review unlocks after you complete the final course step.');
        return;
    }
    showCourseEvaluation();
}

function showContinueToCourseReview() {
    const button = document.getElementById('btn-next-module');
    if (!button) return;
    button.textContent = 'Continue to Course Review →';
    button.onclick = openCourseReview;
    button.style.display = 'flex';
}

function updateProgressBreakdown(breakdown) {
    _progressBreakdown = breakdown || _progressBreakdown;
    const counts = {
        lessons: document.getElementById('nav-lessons-progress'),
        activities: document.getElementById('nav-activities-progress'),
        quizzes: document.getElementById('nav-quizzes-progress'),
        review: document.getElementById('nav-review-progress')
    };
    Object.keys(counts).forEach(function (key) {
        if (!counts[key] || !_progressBreakdown[key]) return;
        counts[key].textContent = Number(_progressBreakdown[key].completed || 0) + '/' + Number(_progressBreakdown[key].total || 0);
    });
    if (counts.review) counts.review.textContent = HAS_COURSE_REVIEW ? '1/1' : '0/1';
}

updateProgressBreakdown(_progressBreakdown);

/* â”€â”€ Module Welcome â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function showModuleWelcome(title, lessonCount, modCount, assessmentCount, modIdx, description) {
    const requestedGroup = document.getElementById('mg-' + modIdx);
    if (requestedGroup && requestedGroup.classList.contains('module-locked')) {
        alert('Complete the previous module and its assessment first.');
        return;
    }
    document.querySelectorAll('.module-title-area').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.lesson-item').forEach(el => el.classList.remove('active'));
    const ta = document.getElementById('mta-' + modIdx);
    if (ta && !ta.classList.contains('completed')) ta.classList.add('active');
    _curModIdx = modIdx;

    document.getElementById('wh-mod-title').textContent = title;
    document.getElementById('ws-mod').textContent       = modCount;
    document.getElementById('ws-les').textContent       = lessonCount;
    document.getElementById('ws-assess').textContent    = assessmentCount;

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

function initializeResumeButton() {
    const resumeButton = document.getElementById('btn-resume-course');
    if (!resumeButton) return;
    if (COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
        resumeButton.textContent = 'Resume: Course Review';
        resumeButton.hidden = false;
        return;
    }
    const nextLesson = Array.from(document.querySelectorAll('.lesson-item')).find(function (lesson) {
        const group = document.getElementById('mg-' + lesson.dataset.module);
        return !lesson.classList.contains('lesson-correct') && group && !group.classList.contains('module-locked');
    });
    if (nextLesson) {
        resumeButton.textContent = 'Resume: ' + nextLesson.querySelector('.lesson-title-text').textContent.trim();
        resumeButton.hidden = false;
        return;
    }

    const moduleGroups = Array.from(document.querySelectorAll('.module-group:not(.course-review-nav)'));
    const availableQuiz = moduleGroups.find(function (group, index) {
        const quiz = QUIZ_DATA[index];
        const score = _moduleScores[index];
        const passed = quiz && score !== undefined
            && Math.round((Number(score) / quiz.questions.length) * 100) >= Number(quiz.passingScore || 75);
        return !passed && group.dataset.finalPassed !== '1' && document.getElementById('qbtn-' + index)
            && !document.getElementById('qbtn-' + index).classList.contains('locked');
    });
    if (availableQuiz) {
        const index = moduleGroups.indexOf(availableQuiz);
        resumeButton.textContent = 'Resume: ' + (QUIZ_DATA[index].title || 'Course assessment');
        resumeButton.hidden = false;
    } else if (COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
        resumeButton.textContent = 'Resume: Course Review';
        resumeButton.hidden = false;
    }
}

function resumeCourse() {
    if (COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
        openCourseReview();
        return;
    }
    const nextLesson = Array.from(document.querySelectorAll('.lesson-item')).find(function (lesson) {
        const group = document.getElementById('mg-' + lesson.dataset.module);
        return !lesson.classList.contains('lesson-correct') && group && !group.classList.contains('module-locked');
    });
    if (nextLesson) {
        nextLesson.click();
        return;
    }
    const moduleGroups = Array.from(document.querySelectorAll('.module-group:not(.course-review-nav)'));
    const availableQuiz = moduleGroups.find(function (group, index) {
        const quiz = QUIZ_DATA[index];
        const score = _moduleScores[index];
        const passed = quiz && score !== undefined
            && Math.round((Number(score) / quiz.questions.length) * 100) >= Number(quiz.passingScore || 75);
        return !passed && group.dataset.finalPassed !== '1' && document.getElementById('qbtn-' + index)
            && !document.getElementById('qbtn-' + index).classList.contains('locked');
    });
    if (availableQuiz) {
        availableQuiz.querySelector('.quiz-row .btn-quiz-sm').click();
        return;
    }
    if (COURSE_EVALUATION_ELIGIBLE) openCourseReview();
}

function continueFromWelcome() {
    const group = document.getElementById('mg-' + _curModIdx);
    const first = group?.querySelector('.lesson-item');
    if (first) {
        first.click();
        return;
    }

    if (!startModuleAssessment(_curModIdx)) {
        alert('This module has no lessons or active assessment available.');
    }
}

function checkLessonAccess(modIdx, lessonIdx) {
    const group = document.getElementById('mg-' + modIdx);
    if (!group || group.classList.contains('module-locked')) {
        alert('Complete the previous module and its assessment first.');
        return false;
    }

    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    if (lessons.slice(0, lessonIdx).some(function (lesson) {
        return !lesson.classList.contains('lesson-correct');
    })) {
        alert('Complete the previous lesson first.');
        return false;
    }

    return true;
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

    var continueButton = document.getElementById('btn-lesson-continue');
    var flowStatus = document.getElementById('lesson-flow-status');
    document.querySelector('#view-lesson .lesson-card').style.display = '';
    document.getElementById('inline-assessment-host').hidden = true;
    document.getElementById('inline-assessment-content').innerHTML = '';
    continueButton.disabled = false;
    continueButton.textContent = 'Continue';
    continueButton.style.display = '';
    flowStatus.style.display = 'none';
    flowStatus.textContent = '';
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
    if (!checkLessonAccess(modIdx, lesIdx)) return;
    _prepLesson(title, desc, duration, modIdx, lesIdx, content);
    startLessonTracking();
    hideAllViews();
    document.getElementById('view-lesson').style.display = 'flex';
    scrollLearningTop();
}

function openLessonAssessmentFromSidebar(modIdx, lessonIdx, assessmentUrl) {
    if (!checkLessonAccess(modIdx, lessonIdx)) return;
    const selectedLesson = document.getElementById('li-' + modIdx + '-' + lessonIdx);
    if (!selectedLesson) return;
    selectedLesson.click();
    continueLesson(assessmentUrl);
}

async function openInlineAssessment(url) {
    const host = document.getElementById('inline-assessment-host');
    const content = document.getElementById('inline-assessment-content');
    const lessonCard = document.querySelector('#view-lesson .lesson-card');
    const continueButton = document.getElementById('btn-lesson-continue');
    const flowStatus = document.getElementById('lesson-flow-status');
    continueButton.style.display = 'none';
    flowStatus.style.display = 'none';
    lessonCard.style.display = 'none';
    host.hidden = false;
    content.innerHTML = '<div class="inline-assessment-panel" role="status">Loading assessment…</div>';

    try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const payload = await response.json();
        if (!response.ok || !payload.html) throw new Error(payload.message || 'Could not load this assessment.');
        content.innerHTML = payload.html;
        const panel = content.querySelector('[data-assessment-kind]');
        if (!panel) throw new Error('The assessment could not be displayed.');

        const canContinue = panel.dataset.canContinue === '1';
        continueButton.textContent = panel.querySelector('.quiz-cooldown')
            ? 'Check quiz availability'
            : (panel.dataset.assessmentKind === 'activity' && panel.querySelector('.is-pending') ? 'Check review status' : 'Continue');
        continueButton.style.display = canContinue ? '' : 'none';
        continueButton.disabled = false;
        startInlineQuizTimer(panel);
        host.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
        content.innerHTML = '<div class="inline-assessment-panel inline-assessment-feedback is-warning" role="alert"></div>';
        content.querySelector('[role="alert"]').textContent = error.message || 'Could not load this assessment.';
        lessonCard.style.display = '';
        host.hidden = true;
        continueButton.textContent = 'Continue';
        continueButton.style.display = '';
        continueButton.disabled = false;
    }
}

function startInlineQuizTimer(panel) {
    const timer = panel.querySelector('[data-inline-quiz-timer]');
    if (!timer) return;
    if (_inlineAssessmentTimer) window.clearInterval(_inlineAssessmentTimer);
    const form = panel.querySelector('[data-assessment-form]');
    let remaining = Number(panel.dataset.timerRemaining || 0);
    const render = function () {
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        timer.textContent = 'Time remaining: ' + minutes + ':' + String(seconds).padStart(2, '0');
        if (remaining <= 60) timer.classList.add('is-urgent');
        if (remaining <= 0) {
            window.clearInterval(_inlineAssessmentTimer);
            _inlineAssessmentTimer = null;
            submitInlineAssessment(form, true);
            return;
        }
        remaining -= 1;
    };
    render();
    _inlineAssessmentTimer = window.setInterval(render, 1000);
}

document.getElementById('inline-assessment-content').addEventListener('submit', function (event) {
    const form = event.target.closest('[data-assessment-form]');
    if (!form) return;
    event.preventDefault();
    submitInlineAssessment(form, false);
});

async function submitInlineAssessment(form, timeExpired) {
    if (!form || form.dataset.submitting === '1') return;
    if (!timeExpired && !form.reportValidity()) return;

    const panel = form.closest('[data-assessment-kind]');
    const error = form.querySelector('[data-assessment-error]');
    const submitButton = form.querySelector('button[type="submit"]');
    const continueButton = document.getElementById('btn-lesson-continue');
    form.dataset.submitting = '1';
    submitButton.disabled = true;
    submitButton.textContent = timeExpired ? 'Time expired…' : 'Submitting…';
    error.hidden = true;

    const formData = new FormData(form);
    if (timeExpired) {
        for (const key of Array.from(formData.keys())) {
            if (key.startsWith('answers[')) formData.delete(key);
        }
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });
        const result = await response.json();
        if (!response.ok || !result.ok) {
            const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
            throw new Error(firstError || result.message || 'Could not submit this assessment.');
        }

        if (_inlineAssessmentTimer) window.clearInterval(_inlineAssessmentTimer);
        _inlineAssessmentTimer = null;
        if (Number.isFinite(Number(result.progress_percent))) {
            _serverProgressPercent = Math.max(0, Math.min(100, Number(result.progress_percent)));
            updateProgress();
        }
        if (result.progress_breakdown) updateProgressBreakdown(result.progress_breakdown);
        form.remove();

        const feedback = document.createElement('div');
        feedback.className = 'inline-assessment-feedback';
        feedback.setAttribute('role', 'status');
        if (panel.dataset.assessmentKind === 'quiz') {
            feedback.classList.add(result.passed ? 'is-success' : 'is-warning');
            feedback.textContent = result.passed
                ? 'You passed with ' + result.score + '%. ' + (result.lesson_completed ? 'This lesson is complete.' : 'Continue to the next required item.')
                : 'You scored ' + result.score + '%. You did not meet the passing score; the retake cooldown and attempt limit still apply.';
            panel.dataset.canContinue = result.passed || result.retake_unlock_at ? '1' : '0';
            continueButton.textContent = result.passed ? 'Continue' : (result.retake_unlock_at ? 'Check quiz availability' : 'Continue');
        } else if (result.status === 'submitted') {
            feedback.classList.add('is-pending');
            feedback.textContent = 'Assignment submitted. It must pass faculty review before this lesson can be completed.';
            panel.dataset.canContinue = '1';
            continueButton.textContent = 'Check review status';
        } else {
            feedback.classList.add('is-success');
            feedback.textContent = result.lesson_completed
                ? 'Activity accepted. This lesson is complete.'
                : 'Activity accepted. Continue to the next required item.';
            panel.dataset.canContinue = '1';
            continueButton.textContent = 'Continue';
        }
        panel.appendChild(feedback);
        continueButton.style.display = panel.dataset.canContinue === '1' ? '' : 'none';
        continueButton.disabled = false;
        if (result.lesson_completed && _curLessonEl) {
            _curLessonEl.classList.add('lesson-correct');
            _curLessonEl.classList.remove('lesson-wrong');
            checkQuizUnlock(_curModIdx);
            checkModuleComplete(_curModIdx);
        }
        if (result.lesson_completed && Number(result.progress_breakdown && result.progress_breakdown.percent) >= 100) {
            reportCourseCompletion();
        }
    } catch (submitError) {
        error.textContent = submitError.message || 'Could not submit this assessment.';
        error.hidden = false;
        form.dataset.submitting = '0';
        submitButton.disabled = false;
        submitButton.textContent = panel.dataset.assessmentKind === 'quiz' ? 'Submit lesson quiz' : 'Submit activity';
    }
}

/* Continue from lesson content into its required activities and assessments. */
function continueLesson(assessmentUrl) {
    const btn = document.getElementById('btn-lesson-continue');
    const lessonEl = _curLessonEl;
    if (!lessonEl || btn.disabled) return;

    const lessonId = lessonEl.dataset.lid;
    const openedAt = _lessonOpenedAt;
    const startRequest = _lessonStartRequest;
    btn.disabled = true;
    btn.textContent = 'Continuing...';

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
            if (!response.ok || !result.ok) throw new Error(result.message || 'Could not continue from this lesson.');

            if (result.evaluation_eligible !== undefined) {
                COURSE_EVALUATION_ELIGIBLE = !!result.evaluation_eligible;
            }
            if (Number.isFinite(Number(result.progress_percent))) {
                _serverProgressPercent = Math.max(0, Math.min(100, Number(result.progress_percent)));
                updateProgress();
            }
            syncCourseEvaluationUnlock();
            if (result.progress_breakdown) updateProgressBreakdown(result.progress_breakdown);

            if (result.next_url) {
                await openInlineAssessment(result.next_url);
                return;
            }

            if (assessmentUrl && result.completed) {
                await openInlineAssessment(assessmentUrl);
                return;
            }

            if (result.completed) {
                lessonEl.classList.add('lesson-correct');
                lessonEl.classList.remove('lesson-wrong', 'active');
                checkQuizUnlock(_curModIdx);
                checkModuleComplete(_curModIdx);
                if (Number(result.progress_breakdown && result.progress_breakdown.percent) >= 100) {
                    reportCourseCompletion();
                }
                advanceAfterLesson();
                return;
            }

            btn.textContent = result.pending_review ? 'Check review status' : 'Continue';
            btn.disabled = false;
            const flowStatus = document.getElementById('lesson-flow-status');
            flowStatus.textContent = result.message || (result.pending_review
                ? 'Your assignment is waiting for faculty review. This lesson will complete automatically if it passes.'
                : 'Finish the required activities and assessments to complete this lesson.');
            flowStatus.style.display = 'block';
        } catch (error) {
            btn.disabled = false;
            btn.textContent = 'Continue';
            alert(error.message || 'Could not continue from this lesson.');
        }
    }, waitMs);
}

/* â”€â”€ Unlock QUIZ when all lessons are marked complete â”€ */
function checkQuizUnlock(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.length === 0 || lessons.every(l => l.classList.contains('lesson-correct'));
    const qbtn    = document.getElementById('qbtn-' + modIdx);
    if (allDone) {
        if (qbtn) {
            qbtn.classList.replace('locked', 'unlocked');
            qbtn.title = 'Take the quiz!';
        } else {
            unlockModule(modIdx + 1);
        }
    } else {
        if (qbtn) {
            qbtn.classList.replace('unlocked', 'locked');
            qbtn.title = 'Complete all lessons first';
        }
    }
}

/* â”€â”€ Module completion marker â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function checkModuleComplete(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.every(l => l.classList.contains('lesson-correct'));
    const ta      = document.getElementById('mta-' + modIdx);
    const sub     = document.getElementById('msub-' + modIdx);
    const finalExamPassed = group.dataset.finalExam === '1' && group.dataset.finalPassed === '1';
    if ((allDone && lessons.length > 0) || finalExamPassed) {
        ta.classList.add('completed');
        ta.classList.remove('active');
        sub.innerHTML = '<span style="color:var(--green);font-weight:800;">\u2713 Completed</span>';
    }
}

/* â”€â”€ Next lesson (Continue button) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function scrollLearningTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function advanceAfterLesson() {
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
    if (nextGroup && !nextGroup.classList.contains('module-locked')) {
        if (nextFirst) {
            nextFirst.click();
            scrollLearningTop();
            return;
        }

        if (startModuleAssessment(nextModIdx)) {
            scrollLearningTop();
            return;
        }
    }

    if (COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
        openCourseReview();
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
        document.querySelectorAll('.module-group:not(.course-review-nav)').length,
        Number(group?.dataset.assessments || 0),
        _curModIdx,
        ''
    );
    scrollLearningTop();
}

function startModuleAssessment(modIdx) {
    const quiz = QUIZ_DATA[modIdx];
    if (!quiz || !quiz.questions || quiz.questions.length === 0) return false;

    checkQuizUnlock(modIdx);
    const quizButton = document.getElementById('qbtn-' + modIdx);
    if (!quizButton || quizButton.classList.contains('locked')) return false;

    const group = document.getElementById('mg-' + modIdx);
    if (group && group.dataset.finalExam === '1') {
        showFinalExamInstructions(modIdx);
        return true;
    }

    handleQuizClick(modIdx);
    return true;
}

function handleQuizClick(modIdx) {
    const qbtn = document.getElementById('qbtn-' + modIdx);
    if (qbtn && qbtn.classList.contains('locked')) {
        alert('Finish the required activities and assessments in every lesson before taking the module quiz.');
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
function showQuizView(modIdx, viewOnly, retakeOnly, serverStarted) {
    _curModIdx = modIdx;
    const data = QUIZ_DATA[modIdx];
    if (!data) return;
    const nextModuleButton = document.getElementById('btn-next-module');
    nextModuleButton.onclick = goToNextModule;
    nextModuleButton.style.display = 'none';

    viewOnly = viewOnly || false;
    retakeOnly = retakeOnly || false;
    if (!viewOnly && !serverStarted) {
        startModuleQuiz(modIdx).then(function () {
            showQuizView(modIdx, false, retakeOnly, true);
        }).catch(function (message) {
            alert(message || 'Could not start this quiz. Reload the course and try again.');
        });
        return;
    }
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
        label.textContent = 'Question ' + (ri + 1);
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
                    optWrap.querySelectorAll('.quiz-opt').forEach(function(el) { el.setAttribute('aria-checked', 'false'); });
                    div.setAttribute('aria-checked', 'true');
                };
                div.setAttribute('role', 'radio');
                div.setAttribute('aria-checked', 'false');
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
    else startQuizTimer(modIdx, retakeOnly === true, _serverQuizDeadlines[modIdx]);
}

function startModuleQuiz(modIdx) {
    const quizId = Number(QUIZ_DATA[modIdx]?.quizId || 0);
    if (!quizId) return Promise.reject('This module does not have an active quiz.');

    return fetch(QUIZ_START_URL_TEMPLATE.replace('__QUIZ_ID__', encodeURIComponent(quizId)), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok || !data.ok) throw new Error(data.message || 'This quiz is not currently available.');
            _serverQuizDeadlines[modIdx] = Number(data.deadline) || null;
            return data;
        });
    }).catch(function (error) {
        return Promise.reject(error.message || error);
    });
}

var _quizTimerId  = null;
var _quizDeadline = 0;

function quizDeadlineKey(modIdx) {
    return 'upskill_quiz_deadline_c{{ $course->id }}_m' + modIdx;
}

function startQuizTimer(modIdx, restartClock, serverDeadline) {
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

    if (serverDeadline) {
        _quizDeadline = serverDeadline;
        try { localStorage.setItem(quizDeadlineKey(modIdx), String(serverDeadline)); } catch (e) {}
    } else if (restartClock || !stored || stored <= Date.now()) {
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

    const moduleCount = document.querySelectorAll('.module-group:not(.course-review-nav)').length;
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
        const moduleGroup = document.getElementById('mg-' + modIdx);
        if (moduleGroup && moduleGroup.dataset.finalExam === '1') {
            moduleGroup.dataset.finalPassed = '1';
            checkModuleComplete(modIdx);
            syncCourseEvaluationUnlock();
            if (COURSE_EVALUATION_ELIGIBLE) {
                openCourseReview();
                return;
            }
        }
        if (hasNextMod) {
            unlockModule(modIdx + 1);
            const nb = document.getElementById('btn-next-module');
            nb.textContent = 'Continue to Module ' + (modIdx + 2) + ' ->';
            nb.onclick = goToNextModule;
            nb.style.display = 'flex';
        } else if (COURSE_EVALUATION_ELIGIBLE && !HAS_COURSE_REVIEW) {
            showContinueToCourseReview();
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

function showBlockedQuizSubmission(modIdx, submission) {
    const scoreEl = document.getElementById('quiz-score-result');
    const submitButton = document.getElementById('btn-quiz-submit');
    if (scoreEl) {
        scoreEl.textContent = (submission && submission.message)
            || 'No attempt is currently available. Check the attempt limit and retake lockout.';
        scoreEl.className = 'quiz-score-result fail';
        scoreEl.style.display = 'block';
    }
    if (submitButton) submitButton.style.display = 'none';
    if (submission && submission.unlock !== null && submission.unlock !== undefined) {
        _serverUnlocks[String(modIdx)] = submission.unlock;
    }
    showRetakeButton(modIdx);

    const group = document.getElementById('mg-' + modIdx);
    if (COURSE_EVALUATION_ELIGIBLE && group && group.dataset.finalExam === '1' && !HAS_COURSE_REVIEW) {
        showContinueToCourseReview();
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
    if (group.dataset.finalExam === '1') checkQuizUnlock(modIdx);
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
    return b || { allowed: 0, used: 0, remaining: null, exhausted: false };
}

function showRetakeButton(modIdx) {
    const btn = document.getElementById('btn-retake');
    if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }

    // Attempts spent â†’ no retake, no countdown. Faculty allowed a fixed
    // number of tries, so the quiz is now review-only.
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
        startModuleQuiz(modIdx).then(function () {
            showQuizView(modIdx, false, true, true);   // Retake only the incorrect questions.
        }).catch(function (message) {
            alert(message || 'Could not start this quiz. Reload the course and try again.');
        });
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
    } else if (!startModuleAssessment(nextIdx)) {
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
