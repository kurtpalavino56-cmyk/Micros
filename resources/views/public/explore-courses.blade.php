{{--
    resources/views/Explore_Courses.blade.php

    "View all Courses" from the homepage.

    Extends layouts.app so it inherits the SAME navbar and footer as the
    homepage. It previously carried its own cut-down topbar (a gold UPSKILL
    wordmark and a "Back to Home" link), which is why the header changed
    when you came here from the homepage.
--}}
@extends('layouts.app')

@section('title', 'All Courses – UpSkill PSU')

@push('styles')
<style>
        /* Fills whatever height is left so its grey background reaches the
       footer on short pages (see the sticky-footer rules in layouts.app). */
    .explore { padding: 3.5rem 0 4.5rem; background: #f4f6fb; }

    .explore__head { margin-bottom: 2rem; }
    .explore__title {
        font-family: var(--font-display);
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        font-weight: 800;
        color: var(--navy);
        margin-bottom: 0.35rem;
    }
    .explore__sub { color: var(--text-muted); font-size: 0.92rem; }
    .explore__filters { display:flex; gap:.55rem; flex-wrap:wrap; margin:0 0 1.6rem; }
    .explore__filter { display:inline-flex; align-items:center; padding:.55rem .9rem; border:1px solid #dce4f1; border-radius:999px; background:rgba(255,255,255,.72); color:var(--navy); font-size:.78rem; font-weight:800; }
    .explore__filter:hover, .explore__filter.is-active { background:var(--navy); color:#fff; border-color:var(--navy); }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.4rem;
    }

    /* Card styling, matching the homepage cards */
    .course-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-card);
        display: flex;
        flex-direction: column;
        transition: transform var(--transition), box-shadow var(--transition);
    }
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(10,31,110,0.14);
    }
    .course-card__thumb { display: block; height: 170px; background: #e3e6f0; }
    .course-card__thumb img { width: 100%; height: 100%; object-fit: cover; }
    .course-card__thumb-placeholder {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #e3e6f0, #cfd4ea);
    }
    .course-card__body {
        padding: 1.15rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        flex: 1;
    }
    .course-card__tags { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .course-card__title { font-size: 1rem; line-height: 1.35; }
    .course-card__title a { color: var(--navy); }
    .course-card__title a:hover { color: var(--gold); }
    .course-card__desc {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.5;
        flex: 1;
    }
    .course-card__meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        flex-wrap: wrap;
    }
    .course-card__rating { color: var(--gold); letter-spacing: 1px; }

    .explore__empty {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: var(--text-muted);
        font-weight: 600;
        box-shadow: var(--shadow-card);
    }

    /* Pagination */
    .explore__pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2.25rem;
    }
    .explore__page-btn {
        display: inline-block;
        padding: 0.55rem 1.3rem;
        border-radius: 50px;
        background: var(--navy);
        color: var(--white);
        font-weight: 600;
        font-size: 0.9rem;
        transition: background var(--transition), transform var(--transition);
    }
    .explore__page-btn:hover { background: var(--navy-dark); transform: translateY(-2px); }
    .explore__page-btn.is-disabled {
        background: var(--line);
        color: var(--text-muted);
        cursor: not-allowed;
        transform: none;
    }
    .explore__page-status { font-size: 0.88rem; font-weight: 600; color: var(--text-muted); }

    @media (max-width: 520px) {
        .courses-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<section class="explore">
    <div class="container">

        <div class="explore__head">
            <h1 class="explore__title">All Courses</h1>
            <p class="explore__sub">
                Every published micro-credential from PSU faculty &mdash;
                {{ $courses->total() }} course{{ $courses->total() === 1 ? '' : 's' }}
            </p>
        </div>

        @if(count($categories ?? []))
            <nav class="explore__filters" aria-label="Course categories">
                <a class="explore__filter {{ empty($category) ? 'is-active' : '' }}" href="{{ route('explore') }}">All courses</a>
                @foreach($categories as $item)
                    <a class="explore__filter {{ ($category ?? null) === $item ? 'is-active' : '' }}" href="{{ route('explore', ['category' => $item]) }}">{{ $item }}</a>
                @endforeach
            </nav>
        @endif

        @if(count($courses))
            <div class="courses-grid">
                @foreach($courses as $course)
                    @include('components.course-card', ['course' => $course])
                @endforeach
            </div>

            @if ($courses->hasPages())
                <nav class="explore__pagination" aria-label="Course pages">
                    @if ($courses->onFirstPage())
                        <span class="explore__page-btn is-disabled" aria-disabled="true">&laquo; Prev</span>
                    @else
                        <a class="explore__page-btn" href="{{ $courses->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
                    @endif

                    <span class="explore__page-status">Page {{ $courses->currentPage() }} of {{ $courses->lastPage() }}</span>

                    @if ($courses->hasMorePages())
                        <a class="explore__page-btn" href="{{ $courses->nextPageUrl() }}" rel="next">Next &raquo;</a>
                    @else
                        <span class="explore__page-btn is-disabled" aria-disabled="true">Next &raquo;</span>
                    @endif
                </nav>
            @endif
        @else
            <div class="explore__empty">No published courses yet. Check back soon!</div>
        @endif

    </div>
</section>
@endsection
