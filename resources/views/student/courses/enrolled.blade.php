{{--
    resources/views/student/Enrolled_Courses.blade.php

    The student's enrolled courses, organized into two areas:
      - Not Yet Completed  (Start / Continue buttons)
      - Completed Courses  (Review button)

    Expected data from StudentController::enrolledCourses():
        'user'              => UserPresenter::student(Auth::user())
        'inProgressCourses' => collection (id,title,category,thumbnail_url,progress_percent)
        'completedCourses'  => collection (same shape)
--}}
<x-layout title="Enrolled Courses | Upskill" shell="student" page="courses-enrolled">

{{-- Sidebar --}}
{{-- Main content --}}
    <div class="page-content">

        <div class="page-head student-page-heading page-header ui-page-header">
            <div>
                <h2 class="ui-page-title">Enrolled Courses</h2>
                <p class="ui-page-subtitle">All the courses you're enrolled in, organized by your progress.</p>
            </div>
            <a href="{{ route('courses.browse') }}">
                <button class="btn-outline ui-button ui-button--secondary" type="button">+ Enroll More</button>
            </a>
        </div>

        @php
            $inProgressCourses = $inProgressCourses ?? collect();
            $completedCourses  = $completedCourses ?? collect();
        @endphp

        @if ($inProgressCourses->isEmpty() && $completedCourses->isEmpty())
            <div class="empty-state ui-empty-state">
                You haven't enrolled in any courses yet.
                <br>
                <a href="{{ route('courses.browse') }}" class="enroll-more">Browse courses to get started</a>
            </div>
        @else
            <h3 class="courses-area-title">
                Not Yet Completed
                <span class="count-chip">{{ $inProgressCourses->count() }}</span>
            </h3>
            @forelse ($inProgressCourses as $course)
                <div class="course-card ui-card-surface">
                    <div class="thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                    <div class="course-info">
                        <h4>{{ $course->title }}</h4>
                        @if($course->category)
                            <div class="cat">{{ $course->category }}</div>
                        @endif
                        @include('components.course-rating', ['rating' => $course->rating_average, 'count' => $course->review_count])
                        <div class="progress-track ui-progress-track">
                            <div class="progress-fill ui-progress-fill" style="width:{{ $course->progress_percent ?? 0 }}%"></div>
                        </div>
                        <div class="pct">{{ $course->progress_percent ?? 0 }}% Complete</div>
                    </div>
                    <a href="{{ route('courses.show', ['id' => $course->id, 'from' => 'enrollments']) }}">
                        <button class="btn-start ui-button ui-button--primary" type="button">
                            {{ ($course->progress_percent ?? 0) > 0 ? 'Continue' : 'Start' }}
                        </button>
                    </a>
                </div>
            @empty
                <div class="empty-state ui-empty-state">Nothing in progress right now.</div>
            @endforelse

            <h3 class="courses-area-title">
                Completed Courses
                <span class="count-chip">{{ $completedCourses->count() }}</span>
            </h3>
            @forelse ($completedCourses as $course)
                <div class="course-card is-completed ui-card-surface">
                    <div class="thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                    <div class="course-info">
                        <h4>{{ $course->title }}</h4>
                        @if($course->category)
                            <div class="cat">{{ $course->category }}</div>
                        @endif
                        @include('components.course-rating', ['rating' => $course->rating_average, 'count' => $course->review_count])
                        <div class="progress-track ui-progress-track">
                            <div class="progress-fill ui-progress-fill" style="width:{{ $course->progress_percent ?? 0 }}%"></div>
                        </div>
                        <div class="pct">{{ $course->progress_percent ?? 0 }}% Complete</div>
                    </div>
                    <a href="{{ route('courses.show', ['id' => $course->id, 'from' => 'enrollments']) }}">
                        <button class="btn-start btn-completed ui-button ui-button--secondary" type="button">Review</button>
                    </a>
                </div>
            @empty
                <div class="empty-state ui-empty-state">No completed courses yet - finish a course and it will appear here.</div>
            @endforelse
        @endif

    </div>

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
