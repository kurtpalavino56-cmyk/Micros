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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Courses | Upskill</title>
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
        --tag-cat:#38d6cf;
        --tag-level-bg:#f3e2a6;
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















    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */


    /* hover: text + icon turn gold (non-active links) */








    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0 0 24px;color:var(--muted);font-size:15px;}

    /* Filters bar */
    .filters-bar{display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin-bottom:32px;}
    .search-outline{display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid var(--line);border-radius:10px;padding:12px 16px;flex:1 1 220px;min-width:200px;}
    .search-outline svg{width:18px;height:18px;color:var(--muted);flex-shrink:0;}
    .search-outline input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .select-wrap{position:relative;flex:0 0 auto;}
    .select-wrap select{appearance:none;background:#fff;border:1.5px solid var(--line);border-radius:10px;padding:12px 42px 12px 18px;font-weight:700;color:#111;font-size:15px;min-width:170px;cursor:pointer;}
    .select-wrap svg{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#111;pointer-events:none;}
    .btn-filter{background:var(--navy);color:#fff;font-weight:800;border:none;border-radius:10px;padding:13px 30px;font-size:15px;flex-shrink:0;}
    /* Recommended toggle: navy off, gold on */
    .btn-recommended{display:inline-flex;align-items:center;gap:8px;background:var(--navy);color:#fff;
        font-weight:800;border:2px solid var(--navy);border-radius:10px;padding:11px 20px;font-size:15px;
        flex-shrink:0;cursor:pointer;transition:background .18s ease,color .18s ease,border-color .18s ease,transform .18s ease;}
    .btn-recommended svg{width:17px;height:17px;}
    .btn-recommended:hover{transform:translateY(-1px);}
    .btn-recommended.is-on{background:var(--gold,#dba617);border-color:var(--gold,#dba617);color:#0c0f4d;}
    .btn-recommended.is-on svg{fill:rgba(12,15,77,.18);}

    /* Course grid */
    .courses-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(min(100%, 290px), 1fr));gap:18px;}
    .browse-card{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fff;display:flex;flex-direction:column;min-width:0;transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease;}
    .browse-card:hover{border-color:#b9c4dc;box-shadow:0 8px 20px rgba(19,23,107,.08);transform:translateY(-2px);}
    .card-thumb{height:160px;background:#e8edf7;background-size:cover;background-position:center;}
    .card-body{padding:16px 17px 15px;display:flex;flex-direction:column;flex:1;}
    .tags{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;}
    .tag{padding:4px 9px;border-radius:5px;font-weight:650;font-size:11px;}
    .tag-category{background:#edf1f8;color:#344054;}
    .tag-level{background:#fff6d7;color:#69500b;}
    .browse-card h3{margin:0 0 8px;font-size:18px;color:#182230;line-height:1.35;}
    .browse-card .desc{margin:0 0 14px;color:var(--muted);font-size:13px;line-height:1.55;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
    .meta{display:flex;align-items:center;gap:12px;color:var(--muted);font-size:12px;flex-wrap:wrap;margin-top:auto;padding-top:10px;border-top:1px solid #edf0f4;}
    .meta span{display:flex;align-items:center;gap:6px;}
    .meta svg{width:16px;height:16px;flex-shrink:0;}
    .browse-card__cta{display:flex;align-items:center;justify-content:space-between;padding-top:12px;margin-top:12px;border-top:1px solid #edf0f4;color:#13176b;font-size:13px;font-weight:750;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:40px;text-align:center;color:var(--muted);grid-column:1/-1;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}

        .courses-grid{grid-template-columns:1fr;}
    }
</style>
</head>
<body>

@include('components.student-navigation')

<div class="layout student-sidebar-layout">

    {{-- Sidebar --}}
    @include('components.student-sidebar')

    {{-- Main content --}}
    <main class="main">

        <div class="page-head student-page-heading">
            <h2>Browse All Courses</h2>
            <p>{{ count($courses) }} {{ count($courses) === 1 ? 'course' : 'courses' }} available</p>
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
                    class="btn-recommended {{ !empty($filters['recommended']) ? 'is-on' : '' }}"
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

            <button type="submit" class="btn-filter">Filter</button>
        </form>

        {{-- Course cards --}}
        <div class="courses-grid">
            @forelse ($courses as $course)
                <a href="{{ route('courses.show', $course->id) }}" class="browse-card">
                    <div class="card-thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                    <div class="card-body">
                        <div class="tags">
                            @if($course->category)
                                <span class="tag tag-category">{{ $course->category }}</span>
                            @endif
                            @if($course->level)
                                <span class="tag tag-level">{{ $course->level }}</span>
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
                <div class="empty-state">
                    @if (!empty($filters['recommended']))
                        No recommended courses yet.
                        <div style="font-weight:600;font-size:13px;margin-top:6px;line-height:1.5;">
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

    </main>
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
