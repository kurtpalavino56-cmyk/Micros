{{--
    resources/views/admin/Course_Detail.blade.php

    Admin › Courses & Badges › Course Detail
    URL: /Admin-courses/{id}   (admin.courses.show)

    Shows everything about a single course and, while it is pending,
    Approve / Denied buttons (same actions as on the list page).

    Expected data from AdminController::showCourse():
        'course' => object with:
            ->id, ->title, ->description, ->category, ->program, ->term,
            ->level, ->duration, ->instructor, ->skills (array),
            ->objectives (array), ->badge, ->status, ->status_key,
            ->students, ->faculty, ->percent, ->modules_count,
            ->lessons_count, ->thumbnail_url, ->created_at, ->approved_at
        'modules' => collection, each ->title, ->description,
                     ->lessons (each ->title, ->type, ->duration),
                     ->quiz (->title, ->questions_count, ->passing_score)|null
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} – Courses &amp; Badges</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:        #0d1b6e;
            --navy-light:  #1a2fa0;
            --navy-pale:   #e8eaf6;
            --teal-bar:    #1f8a6e;
            --white:       #ffffff;
            --surface:     #f4f6fb;
            --border:      #e2e5f0;
            --text-main:   #0d1b6e;
            --text-muted:  #6b7280;
            --text-sub:    #9ca3af;
            --shadow-sm:   0 1px 4px rgba(13,27,110,.07);
            --shadow-md:   0 3px 12px rgba(13,27,110,.11);
            --radius-card: 12px;
            --sidebar-w:   225px;
            --topbar-h:    58px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* ─── TOP NAV ── */
        .topbar {
            position: fixed; inset: 0 0 auto 0; height: var(--topbar-h);
            background: var(--navy); display: flex; align-items: center;
            justify-content: space-between; padding: 0 22px; z-index: 200;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 10px; color: var(--white);
            font-size: 1.05rem; font-weight: 800; letter-spacing: 2px;
            text-transform: uppercase; text-decoration: none;
        }
        .brand-logo {
            width: 34px; height: 34px; border-radius: 50%; background: var(--white);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;
        }
        .brand-logo svg { width: 20px; height: 20px; }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .search-wrap {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22);
            border-radius: 20px; padding: 6px 16px;
        }
        .search-wrap svg { color: rgba(255,255,255,0.65); flex-shrink: 0; }
        .search-wrap input {
            background: none; border: none; outline: none; color: var(--white);
            width: 170px; font-size: 0.825rem;
        }
        .search-wrap input::placeholder { color: rgba(255,255,255,0.55); }
        .avatar-btn {
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(255,255,255,0.15); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--white); text-decoration: none; transition: background .2s;
        }
        .avatar-btn:hover { background: rgba(255,255,255,0.25); }

        /* ─── LAYOUT / SIDEBAR ── */
        .layout { display: flex; margin-top: var(--topbar-h); min-height: calc(100vh - var(--topbar-h)); }
        .sidebar {
            width: var(--sidebar-w); background: var(--white); border-right: 1px solid var(--border);
            position: fixed; top: var(--topbar-h); left: 0; bottom: 0; overflow-y: auto; z-index: 100;
        }
        .sb-section { border-bottom: 1px solid var(--border); }
        .sb-section-hd {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 14px 14px 16px; cursor: pointer; user-select: none;
            border-left: 3px solid transparent; transition: background .18s, border-color .2s;
        }
        .sb-section-hd:hover { background: #f7f9ff; }
        .sb-section.open > .sb-section-hd { border-left-color: #5c6bc0; }
        .sb-hd-left { display: flex; flex-direction: column; gap: 3px; }
        .sb-section-label { font-size: 0.875rem; font-weight: 700; color: #1a237e; }
        .sb-lesson-count { font-size: 0.695rem; color: var(--text-sub); }
        .sb-chevron { width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: transform .28s ease; }
        .sb-section.open .sb-chevron { transform: rotate(180deg); }
        .sb-items { display: none; flex-direction: column; padding: 6px 10px 10px; gap: 1px; }
        .sb-section.open .sb-items { display: flex; }
        .sb-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 9px 10px 9px 12px; text-decoration: none; color: #374151;
            font-size: 0.84rem; border-radius: 6px; border-bottom: 1px solid #f0f2f9;
            transition: background .15s, color .15s;
        }
        .sb-item:last-child { border-bottom: none; }
        .sb-item:hover { background: #eef0fb; color: var(--navy); }
        .sb-item.active { background: #62d4cf; border-radius: 22px; color: #0d2060; font-weight: 600; border-bottom: none; }
        .sb-item.active:hover { background: #4ecbc5; }
        .sb-item-text { flex: 1; }

        /* ─── MAIN ── */
        .main { margin-left: var(--sidebar-w); flex: 1; padding: 30px 34px 44px; max-width: 1060px; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.83rem; font-weight: 700; color: var(--text-muted);
            text-decoration: none; margin-bottom: 16px;
        }
        .back-link:hover { color: var(--navy); }

        /* Header card */
        .detail-head {
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 14px; padding: 26px 28px; box-shadow: var(--shadow-sm);
            margin-bottom: 22px;
        }
        .detail-head-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; flex-wrap: wrap; }
        .detail-title { font-size: 1.45rem; font-weight: 800; color: var(--navy); line-height: 1.25; margin-bottom: 8px; }
        .detail-sub { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }

        .approval-pill {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.72rem; font-weight: 700; padding: 5px 14px;
            border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .approval-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
        .approval-pending  { background: #fff7ed; color: #b45309; border: 1px solid #fed7aa; }
        .approval-pending .dot { background: #f59e0b; }
        .approval-approved { background: #ecfdf5; color: #15803d; border: 1px solid #a7f3d0; }
        .approval-approved .dot { background: #22c55e; }
        .approval-denied   { background: #fff1f2; color: #b91c1c; border: 1px solid #fecdd3; }
        .approval-denied .dot { background: #ef4444; }
        .approval-draft    { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .approval-draft .dot { background: #94a3b8; }

        .detail-actions { display: flex; gap: 10px; margin-top: 18px; }
        .btn-approve, .btn-deny {
            border: none; border-radius: 9px; padding: 11px 26px;
            font-size: 0.88rem; font-weight: 700; cursor: pointer;
            transition: background .18s, transform .15s;
        }
        .btn-approve { background: #16a34a; color: #fff; }
        .btn-approve:hover { background: #15803d; transform: translateY(-1px); }
        .btn-deny { background: #fff1f2; color: #b91c1c; border: 1.5px solid #fecdd3; }
        .btn-deny:hover { background: #ffe4e6; transform: translateY(-1px); }
        .btn-delete {
            border: none; border-radius: 9px; padding: 11px 26px;
            font-size: 0.88rem; font-weight: 700; cursor: pointer;
            background: #b91c1c; color: #fff;
            transition: background .18s, transform .15s;
        }
        .btn-delete:hover { background: #991b1b; transform: translateY(-1px); }

        .alert-success {
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857;
            border-radius: 10px; padding: 11px 16px; font-size: 0.86rem;
            font-weight: 600; margin-bottom: 20px;
        }

        /* Info grid */
        .info-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px;
        }
        @media (max-width: 900px) { .info-grid { grid-template-columns: repeat(2, 1fr); } }
        .info-cell {
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 12px; padding: 14px 16px; box-shadow: var(--shadow-sm);
        }
        .info-cell .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-sub); font-weight: 700; margin-bottom: 4px; }
        .info-cell .val { font-size: 0.95rem; font-weight: 700; color: var(--navy); }

        /* Section cards */
        .section-card {
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 14px; padding: 22px 26px; box-shadow: var(--shadow-sm);
            margin-bottom: 22px;
        }
        /* Completion award: badge art + certificate. */
        .award-grid { display:grid; grid-template-columns:200px minmax(0,1fr); gap:26px; align-items:start; }
        .award-col { min-width:0; }
        .award-label {
            font-size:11.5px; font-weight:800; letter-spacing:.5px; text-transform:uppercase;
            color:#6b7280; margin-bottom:10px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;
        }
        .award-tag {
            background:#eef1fb; color:#13176b; border:1px solid #c9cdf0; border-radius:999px;
            padding:2px 9px; font-size:10.5px; font-weight:800; letter-spacing:.3px; text-transform:none;
        }
        /* Data-URL SVG has no intrinsic size — it fills its box unless capped. */
        .award-badge { width:170px; max-width:100%; height:auto; display:block; margin:0 auto 10px; }
        .award-name { font-size:14.5px; font-weight:800; color:#13176b; text-align:center; word-break:break-word; }
        .award-sub  { font-size:12px; color:#6b7280; text-align:center; margin-top:2px; }
        .award-none {
            border:1px dashed #d7dbe8; border-radius:12px; padding:26px 14px;
            text-align:center; color:#9ca3af; font-size:12.5px;
        }
        .award-upload { border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
        .award-upload img   { width:100%; height:auto; display:block; }
        .award-upload embed { width:100%; height:420px; display:block; }
        .award-link {
            display:inline-block; margin-top:10px; font-size:12.5px;
            font-weight:700; color:#13176b; text-decoration:underline;
        }
        @media (max-width:820px) { .award-grid { grid-template-columns:1fr; } }

        .section-card h3 { font-size: 1rem; font-weight: 800; color: var(--navy); margin-bottom: 12px; }
        .section-card p { font-size: 0.88rem; color: #374151; line-height: 1.65; }

        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip {
            background: var(--navy-pale); color: var(--navy);
            font-size: 0.78rem; font-weight: 700; padding: 6px 14px; border-radius: 999px;
        }

        .obj-list { list-style: none; }
        .obj-list li {
            font-size: 0.88rem; color: #374151; padding: 7px 0 7px 26px;
            position: relative; border-bottom: 1px solid #f0f2f9;
        }
        .obj-list li:last-child { border-bottom: none; }
        .obj-list li::before {
            content: ''; position: absolute; left: 4px; top: 13px;
            width: 8px; height: 8px; border-radius: 50%; background: var(--teal-bar);
        }

        /* Modules */
        .module-block { border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; }
        .module-block:last-child { margin-bottom: 0; }
        .module-title { font-size: 0.95rem; font-weight: 800; color: var(--navy); }
        .module-sub { font-size: 0.8rem; color: var(--text-muted); margin: 3px 0 10px; }
        .lesson-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 12px; background: #fafbff; border-radius: 8px; margin-bottom: 6px;
            font-size: 0.84rem;
        }
        .lesson-row:last-child { margin-bottom: 0; }
        .lesson-meta { color: var(--text-sub); font-size: 0.76rem; font-weight: 600; }
        .quiz-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 12px; background: #fff8eb; border: 1px solid #fde9c0;
            border-radius: 8px; margin-top: 8px; font-size: 0.84rem; font-weight: 700; color: #92600a;
        }
        .modules-empty { color: var(--text-sub); font-size: 0.86rem; }

        /* Expandable lesson / quiz details (admin view) */
        .lesson-toggle, .quiz-toggle { cursor: pointer; }
        .lesson-toggle::before { content: '▸'; margin-right: 8px; color: var(--text-sub); font-size: 0.8rem; }
        .quiz-toggle::before { content: '▸'; margin-right: 8px; }
        .lesson-detail, .quiz-detail {
            display: none; margin: -2px 0 8px; padding: 10px 14px 12px 26px;
            background: #fff; border: 1px dashed var(--border); border-radius: 0 0 8px 8px;
            font-size: 0.82rem; color: var(--text-sub);
        }
        .lesson-detail.open, .quiz-detail.open { display: block; }
        .lesson-desc { margin: 0 0 6px; line-height: 1.5; }
        .lesson-empty { font-style: italic; margin: 0; }
        .lesson-file { display: inline-block; color: var(--navy); font-weight: 700; text-decoration: underline; }
        .quiz-detail { border-color: #fde9c0; }
        .quiz-instructions { margin: 0 0 10px; font-style: italic; }
        .quiz-question { border-top: 1px solid #f3e8cf; padding: 8px 0; }
        .quiz-question:first-of-type { border-top: none; }
        .quiz-question-head { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
        .quiz-question-text { font-weight: 700; color: var(--navy); }
        .quiz-question-meta { white-space: nowrap; font-size: 0.74rem; color: var(--text-sub); }
        .quiz-options { margin: 4px 0 0; padding-left: 18px; list-style: none; }
        .quiz-options li { padding: 2px 0; }
        .quiz-options li.is-correct { color: #15803d; font-weight: 700; }
        .quiz-answer { margin-top: 4px; color: #15803d; }

        .progress-track { height: 6px; background: #e4e8f5; border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
        .progress-fill { height: 100%; background: var(--teal-bar); border-radius: 4px; }
        .pct { font-size: 0.82rem; font-weight: 700; color: var(--navy); }
    </style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<div class="layout">
<main class="main">

    <a href="{{ route('admin.courses') }}" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Courses &amp; Badges
    </a>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if (! empty($course->change_note))
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;
                    padding:16px 18px;margin-bottom:18px;">
            <div style="font-size:12px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;
                        color:#92400e;margin-bottom:6px;">What the author changed</div>
            <div style="color:#4b5563;font-size:14px;line-height:1.55;white-space:pre-wrap;">{{ $course->change_note }}</div>
        </div>
    @endif

    @if (! empty($course->denial_feedback))
        <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:14px;
                    padding:16px 18px;margin-bottom:18px;">
            <div style="font-size:12px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;
                        color:#b91c1c;margin-bottom:6px;">Previous denial feedback</div>
            <div style="color:#4b5563;font-size:14px;line-height:1.55;white-space:pre-wrap;">{{ $course->denial_feedback }}</div>
        </div>
    @endif

    {{-- ── Header ── --}}
    <div class="detail-head">
        <div class="detail-head-top">
            <div>
                <div class="detail-title">{{ $course->title }}</div>
                <div class="detail-sub">
                    By {{ $course->instructor ?? 'Faculty' }}
                    @if ($course->category) · {{ $course->category }} @endif
                    @if ($course->created_at) · Created {{ $course->created_at->format('M j, Y') }} @endif
                </div>
            </div>
            <span class="approval-pill approval-{{ $course->status_key }}">
                <span class="dot"></span>{{ $course->status }}
            </span>
        </div>

        <div class="detail-actions">
            @if ($course->status_key === 'pending')
            <form method="POST" action="{{ route('admin.courses.approve', $course->id) }}">
                @csrf
                <button type="submit" class="btn-approve">Approve</button>
            </form>
            {{-- #1 — denying requires feedback for the author --}}
            <button type="button" class="btn-deny" onclick="document.getElementById('deny-panel').style.display='block';this.style.display='none';">Denied</button>

            <div id="deny-panel" style="display:none;width:100%;margin-top:14px;">
                <form method="POST" action="{{ route('admin.courses.deny', $course->id) }}">
                    @csrf
                    <label for="denial_feedback"
                           style="display:block;font-size:12px;font-weight:800;letter-spacing:.4px;
                                  text-transform:uppercase;color:#6b7280;margin-bottom:6px;">
                        Feedback for the author (required)
                    </label>
                    <textarea id="denial_feedback" name="denial_feedback" required minlength="5"
                              placeholder="Explain what needs to change before this course can be approved…"
                              style="width:100%;min-height:110px;border:1.5px solid #c9ccdb;border-radius:12px;
                                     padding:12px 14px;font-family:inherit;font-size:14px;resize:vertical;">{{ old('denial_feedback') }}</textarea>
                    @error('denial_feedback')
                        <div style="color:#b91c1c;font-size:12.5px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                    <div style="display:flex;gap:10px;margin-top:12px;">
                        <button type="submit" class="btn-deny">Send Denial &amp; Feedback</button>
                        <button type="button" class="btn-ghost"
                                onclick="document.getElementById('deny-panel').style.display='none';
                                         this.closest('#deny-panel').previousElementSibling.style.display='inline-flex';">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
            @endif
            <form method="POST" action="{{ route('admin.courses.destroy', $course->id) }}">
                @csrf
                <button type="submit" class="btn-delete"
                        onclick="return confirm('Permanently delete \'{{ addslashes($course->title) }}\'? Its modules, lessons, quizzes and enrollments will also be removed. This cannot be undone.');">
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- ── Quick facts ── --}}
    <div class="info-grid">
        <div class="info-cell"><div class="lbl">Level</div><div class="val">{{ $course->level ?? '—' }}</div></div>
        <div class="info-cell"><div class="lbl">Duration</div><div class="val">{{ $course->duration ?? '—' }}</div></div>
        <div class="info-cell"><div class="lbl">Program</div><div class="val">{{ $course->program ?? '—' }}</div></div>
        <div class="info-cell"><div class="lbl">Term</div><div class="val">{{ $course->term ?? '—' }}</div></div>
        <div class="info-cell"><div class="lbl">Students</div><div class="val">{{ $course->students }}</div></div>
        <div class="info-cell"><div class="lbl">Modules</div><div class="val">{{ $course->modules_count }}</div></div>
        <div class="info-cell"><div class="lbl">Lessons</div><div class="val">{{ $course->lessons_count }}</div></div>
        <div class="info-cell"><div class="lbl">Badge</div><div class="val">{{ $course->badge }}</div></div>
    </div>

    {{-- ── Description ── --}}
    <div class="section-card">
        <h3>Description</h3>
        <p>{!! $course->description ?: 'No description provided.' !!}</p>
    </div>

    {{-- ── Skills ── --}}
    @if (! empty($course->skills))
    <div class="section-card">
        <h3>Skills Covered</h3>
        <div class="chip-row">
            @foreach ($course->skills as $skill)
                <span class="chip">{{ $skill }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Subject is Related on — internal only ── --}}
    @if (! empty($course->related_skills))
    <div class="section-card">
        <h3>
            Subject is Related on
            <span style="font-weight:600;font-size:11.5px;color:#6b7280;letter-spacing:0;
                         text-transform:none;display:block;margin-top:3px;">
                Set by faculty. Used to recommend this course to students with
                matching skills — not shown on any student page.
            </span>
        </h3>
        <div class="chip-row">
            @foreach ($course->related_skills as $skill)
                <span class="chip" style="background:#eef1fb;color:#13176b;border:1px solid #c9cdf0;">{{ $skill }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Objectives ── --}}
    @if (! empty($course->objectives))
    <div class="section-card">
        <h3>Learning Objectives</h3>
        <ul class="obj-list">
            @foreach ($course->objectives as $objective)
                <li>{{ $objective }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Award: badge + certificate ──────────────────────────────────
         What a student actually receives on completion. Shown here so the
         admin can review the award before approving the course, rather
         than only seeing the badge's name in the stat strip. --}}
    <div class="section-card">
        <h3>Completion Award</h3>

        <div class="award-grid">

            {{-- Badge --}}
            <div class="award-col">
                <div class="award-label">Badge</div>
                @if ($course->badge_icon)
                    <img class="award-badge" src="{{ $course->badge_icon }}" alt="{{ $course->badge }}">
                @else
                    <div class="award-none">No badge designed yet</div>
                @endif
                <div class="award-name">{{ $course->badge }}</div>
                @if ($course->badge_level)
                    <div class="award-sub">{{ $course->badge_level }}</div>
                @endif
            </div>

            {{-- Certificate --}}
            <div class="award-col">
                <div class="award-label">
                    Certificate
                    @if ($course->certificate_enabled)
                        <span class="award-tag">{{ $course->certificate_mode === 'upload' ? 'Uploaded' : 'Auto-generated' }}</span>
                    @endif
                </div>

                @if (! $course->certificate_enabled)
                    <div class="award-none">No certificate configured yet</div>
                @elseif ($course->certificate_mode === 'upload' && $course->certificate_file)
                    @php $ext = strtolower(pathinfo($course->certificate_file, PATHINFO_EXTENSION)); @endphp
                    <div class="award-upload">
                        @if ($ext === 'pdf')
                            <embed src="{{ asset($course->certificate_file) }}" type="application/pdf">
                        @else
                            <img src="{{ asset($course->certificate_file) }}" alt="Uploaded certificate">
                        @endif
                    </div>
                    <a class="award-link" href="{{ asset($course->certificate_file) }}" target="_blank">Open the uploaded file</a>
                @else
                    @include('components.certificate', ['cert' => $course->certificate])
                @endif
            </div>

        </div>
    </div>

    {{-- ── Modules / Lessons / Quizzes ── --}}
    <div class="section-card">
        <h3>Course Content</h3>
        @forelse ($modules as $module)
            <div class="module-block">
                <div class="module-title">{{ $module->title }}</div>
                @if ($module->description)
                    <div class="module-sub">{!! $module->description !!}</div>
                @endif

                @foreach ($module->lessons as $lesson)
                    <div class="lesson-row lesson-toggle" onclick="this.nextElementSibling.classList.toggle('open')">
                        <span>{{ $lesson->title }}</span>
                        <span class="lesson-meta">{{ $lesson->type }}{{ $lesson->duration ? ' · ' . $lesson->duration : '' }}</span>
                    </div>
                    <div class="lesson-detail">
                        @if ($lesson->description)
                            <p class="lesson-desc">{{ $lesson->description }}</p>
                        @endif
                        @if ($lesson->file_url)
                            <a class="lesson-file" href="{{ $lesson->file_url }}" target="_blank" rel="noopener">Open attached file ({{ $lesson->file_name }})</a>
                        @elseif (! $lesson->description)
                            <p class="lesson-desc lesson-empty">No description or file attached.</p>
                        @endif
                    </div>
                @endforeach

                @if ($module->quiz)
                    <div class="quiz-row quiz-toggle" onclick="this.nextElementSibling.classList.toggle('open')">
                        <span>Quiz: {{ $module->quiz->title }}</span>
                        <span>{{ $module->quiz->questions_count }} Questions · Pass {{ $module->quiz->passing_score }}%{{ $module->quiz->time_limit ? ' · ' . $module->quiz->time_limit . ' min' : '' }}</span>
                    </div>
                    <div class="quiz-detail">
                        @if ($module->quiz->instructions)
                            <p class="quiz-instructions">{{ $module->quiz->instructions }}</p>
                        @endif
                        @foreach ($module->quiz->questions as $qi => $q)
                            <div class="quiz-question">
                                <div class="quiz-question-head">
                                    <span class="quiz-question-text">Q{{ $qi + 1 }}. {{ $q->question }}</span>
                                    <span class="quiz-question-meta">{{ $q->type }}{{ $q->points ? ' · ' . $q->points . ' pts' : '' }}</span>
                                </div>
                                @if (! empty($q->options))
                                    <ul class="quiz-options">
                                        @foreach ($q->options as $oi => $opt)
                                            <li class="{{ $opt === $q->correct_answer ? 'is-correct' : '' }}">
                                                {{ chr(65 + $oi) }}. {{ $opt }}@if ($opt === $q->correct_answer) — correct @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif ($q->correct_answer)
                                    <div class="quiz-answer">Answer: <strong>{{ $q->correct_answer }}</strong></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="modules-empty">No modules have been added to this course yet.</div>
        @endforelse
    </div>

    {{-- ── Student progress ── --}}
    <div class="section-card">
        <h3>Completion</h3>
        <div class="progress-track"><div class="progress-fill" style="width: {{ $course->percent }}%"></div></div>
        <div class="pct">{{ $course->percent }}% average completion · {{ $course->students }} enrolled</div>
    </div>

    {{-- ── Institutional completion review (Phase 3, Step 11) ──
         Additive section; the aggregate "Completion" card above is
         unchanged. Confirm/Reject is only shown when an enrollment is
         genuinely awaiting academic-unit confirmation. --}}
    <div class="section-card">
        <h3>Enrollment Review</h3>
        @forelse ($enrollments as $enrollment)
            <div class="info-grid" style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #eef0f6;">
                <div class="info-cell"><div class="lbl">Student</div><div class="val">{{ $enrollment->student_name }}</div></div>
                <div class="info-cell"><div class="lbl">Status</div><div class="val">{{ $enrollment->institutional_label }}</div></div>
                <div class="info-cell"><div class="lbl">Completion Status</div><div class="val">{{ ucfirst(str_replace('_', ' ', $enrollment->completion_status)) }}</div></div>
                <div class="info-cell"><div class="lbl">Faculty Verification</div><div class="val">{{ ucfirst(str_replace('_', ' ', $enrollment->faculty_verification_status)) }}</div></div>
                <div class="info-cell"><div class="lbl">Academic Unit Confirmation</div><div class="val">{{ ucfirst(str_replace('_', ' ', $enrollment->academic_unit_confirmation_status)) }}</div></div>
                <div class="info-cell"><div class="lbl">Completed</div><div class="val">{{ $enrollment->completed_at?->format('M j, Y') ?? '—' }}</div></div>
                <div class="info-cell">
                    @if($enrollment->academic_confirmation_actionable)
                        <form method="POST" action="{{ route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]) }}" style="display:flex;gap:6px;">
                            @csrf
                            <button type="submit" name="decision" value="confirmed" class="btn-approve">Confirm</button>
                            <button type="submit" name="decision" value="rejected" class="btn-deny">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="modules-empty">No enrollments for this course yet.</div>
        @endforelse
    </div>

</main>
</div>{{-- /layout --}}

<script>
    function toggleSection(id) {
        const section = document.getElementById(id);
        if (!section || section.classList.contains('locked')) return;
        section.classList.toggle('open');
    }
</script>


{{-- ── Back to top (appears on long pages) ── --}}
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

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
    @include('components.admin-sidebar')
    
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>