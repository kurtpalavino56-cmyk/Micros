
<x-admin.layout title="Upskill – Courses & Badges" page="admin-courses-index">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">

    {{-- SHARED ADMIN SIDEBAR --}}
    {{-- MAIN CONTENT --}}
    <main class="main">
    <div class="page-wrap">

        <div class="page-head">
            <div>
                <div class="page-heading">Course Approvals</div>
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
                                @if ($course->status_key !== 'approved')
                                    <span class="status status-{{ $course->status_key }}">
                                        <span class="dot"></span>{{ $course->status }}
                                    </span>
                                @endif
                                @if ($course->can_toggle_publish)
                                    <span class="publication-status adm-publication-{{ $course->is_published ? 'published' : 'unpublished' }}">{{ $course->is_published ? 'Published' : 'Unpublished' }}</span>
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

            <div class="empty-state adm-inline-admin-courses-index-1" id="no-match">No courses match your filter.</div>
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

<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function t() { btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true });
        t();
    })();
</script>
</x-admin.layout>
