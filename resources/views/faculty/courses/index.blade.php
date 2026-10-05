{{--
    resources/views/faculty/My_Courses.blade.php

    Faculty â€º My Courses â€” ONE self-contained Blade view with TWO modes:

      â€¢ $mode = 'list'    â†’ the My Courses list (default)
                            URL: /Faculty-mycourses          (faculty.courses)
      â€¢ $mode = 'manage'  â†’ the "Managing Course" screen shown after
                            clicking a course's Manage button
                            URL: /Faculty-mycourses/manage   (faculty.courses.manage)

    âš  STANDALONE: the only connections are between these two modes and back
      to the Faculty Dashboard (route 'faculty.dashboard'). Every other
      button / link / input is intentionally non-functioning (href="#" or
      plain <button type="button">) until the real routes are wired up.

    LIST mode data:
        'user', 'courses' (each: ->title, ->description, ->status,
        ->students_count, ->modules_count, ->lessons_count, ->thumbnail_url)

    MANAGE mode data:
        'user',
        'course'   (->title, ->status, ->level, ->students_count, ->modules_count),
        'modules'  (each: ->title, ->subtitle,
                    ->lessons (->title, ->meta, ->thumbnail_url),
                    ->quiz (->title, ->questions_count, ->passing_score)),
        'students' (each: ->name, ->student_id, ->status, ->avatar_url),
        'quizAverages' (each: ->title, ->percent)
--}}
@php $mode = $mode ?? 'list'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<meta name="csrf-token" content="{{ csrf_token() }}">

@vite(['resources/css/app.css', 'resources/js/app.js'])

<title>{{ $mode === 'quiz' ? 'Add Quiz' : ($mode === 'manage' ? 'Manage Course' : 'My Courses') }} | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --green:#16a34a;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{padding:32px 36px 60px;}

    /* â”€â”€ LIST mode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:26px;margin:0 0 4px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:13px;font-weight:600;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 28px;border-radius:999px;font-size:15px;}
    .page-head .btn-outline{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#e5b82e;border:1px solid #c99812;border-radius:8px;color:#17202a;font-weight:700;box-shadow:0 3px 8px rgba(146,104,0,.18);text-decoration:none;transition:background .15s ease,box-shadow .15s ease,transform .15s ease;}
    .page-head .btn-outline svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;}
    .page-head .btn-outline:hover{background:#f0c642;border-color:#bd8d0c;box-shadow:0 0 0 3px rgba(229,184,46,.2),0 0 18px rgba(229,184,46,.55);transform:translateY(-1px);}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:22px 24px;display:flex;align-items:center;gap:22px;margin-bottom:24px;}
    .thumb{width:88px;height:88px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h3{margin:0 0 8px;font-size:21px;font-weight:700;color:var(--navy);}
    .course-info .desc{font-size:12px;color:var(--navy);opacity:.85;line-height:1.5;max-width:520px;margin-bottom:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
    .course-meta{display:flex;align-items:center;gap:26px;flex-wrap:wrap;}
    .status-chip{display:inline-block;font-size:12px;font-weight:700;padding:8px 24px;border-radius:999px;border:1.5px solid #c9ccdb;background:#fff;color:var(--ink);}
    .status-chip.status-pending{background:#e69a00;border-color:#e69a00;color:#fff;}
    .status-chip.status-published{background:#168545;border-color:#168545;color:#fff;}
    .status-chip.status-denied{background:#c9362b;border-color:#c9362b;color:#fff;}
    .status-chip.status-draft{background:#667085;border-color:#667085;color:#fff;}
    .meta-item{text-align:center;font-size:11px;color:var(--muted);line-height:1.3;}
    .meta-item .meta-num{display:block;font-weight:800;font-size:14px;color:var(--ink);}
    .card-actions{display:flex;flex-direction:column;gap:12px;flex-shrink:0;}
    .btn-action{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 30px;border-radius:999px;font-size:15px;min-width:150px;display:block;width:100%;text-align:center;transition:background .15s ease,color .15s ease;}
    .btn-action:hover{background:#eef1ff;}
    .btn-action.primary{background:var(--navy);color:#fff;}
    .btn-action.primary:hover{background:#1a2fa0;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);font-size:14px;}

    /* â”€â”€ MANAGE mode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .course-banner{background:var(--navy);border-radius:18px;box-shadow:var(--shadow);padding:22px 28px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;color:#fff;margin-bottom:32px;}
    .banner-info .kicker{font-size:13px;font-weight:700;opacity:.85;margin-bottom:6px;}
    .banner-info h2{margin:0 0 10px;font-size:26px;color:#fff;}
    .banner-meta{display:flex;gap:18px;flex-wrap:wrap;font-size:13px;font-weight:700;opacity:.9;}
    .banner-actions{display:flex;flex-direction:column;gap:10px;align-items:stretch;}
    .banner-actions .row{display:flex;gap:10px;}
    .btn-pill{background:#fff;border:none;color:var(--navy);font-weight:700;padding:10px 26px;border-radius:999px;font-size:14px;flex:1;display:inline-flex;align-items:center;justify-content:center;text-align:center;text-decoration:none;cursor:pointer;transition:background .18s ease,color .18s ease,transform .18s ease;}
    a.btn-pill:hover{background:var(--gold,#dba617);color:#0c0f4d;transform:translateY(-1px);}
    span.btn-pill{cursor:default;}

    .section-title{font-size:24px;margin:0 0 16px;color:var(--navy);}
    .section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
    .section-head .section-title{margin:0;}
    .pill-muted{display:inline-block;font-size:12px;font-weight:700;padding:10px 24px;border-radius:999px;border:1.5px solid #c9ccdb;background:#fff;color:#9aa0b4;}
    .content-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:20px;align-items:start;}
    .empty-modules{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:110px 30px;text-align:center;}
    .empty-modules h4{margin:0 0 12px;font-size:17px;color:#111;}
    .empty-modules p{margin:0;font-size:14px;color:#9aa0b4;font-weight:600;}

    .add-module-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;flex-direction:column;gap:14px;margin-bottom:24px;}
    .add-module-inputs{display:flex;gap:14px;flex-wrap:wrap;}
    .input-outline{flex:1;min-width:170px;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;font-size:14px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;}
    .input-outline::placeholder{color:#9aa0b4;}
    .btn-navy{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 26px;border-radius:999px;font-size:14px;align-self:flex-end;}

    .module-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:14px;margin-bottom:26px;display:flex;flex-direction:column;gap:12px;}
    .lesson-row{border:1px solid var(--line);border-radius:14px;padding:12px 14px;display:flex;align-items:center;gap:14px;}
    /* Inline lesson editor â€” reuses .lesson-form, so it matches Add Lesson */
    .btn-edit-lesson{background:#fff;border:1.5px solid var(--navy,#13176b);color:var(--navy,#13176b);
        border-radius:999px;padding:7px 16px;font-weight:800;font-size:12px;white-space:nowrap;cursor:pointer;}
    .btn-edit-lesson:hover{background:var(--navy,#13176b);color:#fff;}
    .lesson-edit-form{background:#f9fbff;border:1px solid var(--line);border-radius:14px;
        padding:16px 16px 12px;margin:-6px 0 14px;}
    .lesson-thumb{width:52px;height:52px;border-radius:10px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .lesson-info{flex:1;min-width:0;}
    .lesson-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .lesson-info .sub{font-size:11px;color:var(--muted);}
    .btn-x{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--navy);background:#fff;color:var(--navy);font-weight:800;font-size:15px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .module-head .lesson-info h4{font-size:15px;}
    .btn-add-lesson{background:#fff;border:1.5px solid #c9ccdb;color:var(--navy);font-weight:700;padding:9px 20px;border-radius:999px;font-size:13px;flex-shrink:0;}
    .lessons-empty{min-height:72px;}
    .quiz-none{font-size:13px;color:#9aa0b4;font-weight:600;flex:1;min-width:0;}

    /* Inline "Add Lesson" form (shown inside a module card) */
    .lesson-form{display:flex;flex-direction:column;gap:14px;padding:14px 4px 6px;}
    .lesson-form .row2{display:grid;grid-template-columns:1fr;gap:14px;}
    .textarea-outline{width:100%;border:1.5px solid #c9ccdb;border-radius:14px;padding:14px 18px;min-height:95px;font-family:inherit;font-size:13px;font-weight:600;color:var(--ink);outline:none;resize:vertical;}
    .textarea-outline::placeholder{color:#9aa0b4;font-weight:500;}
    .sel-wrap{position:relative;}
    .sel-wrap select{appearance:none;-webkit-appearance:none;-moz-appearance:none;width:100%;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 44px 12px 20px;font-size:13px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;background:#fff;cursor:pointer;}
    .sel-wrap::after{content:"";position:absolute;right:20px;top:50%;width:9px;height:9px;border-right:2.5px solid var(--ink);border-bottom:2.5px solid var(--ink);transform:translateY(-70%) rotate(45deg);pointer-events:none;}
    .lesson-form .actions{display:flex;gap:14px;align-items:center;flex-wrap:wrap;}
    .lesson-form .actions .input-outline{flex:0 1 200px;min-width:150px;}
    .btn-save-lesson{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 30px;border-radius:999px;font-size:14px;}
    .btn-cancel-outline{background:#fff;border:1.5px solid #c9ccdb;color:var(--ink);font-weight:700;padding:12px 28px;border-radius:999px;font-size:14px;display:inline-block;}
    @media (max-width:980px){ .lesson-form .row2{grid-template-columns:1fr;} }
    /* â”€â”€ QUIZ BUILDER mode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .back-link{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--navy);margin-bottom:8px;}
    .quiz-head{font-size:24px;margin:0 0 20px;color:var(--navy);}
    .quiz-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:22px;margin-bottom:26px;}
    .q-label{display:block;font-size:11px;font-weight:800;color:var(--navy);margin-bottom:7px;}
    .q-label .req{color:#dc2626;}
    .grid3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:18px;}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;}
    .q-head{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
    .q-head h3{margin:0;font-size:21px;color:var(--navy);}
    .q-head .type-label{font-size:12px;font-weight:800;color:var(--navy);margin-left:auto;}
    .q-head .sel-wrap{min-width:190px;}
    .q-head .points{width:110px;}
    .question-input{max-width:420px;}
    .choice-row{display:flex;align-items:center;gap:16px;margin-bottom:14px;}
    .choice-letter{font-weight:800;font-size:16px;color:var(--navy);width:26px;flex-shrink:0;}
    .choice-row .input-outline{flex:1;max-width:420px;}
    .mark-correct{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:800;color:var(--ink);cursor:pointer;white-space:nowrap;}
    .mark-correct input{display:none;}
    .radio-dot{width:20px;height:20px;border-radius:50%;border:1.5px solid #c9ccdb;background:#fff;flex-shrink:0;}
    .mark-correct input:checked + .radio-dot{background:#22c55e;border-color:#22c55e;}
    .btn-add-choice{width:100%;background:#fff;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px;font-weight:700;color:var(--ink);font-size:13px;margin-top:6px;}
    .tf-label{flex:1;max-width:420px;font-weight:700;font-size:14px;color:var(--ink);border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;background:#f9fafb;}
    .save-quiz-row{display:flex;justify-content:flex-end;}
    @media (max-width:1024px){ .grid3{grid-template-columns:1fr;} .grid2{grid-template-columns:1fr;} }

    /* Matches .lesson-row: the info block takes the free space and the
       buttons sit together on the right. space-between spread all three
       children apart instead, stranding Edit Quiz in the middle. */
    .quiz-row{border:1px solid var(--line);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .quiz-info{flex:1;min-width:0;}
    .quiz-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .quiz-info .sub{font-size:11px;color:var(--muted);}
    .btn-outline-navy{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:9px 20px;border-radius:10px;font-size:13px;flex-shrink:0;}

    /* Lesson activity and submission review UI */
    .activity-panel{display:flex;flex-direction:column;gap:10px;padding:14px 4px 4px;}
    .activity-panel > .activity-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:0 2px 2px;}
    .activity-panel > .activity-head strong{font-size:14px;color:var(--navy);}
    .activity-card{border:1px solid var(--line);border-radius:12px;background:#fff;padding:14px 16px;display:flex;flex-direction:column;gap:12px;}
    .activity-card .activity-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
    .activity-card .activity-head strong{font-size:14px;color:var(--navy);}
    .activity-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .activity-actions form{margin:0;}
    .btn-edit-inline,.btn-delete-activity{border:1px solid #c9ccdb;background:#fff;color:var(--navy);font-family:inherit;font-size:12px;font-weight:700;padding:8px 13px;border-radius:999px;cursor:pointer;}
    .btn-edit-inline:hover,.btn-delete-activity:hover{background:#eef2ff;}
    .btn-delete-activity{border-color:#fecaca;color:#b91c1c;}
    .btn-delete-activity:hover{background:#fef2f2;}
    .activity-review-link{display:inline-flex;align-items:center;gap:7px;align-self:flex-start;border:1px solid #c7d2fe;border-radius:999px;padding:7px 11px;color:var(--navy);font-size:11px;font-weight:800;background:#eef2ff;white-space:nowrap;}
    .activity-review-link:hover{background:#e0e7ff;}
    .pending-count{min-width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:0 6px;background:#dc2626;color:#fff;font-size:10px;}
    .manage-flash{margin:-14px 0 22px;padding:12px 16px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534;font-size:13px;font-weight:700;transition:opacity .25s ease,transform .25s ease;}
    .manage-flash.error{border-color:#fecaca;background:#fef2f2;color:#991b1b;}
    .manage-flash.is-dismissing{opacity:0;transform:translateY(-4px);}
    .activity-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:14px;background:#f8faff;border:1px solid #e2e8f0;border-radius:12px;}
    .activity-form[hidden],.activity-edit-form[hidden]{display:none;}
    .activity-form > input,.activity-form > select,.activity-form > textarea{width:100%;min-width:0;border:1px solid #cbd5e1;border-radius:9px;padding:10px 12px;font-family:inherit;font-size:13px;font-weight:500;color:var(--ink);background:#fff;}
    .activity-form > textarea{grid-column:1/-1;min-height:76px;resize:vertical;}
    .activity-form > label{display:flex;align-items:center;gap:7px;font-size:12px;color:#334155;}
    .activity-form .activity-actions{grid-column:1/-1;}
    .activity-form .activity-actions input{min-width:120px;flex:1;border:1px solid #cbd5e1;border-radius:9px;padding:10px 12px;font-family:inherit;font-size:13px;font-weight:500;}
    .activity-form .activity-actions.full{justify-content:flex-end;}
    .lesson-quiz-card{display:flex;flex-direction:row;align-items:center;}
    .quiz-edit-frame{width:100%;min-height:640px;border:1px solid var(--line);border-radius:14px;background:#fff;margin-top:10px;}
    .quiz-edit-frame[hidden]{display:none;}

    .student-card{border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);padding:14px 16px;display:flex;align-items:center;gap:14px;margin-bottom:14px;}
    .student-thumb{width:48px;height:48px;border-radius:10px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .student-info{flex:1;min-width:0;}
    .student-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .student-info .sub{font-size:11px;color:var(--muted);}
    .completion-review-help{margin:-8px 0 16px;color:var(--muted);font-size:12px;line-height:1.5;}
    .verification-actions{display:flex;flex-direction:column;align-items:stretch;gap:6px;flex-shrink:0;}
    .verification-actions small{font-size:10px;font-weight:700;color:#92400e;text-align:center;}
    .verification-actions form{display:flex;gap:6px;}
    .verification-actions .btn-pill{padding:8px 13px;font-size:12px;}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:8px 22px;border-radius:999px;border:1.5px solid var(--navy);background:#fff;color:var(--navy);flex-shrink:0;}

    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-top:28px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:20px;padding:14px 22px;}
    .panel-body{padding:18px 22px;}
    .avg-row{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
    .avg-row:last-child{margin-bottom:0;}
    .avg-row .avg-title{font-weight:800;font-size:14px;color:var(--navy);white-space:nowrap;}
    .avg-track{flex:1;height:7px;border-radius:999px;background:#e3e6f0;overflow:hidden;}
    .avg-fill{height:100%;background:var(--green);border-radius:999px;}
    .avg-pct{font-weight:800;font-size:14px;color:var(--navy);white-space:nowrap;}

    /* Course builder hierarchy: course > module > lesson > activity */
    body{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;color:#263238;}
    .main{padding-top:24px;}
    .course-banner{background:#fff;color:#263238;border:1px solid #e6e8ec;border-radius:10px;box-shadow:0 3px 10px rgba(16,24,40,.035);padding:18px 20px;margin-bottom:20px;}
    .banner-info .kicker{color:#667085;opacity:1;font-size:11px;letter-spacing:.06em;text-transform:uppercase;}
    .banner-info .faculty-page-title{color:#17202a!important;font-family:"Plus Jakarta Sans",Inter,sans-serif;font-size:25px!important;font-weight:700!important;line-height:1.25;}
    .banner-meta,.banner-meta.faculty-page-subtitle{color:#475467!important;opacity:1;font-size:13px!important;font-weight:600!important;gap:8px 14px;}
    .banner-meta span+span{border-left:1px solid #d0d5dd;padding-left:14px;}
    .banner-actions{flex-direction:row;align-items:center;gap:12px;}
    .banner-actions .row{align-items:center;gap:10px;}
    .banner-actions .btn-pill{background:transparent;border:0;border-radius:5px;color:#344054;padding:8px 10px;font-size:12px;font-weight:700;flex:none;transition:background .15s,color .15s;}
    .banner-actions a.btn-pill:hover{background:#f2f4f7;color:#344054;transform:none;}
    .banner-actions span.btn-pill{padding:0 10px;border-left:1px solid #d0d5dd;border-radius:0;color:#667085;font-weight:600;}
    .banner-actions a.btn-pill{border:1px solid #d0d5dd;}
    .banner-actions a.btn-pill:hover{border-color:#98a2b3;}
    .pending-count{min-width:18px;height:18px;background:#b42318;font-size:10px;}
    .section-head{margin:0 0 12px;}
    .section-title{margin:0;color:#17202a;font-size:19px;font-weight:700;}
    .section-caption{margin:4px 0 0;color:#667085;font-size:12px;line-height:1.45;}
    .readiness-panel{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px 18px;align-items:center;margin:0 0 18px;padding:15px 17px;border:1px solid #d7dbe2;border-radius:9px;background:#fff;}
    .readiness-heading h3{margin:0;color:#17202a;font-size:16px;font-weight:700;}
    .readiness-heading p{margin:4px 0 0;color:#667085;font-size:12px;line-height:1.45;}
    .readiness-list{grid-column:1/-1;display:flex;flex-wrap:wrap;align-items:center;gap:10px 12px;margin:0;padding:0;list-style:none;}
    .readiness-item{display:flex;flex:0 1 auto;align-items:flex-start;gap:8px;min-width:0;color:#475467;font-size:12px;line-height:1.45;}
    .readiness-mark{display:inline-flex;align-items:center;justify-content:center;flex:0 0 18px;width:18px;height:18px;border:1px solid #d0d5dd;border-radius:50%;color:#98a2b3;font-size:11px;font-weight:800;}
    .readiness-item.complete .readiness-mark{border-color:#86efac;background:#f0fdf4;color:#15803d;}
    .readiness-item strong{display:block;color:#344054;font-weight:700;}
    .readiness-item small{display:block;margin-top:2px;color:#667085;font-size:11px;}
    .readiness-action{display:flex;align-items:center;gap:10px;}
    .readiness-action .btn-primary-action:disabled{opacity:.48;cursor:not-allowed;}
    .readiness-state{font-size:12px;font-weight:700;color:#475467;}
    .readiness-state.pending{color:#b45309;}.readiness-state.published{color:#15803d;}.readiness-state.denied{color:#b42318;}
    .modules-section-head{padding:0 1px 10px;border-bottom:1px solid #e5e7eb;}
    .content-grid{grid-template-columns:minmax(0,1fr);gap:22px;}
    .add-module-card{display:none;gap:8px;padding:16px;margin:14px 0 18px;border:1px solid #d0d5dd;border-radius:9px;box-shadow:none;background:#fbfcfe;}
    .add-module-card .up-ckeditor-wrapper .ck.ck-editor__main>.ck-editor__editable{min-height:110px;}
    .form-field-label{color:#344054;font-size:11px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;}
    .form-error{color:#b42318;font-size:12px;font-weight:600;}
    .module-create-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:4px;}
    .btn-primary-action,.btn-secondary-action{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:36px;border-radius:6px;padding:8px 13px;font:700 12px Inter,"Segoe UI",sans-serif;cursor:pointer;}
    .btn-primary-action{border:1px solid var(--navy);background:var(--navy);color:#fff;}
    .btn-primary-action:hover{background:var(--navy-deep);}
    .btn-secondary-action{border:1px solid #cbd5e1;background:#fff;color:#344054;}
    .btn-secondary-action:hover{background:#f8fafc;}
    .module-card{position:relative;display:block;margin:0 0 15px;padding:17px 18px 14px 36px;border:1px solid #d7dbe2;border-left:3px solid var(--navy);border-radius:9px;background:#f7f8fa;box-shadow:0 4px 14px rgba(16,24,40,.04);}
    .module-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding-bottom:13px;border-bottom:1px solid #e5e7eb;}
    .module-heading-copy{min-width:0;}
    .eyebrow-label,.nested-section-label{display:block;color:#667085;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .module-head h4{margin:4px 0 5px;color:#17202a;font-family:"Plus Jakarta Sans",Inter,sans-serif;font-size:17px;font-weight:700;line-height:1.3;overflow-wrap:anywhere;}
    .module-description{max-width:850px;color:#667085;font-size:12px;line-height:1.5;}
    .module-stats{margin-top:8px;color:#667085;font-size:11px;font-weight:600;}
    .module-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .module-actions form,.lesson-actions form,.lesson-row form{margin:0;}
    .module-drag-handle{position:absolute;top:17px;left:10px;padding:0;border:0;background:transparent;color:#98a2b3;cursor:grab;}
    .module-edit-form{margin:14px 0;padding:14px;border:1px solid #e4e7ec;border-radius:8px;background:#fbfcfe;}
    .btn-text-action{border:0;background:transparent;color:#344054;padding:7px 8px;font:700 12px Inter,"Segoe UI",sans-serif;cursor:pointer;}
    .btn-text-action:hover{background:#f2f4f7;border-radius:5px;}
    .btn-icon-danger{display:grid;place-items:center;width:30px;height:30px;border:1px solid #fecdca;border-radius:6px;background:#fff;color:#b42318;cursor:pointer;}
    .btn-icon-danger:hover{background:#fef3f2;}
    .btn-icon-danger svg,.btn-icon-muted svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;}
    .lesson-sort-list{display:flex;flex-direction:column;gap:0;margin-top:3px;}
    .lesson-sort-item{padding:13px 0 12px;border-bottom:1px solid #eef0f3;}
    .lesson-row{display:grid;grid-template-columns:20px 28px minmax(0,1fr) auto;align-items:center;gap:10px;padding:0;border:0;border-radius:0;}
    .lesson-row .drag-handle{padding:0;border:0;background:transparent;color:#98a2b3;cursor:grab;}
    .lesson-number{color:#667085;font-size:11px;font-weight:700;font-variant-numeric:tabular-nums;}
    .lesson-info h4{margin:0 0 3px;color:#263238;font-family:Inter,"Segoe UI",sans-serif;font-size:14px;font-weight:700;line-height:1.35;}
    .lesson-info .sub{color:#667085;font-size:11px;font-weight:500;}
    .lesson-thumb{display:none;}
    .lesson-actions{display:flex;align-items:center;gap:5px;}
    .lesson-collapse-btn{display:inline-flex;align-items:center;gap:5px;}
    .lesson-collapse-btn svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;transition:transform .15s ease;}
    .lesson-collapse-btn[aria-expanded="false"] svg{transform:rotate(180deg);}
    .lesson-action-btn{border:0;border-radius:5px;background:transparent;color:#475467;padding:7px 8px;font:600 11px Inter,"Segoe UI",sans-serif;cursor:pointer;white-space:nowrap;}
    .lesson-action-btn:hover{background:#f2f4f7;color:#17202a;}
    .btn-icon-muted{display:grid;place-items:center;width:28px;height:28px;border:0;border-radius:5px;background:transparent;color:#667085;cursor:pointer;}
    .btn-icon-muted:hover{background:#fef3f2;color:#b42318;}
    .activity-panel{margin:9px 0 0 58px;padding:0 0 0 12px;border-left:2px solid #edf0f4;gap:7px;}
    .activity-panel>.activity-head{justify-content:flex-start;gap:10px;padding:0 0 3px;}
    .activity-panel>.activity-head strong{color:#667085;font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;}
    .activity-card{gap:6px;padding:7px 0;border:0;border-bottom:1px solid #f0f1f3;border-radius:0;background:transparent;box-shadow:none;}
    .activity-card:last-child{border-bottom:0;}
    .activity-card .activity-head{justify-content:space-between;gap:12px;}
    .activity-card .activity-head strong{color:#344054;font-size:12px;font-weight:700;}
    .activity-actions{gap:4px;}
    .btn-edit-inline,.btn-delete-activity{border:0;border-radius:5px;background:transparent;color:#475467;font-family:inherit;font-size:11px;font-weight:600;padding:6px 8px;}
    .btn-edit-inline:hover{background:#f2f4f7;}
    .btn-delete-activity{color:#b42318;}
    .btn-delete-activity:hover{background:#fef3f2;}
    .activity-review-link{padding:5px 0;border:0;border-radius:0;background:transparent;color:#475467;font-size:10px;}
    .activity-review-link:hover{background:transparent;color:var(--navy);text-decoration:underline;}
    .nested-assessment{margin:8px 0 0 58px;padding:10px 0 0 14px;border-left:2px solid #e5e7eb;border-top:1px solid #eef0f3;}
    .nested-section-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:5px;}
    .assessment-sort-list{display:flex;flex-direction:column;gap:0;}
    .assessment-sort-item{border-bottom:1px solid #f0f1f3;}
    .lesson-quiz-card{padding:7px 0;border:0;background:transparent;box-shadow:none;gap:10px;}
    .lesson-quiz-card .drag-handle{padding:0;border:0;background:transparent;color:#98a2b3;}
    .lesson-quiz-card .lesson-info h4{font-size:12px;}
    .quiz-none{flex:initial;color:#667085;font-size:11px;font-weight:500;}
    .lesson-form{margin:12px 0;padding:14px;border:1px solid #e4e7ec;border-radius:8px;background:#fbfcfe;}
    .lesson-form .up-ckeditor-wrapper .ck.ck-editor__main>.ck-editor__editable{min-height:110px;}
    .lesson-form .actions{justify-content:flex-end;gap:8px;}
    .lesson-form .actions .input-outline{margin-right:auto;max-width:170px;}
    .input-outline{border-radius:6px;padding:9px 11px;font-size:13px;font-weight:500;}
    .btn-save-lesson,.btn-cancel-outline{border-radius:6px;padding:8px 13px;font-size:12px;}
    .btn-save-lesson{background:var(--navy);}
    .btn-cancel-outline{padding:8px 13px;}
    .module-add-lesson{margin:12px 0 0 58px;}
    .module-add-lesson .btn-secondary-action{min-height:32px;padding:6px 11px;font-size:11px;}
    .summative-assessment{margin:15px 0 0;padding:12px 0 0;border-top:1px solid #e5e7eb;border-radius:0;box-shadow:none;}
    .summative-label{margin-bottom:7px;color:#667085;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .summative-content{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .quiz-info h4{color:#344054;font-size:13px;font-weight:700;}
    .quiz-info .sub{color:#667085;font-size:11px;}
    .quiz-none{margin-right:auto;}
    .empty-modules{padding:35px 20px;border:1px dashed #d0d5dd;border-radius:8px;box-shadow:none;}
    .empty-modules h4{color:#344054;font-size:14px;}
    .empty-modules p{font-size:12px;color:#667085;}
    .empty-state{padding:22px;border-radius:8px;font-size:12px;}

    /* The inline quiz editor is embedded in Manage Course. Keep it an isolated form. */
    body.inline-quiz{min-height:0;background:#fff;overflow-x:hidden;}
    .inline-quiz .auth-topbar,.inline-quiz .faculty-sidebar,.inline-quiz .back-link,.inline-quiz #back-to-top-btn{display:none!important;}
    .inline-quiz .layout.faculty-sidebar-layout{display:block;min-height:0;}
    .inline-quiz .layout.faculty-sidebar-layout>.main{width:100%;max-width:none;margin:0;padding:18px 20px 24px;}
    .inline-quiz .faculty-page-heading .quiz-head{margin:0 0 14px;font-size:19px!important;line-height:1.35;}
    .inline-quiz .quiz-card{margin-bottom:12px;padding:16px;border-radius:10px;box-shadow:0 2px 8px rgba(16,24,40,.04);}
    .inline-quiz .q-label{font-size:11px;}
    .inline-quiz .input-outline,.inline-quiz .textarea-outline{font-size:13px;}
    .inline-quiz .save-quiz-row{gap:8px;}
    @media(max-width:640px){.inline-quiz .layout.faculty-sidebar-layout>.main{padding:14px 12px 20px;}}
    .student-card{padding:11px 12px;border-radius:9px;box-shadow:none;gap:10px;}
    .student-thumb{width:38px;height:38px;}
    .status-pill{padding:5px 9px;border:1px solid #d0d5dd;border-radius:5px;color:#475467;font-size:10px;}
    .panel{margin-top:18px;border-radius:9px;box-shadow:none;}
    .panel-head{background:#f8fafc;color:#344054;font-size:13px;padding:11px 14px;}
    .panel-head{padding:10px 14px;font-size:13px;}
    .panel-body{padding:13px 14px;}
    .avg-row .avg-title,.avg-pct{color:#344054;font-size:11px;}
    @media(max-width:980px){
        .content-grid{grid-template-columns:1fr;}
        .banner-actions{width:auto;}
        .banner-actions .row{flex-wrap:wrap;}
        .module-card{padding-left:33px;}
        .module-drag-handle{left:8px;}
    }
    @media(max-width:640px){
        .main{padding:16px 12px 42px;}
        .course-banner{padding:16px;}
        .banner-actions{width:100%;justify-content:flex-start;}
        .banner-actions .row{width:100%;}
        .module-head{flex-direction:column;}
        .module-actions{align-self:flex-end;margin-top:-4px;}
        .lesson-row{grid-template-columns:18px 24px minmax(0,1fr);}
        .lesson-actions{grid-column:3;justify-content:flex-start;}
        .activity-panel,.nested-assessment,.module-add-lesson{margin-left:25px;}
        .module-card{padding:14px 12px 12px 29px;}
        .module-drag-handle{left:6px;top:14px;}
        .add-module-card{padding:12px;}
        .lesson-form .actions{align-items:stretch;}
        .lesson-form .actions .input-outline{flex:1 1 100%;max-width:none;}
    }

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .content-grid{grid-template-columns:1fr;}
        .banner-actions{width:100%;}
        .course-card{flex-direction:column;align-items:stretch;gap:16px;}
        .thumb{width:100%;height:140px;}
        .course-info{width:100%;min-width:0;}
        .course-meta{gap:14px;}
        .card-actions{flex-direction:row;width:100%;flex-wrap:wrap;}
        .btn-action{flex:1 1 140px;min-width:0;padding:10px 16px;}
    }

    /* â”€â”€ Phone â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width:640px){
        .main{padding:20px 14px 48px;}
        .course-card{padding:18px 16px;}
        .course-info h3{font-size:18px;line-height:1.3;}
        .course-info .desc{max-width:100%;}

        /* Stats read as a labelled row instead of floating centred columns */
        .course-meta{gap:12px 20px;}
        .meta-item{text-align:left;}

        /* Quiz builder: inputs stay inside the card */
        .quiz-card{padding:16px;}
        .question-input,
        .choice-row .input-outline,
        .tf-label{max-width:100%;}
        .q-head{gap:10px;}
        .q-head .sel-wrap{min-width:0;flex:1 1 100%;}
        .q-head .points{width:100%;}
        .q-head .type-label{margin-left:0;}
        .choice-row{gap:10px;flex-wrap:wrap;}
        .mark-correct{white-space:normal;}
        .save-quiz-row{justify-content:stretch;}
        .save-quiz-row > *{width:100%;}

        .course-banner{padding:18px 16px;}
        .quiz-row{gap:10px;}
        .btn-outline-navy{flex:1 1 auto;}
        .activity-panel{padding-left:0;padding-right:0;}
        .activity-card{padding:12px;}
        .activity-submission{grid-template-columns:minmax(0,1fr);}
        .submission-status{grid-row:2;justify-self:start;}
        .activity-form{grid-template-columns:1fr;}
        .activity-form > textarea,.activity-form .activity-actions{grid-column:1;}
    }

    /* Course structure: module card > lesson cards > combined lesson content card. */
    .lesson-sort-list{gap:10px;margin-top:12px;}
    .lesson-sort-item{padding:11px 12px 12px;border:1px solid #e1e5eb;border-radius:8px;background:#fff;}
    .lesson-row{padding:0 0 10px;}
    .activity-panel{margin:0 0 0 38px;padding:10px 12px;border:1px solid #e4e7ec;border-radius:7px;background:#fbfcfe;}
    .activity-panel>.activity-head{justify-content:space-between;padding:0 0 8px;border-bottom:1px solid #e5e7eb;}
    .activity-panel>.activity-head strong{font-size:12px;}
    .activity-panel>.activity-head .btn-primary-action{border:1px solid #b8860b;background:#e5b82e;color:#17202a;}
    .activity-panel>.activity-head .btn-primary-action:hover{border-color:#9a6f00;background:#dba617;color:#17202a;}
    .assessment-sort-list{margin:0;}
    .assessment-sort-item.lesson-assessment{margin-top:8px;padding-top:9px;border-top:1px solid #dfe3e8;}
    .lesson-assessment .assessment-label,.lesson-assessment-empty .assessment-label{margin-bottom:4px;color:#667085;font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .activity-card .activity-head{display:grid;grid-template-columns:16px minmax(0,1fr) auto;align-items:center;justify-content:initial;gap:10px;width:100%;}
    .activity-copy{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0;}
    .activity-copy .activity-kind,.activity-copy>strong{display:block;}
    .activity-copy .activity-kind{color:#667085;font-size:10px;font-weight:600;}
    .activity-copy>strong{color:#344054;font-size:12px;font-weight:700;line-height:1.35;overflow-wrap:anywhere;}
    .activity-card .activity-actions{justify-content:flex-end;}
    .lesson-quiz-row{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:12px;width:100%;}
    .lesson-quiz-row>div:first-child{display:flex;flex-direction:column;align-items:flex-start;gap:3px;min-width:0;}
    .lesson-quiz-row .activity-actions{justify-content:flex-end;}
    .lesson-quiz-row .lesson-quiz-title{color:#344054;font-size:15px;font-weight:700;line-height:1.35;}
    .activity-meta{color:#667085;font-size:11px;line-height:1.4;}
    .activity-form>.activity-form-context,.activity-form>.full,.activity-form>input[name="title"],.activity-form>select,.activity-form>textarea,.activity-form>label,.activity-form>.activity-actions{grid-column:1/-1;}
    .activity-form>.activity-form-context{display:flex;flex-direction:column;gap:3px;}
    .activity-form-context strong{color:#344054;font-size:13px;font-weight:700;}
    .activity-form-context span{color:#667085;font-size:12px;line-height:1.4;}
    .activity-form>.activity-actions:not(.full){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));}
    .lesson-assessment-empty{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px;padding-top:9px;border-top:1px solid #dfe3e8;}
    .lesson-assessment-empty .assessment-label{flex-basis:100%;margin:0;}
    .activity-empty{margin:7px 0 0;color:#667085;font-size:11px;}
    .summative-assessment{display:block;margin:12px 0 0;padding:11px 13px;border:1px solid #eadcae;border-left:3px solid #dba617;border-radius:8px;background:#fffdf7;}
    .summative-label{margin:0 0 6px;color:#8a6500;font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .summative-content{display:flex;align-items:center;justify-content:space-between;gap:10px;}
    .summative-content .quiz-none{margin:0 auto 0 0;}
    .summative-assessment .quiz-edit-frame{margin-top:10px;}
    .course-banner{background:var(--navy);border-color:var(--navy);color:#fff;}
    .banner-info .kicker{color:#f3cc54;}
    .banner-info .faculty-page-title{color:#fff!important;}
    .banner-meta,.banner-meta.faculty-page-subtitle{color:#fff!important;}
    .banner-meta span+span{border-color:rgba(255,255,255,.35);}
    .banner-actions a.btn-pill{border-color:rgba(255,255,255,.5);color:#fff;}
    .banner-actions a.btn-pill:hover{border-color:rgba(255,255,255,.8);background:rgba(255,255,255,.12);color:#fff;}
    .banner-actions span.btn-pill{border-color:rgba(255,255,255,.35);color:rgba(255,255,255,.88);}
    .banner-actions .course-status-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 11px;border:1px solid rgba(255,255,255,.28);border-radius:999px;background:rgba(255,255,255,.08);color:#fff;font-size:12px;font-weight:700;white-space:nowrap;}
    .course-status-indicator{width:8px;height:8px;flex:0 0 8px;border-radius:50%;background:#94a3b8;box-shadow:0 0 0 3px rgba(148,163,184,.17),0 0 9px rgba(148,163,184,.65);}
    .course-status-badge.status-pending{border-color:rgba(245,158,11,.48);background:rgba(245,158,11,.18);color:#fde68a;}
    .course-status-badge.status-published{border-color:rgba(34,197,94,.45);background:rgba(34,197,94,.17);color:#bbf7d0;}
    .course-status-badge.status-denied{border-color:rgba(239,68,68,.48);background:rgba(239,68,68,.18);color:#fecaca;}
    .course-status-badge.status-pending .course-status-indicator{background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.18),0 0 10px rgba(245,158,11,.85);}
    .course-status-badge.status-published .course-status-indicator{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.18),0 0 10px rgba(34,197,94,.8);}
    .course-status-badge.status-denied .course-status-indicator{background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.2),0 0 10px rgba(239,68,68,.85);}
    .manage-course-page{font-size:15px;width:100%;max-width:1360px;margin-inline:auto;}
    .manage-course-page .readiness-heading h3{font-size:17px;}
    .manage-course-page .readiness-item{font-size:13px;}
    .manage-course-page .banner-info .kicker{font-size:12px;}
    .manage-course-page .banner-info .faculty-page-title{font-size:27px!important;}
    .manage-course-page .banner-meta,.manage-course-page .banner-meta.faculty-page-subtitle{font-size:14px!important;}
    .manage-course-page .banner-actions .btn-pill,.manage-course-page .banner-actions span.btn-pill{font-size:13px;}
    .manage-course-page .course-status-badge{font-size:13px;}
    .manage-course-page .section-title{font-size:21px;}
    .manage-course-page .section-caption,.manage-course-page .completion-review-help{font-size:14px;}
    .manage-course-page .form-field-label{font-size:12px;}
    .manage-course-page .input-outline,.manage-course-page .textarea-outline,.manage-course-page .sel-wrap select{font-size:14px;}
    .manage-course-page .btn-save-lesson,.manage-course-page .btn-cancel-outline{font-size:13px;}
    .manage-course-page .duration-field{font-size:13px;}
    .manage-course-page .module-head h4{font-size:19px;}
    .manage-course-page .eyebrow-label,.manage-course-page .nested-section-label{font-size:11px;}
    .manage-course-page .module-description{font-size:14px;}
    .manage-course-page .module-stats{font-size:13px;}
    .manage-course-page .btn-text-action{font-size:13px;}
    .manage-course-page .lesson-number{font-size:12px;}
    .manage-course-page .lesson-info h4{font-size:16px;}
    .manage-course-page .lesson-info .sub{font-size:13px;}
    .manage-course-page .lesson-action-btn,.manage-course-page .btn-icon-muted{font-size:12px;}
    .manage-course-page .activity-panel>.activity-head strong{font-size:13px;}
    .manage-course-page .activity-panel>.activity-head .btn-primary-action{font-size:13px;}
    .manage-course-page .activity-copy .activity-kind{font-size:12px;}
    .manage-course-page .activity-copy>strong{font-size:14px;}
    .manage-course-page .activity-meta{font-size:13px;}
    .manage-course-page .lesson-assessment .assessment-label,.manage-course-page .lesson-assessment-empty .assessment-label{font-size:13px;}
    .manage-course-page .lesson-quiz-row .lesson-quiz-title{font-size:17px;}
    .manage-course-page .quiz-none,.manage-course-page .activity-empty{font-size:13px;}
    .manage-course-page .btn-primary-action,.manage-course-page .btn-secondary-action,.manage-course-page .btn-edit-inline,.manage-course-page .btn-delete-activity{font-size:13px;}
    .manage-course-page .activity-form>input,.manage-course-page .activity-form>select,.manage-course-page .activity-form>textarea,.manage-course-page .activity-form .activity-actions input{font-size:14px;}
    .manage-course-page .activity-form>label{font-size:13px;}
    .manage-course-page .summative-label{font-size:13px;}
    .manage-course-page .quiz-info h4{font-size:15px;}
    .manage-course-page .quiz-info .sub{font-size:13px;}
    .manage-course-page .empty-modules h4{font-size:16px;}
    .manage-course-page .empty-modules p{font-size:14px;}
    .manage-course-page .student-info h4{font-size:16px;}
    .manage-course-page .student-info .sub,.manage-course-page .verification-actions small{font-size:12px;}
    .manage-course-page .verification-actions .btn-pill{font-size:13px;}
    @media(max-width:640px){
        .lesson-sort-list{gap:8px;}
        .lesson-sort-item{padding:10px;}
        .lesson-row{grid-template-columns:18px 24px minmax(0,1fr);}
        .lesson-collapse-label{display:none;}
        .activity-panel{margin-left:0;padding:9px;}
        .activity-card .activity-head{grid-template-columns:16px minmax(0,1fr);align-items:start;}
        .activity-card .activity-actions{grid-column:2;justify-content:flex-start;}
        .lesson-quiz-row{grid-template-columns:minmax(0,1fr);}
        .lesson-quiz-row .activity-actions{justify-content:flex-start;}
        .lesson-quiz-row{align-items:flex-start;}
        .summative-content{align-items:flex-start;flex-wrap:wrap;}
        .summative-content .quiz-info,.summative-content .quiz-none{flex:1 1 100%;}
    }
</style>
</head>
<body class="{{ request()->boolean('inline') ? 'inline-quiz' : '' }}">

@include('components.authenticated-topbar')

<div class="layout faculty-sidebar-layout">

    {{-- Sidebar â€” Dashboard & My Courses connected, everything else placeholder --}}
    @include('components.faculty-sidebar')

    {{-- Main content --}}
    <main class="main {{ $mode === 'manage' ? 'manage-course-page' : '' }}">

    @if ($mode === 'manage')
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'My Courses', 'url' => route('faculty.courses')],
            ['label' => $course->title ?? 'Course Management'],
        ]])

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• MANAGE MODE â€” "Managing Course" screen â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        <section class="course-banner">
            <div class="banner-info faculty-page-heading faculty-page-heading--inverse">
                <div class="kicker">Managing Course</div>
                <h2 class="faculty-page-title">{{ $course->title ?? 'Course Title' }}</h2>
                <div class="banner-meta faculty-page-subtitle">
                    <span>{{ $course->students_count ?? 0 }} Students</span>
                    <span>{{ $course->modules_count ?? 0 }} {{ ($course->modules_count ?? 0) == 1 ? 'Module' : 'Modules' }}</span>
                    <span>{{ $course->level ?? '' }}</span>
                </div>
            </div>
            <div class="banner-actions">
                <div class="row">
                    <span class="course-status-badge status-{{ Illuminate\Support\Str::slug($course->status ?? 'Draft') }}" aria-label="Course status: {{ $course->status ?? 'Draft' }}"><span class="course-status-indicator" aria-hidden="true"></span>{{ $course->status ?? 'Draft' }}</span>
                    <a href="{{ route('faculty.activities.reviews', $course->id) }}" class="btn-pill">Activity Reviews @if(($pendingActivityReviews ?? 0) > 0)<span class="pending-count">{{ $pendingActivityReviews }}</span>@endif</a>
                    <a href="{{ route('faculty.courses.edit', $course->id) }}" class="btn-pill">Edit Course</a>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="manage-flash" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->has('submission'))
            <div class="manage-flash error" role="alert">{{ $errors->first('submission') }}</div>
        @endif

        <section class="readiness-panel" aria-labelledby="readiness-title">
            <div class="readiness-heading">
                <h3 id="readiness-title">Course readiness</h3>
                <p>Complete these checks before submitting. Draft courses remain private to you.</p>
            </div>
            <div class="readiness-action">
                @if($course->approval_status === 'draft')
                    <form method="POST" action="{{ route('faculty.courses.submit-for-approval', $course->id) }}">
                        @csrf
                        <button class="btn-primary-action" type="submit" @disabled(!$canSubmitForApproval)>Submit for approval</button>
                    </form>
                @elseif($course->approval_status === 'pending')
                    <span class="readiness-state pending">Awaiting admin review</span>
                @elseif($course->approval_status === 'denied')
                    <span class="readiness-state denied">Changes requested</span>
                @else
                    <span class="readiness-state published">Approved</span>
                @endif
            </div>
            <ul class="readiness-list">
                @foreach($readinessChecklist as $check)
                    <li class="readiness-item {{ $check['complete'] ? 'complete' : '' }}">
                        <span class="readiness-mark" aria-hidden="true">{{ $check['complete'] ? '✓' : '!' }}</span>
                        <span><strong>{{ $check['label'] }}</strong>@unless($check['complete'])<small>{{ $check['detail'] }}</small>@endunless</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <div class="content-grid">

            {{-- â”€â”€ Left column: Course Modules â”€â”€ --}}
            <section class="modules-section">
                <div class="section-head modules-section-head">
                    <div>
                        <h3 class="section-title">Course Modules</h3>
                        <p class="section-caption">Organize the lessons, activities, and assessments in this course.</p>
                    </div>
                    <button class="btn-primary-action" type="button" id="toggle-module-create" aria-expanded="{{ $errors->has('module_title') || $errors->has('module_description') ? 'true' : 'false' }}" aria-controls="module-create-form" onclick="toggleModuleCreate()">+ Add Module</button>
                </div>

                {{-- Keep module creation in this page, but reveal it only when requested. --}}
                <form class="add-module-card" id="module-create-form" style="display:{{ $errors->has('module_title') || $errors->has('module_description') ? 'flex' : 'none' }};" method="POST" action="{{ route('faculty.module.store', $course->id) }}">
                    @csrf
                    <label class="form-field-label" for="new-module-title">Module title</label>
                    <input class="input-outline" id="new-module-title" type="text" name="module_title" placeholder="e.g. Web Page Foundations" value="{{ old('module_title') }}" required>
                    <label class="form-field-label" for="module-description-new">Description</label>
                    @include('components.rich-text-editor', ['name' => 'module_description', 'id' => 'module-description-new', 'value' => old('module_description'), 'placeholder' => 'Describe what this module covers...'])
                    @if($errors->has('module_title') || $errors->has('module_description'))
                        <div class="form-error" role="alert">{{ $errors->first('module_title') ?: $errors->first('module_description') }}</div>
                    @endif
                    <div class="module-create-actions">
                        <button class="btn-secondary-action" type="button" onclick="toggleModuleCreate(false)">Cancel</button>
                        <button class="btn-primary-action" type="submit">Create Module</button>
                    </div>
                </form>

                <div class="sortable-list module-sort-list" data-order-kind="modules" data-order-url="{{ route('faculty.modules.reorder', $course->id) }}">
                @forelse ($modules as $module)
                    <div class="module-card sortable-item" data-order-item data-order-id="{{ $module->idx }}">
                        @php
                            $moduleLessons = $module->lessons ?? collect();
                            $moduleLessonCount = $moduleLessons->count();
                            $moduleActivityCount = $moduleLessons->sum(fn ($lesson) => collect($lesson->activities ?? [])->count());
                            $moduleDurationMinutes = $moduleLessons->sum(fn ($lesson) => (int) ($lesson->duration_raw ?? 0));
                            $moduleDurationLabel = $moduleDurationMinutes >= 60
                                ? intdiv($moduleDurationMinutes, 60).'h '.($moduleDurationMinutes % 60).'m'
                                : $moduleDurationMinutes.' min';
                        @endphp
                        <header class="module-head">
                            <div class="module-heading-copy">
                                <span class="eyebrow-label">Module {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h4>{{ $module->title }}</h4>
                                @if($module->subtitle ?? null)
                                    <div class="module-description">{{ $module->subtitle }}</div>
                                @endif
                                <div class="module-stats">
                                    {{ $moduleLessonCount }} {{ Illuminate\Support\Str::plural('lesson', $moduleLessonCount) }}
                                    · {{ $moduleDurationLabel }}
                                    · {{ $moduleActivityCount }} {{ Illuminate\Support\Str::plural('activity', $moduleActivityCount) }}
                                </div>
                            </div>
                            <div class="module-actions">
                                <button class="btn-text-action" type="button" onclick="toggleModuleEdit({{ $module->idx }})">Edit Module</button>
                                <form method="POST" action="{{ route('faculty.module.delete', [$course->id, $module->key]) }}" onsubmit="return confirm('Delete this module and all of its lessons, activities, and assessments? This cannot be undone.');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Delete module" aria-label="Delete module">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg>
                                    </button>
                                </form>
                            </div>
                        </header>
                        {{-- Keep the old sortable handle available without adding a colored icon tile. --}}
                        <button class="drag-handle module-drag-handle" type="button" draggable="true" title="Drag to reorder module" aria-label="Drag to reorder module">⋮⋮</button>

                        {{-- ── Inline module editor — title + rich-text description ── --}}
                        <form class="lesson-form module-edit-form" id="module-edit-{{ $module->idx }}"
                              style="display:{{ $errors->has('module_description') && (int) session('open_module_edit') === (int) $module->idx ? 'flex' : 'none' }};"
                              method="POST"
                              action="{{ route('faculty.module.update', [$course->id, $module->idx]) }}">
                            @csrf

                            <div class="row2">
                                <input class="input-outline" type="text" name="module_title"
                                       placeholder="Module title *"
                                       value="{{ old('module_title', $module->title) }}" required>
                            </div>

                            @include('components.rich-text-editor', [
                                'name' => 'module_description',
                                'id' => 'module-description-edit-'.$module->idx,
                                'value' => old('module_description', $module->description ?? ''),
                                'placeholder' => 'Describe what this module covers...'
                            ])

                            @if ($errors->has('module_description') && (int) session('open_module_edit') === (int) $module->idx)
                                <div style="background:#fdecea;border:1px solid #f2b8b5;color:#a93226;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;">
                                    {{ $errors->first('module_description') }}
                                </div>
                            @endif

                            <div class="actions">
                                <button class="btn-save-lesson" type="submit">Save Module</button>
                                <button class="btn-cancel-outline" type="button"
                                        onclick="toggleModuleEdit({{ $module->idx }})">Cancel</button>
                            </div>
                        </form>

                        @php $showLessonForm = ($addLessonIndex ?? null) === $module->idx
                            || (int) session('open_lesson_form') === (int) $module->idx; @endphp
                        <div class="sortable-list lesson-sort-list" data-order-kind="lessons" data-parent-id="{{ $module->idx }}" data-order-url="{{ route('faculty.lessons.reorder', [$course->id, $module->idx]) }}">
                        @forelse ($module->lessons ?? [] as $lesson)
                            <div class="lesson-sort-item sortable-item" data-order-item data-order-id="{{ $lesson->id }}">
                            <div class="lesson-row">
                                <button class="drag-handle" type="button" draggable="true" title="Drag to reorder lesson" aria-label="Drag to reorder lesson">⋮⋮</button>
                                <span class="lesson-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="lesson-info">
                                    <h4>{{ $lesson->title }}</h4>
                                    <div class="sub">{{ 'Text'.($lesson->duration_raw ? ' · '.$lesson->duration_raw.' min' : '') }}</div>
                                </div>
                                <div class="lesson-actions">
                                    <button type="button" class="lesson-action-btn" onclick="toggleLessonEdit({{ $lesson->id }})" title="Edit lesson">Edit</button>
                                    <form method="POST" action="{{ route('faculty.lesson.delete', [$course->id, $module->idx, $lesson->key ?? 'seed-'.$loop->index]) }}" onsubmit="return confirm('Delete this lesson and its activities and quizzes? This cannot be undone.');">
                                        @csrf
                                        <button class="btn-icon-muted" type="submit" title="Delete lesson" aria-label="Delete lesson">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg>
                                        </button>
                                    </form>
                                    <button type="button" class="lesson-action-btn lesson-collapse-btn" aria-expanded="true" aria-controls="lesson-content-{{ $lesson->id }}" aria-label="Collapse lesson content" title="Collapse lesson" onclick="toggleLessonCollapse({{ $lesson->id }}, this)">
                                        <span class="lesson-collapse-label">Collapse</span>
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="lesson-collapsible-content" id="lesson-content-{{ $lesson->id }}">
                            <section class="activity-panel" aria-label="Lesson activities">
                                <div class="activity-head">
                                    <strong>Activities</strong>
                                    <button class="btn-primary-action btn-compact" type="button" onclick="toggleActivityForm({{ $lesson->id }}, true)">+ Add Activity</button>
                                </div>
                                @php
                                    $lessonActivities = collect($lesson->activities ?? [])->sortBy('sort_order')->values();
                                @endphp
                                <div class="sortable-list assessment-sort-list" data-order-kind="assessments" data-parent-id="{{ $lesson->id }}" data-order-url="{{ route('faculty.lesson-assessments.reorder', [$course->id, $lesson->id]) }}">
                                @forelse($lessonActivities as $activity)
                                    <div class="assessment-sort-item activity-sort-item sortable-item" data-order-item data-order-type="activity" data-order-id="{{ $activity->id }}">
                                    <article class="activity-card">
                                        <div class="activity-head">
                                            <button class="drag-handle activity-drag-handle" type="button" draggable="true" title="Drag to reorder activity" aria-label="Drag to reorder activity">⋮⋮</button>
                                            <div class="activity-copy"><span class="activity-kind">{{ Illuminate\Support\Str::headline($activity->activity_type) }}</span><strong>{{ $activity->title }}</strong><div class="activity-meta">{{ $activity->is_required ? 'Required' : 'Optional' }}@if($activity->activity_type === 'assignment') · {{ $activity->max_points }} points · Pass {{ $activity->passing_percent ?? 70 }}%@endif</div></div>
                                            <div class="activity-actions"><button class="btn-edit-inline" type="button" onclick="toggleActivityEdit({{ $activity->id }})">Edit</button>
                                                <form method="POST" action="{{ route('faculty.lesson-activity.destroy', [$course->id, $activity->id]) }}" onsubmit="return confirm('Remove this activity? Students will no longer see it. Existing submissions will be retained.');">@csrf<button class="btn-delete-activity" type="submit">Remove</button></form>
                                            </div>
                                        </div>
                                        <form class="activity-form activity-edit-form" id="activity-edit-{{ $activity->id }}" @if((int) session('open_activity_edit') !== (int) $activity->id) hidden @endif method="POST" action="{{ route('faculty.lesson-activity.update', [$course->id, $activity->id]) }}">@csrf
                                            @if((int) session('open_activity_edit') === (int) $activity->id && $errors->any())<div class="full" role="alert" style="color:#b91c1c;">{{ $errors->first() }}</div>@endif
                                            <input name="title" maxlength="255" value="{{ old('title', $activity->title) }}" placeholder="Activity title" required>
                                            <select name="activity_type">@foreach(['practice'=>'Practice','reflection'=>'Reflection','assignment'=>'Assignment (faculty reviewed)'] as $type=>$label)<option value="{{ $type }}" @selected(old('activity_type', $activity->activity_type) === $type)>{{ $label }}</option>@endforeach</select>
                                            <textarea name="instructions" maxlength="10000" placeholder="Instructions and criteria">{{ old('instructions', $activity->instructions) }}</textarea>
                                            <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $activity->is_required))> Required for lesson completion</label>
                                            <div class="activity-actions"><input type="number" name="max_points" min="1" max="10000" placeholder="Points (assignment)" value="{{ old('max_points', $activity->max_points) }}"><input type="number" name="passing_percent" min="1" max="100" placeholder="Pass %" value="{{ old('passing_percent', $activity->passing_percent) }}"></div>
                                            <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityEdit({{ $activity->id }})">Cancel</button></div>
                                        </form>
                                    </article>
                                    </div>
                                @empty
                                    <p class="activity-empty">No activities added to this lesson.</p>
                                @endforelse
                                @if($lesson->quiz ?? null)
                                    <section class="assessment-sort-item lesson-assessment sortable-item" data-order-item data-order-type="quiz" data-order-id="{{ $lesson->quiz->id }}" aria-label="Lesson assessment">
                                        <div class="assessment-label">Assessment</div>
                                        <div class="lesson-quiz-row">
                                            <div><strong class="lesson-quiz-title">{{ $lesson->quiz->title }}</strong><div class="activity-meta">{{ $lesson->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $lesson->quiz->questions_count) }} · Pass {{ $lesson->quiz->passing_score }}%</div></div>
                                            <div class="activity-actions">
                                                <button class="btn-edit-inline" type="button" onclick="toggleLessonQuizEdit({{ $lesson->id }})">Edit Quiz</button>
                                                <button class="drag-handle activity-drag-handle" type="button" draggable="true" title="Drag to reorder lesson assessment" aria-label="Drag to reorder lesson assessment">⋮⋮</button>
                                            </div>
                                        </div>
                                        <iframe class="quiz-edit-frame" id="lesson-quiz-edit-{{ $lesson->id }}" title="Edit lesson quiz" loading="lazy" hidden src="{{ route('faculty.lesson-quiz.create', [$course->id, $lesson->id, 'inline' => 1]) }}"></iframe>
                                    </section>
                                @endif
                                </div>
                                @unless($lesson->quiz ?? null)
                                    <div class="lesson-assessment-empty">
                                        <div class="assessment-label">Assessment</div>
                                        <span class="quiz-none">No lesson quiz created yet.</span>
                                        <a class="btn-secondary-action btn-compact" href="{{ route('faculty.lesson-quiz.create', [$course->id, $lesson->id]) }}">+ Add Lesson Quiz</a>
                                    </div>
                                @endunless
                                <form class="activity-form" id="activity-add-{{ $lesson->id }}" @if((int) session('open_activity_form') !== (int) $lesson->id) hidden @endif method="POST" action="{{ route('faculty.lesson-activity.store', [$course->id, $lesson->id]) }}">@csrf
                                    @if((int) session('open_activity_form') === (int) $lesson->id && $errors->any())<div class="full" role="alert" style="color:#b91c1c;">{{ $errors->first() }}</div>@endif
                                    <div class="activity-form-context"><strong>Add activity to this lesson</strong><span>{{ $lesson->title }}</span></div>
                                    <input name="title" maxlength="255" value="{{ old('title') }}" placeholder="Activity title" required>
                                    <select name="activity_type"><option value="practice" @selected(old('activity_type') === 'practice')>Practice</option><option value="reflection" @selected(old('activity_type') === 'reflection')>Reflection</option><option value="assignment" @selected(old('activity_type') === 'assignment')>Assignment (faculty reviewed)</option></select>
                                    <textarea name="instructions" maxlength="10000" placeholder="Instructions and criteria">{{ old('instructions') }}</textarea>
                                    <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', true))> Required for lesson completion</label>
                                    <div class="activity-actions"><input type="number" name="max_points" min="1" max="10000" value="{{ old('max_points') }}" placeholder="Points (assignment)"><input type="number" name="passing_percent" min="1" max="100" value="{{ old('passing_percent') }}" placeholder="Pass %"></div>
                                    <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityForm({{ $lesson->id }}, false)">Cancel</button></div>
                                </form>
                            </section>

                            {{-- â”€â”€ Inline lesson editor â€” same layout as the
                                 Add Lesson form above â”€â”€ --}}
                            @if($lesson->id ?? null)
                                <form class="lesson-form lesson-edit-form" id="lesson-edit-{{ $lesson->id }}"
                                      style="display:{{ session('open_lesson_edit') == $lesson->id ? 'flex' : 'none' }};"
                                      method="POST"
                                      action="{{ route('faculty.lesson.update', [$course->id, $module->idx, $lesson->id]) }}">
                                    @csrf

                                    <div class="row2">
                                        <input class="input-outline" type="text" name="lesson_title"
                                               placeholder="Lesson title *"
                                               value="{{ old('lesson_title', $lesson->title) }}" required>
                                    </div>

                                    @include('components.rich-text-editor', [
                                        'name' => 'lesson_content',
                                        'id' => 'lesson-content-edit-'.$lesson->id,
                                        'value' => old('lesson_content', $lesson->content),
                                        'placeholder' => 'Write the lesson content...'
                                    ])

                                    @if ($errors->has('lesson_content') && (int) session('open_lesson_edit') === (int) $lesson->id)
                                        <div style="background:#fdecea;border:1px solid #f2b8b5;color:#a93226;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;">
                                            {{ $errors->first('lesson_content') }}
                                        </div>
                                    @endif

                                    <div class="actions">
                                        <input class="input-outline" type="number" name="duration"
                                               placeholder="Duration ( Min )" min="0"
                                               value="{{ old('duration', $lesson->duration_raw ?: '') }}">
                                        <button class="btn-save-lesson" type="submit">Save Lesson</button>
                                        <button class="btn-cancel-outline" type="button"
                                                onclick="toggleLessonEdit({{ $lesson->id }})">Cancel</button>
                                    </div>
                                </form>
                            @endif
                            </div>{{-- /lesson-collapsible-content --}}

                            </div>{{-- /lesson-sort-item --}}

                        @empty
                            <div class="lessons-empty" id="lessons-empty-{{ $module->idx }}">
                                <span>This module has no lessons yet.</span>
                            </div>
                        @endforelse
                        </div>{{-- /lesson-sort-list --}}

                        <form class="lesson-form" id="lesson-form-{{ $module->idx }}"
                              style="display:{{ $showLessonForm ? 'flex' : 'none' }};"
                              method="POST" action="{{ route('faculty.lesson.store', [$course->id, $module->idx]) }}">
                            @csrf
                            <label class="form-field-label" for="lesson-title-add-{{ $module->idx }}">Lesson title</label>
                            <input class="input-outline" id="lesson-title-add-{{ $module->idx }}" type="text" name="lesson_title" placeholder="e.g. HTML, CSS, and JavaScript" value="{{ old('lesson_title') }}" required>
                            <label class="form-field-label" for="lesson-content-add-{{ $module->idx }}">Description</label>
                            @include('components.rich-text-editor', [
                                'name' => 'lesson_content',
                                'id' => 'lesson-content-add-'.$module->idx,
                                'value' => old('lesson_content'),
                                'placeholder' => 'Write the lesson content...'
                            ])
                            @if ($errors->has('lesson_content') && (int) session('open_lesson_form') === (int) $module->idx)
                                <div class="form-error" role="alert">{{ $errors->first('lesson_content') }}</div>
                            @endif
                            <div class="actions">
                                <label class="duration-field">Duration
                                    <input class="input-outline" type="number" name="duration" placeholder="Minutes" min="0" value="{{ old('duration') }}">
                                    <span>minutes</span>
                                </label>
                                <button class="btn-secondary-action" type="button" onclick="toggleLessonForm({{ $module->idx }}, false)">Cancel</button>
                                <button class="btn-primary-action" type="submit">Save Lesson</button>
                            </div>
                        </form>
                        <div class="module-add-lesson">
                            <button class="btn-primary-action" type="button" onclick="toggleLessonForm({{ $module->idx }}, true)">+ Add Lesson</button>
                        </div>

                        {{-- The module quiz is the summative assessment for all lessons in this module. --}}
                        <section class="quiz-row summative-assessment" id="quiz-row-{{ $module->idx }}" aria-label="Summative assessment">
                            <div class="summative-label">Summative Assessment</div>
                            <div class="summative-content">
                            @if($module->quiz ?? null)
                                <div class="quiz-info">
                                    <h4>{{ $module->quiz->title }}</h4>
                                    <div class="sub">{{ $module->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $module->quiz->questions_count) }} · Pass {{ $module->quiz->passing_score }}%</div>
                                </div>
                                <button class="btn-edit-inline" type="button" onclick="toggleQuizEdit({{ $module->idx }})">Edit Quiz</button>
                                <form method="POST"
                                      action="{{ route('faculty.quiz.destroy', [$course->id, $module->idx]) }}"
                                      style="display:inline-block;margin-left:8px;"
                                      onsubmit="return confirm('Delete this summative quiz and its student attempts? This cannot be undone.');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Delete summative quiz" aria-label="Delete summative quiz"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button>
                                </form>
                            @else
                                <span class="quiz-none">No summative assessment created yet.</span>
                                <a class="btn-secondary-action" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">+ Add Summative Quiz</a>
                            @endif
                            </div>
                            @if($module->quiz ?? null)
                                <iframe class="quiz-edit-frame" id="quiz-edit-{{ $module->idx }}" title="Edit summative quiz" loading="lazy" hidden src="{{ route('faculty.quiz.create', [$course->id, $module->idx, 'inline' => 1]) }}"></iframe>
                            @endif
                        </section>
                    </div>
                @empty
                    <div class="empty-modules">
                        <h4>No Modules yet</h4>
                        <p>Add your first Module above</p>
                    </div>
                @endforelse
                </div>{{-- /module-sort-list --}}
                <div id="reorder-status" aria-live="polite"></div>
            </section>

            {{-- Course quiz averages remain available below the authoring workspace. --}}
            @if($quizAverages->isNotEmpty())
            <aside>
                <div class="panel">
                    <div class="panel-head">Quiz Average</div>
                    <div class="panel-body">
                        @forelse ($quizAverages as $avg)
                            <div class="avg-row">
                                <span class="avg-title">{{ $avg->title }}</span>
                                <div class="avg-track">
                                    <div class="avg-fill" style="width:{{ $avg->percent }}%"></div>
                                </div>
                                <span class="avg-pct">{{ $avg->percent }}%</span>
                            </div>
                        @empty
                            <div class="empty-state" style="border:none;padding:0;text-align:left;">No quiz results yet.</div>
                        @endforelse
                    </div>
                </div>
            </aside>
            @endif

        </div>

    @elseif ($mode === 'quiz')

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• QUIZ BUILDER MODE â€” "Add Quiz : <module>" â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        {{-- âœ… Back to the Managing Course screen --}}
        <a class="back-link" href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">â€¹ Back to Modules</a>
        <div class="faculty-page-heading"><h2 class="quiz-head faculty-page-title">{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz : {{ $moduleTitle }}</h2></div>
        @if(session('success'))<div class="quiz-card" role="status" style="padding:10px 14px;background:#ecfdf3;color:#166534;">{{ session('success') }}</div>@endif

        {{-- âœ… Real form â€” Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form method="POST" action="{{ !empty($lessonId) ? route('faculty.lesson-quiz.store', [$course->id, $lessonId]) : route('faculty.quiz.store', [$course->id, $moduleIdx]) }}">
            @csrf
            @if(request()->boolean('inline'))<input type="hidden" name="inline" value="1">@endif

            {{-- Quiz settings --}}
            <div class="quiz-card">
                <div class="grid3">
                    <div>
                        <label class="q-label">Quiz Title <span class="req">*</span></label>
                        <input class="input-outline" style="width:100%;" type="text" name="quiz_title" placeholder="MySQL Fundamentals Quiz" value="{{ $quiz->title ?? '' }}" required>
                    </div>
                    <div>
                        <label class="q-label">No. of Items <span class="req">*</span></label>
                        <input class="input-outline" style="width:100%;" type="number" name="items" placeholder="1" min="1" value="{{ $quiz->items ?? '' }}">
                    </div>
                    <div>
                        <label class="q-label">Passing Score (%)</label>
                        <input class="input-outline" style="width:100%;" type="number" name="passing_score" placeholder="100" min="0" max="100" value="{{ $quiz->passing_score ?? '' }}">
                    </div>
                </div>
                <div class="grid2">
                    <div>
                        <label class="q-label">Attempts allowed</label>
                        <input class="input-outline" style="width:100%;" type="text" name="attempts" placeholder="1 Attempt" value="{{ $quiz->attempts ?? '' }}">
                    </div>
                    <div>
                        <label class="q-label">Time Limit (min)</label>
                        <input class="input-outline" style="width:100%;" type="number" name="time_limit" placeholder="30" min="0" value="{{ $quiz->time_limit ?? '' }}">
                    </div>
                </div>
                <div>
                    <label class="q-label">Instructions ( Optional )</label>
                    <textarea class="textarea-outline" name="instructions" placeholder="Read each question carefully before answering. You may only submit once.">{{ $quiz->instructions ?? '' }}</textarea>
                </div>
            </div>

            {{-- Questions â€” for a NEW quiz the containers are generated after
                 clicking "Create Quiz"; the count follows "No. of Items" --}}
            @php
                $questions = $quiz->questions ?? [];
                $isNewQuiz = empty($questions);
            @endphp

            @if ($isNewQuiz)
            <div class="quiz-card" id="create-quiz-intro" style="text-align:center;">
                <p style="margin:0 0 16px;color:var(--muted);font-size:14px;">
                    Set the quiz details above â€” especially <strong>No. of Items</strong> â€” then click
                    <strong>Create Quiz</strong>. One question container will be created per item.
                </p>
                <button class="btn-save-lesson" type="button" onclick="createQuizQuestions()">Create Quiz</button>
            </div>
            @endif

            <div id="questions-area" style="display:{{ $isNewQuiz ? 'none' : 'block' }};">
            <div id="questions">
                @foreach ($questions as $q => $question)
                    <div class="quiz-card question-card">
                        <div class="q-head">
                            <h3>Question {{ $q + 1 }}</h3>
                            <span class="type-label">Type</span>
                            <div class="sel-wrap">
                                @php $qType = $question['type'] ?? 'Multiple Choice'; @endphp
                                <select name="questions[{{ $q }}][type]" onchange="questionTypeChanged(this)">
                                    <option value="Multiple Choice" {{ $qType === 'Multiple Choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="True or False" {{ $qType === 'True or False' ? 'selected' : '' }}>True or False</option>
                                    <option value="Identification" {{ $qType === 'Identification' ? 'selected' : '' }}>Identification</option>
                                </select>
                            </div>
                            <input class="input-outline points" type="number" name="questions[{{ $q }}][points]" placeholder="Points" min="0" value="{{ $question['points'] ?? '' }}">
                            {{-- âœ… Removes THIS question card --}}
                            <button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">&times;</button>
                        </div>

                        <div style="margin-bottom:18px;">
                            <input class="input-outline question-input" style="width:100%;" type="text" name="questions[{{ $q }}][text]" placeholder="What does SQL stand for?" value="{{ $question['text'] ?? '' }}">
                        </div>

                        @php
                            $savedChoices = $question['choices'] ?? [];
                            $choiceCount  = max(3, count($savedChoices));
                            $correct      = $question['correct'] ?? null;
                        @endphp

                        {{-- âœ… The section below changes with the question Type --}}

                        {{-- Multiple Choice: A/B/C choices + Add a Choice --}}
                        <div class="q-mc" style="display:{{ $qType === 'Multiple Choice' ? 'block' : 'none' }};">
                            <div class="choices" id="choices-{{ $q }}">
                                @for ($i = 0; $i < $choiceCount; $i++)
                                    @php $letter = chr(65 + $i); @endphp
                                    <div class="choice-row">
                                        <span class="choice-letter">{{ $letter }}.</span>
                                        <input class="input-outline" type="text" name="questions[{{ $q }}][choices][]" placeholder="Choice {{ $letter }}" value="{{ $qType === 'Multiple Choice' ? ($savedChoices[$i] ?? '') : '' }}">
                                        <label class="mark-correct">
                                            Mark as Correct
                                            <input type="radio" name="questions[{{ $q }}][correct]" value="{{ $i }}" {{ $qType === 'Multiple Choice' && $correct !== null && (int) $correct === $i ? 'checked' : '' }}>
                                            <span class="radio-dot"></span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            {{-- âœ… Adds another choice row (D, E, F ...) --}}
                            <button class="btn-add-choice" type="button" onclick="addChoice({{ $q }})">+ Add a Choice</button>
                        </div>

                        {{-- True or False: fixed True / False rows --}}
                        <div class="q-tf" style="display:{{ $qType === 'True or False' ? 'block' : 'none' }};">
                            @foreach (['True', 'False'] as $ti => $tfLabel)
                                <div class="choice-row">
                                    <span class="tf-label">{{ $tfLabel }}</span>
                                    <label class="mark-correct">
                                        Mark as Correct
                                        <input type="radio" name="questions[{{ $q }}][tf_correct]" value="{{ $ti }}" {{ $qType === 'True or False' && $correct !== null && (int) $correct === $ti ? 'checked' : '' }}>
                                        <span class="radio-dot"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Identification: a single correct-answer field --}}
                        <div class="q-id" style="display:{{ $qType === 'Identification' ? 'block' : 'none' }};">
                            <label class="q-label">Correct Answer</label>
                            <input class="input-outline" style="width:100%;max-width:420px;" type="text" name="questions[{{ $q }}][answer]" placeholder="Type the correct answer" value="{{ $question['answer'] ?? '' }}">
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- âœ… Adds another fresh Question card --}}
            <button class="btn-add-choice" type="button" style="margin-bottom:26px;" onclick="addQuestion()">+ Add Question</button>
            </div>{{-- /questions-area --}}

            <div class="save-quiz-row" id="save-quiz-row" style="display:{{ $isNewQuiz ? 'none' : 'flex' }};">
                <button class="btn-save-lesson" type="submit">Save Quiz</button>
            </div>
        </form>

    @else

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• LIST MODE â€” My Courses â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        <div class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">My Courses</h2>
                <p class="faculty-page-subtitle">{{ $courses->count() }} Total {{ $courses->count() == 1 ? 'course' : 'courses' }} created</p>
            </div>
            {{-- âœ… Connected â€” goes to Faculty â€º Create Courses only --}}
            <a href="{{ route('faculty.create') }}" class="btn-outline" aria-label="Create a course"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Create Courses</span></a>
        </div>

        @forelse ($courses as $course)
            <div class="course-card">
                <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    @php
                        $descPreview = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) ($course->description ?? ''))))), 130);
                    @endphp
                    @if($descPreview !== '')
                        <div class="desc">{{ $descPreview }}</div>
                    @endif
                    <div class="course-meta">
                        <span class="status-chip status-{{ Illuminate\Support\Str::slug($course->status ?? 'Draft') }}">
                            {{ $course->status ?? 'Draft' }}
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->students_count ?? 0 }}</span>
                            Students
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->modules_count ?? 0 }}</span>
                            Modules
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->lessons_count ?? 0 }}</span>
                            Lessons
                        </span>
                    </div>
                </div>
                <div class="card-actions">
                    {{-- ✅ Manage → Managing Course screen for THIS course (same blade, manage mode) --}}
                    <a href="{{ route('faculty.courses.manage', $course->id ?? 1) }}" class="btn-action primary">Manage</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                You haven't created any courses yet.
            </div>
        @endforelse

    @endif

    </main>
</div>

<script>
    function toggleModuleCreate(show) {
        var form = document.getElementById('module-create-form');
        var button = document.getElementById('toggle-module-create');
        if (!form) return;
        var opening = typeof show === 'boolean' ? show : form.style.display === 'none';
        form.style.display = opening ? 'flex' : 'none';
        if (button) button.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            form.querySelector('input[name="module_title"]')?.focus();
        }
    }

    function toggleLessonForm(i, show) {
        var form  = document.getElementById('lesson-form-' + i);
        var empty = document.getElementById('lessons-empty-' + i);
        if (form)  form.style.display  = show ? 'flex' : 'none';
        if (empty) empty.style.display = show ? 'none' : 'block';
        if (show && form) {
            var first = form.querySelector('input[name="lesson_title"]');
            if (first) first.focus();
        }
    }

    function toggleActivityForm(lessonId, show) {
        var form = document.getElementById('activity-add-' + lessonId);
        if (!form) return;
        form.hidden = typeof show === 'boolean' ? !show : !form.hidden;
        if (!form.hidden) form.querySelector('input[name="title"]')?.focus();
    }

    function toggleActivityEdit(activityId) {
        var form = document.getElementById('activity-edit-' + activityId);
        if (!form) return;
        form.hidden = !form.hidden;
        if (!form.hidden) form.querySelector('input[name="title"]')?.focus();
    }

    function toggleQuizEdit(moduleId) {
        var frame = document.getElementById('quiz-edit-' + moduleId);
        if (!frame) return;
        frame.hidden = !frame.hidden;
        if (!frame.hidden) frame.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function toggleLessonQuizEdit(lessonId) {
        var frame = document.getElementById('lesson-quiz-edit-' + lessonId);
        if (!frame) {
            window.location.href = @json(route('faculty.lesson-quiz.create', [$course->id ?? 0, '__LESSON__'])).replace('__LESSON__', lessonId);
            return;
        }
        frame.hidden = !frame.hidden;
        if (!frame.hidden) frame.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.querySelectorAll('[data-order-kind][data-order-url]').forEach(function (list) {
        var draggedItem = null;
        var queuedItems = null;
        var isSavingOrder = false;

        async function saveQueuedOrder() {
            if (isSavingOrder || !queuedItems) return;
            isSavingOrder = true;
            var status = document.getElementById('reorder-status');

            while (queuedItems) {
                var items = queuedItems;
                queuedItems = null;
                if (status) status.textContent = 'Saving new order…';

                try {
                    var response = await fetch(list.dataset.orderUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ items: items }),
                    });
                    if (!response.ok) throw new Error('The new order could not be saved.');
                } catch (error) {
                    queuedItems = null;
                    if (status) status.textContent = error.message || 'Could not save the new order.';
                    window.setTimeout(function () { window.location.reload(); }, 900);
                    break;
                }
            }

            isSavingOrder = false;
            if (!queuedItems && status && status.textContent === 'Saving new order…') {
                status.textContent = 'Order saved.';
            }
            if (queuedItems) saveQueuedOrder();
        }

        list.addEventListener('dragstart', function (event) {
            var handle = event.target.closest('.drag-handle');
            if (!handle || handle.closest('.sortable-list') !== list) return;
            draggedItem = handle.closest('[data-order-item]');
            if (!draggedItem || draggedItem.parentElement !== list) return;
            draggedItem.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', draggedItem.dataset.orderId);
        });
        list.addEventListener('dragend', function () {
            list.querySelectorAll('.is-dragging,.is-drop-target').forEach(function (item) {
                item.classList.remove('is-dragging', 'is-drop-target');
            });
            draggedItem = null;
        });
        list.addEventListener('dragover', function (event) {
            if (!draggedItem) return;
            // Accept drops on the list's empty space too. Without this,
            // dropping below the last row can move the DOM item but never
            // dispatch a `drop` event, so the previous server order returns.
            event.preventDefault();
            var target = event.target.closest('[data-order-item]');
            if (!target || target === draggedItem || target.parentElement !== list) {
                var remainingItems = Array.from(list.querySelectorAll(':scope > [data-order-item]'))
                    .filter(function (item) { return item !== draggedItem; });
                var nextItem = remainingItems.find(function (item) {
                    var bounds = item.getBoundingClientRect();
                    return event.clientY < bounds.top + bounds.height / 2;
                });
                list.insertBefore(draggedItem, nextItem || null);
                return;
            }
            target.classList.add('is-drop-target');
            var bounds = target.getBoundingClientRect();
            var insertAfter = event.clientY > bounds.top + bounds.height / 2;
            list.insertBefore(draggedItem, insertAfter ? target.nextSibling : target);
        });
        list.addEventListener('dragleave', function (event) {
            var target = event.target.closest('[data-order-item]');
            if (target && !list.contains(event.relatedTarget)) target.classList.remove('is-drop-target');
        });
        list.addEventListener('drop', function (event) {
            if (!draggedItem) return;
            event.preventDefault();
            var kind = list.dataset.orderKind;
            var orderedItems = Array.from(list.querySelectorAll(':scope > [data-order-item]'));
            queuedItems = kind === 'assessments'
                ? orderedItems.map(function (item) { return { type: item.dataset.orderType, id: Number(item.dataset.orderId) }; })
                : orderedItems.map(function (item) { return Number(item.dataset.orderId); });
            saveQueuedOrder();
        });
    });

    // Lessons are editor-first. Keep CKEditor data synchronized with the
    // underlying textarea before the normal browser form submission.
    document.querySelectorAll('.lesson-form, #module-create-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-ckeditor]').forEach(function (editorElement) {
                var editor = window.upskillEditorInstances?.get(editorElement);
                if (editor) editorElement.value = editor.getData();
            });
        });
    });

    // Show / hide the inline module editor (title + description).
    function toggleModuleEdit(moduleIdx) {
        var box = document.getElementById('module-edit-' + moduleIdx);
        if (!box) return;
        var opening = box.style.display === 'none' || box.style.display === '';
        box.style.display = opening ? 'flex' : 'none';
        if (opening) {
            var first = box.querySelector('input[name="module_title"]');
            if (first) first.focus();
        }
    }

    function addChoice(q) {
        var box = document.getElementById('choices-' + q);
        if (!box) return;
        var i = box.children.length;
        if (i >= 26) return;
        var letter = String.fromCharCode(65 + i);   // D, E, F ...
        var row = document.createElement('div');
        row.className = 'choice-row';
        row.innerHTML =
            '<span class="choice-letter">' + letter + '.</span>' +
            '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
            '<label class="mark-correct">Mark as Correct' +
            '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
            '<span class="radio-dot"></span></label>';
        box.appendChild(row);
    }

    // Next unused question index (indices never repeat, even after removals)
    var nextQuestionIndex = document.querySelectorAll('#questions .question-card').length;

    // â”€â”€ "Create Quiz" â€” builds exactly N question containers, where N is the
    //    value of the "No. of Items" field (5 items â†’ 5 question cards) â”€â”€
    function createQuizQuestions() {
        var itemsInput = document.querySelector('input[name="items"]');
        var n = parseInt(itemsInput && itemsInput.value, 10);
        if (!n || n < 1) {
            alert('Please set the "No. of Items" first (at least 1) before creating the quiz.');
            if (itemsInput) itemsInput.focus();
            return;
        }
        if (n > 100) n = 100;

        var wrap = document.getElementById('questions');
        if (wrap) wrap.innerHTML = '';
        nextQuestionIndex = 0;
        for (var i = 0; i < n; i++) addQuestion();

        var intro = document.getElementById('create-quiz-intro');
        if (intro) intro.style.display = 'none';
        var area = document.getElementById('questions-area');
        if (area) area.style.display = 'block';
        var saveRow = document.getElementById('save-quiz-row');
        if (saveRow) saveRow.style.display = 'flex';
    }

    // âœ… Swaps the question card's display to match the chosen Type
    function questionTypeChanged(sel) {
        var card = sel.closest('.question-card');
        if (!card) return;
        var t  = sel.value;
        var mc = card.querySelector('.q-mc');
        var tf = card.querySelector('.q-tf');
        var id = card.querySelector('.q-id');
        if (mc) mc.style.display = t === 'Multiple Choice' ? 'block' : 'none';
        if (tf) tf.style.display = t === 'True or False'   ? 'block' : 'none';
        if (id) id.style.display = t === 'Identification'  ? 'block' : 'none';
    }

    function addQuestion() {
        var wrap = document.getElementById('questions');
        if (!wrap) return;
        var q = nextQuestionIndex++;
        var card = document.createElement('div');
        card.className = 'quiz-card question-card';
        var tfHtml = '';
        ['True', 'False'].forEach(function (label, i) {
            tfHtml +=
                '<div class="choice-row">' +
                '<span class="tf-label">' + label + '</span>' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][tf_correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        var choicesHtml = '';
        ['A', 'B', 'C'].forEach(function (letter, i) {
            choicesHtml +=
                '<div class="choice-row">' +
                '<span class="choice-letter">' + letter + '.</span>' +
                '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        card.innerHTML =
            '<div class="q-head">' +
                '<h3>Question ?</h3>' +
                '<span class="type-label">Type</span>' +
                '<div class="sel-wrap">' +
                    '<select name="questions[' + q + '][type]" onchange="questionTypeChanged(this)">' +
                        '<option value="Multiple Choice">Multiple Choice</option>' +
                        '<option value="True or False">True or False</option>' +
                        '<option value="Identification">Identification</option>' +
                    '</select>' +
                '</div>' +
                '<input class="input-outline points" type="number" name="questions[' + q + '][points]" placeholder="Points" min="0">' +
                '<button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">\u2715</button>' +
            '</div>' +
            '<div style="margin-bottom:18px;">' +
                '<input class="input-outline question-input" style="width:100%;" type="text" name="questions[' + q + '][text]" placeholder="Enter your question">' +
            '</div>' +
            '<div class="q-mc">' +
                '<div class="choices" id="choices-' + q + '">' + choicesHtml + '</div>' +
                '<button class="btn-add-choice" type="button" onclick="addChoice(' + q + ')">+ Add a Choice</button>' +
            '</div>' +
            '<div class="q-tf" style="display:none;">' + tfHtml + '</div>' +
            '<div class="q-id" style="display:none;">' +
                '<label class="q-label">Correct Answer</label>' +
                '<input class="input-outline" style="width:100%;max-width:420px;" type="text" name="questions[' + q + '][answer]" placeholder="Type the correct answer">' +
            '</div>';
        wrap.appendChild(card);
        renumberQuestions();
        var first = card.querySelector('input[type="text"]');
        if (first) first.focus();
    }

    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (card && confirm('Remove this question?')) {
            card.remove();
            renumberQuestions();
        }
    }

    function renumberQuestions() {
        document.querySelectorAll('#questions .question-card .q-head h3').forEach(function (h, i) {
            h.textContent = 'Question ' + (i + 1);
        });
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.manage-flash:not(.error)').forEach(function (message) {
            window.setTimeout(function () {
                message.classList.add('is-dismissing');
                window.setTimeout(function () {
                    message.remove();
                }, 280);
            }, 5000);
        });
    });

    function toggleLessonCollapse(lessonId, button) {
        var content = document.getElementById('lesson-content-' + lessonId);
        if (!content || !button) return;

        var isExpanded = button.getAttribute('aria-expanded') === 'true';
        var nextLabel = isExpanded ? 'Expand' : 'Collapse';

        content.hidden = isExpanded;
        button.setAttribute('aria-expanded', String(!isExpanded));
        button.setAttribute('aria-label', nextLabel + ' lesson content');
        button.setAttribute('title', nextLabel + ' lesson');

        var label = button.querySelector('.lesson-collapse-label');
        if (label) label.textContent = nextLabel;
    }

    // Show / hide the inline lesson editor.
    function toggleLessonEdit(lessonId) {
        var box = document.getElementById('lesson-edit-' + lessonId);
        if (!box) return;
        var opening = box.style.display === 'none' || box.style.display === '';
        box.style.display = opening ? 'flex' : 'none';   // .lesson-form is a flex column
        if (opening) {
            var first = box.querySelector('input[name="lesson_title"]');
            if (first) first.focus();
        }
    }

    // Reopen the editor the server flagged after a failed upload.
    document.addEventListener('DOMContentLoaded', function () {
        var open = document.querySelector('.lesson-edit-form[style*="flex"], .module-edit-form[style*="flex"]');
        if (open) open.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
</script>
</body>
</html>
