{{--
    resources/views/faculty/Create_Courses.blade.php

    Faculty â€º Create Courses â€” single self-contained Blade view (no layout).
    âš  STANDALONE within the faculty side: links back to the Faculty
      Dashboard (route 'faculty.dashboard') and Faculty My Courses
      (route 'faculty.courses'). Saving POSTs to route
      'faculty.create.store', which stores the course in the SESSION
      (no database yet) and redirects to the Managing Course screen
      for the newly created course.

    Expected data from the route, e.g.:

    return view('faculty.courses.create', [
        'user'       => $user,            // needs ->name (avatar optional)
        'categories' => ['Web Development', ...],
        'programs'   => ['BSIT', ...],
        'terms'      => ['1st Semester 2026 - 2027', ...],
        'levels'     => ['Beginner', 'Intermediate', 'Advanced'],
    ]);
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ ($editing ?? false) ? 'Edit Course' : 'Create Courses' }} | Upskill</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

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
        --green:#22c55e;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,0.14);}
    .side-icon-box svg{color:#fff;width:22px;height:22px;transition:color .15s ease;}
    .side-link.active .side-icon-box{background:var(--navy);}
    .side-link.plain .side-icon-box{background:transparent;}
    .side-link.plain .side-icon-box svg{color:#fff;width:26px;height:26px;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{padding:32px 36px 60px;max-width:1420px;}
    /* Form on the left, certificate preview on the right. The form keeps its
       original reading width; the preview takes the space that used to sit
       empty beside it. */
    .build-shell{display:grid;grid-template-columns:minmax(0,780px) minmax(0,470px);
        gap:28px;align-items:start;}
    .build-form{min-width:0;}
    .build-cert{position:sticky;top:20px;}
    .build-cert__hd{display:flex;align-items:center;justify-content:space-between;
        font-size:12px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;
        color:var(--muted);margin-bottom:10px;}
    .build-cert__hd a{color:var(--navy);text-decoration:underline;text-transform:none;
        letter-spacing:0;font-size:12.5px;}
    .build-cert__note{font-size:12px;color:#9a3412;background:#fff7ed;border:1px solid #fed7aa;
        border-radius:10px;padding:9px 12px;margin-top:12px;line-height:1.5;}


    @media(max-width:1240px){
        .build-shell{grid-template-columns:1fr;}
        .build-cert{position:static;max-width:460px;}
    }
    .page-head{margin-bottom:26px;}
    .page-head__row{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;}
    .head-actions{display:flex;gap:10px;flex-wrap:wrap;flex-shrink:0;}
    .btn-award{display:inline-flex;align-items:center;gap:8px;background:var(--navy);color:#fff;
        border:2px solid var(--navy);border-radius:12px;padding:10px 18px;font-size:13px;
        font-weight:800;cursor:pointer;font-family:inherit;transition:background .15s,transform .15s;}
    .btn-award:hover{background:#0f1352;transform:translateY(-1px);}
    .btn-award svg{width:17px;height:17px;flex-shrink:0;}
    .btn-award--gold{background:var(--gold);border-color:var(--gold);color:#13176b;}
    .btn-award--gold:hover{background:#c4930f;}
    .btn-award--muted{background:#fff;border-color:var(--line);color:#9ca3af;}
    .btn-award--muted:hover{background:#f7f9ff;transform:none;}
    .page-head h2{font-size:26px;margin:0 0 4px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:13px;font-weight:600;}

    /* Form cards */
    .form-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);margin-bottom:28px;overflow:hidden;}
    .form-card-head{padding:16px 24px;border-bottom:1px solid var(--line);}
    .form-card-head h3{margin:0;font-size:18px;color:var(--navy);}
    .form-card-body{padding:22px 24px;}

    .field{margin-bottom:20px;}
    .field:last-child{margin-bottom:0;}
    .field label{display:block;font-size:12px;font-weight:800;color:var(--navy);margin-bottom:8px;}
    .field label .req{color:#dc2626;}
    .field-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

    .input, .select, .textarea{
        width:100%;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;
        font-size:13px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;background:#fff;
    }
    .input::placeholder, .textarea::placeholder{color:#9aa0b4;font-weight:500;}
    .textarea{border-radius:14px;min-height:110px;resize:vertical;}
    .textarea.tall{min-height:130px;}
    .field-hint{display:block;margin-top:6px;color:#7b8498;font-size:11px;line-height:1.4;}

    .select-wrap{position:relative;}
    .select{appearance:none;-webkit-appearance:none;-moz-appearance:none;padding-right:48px;cursor:pointer;}
    .competency-entry{display:flex;align-items:center;gap:8px;}
    .competency-entry .input{min-width:0;}
    .competency-add{flex:0 0 auto;border:0;border-radius:999px;padding:12px 20px;background:var(--navy);color:#fff;font-size:13px;font-weight:800;}
    .competency-add:hover{background:var(--gold);color:var(--navy);}
    .competency-list{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px;}
    .competency-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid #c9cdf0;border-radius:999px;padding:5px 8px 5px 11px;background:#eef1fb;color:var(--navy);font-size:12px;font-weight:700;}
    .competency-remove{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;border:0;border-radius:50%;background:transparent;color:var(--navy);font-size:16px;line-height:1;cursor:pointer;padding:0;}
    .competency-remove:hover{background:var(--navy);color:#fff;}
    .select-wrap::after{
        content:"";position:absolute;right:16px;top:50%;transform:translateY(-50%);
        width:22px;height:22px;border-radius:50%;background:var(--navy);pointer-events:none;
    }
    .select-wrap::before{
        content:"";position:absolute;right:22px;top:50%;transform:translateY(-60%) rotate(45deg);
        width:6px;height:6px;border-right:2px solid #fff;border-bottom:2px solid #fff;z-index:1;pointer-events:none;
    }

    /* Type-gated thumbnail upload: choose an extension, then the file */
    .file-picker{display:flex;align-items:center;gap:10px;flex-wrap:wrap;max-width:100%;}
    .file-type-select{border:1.5px solid #c9ccdb;border-radius:8px;background:#fff;color:var(--ink);
        font-size:12px;font-weight:700;padding:9px 26px 9px 12px;cursor:pointer;width:auto;min-width:0;
        -webkit-appearance:none;-moz-appearance:none;appearance:none;
        background-image:url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2313176b' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 9px center;background-size:11px 11px;}
    .file-type-select::-ms-expand{display:none;}
    .file-type-select:focus{outline:none;border-color:var(--navy,#13176b);}
    /* pointer-events:none is what actually blocks the click â€” a disabled
       <input> inside a <label> still lets it through in some browsers. */
        .file-row.is-disabled{cursor:not-allowed;pointer-events:none;}
    .file-row.is-disabled .file-name{color:#aab0c0;}
    .thumb-current{margin-top:10px;font-size:12.5px;color:var(--muted);}
    .thumb-current strong{color:var(--ink);}
    .file-row{display:flex;align-items:center;border:1.5px solid #c9ccdb;border-radius:8px;overflow:hidden;max-width:300px;}
    .file-row .file-btn{background:#f3f4f6;border:none;border-right:1.5px solid #c9ccdb;padding:9px 14px;font-size:12px;font-weight:700;color:var(--ink);white-space:nowrap;}
    .file-row .file-name{padding:9px 14px;font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .file-row input[type=file]{display:none;}

    /* Settings row */
    .settings-row{display:flex;align-items:center;gap:34px;flex-wrap:wrap;}
    .radio-item{display:flex;align-items:center;gap:10px;font-size:12px;font-weight:700;color:var(--ink);cursor:pointer;}
    .radio-item input{display:none;}
    .radio-dot{width:18px;height:18px;border-radius:50%;border:1.5px solid #c9ccdb;background:#fff;flex-shrink:0;position:relative;}
    .radio-item input:checked + .radio-dot{background:var(--green);border-color:var(--green);}
    .settings-label{font-size:12px;font-weight:800;color:var(--navy);}

    /* Actions */
    .form-actions{display:flex;gap:14px;}
    .btn-save{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 36px;border-radius:999px;font-size:15px;}
    .btn-cancel{background:#fff;border:1.5px solid #c9ccdb;color:var(--muted);font-weight:700;padding:12px 30px;border-radius:999px;font-size:15px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .field-grid{grid-template-columns:1fr;}
    }

    /* â”€â”€ Subject is Related on â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .related-wrap{position:relative;}
    .related-none-note{margin:0 0 6px;font-size:11.5px;color:#9aa0b8;}
    .related-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
    .related-chip{background:#eef1fb;color:#13176b;border:1px solid #c9cdf0;
        border-radius:999px;padding:3px 11px;font-size:11.5px;font-weight:700;}
    .related-mini{
        display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;min-height:43px;
        background:#fff;border:1.5px solid #dfe3f3;border-radius:999px;
        padding:12px 20px;font-size:13px;font-weight:600;font-family:inherit;
        color:#6b7280;cursor:pointer;transition:border-color .18s,color .18s,background .18s;
    }
    .related-mini:hover{border-color:#13176b;color:#13176b;}
    .related-mini svg{width:15px;height:15px;}
    .related-mini.has-value{background:#13176b;border-color:#13176b;color:#fff;}

    /* Centred modal. The parent .form-card uses overflow:hidden, which
       clipped the old absolutely-positioned panel; position:fixed escapes
       that entirely and matches the About Me dialog students see. */
    .related-backdrop{
        position:fixed;inset:0;z-index:1200;
        background:rgba(9,12,45,.45);
    }
    .related-backdrop[hidden]{display:none;}

    .related-pop{
        position:fixed;z-index:1201;
        top:50%;left:50%;transform:translate(-50%,-50%);
        width:min(460px,calc(100vw - 32px));
        max-height:min(82vh,640px);
        display:flex;flex-direction:column;
        background:#fff;border:1.5px solid #e6e3f7;border-radius:20px;
        box-shadow:0 30px 70px rgba(9,12,45,.38);padding:20px;
    }
    .related-pop[hidden]{display:none;}
    .related-pop-hd{margin-bottom:12px;flex-shrink:0;}
    .related-pop-hd strong{display:block;font-size:15px;color:#13176b;}
    .related-pop-hd span{display:block;font-size:11.5px;color:#8a8fa8;margin-top:2px;line-height:1.4;}

    .related-search{position:relative;margin-bottom:10px;flex-shrink:0;}
    .related-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#8a8fa8;}
    .related-search input{
        width:100%;border:1.5px solid #e6e3f7;border-radius:999px;
        padding:9px 32px 9px 34px;font-size:13.5px;color:#2b2f4a;outline:none;
    }
    .related-search input:focus{border-color:#13176b;}
    .related-clear{
        position:absolute;right:8px;top:50%;transform:translateY(-50%);
        border:none;background:transparent;color:#8a8fa8;font-size:19px;
        line-height:1;cursor:pointer;padding:0 6px;
    }
    .related-clear:hover{color:#13176b;}

    .related-selected-line{
        display:flex;align-items:center;justify-content:space-between;gap:8px;
        font-size:11.5px;color:#8a8fa8;margin-bottom:8px;flex-shrink:0;
    }
    .related-clearall{border:none;background:transparent;color:#13176b;font-size:11.5px;
        font-weight:700;cursor:pointer;padding:0;}
    .related-clearall:hover{text-decoration:underline;}

    .related-group[hidden]{display:none;}
    .related-group-hd{
        font-size:10.5px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;
        color:#a8adc4;margin:10px 0 6px 6px;
    }
    .related-group:first-child .related-group-hd{margin-top:2px;}
    .related-empty{padding:18px 6px;text-align:center;color:#8a8fa8;font-size:12.5px;}
    .related-opt[hidden]{display:none;}

    .related-list{flex:1 1 auto;min-height:0;overflow-y:auto;display:flex;
        flex-direction:column;gap:10px;padding:2px 2px 4px;}
    .related-opt{
        position:relative;
        display:flex;align-items:center;gap:12px;cursor:pointer;
        background:#fff;border:1px solid #eceaf8;border-radius:999px;
        padding:11px 16px;font-size:13.5px;color:#2b2f4a;
        transition:border-color .15s,box-shadow .15s;
    }
    .related-opt:hover{border-color:#c9c4ee;}
    .related-opt input{position:absolute;opacity:0;pointer-events:none;}
    .related-tick{
        width:19px;height:19px;border-radius:50%;flex-shrink:0;
        border:1.5px solid #d5d2e8;background:#fff;
        display:inline-flex;align-items:center;justify-content:center;
        transition:background .15s,border-color .15s;
    }
    .related-tick svg{width:11px;height:11px;color:#fff;opacity:0;}
    .related-opt.on{border-color:#c9c4ee;box-shadow:0 1px 4px rgba(19,23,107,.06);}
    .related-opt.on .related-tick{background:#13176b;border-color:#13176b;}
    .related-opt.on .related-tick svg{opacity:1;}
    .related-name{font-weight:600;}

    .related-pop-ft{display:flex;gap:8px;justify-content:flex-end;margin-top:14px;flex-shrink:0;padding-top:14px;border-top:1px solid #eceaf8;}
    .related-btn{
        border:1.5px solid #dfe3f3;background:#fff;color:#13176b;
        border-radius:10px;padding:8px 16px;font-size:13.5px;font-weight:700;cursor:pointer;
    }
    .related-btn:hover{background:#f3f6ff;}
    .related-btn.primary{background:#13176b;border-color:#13176b;color:#fff;}

    @media (max-width:520px){ .related-pop{padding:16px;border-radius:16px;} }

    /* Simplified course-creation UI */
    .main{padding:28px 34px 56px;max-width:1160px;}
    .build-shell{display:block;max-width:980px;}
    .page-head{margin-bottom:20px;}
    .page-head h2{font-size:24px;font-weight:750;}
    .page-head p{font-weight:400;color:#667085;}
    .sidebar{margin:18px 8px 18px 18px;border-radius:8px;box-shadow:none;padding:18px 12px;}
    .side-link{border-radius:5px;padding:10px 11px;font-size:14px;}
    .side-icon-box{width:30px;height:30px;border-radius:5px;}
    .form-card{border-radius:7px;box-shadow:none;margin-bottom:18px;}
    .form-card-head{padding:13px 18px;background:#fff;}
    .form-card-head h3{font-size:16px;font-weight:750;}
    .form-card-body{padding:18px;}
    .field{margin-bottom:16px;}
    .field label{font-size:12px;margin-bottom:6px;}
    .field-grid{gap:16px;}
    .input,.select,.textarea{border:1px solid #cfd6e2;border-radius:5px;padding:9px 11px;font-size:13px;font-weight:400;min-height:40px;}
    .textarea{border-radius:5px;min-height:100px;}
    .select{padding-right:34px;}
    .select-wrap::after{right:10px;width:16px;height:16px;border-radius:3px;background:#13176b;}
    .select-wrap::before{right:15px;width:5px;height:5px;}
    .competency-entry{gap:7px;}
    .competency-add{border-radius:5px;padding:9px 13px;}
    .competency-chip{border-radius:4px;padding:5px 7px 5px 9px;}
    .form-actions{gap:8px;}
    .btn-save,.btn-cancel{border-radius:5px;padding:9px 18px;font-size:13px;}
    .btn-save{background:#13176b;}
    .btn-cancel{background:#fff;border:1px solid #cfd6e2;color:#344054;}
    .file-type-select,.file-row{border-radius:5px;}
    .related-pop{border-radius:6px;box-shadow:0 8px 22px rgba(16,24,40,.12);}
    #back-to-top-btn{border-radius:5px;box-shadow:none;width:40px;height:40px;}
    @media(max-width:900px){.main{padding:22px 18px 48px}.field-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

@include('components.authenticated-topbar')

<div class="layout faculty-sidebar-layout">

    {{-- Sidebar â€” Dashboard & My Courses connected, Create Courses is this page --}}
    @include('components.faculty-sidebar')

    {{-- Main content --}}
    <main class="main">
    <div class="build-shell">
            <div class="build-form">
                @if (($editing ?? false) && ($course ?? null))
                    @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
                        ['label' => 'My Courses', 'url' => route('faculty.courses')],
                        ['label' => $course->title ?? 'Course', 'url' => route('faculty.courses.manage', $course->id)],
                        ['label' => 'Edit Course'],
                    ]])
                @endif

        <div class="page-head">
            <div class="page-head__row">
                <div>
                    <h2>{{ ($editing ?? false) ? 'Edit Course' : 'Create New Course' }}</h2>
                    <p>{{ ($editing ?? false)
                            ? 'Update the details below. Re-submitting sends the course back for admin approval.'
                            : 'Fill in the Details below to create your course' }}</p>
                </div>

                {{-- Certificate and badge builders. Both need a saved course
                     to attach to, so on a brand-new course they explain that
                     rather than linking nowhere. --}}

            </div>
        </div>

        {{-- âœ… Real form â€” Save stores the course (session for now) and
             redirects to the Managing Course screen for the new course --}}
        @php $isEdit = ($editing ?? false) && ($course ?? null); @endphp

        <form method="POST"
              action="{{ $isEdit ? route('faculty.courses.update', $course->id) : route('faculty.create.store') }}"
              enctype="multipart/form-data" id="create-course-form">
            @csrf
            @if ($isEdit) @method('PATCH') @endif

        {{-- Validation / save errors â€” only visible when something went wrong --}}
        @if ($errors->any())
        <div style="background:#fdecea;border:1px solid #f2b8b5;color:#a93226;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:14px;font-weight:600;">
            <strong>The course couldn't be saved. Please fix the following:</strong>
            <ul style="margin:8px 0 0 20px;font-weight:500;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- â”€â”€ Basic Information â”€â”€ --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Basic Information</h3></div>
            <div class="form-card-body">

                <div class="field">
                    <label>Course Title <span class="req">*</span></label>
                    <input class="input" type="text" name="title" id="courseTitleInput" placeholder="e.g. Introduction to Web Development" value="{{ old('title', $course->title ?? '') }}" required>
                </div>


                @php
                    $skillList     = $skillOptions ?? [];
                    $relatedSaved = old('related_skills');
                    if (! is_array($relatedSaved)) {
                        // Decoded defensively: works whether or not the Course
                        // model casts related_skills to an array.
                        $relatedSaved = ($course ?? null)
                            ? \App\Http\Controllers\FacultyController::relatedSkillsOf($course)
                            : [];
                    }
                @endphp

                <div class="field field-grid">
                    <div>
                        <label>Category <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select class="select" name="category">
                                <option value="">Select a Category</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category }}" @selected(old('category', $course->category ?? '') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Subject is Related on â€” internal only. Drives the
                         Recommended toggle on the student Browse page; never
                         rendered on any student-facing screen. --}}
                    <div>
                        <label>Subject is Related on</label>
                        {{-- Mirrors the ticked boxes as plain text. The checkbox
                             array is still posted; this guarantees the value
                             survives even if the boxes are not submitted. --}}
                        <input type="hidden" name="related_skills_csv" id="relatedCsv"
                               value="{{ implode('||', $relatedSaved) }}">

                        @if (($editing ?? false) && empty($relatedSaved))
                            <p class="related-none-note">
                                Nothing saved for this course yet.
                            </p>
                        @endif

                        <div class="related-wrap" id="relatedWrap">
                            <button type="button" class="related-mini {{ count($relatedSaved) ? 'has-value' : '' }}"
                                    id="relatedMini" onclick="openRelated()">
                                <span id="relatedMiniLabel">
                                    {{ count($relatedSaved) ? count($relatedSaved) . ' selected' : 'None' }}
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>

                            <div class="related-backdrop" id="relatedBackdrop" hidden></div>

                            <div class="related-pop" id="relatedPop" hidden role="dialog"
                                 aria-modal="true" aria-label="Related skills">
                                <div class="related-pop-hd">
                                    <strong>Related skills</strong>
                                    <span>Used to recommend this course to students</span>
                                </div>

                                <div class="related-search">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                                    </svg>
                                    <input type="text" id="relatedSearch" autocomplete="off"
                                           placeholder="Search {{ count($skillList) }} skills..."
                                           oninput="filterRelated(this.value)">
                                    <button type="button" class="related-clear" onclick="clearRelatedSearch()"
                                            title="Clear search">&times;</button>
                                </div>

                                <div class="related-selected-line">
                                    <span id="relatedCountLine">No skills selected</span>
                                    <button type="button" class="related-clearall" onclick="clearRelatedAll()">Clear all</button>
                                </div>

                                <div class="related-list" id="relatedList">
                                    @foreach (($skillGroups ?? []) as $groupName => $groupSkills)
                                        <div class="related-group" data-group="{{ $groupName }}">
                                            <div class="related-group-hd">{{ $groupName }}</div>
                                            @foreach ($groupSkills as $skill)
                                                <label class="related-opt {{ in_array($skill, $relatedSaved) ? 'on' : '' }}"
                                                       data-skill="{{ strtolower($skill) }}"
                                                       data-groupname="{{ strtolower($groupName) }}">
                                                    <input type="checkbox" name="related_skills[]"
                                                           value="{{ $skill }}"
                                                           @checked(in_array($skill, $relatedSaved))>
                                                    <span class="related-tick">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2">
                                                            <path d="M5 12.5l5 5 9-10"/>
                                                        </svg>
                                                    </span>
                                                    <span class="related-name">{{ $skill }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <div class="related-empty" id="relatedEmpty" hidden>No skills match that search.</div>
                                </div>

                                <div class="related-pop-ft">
                                    <button type="button" class="related-btn" onclick="cancelRelated()">Cancel</button>
                                    <button type="button" class="related-btn primary" onclick="saveRelated()">Save</button>
                                </div>
                            </div>
                        </div>

                        @if (count($relatedSaved))
                            <div class="related-chips" id="relatedChips">
                                @foreach ($relatedSaved as $skill)
                                    <span class="related-chip">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="field field-grid">
                    <div>
                        <label>Competencies</label>
                        @php
                            $savedCompetencies = old('competencies', isset($course) && $course ? ($course->skills ?? []) : []);
                            $savedCompetencies = is_array($savedCompetencies) ? $savedCompetencies : [];
                        @endphp
                        <div class="competency-entry">
                            <input class="input" type="text" id="competencyInput" placeholder="Enter a competency">
                            <button type="button" class="competency-add" id="addCompetency">Add</button>
                        </div>
                        <div class="competency-list" id="competencyList">
                            @foreach ($savedCompetencies as $competency)
                                <span class="competency-chip"><input type="hidden" name="competencies[]" value="{{ $competency }}"><span>{{ $competency }}</span><button type="button" class="competency-remove" aria-label="Remove {{ $competency }}">&times;</button></span>
                            @endforeach
                        </div>
                        <small style="display:block;margin-top:7px;color:#6b7280;font-size:11.5px;">Add competencies one at a time.</small>
                    </div>
                    <div>
                        <label>PQF Level Alignment</label>
                        <div class="select-wrap">
                            <select class="select" name="pqf_level">
                                <option value="">Select PQF Level</option>
                                @foreach ([5,6,7,8] as $pqfLevel)
                                    <option value="{{ $pqfLevel }}" @selected((string) old('pqf_level', $course->pqf_level ?? '') === (string) $pqfLevel)>PQF Level {{ $pqfLevel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label>Level <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select class="select" name="level">
                                @foreach ($levels ?? ['Beginner'] as $level)
                                    <option value="{{ $level }}" @selected(old('level', $course->level ?? 'Beginner') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Description <span class="req">*</span></label>
                    @include('components.rich-text-editor', ['name' => 'description', 'id' => 'course-description-editor', 'value' => old('description', $course->description ?? ''), 'placeholder' => 'Describe the micro-credential, its purpose, and what learners will achieve.'])
                </div>

                <div class="field field-grid">
                    <div>
                        <label>Duration (Hours)</label>
                        <input class="input" type="number" name="duration" placeholder="0" min="0" value="{{ old('duration', isset($course) && $course ? (float) rtrim($course->duration ?? '', 'h') : '') }}">
                    </div>
                    <div>
                        <label>Passing Score</label>
                        <input class="input" type="number" name="passing_score" placeholder="75" min="0" max="100" value="{{ old('passing_score', $course->passing_score ?? '') }}">
                    </div>
                </div>

            </div>
        </section>

        {{-- â”€â”€ Thumbnail â”€â”€ --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Thumbnail</h3></div>
            <div class="form-card-body">
                {{-- Pick the image TYPE first; the picker then accepts only
                     that type. Uploaded by FacultyController::storeThumbnail()
                     into public/uploads/thumbnails. --}}
                <div class="file-picker">
                    <select class="file-type-select" id="thumbType" onchange="thumbTypeChanged()">
                        <option value="">Select file type...</option>
                        <option value="JPG">JPG / JPEG</option>
                        <option value="PNG">PNG</option>
                        <option value="WEBP">WEBP</option>
                        <option value="GIF">GIF</option>
                    </select>
                    <label class="file-row is-disabled" id="thumbRow">
                        <span class="file-btn">Choose File</span>
                        <span class="file-name" id="thumbName">Select a file type first</span>
                        <input type="file" name="thumbnail" id="thumbInput" disabled
                               onchange="thumbFileChanged(this)">
                    </label>
                </div>

                @if (($editing ?? false) && ($course->thumbnail_url ?? null))
                    <div class="thumb-current">
                        Current thumbnail: <strong>{{ basename($course->thumbnail_url) }}</strong>
                        &mdash; leave the picker empty to keep it.
                    </div>
                @endif
            </div>
        </section>

        {{-- â”€â”€ Learning Details â”€â”€ --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Learning Details</h3></div>
            <div class="form-card-body">

                <div class="field">
                    <label>Learning Objectives</label>
                    <textarea class="textarea" name="learning_objectives">{{ old('learning_objectives', isset($course) && $course ? implode("\n", $course->objectives ?? []) : '') }}</textarea>
                </div>

                {{-- #7 â€” Prerequisites are COURSES already live on the site.
                     (This used to list academic terms, which was a leftover
                     from the Term dropdown.) --}}
                @php
                    $selectedPrereqs = collect(old('prerequisite_ids', $course->prerequisite_ids ?? []))
                        ->map(fn ($v) => (int) $v)->all();
                @endphp
                <div class="field">
                    <label>Prerequisites (if any)</label>
                    <p style="margin:0 0 10px;color:#6b7280;font-size:12.5px;">
                        Courses a student should complete before taking this one.
                    </p>

                    @if (($prereqOptions ?? collect())->isEmpty())
                        <div style="border:1.5px dashed #c9ccdb;border-radius:12px;padding:16px;
                                    color:#6b7280;font-size:13px;">
                            No other published courses are available yet.
                        </div>
                    @else
                        <input type="text" id="prereq-search" placeholder="Search courses..."
                               style="width:100%;max-width:420px;border:1.5px solid #c9ccdb;border-radius:10px;
                                      padding:10px 13px;font-size:13.5px;font-family:inherit;margin-bottom:10px;">
                        <div id="prereq-list"
                             style="max-height:220px;overflow-y:auto;border:1.5px solid #c9ccdb;
                                    border-radius:12px;padding:8px;background:#fff;">
                            @foreach ($prereqOptions as $opt)
                                <label class="prereq-item"
                                       data-name="{{ strtolower($opt->title . ' ' . $opt->category) }}"
                                       style="display:flex;align-items:center;gap:10px;padding:9px 10px;
                                              border-radius:8px;cursor:pointer;font-size:13.5px;">
                                    <input type="checkbox" name="prerequisite_ids[]" value="{{ $opt->id }}"
                                           style="width:16px;height:16px;accent-color:#13176b;"
                                           @checked(in_array($opt->id, $selectedPrereqs, true))>
                                    <span style="font-weight:700;">{{ $opt->title }}</span>
                                    @if ($opt->category)
                                        <span style="color:#6b7280;font-size:12px;"> - {{ $opt->category }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </section>

        {{-- Badge configuration is part of course initialization. Certificates are automatic. --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Digital Badge</h3></div>
            <div class="form-card-body">
                @include('components.inline-badge-builder', ['course' => $course ?? null])
                <div class="auto-credential-note"><strong>Certificate of Completion:</strong> automatically issued after all required learning, mastery, faculty verification (if enabled), and academic-unit approval are satisfied. No separate certificate customization step is required.</div>
            </div>
        </section>

        {{-- Completion Requirements --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Completion Requirements</h3></div>
            <div class="form-card-body">
                <div class="field">
                    <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;">
                        <input type="hidden" name="requires_faculty_verification" value="0">
                        <input type="checkbox" name="requires_faculty_verification" value="1" style="width:16px;height:16px;accent-color:#13176b;" @checked(old('requires_faculty_verification', $course->requires_faculty_verification ?? false))>
                        Require faculty verification before this microcredential is officially completed
                    </label>
                    <small class="field-hint">When enabled, the assigned faculty member must verify the learner before the badge and automatic certificate are released.</small>
                </div>
            </div>
        </section>

        {{-- â”€â”€ Settings â”€â”€ --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Settings</h3></div>
            <div class="form-card-body">
                <div class="settings-row">
                    <span class="settings-label">Status</span>

                    <label class="radio-item">
                        <input type="radio" name="status" value="draft" @checked(old('status', ($course->approval_status ?? '') === 'draft' ? 'draft' : 'submit') === 'draft')>
                        <span class="radio-dot"></span>
                        Draft
                    </label>

                    <label class="radio-item">
                        <input type="radio" name="status" value="submit" @checked(old('status', ($course->approval_status ?? '') === 'draft' ? 'draft' : 'submit') === 'submit')>
                        <span class="radio-dot"></span>
                        Submit for approval
                    </label>
                </div>

                @if ($editing ?? false)
                    {{-- #12 â€” tell the admin what changed, so they can review
                         the difference rather than the whole course again. --}}
                    <div class="field" style="margin-top:18px;">
                        <label>What did you change? (shown to the admin on review)</label>
                        <textarea class="textarea" name="change_note" rows="4"
                                  placeholder="e.g. Rewrote Module 2 lessons and lowered the passing score to 70%.">{{ old('change_note', $course->change_note ?? '') }}</textarea>
                    </div>
                @endif
            </div>
        </section>

        {{-- âœ… Save submits the form â†’ Managing Course screen for the new
             course Â· Cancel returns to the dashboard without saving --}}
        <div class="form-actions">
            <button class="btn-save" type="submit">{{ ($editing ?? false) ? 'Save Changes' : 'Save' }}</button>
            <a href="{{ ($editing ?? false) && ($course ?? null)
                            ? route('faculty.courses.manage', $course->id)
                            : route('faculty.dashboard') }}"><button class="btn-cancel" type="button">Cancel</button></a>
        </div>

        </form>

      </div>{{-- /.build-form --}}



    </div>{{-- /.build-shell --}}
    </main>


</div>


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

<script>
    (function () {
        var input = document.getElementById('competencyInput');
        var addButton = document.getElementById('addCompetency');
        var list = document.getElementById('competencyList');
        if (!input || !addButton || !list) return;

        function addCompetency() {
            var value = input.value.trim();
            if (!value) return;
            var existing = Array.prototype.slice.call(list.querySelectorAll('input[name="competencies[]"]'));
            if (existing.some(function (field) { return field.value.toLowerCase() === value.toLowerCase(); })) {
                input.value = '';
                return;
            }

            var chip = document.createElement('span');
            chip.className = 'competency-chip';
            chip.innerHTML = '<input type="hidden" name="competencies[]"><span></span><button type="button" class="competency-remove" aria-label="Remove competency">&times;</button>';
            chip.querySelector('input').value = value;
            chip.querySelector('span').textContent = value;
            chip.querySelector('button').addEventListener('click', function () { chip.remove(); });
            list.appendChild(chip);
            input.value = '';
            input.focus();
        }

        addButton.addEventListener('click', addCompetency);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addCompetency();
            }
        });
        list.querySelectorAll('.competency-remove').forEach(function (button) {
            button.addEventListener('click', function () { button.closest('.competency-chip').remove(); });
        });
    })();

    // â”€â”€ "Subject is Related on" picker â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //    Cancel restores whatever was ticked when the panel was opened, so
    //    a course that was saved with skills keeps them; a brand-new course
    //    falls back to None.
    var _relatedSnapshot = [];

    function relatedBoxes() {
        return Array.prototype.slice.call(
            document.querySelectorAll('#relatedPop input[name="related_skills[]"]')
        );
    }

    function syncRelatedRows() {
        relatedBoxes().forEach(function (box) {
            box.closest('.related-opt').classList.toggle('on', box.checked);
        });
    }

    /* A handful of skills sit under two fields (Statistics in Economics and
       Mathematics, Therapy in Nutrition and Social Work, and so on). Ticking
       one copy ticks the other, so the count and the rows always agree. */
    function mirrorRelated(box) {
        relatedBoxes().forEach(function (other) {
            if (other !== box && other.value === box.value) {
                other.checked = box.checked;
            }
        });
    }

    function refreshRelatedLabel() {
        // Distinct values â€” a skill listed under two fields must count once.
        var picked = {};
        relatedBoxes().forEach(function (b) { if (b.checked) picked[b.value] = true; });
        var count = Object.keys(picked).length;

        // Mirror into the hidden text field that always posts.
        var csv = document.getElementById('relatedCsv');
        if (csv) csv.value = Object.keys(picked).join('||');
        var mini  = document.getElementById('relatedMini');
        var label = document.getElementById('relatedMiniLabel');
        var line  = document.getElementById('relatedCountLine');

        if (line) {
            line.textContent = count
                ? count + (count === 1 ? ' skill selected' : ' skills selected')
                : 'No skills selected';
        }
        if (!mini || !label) return;

        label.textContent = count ? count + ' selected' : 'None';
        mini.classList.toggle('has-value', count > 0);
    }

    /* Filter by skill name or group name. Ticked skills always stay
       visible, so you never lose track of a selection while searching. */
    function filterRelated(term) {
        term = (term || '').trim().toLowerCase();

        var anyVisible = false;

        document.querySelectorAll('#relatedList .related-group').forEach(function (group) {
            var groupVisible = false;

            group.querySelectorAll('.related-opt').forEach(function (opt) {
                var box     = opt.querySelector('input');
                var name    = opt.getAttribute('data-skill') || '';
                var gname   = opt.getAttribute('data-groupname') || '';
                var matches = !term
                    || name.indexOf(term) !== -1
                    || gname.indexOf(term) !== -1
                    || (box && box.checked);

                opt.hidden = !matches;
                if (matches) groupVisible = true;
            });

            group.hidden = !groupVisible;
            if (groupVisible) anyVisible = true;
        });

        var empty = document.getElementById('relatedEmpty');
        if (empty) empty.hidden = anyVisible;
    }

    function clearRelatedSearch() {
        var input = document.getElementById('relatedSearch');
        if (input) { input.value = ''; input.focus(); }
        filterRelated('');
    }

    function clearRelatedAll() {
        relatedBoxes().forEach(function (b) { b.checked = false; });
        syncRelatedRows();
        refreshRelatedLabel();
        filterRelated(document.getElementById('relatedSearch')?.value || '');
    }

    function openRelated() {
        var pop  = document.getElementById('relatedPop');
        var back = document.getElementById('relatedBackdrop');
        if (!pop) return;

        // Remember the current state so Cancel can put it back.
        _relatedSnapshot = relatedBoxes().filter(function (b) { return b.checked; })
                                         .map(function (b) { return b.value; });
        pop.hidden = false;
        if (back) back.hidden = false;

        // Stop the page behind the modal from scrolling.
        document.body.style.overflow = 'hidden';

        syncRelatedRows();
        clearRelatedSearch();

        var input = document.getElementById('relatedSearch');
        if (input) input.focus();
    }

    function closeRelated() {
        var pop  = document.getElementById('relatedPop');
        var back = document.getElementById('relatedBackdrop');
        if (pop)  pop.hidden = true;
        if (back) back.hidden = true;
        document.body.style.overflow = '';
    }

    function saveRelated() {
        refreshRelatedLabel();
        closeRelated();
    }

    function cancelRelated() {
        var pop = document.getElementById('relatedPop');
        if (!pop || pop.hidden) return;   // nothing open â†’ nothing to undo

        relatedBoxes().forEach(function (box) {
            box.checked = _relatedSnapshot.indexOf(box.value) !== -1;
        });
        syncRelatedRows();
        refreshRelatedLabel();
        closeRelated();
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncRelatedRows();
        refreshRelatedLabel();

        // Final sync immediately before the form posts.
        var courseForm = document.getElementById('create-course-form');
        if (courseForm) {
            courseForm.addEventListener('submit', function () { refreshRelatedLabel(); });
        }

        // Tick rows visually as they change
        relatedBoxes().forEach(function (box) {
            box.addEventListener('change', function () {
                mirrorRelated(box);
                syncRelatedRows();
                refreshRelatedLabel();
            });
        });

        // Clicking the backdrop behaves like Cancel
        var back = document.getElementById('relatedBackdrop');
        if (back) back.addEventListener('click', cancelRelated);

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;

            var pop = document.getElementById('relatedPop');
            if (pop && !pop.hidden) cancelRelated();
        });
    });

    // â”€â”€ Draft persistence: keep filled fields when leaving & returning â”€â”€
    (function () {
        var form = document.getElementById('create-course-form');
        if (!form) return;

        // Editing an existing course: the fields are already populated from
        // the database. Restoring a stale "new course" draft over the top
        // would silently replace the real values.
        var IS_EDIT = {{ ($editing ?? false) ? 'true' : 'false' }};
        if (IS_EDIT) return;

        var KEY = 'upskill_create_course_draft';

        // A previously abandoned draft repopulates Title/Category/Term/Level
        // but knows nothing about the skills picker, which makes a fresh
        // Create form look like a half-loaded Edit form. Drop drafts older
        // than a day so that cannot happen silently.
        try {
            var stamp = parseInt(localStorage.getItem(KEY + '_at') || '0', 10);
            if (stamp && Date.now() - stamp > 86400000) {
                localStorage.removeItem(KEY);
                localStorage.removeItem(KEY + '_at');
            }
        } catch (e) {}

        // Restore saved values on load (server-rendered old() values win when
        // the form bounced back from a validation error).
        try {
            var saved = JSON.parse(localStorage.getItem(KEY) || '{}');
            var hasServerValues = {{ $errors->any() ? 'true' : 'false' }};
            if (!hasServerValues) {
                Object.keys(saved).forEach(function (name) {
                    var field = form.querySelector('[name="' + name + '"]');
                    if (!field) return;
                    if (field.type === 'radio') {
                        var r = form.querySelector('[name="' + name + '"][value="' + saved[name] + '"]');
                        if (r) r.checked = true;
                    } else if (field.type !== 'file') {
                        field.value = saved[name];
                    }
                });
            }
        } catch (e) {}

        // Save every change.
        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);
        function saveDraft() {
            var data = {};
            form.querySelectorAll('input, select, textarea').forEach(function (f) {
                if (!f.name || f.type === 'file' || f.type === 'password') return;
                if (f.type === 'radio') { if (f.checked) data[f.name] = f.value; return; }
                if (f.type === 'checkbox') { data[f.name] = f.checked ? f.value : ''; return; }
                data[f.name] = f.value;
            });
            try {
                localStorage.setItem(KEY, JSON.stringify(data));
                localStorage.setItem(KEY + '_at', String(Date.now()));
            } catch (e) {}
        }

        // Clear the draft once the course is actually created.
        form.addEventListener('submit', function () {
            try {
                localStorage.removeItem(KEY);
                localStorage.removeItem(KEY + '_at');
            } catch (e) {}
        });
    })();
</script>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
<script>
    // Filter the prerequisite course list as you type.
    (function () {
        var box = document.getElementById('prereq-search');
        if (!box) return;
        box.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            document.querySelectorAll('#prereq-list .prereq-item').forEach(function (row) {
                row.style.display = !q || row.dataset.name.indexOf(q) !== -1 ? 'flex' : 'none';
            });
        });
    })();
</script>
<script>
    // Thumbnail picker: the file input stays locked until an image type is
    // chosen, then only accepts that extension.
    var THUMB_TYPES = {
        JPG:  ['jpg', 'jpeg'],
        PNG:  ['png'],
        WEBP: ['webp'],
        GIF:  ['gif']
    };

    function thumbTypeChanged() {
        var sel   = document.getElementById('thumbType');
        var input = document.getElementById('thumbInput');
        var row   = document.getElementById('thumbRow');
        var span  = document.getElementById('thumbName');
        if (!sel || !input || !row || !span) return;

        var exts = THUMB_TYPES[sel.value];

        // Switching type always clears the choice, so a PNG picked under
        // "PNG" cannot survive a switch to "GIF".
        input.value = '';

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

    function thumbFileChanged(input) {
        var span = document.getElementById('thumbName');
        var sel  = document.getElementById('thumbType');
        if (!span) return;

        if (!input.files || !input.files.length) {
            span.textContent = sel && sel.value ? 'No File Chosen' : 'Select a file type first';
            return;
        }

        var name = input.files[0].name;
        var ext  = (name.split('.').pop() || '').toLowerCase();
        var exts = sel ? THUMB_TYPES[sel.value] : null;

        if (exts && exts.indexOf(ext) === -1) {
            alert('"' + name + '" is not a ' + sel.options[sel.selectedIndex].text + ' file.\n\n'
                + 'Choose a file ending in ' + exts.map(function (e) { return '.' + e; }).join(' or ')
                + ', or change the file type above.');
            input.value = '';
            span.textContent = 'No File Chosen';
            return;
        }

        span.textContent = name;
    }
</script>
</body>
</html>
