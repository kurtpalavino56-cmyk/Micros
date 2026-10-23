{{--
    resources/views/student/Course_Description.blade.php

    Expected data from the controller, e.g.:

    return view('student.courses.description', [
        'user'              => $user,               // Auth::user() - needs ->name
        'course'            => $course,             // single Course model
        //   ->id, ->title, ->short_description, ->description, ->category, ->level, ->is_featured
        //   ->instructor (string name), ->duration, ->learning_hours, ->enrolled_count
        //   ->thumbnail_url, ->skills (array), ->learning_outcomes (array)
        //   ->pqf_level, ->target_learners, ->delivery_mode, ->credit_bearing,
        //   ->credit_equivalency, ->equivalent_course, ->badge_name, ->badge_description,
        //   ->assessment_strategy, ->grading_rubric
        'instructor_detail' => $instructorDetail,   // ->name, ->department, ->bio, ->avatar_url
        'modules'           => $modules,            // modules with lessons, lesson quizzes, and optional module quiz
        'finalExam'         => $finalExam ?? null,   // separate course-completion assessment
        'prerequisites'     => $prerequisites,      // each: ->title, ->completed
        'prerequisites_met' => $prerequisitesMet,
        'missing_prerequisite_titles' => $missing,
        'is_enrolled'       => $isEnrolled,
        'is_completed'      => $isCompleted,
        'progress_percent'  => $progressPercent,    // int 0-100
    ]);

    Layout: hero + content on the left, sticky enroll card on the right.
    Below 1100px the card moves directly under the hero.
--}}
@push('styles')
<style>
    body.app-shell.page-course-description .course-tabs {
        display: flex;
        gap: 2rem;
        margin: 0 0 2rem;
        border-bottom: 1px solid #dfe4ee;
    }
    body.app-shell.page-course-description .course-tab {
        appearance: none;
        margin-bottom: -1px;
        padding: 1.1rem 0 .85rem;
        border: 0;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        background: none;
        color: #5b667a;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }
    body.app-shell.page-course-description .course-tab:hover { color: #1d2433; }
    body.app-shell.page-course-description .course-tab[aria-selected="true"] { color: #172f94; border-bottom-color: #172f94; }
    body.app-shell.page-course-description .course-tab-count { margin-left: .35rem; color: #5b667a; font-size: .85rem; font-weight: 400; }
    body.app-shell.page-course-description .course-details-tabs:not(.has-js) .course-tabs { display: none; }
    body.app-shell.page-course-description .course-tab-panel[hidden] { display: none !important; }
    body.app-shell.page-course-description .course-details-tabs { padding: 0 32px; }
    body.app-shell.page-course-description .course-public-section {
        margin: 0 0 2.25rem;
        padding: 0 0 2.25rem;
        border-bottom: 1px solid #dfe4ee;
    }
    body.app-shell.page-course-description .course-public-section.info-panel {
        overflow: visible;
        border: 0;
        border-bottom: 1px solid #dfe4ee;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    body.app-shell.page-course-description .course-public-section .ui-card-header {
        display: block;
        min-height: 0;
        margin: 0 0 1.1rem;
        padding: 0;
        border: 0;
        background: transparent;
    }
    body.app-shell.page-course-description .course-public-section .ui-card-header__title {
        color: #0f1f63;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -.015em;
    }
    body.app-shell.page-course-description .course-public-section:last-child { margin-bottom: 0; border-bottom: 0; }
    body.app-shell.page-course-description .course-public-section > h2 {
        margin: 0 0 1.1rem;
        color: #0f1f63;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -.015em;
    }
    body.app-shell.page-course-description .course-public-section > .info-panel-body { padding: 0; }
    body.app-shell.page-course-description .course-public-section .detail-grid { margin: 0; }
    body.app-shell.page-course-description .course-outline-meta { margin: -.5rem 0 1rem; color: #5b667a; font-size: .92rem; }
    @media (max-width: 640px) {
        body.app-shell.page-course-description .course-public-section > h2 { font-size: 1.3rem; }
        body.app-shell.page-course-description .course-public-section .ui-card-header__title { font-size: 1.3rem; }
        body.app-shell.page-course-description .course-tabs { gap: 1.5rem; margin-bottom: 1.5rem; }
    }
    @media (max-width: 760px) {
        body.app-shell.page-course-description .course-details-tabs { padding: 0 18px; }
    }
</style>
@endpush
<x-layout title="{{ $course->title ?? 'Course' }} | Upskill" shell="student" page="course-description">

<div class="page-content">

        {{-- Breadcrumbs --}}
        @php
            $cameFromEnrollments = request()->query('from') === 'enrollments';
            $prerequisites = $prerequisites ?? collect();
        @endphp
        @include('components.breadcrumbs', ['breadcrumbClass' => 'student-breadcrumbs--detail', 'items' => [
            [
                'label' => $cameFromEnrollments ? 'Enrolled Courses' : 'Browse Courses',
                'url' => $cameFromEnrollments ? route('courses.enrolled') : route('courses.browse'),
            ],
            ['label' => $course->title ?? 'Course'],
        ]])

        @if($removalNotice)
            <div class="course-removal-notice" role="status">
                <strong>You were removed from this course.</strong>
                <p>Reason: {{ Illuminate\Support\Str::after($removalNotice->message, 'Reason: ') }}</p>
            </div>
        @endif

        <div class="course-shell">

            {{-- ── Hero: title, summary, skills, meta ── --}}
            <section class="hero">
                <div class="hero-tags">
                    @if($course->category)
                        <span class="tag tag-cat ui-badge">{{ $course->category }}</span>
                    @endif
                    @if($course->level)
                        <span class="tag tag-level ui-badge">{{ $course->level }}</span>
                    @endif
                    @if($course->is_featured ?? false)
                        <span class="tag tag-feat ui-badge">Featured</span>
                    @endif
                </div>

                <h1 class="hero-title ui-page-title">{{ $course->title }}</h1>
                @php
                    $courseSummary = trim((string) ($course->short_description ?? ''));
                    if ($courseSummary === '') {
                        $courseSummary = \Illuminate\Support\Str::limit(
                            trim(html_entity_decode(strip_tags((string) $course->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                            180
                        );
                    }
                @endphp
                @if(($courseSummary ?? '') !== '')
                    <p class="hero-desc ui-page-subtitle">{{ $courseSummary }}</p>
                @endif

                @if(!empty($course->skills))
                    <div class="skills-label">Skills you'll gain</div>
                    <div class="skill-tags">
                        @foreach($course->skills as $skill)
                            <span class="skill-tag">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="hero-meta">
                    @if($course->instructor)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">person</span>
                            <span class="hero-meta__label">{{ $course->instructor }}</span>
                        </span>
                    @endif
                    @if($course->learning_hours || $course->duration)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">schedule</span>
                            <span class="hero-meta__label">{{ $course->learning_hours ? $course->learning_hours.' '.Str::plural('hour', $course->learning_hours) : $course->duration }}</span>
                        </span>
                    @endif
                    @if($modules->isNotEmpty())
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">menu_book</span>
                            <span class="hero-meta__label">{{ $modules->count() }} {{ Str::plural('module', $modules->count()) }}</span>
                        </span>
                    @endif
                    @if($course->enrolled_count ?? false)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">groups</span>
                            <span class="hero-meta__label">{{ $course->enrolled_count }} enrolled</span>
                        </span>
                    @endif
                </div>
            </section>

            {{-- ── Enroll card (sticky on the right) ── --}}
            <aside class="enroll-card ui-card-surface" aria-label="Enrollment and course details">
                <div class="enroll-thumb"
                     @if($course->thumbnail_url)
                         style="background-image:url('{{ $course->thumbnail_url }}')"
                     @endif>
                </div>
                <div class="enroll-body">
                    <div class="progress-label">
                        <span>Your progress</span>
                        <span>{{ $progress_percent ?? 0 }}%</span>
                    </div>
                    <div class="progress-track ui-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress_percent ?? 0 }}">
                        <div class="progress-fill ui-progress-fill" style="width:{{ $progress_percent ?? 0 }}%"></div>
                    </div>

                    @if($prerequisites->isNotEmpty())
                        <div class="prereq-box course-prerequisites">
                            <strong>Prerequisites</strong>
                            <ul>
                                @foreach($prerequisites as $prerequisite)
                                    <li>
                                        <span>{{ $prerequisite->title }}</span>
                                        <span class="prereq-state {{ $prerequisite->completed ? 'is-done' : 'is-todo' }}">{{ $prerequisite->completed ? 'Completed' : 'Not completed' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($errors->has('prerequisites'))
                        <p role="alert" class="form-error">{{ $errors->first('prerequisites') }}</p>
                    @endif

                    @if($is_completed ?? false)
                        <div class="completion-state" role="status">Course completed ✓</div>
                    @elseif($is_enrolled ?? false)
                        <div class="enroll-actions">
                            <a href="{{ route('courses.learn', $course->id) }}" class="btn-continue ui-button ui-button--primary">
                                {{ ($progress_percent ?? 0) > 0 ? 'Continue learning' : 'Start course' }}
                            </a>
                        </div>
                    @elseif(!($prerequisites_met ?? true))
                        <div class="enroll-actions">
                            <button type="button" class="btn-enroll ui-button ui-button--primary" disabled aria-disabled="true">
                                Complete prerequisites first
                            </button>
                            <small class="completion-help">
                                Complete: {{ ($missing_prerequisite_titles ?? collect())->implode(', ') }}
                            </small>
                        </div>
                    @else
                        <div class="enroll-actions">
                            <form action="{{ route('courses.enroll', $course->id) }}" method="POST" class="enroll-form">
                                @csrf
                                <button type="submit" class="btn-enroll ui-button ui-button--primary">Enroll now</button>
                            </form>
                        </div>
                    @endif

                    <div class="enroll-perks">
                        <div class="perk">
                            <span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span>
                            Earn a digital certificate
                        </div>
                    </div>

                    @if($course->pqf_level || $course->delivery_mode || $course->target_learners || $course->badge_name)
                        <section class="course-card-details" aria-label="Course details">
                            <dl class="detail-grid">
                                @if($course->pqf_level)<div><dt><span class="material-symbols-rounded" aria-hidden="true">school</span>PQF level</dt><dd>Level {{ $course->pqf_level }}</dd></div>@endif
                                @if($course->delivery_mode)<div><dt><span class="material-symbols-rounded" aria-hidden="true">devices</span>Delivery</dt><dd>{{ Str::headline($course->delivery_mode) }}</dd></div>@endif
                                @if($course->target_learners)<div><dt><span class="material-symbols-rounded" aria-hidden="true">groups</span>Intended learners</dt><dd>{{ $course->target_learners }}</dd></div>@endif
                                @if($course->badge_name)<div><dt><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span>Completion badge</dt><dd>{{ $course->badge_name }}@if($course->badge_description) &middot; {{ $course->badge_description }}@endif</dd></div>@endif
                            </dl>
                        </section>
                    @endif
                </div>
            </aside>

            {{-- ── Main content column ── --}}
            <div class="course-content">
                @php $courseLessonCount = $modules->sum(fn ($module) => $module->lessons->count()); @endphp
                <div class="course-details-tabs">
                    <div class="course-tabs" role="tablist" aria-label="Course sections">
                        <button class="course-tab" type="button" role="tab" id="student-tab-overview" aria-controls="student-panel-overview" aria-selected="true">Overview</button>
                        <button class="course-tab" type="button" role="tab" id="student-tab-lessons" aria-controls="student-panel-lessons" aria-selected="false" tabindex="-1">Lessons @if($courseLessonCount)<span class="course-tab-count">{{ $courseLessonCount }}</span>@endif</button>
                    </div>

                    <div class="course-tab-panel" id="student-panel-overview" role="tabpanel" aria-labelledby="student-tab-overview">

                {{-- About --}}
                @if(trim(strip_tags((string) $course->description)) !== '')
                    <section class="info-panel course-public-section course-overview" aria-labelledby="course-overview-title">
                        <div class="info-panel-head ui-card-header">
                            <h2 class="ui-card-header__title course-overview__title" id="course-overview-title">About this course</h2>
                        </div>
                        <div class="info-panel-body">
                            <div class="rich-content">{!! $course->description !!}</div>
                        </div>
                    </section>
                @endif

                {{-- Learning outcomes --}}
                <section class="info-panel ui-card-surface course-public-section">
                    <div class="info-panel-head ui-card-header">
                        <h2 class="ui-card-header__title">Learning outcomes</h2>
                    </div>
                    <div class="info-panel-body">
                        @if(!empty($course->learning_outcomes))
                            @if(count($course->learning_outcomes) === 1)
                                <div class="rich-content">{!! \App\Support\RichTextSanitizer::sanitize($course->learning_outcomes[0]) !!}</div>
                            @else
                                <ul class="objectives-list">
                                    @foreach($course->learning_outcomes as $objective)
                                        <li>
                                            <span class="material-symbols-rounded" aria-hidden="true">check</span>
                                            <span>{{ $objective }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @else
                            <p class="empty-note">No measurable outcomes listed yet.</p>
                        @endif
                    </div>
                </section>

                {{-- Remaining micro-credential details --}}
                @if($course->credit_bearing || $course->assessment_strategy || $course->grading_rubric)
                    <section class="info-panel ui-card-surface course-public-section">
                        <div class="info-panel-head ui-card-header">
                            <h2 class="ui-card-header__title">Micro-credential details</h2>
                        </div>
                        <div class="info-panel-body">
                            <dl class="detail-grid">
                                @if($course->credit_bearing)<div><dt><span class="material-symbols-rounded" aria-hidden="true">account_balance</span>Credit information</dt><dd>{{ $course->credit_equivalency ?: 'Proposed for institutional review' }}@if($course->equivalent_course) &middot; {{ $course->equivalent_course }}@endif</dd></div>@endif
                                @if($course->assessment_strategy)<div class="is-wide"><dt><span class="material-symbols-rounded" aria-hidden="true">fact_check</span>Assessment strategy</dt><dd>{{ $course->assessment_strategy }}</dd></div>@endif
                                @if($course->grading_rubric)<div class="is-wide"><dt><span class="material-symbols-rounded" aria-hidden="true">rule</span>Mastery criteria</dt><dd>{{ $course->grading_rubric }}</dd></div>@endif
                            </dl>
                        </div>
                    </section>
                @endif

                {{-- Instructor --}}
                <section class="info-panel ui-card-surface course-public-section">
                    <div class="info-panel-head ui-card-header">
                        <h2 class="ui-card-header__title">About the instructor</h2>
                    </div>
                    <div class="info-panel-body">
                        @if($instructor_detail ?? false)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar"
                                     @if($instructor_detail->avatar_url)
                                         style="background-image:url('{{ $instructor_detail->avatar_url }}')"
                                     @endif>
                                    @unless($instructor_detail->avatar_url){{ Str::upper(Str::substr($instructor_detail->name, 0, 1)) }}@endunless
                                </div>
                                <div>
                                    <p class="instructor-name">{{ $instructor_detail->name }}</p>
                                    @if($instructor_detail->department ?? false)
                                        <p class="instructor-dept">{{ $instructor_detail->department }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($instructor_detail->bio ?? false)
                                <p class="instructor-bio">{{ $instructor_detail->bio }}</p>
                            @endif
                        @elseif($course->instructor)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar">{{ Str::upper(Str::substr($course->instructor, 0, 1)) }}</div>
                                <div>
                                    <p class="instructor-name">{{ $course->instructor }}</p>
                                    <p class="instructor-dept">Instructor</p>
                                </div>
                            </div>
                        @else
                            <p class="empty-note">No instructor information available.</p>
                        @endif
                    </div>
                </section>
                    </div>

                    <div class="course-tab-panel" id="student-panel-lessons" role="tabpanel" aria-labelledby="student-tab-lessons">

                {{-- Course content --}}
                @if(($modules ?? collect())->isNotEmpty() || ($finalExam ?? null))
                    <section class="info-panel ui-card-surface course-public-section">
                        <div class="info-panel-head ui-card-header">
                            <h2 class="ui-card-header__title">Course outline</h2>
                        </div>
                        <p class="course-outline-meta">{{ $modules->count() }} {{ Str::plural('module', $modules->count()) }} &middot; {{ $courseLessonCount }} {{ Str::plural('lesson', $courseLessonCount) }}</p>

                        @foreach($modules as $module)
                            <div class="module">
                                <div class="module-item">
                                    <div class="module-num" aria-hidden="true">{{ $loop->iteration }}</div>
                                    <div class="module-info">
                                        <p class="module-title"><span class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Module {{ $loop->iteration }}: </span>{{ $module->title }}</p>
                                        @if($module->description ?? false)
                                            <p class="module-sub">{{ $module->description }}</p>
                                        @endif
                                    </div>
                                    <span class="module-count">{{ $module->lessons->count() }} {{ Str::plural('lesson', $module->lessons->count()) }}</span>
                                </div>
                                @if($module->lessons->isNotEmpty())
                                    <div class="course-module-lessons">
                                        @foreach($module->lessons as $lesson)
                                            <div class="course-module-lesson">
                                                <span>{{ $lesson->title }}</span>
                                                <small>{{ $lesson->type ?: 'Lesson' }}@if($lesson->duration) &middot; {{ $lesson->duration }}@endif</small>
                                            </div>
                                            @foreach($lesson->quizzes as $lessonQuiz)
                                                <div class="course-module-lesson is-quiz">
                                                    <span>Lesson quiz: {{ $lessonQuiz->title }}</span>
                                                    <small>{{ $lessonQuiz->questions_count }} {{ Str::plural('question', $lessonQuiz->questions_count) }} &middot; Pass {{ $lessonQuiz->passing_score }}%</small>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @endif
                                @if($module->quiz)
                                    <div class="quiz-row">
                                        <div class="quiz-info">
                                            <p class="quiz-title">Module quiz: {{ $module->quiz->title }}</p>
                                            <p class="quiz-sub">{{ $module->quiz->questions_count }} {{ Str::plural('question', $module->quiz->questions_count) }} &middot; Pass {{ $module->quiz->passing_score }}%</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($finalExam)
                            <div class="quiz-row">
                                <div class="quiz-info">
                                    <p class="quiz-title">Final Exam</p>
                                    @if($finalExam->quiz)
                                        <p class="quiz-sub">{{ $finalExam->quiz->questions_count }} {{ Str::plural('question', $finalExam->quiz->questions_count) }} &middot; Pass {{ $finalExam->quiz->passing_score }}%</p>
                                    @else
                                        <p class="quiz-sub">Course-completion assessment</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </section>
                @else
                    <section class="course-public-section">
                        <h2>Course outline</h2>
                        <p class="empty-note">The course outline will be available soon.</p>
                    </section>
                @endif
                    </div>
                </div>{{-- /.course-details-tabs --}}
            </div>{{-- /.course-content --}}

        </div>{{-- /.course-shell --}}

    </div>

{{-- Back to top (appears on long pages) --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
</button>

<script>
    (function () {
        var root = document.querySelector('.course-details-tabs');
        if (!root) return;
        root.classList.add('has-js');

        var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
        var panels = tabs.map(function (tab) {
            return document.getElementById(tab.getAttribute('aria-controls'));
        });

        function show(index, focusTab) {
            tabs.forEach(function (tab, tabIndex) {
                var isSelected = tabIndex === index;
                tab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                tab.tabIndex = isSelected ? 0 : -1;
                if (panels[tabIndex]) panels[tabIndex].hidden = !isSelected;
            });
            if (focusTab) tabs[index].focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                show(index, false);
                if (history.replaceState) {
                    history.replaceState(null, '', index === 1 ? '#lessons' : '#overview');
                }
            });
            tab.addEventListener('keydown', function (event) {
                var nextIndex = null;
                if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
                else if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
                else if (event.key === 'Home') nextIndex = 0;
                else if (event.key === 'End') nextIndex = tabs.length - 1;
                if (nextIndex !== null) {
                    event.preventDefault();
                    show(nextIndex, true);
                }
            });
        });

        show(window.location.hash === '#lessons' ? 1 : 0, false);
    })();

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
