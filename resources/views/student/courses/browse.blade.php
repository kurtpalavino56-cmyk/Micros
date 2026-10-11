{{--
    resources/views/courses_browse.blade.php

    Expected data from the controller, e.g.:

    return view('student.courses.browse', [
        'user'    => $user,                 // Auth::user() - needs ->name
        'courses' => $availableCourses,      // collection of Course models
        'filters' => [                       // optional, for pre-filled search/filter state
            'q' => request('q'),
            'category' => request('category'),
            'level' => request('level'),
        ],
        'categories' => $categoryList,       // optional, for the category dropdown options
        'levels'     => $levelList,          // optional, for the level dropdown options
    ]);

    Each $course is expected to expose:
        ->id, ->title, ->description, ->category, ->level,
        ->instructor, ->duration, ->lessons_count, ->thumbnail_url
--}}
<x-layout title="Browse Courses | Upskill" shell="student" page="courses-browse">

{{-- Sidebar --}}
{{-- Main content --}}
    <div class="page-content">

        <div class="page-head student-page-heading page-header ui-page-header">
            <h2 class="ui-page-title">Browse All Courses</h2>
            <p class="ui-page-subtitle">{{ count($courses) }} {{ count($courses) === 1 ? 'course' : 'courses' }} available</p>
        </div>

        {{-- Search + filters --}}
        <form action="{{ route('courses.browse') }}" method="GET" class="filters-bar">
            <div class="search-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" name="q" placeholder="Search" value="{{ $filters['q'] ?? '' }}">
            </div>

            {{-- Recommended: navy by default, gold when on. The hidden input
                 carries the state through the normal Filter submit. --}}
            <input type="hidden" name="recommended" id="recommendedInput"
                   value="{{ !empty($filters['recommended']) ? 1 : 0 }}">
            <button type="button" id="btnRecommended"
                    class="btn-recommended ui-button ui-button--primary {{ !empty($filters['recommended']) ? 'is-on' : '' }}"
                    aria-pressed="{{ !empty($filters['recommended']) ? 'true' : 'false' }}"
                    onclick="toggleRecommended()"
                    title="Match courses to the skills on your profile">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M12 3l2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6L3.3 9.4l6.1-.8L12 3z"/>
                </svg>
                Recommended
            </button>

            <div class="select-wrap">
                <select name="category">
                    <option value="">All Categories</option>
                    @foreach ($categories ?? [] as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </div>

            <div class="select-wrap">
                <select name="level">
                    <option value="">All Levels</option>
                    @foreach ($levels ?? [] as $level)
                        <option value="{{ $level }}" @selected(($filters['level'] ?? '') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </div>

            <button type="submit" class="btn-filter ui-button ui-button--primary">Filter</button>
        </form>

        {{-- Course cards --}}
        <div class="courses-grid">
            @forelse ($courses as $course)
                <a href="{{ route('courses.show', $course->id) }}" class="browse-card ui-card-surface">
                    <div class="card-thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                    <div class="card-body">
                        <div class="tags">
                            @if($course->category)
                                <span class="tag tag-category ui-badge">{{ $course->category }}</span>
                            @endif
                            @if($course->level)
                                <span class="tag tag-level ui-badge">{{ $course->level }}</span>
                            @endif
                        </div>
                        <h3>{{ $course->title }}</h3>
                        @php
                            $descPreview = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) ($course->description ?? ''))))), 120);
                        @endphp
                        @if($descPreview !== '')
                            <p class="desc">{{ $descPreview }}</p>
                        @endif
                        <div class="meta">
                            @include('components.course-rating', ['rating' => $course->rating_average, 'count' => $course->review_count])
                            @if($course->instructor)
                                <span>{{ $course->instructor }}</span>
                            @endif
                            @if($course->learning_hours || $course->duration)
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                    {{ $course->learning_hours ? $course->learning_hours.' hours' : $course->duration }}
                                </span>
                            @endif
                            @if($course->lessons_count)
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    {{ $course->lessons_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state ui-empty-state">
                    @if (!empty($filters['recommended']))
                        No recommended courses yet.
                        <div style="font-weight:600;font-size:var(--text-sm);margin-top:6px;line-height:1.5;">
                            Recommendations compare your profile skills with what each course
                            is related to. Turn <strong>Recommended</strong> off to see every
                            course, or add more skills from your Profile.
                        </div>
                    @else
                        No courses match your search right now. Try adjusting your filters.
                    @endif
                </div>
            @endforelse
        </div>

    </div>


<script>
    // Flip the button and the hidden field together. The value travels with
    // the form when Filter is pressed, so the server does the matching.
    function toggleRecommended() {
        var btn   = document.getElementById('btnRecommended');
        var input = document.getElementById('recommendedInput');
        if (!btn || !input) return;

        var on = btn.classList.toggle('is-on');
        input.value = on ? '1' : '0';
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    }
</script>

{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

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
</x-layout>
