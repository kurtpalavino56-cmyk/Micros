{{--
    resources/views/faculty/profile.blade.php
    Faculty › My Profile — identity header, About me, Settings, and the Edit Profile drawer.

    Expects: $user (name, role, phone, email, joined, location, user_code, birth_date,
             birth_date_raw, gender, education, bio, about, language, timezone, avatar_url),
             $languages, $timezones, $performance, $activity, $profileCourses.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Upskill</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
<style>
    :root{--navy:#13176b;--navy-deep:#0c0f4d;--gold:#dba617;--ink:#101010;--muted:#667085;--line:#e6e8f0;--bg:#f7f8fc;--bad:#b42318;}
    *{box-sizing:border-box;}
    body{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;color:var(--ink);margin:0;background:var(--bg);}
    a{text-decoration:none;color:inherit;} button{font-family:inherit;cursor:pointer;}
    :focus-visible{outline:3px solid var(--gold);outline-offset:2px;border-radius:6px;}
    h2,h3{font-family:"Plus Jakarta Sans",Inter,sans-serif;color:var(--ink);margin:0;}

    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}
    .layout > *{min-width:0;}
    .main{padding:32px 32px 48px;}
    .profile-page{max-width:1180px;margin:0 auto;}

    /* Header */
    .page-head{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:22px;}
    .page-head h2{font-size:32px;letter-spacing:-.02em;}
    .page-head p{margin:6px 0 0;color:var(--muted);font-size:14px;}
    .page-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .btn{min-height:42px;padding:0 20px;border-radius:10px;font-size:14px;font-weight:600;border:1px solid transparent;transition:background .15s,border-color .15s,color .15s;}
    .btn-edit-profile{background:var(--navy);color:#fff;}
    .btn-edit-profile:hover{background:var(--navy-deep);}
    /* .btn-logout{background:#fff;border-color:var(--line);color:var(--bad);}
    .btn-logout:hover{background:#fef3f2;border-color:#fecdca;} */

    /* Identity */
    .hero{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden;}
    .hero-cover{height:96px;background:linear-gradient(120deg,#fff3c4 0%,#fff8df 62%,#fefcf3 100%);}
    .hero-body{display:flex;align-items:flex-start;gap:24px;flex-wrap:wrap;padding:0 28px 26px;}
    .avatar-wrap{position:relative;margin-top:-48px;flex-shrink:0;}
    .avatar-ring{width:104px;height:104px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eef0fb center/cover no-repeat;color:var(--navy);border:4px solid #fff;box-shadow:0 0 0 1px var(--line);}
    .avatar-ring svg{width:50px;height:50px;}
    .avatar-edit{position:absolute;right:0;bottom:2px;width:32px;height:32px;border-radius:50%;background:var(--navy);color:#fff;border:3px solid #fff;display:flex;align-items:center;justify-content:center;padding:0;}
    .avatar-edit:hover{background:var(--gold);color:var(--navy);} .avatar-edit svg{width:14px;height:14px;}
    .identity{flex:1 1 320px;min-width:0;padding-top:16px;}
    .identity h3{font-size:24px;display:flex;flex-wrap:wrap;align-items:center;gap:10px;letter-spacing:-.01em;}
    .role-pill{background:#fff4d1;color:#7a5600;font:600 12px Inter,sans-serif;padding:4px 12px;border-radius:99px;}
    .headline{margin:6px 0 0;color:var(--muted);font-size:14px;}
    .identity-grid{display:flex;flex-wrap:wrap;gap:10px 28px;margin-top:14px;}
    .id-item{display:flex;align-items:center;gap:8px;font-size:14px;color:var(--ink);min-width:0;overflow-wrap:anywhere;}
    .id-item svg{width:16px;height:16px;color:var(--muted);flex-shrink:0;}

    /* Completeness banner */
    .complete{display:flex;gap:20px 28px;align-items:center;flex-wrap:wrap;background:#fffaf0;border:1px solid #f3dfa2;border-radius:16px;padding:18px 22px;margin-top:20px;}
    .complete-meter{flex:1 1 240px;}
    .meter-top{display:flex;justify-content:space-between;gap:12px;font-size:14px;font-weight:600;color:var(--ink);}
    .meter-track{height:8px;border-radius:99px;background:#f1e6c2;margin-top:8px;overflow:hidden;}
    .meter-fill{display:block;height:100%;background:var(--gold);border-radius:99px;}
    .todo-chips{display:flex;flex-wrap:wrap;gap:8px;flex:2 1 300px;}
    .chip{border:1px dashed #d9b84a;background:#fff;color:#7a5600;font-size:12px;font-weight:600;padding:6px 12px;border-radius:99px;}
    .chip:hover{background:#fff4d1;}
    .add-link{background:none;border:none;padding:0;color:var(--navy);font-weight:600;font-size:13px;text-decoration:underline;text-underline-offset:3px;}
    .add-link:hover{color:var(--gold-dark,#b8860b);}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:20px;}
    .stat{background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px 20px;}
    .stat b{display:block;font-family:"Plus Jakarta Sans",Inter,sans-serif;font-size:28px;line-height:1.1;color:var(--ink);font-variant-numeric:tabular-nums;}
    .stat span{display:block;margin-top:6px;font-size:13px;color:var(--muted);}

    /* Courses + trends */
    .section-head{display:flex;justify-content:space-between;align-items:baseline;gap:12px;margin:36px 0 14px;}
    .section-head h3{font-size:19px;} .section-head a{font-size:13px;font-weight:600;color:var(--navy);}
    .course-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
    .course-card{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;}
    .course-thumb{height:116px;background:linear-gradient(135deg,#e8ebf9,#f6efd6) center/cover no-repeat;}
    .course-info{padding:16px 18px 18px;display:flex;flex-direction:column;gap:12px;flex:1;}
    .course-info h4{margin:0;font-size:15px;color:var(--ink);line-height:1.35;}
    .course-meta{display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:12px;color:var(--muted);}
    .status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:#eef0f4;color:#475467;white-space:nowrap;}
    .status-pill.ok{background:#e4f6ee;color:#157f5b;} .status-pill.warn{background:#fff3d1;color:#9a6700;}
    .course-stats{display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--ink);margin-top:auto;}
    .course-stats b{color:var(--ink);}
    .star{color:#b8860b;}
    .progress{display:flex;align-items:center;gap:10px;font-size:12px;color:var(--muted);}
    .progress-track{flex:1;height:6px;border-radius:99px;background:var(--line);overflow:hidden;}
    .progress-fill{display:block;height:100%;background:var(--navy);border-radius:99px;}
    .trends{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;}
    .trends .panel p.sub{margin:4px 0 0;font-size:13px;color:var(--muted);}
    .chart{display:flex;align-items:flex-end;gap:10px;height:150px;margin-top:18px;}
    .bar-col{flex:1;min-width:0;height:100%;display:flex;flex-direction:column;align-items:center;gap:6px;}
    .bar-area{flex:1;width:100%;display:flex;align-items:flex-end;justify-content:center;}
    .bar{width:100%;max-width:38px;min-height:3px;border-radius:8px 8px 3px 3px;background:var(--navy);}
    .bar.alt{background:var(--gold);}
    .bar-val{font-size:11px;font-weight:600;color:var(--ink);height:14px;}
    .bar-label{font-size:11px;color:var(--muted);}
    .empty-state{border:1px dashed #cfd3e2;border-radius:14px;padding:26px 18px;text-align:center;color:var(--muted);font-size:14px;line-height:1.6;background:#fff;}
    .empty-state a{color:var(--navy);font-weight:600;text-decoration:underline;}
    @media (max-width:980px){.stats{grid-template-columns:repeat(2,minmax(0,1fr));} .trends{grid-template-columns:1fr;}}
    @media (max-width:640px){.hero-body{padding:0 18px 22px;} .complete{padding:16px;}}

    /* Sections */
    .two-col{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin-top:24px;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:18px;padding:24px 28px;min-width:0;}
    .panel h3{font-size:17px;margin-bottom:6px;}
    .profile-info-panel{padding:0;overflow:hidden;}
    .profile-info-head{padding:22px 28px 12px;background:#f9fafb;border-bottom:1px solid var(--line);}
    .profile-info-head h3{margin:0;}
    .profile-info-body{padding:0 28px 24px;}
    .about-intro{margin:10px 0 4px;color:var(--ink);font-size:14px;line-height:1.65;}
    .row{display:flex;justify-content:space-between;align-items:center;gap:6px 16px;flex-wrap:wrap;padding:14px 0;border-bottom:1px solid var(--line);}
    .row:last-child{border-bottom:none;padding-bottom:0;}
    .row-label{font-size:13px;color:var(--muted);font-weight:500;flex-shrink:0;}
    .row-value{font-size:14px;font-weight:600;color:var(--ink);text-align:right;min-width:0;overflow-wrap:anywhere;}
    .row.stack{display:block;} .row.stack .row-value{display:block;text-align:left;font-weight:400;color:var(--ink);line-height:1.65;margin-top:6px;}
    .btn-change{background:#fff;border:1px solid var(--line);color:var(--navy);font-weight:600;padding:6px 16px;border-radius:99px;font-size:12px;}
    .btn-change:hover{border-color:var(--navy);}
    .row-end{display:flex;align-items:center;gap:12px;}

    /* Drawer */
    .modal-overlay{position:fixed;inset:0;background:rgba(12,15,77,.4);z-index:1000;display:flex;justify-content:flex-end;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s;}
    .modal-overlay.open{opacity:1;visibility:visible;}
    .modal-panel{width:500px;max-width:100vw;height:100dvh;background:#fff;display:flex;flex-direction:column;box-shadow:-12px 0 40px rgba(19,23,107,.15);transform:translateX(100%);transition:transform .3s cubic-bezier(.16,1,.3,1);overflow:hidden;}
    .modal-overlay.open .modal-panel{transform:none;}
    .modal-header{padding:20px 26px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .modal-header h3{font-size:20px;} .modal-header p{margin:2px 0 0;color:var(--muted);font-size:13px;}
    .modal-close{width:36px;height:36px;border-radius:50%;border:1px solid var(--line);background:#fff;color:var(--navy);font-size:22px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .modal-close:hover{background:var(--bg);}
    .modal-tabs{display:flex;gap:4px;padding:0 26px;border-bottom:1px solid var(--line);overflow-x:auto;}
    .tab-btn{background:none;border:none;padding:12px 12px;font-size:14px;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-1px;white-space:nowrap;}
    .tab-btn.active{color:var(--navy);border-bottom-color:var(--navy);} .tab-btn:hover:not(.active){color:var(--navy);}
    #editProfileForm{flex:1 1 auto;min-height:0;display:flex;flex-direction:column;}
    .modal-body{flex:1 1 auto;min-height:0;overflow-y:auto;overscroll-behavior:contain;padding:24px 26px;}
    .tab-content{display:none;} .tab-content.active{display:block;}
    .avatar-upload-area{display:flex;align-items:center;gap:16px;padding:16px;border:1px dashed #cfd3e2;border-radius:14px;margin-bottom:24px;background:var(--bg);}
    .avatar-preview{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eef0fb center/cover no-repeat;color:var(--navy);flex-shrink:0;overflow:hidden;}
    .avatar-preview svg{width:34px;height:34px;}
    .avatar-upload-text p{margin:0 0 8px;color:var(--muted);font-size:12.5px;line-height:1.5;}
    .btn-upload-avatar{background:var(--navy);color:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;cursor:pointer;}
    .btn-upload-avatar:hover{background:var(--navy-deep);}
    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-weight:600;color:var(--ink);font-size:13px;margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;border:1px solid #d0d5dd;border-radius:10px;padding:11px 13px;font:400 14px Inter,sans-serif;color:var(--ink);background:#fff;transition:border-color .15s,box-shadow .15s;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--navy);box-shadow:0 0 0 3px rgba(19,23,107,.1);}
    .form-group textarea{resize:vertical;min-height:84px;line-height:1.5;}
    .form-group .hint{color:var(--muted);font-size:12px;margin:5px 0 0;}
    .profile-readonly-field,.form-group input[readonly]{background:var(--bg);color:var(--muted);cursor:not-allowed;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .form-divider{border:none;border-top:1px solid var(--line);margin:22px 0;}
    .form-section-label{font-size:13px;font-weight:700;color:var(--navy);margin:0 0 14px;}
    .form-errors{background:#fef3f2;border:1px solid #fecdca;border-radius:10px;padding:12px 15px;margin-bottom:20px;color:var(--bad);font-size:13px;line-height:1.6;}
    .form-errors ul{margin:0;padding-left:18px;}
    .pwd-wrap{position:relative;} .pwd-wrap input{padding-right:44px;}
    .pwd-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);padding:4px;display:flex;}
    .pwd-toggle svg{width:17px;height:17px;}
    .settings-info-box{background:#eef0fb;border-radius:10px;padding:12px 14px;margin-bottom:20px;display:flex;gap:10px;align-items:flex-start;}
    .settings-info-box svg{width:16px;height:16px;color:var(--navy);flex-shrink:0;margin-top:2px;}
    .settings-info-box p{margin:0;color:var(--navy);font-size:13px;line-height:1.5;}
    .modal-footer{padding:16px 26px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end;background:#fff;}
    .btn-cancel-modal{background:#fff;border-color:var(--line);color:var(--navy);}
    .btn-cancel-modal:hover{border-color:var(--navy);}
    .btn-save{background:var(--navy);color:#fff;display:flex;align-items:center;gap:7px;}
    .btn-save:hover{background:var(--navy-deep);} .btn-save svg{width:16px;height:16px;}

    @media (max-width:980px){.layout{grid-template-columns:1fr;} .main{padding:24px 16px 32px;} .two-col{grid-template-columns:1fr;}}
    @media (max-width:640px){
        html,body{overflow-x:hidden;}
        .page-head h2{font-size:26px;}
        .panel{padding:20px 18px;}
        .profile-info-panel{padding:0;}
        .profile-info-head{padding:18px 18px 10px;}
        .profile-info-body{padding:0 18px 20px;}
        .avatar-ring{width:76px;height:76px;} .avatar-ring svg{width:40px;height:40px;}
        .identity{flex-basis:100%;}
        .row-value{text-align:left;}
        .form-row{grid-template-columns:1fr;}
        .modal-panel{width:100%;} .modal-body{padding:18px 16px;} .modal-header,.modal-footer{padding-left:16px;padding-right:16px;} .modal-tabs{padding:0 16px;}
    }
    @media (prefers-reduced-motion:reduce){.modal-overlay,.modal-panel{transition:none;}}
</style>
</head>
<body>

<x-flash-toast :message="session('success')" />

@include('components.authenticated-topbar')

<div class="layout faculty-sidebar-layout">

    @include('components.faculty-sidebar')

    <main class="main">
        <div class="profile-page">

        <header class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">My Profile</h2>
                <p class="faculty-page-subtitle">Manage your personal information and account preferences</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-edit-profile" type="button" onclick="openEditModal('personal')">Edit profile</button>
            </div>
        </header>

        @php
            $has = fn ($v) => filled($v) && $v !== '-';
            $courseList = collect($profileCourses ?? []);
            $totalCourses = $courseList->count();
            $totalEnrollments = (int) $courseList->sum('students');
            $reviewTotal = (int) $courseList->sum('review_count');
            $avgRating = $reviewTotal > 0 ? round($courseList->sum(fn ($c) => $c->rating * $c->review_count) / $reviewTotal, 1) : null;
            $avgCompletion = $totalCourses ? (int) round($courseList->avg('completion')) : null;
            $checks = [
                ['label' => 'Add a profile photo', 'done' => $has($user->avatar_url ?? null), 'tab' => 'personal'],
                ['label' => 'Add your phone number', 'done' => $has($user->phone ?? null), 'tab' => 'personal'],
                ['label' => 'Add your location', 'done' => $has($user->location ?? null), 'tab' => 'personal'],
                ['label' => 'Write a short introduction', 'done' => $has($user->about ?? null), 'tab' => 'about'],
                ['label' => 'Add your date of birth', 'done' => $has($user->birth_date ?? null), 'tab' => 'about'],
                ['label' => 'Select your gender', 'done' => $has($user->gender ?? null), 'tab' => 'about'],
                ['label' => 'Add your education', 'done' => $has($user->education ?? null), 'tab' => 'about'],
                ['label' => 'Add your bio or skills', 'done' => $has($user->bio ?? null), 'tab' => 'about'],
            ];
            $pct = (int) round(collect($checks)->where('done', true)->count() / count($checks) * 100);
        @endphp

        {{-- Identity --}}
        <section class="hero">
            <div class="hero-cover" aria-hidden="true"></div>
            <div class="hero-body">
                <div class="avatar-wrap">
                    <div class="avatar-ring" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                        @unless($user->avatar_url ?? null)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        @endunless
                    </div>
                    <button class="avatar-edit" type="button" onclick="openEditModal('personal')" title="Change photo" aria-label="Change photo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    </button>
                </div>
                <div class="identity">
                    <h3>{{ $user->name ?? 'Faculty Member' }}<span class="role-pill">{{ $user->role ?? 'Faculty' }}</span></h3>
                    @if($has($user->education ?? null))<p class="headline">{{ $user->education }}</p>@endif
                    <div class="identity-grid">
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>{{ $user->phone ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>{{ $user->email ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $user->location ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Joined on {{ $user->joined ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>{{ $user->user_code ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Profile completeness (hidden once everything is filled in) --}}
        @if($pct < 100)
            <section class="complete" aria-label="Profile completeness">
                <div class="complete-meter">
                    <div class="meter-top"><span>Your profile is {{ $pct }}% complete</span><span>{{ collect($checks)->where('done', true)->count() }}/{{ count($checks) }}</span></div>
                    <div class="meter-track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"><span class="meter-fill" style="width:{{ $pct }}%"></span></div>
                </div>
                <div class="todo-chips">
                    @foreach($checks as $check)
                        @unless($check['done'])<button type="button" class="chip" onclick="openEditModal('{{ $check['tab'] }}')">+ {{ $check['label'] }}</button>@endunless
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Teaching snapshot --}}
        <section class="stats" aria-label="Teaching snapshot">
            <div class="stat"><b>{{ $totalCourses }}</b><span>Courses created</span></div>
            <div class="stat"><b>{{ $totalEnrollments }}</b><span>Total enrollments</span></div>
            <div class="stat"><b>@if($avgRating !== null)<span class="star" style="display:inline;margin:0;font-size:inherit;">★</span> {{ number_format($avgRating, 1) }}@else — @endif</b><span>{{ $reviewTotal > 0 ? 'Average rating · '.$reviewTotal.' '.\Illuminate\Support\Str::plural('review', $reviewTotal) : 'No ratings yet' }}</span></div>
            <div class="stat"><b>{{ $avgCompletion !== null ? $avgCompletion.'%' : '—' }}</b><span>Average learner completion</span></div>
        </section>

        <div class="two-col">

            {{-- About me --}}
            <section class="panel profile-info-panel">
                <div class="profile-info-head"><h3>About me</h3></div>
                <div class="profile-info-body">
                @if($has($user->about ?? null))<p class="about-intro">{{ $user->about }}</p>
                @else<p class="about-intro"><button type="button" class="add-link" onclick="openEditModal('about')">Write a short introduction</button> so learners know who you are.</p>@endif
                <div class="row"><span class="row-label">Date of birth</span><span class="row-value">@if($has($user->birth_date ?? null)){{ $user->birth_date }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add date of birth</button>@endif</span></div>
                <div class="row"><span class="row-label">Gender</span><span class="row-value">@if($has($user->gender ?? null)){{ $user->gender }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Select gender</button>@endif</span></div>
                <div class="row"><span class="row-label">Education</span><span class="row-value">@if($has($user->education ?? null)){{ $user->education }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add education</button>@endif</span></div>
                <div class="row stack"><span class="row-label">Bio</span><span class="row-value">@if($has($user->bio ?? null)){{ $user->bio }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add your bio or skills</button>@endif</span></div>
                </div>
            </section>

            {{-- Settings --}}
            <section class="panel profile-info-panel">
                <div class="profile-info-head"><h3>Settings</h3></div>
                <div class="profile-info-body">
                <div class="row"><span class="row-label">Email</span><span class="row-value">{{ $user->email ?? '-' }}</span></div>
                <div class="row">
                    <span class="row-label">Password</span>
                    <span class="row-end"><span class="row-value">************</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                <div class="row">
                    <span class="row-label">Language</span>
                    <span class="row-end"><span class="row-value">{{ $user->language ?? 'English' }}</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                <div class="row">
                    <span class="row-label">Time zone</span>
                    <span class="row-end"><span class="row-value">{{ ($user->timezone ?? '') !== '' ? $user->timezone : '-' }}</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                </div>
            </section>

        </div>

        {{-- My courses --}}
        <div class="section-head"><h3>My courses</h3><a href="{{ route('faculty.courses') }}">View all courses</a></div>
        @if($courseList->isNotEmpty())
            <div class="course-grid">
                @foreach($courseList as $course)
                    @php $st = strtolower($course->status ?? ''); $stClass = str_contains($st, 'publish') ? 'ok' : (str_contains($st, 'pending') ? 'warn' : ''); @endphp
                    <article class="course-card">
                        <div class="course-thumb" @if($course->thumbnail_url) style="background-image:url('{{ asset($course->thumbnail_url) }}')" @endif></div>
                        <div class="course-info">
                            <div class="course-meta"><span>{{ $course->category }}</span><span class="status-pill {{ $stClass }}">{{ $course->status }}</span></div>
                            <h4>{{ $course->title }}</h4>
                            <div class="course-stats">
                                <span><b>{{ $course->students }}</b> learners</span>
                                <span>@if($course->review_count > 0)<span class="star">★</span> <b>{{ number_format($course->rating, 1) }}</b> ({{ $course->review_count }})@else No ratings yet @endif</span>
                            </div>
                            <div class="progress" title="Average learner completion">
                                <span class="progress-track"><span class="progress-fill" style="width:{{ min(100, max(0, $course->completion)) }}%"></span></span>
                                <span>{{ $course->completion }}% avg. completion</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">You haven't created a course yet. <a href="{{ route('faculty.create') }}">Create your first course</a> and it will appear here.</div>
        @endif

        {{-- Teaching trends --}}
        @php
            $perf = collect($performance ?? []);
            $act = collect($activity ?? []);
            $actMax = max(1, (int) $act->max('hours'));
        @endphp
        <div class="section-head"><h3>Teaching trends</h3></div>
        <div class="trends">
            <section class="panel">
                <h3>New enrollments</h3>
                <p class="sub">Last 6 months, relative to your busiest month</p>
                @if($perf->sum('percent') > 0)
                    <div class="chart">
                        @foreach($perf as $m)
                            <div class="bar-col" title="{{ $m['label'] }}: {{ $m['percent'] }}% of peak">
                                <div class="bar-area"><span class="bar" style="height:{{ max(2, $m['percent']) }}%"></span></div>
                                <span class="bar-label">{{ $m['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="margin-top:16px;">No enrollments in the last 6 months.</div>
                @endif
            </section>
            <section class="panel">
                <h3>Learner activity this week</h3>
                <p class="sub">Lesson, quiz and enrollment events from your learners</p>
                @if($act->sum('hours') > 0)
                    <div class="chart">
                        @foreach($act as $d)
                            <div class="bar-col" title="{{ $d['label'] }}: {{ $d['hours'] }} events">
                                <span class="bar-val">{{ $d['hours'] ?: '' }}</span>
                                <div class="bar-area"><span class="bar alt" style="height:{{ max(2, (int) round($d['hours'] / $actMax * 100)) }}%"></span></div>
                                <span class="bar-label">{{ $d['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="margin-top:16px;">No learner activity recorded this week yet.</div>
                @endif
            </section>
        </div>

        </div>
    </main>
</div>

{{-- ===== EDIT PROFILE DRAWER ===== --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Edit profile">

        <div class="modal-header">
            <div><h3>Edit profile</h3><p>Update your personal information</p></div>
            <button class="modal-close" type="button" onclick="closeEditModal()" title="Close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-tabs" role="tablist">
            <button class="tab-btn active" type="button" role="tab" data-tab="personal" onclick="switchTab('personal')">Personal info</button>
            <button class="tab-btn" type="button" role="tab" data-tab="about" onclick="switchTab('about')">About me</button>
            <button class="tab-btn" type="button" role="tab" data-tab="settings" onclick="switchTab('settings')">Account settings</button>
        </div>

        <form id="editProfileForm" action="{{ route('faculty.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                @if ($errors->any())
                    <div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                {{-- Personal info --}}
                <div id="tab-personal" class="tab-content active">
                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <p>JPG, PNG or GIF, up to 10 MB.<br>Resized to 400 × 400 px automatically.</p>
                            <label class="btn-upload-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload photo
                                <input type="file" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        </div>
                    </div>

                    <p class="form-section-label">Basic information</p>
                    <div class="form-group">
                        <label for="edit_name">Full name</label>
                        <input type="text" id="edit_name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Enter your full name">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone number</label>
                            <input type="tel" id="edit_phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="e.g. 09XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input type="text" id="edit_location" name="location" value="{{ old('location', $user->location ?? '') }}" placeholder="City, Province">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role / title</label>
                        <input class="profile-readonly-field" type="text" id="edit_role" name="role" value="{{ old('role', $user->role ?? '') }}" placeholder="e.g. Faculty, Professor" readonly>
                    </div>
                </div>

                {{-- About me --}}
                <div id="tab-about" class="tab-content">
                    <div class="form-group">
                        <label for="edit_about">About me</label>
                        <textarea id="edit_about" name="about" rows="3" placeholder="Share a little about yourself...">{{ old('about', $user->about ?? '') }}</textarea>
                        <p class="hint">A short description shown on your profile.</p>
                    </div>
                    <hr class="form-divider">
                    <p class="form-section-label">Personal details</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_dob">Date of birth</label>
                            <input type="date" id="edit_dob" name="date_of_birth" value="{{ old('date_of_birth', $user->birth_date_raw ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male" @selected(old('gender', $user->gender ?? '') === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender', $user->gender ?? '') === 'Female')>Female</option>
                                <option value="Other" @selected(old('gender', $user->gender ?? '') === 'Other')>Other</option>
                                <option value="Prefer not to say" @selected(old('gender', $user->gender ?? '') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_education">Education</label>
                        <input type="text" id="edit_education" name="education" value="{{ old('education', $user->education ?? '') }}" placeholder="e.g. BS Information Technology">
                    </div>
                    <div class="form-group">
                        <label for="edit_bio">Bio / skills</label>
                        <textarea id="edit_bio" name="bio" rows="3" placeholder="List your skills, expertise, or career highlights...">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Account settings --}}
                <div id="tab-settings" class="tab-content">
                    <div class="settings-info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Leave the password fields empty if you don't want to change your password.</p>
                    </div>

                    <p class="form-section-label">Email address</p>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input class="profile-readonly-field" type="email" id="edit_email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="you@example.com" readonly>
                        <p class="hint">Email changes are managed by an administrator.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Change password</p>
                    <div class="form-group">
                        <label for="edit_curr_pwd">Current password</label>
                        <div class="pwd-wrap">
                            <input type="password" id="edit_curr_pwd" name="current_password" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('edit_curr_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_new_pwd">New password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_new_pwd" name="password" placeholder="Min. 8 characters">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_new_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_confirm_pwd">Confirm password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_confirm_pwd" name="password_confirmation" placeholder="Repeat new password">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_confirm_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Preferences</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_language">Language</label>
                            <select id="edit_language" name="language">
                                @foreach($languages ?? ['English'] as $lang)
                                    <option value="{{ $lang }}" @selected(old('language', $user->language ?? 'English') === $lang)>{{ $lang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_timezone">Time zone</label>
                            <select id="edit_timezone" name="timezone">
                                @foreach($timezones ?? [] as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $user->timezone ?? '') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-save">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ====== Edit profile drawer ====== */
let lastFocus = null;
function openEditModal(tab) {
    lastFocus = document.activeElement;
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
    setTimeout(() => { const c = document.querySelector('#editModal .modal-close'); if (c) c.focus(); }, 50);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
}
function handleOverlayClick(e) { if (e.target === document.getElementById('editModal')) closeEditModal(); }
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => { const on = b.dataset.tab === tab; b.classList.toggle('active', on); b.setAttribute('aria-selected', on); });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.toggle('active', c.id === 'tab-' + tab));
}

/* Password show/hide */
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
}

/* Avatar: resize client-side, then store in the hidden field (avoids PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else       { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';
            document.getElementById('avatarBase64').value = dataUrl;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEditModal(); });

/* Re-open the drawer if the save failed validation */
@if ($errors->any())
openEditModal(@json($errors->has('current_password') || $errors->has('password') || $errors->has('email') ? 'settings' : 'personal'));
@endif
</script>

<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:900;width:44px;height:44px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;box-shadow:0 8px 20px rgba(19,23,107,.3);transition:background .2s;}
    #back-to-top-btn:hover{background:#dba617;}
    #back-to-top-btn svg{width:20px;height:20px;}
</style>
<script>
    (function () {
        var b = document.getElementById('back-to-top-btn');
        function t() { b.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true }); t();
    })();
</script>

@include('components.responsive')
</body>
</html>