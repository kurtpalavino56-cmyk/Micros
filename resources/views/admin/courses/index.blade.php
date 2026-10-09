<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Courses &amp; Badges</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy: #071550;
            --blue: #0b4ea2;
            --soft: #eaf2ff;
            --yellow: #f4c430;
            --yellow-dark: #e8b91f;
            --yellow-soft: #fff8dc;
            --surface: #f5f7fa;
            --white: #ffffff;
            --line: #e2e7ef;
            --text: #17212b;
            --muted: #687385;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Layout (topbar + shared sidebar offset) */
        .layout { display: block; min-height: calc(100vh - 66px); }
        .main {
            margin-left: 238px !important;
            width: calc(100% - 238px) !important;
            min-width: 0;
            padding: 30px 34px 56px;
        }
        .page-wrap { max-width: 1120px; margin: 0 auto; }

        /* Header */
        .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .page-heading { font-size: 1.35rem; font-weight: 700; color: var(--blue); }
        .page-sub { margin-top: 3px; font-size: .85rem; color: var(--muted); }

        .search {
            min-width: 240px; padding: 9px 13px;
            border: 1px solid var(--line); border-radius: 8px;
            background: var(--white); font: inherit; font-size: .85rem; color: var(--text); outline: none;
        }
        .search:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(11,78,162,.1); }

        /* Status tabs */
        .tabs { display: flex; flex-wrap: wrap; gap: 22px; border-bottom: 1px solid var(--line); margin: 18px 0 20px; }
        .tab {
            padding: 9px 0; border: 0; border-bottom: 2px solid transparent; background: none;
            font: inherit; font-size: .88rem; color: var(--muted); cursor: pointer;
        }
        .tab:hover { color: var(--navy); }
        .tab.active { color: var(--blue); font-weight: 600; border-bottom-color: var(--yellow); }
        .tab .n { margin-left: 4px; font-size: .78rem; color: var(--muted); }

        .alert-success {
            background: #eaf8ef; border: 1px solid #ccebd6; color: #166534;
            border-radius: 10px; padding: 11px 16px; font-size: .86rem; font-weight: 600; margin-bottom: 18px;
        }

        /* Grid + card */
        .course-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); align-items: stretch; gap: 18px; }

        .course-card {
            display: flex; min-width: 0; min-height: 276px; flex-direction: column; gap: 13px;
            background: var(--white); border: 1px solid var(--line); border-radius: 14px;
            padding: 20px; cursor: pointer;
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }
        .course-card:hover { transform: translateY(-2px); border-color: #c6d3e8; box-shadow: 0 8px 22px rgba(17,24,39,.07); }
        .course-card.is-pending { border-color: #e8c95a; box-shadow: inset 3px 0 0 var(--yellow); }
        .course-card:focus-within { outline: 3px solid rgba(18,59,122,.18); outline-offset: 2px; }

        .card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 28px; }
        .card-status-group { display: flex; min-width: 0; align-items: center; flex-wrap: wrap; gap: 8px; }
        .status { display: inline-flex; align-items: center; gap: 7px; font-size: .78rem; font-weight: 600; color: var(--muted); }
        .status .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--blue); flex: 0 0 8px; }
        .status-pending .dot { background: var(--yellow); }
        .status-denied .dot { background: #d64545; }
        .status-draft .dot { background: #a3adbd; }
        .publication-status {
            display: inline-flex; align-items: center; border: 1px solid transparent; border-radius: 999px;
            padding: 4px 9px; font-size: .68rem;
            font-weight: 700; letter-spacing: .02em;
        }
        .publication-status.is-published { background: #edf7f1; border-color: #dcefe3; color: #176b3b; }
        .publication-status.is-unpublished { background: #fff7e5; border-color: #f3e5c3; color: #805400; }

        .star-form { display: flex; }
        .star-btn {
            display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; padding: 0; border: 1px solid transparent; border-radius: 8px; background: none; color: #8b95a5; cursor: pointer;
            transition: color .15s ease, background .15s ease, border-color .15s ease;
        }
        .star-btn:hover { border-color: #f0dfb8; background: #fff8e7; color: #c99a00; }
        .star-btn.is-on { color: #e0a800; }
        .star-btn.is-on svg { fill: #e0a800; }
        .star-btn svg { width: 18px; height: 18px; }

        .course-title { min-height: 2.6em; font-size: 1.02rem; font-weight: 650; line-height: 1.3; color: var(--text); overflow-wrap: anywhere; }
        .course-meta { min-height: 1.25em; font-size: .8rem; line-height: 1.5; color: var(--muted); }
        .course-intro { display: grid; grid-template-columns: 64px minmax(0, 1fr); align-items: start; gap: 13px; }
        .course-thumb, .course-thumb-placeholder { display: flex; width: 64px; height: 64px; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--line); border-radius: 9px; background: #f3f5f8; }
        .course-thumb { object-fit: cover; }
        .course-thumb-placeholder { color: #8791a0; }
        .course-thumb-placeholder svg { width: 25px; height: 25px; }
        .course-intro-copy { min-width: 0; }
        .course-intro-copy .course-title { min-height: 0; }
        .course-intro-copy .course-meta { margin-top: 7px; }

        .chips { display: flex; min-height: 29px; flex-wrap: wrap; align-items: flex-start; gap: 7px; }
        .chip {
            display: inline-flex; align-items: center; gap: 7px; max-width: min(100%, 260px); min-height: 27px;
            padding: 4px 10px 4px 5px; border: 1px solid #e7ebf0; border-radius: 8px;
            background: #f8f9fb; color: #334155; font-size: .73rem; font-weight: 600;
        }
        .chip img { width: 18px; height: 18px; object-fit: contain; flex: 0 0 18px; }
        .chip .blank { width: 18px; height: 18px; flex: 0 0 18px; border-radius: 50%; background: #e2e7ef; }
        .chip.plain { padding-left: 10px; }
        .chip.off { background: #f8f9fb; color: #8791a0; }
        .chip span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .related { min-height: 1.2em; color: var(--muted); font-size: .75rem; line-height: 1.4; }

        .course-progress { margin-top: auto; }
        .progress-track { height: 5px; border-radius: 999px; background: #edf0f4; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--blue); border-radius: 2px; }
        .pct { margin-top: 6px; font-size: .75rem; font-weight: 600; color: var(--muted); }

        /* Actions */
        .actions { display: flex; gap: 8px; margin-top: 1px; }
        .actions form, .actions a { flex: 1; display: flex; }
        .btn {
            flex: 1; min-height: 40px; padding: 9px 14px; border-radius: 8px; border: 1px solid var(--line);
            background: var(--white); color: var(--text); font: inherit; font-size: .8rem; font-weight: 650; text-align: center; text-decoration: none; cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }
        .btn:hover { border-color: #cbd5e1; background: #f5f7fa; }
        .btn-approve { background: var(--yellow); border-color: var(--yellow); color: var(--navy); }
        .btn-approve:hover { background: var(--yellow-dark); }
        .btn-publish { background: #edf7f1; border-color: #cce9d7; color: #176b3b; }
        .btn-publish:hover { background: #dff1e6; }
        .btn-unpublish { background: #fff4dc; border-color: #f0dfb8; color: #805400; }
        .btn-unpublish:hover { background: #ffedc2; }
        .empty-state {
            padding: 40px 24px; border: 1px solid var(--line); border-radius: 12px;
            background: var(--white); color: var(--muted); text-align: center; font-size: .88rem;
        }

        @media (max-width: 1024px) {
            .main { margin-left: 0 !important; width: 100% !important; padding: 24px 20px 48px; }
        }
        @media (max-width: 560px) {
            .main { padding: 18px 12px 40px; }
            .search { width: 100%; }
            .course-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])

<div class="layout admin-layout">

    {{-- SHARED ADMIN SIDEBAR --}}
    @include('components.admin-sidebar')

    {{-- MAIN CONTENT --}}
    <main class="main">
    <div class="page-wrap">

        <div class="page-head">
            <div>
                <div class="page-heading">Courses and badges</div>
                <div class="page-sub">Review courses and manage their completion badges.</div>
            </div>
            <input type="search" class="search" id="course-search" placeholder="Search courses" aria-label="Search courses">
        </div>

        @php
            $count = fn ($key) => $courses->where('status_key', $key)->count();
        @endphp

        <div class="tabs" role="tablist">
            <button type="button" class="tab active" data-filter="all">All<span class="n">{{ $courses->count() }}</span></button>
            <button type="button" class="tab" data-filter="pending">Pending review<span class="n">{{ $count('pending') }}</span></button>
            <button type="button" class="tab" data-filter="approved">Approved<span class="n">{{ $count('approved') }}</span></button>
            <button type="button" class="tab" data-filter="denied">Denied<span class="n">{{ $count('denied') }}</span></button>
        </div>

        <x-flash-toast :message="session('success')" />

        @if ($courses->isEmpty())
            <div class="empty-state">No courses yet. Courses created by faculty will appear here for review.</div>
        @else
            <div class="course-grid" id="course-grid">
                @foreach ($courses as $course)
                    <div class="course-card {{ $course->status_key === 'pending' ? 'is-pending' : '' }}"
                         data-status="{{ $course->status_key }}"
                         data-search="{{ strtolower($course->title . ' ' . ($course->instructor ?? '')) }}"
                         onclick="window.location='{{ route('admin.courses.show', $course->id) }}'">

                        <div class="card-top">
                            <div class="card-status-group">
                                <span class="status status-{{ $course->status_key }}">
                                    <span class="dot"></span>{{ $course->status_key === 'approved' ? 'Approved' : $course->status }}
                                </span>
                                @if ($course->can_toggle_publish)
                                    <span class="publication-status {{ $course->is_published ? 'is-published' : 'is-unpublished' }}">
                                        {{ $course->is_published ? 'Published' : 'Unpublished' }}
                                    </span>
                                @endif
                            </div>

                            <form class="star-form" method="POST" action="{{ route('admin.courses.feature', $course->id) }}" onclick="event.stopPropagation();">
                                @csrf
                                <button type="submit"
                                        class="star-btn {{ $course->is_featured ? 'is-on' : '' }}"
                                        aria-label="{{ $course->is_featured ? 'Unfeature course' : 'Feature course on homepage' }}"
                                        title="{{ $course->is_featured ? 'Shown on the homepage. Click to unfeature.' : 'Feature this course on the homepage' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg>
                                </button>
                            </form>
                        </div>

                        <div class="course-intro">
                            @if ($course->thumbnail_url)
                                <img class="course-thumb" src="{{ $course->thumbnail_url }}" alt="" loading="lazy">
                            @else
                                <div class="course-thumb-placeholder" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m2 9 10-5 10 5-10 5L2 9Z"/><path d="M6 11v5c3.5 2.5 8.5 2.5 12 0v-5M22 9v6"/></svg>
                                </div>
                            @endif
                            <div class="course-intro-copy">
                                <div class="course-title">{{ $course->title }}</div>
                                <div class="course-meta">
                                    {{ $course->students }} students · {{ $course->faculty }} faculty · By {{ $course->instructor ?? 'Faculty' }}
                                </div>
                            </div>
                        </div>

                        <div class="chips">
                            <div class="chip {{ $course->badge_icon ? '' : 'off' }}">
                                @if ($course->badge_icon)
                                    <img src="{{ $course->badge_icon }}" alt="">
                                @else
                                    <span class="blank"></span>
                                @endif
                                <span>{{ $course->badge ?: 'No badge yet' }}</span>
                            </div>

                            <div class="chip plain {{ $course->certificate_enabled ? '' : 'off' }}">
                                <span>
                                    @if (! $course->certificate_enabled)
                                        No certificate
                                    @else
                                        {{ $course->certificate_mode === 'upload' ? 'Uploaded certificate' : 'Auto certificate' }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- "Related on": internal, faculty/admin only --}}
                        <div class="related">
                            @if (! empty($course->related_skills))
                                Related: {{ implode(', ', array_slice($course->related_skills, 0, 3)) }}
                                @if (count($course->related_skills) > 3) +{{ count($course->related_skills) - 3 }} @endif
                            @endif
                        </div>

                        <div class="course-progress">
                            <div class="progress-track"><div class="progress-fill" style="width: {{ $course->percent }}%"></div></div>
                            <div class="pct">{{ $course->percent }}% complete</div>
                        </div>

                        @if ($course->status_key === 'pending')
                            <div class="actions" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn">Review course</a>
                            </div>
                        @elseif ($course->can_toggle_publish)
                            <div class="actions" onclick="event.stopPropagation();">
                                <form method="POST" action="{{ route('admin.courses.publish', $course->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="btn {{ $course->is_published ? 'btn-unpublish' : 'btn-publish' }}"
                                            >
                                        {{ $course->is_published ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="actions" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn">View course details</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="empty-state" id="no-match" style="display:none;">No courses match your filter.</div>
        @endif

    </div>
    </main>
</div>

<script>
    (function () {
        var tabs = document.querySelectorAll('.tab');
        var cards = document.querySelectorAll('.course-card');
        var search = document.getElementById('course-search');
        var none = document.getElementById('no-match');
        var filter = 'all';

        function apply() {
            var q = (search.value || '').trim().toLowerCase();
            var shown = 0;
            cards.forEach(function (card) {
                var ok = (filter === 'all' || card.dataset.status === filter) &&
                         (!q || card.dataset.search.indexOf(q) !== -1);
                card.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            if (none) none.style.display = shown ? 'none' : 'block';
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                filter = tab.dataset.filter;
                apply();
            });
        });
        if (search) search.addEventListener('input', apply);
    })();
</script>

{{-- Back to top --}}
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

@include('components.responsive')
</body>
</html>
