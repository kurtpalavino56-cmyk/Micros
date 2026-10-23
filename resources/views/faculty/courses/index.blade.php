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

<title>{{ $mode === 'final-exam' ? (($quiz ?? null) ? 'Edit Final Exam' : 'Create Final Exam') : ($mode === 'quiz' ? 'Add Quiz' : ($mode === 'manage' ? 'Manage Course' : 'My Courses')) }} | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b; --navy-deep:#0c0f4d; --banner:#172f94; --gold:#dba617; --gold-dark:#b8860b;
        --ink:#1b1f3b; --text:#344054; --muted:#667085; --line:#e6e8f0; --soft:#fafbfd; --bg:#f7f8fc;
        --green:#157f5b; --amber:#9a6700; --red:#b42318; --thumb:#d8e3f8;
        --shadow:0 1px 2px rgba(19,23,107,.05),0 6px 18px rgba(19,23,107,.05);
    }
    *{box-sizing:border-box;}
    body{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;color:var(--ink);margin:0;background:var(--bg);}
    a{text-decoration:none;color:inherit;} button{font-family:inherit;cursor:pointer;}
    :focus-visible{outline:3px solid var(--gold);outline-offset:2px;border-radius:6px;}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,.25);margin:18px 6px;}
    .main{padding:28px 36px 64px;min-width:0;}
    .manage-course-page{width:100%;max-width:1360px;margin-inline:auto;font-size:15px;}

    /* ── Buttons ───────────────────────────────────────────── */
    .btn-primary-action,.btn-secondary-action,.btn-save-lesson,.btn-cancel-outline,.btn-edit-inline,.btn-delete-activity,.btn-outline-navy{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;padding:8px 16px;border-radius:9px;font:600 13px Inter,"Segoe UI",sans-serif;white-space:nowrap;transition:background .15s,border-color .15s,color .15s;}
    .btn-primary-action,.btn-save-lesson{border:1px solid var(--navy);background:var(--navy);color:#fff;}
    .btn-primary-action:hover,.btn-save-lesson:hover{background:var(--navy-deep);}
    .btn-secondary-action,.btn-cancel-outline,.btn-edit-inline,.btn-outline-navy{border:1px solid #d0d5dd;background:#fff;color:var(--text);}
    .btn-secondary-action:hover,.btn-cancel-outline:hover,.btn-edit-inline:hover,.btn-outline-navy:hover{background:var(--bg);border-color:#98a2b3;}
    .btn-delete-activity{border:1px solid transparent;background:transparent;color:var(--red);min-height:32px;padding:6px 10px;}
    .btn-delete-activity:hover{background:#fef3f2;}
    .btn-edit-inline{min-height:32px;padding:6px 12px;} a.btn-edit-inline{display:inline-flex;}
    .btn-compact{min-height:32px;padding:6px 12px;font-size:12px;}
    .btn-primary-action:disabled{opacity:.45;cursor:not-allowed;}
    .btn-text-action{border:0;background:transparent;color:var(--text);padding:8px 10px;border-radius:8px;font:600 13px Inter,sans-serif;}
    .btn-text-action:hover{background:#f2f4f7;}
    .btn-icon-danger,.btn-icon-muted{display:grid;place-items:center;border-radius:8px;background:#fff;}
    .btn-icon-danger{width:34px;height:34px;border:1px solid #fecdca;color:var(--red);} .btn-icon-danger:hover{background:#fef3f2;}
    .btn-icon-muted{width:30px;height:30px;border:0;background:transparent;color:var(--muted);} .btn-icon-muted:hover{background:#fef3f2;color:var(--red);}
    .btn-icon-danger svg,.btn-icon-muted svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;}
    .btn-x{width:34px;height:34px;border-radius:50%;border:1px solid #d0d5dd;background:#fff;color:var(--muted);font-size:18px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .btn-x:hover{background:#fef3f2;border-color:#fecdca;color:var(--red);}
    .drag-handle{padding:0;border:0;background:transparent;color:#98a2b3;cursor:grab;letter-spacing:-2px;}
    .drag-handle:hover{color:var(--navy);}

    /* ── Inputs ────────────────────────────────────────────── */
    .input-outline,.textarea-outline,.sel-wrap select{width:100%;border:1px solid #d0d5dd;border-radius:9px;padding:10px 13px;font:500 14px Inter,sans-serif;color:var(--ink);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;}
    .input-outline:focus,.textarea-outline:focus,.sel-wrap select:focus,.activity-form input:focus,.activity-form select:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(19,23,107,.1);}
    .input-outline::placeholder,.textarea-outline::placeholder{color:#98a2b3;font-weight:400;}
    .textarea-outline{min-height:95px;resize:vertical;line-height:1.5;}
    .sel-wrap{position:relative;}
    .sel-wrap select{appearance:none;-webkit-appearance:none;-moz-appearance:none;padding-right:40px;cursor:pointer;}
    .sel-wrap::after{content:"";position:absolute;right:16px;top:50%;width:8px;height:8px;border-right:2px solid var(--muted);border-bottom:2px solid var(--muted);transform:translateY(-70%) rotate(45deg);pointer-events:none;}
    .form-field-label,.q-label{display:block;color:var(--text);font-size:12px;font-weight:600;margin-bottom:6px;}
    .q-label .req{color:#dc2626;}
    .field-help{display:block;margin-top:5px;color:var(--muted);font-size:12px;line-height:1.4;}
    .form-error{color:var(--red);font-size:13px;font-weight:600;}

    /* ── Status ────────────────────────────────────────────── */
    .status-chip{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;background:#eef0f4;color:#475467;}
    .status-chip::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor;}
    .status-chip.status-published{background:#e4f6ee;color:var(--green);}
    .status-chip.status-pending,.status-chip.status-pending-approval{background:#fff3d1;color:var(--amber);}
    .status-chip.status-denied,.status-chip.status-returned{background:#fee4e2;color:var(--red);}
    .status-chip.status-draft{background:#eef0f4;color:#475467;}
    .empty-state{border:1px dashed #cfd3e2;border-radius:14px;padding:30px;text-align:center;color:var(--muted);font-size:14px;background:#fff;}

    /* ── LIST mode (heading + subheading unchanged) ────────── */
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:26px;margin:0 0 4px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:13px;font-weight:600;}
    .page-head .btn-outline{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 20px;background:#fff2bf;border:1px solid #ead477;border-radius:10px;color:#17202a;font-size:14px;font-weight:700;transition:background .15s,box-shadow .15s,transform .15s;}
    .page-head .btn-outline svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;}
    .page-head .btn-outline:hover{background:#ffeb99;box-shadow:0 0 0 3px rgba(229,184,46,.18);transform:translateY(-1px);}

    .course-card{display:flex;align-items:center;gap:22px;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:20px 24px;margin-bottom:16px;transition:box-shadow .2s,border-color .2s;}
    .course-card:hover{border-color:#d3d7e8;box-shadow:0 2px 4px rgba(19,23,107,.05),0 12px 28px rgba(19,23,107,.08);}
    .thumb{width:96px;height:96px;border-radius:14px;background:linear-gradient(135deg,var(--thumb),#f6efd6) center/cover no-repeat;flex-shrink:0;}
    .course-info{flex:1;min-width:0;}
    .course-info h3{margin:0 0 6px;font-size:20px;font-weight:700;color:#000;line-height:1.3;}
    .course-info .desc{font-size:13px;color:var(--muted);line-height:1.55;max-width:560px;margin-bottom:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
    .course-meta{display:flex;align-items:center;gap:12px 28px;flex-wrap:wrap;}
    .meta-item{font-size:12px;color:var(--muted);line-height:1.3;}
    .meta-item .meta-num{display:block;font-weight:700;font-size:16px;color:var(--ink);}
    .card-actions{display:flex;flex-direction:column;gap:10px;flex-shrink:0;}
    .btn-action{display:block;width:100%;min-width:136px;text-align:center;padding:8px 14px;border-radius:10px;border:1px solid #d0d5dd;background:#fff;color:var(--navy);font-size:13px;font-weight:600;transition:background .15s,border-color .15s;}
    .btn-action:hover{background:var(--bg);border-color:var(--navy);}
    .btn-action.primary{background:var(--navy);border-color:var(--navy);color:#fff;} .btn-action.primary:hover{background:var(--navy-deep);}

    /* ── MANAGE mode: banner (heading + subheading unchanged) ─ */
    .course-banner{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;background:linear-gradient(120deg,var(--banner) 0%,#13176b 100%);border:0;border-radius:16px;box-shadow:var(--shadow);padding:22px 26px;margin-bottom:20px;color:#fff;}
    .banner-info .kicker{color:#f3cc54;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;}
    .banner-info .faculty-page-title{color:#fff!important;font-family:"Plus Jakarta Sans",Inter,sans-serif;font-size:27px!important;font-weight:700!important;line-height:1.25;margin:0 0 10px;}
    .banner-meta,.banner-meta.faculty-page-subtitle{display:flex;flex-wrap:wrap;gap:8px 14px;color:#fff!important;font-size:14px!important;font-weight:600!important;}
    .banner-meta span+span{border-left:1px solid rgba(255,255,255,.35);padding-left:14px;}
    .banner-actions{display:flex;align-items:center;gap:12px;}
    .banner-actions .row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .banner-actions a.btn-pill{display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.5);border-radius:9px;color:#fff;padding:9px 16px;font-size:13px;font-weight:700;transition:background .15s,border-color .15s;}
    .banner-actions a.btn-pill:hover{background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.85);}
    .banner-actions .course-status-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border:1px solid rgba(255,255,255,.28);border-radius:999px;background:rgba(255,255,255,.08);color:#fff;font-size:13px;font-weight:700;white-space:nowrap;}
    .course-status-indicator{width:8px;height:8px;flex:0 0 8px;border-radius:50%;background:#94a3b8;box-shadow:0 0 0 3px rgba(148,163,184,.2);}
    .course-status-badge.status-pending{border-color:rgba(245,158,11,.5);background:rgba(245,158,11,.18);color:#fde68a;}
    .course-status-badge.status-published{border-color:rgba(34,197,94,.45);background:rgba(34,197,94,.17);color:#bbf7d0;}
    .course-status-badge.status-denied{border-color:rgba(239,68,68,.5);background:rgba(239,68,68,.18);color:#fecaca;}
    .course-status-badge.status-pending .course-status-indicator{background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.2);}
    .course-status-badge.status-published .course-status-indicator{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2);}
    .course-status-badge.status-denied .course-status-indicator{background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.2);}

    .manage-flash{margin:0 0 18px;padding:12px 16px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534;font-size:14px;font-weight:600;transition:opacity .25s,transform .25s;}
    .manage-flash.error{border-color:#fecaca;background:#fef2f2;color:#991b1b;}
    .manage-flash.review-feedback{border-color:#fde68a;background:#fffbeb;color:#78350f;}
    .manage-flash.review-feedback strong{display:block;margin-bottom:4px;}
    .manage-flash.review-feedback p{margin:0;font-weight:500;line-height:1.55;white-space:pre-wrap;}
    .manage-flash.is-dismissing{opacity:0;transform:translateY(-4px);}

    /* Readiness */
    .readiness-panel{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px 20px;align-items:center;margin:0 0 22px;padding:18px 20px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);}
    .readiness-heading h3{margin:0;color:#17202a;font-size:17px;font-weight:700;}
    .readiness-heading p{margin:4px 0 0;color:var(--muted);font-size:13px;line-height:1.45;}
    .readiness-action{display:flex;align-items:center;gap:10px;}
    .readiness-state{font-size:13px;font-weight:700;color:#475467;}
    .readiness-state.pending{color:var(--amber);} .readiness-state.published{color:var(--green);} .readiness-state.denied{color:var(--red);}
    .readiness-list{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:0;list-style:none;}
    .readiness-item{display:flex;align-items:flex-start;gap:8px;padding:8px 12px;border:1px solid #f3dfa2;border-radius:10px;background:#fffaf0;color:var(--text);font-size:13px;line-height:1.4;}
    .readiness-item.complete{border-color:#bbf7d0;background:#f0fdf4;}
    .readiness-mark{display:inline-flex;align-items:center;justify-content:center;flex:0 0 18px;width:18px;height:18px;border-radius:50%;background:#fde68a;color:#7a5600;font-size:11px;font-weight:800;}
    .readiness-item.complete .readiness-mark{background:#86efac;color:#14532d;}
    .readiness-item strong{display:block;font-weight:600;color:#17202a;}
    .readiness-item small{display:block;margin-top:2px;color:var(--muted);font-size:12px;}

    /* Sections */
    .content-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:22px;align-items:start;}
    .section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin:0 0 14px;}
    .modules-section-head{padding:0 1px 12px;border-bottom:1px solid var(--line);}
    .section-title{margin:0;color:#17202a;font-size:21px;font-weight:700;}
    .section-caption{margin:4px 0 0;color:var(--muted);font-size:14px;line-height:1.45;}
    .add-module-card{display:none;flex-direction:column;gap:8px;padding:18px;margin:0 0 18px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);}
    .module-create-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:6px;}
    .empty-modules{padding:44px 20px;text-align:center;background:#fff;border:1px dashed #cfd3e2;border-radius:14px;}
    .empty-modules h4{margin:0 0 6px;color:var(--text);font-size:16px;} .empty-modules p{margin:0;color:var(--muted);font-size:14px;}
    #reorder-status{min-height:18px;margin-top:6px;color:var(--muted);font-size:12px;}
    .is-dragging{opacity:.45;} .is-drop-target{outline:2px dashed var(--gold);outline-offset:2px;}

    /* Module */
    .module-card{position:relative;margin:0 0 18px;padding:20px 20px 18px 42px;background:#fff;border:1px solid var(--line);border-left:4px solid var(--navy);border-radius:14px;box-shadow:var(--shadow);}
    .module-drag-handle{position:absolute;top:22px;left:12px;}
    .module-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding-bottom:14px;border-bottom:1px solid var(--line);}
    .module-heading-copy{min-width:0;}
    .eyebrow-label{display:block;color:var(--muted);font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .module-head h4{margin:4px 0 6px;color:#17202a;font-family:"Plus Jakarta Sans",Inter,sans-serif;font-size:19px;font-weight:700;line-height:1.3;overflow-wrap:anywhere;}
    .module-description{max-width:850px;color:var(--muted);font-size:14px;line-height:1.55;}
    .module-stats{margin-top:8px;color:var(--muted);font-size:13px;font-weight:500;}
    .module-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .module-actions form,.lesson-actions form,.activity-actions form{margin:0;}

    /* Forms inside modules */
    .lesson-form{display:flex;flex-direction:column;gap:12px;margin:14px 0;padding:16px;background:var(--soft);border:1px solid var(--line);border-radius:12px;}
    .lesson-form .row2{display:grid;grid-template-columns:1fr;gap:12px;}
    .lesson-form .actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;}
    .lesson-form .actions>.input-outline,.lesson-form .actions>.duration-field{margin-right:auto;max-width:200px;}
    .duration-field{display:flex;align-items:center;gap:8px;color:var(--text);font-size:13px;font-weight:600;}
    .duration-field .input-outline{width:110px;} .duration-field span{color:var(--muted);font-weight:500;}
    .up-ckeditor-wrapper .ck.ck-editor__main>.ck-editor__editable{min-height:110px;}

    /* Lessons */
    .lesson-sort-list{display:flex;flex-direction:column;gap:10px;margin-top:14px;}
    .lesson-sort-item{padding:12px 14px;background:var(--soft);border:1px solid var(--line);border-radius:12px;}
    .lesson-row{display:grid;grid-template-columns:18px 28px minmax(0,1fr) auto;align-items:center;gap:10px;padding:0 0 10px;}
    .lesson-number{display:grid;place-items:center;width:26px;height:26px;border-radius:50%;background:#eef0fb;color:var(--navy);font-size:12px;font-weight:700;font-variant-numeric:tabular-nums;}
    .lesson-info h4{margin:0 0 2px;color:#17202a;font-size:16px;font-weight:700;line-height:1.35;}
    .lesson-info .sub{color:var(--muted);font-size:13px;}
    .lesson-actions{display:flex;align-items:center;gap:4px;}
    .lesson-action-btn{border:0;border-radius:7px;background:transparent;color:var(--text);padding:7px 10px;font:600 12px Inter,sans-serif;white-space:nowrap;}
    .lesson-action-btn:hover{background:#eceef6;color:var(--navy);}
    .lesson-collapse-btn{display:inline-flex;align-items:center;gap:5px;}
    .lesson-collapse-btn svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;transition:transform .15s;}
    .lesson-collapse-btn[aria-expanded="false"] svg{transform:rotate(180deg);}
    .lesson-collapsible-content[hidden]{display:none;}
    .lessons-empty{padding:18px;text-align:center;color:var(--muted);font-size:14px;border:1px dashed #d0d5dd;border-radius:12px;background:var(--soft);}
    .module-add-lesson{display:flex;justify-content:center;margin:14px 0 0;}

    /* Activities + lesson assessment */
    .activity-panel{margin:0 0 0 38px;padding:12px 14px;background:#fff;border:1px solid var(--line);border-radius:10px;}
    .activity-panel>.activity-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:0 0 10px;border-bottom:1px solid var(--line);}
    .activity-panel>.activity-head strong{color:var(--muted);font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .activity-panel>.activity-head .btn-primary-action{border-color:#b8860b;background:#e5b82e;color:#17202a;}
    .activity-panel>.activity-head .btn-primary-action:hover{background:var(--gold);}
    .assessment-sort-list{display:flex;flex-direction:column;margin:0;}
    .assessment-sort-item{border-bottom:1px solid #f0f1f5;}
    .assessment-sort-item:last-child{border-bottom:0;}
    .activity-card{display:flex;flex-direction:column;gap:8px;padding:10px 0;background:transparent;}
    .activity-card .activity-head{display:grid;grid-template-columns:16px minmax(0,1fr) auto;align-items:center;gap:10px;width:100%;}
    .activity-copy{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0;}
    .activity-kind{color:var(--muted);font-size:12px;font-weight:600;}
    .activity-copy>strong{color:#17202a;font-size:14px;font-weight:700;line-height:1.35;overflow-wrap:anywhere;}
    .activity-meta{color:var(--muted);font-size:13px;line-height:1.4;}
    .activity-actions{display:flex;align-items:center;justify-content:flex-end;gap:4px;flex-wrap:wrap;}
    .activity-empty{margin:8px 0 0;color:var(--muted);font-size:13px;}
    .lesson-assessment{margin-top:8px;padding-top:10px;border-top:1px solid var(--line);}
    .assessment-label{margin-bottom:4px;color:var(--muted);font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .lesson-quiz-row{display:grid;grid-template-columns:16px minmax(0,1fr) auto;align-items:center;gap:12px;width:100%;}
    .lesson-quiz-title{color:#17202a;font-size:16px;font-weight:700;line-height:1.35;}
    .lesson-assessment-empty{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px;padding-top:10px;border-top:1px solid var(--line);}
    .lesson-assessment-empty .assessment-label{flex-basis:100%;margin:0;}
    .quiz-none{margin-right:auto;color:var(--muted);font-size:13px;}
    .activity-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px;padding:14px;background:var(--soft);border:1px solid var(--line);border-radius:12px;}
    .activity-form[hidden]{display:none;}
    .activity-form>input,.activity-form>select,.activity-form .activity-actions input{width:100%;min-width:0;border:1px solid #d0d5dd;border-radius:9px;padding:10px 12px;font:500 14px Inter,sans-serif;color:var(--ink);background:#fff;outline:none;}
    .activity-form>.activity-form-context,.activity-form>.full,.activity-form>input[name="title"],.activity-form>select,.activity-form>.up-ckeditor-wrapper,.activity-form>label,.activity-form>.activity-actions{grid-column:1/-1;}
    .activity-form>label{display:flex;align-items:center;gap:8px;color:var(--text);font-size:13px;}
    .activity-form-context{display:flex;flex-direction:column;gap:3px;} .activity-form-context strong{color:#17202a;font-size:14px;} .activity-form-context span{color:var(--muted);font-size:13px;}
    .activity-form>.activity-actions:not(.full){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));}
    .activity-form .activity-actions.full{justify-content:flex-end;}

    /* Summative + final exam + quiz averages */
    .summative-assessment{display:block;margin:16px 0 0;padding:14px 16px;background:#fffdf7;border:1px solid #eadcae;border-left:4px solid var(--gold);border-radius:12px;}
    .summative-label{margin:0 0 8px;color:#8a6500;font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;}
    .summative-content{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .quiz-info{flex:1;min-width:0;} .quiz-info h4{margin:0 0 2px;color:#17202a;font-size:15px;font-weight:700;} .quiz-info .sub{color:var(--muted);font-size:13px;}
    .panel{margin-top:22px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);overflow:hidden;}
    .panel-head{padding:12px 18px;background:var(--soft);border-bottom:1px solid var(--line);color:var(--text);font-size:14px;font-weight:700;}
    .panel-body{padding:16px 18px;}
    .avg-row{display:flex;align-items:center;gap:14px;margin-bottom:14px;} .avg-row:last-child{margin-bottom:0;}
    .avg-title{font-weight:600;font-size:13px;color:var(--text);white-space:nowrap;}
    .avg-track{flex:1;height:8px;border-radius:999px;background:#e9ebf5;overflow:hidden;} .avg-fill{height:100%;background:var(--green);border-radius:999px;}
    .avg-pct{font-weight:700;font-size:13px;color:var(--navy);white-space:nowrap;}

    /* ── QUIZ BUILDER ──────────────────────────────────────── */
    .back-link{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--navy);margin-bottom:8px;}
    .back-link:hover{text-decoration:underline;}
    .quiz-head{font-size:24px;margin:0 0 20px;color:var(--navy);}
    .quiz-card{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);padding:22px;margin-bottom:20px;}
    .grid3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:18px;}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;}
    .question-bulk-tools{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:12px 16px;margin-bottom:16px;border:1px solid var(--line);border-radius:12px;background:#fff;color:var(--ink);font-size:13px;}
    .question-bulk-tools label{display:inline-flex;align-items:center;gap:9px;font-weight:600;cursor:pointer;}
    .question-bulk-status{color:var(--muted);}
    .question-select{width:17px;height:17px;accent-color:var(--navy);cursor:pointer;flex:0 0 auto;}
    .q-head{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px;}
    .q-head h3{margin:0;font-size:19px;color:var(--navy);}
    .q-head .type-label{margin-left:auto;font-size:12px;font-weight:700;color:var(--muted);}
    .q-head .sel-wrap{min-width:190px;} .q-head .points{width:110px;}
    .question-input{max-width:520px;}
    .choice-row{display:flex;align-items:center;gap:14px;margin-bottom:12px;}
    .choice-letter{width:26px;flex-shrink:0;font-weight:700;font-size:15px;color:var(--navy);}
    .choice-row .input-outline{flex:1;max-width:520px;}
    .mark-correct{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:var(--text);cursor:pointer;white-space:nowrap;}
    .mark-correct input{display:none;}
    .radio-dot{width:20px;height:20px;border-radius:50%;border:1.5px solid #c9ccdb;background:#fff;flex-shrink:0;transition:background .15s,border-color .15s;}
    .mark-correct input:checked + .radio-dot{background:#22c55e;border-color:#22c55e;box-shadow:inset 0 0 0 3px #fff;}
    .btn-add-choice{width:100%;background:#fff;border:1.5px dashed #c9ccdb;border-radius:10px;padding:11px;font-weight:600;color:var(--navy);font-size:13px;margin-top:6px;}
    .btn-add-choice:hover{background:var(--bg);border-color:var(--navy);}
    .tf-label{flex:1;max-width:520px;font-weight:600;font-size:14px;color:var(--ink);border:1px solid #d0d5dd;border-radius:9px;padding:10px 14px;background:var(--soft);}
    .save-quiz-row{display:flex;justify-content:center;}

    /* Inline quiz editor (embedded in Manage Course) */
    body.inline-quiz{min-height:0;background:#fff;overflow-x:hidden;}
    .inline-quiz .auth-topbar,.inline-quiz .faculty-sidebar,.inline-quiz .back-link,.inline-quiz #back-to-top-btn{display:none!important;}
    .inline-quiz .layout.faculty-sidebar-layout{display:block;min-height:0;}
    .inline-quiz .layout.faculty-sidebar-layout>.main{width:100%;max-width:none;margin:0;padding:18px 20px 24px;}
    .inline-quiz .faculty-page-heading .quiz-head{margin:0 0 14px;font-size:19px!important;line-height:1.35;}
    .inline-quiz .quiz-card{margin-bottom:12px;padding:16px;}
    .inline-quiz .save-quiz-row{gap:8px;}

    /* ── Responsive ────────────────────────────────────────── */
    @media (max-width:1024px){.grid3,.grid2{grid-template-columns:1fr;}}
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .course-card{flex-direction:column;align-items:stretch;gap:16px;}
        .thumb{width:100%;height:140px;}
        .card-actions{flex-direction:row;flex-wrap:wrap;}
        .btn-action{flex:1 1 140px;min-width:0;}
        .readiness-panel{grid-template-columns:1fr;}
        .module-card{padding-left:36px;} .module-drag-handle{left:10px;}
        .activity-panel{margin-left:0;}
    }
    @media (max-width:640px){
        .main{padding:16px 12px 42px;}
        .course-banner{padding:18px 16px;} .banner-actions{width:100%;}
        .course-meta{gap:12px 20px;}
        .module-card{padding:16px 12px 14px 30px;} .module-drag-handle{left:7px;top:18px;}
        .module-head{flex-direction:column;} .module-actions{align-self:flex-end;margin-top:-4px;}
        .lesson-sort-item{padding:10px;}
        .lesson-row{grid-template-columns:18px 26px minmax(0,1fr);}
        .lesson-actions{grid-column:3;justify-content:flex-start;flex-wrap:wrap;}
        .lesson-collapse-label{display:none;}
        .activity-panel{padding:10px;}
        .activity-card .activity-head,.lesson-quiz-row{grid-template-columns:16px minmax(0,1fr);align-items:start;}
        .activity-card .activity-actions,.lesson-quiz-row .activity-actions{grid-column:2;justify-content:flex-start;}
        .activity-form{grid-template-columns:1fr;} .activity-form>.activity-actions:not(.full){grid-template-columns:1fr;}
        .lesson-form{padding:12px;}
        .lesson-form .actions>.input-outline,.lesson-form .actions>.duration-field{flex:1 1 100%;max-width:none;}
        .summative-content .quiz-info,.summative-content .quiz-none{flex:1 1 100%;}
        .quiz-card{padding:16px;}
        .question-input,.choice-row .input-outline,.tf-label{max-width:100%;}
        .q-head{gap:10px;} .q-head .sel-wrap{min-width:0;flex:1 1 100%;} .q-head .points{width:100%;} .q-head .type-label{margin-left:0;}
        .choice-row{gap:10px;flex-wrap:wrap;} .mark-correct{white-space:normal;}
        .inline-quiz .layout.faculty-sidebar-layout>.main{padding:14px 12px 20px;}
    }
    @media (prefers-reduced-motion:reduce){*{transition:none!important;}}
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
                    <a href="{{ route('faculty.courses.edit', $course->id) }}" class="btn-pill">Edit Course Details</a>
                </div>
            </div>
        </section>

        <x-flash-toast :message="session('success')" />
        @if($errors->has('submission'))
            <div class="manage-flash error" role="alert">{{ $errors->first('submission') }}</div>
        @endif
        @if(filled($course->denial_feedback ?? null))
            <section class="manage-flash review-feedback" role="alert" aria-labelledby="admin-review-feedback-title">
                <strong id="admin-review-feedback-title">Feedback from admin</strong>
                <p>{{ $course->denial_feedback }}</p>
            </section>
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
                            {{-- Inline lesson editor stays with the lesson content,
                                 before its activities and assessments. --}}
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
                                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'activity-instructions-edit-'.$activity->id, 'value' => old('instructions', $activity->instructions), 'placeholder' => 'Instructions and criteria'])
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
                                            <button class="drag-handle activity-drag-handle" type="button" draggable="true" title="Drag to reorder lesson assessment" aria-label="Drag to reorder lesson assessment">⋮⋮</button>
                                            <div><strong class="lesson-quiz-title">{{ $lesson->quiz->title }}</strong><div class="activity-meta">{{ $lesson->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $lesson->quiz->questions_count) }} · Pass {{ $lesson->quiz->passing_score }}%</div></div>
                                            <div class="activity-actions">
                                                <a class="btn-edit-inline" href="{{ route('faculty.lesson-quiz.create', [$course->id, $lesson->id]) }}">Edit Quiz</a>
                                            </div>
                                        </div>
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
                                    @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'activity-instructions-add-'.$lesson->id, 'value' => old('instructions'), 'placeholder' => 'Instructions and criteria'])
                                    <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', true))> Required for lesson completion</label>
                                    <div class="activity-actions"><input type="number" name="max_points" min="1" max="10000" value="{{ old('max_points') }}" placeholder="Points (assignment)"><input type="number" name="passing_percent" min="1" max="100" value="{{ old('passing_percent') }}" placeholder="Pass %"></div>
                                    <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityForm({{ $lesson->id }}, false)">Cancel</button></div>
                                </form>
                            </section>

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
                                <a class="btn-edit-inline" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">Edit Quiz</a>
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

                <section class="panel" aria-labelledby="course-completion-title" style="margin-top:18px;padding:22px;border:1px solid #e5e7eb;border-radius:14px;background:#fff;">
                    <h3 id="course-completion-title" style="margin:0 0 6px;">Course completion</h3>
                    <p style="margin:0 0 16px;color:#667085;">Add a separate course assessment that unlocks after students finish the course modules.</p>
                    @if($finalModule)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                            <div>
                                <strong>Final Exam</strong>
                                @if($finalModule->quiz)
                                    <div style="margin-top:4px;color:#667085;">{{ $finalModule->quiz->title }} · {{ $finalModule->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $finalModule->quiz->questions_count) }} · Pass {{ $finalModule->quiz->passing_score }}% · {{ $finalModule->quiz->attempts ?: 'Unlimited' }} attempts</div>
                                @else
                                    <div style="margin-top:4px;color:#b45309;">Final Exam setup is required before course approval.</div>
                                @endif
                            </div>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <a class="btn-secondary-action" href="{{ route('faculty.final-exam.edit', [$course->id, $finalModule->id]) }}">Edit Final Exam</a>
                                <form method="POST" action="{{ route('faculty.final-exam.destroy', $course->id) }}" onsubmit="return confirm('Remove the final exam and its quiz?');">
                                    @csrf
                                    <button class="btn-text-action" type="submit">Remove final exam</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('faculty.final-exam.store', $course->id) }}">
                            @csrf
                            <button class="btn-primary-action" type="submit">Add final exam</button>
                        </form>
                    @endif
                </section>
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

    @elseif (in_array($mode, ['quiz', 'final-exam'], true))

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• QUIZ BUILDER MODE â€” "Add Quiz : <module>" â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        {{-- âœ… Back to the Managing Course screen --}}
        <a class="back-link" href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">&larr; Back to Modules</a>
        <div class="faculty-page-heading"><h2 class="quiz-head faculty-page-title">@if($mode === 'final-exam'){{ ($quiz ?? null) ? 'Edit' : 'Create' }} Final Exam: {{ $course->title }}@else{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz: {{ $moduleTitle }}@endif</h2></div>
        <x-flash-toast :message="session('success')" />

        {{-- âœ… Real form â€” Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form method="POST" action="{{ $mode === 'final-exam' ? route('faculty.final-exam.update', [$course->id, $moduleIdx]) : (!empty($lessonId) ? route('faculty.lesson-quiz.store', [$course->id, $lessonId]) : route('faculty.quiz.store', [$course->id, $moduleIdx])) }}">
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
                        <input class="input-outline" style="width:100%;" type="text" name="attempts" placeholder="Unlimited" value="{{ $quiz->attempts ?? '' }}">
                        <small class="field-help">Leave blank for unlimited attempts. Enter a number to set a limit.</small>
                    </div>
                    <div>
                        <label class="q-label">Time Limit (min)</label>
                        <input class="input-outline" style="width:100%;" type="number" name="time_limit" placeholder="No time limit" min="1" value="{{ ($quiz->time_limit ?? 0) > 0 ? $quiz->time_limit : '' }}">
                        <small class="field-help">Leave blank to allow unlimited time. A timer is applied only when you enter a number.</small>
                    </div>
                </div>
                <div>
                    <label class="q-label">Instructions ( Optional )</label>
                    @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'assessment-instructions', 'value' => old('instructions', $quiz->instructions ?? ''), 'placeholder' => 'Add instructions for students...'])
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
                    Set the {{ $mode === 'final-exam' ? 'final exam' : 'quiz' }} details above &mdash; especially <strong>No. of Items</strong> &mdash; then click
                    <strong>{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</strong>. One question container will be created per item.
                </p>
                <button class="btn-save-lesson" type="button" onclick="createQuizQuestions()">{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</button>
            </div>
            @endif

            <div id="questions-area" style="display:{{ $isNewQuiz ? 'none' : 'block' }};">
            <div class="question-bulk-tools" id="question-bulk-tools">
                <label for="select-all-questions">
                    <input class="question-select" id="select-all-questions" type="checkbox" aria-label="Select all questions">
                    <span>Select all questions</span>
                </label>
                <span class="question-bulk-status" id="question-bulk-status" aria-live="polite">Select questions to change their type or points together.</span>
            </div>
            <div id="questions">
                @foreach ($questions as $q => $question)
                    <div class="quiz-card question-card">
                        <div class="q-head">
                            <input class="question-select question-select-item" type="checkbox" aria-label="Select question {{ $q + 1 }}" onchange="updateQuestionSelectionState()">
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
                            <input class="input-outline points" type="number" name="questions[{{ $q }}][points]" placeholder="Points" min="0" value="{{ $question['points'] ?? '' }}" oninput="syncSelectedQuestionPoints(this)">
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
                <button class="btn-save-lesson" type="submit">{{ $mode === 'final-exam' ? 'Save Final Exam' : 'Save Quiz' }}</button>
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
            <a href="{{ route('faculty.create') }}" class="btn-outline" aria-label="Create a new course"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Create New Course</span></a>
        </div>

        @forelse ($courses as $course)
            <div class="course-card">
                <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    @php($shortDescription = trim((string) ($course->short_description ?? '')))
                    @if($shortDescription !== '')
                        <div class="desc">{{ $shortDescription }}</div>
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
                    <a href="{{ route('faculty.activities.reviews', $course->id ?? 1) }}" class="btn-action">Activity Reviews</a>
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
        var t = sel.value;
        renderQuestionType(card, t);

        var selection = card.querySelector('.question-select-item');
        if (selection && selection.checked) {
            document.querySelectorAll('#questions .question-card').forEach(function (selectedCard) {
                if (selectedCard === card) return;
                var selected = selectedCard.querySelector('.question-select-item');
                var selectedType = selectedCard.querySelector('select[name$="[type]"]');
                if (!selected || !selected.checked || !selectedType) return;
                selectedType.value = t;
                renderQuestionType(selectedCard, t);
            });
        }
    }

    function renderQuestionType(card, type) {
        var mc = card.querySelector('.q-mc');
        var tf = card.querySelector('.q-tf');
        var id = card.querySelector('.q-id');
        if (mc) mc.style.display = type === 'Multiple Choice' ? 'block' : 'none';
        if (tf) tf.style.display = type === 'True or False' ? 'block' : 'none';
        if (id) id.style.display = type === 'Identification' ? 'block' : 'none';
    }

    function updateQuestionSelectionState() {
        var master = document.getElementById('select-all-questions');
        var status = document.getElementById('question-bulk-status');
        var cards = Array.prototype.slice.call(document.querySelectorAll('#questions .question-card'));
        var selectedCount = cards.filter(function (card) {
            var checkbox = card.querySelector('.question-select-item');
            return checkbox && checkbox.checked;
        }).length;

        if (master) {
            master.checked = cards.length > 0 && selectedCount === cards.length;
            master.indeterminate = selectedCount > 0 && selectedCount < cards.length;
            master.setAttribute('aria-checked', master.indeterminate ? 'mixed' : String(master.checked));
        }
        if (status) {
            status.textContent = selectedCount === 0
                ? 'Select questions to change their type or points together.'
                : selectedCount + ' question' + (selectedCount === 1 ? '' : 's') + ' selected. Changing type or points on a selected question updates all selected questions.';
        }
    }

    function syncSelectedQuestionPoints(input) {
        var card = input.closest('.question-card');
        var selection = card && card.querySelector('.question-select-item');
        if (!selection || !selection.checked) return;

        document.querySelectorAll('#questions .question-card').forEach(function (selectedCard) {
            if (selectedCard === card) return;
            var selected = selectedCard.querySelector('.question-select-item');
            var points = selectedCard.querySelector('.points');
            if (selected && selected.checked && points) points.value = input.value;
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.id === 'select-all-questions') {
            var shouldSelect = event.target.checked;
            document.querySelectorAll('#questions .question-select-item').forEach(function (checkbox) {
                checkbox.checked = shouldSelect;
            });
            updateQuestionSelectionState();
        }
    });

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
                '<input class="question-select question-select-item" type="checkbox" aria-label="Select question" onchange="updateQuestionSelectionState()">' +
                '<h3>Question ?</h3>' +
                '<span class="type-label">Type</span>' +
                '<div class="sel-wrap">' +
                    '<select name="questions[' + q + '][type]" onchange="questionTypeChanged(this)">' +
                        '<option value="Multiple Choice">Multiple Choice</option>' +
                        '<option value="True or False">True or False</option>' +
                        '<option value="Identification">Identification</option>' +
                    '</select>' +
                '</div>' +
                '<input class="input-outline points" type="number" name="questions[' + q + '][points]" placeholder="Points" min="0" oninput="syncSelectedQuestionPoints(this)">' +
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
        updateQuestionSelectionState();
        var first = card.querySelector('input[type="text"]');
        if (first) first.focus();
    }

    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (card && confirm('Remove this question?')) {
            card.remove();
            renumberQuestions();
            updateQuestionSelectionState();
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
