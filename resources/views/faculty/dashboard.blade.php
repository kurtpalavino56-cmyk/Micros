{{--
    resources/views/faculty/dashboard.blade.php

    Faculty Dashboard â€” single self-contained Blade view (no layout file).
    âš  STANDALONE: the ONLY connection on this page is the sidebar
      "My Courses" link â†’ route('faculty.courses'). Every other button /
      link is intentionally non-functioning (href="#" or plain
      <button type="button">) until the real routes are wired up.

    Expected data from the route, e.g.:

    return view('faculty.dashboard', [
        'user'  => $user,                  // needs ->name (avatar optional)
        'stats' => [
            'total_courses'  => 5,
            'published'      => 3,
            'total_students' => 5,
            'enrollments'    => 7,
        ],
        'courses' => $facultyCourses,      // collection of the faculty's courses
    ]);

    Each $course is expected to expose:
        ->title, ->status ('Published' | 'Draft'), ->students_count,
        ->modules_count, ->thumbnail_url
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Dashboard | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
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
    .brand .logo svg{width:30px;height:30px;}
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
    .main{padding:32px 36px 60px;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 24px;border-radius:999px;font-size:15px;}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:36px;}
    /* Gradient stat cards (same design as Faculty Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:44px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Content grid */
    .content-grid{display:grid;grid-template-columns:1fr 360px;gap:28px;}
    .courses-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
    .courses-head h3{font-size:24px;margin:0;color:var(--navy);}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;align-items:center;gap:18px;margin-bottom:20px;}
    .thumb{width:78px;height:78px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h4{margin:0 0 10px;font-size:17px;color:var(--navy);}
    .course-meta{display:flex;align-items:center;gap:22px;flex-wrap:wrap;}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:6px 18px;border-radius:999px;}
    .status-pill.published{background:#86efac;color:#14532d;}
    .status-pill.draft{background:#fde68a;color:#713f12;}
    .meta-item{text-align:center;font-size:12px;color:var(--muted);line-height:1.3;}
    .meta-item .meta-num{display:block;font-weight:800;font-size:15px;color:var(--navy);}
    .btn-manage{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:10px;font-size:15px;flex-shrink:0;}
    .card-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .card-actions a{display:inline-flex;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);}

    /* Side panels */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:24px;min-height:200px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:22px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;}
    .panel-body{padding:18px 22px;color:var(--muted);font-size:14px;}
    .quick-actions{display:flex;gap:14px;flex-wrap:wrap;}
    .btn-quick{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:999px;font-size:14px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:column;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .content-grid{grid-template-columns:1fr;}
    }

    /* â”€â”€ Phone: stack the course card instead of letting it wrap â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width:640px){
        .main{padding:20px 14px 48px;}

        .course-card{flex-direction:column;align-items:stretch;gap:14px;padding:16px;}
        .thumb{width:100%;height:140px;}
        .course-info{width:100%;min-width:0;}
        .course-info h4{font-size:17px;line-height:1.3;}
        .course-meta{gap:10px 18px;}
        .meta-item{text-align:left;}

        /* Buttons share the full width instead of trailing off the edge */
        .card-actions{width:100%;gap:10px;}
        .card-actions a{flex:1;}
        .btn-manage{width:100%;padding:10px 12px;font-size:14px;}

        /* Stat tiles stay 2x2 on phones â€” see components/responsive.blade.php */
        .stats{grid-template-columns:repeat(2,1fr);}
        .quick-actions .btn-quick{flex:1 1 auto;}
    }
</style>
<style>
main.faculty-dashboard {
  --dash-navy:#0B1B45; --dash-navy-deep:#071233; --dash-royal:#2447D4;
  --dash-gold:#F4C430; --dash-gold-soft:#FFF5D1; --dash-canvas:#F4F6FB;
  --dash-ink:#0E1A3A; --dash-muted:#64748B; --dash-line:#E6EAF2;
  color:var(--dash-ink);font-family:'Inter',ui-sans-serif,system-ui,sans-serif;
  padding:32px clamp(20px,3vw,44px) 56px;min-width:0;
}
.layout:has(main.faculty-dashboard){background:var(--dash-canvas);}
main.faculty-dashboard h1,main.faculty-dashboard h2,main.faculty-dashboard h3,main.faculty-dashboard h4,
main.faculty-dashboard .num,main.faculty-dashboard .meta-num{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif}
main.faculty-dashboard .page-head{align-items:center;margin-bottom:24px}
main.faculty-dashboard .page-head h2{font-size:27px;font-weight:800;letter-spacing:-.04em;color:var(--dash-ink)}
main.faculty-dashboard .page-head p{font-size:14px;color:var(--dash-muted)}
main.faculty-dashboard .page-head p[style]{display:inline-flex;margin-top:12px!important;padding:6px 11px;border:1px solid var(--dash-line);border-radius:999px;background:#fff;color:var(--dash-navy)!important;font-size:11px}
main.faculty-dashboard .btn-outline{border:0;border-radius:999px;background:var(--dash-gold);padding:12px 20px;color:var(--dash-navy);font-size:13px;font-weight:700;box-shadow:0 7px 20px rgba(244,196,48,.25)}
main.faculty-dashboard .stats{gap:15px;margin-bottom:30px}
main.faculty-dashboard .stat-card{position:relative;min-height:176px;padding:20px 22px;align-items:stretch;text-align:left;border:0;border-radius:18px;color:#fff;box-shadow:0 12px 26px rgba(11,27,69,.12);transition:transform .2s ease,box-shadow .2s ease}
main.faculty-dashboard .stat-card:hover{transform:translateY(-4px);box-shadow:0 18px 32px rgba(11,27,69,.2)}
main.faculty-dashboard .stat-card.c-navy{background:#0B1B45}
main.faculty-dashboard .stat-card.c-gold{background:#F4C430;color:#0B1B45}
main.faculty-dashboard .stat-card.c-cyan{background:#2447D4}
main.faculty-dashboard .stat-card.c-deep{background:#FFF5D1;color:#0B1B45;box-shadow:inset 0 0 0 1px rgba(244,196,48,.35)}
main.faculty-dashboard .stat-card:after{content:"";position:absolute;width:124px;height:124px;right:-35px;top:-44px;border-radius:50%;background:rgba(255,255,255,.11)}
main.faculty-dashboard .stat-top{position:static;display:flex;flex-direction:column;align-items:flex-start;justify-content:flex-start;gap:0;margin:0}
main.faculty-dashboard .stat-top svg{width:38px;height:38px;margin-bottom:18px;padding:9px;border-radius:12px;background:rgba(255,255,255,.12);color:#F4C430}
main.faculty-dashboard .stat-card.c-gold .stat-top svg,main.faculty-dashboard .stat-card.c-deep .stat-top svg{background:rgba(11,27,69,.09);color:#0B1B45}
main.faculty-dashboard .stat-top .num{font-size:38px;color:inherit;line-height:1}
main.faculty-dashboard .stat-card .label{margin-top:8px;font-size:13px;color:inherit;font-weight:600;opacity:.78}
main.faculty-dashboard .content-grid{grid-template-columns:minmax(0,1.7fr) minmax(270px,.8fr);gap:22px}
main.faculty-dashboard .courses-head h3{font-size:19px;font-weight:700;color:var(--dash-ink)}
main.faculty-dashboard .course-card,main.faculty-dashboard .panel{border:1px solid var(--dash-line);border-radius:17px;background:#fff;box-shadow:0 5px 18px rgba(11,27,69,.045);transition:transform .2s ease,box-shadow .2s ease}
main.faculty-dashboard .course-card:hover,main.faculty-dashboard .panel:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(11,27,69,.1)}
main.faculty-dashboard .course-card{gap:14px;padding:15px;margin-bottom:12px}
main.faculty-dashboard .thumb{width:58px;height:58px;border-radius:12px;background-color:#EEF2FF}
main.faculty-dashboard .course-info h4{font-size:13px;font-weight:700;color:var(--dash-ink)}
main.faculty-dashboard .course-meta{gap:14px}
main.faculty-dashboard .status-pill{padding:5px 10px;font-size:10px}
main.faculty-dashboard .status-pill.published{background:rgba(36,71,212,.09);color:var(--dash-royal)}
main.faculty-dashboard .status-pill.draft{background:var(--dash-gold-soft);color:#9A7100}
main.faculty-dashboard .meta-item{font-size:10px;color:var(--dash-muted)}
main.faculty-dashboard .meta-item .meta-num{font-size:12px;color:var(--dash-ink)}
main.faculty-dashboard .btn-manage,main.faculty-dashboard .btn-quick{border:1px solid var(--dash-line);border-radius:999px;padding:9px 14px;background:#fff;color:var(--dash-navy);font-size:11px;font-weight:700}
main.faculty-dashboard .btn-manage{border-color:var(--dash-navy);background:var(--dash-navy);color:#fff}
main.faculty-dashboard .card-actions a:first-child .btn-manage{border-color:#0B1B45;background:#0B1B45;color:#fff}
main.faculty-dashboard .card-actions a:nth-child(2) .btn-manage{border-color:transparent;background:#EEF2FF;color:#2447D4}
main.faculty-dashboard .btn-manage,main.faculty-dashboard .btn-quick,main.faculty-dashboard .btn-outline{transition:transform .18s ease,background-color .18s ease,color .18s ease,box-shadow .18s ease}
main.faculty-dashboard .card-actions a:first-child .btn-manage:hover{background:#1A2F6E;transform:translateY(-1px)}
main.faculty-dashboard .card-actions a:nth-child(2) .btn-manage:hover{background:#2447D4;color:#fff;transform:translateY(-1px)}
main.faculty-dashboard .btn-outline:hover{background:#FFD84D;transform:translateY(-2px);box-shadow:0 10px 24px rgba(244,196,48,.35)}
main.faculty-dashboard .panel{overflow:hidden;margin-bottom:18px;min-height:0}
main.faculty-dashboard .panel-head{padding:15px 18px;border-bottom:1px solid #EEF1F6;background:#fff;color:var(--dash-ink);font-size:13px;font-weight:700}
main.faculty-dashboard .panel-body{padding:17px 18px;color:var(--dash-muted);font-size:12px}
main.faculty-dashboard .enrollment-bars{display:flex;height:112px;align-items:stretch;justify-content:space-between;gap:8px;padding:2px 3px 0}
main.faculty-dashboard .enrollment-month{display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:7px}
main.faculty-dashboard .enrollment-bar{display:block;width:100%;min-height:5px;border-radius:6px 6px 2px 2px;background:linear-gradient(180deg,#5975E8,#2447D4);transition:filter .18s ease}
main.faculty-dashboard .enrollment-month:hover .enrollment-bar{filter:brightness(1.12)}
main.faculty-dashboard .enrollment-month small{color:var(--dash-muted);font-size:10px}
main.faculty-dashboard .enrollment-bars.is-empty{align-items:flex-end}
main.faculty-dashboard .enrollment-bars.is-empty span{display:block;flex:1;border-radius:6px 6px 2px 2px;background:#EEF1F6}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(1){height:30%}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(2){height:55%}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(3){height:40%}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(4){height:70%}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(5){height:45%}
main.faculty-dashboard .enrollment-bars.is-empty span:nth-child(6){height:60%}
main.faculty-dashboard .enrollment-empty-note{display:flex;align-items:center;min-height:54px;margin-top:17px;padding:12px 14px;border-radius:13px;background:rgba(255,245,209,.72);color:rgba(11,27,69,.8);font-size:11px;line-height:1.55}
main.faculty-dashboard .quick-actions{gap:8px}
main.faculty-dashboard .quick-actions{display:flex;flex-direction:column;gap:4px}
main.faculty-dashboard .btn-quick{display:block;width:100%;border-color:transparent;border-radius:12px;background:transparent;padding:12px 13px;text-align:left;color:var(--dash-ink)}
main.faculty-dashboard .btn-quick:hover{background:var(--dash-canvas);border-color:transparent}
@media(max-width:980px){main.faculty-dashboard .content-grid{grid-template-columns:1fr}}
@media(max-width:640px){main.faculty-dashboard{padding:24px 16px 40px}main.faculty-dashboard .stats{gap:10px;margin-bottom:22px}main.faculty-dashboard .stat-card{min-height:142px;padding:14px 12px}main.faculty-dashboard .stat-top svg{width:32px;height:32px;margin-bottom:12px}main.faculty-dashboard .stat-top .num{font-size:25px}main.faculty-dashboard .course-card{padding:13px}}
</style>
</head>
<body>

@include('components.authenticated-topbar')

<div class="layout faculty-sidebar-layout">

    {{-- Sidebar â€” âš  all links are placeholders (href="#"), not wired to routes --}}
    @include('components.faculty-sidebar')

    {{-- Main content --}}
    <main class="main faculty-dashboard">

        <div class="page-head">
            <div>
                <h2>Faculty Dashboard</h2>
                <p>Manage your Courses and Track student Performance</p>
                <p style="margin-top: 8px; color: var(--navy); font-weight: 700;">User Code: {{ $user->user_code ?? '-' }}</p>
            </div>
            {{-- âœ… Connected â€” goes to Faculty â€º Create Courses only --}}
            <a href="{{ route('faculty.create') }}"><button class="btn-outline" type="button">Create Courses</button></a>
        </div>

        {{-- Stat cards --}}
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4z"/></svg>
                    <span class="num">{{ $stats['total_courses'] ?? 0 }}</span>
                </div>
                <div class="label">Total Courses</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                    <span class="num">{{ $stats['published'] ?? 0 }}</span>
                </div>
                <div class="label">Published</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="num">{{ $stats['total_students'] ?? 0 }}</span>
                </div>
                <div class="label">Total Students</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.2 14.7 5l3.2-.2.8 3.1 2.5 2-1.4 2.9 1 3-2.8 1.7-.9 3-3.2-.4L11.5 22l-2.4-2.2-3.2.1-.5-3.1-2.3-2.2 1.5-2.8-.7-3 2.9-1.4 1.2-2.9 3.1.7z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span class="num">{{ $stats['enrollments'] ?? 0 }}</span>
                </div>
                <div class="label">Enrollments</div>
            </div>
        </section>

        {{-- Courses + side panels --}}
        <div class="content-grid">

            <section class="courses-col">
                <div class="courses-head">
                    <h3>My Courses</h3>
                </div>

                @forelse ($courses as $course)
                    <div class="course-card">
                        <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                        <div class="course-info">
                            <h4>{{ $course->title }}</h4>
                            <div class="course-meta">
                                <span class="status-pill {{ strtolower($course->status ?? 'draft') === 'published' ? 'published' : 'draft' }}">
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
                            </div>
                        </div>
                        <div class="card-actions">
                            {{-- âœ… Connected â€” goes to Faculty â€º My Courses Manage (per-course) --}}
                            <a href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">
                                <button class="btn-manage" type="button">Manage</button>
                            </a>
                            {{-- Analytics button: open faculty analytics scoped to this course --}}
                            <a href="{{ route('faculty.analytics', ['course_id' => $course->id ?? null]) }}">
                                <button class="btn-manage" type="button">Analytics</button>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        You haven't created any courses yet.
                    </div>
                @endforelse
            </section>

            <aside class="side-col">
                <div class="panel">
                    <div class="panel-head">
                        <span>Monthly Enrollments</span>
                    </div>
                    <div class="panel-body">
                        @php $monthlyEnrollments = $monthlyEnrollments ?? collect(); @endphp
                        @if ($monthlyEnrollments->contains(fn ($month) => $month->count > 0))
                            <div class="enrollment-bars" role="img" aria-label="Enrollments for the last six months">
                                @foreach ($monthlyEnrollments as $month)
                                    <div class="enrollment-month">
                                        <span class="enrollment-bar" style="height: {{ max(6, $month->percent) }}%" title="{{ $month->count }} enrollment{{ $month->count === 1 ? '' : 's' }}"></span>
                                        <small>{{ $month->label }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <div class="enrollment-empty-note">Enrollment totals for the last six months.</div>
                        @else
                            <div class="enrollment-bars is-empty" aria-hidden="true">
                                <span></span><span></span><span></span><span></span><span></span><span></span>
                            </div>
                            <div class="enrollment-empty-note">No enrollment data yet. Trends will appear once students join.</div>
                        @endif
                    </div>
                </div>

                <div class="panel" style="min-height:auto;">
                    <div class="panel-head">
                        <span>Quick Actions</span>
                    </div>
                    <div class="panel-body">
                        <div class="quick-actions">
                            <a class="btn-quick" href="{{ route('faculty.students') }}">View Students</a>
                            <a class="btn-quick" href="{{ route('faculty.courses') }}">View Courses</a>
                            <a class="btn-quick" href="{{ route('faculty.create') }}">Create a Course</a>
                        </div>
                    </div>
                </div>
            </aside>

        </div>

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

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>



