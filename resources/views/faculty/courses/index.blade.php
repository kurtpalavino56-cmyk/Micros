{{--
    resources/views/faculty/My_Courses.blade.php

    Faculty â€º My Courses â€” ONE self-contained Blade view with TWO modes:

      â€¢ $mode = 'list'    â†’ the My Courses list (default)
                            URL: /Faculty-mycourses          (faculty.courses)
      â€¢ $mode = 'manage'  â†’ the "Managing Course" screen shown after
                            clicking a course's Manage button
                            URL: /Faculty-mycourses/manage   (faculty.courses.manage)

    âš  STANDALONE: the only connections are between these two modes and back
      to the Faculty Dashboard (route 'faculty.dashboard'). Every other
      button / link / input is intentionally non-functioning (href="#" or
      plain <button type="button">) until the real routes are wired up.

    LIST mode data:
        'user', 'courses' (each: ->title, ->description, ->status,
        ->students_count, ->modules_count, ->lessons_count, ->thumbnail_url)

    MANAGE mode data:
        'user',
        'course'   (->title, ->status, ->level, ->students_count, ->modules_count),
        'modules'  (each: ->title, ->subtitle,
                    ->lessons (->title, ->meta, ->thumbnail_url),
                    ->quiz (->title, ->questions_count, ->passing_score)),
        'students' (each: ->name, ->student_id, ->status, ->avatar_url),
        'quizAverages' (each: ->title, ->percent)
--}}
@php $mode = $mode ?? 'list'; @endphp
@php $facultyBodyClass = request()->boolean('inline') ? 'inline-quiz' : ''; @endphp
<x-faculty.layout title="{{ $mode === 'final-exam' ? (($quiz ?? null) ? 'Edit Final Exam' : 'Create Final Exam') : ($mode === 'quiz' ? 'Add Quiz' : ($mode === 'manage' ? 'Manage Course' : 'My Courses')) }} | Upskill" data-faculty-page="faculty-courses-index" class="{{ $facultyBodyClass }}" use-app-css use-app-js>
    <x-slot:head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">






    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>



    {{-- Sidebar â€” Dashboard & My Courses connected, everything else placeholder --}}


    {{-- Main content --}}
    <main class="main {{ $mode === 'manage' ? 'manage-course-page' : '' }}">

    @if ($mode === 'manage')
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'My Courses', 'url' => route('faculty.courses')],
            ['label' => $course->title ?? 'Course Management'],
        ]])

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• MANAGE MODE â€” "Managing Course" screen â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        <section class="course-banner">
            <div class="banner-info faculty-page-heading faculty-page-heading--inverse">
                <div class="kicker">Managing Course</div>
                <h2 class="faculty-page-title">{{ $course->title ?? 'Course Title' }}</h2>
                <div class="banner-meta faculty-page-subtitle">
                    <span>{{ $course->students_count ?? 0 }} Students</span>
                    <span>{{ $course->modules_count ?? 0 }} {{ ($course->modules_count ?? 0) == 1 ? 'Module' : 'Modules' }}</span>
                    <span>{{ $course->level ?? '' }}</span>
                </div>
            </div>
            <div class="banner-actions">
                <div class="row">
                    <span class="course-status-badge status-{{ Illuminate\Support\Str::slug($course->status ?? 'Draft') }}" aria-label="Course status: {{ $course->status ?? 'Draft' }}"><span class="course-status-indicator" aria-hidden="true"></span>{{ $course->status ?? 'Draft' }}</span>
                    <a href="{{ route('faculty.courses.edit', $course->id) }}" class="btn-pill">Edit Course Details</a>
                </div>
            </div>
        </section>

        <x-flash-toast :message="session('success')" />
        @if($errors->has('submission'))
            <div class="manage-flash error" role="alert">{{ $errors->first('submission') }}</div>
        @endif
        @if(filled($course->denial_feedback ?? null))
            <section class="manage-flash review-feedback" role="alert" aria-labelledby="admin-review-feedback-title">
                <strong id="admin-review-feedback-title">Feedback from admin</strong>
                <p>{{ $course->denial_feedback }}</p>
            </section>
        @endif

        <section class="readiness-panel" aria-labelledby="readiness-title">
            <div class="readiness-heading">
                <h3 id="readiness-title">Course readiness</h3>
                <p>Complete these checks before submitting. Draft courses remain private to you.</p>
            </div>
            <div class="readiness-action">
                @if($course->approval_status === 'draft')
                    <form method="POST" action="{{ route('faculty.courses.submit-for-approval', $course->id) }}">
                        @csrf
                        <button class="btn-primary-action" type="submit" @disabled(!$canSubmitForApproval)>Submit for approval</button>
                    </form>
                @elseif($course->approval_status === 'pending')
                    <span class="readiness-state pending">Awaiting admin review</span>
                @elseif($course->approval_status === 'denied')
                    <span class="readiness-state denied">Changes requested</span>
                @else
                    <span class="readiness-state published">Approved</span>
                @endif
            </div>
            <ul class="readiness-list">
                @foreach($readinessChecklist as $check)
                    <li class="readiness-item {{ $check['complete'] ? 'complete' : '' }}">
                        <span class="readiness-mark" aria-hidden="true">{{ $check['complete'] ? '✓' : '!' }}</span>
                        <span><strong>{{ $check['label'] }}</strong>@unless($check['complete'])<small>{{ $check['detail'] }}</small>@endunless</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <div class="content-grid">

            {{-- â”€â”€ Left column: Course Modules â”€â”€ --}}
            <section class="modules-section">
                <div class="section-head modules-section-head">
                    <div>
                        <h3 class="section-title">Course Modules</h3>
                        <p class="section-caption">Organize the lessons, activities, and assessments in this course.</p>
                    </div>
                    <button class="btn-primary-action" type="button" id="toggle-module-create" aria-expanded="{{ $errors->has('module_title') || $errors->has('module_description') ? 'true' : 'false' }}" aria-controls="module-create-form" onclick="toggleModuleCreate()">+ Add Module</button>
                </div>

                {{-- Keep module creation in this page, but reveal it only when requested. --}}
                <form class="add-module-card" id="module-create-form" style="display:{{ $errors->has('module_title') || $errors->has('module_description') ? 'flex' : 'none' }};" method="POST" action="{{ route('faculty.module.store', $course->id) }}">
                    @csrf
                    <label class="form-field-label" for="new-module-title">Module title</label>
                    <input class="input-outline" id="new-module-title" type="text" name="module_title" placeholder="e.g. Web Page Foundations" value="{{ old('module_title') }}" required>
                    <label class="form-field-label" for="module-description-new">Description</label>
                    @include('components.rich-text-editor', ['name' => 'module_description', 'id' => 'module-description-new', 'value' => old('module_description'), 'placeholder' => 'Describe what this module covers...'])
                    @if($errors->has('module_title') || $errors->has('module_description'))
                        <div class="form-error" role="alert">{{ $errors->first('module_title') ?: $errors->first('module_description') }}</div>
                    @endif
                    <div class="module-create-actions">
                        <button class="btn-secondary-action" type="button" onclick="toggleModuleCreate(false)">Cancel</button>
                        <button class="btn-primary-action" type="submit">Create Module</button>
                    </div>
                </form>

                <div class="sortable-list module-sort-list" data-order-kind="modules" data-order-url="{{ route('faculty.modules.reorder', $course->id) }}">
                @forelse ($modules as $module)
                    <div class="module-card sortable-item" data-order-item data-order-id="{{ $module->idx }}">
                        @php
                            $moduleLessons = $module->lessons ?? collect();
                            $moduleLessonCount = $moduleLessons->count();
                            $moduleActivityCount = $moduleLessons->sum(fn ($lesson) => collect($lesson->activities ?? [])->count());
                            $moduleDurationMinutes = $moduleLessons->sum(fn ($lesson) => (int) ($lesson->duration_raw ?? 0));
                            $moduleDurationLabel = $moduleDurationMinutes >= 60
                                ? intdiv($moduleDurationMinutes, 60).'h '.($moduleDurationMinutes % 60).'m'
                                : $moduleDurationMinutes.' min';
                        @endphp
                        <header class="module-head">
                            <div class="module-heading-copy">
                                <span class="eyebrow-label">Module {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h4>{{ $module->title }}</h4>
                                @if($module->subtitle ?? null)
                                    <div class="module-description">{{ $module->subtitle }}</div>
                                @endif
                                <div class="module-stats">
                                    {{ $moduleLessonCount }} {{ Illuminate\Support\Str::plural('lesson', $moduleLessonCount) }}
                                    · {{ $moduleDurationLabel }}
                                    · {{ $moduleActivityCount }} {{ Illuminate\Support\Str::plural('activity', $moduleActivityCount) }}
                                </div>
                            </div>
                            <div class="module-actions">
                                <button class="btn-text-action" type="button" onclick="toggleModuleEdit({{ $module->idx }})">Edit Module</button>
                                <form method="POST" action="{{ route('faculty.module.delete', [$course->id, $module->key]) }}" onsubmit="return confirm('Delete this module and all of its lessons, activities, and assessments? This cannot be undone.');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Delete module" aria-label="Delete module">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg>
                                    </button>
                                </form>
                            </div>
                        </header>
                        {{-- Keep the old sortable handle available without adding a colored icon tile. --}}
                        <button class="drag-handle module-drag-handle" type="button" draggable="true" title="Drag to reorder module" aria-label="Drag to reorder module">⋮⋮</button>

                        {{-- ── Inline module editor — title + rich-text description ── --}}
                        <form class="lesson-form module-edit-form" id="module-edit-{{ $module->idx }}"
                              style="display:{{ $errors->has('module_description') && (int) session('open_module_edit') === (int) $module->idx ? 'flex' : 'none' }};"
                              method="POST"
                              action="{{ route('faculty.module.update', [$course->id, $module->idx]) }}">
                            @csrf

                            <div class="row2">
                                <input class="input-outline" type="text" name="module_title"
                                       placeholder="Module title *"
                                       value="{{ old('module_title', $module->title) }}" required>
                            </div>

                            @include('components.rich-text-editor', [
                                'name' => 'module_description',
                                'id' => 'module-description-edit-'.$module->idx,
                                'value' => old('module_description', $module->description ?? ''),
                                'placeholder' => 'Describe what this module covers...'
                            ])

                            @if ($errors->has('module_description') && (int) session('open_module_edit') === (int) $module->idx)
                                <div class="fac-course-inline-error">
                                    {{ $errors->first('module_description') }}
                                </div>
                            @endif

                            <div class="actions">
                                <button class="btn-save-lesson" type="submit">Save Module</button>
                                <button class="btn-cancel-outline" type="button"
                                        onclick="toggleModuleEdit({{ $module->idx }})">Cancel</button>
                            </div>
                        </form>

                        @php $showLessonForm = ($addLessonIndex ?? null) === $module->idx
                            || (int) session('open_lesson_form') === (int) $module->idx; @endphp
                        <div class="sortable-list lesson-sort-list" data-order-kind="lessons" data-parent-id="{{ $module->idx }}" data-order-url="{{ route('faculty.lessons.reorder', [$course->id, $module->idx]) }}">
                        @forelse ($module->lessons ?? [] as $lesson)
                            <div class="lesson-sort-item sortable-item" data-order-item data-order-id="{{ $lesson->id }}">
                            <div class="lesson-row">
                                <button class="drag-handle" type="button" draggable="true" title="Drag to reorder lesson" aria-label="Drag to reorder lesson">⋮⋮</button>
                                <span class="lesson-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="lesson-info">
                                    <h4>{{ $lesson->title }}</h4>
                                    <div class="sub">{{ 'Text'.($lesson->duration_raw ? ' · '.$lesson->duration_raw.' min' : '') }}</div>
                                </div>
                                <div class="lesson-actions">
                                    <button type="button" class="lesson-action-btn" onclick="toggleLessonEdit({{ $lesson->id }})" title="Edit lesson">Edit</button>
                                    <form method="POST" action="{{ route('faculty.lesson.delete', [$course->id, $module->idx, $lesson->key ?? 'seed-'.$loop->index]) }}" onsubmit="return confirm('Delete this lesson and its activities and quizzes? This cannot be undone.');">
                                        @csrf
                                        <button class="btn-icon-muted" type="submit" title="Delete lesson" aria-label="Delete lesson">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg>
                                        </button>
                                    </form>
                                    <button type="button" class="lesson-action-btn lesson-collapse-btn" aria-expanded="true" aria-controls="lesson-content-{{ $lesson->id }}" aria-label="Collapse lesson content" title="Collapse lesson" onclick="toggleLessonCollapse({{ $lesson->id }}, this)">
                                        <span class="lesson-collapse-label">Collapse</span>
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="lesson-collapsible-content" id="lesson-content-{{ $lesson->id }}">
                            {{-- Inline lesson editor stays with the lesson content,
                                 before its activities and assessments. --}}
                            @if($lesson->id ?? null)
                                <form class="lesson-form lesson-edit-form" id="lesson-edit-{{ $lesson->id }}"
                                      style="display:{{ session('open_lesson_edit') == $lesson->id ? 'flex' : 'none' }};"
                                      method="POST"
                                      action="{{ route('faculty.lesson.update', [$course->id, $module->idx, $lesson->id]) }}">
                                    @csrf

                                    <div class="row2">
                                        <input class="input-outline" type="text" name="lesson_title"
                                               placeholder="Lesson title *"
                                               value="{{ old('lesson_title', $lesson->title) }}" required>
                                    </div>

                                    @include('components.rich-text-editor', [
                                        'name' => 'lesson_content',
                                        'id' => 'lesson-content-edit-'.$lesson->id,
                                        'value' => old('lesson_content', $lesson->content),
                                        'placeholder' => 'Write the lesson content...'
                                    ])

                                    @if ($errors->has('lesson_content') && (int) session('open_lesson_edit') === (int) $lesson->id)
                                        <div class="fac-course-inline-error">
                                            {{ $errors->first('lesson_content') }}
                                        </div>
                                    @endif

                                    <div class="actions">
                                        <input class="input-outline" type="number" name="duration"
                                               placeholder="Duration ( Min )" min="0"
                                               value="{{ old('duration', $lesson->duration_raw ?: '') }}">
                                        <button class="btn-save-lesson" type="submit">Save Lesson</button>
                                        <button class="btn-cancel-outline" type="button"
                                                onclick="toggleLessonEdit({{ $lesson->id }})">Cancel</button>
                                    </div>
                                </form>
                            @endif

                            <section class="activity-panel" aria-label="Lesson activities">
                                <div class="activity-head">
                                    <strong>Activities</strong>
                                    <button class="btn-primary-action btn-compact" type="button" onclick="toggleActivityForm({{ $lesson->id }}, true)">+ Add Activity</button>
                                </div>
                                @php
                                    $lessonActivities = collect($lesson->activities ?? [])->sortBy('sort_order')->values();
                                @endphp
                                <div class="sortable-list assessment-sort-list" data-order-kind="assessments" data-parent-id="{{ $lesson->id }}" data-order-url="{{ route('faculty.lesson-assessments.reorder', [$course->id, $lesson->id]) }}">
                                @forelse($lessonActivities as $activity)
                                    <div class="assessment-sort-item activity-sort-item sortable-item" data-order-item data-order-type="activity" data-order-id="{{ $activity->id }}">
                                    <article class="activity-card">
                                        <div class="activity-head">
                                            <button class="drag-handle activity-drag-handle" type="button" draggable="true" title="Drag to reorder activity" aria-label="Drag to reorder activity">⋮⋮</button>
                                            <div class="activity-copy"><span class="activity-kind">{{ Illuminate\Support\Str::headline($activity->activity_type) }}</span><strong>{{ $activity->title }}</strong><div class="activity-meta">{{ $activity->is_required ? 'Required' : 'Optional' }}@if($activity->activity_type === 'assignment') · {{ $activity->max_points }} points · Pass {{ $activity->passing_percent ?? 70 }}%@endif</div></div>
                                            <div class="activity-actions"><button class="btn-edit-inline" type="button" onclick="toggleActivityEdit({{ $activity->id }})">Edit</button>
                                                <form method="POST" action="{{ route('faculty.lesson-activity.destroy', [$course->id, $activity->id]) }}" onsubmit="return confirm('Remove this activity? Students will no longer see it. Existing submissions will be retained.');">@csrf<button class="btn-delete-activity" type="submit">Remove</button></form>
                                            </div>
                                        </div>
                                        <form class="activity-form activity-edit-form" id="activity-edit-{{ $activity->id }}" @if((int) session('open_activity_edit') !== (int) $activity->id) hidden @endif method="POST" action="{{ route('faculty.lesson-activity.update', [$course->id, $activity->id]) }}">@csrf
                                            @if((int) session('open_activity_edit') === (int) $activity->id && $errors->any())<div class="full fac-course-error-text" role="alert">{{ $errors->first() }}</div>@endif
                                            <input name="title" maxlength="255" value="{{ old('title', $activity->title) }}" placeholder="Activity title" required>
                                            <select name="activity_type">@foreach(['practice'=>'Practice','reflection'=>'Reflection','assignment'=>'Assignment (faculty reviewed)'] as $type=>$label)<option value="{{ $type }}" @selected(old('activity_type', $activity->activity_type) === $type)>{{ $label }}</option>@endforeach</select>
                                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'activity-instructions-edit-'.$activity->id, 'value' => old('instructions', $activity->instructions), 'placeholder' => 'Instructions and criteria'])
                                            <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $activity->is_required))> Required for lesson completion</label>
                                            <div class="activity-actions"><input type="number" name="max_points" min="1" max="10000" placeholder="Points (assignment)" value="{{ old('max_points', $activity->max_points) }}"><input type="number" name="passing_percent" min="1" max="100" placeholder="Pass %" value="{{ old('passing_percent', $activity->passing_percent) }}"></div>
                                            <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityEdit({{ $activity->id }})">Cancel</button></div>
                                        </form>
                                    </article>
                                    </div>
                                @empty
                                    <p class="activity-empty">No activities added to this lesson.</p>
                                @endforelse
                                @if($lesson->quiz ?? null)
                                    <section class="assessment-sort-item lesson-assessment sortable-item" data-order-item data-order-type="quiz" data-order-id="{{ $lesson->quiz->id }}" aria-label="Lesson assessment">
                                        <div class="assessment-label">Assessment</div>
                                        <div class="lesson-quiz-row">
                                            <button class="drag-handle activity-drag-handle" type="button" draggable="true" title="Drag to reorder lesson assessment" aria-label="Drag to reorder lesson assessment">⋮⋮</button>
                                            <div><strong class="lesson-quiz-title">{{ $lesson->quiz->title }}</strong><div class="activity-meta">{{ $lesson->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $lesson->quiz->questions_count) }} · Pass {{ $lesson->quiz->passing_score }}%</div></div>
                                            <div class="activity-actions">
                                                <a class="btn-edit-inline" href="{{ route('faculty.lesson-quiz.create', [$course->id, $lesson->id]) }}">Edit Quiz</a>
                                            </div>
                                        </div>
                                    </section>
                                @endif
                                </div>
                                @unless($lesson->quiz ?? null)
                                    <div class="lesson-assessment-empty">
                                        <div class="assessment-label">Assessment</div>
                                        <span class="quiz-none">No lesson quiz created yet.</span>
                                        <a class="btn-secondary-action btn-compact" href="{{ route('faculty.lesson-quiz.create', [$course->id, $lesson->id]) }}">+ Add Lesson Quiz</a>
                                    </div>
                                @endunless
                                <form class="activity-form" id="activity-add-{{ $lesson->id }}" @if((int) session('open_activity_form') !== (int) $lesson->id) hidden @endif method="POST" action="{{ route('faculty.lesson-activity.store', [$course->id, $lesson->id]) }}">@csrf
                                    @if((int) session('open_activity_form') === (int) $lesson->id && $errors->any())<div class="full fac-course-error-text" role="alert">{{ $errors->first() }}</div>@endif
                                    <div class="activity-form-context"><strong>Add activity to this lesson</strong><span>{{ $lesson->title }}</span></div>
                                    <input name="title" maxlength="255" value="{{ old('title') }}" placeholder="Activity title" required>
                                    <select name="activity_type"><option value="practice" @selected(old('activity_type') === 'practice')>Practice</option><option value="reflection" @selected(old('activity_type') === 'reflection')>Reflection</option><option value="assignment" @selected(old('activity_type') === 'assignment')>Assignment (faculty reviewed)</option></select>
                                    @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'activity-instructions-add-'.$lesson->id, 'value' => old('instructions'), 'placeholder' => 'Instructions and criteria'])
                                    <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', true))> Required for lesson completion</label>
                                    <div class="activity-actions"><input type="number" name="max_points" min="1" max="10000" value="{{ old('max_points') }}" placeholder="Points (assignment)"><input type="number" name="passing_percent" min="1" max="100" value="{{ old('passing_percent') }}" placeholder="Pass %"></div>
                                    <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityForm({{ $lesson->id }}, false)">Cancel</button></div>
                                </form>
                            </section>

                            </div>{{-- /lesson-collapsible-content --}}

                            </div>{{-- /lesson-sort-item --}}

                        @empty
                            <div class="lessons-empty" id="lessons-empty-{{ $module->idx }}">
                                <span>This module has no lessons yet.</span>
                            </div>
                        @endforelse
                        </div>{{-- /lesson-sort-list --}}

                        <form class="lesson-form" id="lesson-form-{{ $module->idx }}"
                              style="display:{{ $showLessonForm ? 'flex' : 'none' }};"
                              method="POST" action="{{ route('faculty.lesson.store', [$course->id, $module->idx]) }}">
                            @csrf
                            <label class="form-field-label" for="lesson-title-add-{{ $module->idx }}">Lesson title</label>
                            <input class="input-outline" id="lesson-title-add-{{ $module->idx }}" type="text" name="lesson_title" placeholder="e.g. HTML, CSS, and JavaScript" value="{{ old('lesson_title') }}" required>
                            <label class="form-field-label" for="lesson-content-add-{{ $module->idx }}">Description</label>
                            @include('components.rich-text-editor', [
                                'name' => 'lesson_content',
                                'id' => 'lesson-content-add-'.$module->idx,
                                'value' => old('lesson_content'),
                                'placeholder' => 'Write the lesson content...'
                            ])
                            @if ($errors->has('lesson_content') && (int) session('open_lesson_form') === (int) $module->idx)
                                <div class="form-error" role="alert">{{ $errors->first('lesson_content') }}</div>
                            @endif
                            <div class="actions">
                                <label class="duration-field">Duration
                                    <input class="input-outline" type="number" name="duration" placeholder="Minutes" min="0" value="{{ old('duration') }}">
                                    <span>minutes</span>
                                </label>
                                <button class="btn-secondary-action" type="button" onclick="toggleLessonForm({{ $module->idx }}, false)">Cancel</button>
                                <button class="btn-primary-action" type="submit">Save Lesson</button>
                            </div>
                        </form>
                        <div class="module-add-lesson">
                            <button class="btn-primary-action" type="button" onclick="toggleLessonForm({{ $module->idx }}, true)">+ Add Lesson</button>
                        </div>

                        {{-- The module quiz is the summative assessment for all lessons in this module. --}}
                        <section class="quiz-row summative-assessment" id="quiz-row-{{ $module->idx }}" aria-label="Summative assessment">
                            <div class="summative-label">Summative Assessment</div>
                            <div class="summative-content">
                            @if($module->quiz ?? null)
                                <div class="quiz-info">
                                    <h4>{{ $module->quiz->title }}</h4>
                                    <div class="sub">{{ $module->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $module->quiz->questions_count) }} · Pass {{ $module->quiz->passing_score }}%</div>
                                </div>
                                <a class="btn-edit-inline" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">Edit Quiz</a>
                                <form method="POST"
                                      action="{{ route('faculty.quiz.destroy', [$course->id, $module->idx]) }}"
                                      class="fac-inline-action"
                                      onsubmit="return confirm('Delete this summative quiz and its student attempts? This cannot be undone.');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Delete summative quiz" aria-label="Delete summative quiz"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button>
                                </form>
                            @else
                                <span class="quiz-none">No summative assessment created yet.</span>
                                <a class="btn-secondary-action" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">+ Add Summative Quiz</a>
                            @endif
                            </div>
                        </section>
                    </div>
                @empty
                    <div class="empty-modules">
                        <h4>No Modules yet</h4>
                        <p>Add your first Module above</p>
                    </div>
                @endforelse
                </div>{{-- /module-sort-list --}}
                <div id="reorder-status" aria-live="polite"></div>

                <section class="panel fac-final-exam-panel" aria-labelledby="course-completion-title">
                    <h3 id="course-completion-title" class="fac-final-exam-title">Course completion</h3>
                    <p class="fac-final-exam-description">Add a separate course assessment that unlocks after students finish the course modules.</p>
                    @if($finalModule)
                        <div class="fac-final-exam-header">
                            <div>
                                <strong>Final Exam</strong>
                                @if($finalModule->quiz)
                                    <div class="fac-final-exam-meta">{{ $finalModule->quiz->title }} · {{ $finalModule->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $finalModule->quiz->questions_count) }} · Pass {{ $finalModule->quiz->passing_score }}% · {{ $finalModule->quiz->attempts ?: 'Unlimited' }} attempts</div>
                                @else
                                    <div class="fac-final-exam-warning">Final Exam setup is required before course approval.</div>
                                @endif
                            </div>
                            <div class="fac-final-exam-actions">
                                <a class="btn-secondary-action" href="{{ route('faculty.final-exam.edit', [$course->id, $finalModule->id]) }}">Edit Final Exam</a>
                                <form method="POST" action="{{ route('faculty.final-exam.destroy', $course->id) }}" onsubmit="return confirm('Remove the final exam and its quiz?');">
                                    @csrf
                                    <button class="btn-text-action" type="submit">Remove final exam</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('faculty.final-exam.store', $course->id) }}">
                            @csrf
                            <button class="btn-primary-action" type="submit">Add final exam</button>
                        </form>
                    @endif
                </section>
            </section>

            {{-- Course quiz averages remain available below the authoring workspace. --}}
            @if($quizAverages->isNotEmpty())
            <aside>
                <div class="panel">
                    <div class="panel-head">Quiz Average</div>
                    <div class="panel-body">
                        @forelse ($quizAverages as $avg)
                            <div class="avg-row">
                                <span class="avg-title">{{ $avg->title }}</span>
                                <div class="avg-track">
                                    <div class="avg-fill" style="width:{{ $avg->percent }}%"></div>
                                </div>
                                <span class="avg-pct">{{ $avg->percent }}%</span>
                            </div>
                        @empty
                            <div class="empty-state fac-empty-state-plain">No quiz results yet.</div>
                        @endforelse
                    </div>
                </div>
            </aside>
            @endif

        </div>

    @elseif (in_array($mode, ['quiz', 'final-exam'], true))

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• QUIZ BUILDER MODE â€” "Add Quiz : <module>" â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        {{-- âœ… Back to the Managing Course screen --}}
        <a class="back-link" href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">&larr; Back to Modules</a>
        <div class="faculty-page-heading"><h2 class="quiz-head faculty-page-title">@if($mode === 'final-exam'){{ ($quiz ?? null) ? 'Edit' : 'Create' }} Final Exam: {{ $course->title }}@else{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz: {{ $moduleTitle }}@endif</h2></div>
        <x-flash-toast :message="session('success')" />

        {{-- âœ… Real form â€” Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form method="POST" action="{{ $mode === 'final-exam' ? route('faculty.final-exam.update', [$course->id, $moduleIdx]) : (!empty($lessonId) ? route('faculty.lesson-quiz.store', [$course->id, $lessonId]) : route('faculty.quiz.store', [$course->id, $moduleIdx])) }}">
            @csrf
            @if(request()->boolean('inline'))<input type="hidden" name="inline" value="1">@endif

            {{-- Quiz settings --}}
            <div class="quiz-card">
                <div class="grid3">
                    <div>
                        <label class="q-label">Quiz Title <span class="req">*</span></label>
                        <input class="input-outline fac-width-full" type="text" name="quiz_title" placeholder="MySQL Fundamentals Quiz" value="{{ $quiz->title ?? '' }}" required>
                    </div>
                    <div>
                        <label class="q-label">No. of Items <span class="req">*</span></label>
                        <input class="input-outline fac-width-full" type="number" name="items" placeholder="1" min="1" value="{{ $quiz->items ?? '' }}">
                    </div>
                    <div>
                        <label class="q-label">Passing Score (%)</label>
                        <input class="input-outline fac-width-full" type="number" name="passing_score" placeholder="100" min="0" max="100" value="{{ $quiz->passing_score ?? '' }}">
                    </div>
                </div>
                <div class="grid2">
                    <div>
                        <label class="q-label">Attempts allowed</label>
                        <input class="input-outline fac-width-full" type="text" name="attempts" placeholder="Unlimited" value="{{ $quiz->attempts ?? '' }}">
                        <small class="field-help">Leave blank for unlimited attempts. Enter a number to set a limit.</small>
                    </div>
                    <div>
                        <label class="q-label">Time Limit (min)</label>
                        <input class="input-outline fac-width-full" type="number" name="time_limit" placeholder="No time limit" min="1" value="{{ ($quiz->time_limit ?? 0) > 0 ? $quiz->time_limit : '' }}">
                        <small class="field-help">Leave blank to allow unlimited time. A timer is applied only when you enter a number.</small>
                    </div>
                </div>
                <div>
                    <label class="q-label">Instructions ( Optional )</label>
                    @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'assessment-instructions', 'value' => old('instructions', $quiz->instructions ?? ''), 'placeholder' => 'Add instructions for students...'])
                </div>
            </div>

            {{-- Questions â€” for a NEW quiz the containers are generated after
                 clicking "Create Quiz"; the count follows "No. of Items" --}}
            @php
                $questions = $quiz->questions ?? [];
                $isNewQuiz = empty($questions);
            @endphp

            @if ($isNewQuiz)
            <div class="quiz-card fac-quiz-intro" id="create-quiz-intro">
                <p class="fac-quiz-intro-copy">
                    Set the {{ $mode === 'final-exam' ? 'final exam' : 'quiz' }} details above &mdash; especially <strong>No. of Items</strong> &mdash; then click
                    <strong>{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</strong>. One question container will be created per item.
                </p>
                <button class="btn-save-lesson" type="button" onclick="createQuizQuestions()">{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</button>
            </div>
            @endif

            <div id="questions-area" style="display:{{ $isNewQuiz ? 'none' : 'block' }};">
            <div class="question-bulk-tools" id="question-bulk-tools">
                <label for="select-all-questions">
                    <input class="question-select" id="select-all-questions" type="checkbox" aria-label="Select all questions">
                    <span>Select all questions</span>
                </label>
                <span class="question-bulk-status" id="question-bulk-status" aria-live="polite">Select questions to change their type or points together.</span>
            </div>
            <div id="questions">
                @foreach ($questions as $q => $question)
                    <div class="quiz-card question-card">
                        <div class="q-head">
                            <input class="question-select question-select-item" type="checkbox" aria-label="Select question {{ $q + 1 }}" onchange="updateQuestionSelectionState()">
                            <h3>Question {{ $q + 1 }}</h3>
                            <span class="type-label">Type</span>
                            <div class="sel-wrap">
                                @php $qType = $question['type'] ?? 'Multiple Choice'; @endphp
                                <select name="questions[{{ $q }}][type]" onchange="questionTypeChanged(this)">
                                    <option value="Multiple Choice" {{ $qType === 'Multiple Choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="True or False" {{ $qType === 'True or False' ? 'selected' : '' }}>True or False</option>
                                    <option value="Identification" {{ $qType === 'Identification' ? 'selected' : '' }}>Identification</option>
                                </select>
                            </div>
                            <input class="input-outline points" type="number" name="questions[{{ $q }}][points]" placeholder="Points" min="0" value="{{ $question['points'] ?? '' }}" oninput="syncSelectedQuestionPoints(this)">
                            {{-- âœ… Removes THIS question card --}}
                            <button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">&times;</button>
                        </div>

                        <div class="fac-question-field-spacing">
                            <input class="input-outline question-input fac-width-full" type="text" name="questions[{{ $q }}][text]" placeholder="What does SQL stand for?" value="{{ $question['text'] ?? '' }}">
                        </div>

                        @php
                            $savedChoices = $question['choices'] ?? [];
                            $choiceCount  = max(3, count($savedChoices));
                            $correct      = $question['correct'] ?? null;
                        @endphp

                        {{-- âœ… The section below changes with the question Type --}}

                        {{-- Multiple Choice: A/B/C choices + Add a Choice --}}
                        <div class="q-mc" style="display:{{ $qType === 'Multiple Choice' ? 'block' : 'none' }};">
                            <div class="choices" id="choices-{{ $q }}">
                                @for ($i = 0; $i < $choiceCount; $i++)
                                    @php $letter = chr(65 + $i); @endphp
                                    <div class="choice-row">
                                        <span class="choice-letter">{{ $letter }}.</span>
                                        <input class="input-outline" type="text" name="questions[{{ $q }}][choices][]" placeholder="Choice {{ $letter }}" value="{{ $qType === 'Multiple Choice' ? ($savedChoices[$i] ?? '') : '' }}">
                                        <label class="mark-correct">
                                            Mark as Correct
                                            <input type="radio" name="questions[{{ $q }}][correct]" value="{{ $i }}" {{ $qType === 'Multiple Choice' && $correct !== null && (int) $correct === $i ? 'checked' : '' }}>
                                            <span class="radio-dot"></span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            {{-- âœ… Adds another choice row (D, E, F ...) --}}
                            <button class="btn-add-choice" type="button" onclick="addChoice({{ $q }})">+ Add a Choice</button>
                        </div>

                        {{-- True or False: fixed True / False rows --}}
                        <div class="q-tf" style="display:{{ $qType === 'True or False' ? 'block' : 'none' }};">
                            @foreach (['True', 'False'] as $ti => $tfLabel)
                                <div class="choice-row">
                                    <span class="tf-label">{{ $tfLabel }}</span>
                                    <label class="mark-correct">
                                        Mark as Correct
                                        <input type="radio" name="questions[{{ $q }}][tf_correct]" value="{{ $ti }}" {{ $qType === 'True or False' && $correct !== null && (int) $correct === $ti ? 'checked' : '' }}>
                                        <span class="radio-dot"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Identification: a single correct-answer field --}}
                        <div class="q-id" style="display:{{ $qType === 'Identification' ? 'block' : 'none' }};">
                            <label class="q-label">Correct Answer</label>
                            <input class="input-outline fac-identification-answer" type="text" name="questions[{{ $q }}][answer]" placeholder="Type the correct answer" value="{{ $question['answer'] ?? '' }}">
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- âœ… Adds another fresh Question card --}}
            <button class="btn-add-choice fac-add-question-spacing" type="button" onclick="addQuestion()">+ Add Question</button>
            </div>{{-- /questions-area --}}

            <div class="save-quiz-row" id="save-quiz-row" style="display:{{ $isNewQuiz ? 'none' : 'flex' }};">
                <button class="btn-save-lesson" type="submit">{{ $mode === 'final-exam' ? 'Save Final Exam' : 'Save Quiz' }}</button>
            </div>
        </form>

    @else

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• LIST MODE â€” My Courses â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        <div class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">My Courses</h2>
                <p class="faculty-page-subtitle">{{ $courses->count() }} Total {{ $courses->count() == 1 ? 'course' : 'courses' }} created</p>
            </div>
            {{-- âœ… Connected â€” goes to Faculty â€º Create Courses only --}}
            <a href="{{ route('faculty.create') }}" class="btn-outline" aria-label="Create a new course"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Create New Course</span></a>
        </div>

        @forelse ($courses as $course)
            <div class="course-card">
                <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    @php($shortDescription = trim((string) ($course->short_description ?? '')))
                    @if($shortDescription !== '')
                        <div class="desc">{{ $shortDescription }}</div>
                    @endif
                    <div class="course-meta">
                        <span class="status-chip status-{{ Illuminate\Support\Str::slug($course->status ?? 'Draft') }}">
                            {{ $course->status ?? 'Draft' }}
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->students_count ?? 0 }}</span>
                            Students
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->modules_count ?? 0 }}</span>
                            Modules
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->lessons_count ?? 0 }}</span>
                            Lessons
                        </span>
                    </div>
                </div>
                <div class="card-actions">
                    {{-- ✅ Manage → Managing Course screen for THIS course (same blade, manage mode) --}}
                    <a href="{{ route('faculty.courses.manage', $course->id ?? 1) }}" class="btn-action primary">Manage</a>
                    <a href="{{ route('faculty.activities.reviews', $course->id ?? 1) }}" class="btn-action">Activity Reviews</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                You haven't created any courses yet.
            </div>
        @endforelse

    @endif

    </main>

    <x-slot:scripts>
<script>
    function toggleModuleCreate(show) {
        var form = document.getElementById('module-create-form');
        var button = document.getElementById('toggle-module-create');
        if (!form) return;
        var opening = typeof show === 'boolean' ? show : form.style.display === 'none';
        form.style.display = opening ? 'flex' : 'none';
        if (button) button.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            form.querySelector('input[name="module_title"]')?.focus();
        }
    }

    function toggleLessonForm(i, show) {
        var form  = document.getElementById('lesson-form-' + i);
        var empty = document.getElementById('lessons-empty-' + i);
        if (form)  form.style.display  = show ? 'flex' : 'none';
        if (empty) empty.style.display = show ? 'none' : 'block';
        if (show && form) {
            var first = form.querySelector('input[name="lesson_title"]');
            if (first) first.focus();
        }
    }

    function toggleActivityForm(lessonId, show) {
        var form = document.getElementById('activity-add-' + lessonId);
        if (!form) return;
        form.hidden = typeof show === 'boolean' ? !show : !form.hidden;
        if (!form.hidden) form.querySelector('input[name="title"]')?.focus();
    }

    function toggleActivityEdit(activityId) {
        var form = document.getElementById('activity-edit-' + activityId);
        if (!form) return;
        form.hidden = !form.hidden;
        if (!form.hidden) form.querySelector('input[name="title"]')?.focus();
    }

    document.querySelectorAll('[data-order-kind][data-order-url]').forEach(function (list) {
        var draggedItem = null;
        var queuedItems = null;
        var isSavingOrder = false;

        async function saveQueuedOrder() {
            if (isSavingOrder || !queuedItems) return;
            isSavingOrder = true;
            var status = document.getElementById('reorder-status');

            while (queuedItems) {
                var items = queuedItems;
                queuedItems = null;
                if (status) status.textContent = 'Saving new order…';

                try {
                    var response = await fetch(list.dataset.orderUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ items: items }),
                    });
                    if (!response.ok) throw new Error('The new order could not be saved.');
                } catch (error) {
                    queuedItems = null;
                    if (status) status.textContent = error.message || 'Could not save the new order.';
                    window.setTimeout(function () { window.location.reload(); }, 900);
                    break;
                }
            }

            isSavingOrder = false;
            if (!queuedItems && status && status.textContent === 'Saving new order…') {
                status.textContent = 'Order saved.';
            }
            if (queuedItems) saveQueuedOrder();
        }

        list.addEventListener('dragstart', function (event) {
            var handle = event.target.closest('.drag-handle');
            if (!handle || handle.closest('.sortable-list') !== list) return;
            draggedItem = handle.closest('[data-order-item]');
            if (!draggedItem || draggedItem.parentElement !== list) return;
            draggedItem.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', draggedItem.dataset.orderId);
        });
        list.addEventListener('dragend', function () {
            list.querySelectorAll('.is-dragging,.is-drop-target').forEach(function (item) {
                item.classList.remove('is-dragging', 'is-drop-target');
            });
            draggedItem = null;
        });
        list.addEventListener('dragover', function (event) {
            if (!draggedItem) return;
            // Accept drops on the list's empty space too. Without this,
            // dropping below the last row can move the DOM item but never
            // dispatch a `drop` event, so the previous server order returns.
            event.preventDefault();
            var target = event.target.closest('[data-order-item]');
            if (!target || target === draggedItem || target.parentElement !== list) {
                var remainingItems = Array.from(list.querySelectorAll(':scope > [data-order-item]'))
                    .filter(function (item) { return item !== draggedItem; });
                var nextItem = remainingItems.find(function (item) {
                    var bounds = item.getBoundingClientRect();
                    return event.clientY < bounds.top + bounds.height / 2;
                });
                list.insertBefore(draggedItem, nextItem || null);
                return;
            }
            target.classList.add('is-drop-target');
            var bounds = target.getBoundingClientRect();
            var insertAfter = event.clientY > bounds.top + bounds.height / 2;
            list.insertBefore(draggedItem, insertAfter ? target.nextSibling : target);
        });
        list.addEventListener('dragleave', function (event) {
            var target = event.target.closest('[data-order-item]');
            if (target && !list.contains(event.relatedTarget)) target.classList.remove('is-drop-target');
        });
        list.addEventListener('drop', function (event) {
            if (!draggedItem) return;
            event.preventDefault();
            var kind = list.dataset.orderKind;
            var orderedItems = Array.from(list.querySelectorAll(':scope > [data-order-item]'));
            queuedItems = kind === 'assessments'
                ? orderedItems.map(function (item) { return { type: item.dataset.orderType, id: Number(item.dataset.orderId) }; })
                : orderedItems.map(function (item) { return Number(item.dataset.orderId); });
            saveQueuedOrder();
        });
    });

    // Lessons are editor-first. Keep CKEditor data synchronized with the
    // underlying textarea before the normal browser form submission.
    document.querySelectorAll('.lesson-form, #module-create-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-ckeditor]').forEach(function (editorElement) {
                var editor = window.upskillEditorInstances?.get(editorElement);
                if (editor) editorElement.value = editor.getData();
            });
        });
    });

    // Show / hide the inline module editor (title + description).
    function toggleModuleEdit(moduleIdx) {
        var box = document.getElementById('module-edit-' + moduleIdx);
        if (!box) return;
        var opening = box.style.display === 'none' || box.style.display === '';
        box.style.display = opening ? 'flex' : 'none';
        if (opening) {
            var first = box.querySelector('input[name="module_title"]');
            if (first) first.focus();
        }
    }

    function addChoice(q) {
        var box = document.getElementById('choices-' + q);
        if (!box) return;
        var i = box.children.length;
        if (i >= 26) return;
        var letter = String.fromCharCode(65 + i);   // D, E, F ...
        var row = document.createElement('div');
        row.className = 'choice-row';
        row.innerHTML =
            '<span class="choice-letter">' + letter + '.</span>' +
            '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
            '<label class="mark-correct">Mark as Correct' +
            '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
            '<span class="radio-dot"></span></label>';
        box.appendChild(row);
    }

    // Next unused question index (indices never repeat, even after removals)
    var nextQuestionIndex = document.querySelectorAll('#questions .question-card').length;

    // â”€â”€ "Create Quiz" â€” builds exactly N question containers, where N is the
    //    value of the "No. of Items" field (5 items â†’ 5 question cards) â”€â”€
    function createQuizQuestions() {
        var itemsInput = document.querySelector('input[name="items"]');
        var n = parseInt(itemsInput && itemsInput.value, 10);
        if (!n || n < 1) {
            alert('Please set the "No. of Items" first (at least 1) before creating the quiz.');
            if (itemsInput) itemsInput.focus();
            return;
        }
        if (n > 100) n = 100;

        var wrap = document.getElementById('questions');
        if (wrap) wrap.innerHTML = '';
        nextQuestionIndex = 0;
        for (var i = 0; i < n; i++) addQuestion();

        var intro = document.getElementById('create-quiz-intro');
        if (intro) intro.style.display = 'none';
        var area = document.getElementById('questions-area');
        if (area) area.style.display = 'block';
        var saveRow = document.getElementById('save-quiz-row');
        if (saveRow) saveRow.style.display = 'flex';
    }

    // âœ… Swaps the question card's display to match the chosen Type
    function questionTypeChanged(sel) {
        var card = sel.closest('.question-card');
        if (!card) return;
        var t = sel.value;
        renderQuestionType(card, t);

        var selection = card.querySelector('.question-select-item');
        if (selection && selection.checked) {
            document.querySelectorAll('#questions .question-card').forEach(function (selectedCard) {
                if (selectedCard === card) return;
                var selected = selectedCard.querySelector('.question-select-item');
                var selectedType = selectedCard.querySelector('select[name$="[type]"]');
                if (!selected || !selected.checked || !selectedType) return;
                selectedType.value = t;
                renderQuestionType(selectedCard, t);
            });
        }
    }

    function renderQuestionType(card, type) {
        var mc = card.querySelector('.q-mc');
        var tf = card.querySelector('.q-tf');
        var id = card.querySelector('.q-id');
        if (mc) mc.style.display = type === 'Multiple Choice' ? 'block' : 'none';
        if (tf) tf.style.display = type === 'True or False' ? 'block' : 'none';
        if (id) id.style.display = type === 'Identification' ? 'block' : 'none';
    }

    function updateQuestionSelectionState() {
        var master = document.getElementById('select-all-questions');
        var status = document.getElementById('question-bulk-status');
        var cards = Array.prototype.slice.call(document.querySelectorAll('#questions .question-card'));
        var selectedCount = cards.filter(function (card) {
            var checkbox = card.querySelector('.question-select-item');
            return checkbox && checkbox.checked;
        }).length;

        if (master) {
            master.checked = cards.length > 0 && selectedCount === cards.length;
            master.indeterminate = selectedCount > 0 && selectedCount < cards.length;
            master.setAttribute('aria-checked', master.indeterminate ? 'mixed' : String(master.checked));
        }
        if (status) {
            status.textContent = selectedCount === 0
                ? 'Select questions to change their type or points together.'
                : selectedCount + ' question' + (selectedCount === 1 ? '' : 's') + ' selected. Changing type or points on a selected question updates all selected questions.';
        }
    }

    function syncSelectedQuestionPoints(input) {
        var card = input.closest('.question-card');
        var selection = card && card.querySelector('.question-select-item');
        if (!selection || !selection.checked) return;

        document.querySelectorAll('#questions .question-card').forEach(function (selectedCard) {
            if (selectedCard === card) return;
            var selected = selectedCard.querySelector('.question-select-item');
            var points = selectedCard.querySelector('.points');
            if (selected && selected.checked && points) points.value = input.value;
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.id === 'select-all-questions') {
            var shouldSelect = event.target.checked;
            document.querySelectorAll('#questions .question-select-item').forEach(function (checkbox) {
                checkbox.checked = shouldSelect;
            });
            updateQuestionSelectionState();
        }
    });

    function addQuestion() {
        var wrap = document.getElementById('questions');
        if (!wrap) return;
        var q = nextQuestionIndex++;
        var card = document.createElement('div');
        card.className = 'quiz-card question-card';
        var tfHtml = '';
        ['True', 'False'].forEach(function (label, i) {
            tfHtml +=
                '<div class="choice-row">' +
                '<span class="tf-label">' + label + '</span>' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][tf_correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        var choicesHtml = '';
        ['A', 'B', 'C'].forEach(function (letter, i) {
            choicesHtml +=
                '<div class="choice-row">' +
                '<span class="choice-letter">' + letter + '.</span>' +
                '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        card.innerHTML =
            '<div class="q-head">' +
                '<input class="question-select question-select-item" type="checkbox" aria-label="Select question" onchange="updateQuestionSelectionState()">' +
                '<h3>Question ?</h3>' +
                '<span class="type-label">Type</span>' +
                '<div class="sel-wrap">' +
                    '<select name="questions[' + q + '][type]" onchange="questionTypeChanged(this)">' +
                        '<option value="Multiple Choice">Multiple Choice</option>' +
                        '<option value="True or False">True or False</option>' +
                        '<option value="Identification">Identification</option>' +
                    '</select>' +
                '</div>' +
                '<input class="input-outline points" type="number" name="questions[' + q + '][points]" placeholder="Points" min="0" oninput="syncSelectedQuestionPoints(this)">' +
                '<button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">\u2715</button>' +
            '</div>' +
            '<div style="margin-bottom:18px;">' +
                '<input class="input-outline question-input" style="width:100%;" type="text" name="questions[' + q + '][text]" placeholder="Enter your question">' +
            '</div>' +
            '<div class="q-mc">' +
                '<div class="choices" id="choices-' + q + '">' + choicesHtml + '</div>' +
                '<button class="btn-add-choice" type="button" onclick="addChoice(' + q + ')">+ Add a Choice</button>' +
            '</div>' +
            '<div class="q-tf" style="display:none;">' + tfHtml + '</div>' +
            '<div class="q-id" style="display:none;">' +
                '<label class="q-label">Correct Answer</label>' +
                '<input class="input-outline" style="width:100%;max-width:420px;" type="text" name="questions[' + q + '][answer]" placeholder="Type the correct answer">' +
            '</div>';
        wrap.appendChild(card);
        renumberQuestions();
        updateQuestionSelectionState();
        var first = card.querySelector('input[type="text"]');
        if (first) first.focus();
    }

    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (card && confirm('Remove this question?')) {
            card.remove();
            renumberQuestions();
            updateQuestionSelectionState();
        }
    }

    function renumberQuestions() {
        document.querySelectorAll('#questions .question-card .q-head h3').forEach(function (h, i) {
            h.textContent = 'Question ' + (i + 1);
        });
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
    @include('components.responsive')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.manage-flash:not(.error)').forEach(function (message) {
            window.setTimeout(function () {
                message.classList.add('is-dismissing');
                window.setTimeout(function () {
                    message.remove();
                }, 280);
            }, 5000);
        });
    });

    function toggleLessonCollapse(lessonId, button) {
        var content = document.getElementById('lesson-content-' + lessonId);
        if (!content || !button) return;

        var isExpanded = button.getAttribute('aria-expanded') === 'true';
        var nextLabel = isExpanded ? 'Expand' : 'Collapse';

        content.hidden = isExpanded;
        button.setAttribute('aria-expanded', String(!isExpanded));
        button.setAttribute('aria-label', nextLabel + ' lesson content');
        button.setAttribute('title', nextLabel + ' lesson');

        var label = button.querySelector('.lesson-collapse-label');
        if (label) label.textContent = nextLabel;
    }

    // Show / hide the inline lesson editor.
    function toggleLessonEdit(lessonId) {
        var box = document.getElementById('lesson-edit-' + lessonId);
        if (!box) return;
        var opening = box.style.display === 'none' || box.style.display === '';
        box.style.display = opening ? 'flex' : 'none';   // .lesson-form is a flex column
        if (opening) {
            var first = box.querySelector('input[name="lesson_title"]');
            if (first) first.focus();
        }
    }

    // Reopen the editor the server flagged after a failed upload.
    document.addEventListener('DOMContentLoaded', function () {
        var open = document.querySelector('.lesson-edit-form[style*="flex"], .module-edit-form[style*="flex"]');
        if (open) open.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
</script>
    </x-slot:scripts>
</x-faculty.layout>
