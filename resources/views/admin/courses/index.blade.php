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
            --line-soft: #eef1f5;
            --text: #17212b;
            --muted: #687385;
            --faint: #8b95a5;
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
        .page-heading { font-size: 1.35rem; font-weight: 700; color: #000; }
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
        .page-head-tools { display: grid; justify-items: end; gap: 8px; }
        .featured-limit {
            color: var(--muted); font-size: .78rem; text-align: right;
        }
        .featured-limit strong { color: var(--text); }

        /* =========================================================
           COURSE CARD
           Reading order: status → title → people/stats → details
           → progress → one action.
        ========================================================= */
        .course-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); align-items: stretch; gap: 18px; }

        .course-card {
            display: flex; flex-direction: column; min-width: 0;
            background: var(--white); border: 1px solid var(--line); border-radius: 12px;
            padding: 18px 20px 20px; cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .course-card:hover { border-color: #c6d3e8; box-shadow: 0 6px 18px rgba(17,24,39,.06); }
        .course-card.is-pending { box-shadow: inset 3px 0 0 var(--yellow); }
        .course-card.is-pending:hover { box-shadow: inset 3px 0 0 var(--yellow), 0 6px 18px rgba(17,24,39,.06); }
        .course-card:focus-within { outline: 3px solid rgba(18,59,122,.18); outline-offset: 2px; }

        /* 1 · Status line (quiet, small) */
        .card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 30px; margin-bottom: 12px; }
        .card-status-group { display: flex; min-width: 0; align-items: center; flex-wrap: wrap; gap: 6px; font-size: .76rem; color: var(--muted); }
        .status { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: var(--text); }
        .status .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blue); flex: 0 0 7px; }
        .status-pending .dot { background: var(--yellow); }
        .status-denied .dot { background: #d64545; }
        .status-draft .dot { background: #a3adbd; }
        .publication-status { font-weight: 500; color: var(--muted); }
        .publication-status::before { content: "•"; margin-right: 6px; color: var(--faint); }

        .star-form { display: flex; }
        .star-btn {
            display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; padding: 0;
            border: 0; border-radius: 6px; background: none; color: #a3adbd; cursor: pointer;
            transition: color .15s ease, background .15s ease;
        }
        .star-btn:hover { background: var(--surface); color: #c99a00; }
        .star-btn.is-on { color: #e0a800; }
        .star-btn.is-on svg { fill: #e0a800; }
        .star-btn svg { width: 18px; height: 18px; }

        /* 2 · Identity: thumbnail + title + byline (the focal point) */
        .course-intro { display: grid; grid-template-columns: 56px minmax(0, 1fr); align-items: start; gap: 14px; }
        .course-thumb, .course-thumb-placeholder {
            display: flex; width: 56px; height: 56px; align-items: center; justify-content: center;
            overflow: hidden; border-radius: 10px; background: var(--line-soft);
        }
        .course-thumb { object-fit: cover; }
        .course-thumb-placeholder { color: var(--faint); }
        .course-thumb-placeholder svg { width: 24px; height: 24px; }
        .course-intro-copy { min-width: 0; }
        .course-title {
            font-size: 1.05rem; font-weight: 700; line-height: 1.3; letter-spacing: -.01em; color: var(--navy);
            overflow-wrap: anywhere;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .course-by { margin-top: 5px; font-size: .8rem; color: var(--muted); }
        .course-stats { margin-top: 2px; font-size: .78rem; color: var(--faint); }

        /* 3 · Details: ruled list, label left / value right */
        .details {
            margin: 16px 0 16px; border-top: 1px solid var(--line-soft);
        }
        .detail-row {
            display: grid; grid-template-columns: 84px minmax(0, 1fr); align-items: center; gap: 10px;
            min-height: 38px; padding: 8px 0; border-bottom: 1px solid var(--line-soft);
        }
        .detail-label { font-size: .75rem; color: var(--faint); }
        .detail-value {
            display: flex; align-items: center; gap: 8px; min-width: 0;
            font-size: .82rem; font-weight: 500; color: var(--text);
        }
        .detail-value span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .detail-value img { width: 20px; height: 20px; object-fit: contain; flex: 0 0 20px; }
        .detail-value .blank { width: 20px; height: 20px; flex: 0 0 20px; border-radius: 50%; background: var(--line); }
        .detail-value.off { color: var(--faint); font-weight: 400; }

        /* 4 · Progress */
        .course-progress { margin-top: auto; padding-bottom: 16px; }
        .progress-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 7px; font-size: .75rem; color: var(--faint); }
        .progress-head strong { font-size: .85rem; font-weight: 700; color: var(--navy); }
        .progress-track { height: 5px; border-radius: 999px; background: var(--line-soft); overflow: hidden; }
        .progress-fill { height: 100%; background: var(--blue); border-radius: 999px; }

        /* 5 · One action */
        .actions { display: flex; }
        .actions form, .actions a { flex: 1; display: flex; }
        .btn {
            flex: 1; min-height: 40px; padding: 9px 14px; border-radius: 8px; border: 1px solid var(--line);
            background: var(--white); color: var(--navy); font: inherit; font-size: .82rem; font-weight: 600;
            text-align: center; text-decoration: none; cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }
        .btn:hover { border-color: #b9c6dc; background: var(--surface); }
        .btn-approve { background: var(--navy); border-color: var(--navy); color: #fff; }
        .btn-approve:hover { background: #0c1f6b; border-color: #0c1f6b; }
        .btn-publish { color: var(--blue); border-color: #c4d6f0; }
        .btn-publish:hover { background: var(--soft); }
        .btn-unpublish { color: var(--muted); }

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
            <div class="page-head-tools">
                <input type="search" class="search" id="course-search" placeholder="Search courses" aria-label="Search courses">
                <div class="featured-limit">
                    Featured courses: <strong>{{ $featuredCourseCount }} / {{ \App\Services\CourseModerationService::MAX_FEATURED_COURSES }}</strong>.
                    @if ($featuredCourseCount >= \App\Services\CourseModerationService::MAX_FEATURED_COURSES)
                        Unfeature a course before featuring another.
                    @else
                        You can feature up to {{ \App\Services\CourseModerationService::MAX_FEATURED_COURSES }} courses on the homepage.
                    @endif
                </div>
            </div>
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

        <x-flash-toast :message="session('success') ?? $errors->first('feature')" :type="session('success') ? 'success' : 'error'" />

        @if ($courses->isEmpty())
            <div class="empty-state">No courses yet. Courses created by faculty will appear here for review.</div>
        @else
            <div class="course-grid" id="course-grid">
                @foreach ($courses as $course)
                    <div class="course-card {{ $course->status_key === 'pending' ? 'is-pending' : '' }}"
                         data-status="{{ $course->status_key }}"
                         data-search="{{ strtolower($course->title . ' ' . ($course->instructor ?? '')) }}"
                         onclick="window.location='{{ route('admin.courses.show', $course->id) }}'">

                        {{-- 1 · Status --}}
                        <div class="card-top">
                            <div class="card-status-group">
                                <span class="status status-{{ $course->status_key }}">
                                    <span class="dot"></span>{{ $course->status_key === 'approved' ? 'Approved' : $course->status }}
                                </span>
                                @if ($course->can_toggle_publish)
                                    <span class="publication-status">{{ $course->is_published ? 'Published' : 'Unpublished' }}</span>
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

                        {{-- 2 · Identity --}}
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
                                <div class="course-by">By {{ $course->instructor ?? 'Faculty' }}</div>
                                <div class="course-stats">{{ $course->faculty }} faculty &middot; {{ $course->students }} students</div>
                            </div>
                        </div>

                        {{-- 3 · Details --}}
                        <div class="details">
                            <div class="detail-row">
                                <span class="detail-label">Badge</span>
                                <div class="detail-value {{ $course->badge_icon || $course->badge ? '' : 'off' }}">
                                    @if ($course->badge_icon)
                                        <img src="{{ $course->badge_icon }}" alt="">
                                    @elseif ($course->badge)
                                        <span class="blank"></span>
                                    @endif
                                    <span>{{ $course->badge ?: 'No badge yet' }}</span>
                                </div>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Certificate</span>
                                <div class="detail-value {{ $course->certificate_enabled ? '' : 'off' }}">
                                    <span>
                                        @if (! $course->certificate_enabled)
                                            None
                                        @else
                                            {{ $course->certificate_mode === 'upload' ? 'Uploaded' : 'Auto-generated' }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if (! empty($course->related_skills))
                                <div class="detail-row">
                                    <span class="detail-label">Related</span>
                                    <div class="detail-value">
                                        <span>
                                            {{ implode(', ', array_slice($course->related_skills, 0, 3)) }}
                                            @if (count($course->related_skills) > 3) +{{ count($course->related_skills) - 3 }} @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- 4 · Progress --}}
                        <div class="course-progress">
                            <div class="progress-head"><span>Completion</span><strong>{{ $course->percent }}%</strong></div>
                            <div class="progress-track"><div class="progress-fill" style="width: {{ $course->percent }}%"></div></div>
                        </div>

                        {{-- 5 · Action --}}
                        @if ($course->status_key === 'pending')
                            <div class="actions" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-approve">Review course</a>
                            </div>
                        @elseif ($course->can_toggle_publish)
                            <div class="actions" onclick="event.stopPropagation();">
                                <form method="POST" action="{{ route('admin.courses.publish', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn {{ $course->is_published ? 'btn-unpublish' : 'btn-publish' }}">
                                        {{ $course->is_published ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="actions" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn">View details</a>
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