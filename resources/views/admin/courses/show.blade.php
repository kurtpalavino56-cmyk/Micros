{{--
    resources/views/admin/Course_Detail.blade.php

    Admin › Courses & Badges › Course Detail
    URL: /Admin-courses/{id}   (admin.courses.show)

    Data from AdminController::showCourse(): $course, $modules, $enrollments
    (same fields as before; optional: $course->is_featured, ->thumbnail_url)
--}}
<x-admin.layout title="{{ $course->title }} – Courses & Badges" page="admin-courses-show">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
@php
    // CKEditor leaves <p>&nbsp;</p> for every blank line the author types.
    // On a review page those show up as large gaps, so drop them.
    $tidy = function ($html) {
        $html = (string) $html;
        $html = preg_replace('/<p>(?:\s|&nbsp;|\x{00A0}|<br\s*\/?>)*<\/p>/iu', '', $html);
        $html = preg_replace('/(?:<br\s*\/?>\s*){2,}/i', '<br>', $html);

        return trim($html);
    };

    // Stored thumbnails are relative paths (uploads/thumbnails/...).
    $thumb = $course->thumbnail_url ?? null;
    if ($thumb && ! preg_match('#^(https?:)?//|^/|^data:#i', $thumb)) {
        $thumb = asset($thumb);
    }
@endphp
{{-- Icon sprite --}}
<svg width="0" height="0" aria-hidden="true" class="adm-inline-admin-courses-show-1">
    <symbol id="i-user" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
    <symbol id="i-bars" viewBox="0 0 24 24"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></symbol>
    <symbol id="i-cap" viewBox="0 0 24 24"><path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-layers" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></symbol>
    <symbol id="i-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></symbol>
    <symbol id="i-award" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.48 12.89L17 22l-5-3-5 3 1.52-9.11"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></symbol>
    <symbol id="i-msg" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
    <symbol id="i-down" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></symbol>
    <symbol id="i-ext" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></symbol>
    <symbol id="i-left" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></symbol>
</svg>

<div class="layout admin-layout">

    {{-- SHARED ADMIN SIDEBAR --}}
    {{-- MAIN CONTENT --}}
    <main class="main">
    <div class="page-wrap">

        @include('components.breadcrumbs', ['items' => [
            ['label' => 'Courses & Badges', 'url' => route('admin.courses')],
            ['label' => $course->title],
        ]])

        <x-flash-toast :message="session('success')" />
        @error('approval')
            <div class="alert-error" role="alert">{{ $message }}</div>
        @enderror

        @if (! empty($course->change_note))
            <div class="banner banner-note"><strong>What the author changed</strong>{{ $course->change_note }}</div>
        @endif

        @if (! empty($course->denial_feedback))
            <div class="banner banner-denied"><strong>Previous denial feedback</strong>{{ $course->denial_feedback }}</div>
        @endif

        {{-- ── Hero ── --}}
        @php
            $shortDesc = \Illuminate\Support\Str::limit(trim((string) ($course->short_description ?? '')), 170);
        @endphp
        <section class="card hero">
            <div class="thumb">
                @if (! empty($thumb))
                    <img src="{{ $thumb }}" alt="">
                @else
                    <svg class="ico"><use href="#i-cap"/></svg>
                @endif
                @if (! empty($course->is_featured))
                    <span class="featured-tag">Featured</span>
                @endif
            </div>

            <div class="hero-info">
                <div class="detail-title">{{ $course->title }}</div>
                <div class="by"><svg class="ico"><use href="#i-user"/></svg>By {{ $course->instructor ?? 'Faculty' }}</div>
                <div class="meta-row">
                    @if ($course->category)<span><svg class="ico"><use href="#i-tag"/></svg>{{ $course->category }}</span>@endif
                    @if ($course->created_at)<span><svg class="ico"><use href="#i-cal"/></svg>Created {{ $course->created_at->format('M j, Y') }}</span>@endif
                </div>
                @if ($shortDesc !== '')
                    <p class="hero-desc">{{ $shortDesc }}</p>
                @endif
            </div>

            <div class="hero-actions">
                <span class="status-pill status-{{ $course->status_key }}"><span class="dot"></span>{{ $course->status }}</span>

                @if ($course->status_key === 'pending')
                    @if ($course->can_approve)
                        <form method="POST" action="{{ route('admin.courses.approve', $course->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-approve"><svg class="ico"><use href="#i-check"/></svg>Approve</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-approve approval-disabled" disabled title="The course is missing required content">Incomplete course</button>
                    @endif
                    <button type="button" class="btn btn-outline" id="deny-open"><svg class="ico"><use href="#i-msg"/></svg>Deny with feedback</button>
                @endif

                <form method="POST" action="{{ route('admin.courses.destroy', $course->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-delete"
                            onclick="return confirm('Permanently delete \'{{ addslashes($course->title) }}\'? Its modules, lessons, quizzes and enrollments will also be removed. This cannot be undone.');">
                        <svg class="ico"><use href="#i-trash"/></svg>Delete course
                    </button>
                </form>

                @if ($course->can_toggle_publish && $course->is_published)
                    <form method="POST" action="{{ route('admin.courses.publish', $course->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline">Unpublish course</button>
                    </form>
                @endif
            </div>

            @if ($course->status_key === 'pending')
                <div id="deny-panel" class="{{ $errors->has('denial_feedback') ? 'open' : '' }}">
                    <form method="POST" action="{{ route('admin.courses.deny', $course->id) }}">
                        @csrf
                        <label for="denial_feedback">Feedback for the author (required)</label>
                        <textarea id="denial_feedback" name="denial_feedback" required minlength="5"
                                  placeholder="Explain what needs to change before this course can be approved.">{{ old('denial_feedback') }}</textarea>
                        @error('denial_feedback')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                        <div class="deny-actions">
                            <button type="submit" class="btn btn-approve">Send denial and feedback</button>
                            <button type="button" class="btn" id="deny-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

        @if ($course->status_key === 'pending')
            <section class="card approval-readiness" aria-labelledby="approval-readiness-title">
                <h2 id="approval-readiness-title">Approval readiness</h2>
                <p>{{ $course->can_approve ? 'Required course content is in place.' : 'Approval is unavailable until the missing requirements are completed.' }}</p>
                <div class="approval-checks">
                    @foreach ($course->readiness_checklist as $check)
                        <div class="approval-check {{ $check['complete'] ? 'complete' : '' }}">
                            <span class="approval-check-mark" aria-hidden="true">{{ $check['complete'] ? '✓' : '!' }}</span>
                            <span>{{ $check['label'] }}@unless($check['complete'])<br><small>{{ $check['detail'] }}</small>@endunless</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="cols">

            {{-- ── LEFT ── --}}
            <div class="stack">

                <section class="card stats">
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-bars"/></svg></span><div><small>Level</small><b>{{ $course->level ?? '—' }}</b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-clock"/></svg></span><div><small>Duration</small><b>{{ $course->duration ?? '—' }}</b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-users"/></svg></span><div><small>Students</small><b>{{ $course->students }}</b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-layers"/></svg></span><div><small>Modules</small><b>{{ $course->modules_count }}</b></div></div>
                    <div class="stat"><span class="stat-ico"><svg class="ico"><use href="#i-play"/></svg></span><div><small>Lessons</small><b>{{ $course->lessons_count }}</b></div></div>
                </section>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-file"/></svg>Description</div>
                    @php
                        $descHtml = $tidy($course->description);
                        $descBlank = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($descHtml, '<img><oembed>')))) === '';
                    @endphp
                    <div class="rich rich-content">{!! $descBlank ? 'No description provided.' : $descHtml !!}</div>
                </section>

                <section class="card syllabus-details">
                    <div class="card-title"><svg class="ico"><use href="#i-file"/></svg>Micro-credential syllabus details</div>
                    <div class="chip-row"><span class="chip">PQF {{ $course->pqf_level ? 'Level '.$course->pqf_level : 'not specified' }}</span><span class="chip">{{ $course->learning_hours ? $course->learning_hours.' learning hours' : 'Learning hours not specified' }}</span><span class="chip">{{ $course->delivery_mode ? Illuminate\Support\Str::headline($course->delivery_mode) : 'Delivery mode not specified' }}</span></div>
                    <p><strong>Intended learners:</strong> {{ $course->target_learners ?: 'Not specified' }}</p>
                    <p><strong>Assessment strategy:</strong> {{ $course->assessment_strategy ?: 'Not specified' }}</p>
                    <p><strong>Mastery criteria:</strong> {{ $course->grading_rubric ?: 'Not specified' }}</p>
                    @if(!empty($course->learning_outcomes))
                        <strong class="outcome-heading">Measurable learning outcomes</strong>
                        @if(count($course->learning_outcomes) === 1)
                            <div class="rich rich-content">{!! \App\Support\RichTextSanitizer::sanitize($course->learning_outcomes[0]) !!}</div>
                        @else
                            <ul class="obj-list">
                                @foreach($course->learning_outcomes as $outcome)
                                    <li><div class="rich rich-content">{!! \App\Support\RichTextSanitizer::sanitize($outcome) !!}</div></li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </section>

                <div class="stack">
                    <section class="card">
                        <div class="card-title"><svg class="ico"><use href="#i-tag"/></svg>Skills covered</div>
                        @if (! empty($course->skills))
                            <div class="chip-row">
                                @foreach ($course->skills as $skill)
                                    <span class="chip">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-note">No skills listed.</div>
                        @endif

                        {{-- "Subject is Related on": internal only --}}
                        @if (! empty($course->related_skills))
                            <div class="related-head">Related on</div>
                            <div class="related-hint">Set by faculty. Used to recommend this course to learners, and not shown on any student page.</div>
                            <div class="chip-row">
                                @foreach ($course->related_skills as $skill)
                                    <span class="chip rel">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-file"/></svg>Course content</div>
                    @forelse ($modules as $module)
                        @php
                            $lCount = count($module->lessons);
                        @endphp
                        <div class="module-block open">
                            <button type="button" class="module-head" onclick="this.parentElement.classList.toggle('open')">
                                <span class="module-index">{{ $loop->iteration }}</span>
                                <span class="module-title">{{ $module->title }}</span>
                                <span class="module-count">{{ $lCount }} {{ $lCount === 1 ? 'lesson' : 'lessons' }} · {{ count($module->activities) }} activities · {{ count($module->learning_quizzes) + (int) (bool) $module->quiz }} quizzes</span>
                                <svg class="ico"><use href="#i-down"/></svg>
                            </button>

                            <div class="module-body">
                                @php
                                    $moduleDescription = $tidy($module->description);
                                    $moduleDescriptionIsBlank = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($moduleDescription, '<img><oembed>')))) === '';
                                @endphp
                                @if (!$moduleDescriptionIsBlank)
                                    <section class="module-overview" aria-label="Module details">
                                        <div class="module-overview-title">Module details</div>
                                        <div class="module-overview-content rich rich-content">{!! $moduleDescription !!}</div>
                                    </section>
                                @elseif (!$module->is_final)
                                    <section class="module-overview" aria-label="Module details">
                                        <div class="module-overview-title">Module details</div>
                                        <div class="module-overview-empty">No module description provided.</div>
                                    </section>
                                @endif

                                @foreach ($module->lessons as $lesson)
                                    <div class="lesson-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                        <span>{{ $lesson->title }}</span>
                                        <span class="lesson-meta">{{ $lesson->type }}{{ $lesson->duration ? ' · ' . $lesson->duration : '' }}</span>
                                    </div>
                                    @php
                                        $blank = fn ($h) => trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags((string) $h, '<img><iframe><oembed><video>')))) === '';
                                        $lBody  = $tidy($lesson->content ?? $lesson->body ?? '');
                                        $lDesc  = $tidy($lesson->description ?? '');
                                        $lVideo = $lesson->video_url ?? null;
                                        $lFile  = $lesson->file_url ?? null;
                                        if ($lFile && ! preg_match('#^(https?:)?//|^/#i', $lFile)) {
                                            $lFile = asset($lFile);
                                        }
                                        $lExt = $lFile ? strtolower(pathinfo(parse_url($lFile, PHP_URL_PATH) ?: $lFile, PATHINFO_EXTENSION)) : null;
                                        $lIsPdf = $lExt === 'pdf';
                                        $lIsImageFile = in_array($lExt, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true);
                                        $hasBody = ! $blank($lBody);
                                        $hasDesc = ! $blank($lDesc);
                                    @endphp
                                    <div class="lesson-detail open">
                                        @if ($hasBody)
                                            <div class="rich rich-content">{!! $lBody !!}</div>
                                        @elseif ($hasDesc)
                                            <div class="rich rich-content">{!! $lDesc !!}</div>
                                        @endif
                                        @if ($lFile && (($lesson->type ?? '') === 'Image' || $lIsImageFile))
                                            <div class="rich"><img src="{{ $lFile }}" alt="{{ $lesson->title }}"></div>
                                        @elseif ($lFile && $lIsPdf)
                                            <div class="file-embed"><embed src="{{ $lFile }}" type="application/pdf"></div>
                                        @endif
                                        @if ($lVideo)
                                            <p class="lesson-desc"><a class="lesson-file" href="{{ $lVideo }}" target="_blank" rel="noopener">Open video</a></p>
                                        @endif
                                        @if ($lFile)
                                            <p class="lesson-desc"><a class="lesson-file" href="{{ $lFile }}" target="_blank" rel="noopener">Open attached file ({{ $lesson->file_name ?? 'file' }})</a></p>
                                        @endif
                                        @foreach($lesson->lesson_quizzes as $lessonQuiz)
                                            <section class="lesson-assessment">
                                                <div class="lesson-assessment-title">
                                                    <span>{{ $lessonQuiz->title }}</span>
                                                    <span class="lesson-assessment-meta">
                                                        Lesson quiz · {{ $lessonQuiz->questions_count }} {{ $lessonQuiz->questions_count === 1 ? 'question' : 'questions' }} · Pass {{ $lessonQuiz->passing_score }}%
                                                        @if ($lessonQuiz->time_limit)
                                                            · {{ $lessonQuiz->time_limit }} min
                                                        @endif
                                                    </span>
                                                </div>
                                                @if ($lessonQuiz->instructions)
                                                    <div class="assessment-instructions up-ckeditor-wrapper"><div class="rich ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($lessonQuiz->instructions) !!}</div></div>
                                                @endif
                                                @forelse ($lessonQuiz->questions as $qi => $question)
                                                    <div class="assessment-question">
                                                        <div class="assessment-question-title">Q{{ $qi + 1 }}. {{ $question->question }}</div>
                                                        <div class="lesson-assessment-meta">{{ $question->type }}{{ $question->points ? ' · ' . $question->points . ' pts' : '' }}</div>
                                                        @if (! empty($question->options))
                                                            <ul class="assessment-options">
                                                                @foreach ($question->options as $oi => $option)
                                                                    <li class="{{ $option === $question->correct_answer ? 'is-correct' : '' }}">
                                                                        {{ chr(65 + $oi) }}. {{ $option }}
                                                                        @if ($option === $question->correct_answer)
                                                                            · Correct answer
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @elseif ($question->correct_answer)
                                                            <div class="assessment-answer">Answer: {{ $question->correct_answer }}</div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="lesson-desc lesson-empty">No questions have been added to this quiz.</p>
                                                @endforelse
                                            </section>
                                        @endforeach
                                        @foreach($lesson->lesson_activities as $lessonActivity)
                                            <section class="lesson-activity">
                                                <div class="lesson-activity-head">
                                                    <span>{{ $lessonActivity->title }} <span class="lesson-assessment-meta">({{ Illuminate\Support\Str::headline($lessonActivity->activity_type) }})</span></span>
                                                    <span class="lesson-activity-meta">
                                                        {{ $lessonActivity->is_required ? 'Required' : 'Optional' }}
                                                        @if ($lessonActivity->activity_type === 'assignment')
                                                            · {{ $lessonActivity->max_points ?? '—' }} points · Pass {{ $lessonActivity->passing_percent ?? 70 }}%
                                                        @endif
                                                    </span>
                                                </div>
                                                @if (filled($lessonActivity->instructions))
                                                    <div class="activity-instructions up-ckeditor-wrapper"><div class="rich ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($lessonActivity->instructions) !!}</div></div>
                                                @else
                                                    <p class="lesson-desc lesson-empty">No instructions have been added to this activity.</p>
                                                @endif
                                                @if (! empty($lessonActivity->rubric))
                                                    <div class="lesson-assessment-meta">Rubric</div>
                                                    <pre class="activity-rubric">{{ json_encode($lessonActivity->rubric, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @endif
                                            </section>
                                        @endforeach
                                        @if (! $hasBody && ! $hasDesc && ! $lVideo && ! $lFile)
                                            <p class="lesson-desc lesson-empty">No content has been added to this lesson yet.</p>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach($module->activities as $moduleActivity)
                                    <section class="lesson-activity">
                                        <div class="lesson-activity-head"><span>{{ $moduleActivity->title }} <span class="lesson-assessment-meta">({{ Illuminate\Support\Str::headline($moduleActivity->activity_type) }} · Module activity)</span></span><span class="lesson-activity-meta">{{ $moduleActivity->is_required ? 'Required' : 'Optional' }}@if($moduleActivity->activity_type === 'assignment') · {{ $moduleActivity->max_points ?? '—' }} points · Pass {{ $moduleActivity->passing_percent ?? 70 }}%@endif</span></div>
                                        @if(filled($moduleActivity->instructions))<div class="activity-instructions up-ckeditor-wrapper"><div class="rich ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($moduleActivity->instructions) !!}</div></div>@else<p class="lesson-desc lesson-empty">No instructions have been added to this activity.</p>@endif
                                    </section>
                                @endforeach

                                @foreach($module->learning_quizzes as $learningQuiz)
                                    <section class="lesson-assessment"><div class="lesson-assessment-title"><span>{{ $learningQuiz->title }}</span><span class="lesson-assessment-meta">Module quiz · {{ $learningQuiz->questions_count }} questions · Pass {{ $learningQuiz->passing_score }}%</span></div>
                                        @foreach($learningQuiz->questions as $qi => $question)
                                            <div class="assessment-question"><div class="assessment-question-title">Q{{ $qi + 1 }}. {{ $question->question }}</div><div class="lesson-assessment-meta">{{ $question->type }}{{ $question->points ? ' · '.$question->points.' pts' : '' }}</div></div>
                                        @endforeach
                                    </section>
                                @endforeach

                                @if ($module->quiz)
                                    <div class="quiz-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                        <span>Quiz: {{ $module->quiz->title }}</span>
                                        <span>{{ $module->quiz->questions_count }} questions · Pass {{ $module->quiz->passing_score }}%{{ $module->quiz->time_limit ? ' · ' . $module->quiz->time_limit . ' min' : '' }}</span>
                                    </div>
                                    <div class="quiz-detail">
                                        @if ($module->quiz->instructions)
                                            <div class="quiz-instructions up-ckeditor-wrapper"><div class="rich ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($module->quiz->instructions) !!}</div></div>
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
                                                                {{ chr(65 + $oi) }}. {{ $opt }}@if ($opt === $q->correct_answer) (correct) @endif
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

                                @if ($lCount === 0 && ! $module->quiz)
                                    <div class="module-sub empty-note">No lessons in this module yet.</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-note">No modules have been added to this course yet.</div>
                    @endforelse
                </section>
            </div>

            {{-- ── RIGHT ── --}}
            <div class="stack">

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-award"/></svg>Completion award</div>

                    @if ($course->badge_icon)
                        <img class="award-badge" src="{{ $course->badge_icon }}" alt="{{ $course->badge }}">
                    @else
                        <div class="award-none">No badge designed yet</div>
                    @endif
                    <div class="award-name adm-inline-admin-courses-show-2">{{ $course->badge }}</div>

                    <div class="cert-head">
                        <svg class="ico"><use href="#i-award"/></svg>Certificate
                        @if ($course->certificate_enabled)
                            <span class="cert-tag">{{ $course->certificate_mode === 'upload' ? 'Uploaded' : 'Auto-generated' }}</span>
                        @endif
                    </div>

                    @if (! $course->certificate_enabled)
                        <div class="award-none">No certificate configured yet</div>
                    @elseif ($course->certificate_mode === 'upload' && $course->certificate_file)
                        @php $ext = strtolower(pathinfo($course->certificate_file, PATHINFO_EXTENSION)); @endphp
                        <div class="cert-box">
                            @if ($ext === 'pdf')
                                <embed src="{{ asset($course->certificate_file) }}" type="application/pdf">
                            @else
                                <img src="{{ asset($course->certificate_file) }}" alt="Uploaded certificate">
                            @endif
                        </div>
                        <a class="btn cert-btn" href="{{ asset($course->certificate_file) }}" target="_blank" rel="noopener">
                            View certificate <svg class="ico"><use href="#i-ext"/></svg>
                        </a>
                    @else
                        <div class="cert-box">
                            @include('components.certificate', ['cert' => $course->certificate])
                        </div>
                    @endif
                </section>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-bars"/></svg>Course progress</div>
                    <div class="progress-line">
                        <div class="progress-track"><div class="progress-fill" style="width: {{ $course->percent }}%"></div></div>
                        <span class="pct-num">{{ $course->percent }}%</span>
                    </div>
                    <div class="pct-sub">Average completion · {{ $course->students }} enrolled</div>
                </section>

                <section class="card">
                    <div class="card-title"><svg class="ico"><use href="#i-users"/></svg>Completion verification</div>
                    <p>Verify completed course requirements and record the academic-unit decision for this course.</p>
                    <a class="btn btn-blue enrollment-review-link" href="{{ route('admin.courses.enrollments', $course->id) }}">
                        Review completed requirements
                    </a>
                </section>
            </div>
        </div>

    </div>
    </main>
</div>

<script>
    // CKEditor saves embedded video as <oembed url="...">, and a PDF
    // dropped into the editor as <figure class="upskill-pdf"><oembed
    // url="..."></oembed></figure>. Neither renders on its own in a
    // plain browser, so swap each one for a real player or viewer.
    (function () {
        document.querySelectorAll('.rich oembed[url]').forEach(function (el) {
            var url = el.getAttribute('url') || '';
            var figure = el.closest('figure.upskill-pdf');
            var isPdf = !!figure || /\.pdf(\?|#|$)/i.test(url);

            if (isPdf) {
                var wrap = document.createElement('div');
                wrap.className = 'file-embed';
                var embed = document.createElement('embed');
                embed.src = url;
                embed.type = 'application/pdf';
                wrap.appendChild(embed);
                (figure || el).replaceWith(wrap);
                return;
            }

            var yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/);
            var vm = url.match(/vimeo\.com\/(\d+)/);
            var src = yt ? 'https://www.youtube.com/embed/' + yt[1]
                : (vm ? 'https://player.vimeo.com/video/' + vm[1] : null);
            var node;

            if (src) {
                node = document.createElement('iframe');
                node.src = src;
                node.loading = 'lazy';
                node.allowFullscreen = true;
                node.style.cssText = 'width:100%;aspect-ratio:16/9;border:0;border-radius:8px;';
            } else if (/^https?:\/\//i.test(url)) {
                node = document.createElement('a');
                node.href = url;
                node.target = '_blank';
                node.rel = 'noopener';
                node.className = 'lesson-file';
                node.textContent = 'Open embedded media';
            } else {
                return;
            }
            el.replaceWith(node);
        });

        // Non-PDF files dropped into CKEditor come out as
        // <a class="lesson-file-attachment" data-file-name="...">📎 name</a>.
        // Give them the same "Open attached file" look as the legacy
        // file_url attachments.
        document.querySelectorAll('.rich a.lesson-file-attachment').forEach(function (el) {
            el.classList.add('lesson-file');
            var name = el.getAttribute('data-file-name');
            if (name) el.textContent = 'Open attached file (' + name + ')';
        });
    })();
</script>

<script>
    (function () {
        var open = document.getElementById('deny-open');
        var panel = document.getElementById('deny-panel');
        var cancel = document.getElementById('deny-cancel');
        if (!panel) return;

        function show() { panel.classList.add('open'); if (open) open.style.display = 'none'; }
        function hide() { panel.classList.remove('open'); if (open) open.style.display = ''; }

        if (open) open.addEventListener('click', show);
        if (cancel) cancel.addEventListener('click', hide);
        if (location.hash === '#deny-panel' || panel.classList.contains('open')) show();
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
