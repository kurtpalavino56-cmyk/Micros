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
<x-faculty.layout title="{{ $mode === 'final-exam' ? (($quiz ?? null) ? 'Edit Final Exam' : 'Create Final Exam') : (in_array($mode, ['quiz', 'module-quiz'], true) ? 'Add Quiz' : ($mode === 'manage' ? 'Manage Course' : 'My Courses')) }} | Upskill" data-faculty-page="faculty-courses-index" class="{{ $facultyBodyClass }}" use-app-css use-app-js>
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
                    @if($modules->isNotEmpty())
                        <button class="btn-secondary-action module-toggle-all" type="button" aria-expanded="false" onclick="toggleAllModules(this)">Expand All</button>
                    @endif
                </div>

                <div class="sortable-list module-sort-list" data-order-kind="modules" data-order-url="{{ route('faculty.modules.reorder', $course->id) }}">
                @forelse ($modules as $module)
                    <div class="module-card sortable-item" data-order-item data-order-id="{{ $module->idx }}">
                        @php
                            $moduleLessons = $module->lessons ?? collect();
                            $moduleLessonCount = $moduleLessons->count();
                            $moduleActivityCount = collect($module->activities ?? [])->count();
                            $moduleDurationMinutes = $moduleLessons->sum(fn ($lesson) => (int) ($lesson->duration_raw ?? 0));
                            $moduleDurationLabel = $moduleDurationMinutes >= 60
                                ? intdiv($moduleDurationMinutes, 60).'h '.($moduleDurationMinutes % 60).'m'
                                : $moduleDurationMinutes.' min';
                        @endphp
                        <header class="module-head">
                            <div class="module-heading-copy">
                                <span class="eyebrow-label">Module {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h4>{{ $module->title }}</h4>
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
                        @php
                            $moduleHasOpenForm = ($errors->has('module_description') && (int) session('open_module_edit') === (int) $module->idx)
                                || ($addLessonIndex ?? null) === $module->idx
                                || (int) session('open_lesson_form') === (int) $module->idx
                                || (int) session('open_module_content') === (int) $module->idx;
                        @endphp
                        <button class="module-collapse-toggle" type="button" aria-expanded="{{ $moduleHasOpenForm ? 'true' : 'false' }}" aria-controls="module-body-{{ $module->idx }}" aria-label="{{ $moduleHasOpenForm ? 'Collapse' : 'Expand' }} module" onclick="toggleModuleCollapse({{ $module->idx }}, this)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
                        <div id="module-body-{{ $module->idx }}" @if(! $moduleHasOpenForm) hidden @endif>

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

                        @php
    $showLessonForm = ($addLessonIndex ?? null) === $module->idx
        || (int) session('open_lesson_form') === (int) $module->idx;
    $moduleItems = collect($module->lessons ?? [])->map(fn ($lesson) => (object) ['type' => 'lesson', 'order' => $lesson->module_order, 'item' => $lesson])
        ->merge(collect($module->activities ?? [])->map(fn ($activity) => (object) ['type' => 'activity', 'order' => $activity->module_order, 'item' => $activity]))
        ->merge(collect($module->learning_quizzes ?? [])->map(fn ($quiz) => (object) ['type' => 'quiz', 'order' => $quiz->module_order, 'item' => $quiz]))
        ->sortBy('order')->values();
@endphp

                        <form class="lesson-form" id="lesson-form-{{ $module->idx }}"
                              style="display:{{ $showLessonForm ? 'flex' : 'none' }};"
                              method="POST" action="{{ route('faculty.lesson.store', [$course->id, $module->idx]) }}">
                            @csrf
                            <label class="form-field-label" for="lesson-title-add-{{ $module->idx }}">Lesson title</label>
                            <input class="input-outline" id="lesson-title-add-{{ $module->idx }}" type="text" name="lesson_title" placeholder="e.g. Web Page Foundations" value="{{ old('lesson_title') }}" required>
                            <label class="form-field-label" for="lesson-content-add-{{ $module->idx }}">Description</label>
                            @include('components.rich-text-editor', ['name' => 'lesson_content', 'id' => 'lesson-content-add-'.$module->idx, 'value' => old('lesson_content'), 'placeholder' => 'Write the lesson content...'])
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

                        <form class="activity-form module-activity-create-form" id="module-activity-add-{{ $module->idx }}" @if((int) session('open_module_content') !== (int) $module->idx) hidden @endif method="POST" action="{{ route('faculty.module-activity.store', [$course->id, $module->idx]) }}">@csrf
                            @if((int) session('open_module_content') === (int) $module->idx && $errors->any())<div class="full fac-course-error-text" role="alert">{{ $errors->first() }}</div>@endif
                            <div class="activity-form-context"><strong>Add activity to this module</strong><span>{{ $module->title }}</span></div>
                            <input name="title" maxlength="255" value="{{ old('title') }}" placeholder="Activity title" required>
                            <select name="activity_type"><option value="practice" @selected(old('activity_type', 'practice') === 'practice')>Practice</option><option value="reflection" @selected(old('activity_type') === 'reflection')>Reflection</option><option value="assignment" @selected(old('activity_type') === 'assignment')>Assignment (faculty reviewed)</option></select>
                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'module-activity-instructions-add-'.$module->idx, 'value' => old('instructions'), 'placeholder' => 'Instructions and criteria'])
                            <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', true))> Required for module completion</label>
                            <div class="activity-actions" data-assignment-fields><input type="number" name="max_points" min="1" max="10000" value="{{ old('max_points') }}" placeholder="Points (assignment)"><input type="number" name="passing_percent" min="1" max="100" value="{{ old('passing_percent') }}" placeholder="Pass %"></div>
                            <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleModuleActivityForm({{ $module->idx }}, false)">Cancel</button></div>
                        </form>

                        <div class="sortable-list module-content-sort-list" data-order-kind="module-items" data-parent-id="{{ $module->idx }}" data-order-url="{{ route('faculty.module-items.reorder', [$course->id, $module->idx]) }}">
                            @forelse($moduleItems as $moduleItem)
                                @php $item = $moduleItem->item; @endphp
                                <article class="module-content-card sortable-item module-content-card-{{ $moduleItem->type }}" data-order-item data-order-type="{{ $moduleItem->type }}" data-order-id="{{ $item->id }}">
                                    <button class="drag-handle module-content-drag-handle" type="button" draggable="true" title="Drag to reorder {{ $moduleItem->type }}" aria-label="Drag to reorder {{ $moduleItem->type }}">⋮⋮</button>
                                    <span class="module-content-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="module-content-copy">
                                        @if($moduleItem->type === 'lesson')
                                            <span class="module-content-kind">Lesson</span>
                                            <strong>{{ $item->title }}</strong>
                                            <span class="module-content-meta">Text{{ $item->duration_raw ? ' · '.$item->duration_raw.' min' : '' }}</span>
                                        @elseif($moduleItem->type === 'activity')
                                            <span class="module-content-kind">{{ Illuminate\Support\Str::headline($item->activity_type) }} · Activity</span>
                                            <strong>{{ $item->title }}</strong>
                                            <span class="module-content-meta">{{ $item->is_required ? 'Required' : 'Optional' }} · {{ $item->submissions_count }} submissions</span>
                                        @else
                                            <span class="module-content-kind">Quiz</span>
                                            <strong>{{ $item->title }}</strong>
                                            <span class="module-content-meta">{{ $item->questions_count }} questions · Pass {{ $item->passing_score }}%@if(($item->sets_count ?? 1) > 1) · {{ $item->sets_count }} sets @endif</span>
                                        @endif
                                    </div>
                                    <div class="module-content-actions">
                                        @if($moduleItem->type === 'lesson')
                                            <button class="module-content-action" type="button" onclick="toggleLessonEdit({{ $item->id }})">Edit</button>
                                            <form method="POST" action="{{ route('faculty.lesson.delete', [$course->id, $module->idx, $item->key]) }}" onsubmit="return confirm('Remove this lesson? This cannot be undone.');">@csrf<button class="btn-icon-danger" type="submit" title="Remove lesson" aria-label="Remove lesson"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button></form>
                                        @elseif($moduleItem->type === 'activity')
                                            <button class="module-content-action" type="button" onclick="toggleActivityEdit({{ $item->id }})">Edit</button>
                                            <form method="POST" action="{{ route('faculty.lesson-activity.destroy', [$course->id, $item->id]) }}" onsubmit="return confirm('Remove this activity? Existing submissions will be retained.');">@csrf<button class="btn-icon-danger" type="submit" title="Remove activity" aria-label="Remove activity"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button></form>
                                        @else
                                            <a class="module-content-action" href="{{ route('faculty.module-quiz.edit', [$course->id, $module->idx, $item->id]) }}">Edit</a>
                                            <form method="POST" action="{{ route('faculty.module-quiz.destroy', [$course->id, $module->idx, $item->id]) }}" onsubmit="return confirm('Remove this quiz and its student attempts? This cannot be undone.');">@csrf<button class="btn-icon-danger" type="submit" title="Remove quiz" aria-label="Remove quiz"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button></form>
                                        @endif
                                    </div>

                                    @if($moduleItem->type === 'lesson')
                                        <form class="lesson-form lesson-edit-form module-content-edit-form" id="lesson-edit-{{ $item->id }}" style="display:none;" method="POST" action="{{ route('faculty.lesson.update', [$course->id, $module->idx, $item->id]) }}">@csrf
                                            <div class="row2"><input class="input-outline" type="text" name="lesson_title" placeholder="Lesson title *" value="{{ old('lesson_title', $item->title) }}" required></div>
                                            @include('components.rich-text-editor', ['name' => 'lesson_content', 'id' => 'lesson-content-edit-'.$item->id, 'value' => old('lesson_content', $item->content), 'placeholder' => 'Write the lesson content...'])
                                            @if ($errors->has('lesson_content') && (int) session('open_lesson_edit') === (int) $item->id)<div class="fac-course-inline-error">{{ $errors->first('lesson_content') }}</div>@endif
                                            <div class="actions"><input class="input-outline" type="number" name="duration" placeholder="Duration ( Min )" min="0" value="{{ old('duration', $item->duration_raw ?: '') }}"><button class="btn-save-lesson" type="submit">Save Lesson</button><button class="btn-cancel-outline" type="button" onclick="toggleLessonEdit({{ $item->id }})">Cancel</button></div>
                                        </form>
                                    @elseif($moduleItem->type === 'activity')
                                        <form class="activity-form activity-edit-form module-content-edit-form" id="activity-edit-{{ $item->id }}" hidden method="POST" action="{{ route('faculty.lesson-activity.update', [$course->id, $item->id]) }}">@csrf
                                            <input name="title" maxlength="255" value="{{ $item->title }}" placeholder="Activity title" required>
                                            <select name="activity_type">@foreach(['practice'=>'Practice','reflection'=>'Reflection','assignment'=>'Assignment (faculty reviewed)'] as $type=>$label)<option value="{{ $type }}" @selected($item->activity_type === $type)>{{ $label }}</option>@endforeach</select>
                                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'module-activity-instructions-edit-'.$item->id, 'value' => $item->instructions, 'placeholder' => 'Instructions and criteria'])
                                            <label><input type="checkbox" name="is_required" value="1" @checked($item->is_required)> Required for module completion</label>
                                            <div class="activity-actions" data-assignment-fields><input type="number" name="max_points" min="1" max="10000" value="{{ $item->max_points }}" placeholder="Points (assignment)"><input type="number" name="passing_percent" min="1" max="100" value="{{ $item->passing_percent }}" placeholder="Pass %"></div>
                                            <div class="activity-actions full"><button class="btn-save-lesson" type="submit">Save Activity</button><button class="btn-cancel-outline" type="button" onclick="toggleActivityEdit({{ $item->id }})">Cancel</button></div>
                                        </form>
                                    @endif
                                </article>
                            @empty
                                <div class="module-content-empty">This module has no learning items yet.</div>
                            @endforelse
                        </div>

                        <div class="module-add-lesson">
                            <details class="module-add-menu">
                                <summary class="btn-primary-action module-authoring-add-button">+ Add</summary>
                                <div class="module-add-options">
                                    <button type="button" onclick="hideModuleAddMenu(this); toggleLessonForm({{ $module->idx }}, true)">Lesson</button>
                                    <button type="button" onclick="hideModuleAddMenu(this); toggleModuleActivityForm({{ $module->idx }}, true)">Activity</button>
                                    <a href="{{ route('faculty.module-quiz.create', [$course->id, $module->idx]) }}" onclick="hideModuleAddMenu(this)">Quiz</a>
                                </div>
                            </details>
                        </div>

                        {{-- The module quiz is the summative assessment for all lessons in this module. --}}
                        <section class="quiz-row summative-assessment" id="quiz-row-{{ $module->idx }}" aria-label="Summative assessment">
                            <div class="summative-label">Summative Assessment</div>
                            <div class="summative-content">
                            @if($module->quiz ?? null)
                                <div class="quiz-info">
                                    <h4>{{ $module->quiz->title }}</h4>
                                    <div class="sub">{{ $module->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $module->quiz->questions_count) }} · Pass {{ $module->quiz->passing_score }}%@if(($module->quiz->sets_count ?? 1) > 1) · {{ $module->quiz->sets_count }} sets @endif</div>
                                </div>
                                <a class="module-content-action" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">Edit</a>
                                <form method="POST"
                                      action="{{ route('faculty.quiz.destroy', [$course->id, $module->idx]) }}"
                                      class="fac-inline-action"
                                      onsubmit="return confirm('Delete this summative quiz and its student attempts? This cannot be undone.');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Remove summative assessment" aria-label="Remove summative assessment"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button>
                                </form>
                            @else
                                <span class="quiz-none">No summative assessment created yet.</span>
                                <a class="btn-secondary-action" href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">+ Add Summative Quiz</a>
                            @endif
                            </div>
                        </section>
                        </div>
                    </div>
                @empty
                    <div class="empty-modules">
                        <h4>No Modules yet</h4>
                        <p>Add your first module below</p>
                    </div>
                @endforelse
                </div>{{-- /module-sort-list --}}
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
                <div class="module-add-module-footer">
                    <button class="btn-primary-action module-authoring-add-button" type="button" id="toggle-module-create" aria-expanded="{{ $errors->has('module_title') || $errors->has('module_description') ? 'true' : 'false' }}" aria-controls="module-create-form" onclick="toggleModuleCreate()">+ Add Module</button>
                </div>
                <div id="reorder-status" aria-live="polite"></div>

                <section class="panel fac-final-exam-panel" aria-labelledby="course-completion-title">
                    @if($finalModule)
                        <div class="fac-final-exam-header">
                            <div>
                                <strong>Final Exam</strong>
                                @if($finalModule->quiz)
                                    <div class="fac-final-exam-meta">{{ $finalModule->quiz->title }} · {{ $finalModule->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $finalModule->quiz->questions_count) }} · Pass {{ $finalModule->quiz->passing_score }}% · {{ $finalModule->quiz->attempts ?: 'Unlimited' }} attempts @if(($finalModule->quiz->sets_count ?? 1) > 1) · {{ $finalModule->quiz->sets_count }} sets @endif</div>
                                @else
                                    <div class="fac-final-exam-warning">Final Exam setup is required before course approval.</div>
                                @endif
                            </div>
                            <div class="fac-final-exam-actions">
                                <a class="btn-secondary-action" href="{{ route('faculty.final-exam.edit', [$course->id, $finalModule->id]) }}">Edit Final Exam</a>
                                <form method="POST" action="{{ route('faculty.final-exam.destroy', $course->id) }}" onsubmit="return confirm('Remove the final exam and its quiz?');">
                                    @csrf
                                    <button class="btn-icon-danger" type="submit" title="Remove final exam" aria-label="Remove final exam"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button>
                                </form>
                            </div>
                        </div>
                    @else
                        <form class="fac-final-exam-add-form" method="POST" action="{{ route('faculty.final-exam.store', $course->id) }}">
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

    @elseif (in_array($mode, ['quiz', 'module-quiz', 'final-exam'], true))

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• QUIZ BUILDER MODE â€” "Add Quiz : <module>" â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

        {{-- âœ… Back to the Managing Course screen --}}
        <a class="back-link" href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">&larr; Back to Modules</a>
        <div class="faculty-page-heading"><h2 class="quiz-head faculty-page-title">@if($mode === 'final-exam'){{ ($quiz ?? null) ? 'Edit' : 'Create' }} Final Exam: {{ $course->title }}@else{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz: {{ $moduleTitle }}@endif</h2></div>
        <x-flash-toast :message="session('success')" />

        {{-- âœ… Real form â€” Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form method="POST" action="{{ $mode === 'final-exam' ? route('faculty.final-exam.update', [$course->id, $moduleIdx]) : ($mode === 'module-quiz' ? (!empty($moduleQuizId) ? route('faculty.module-quiz.update', [$course->id, $moduleIdx, $moduleQuizId]) : route('faculty.module-quiz.store', [$course->id, $moduleIdx])) : (!empty($lessonId) ? route('faculty.lesson-quiz.store', [$course->id, $lessonId]) : route('faculty.quiz.store', [$course->id, $moduleIdx]))) }}">
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
                        <input class="input-outline fac-width-full" type="number" name="items" id="quiz-items" placeholder="1" min="1" max="100" value="{{ $quiz->items ?? '' }}" onchange="itemsChanged(this)">
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
                <div class="quiz-shuffle-options">
                    <label>
                        <input type="checkbox" name="shuffle_questions" value="1" @checked(old('_token') ? old('shuffle_questions') : ($quiz->shuffle_questions ?? false))>
                        <span><strong>Shuffle question order</strong><small>Each attempt shows its set's questions in a different order.</small></span>
                    </label>
                    <label>
                        <input type="checkbox" name="shuffle_choices" value="1" @checked(old('_token') ? old('shuffle_choices') : ($quiz->shuffle_choices ?? false))>
                        <span><strong>Shuffle choices order</strong><small>Multiple-choice options appear in a different order per attempt. True or False and locked questions keep their order.</small></span>
                    </label>
                </div>
                <div>
                    <label class="q-label">Instructions ( Optional )</label>
                    @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'assessment-instructions', 'value' => old('instructions', $quiz->instructions ?? ''), 'placeholder' => 'Add instructions for students...'])
                </div>
            </div>

            {{-- Questions live in SETS (Set A, Set B, ...). Every set has the same
                 number of questions; each student attempt is served one set.
                 All sets stay in the DOM (only the active one is visible) so a
                 single form POST saves everything as sets[s][questions][q][...].
                 For a NEW quiz the cards are generated after "Create Quiz". --}}
            @php
                $quizSets = collect($quiz->sets ?? []);
                $knownSets = $quizSets->keyBy(fn ($set) => (string) ($set['id'] ?? ''));
                $knownQuestions = $quizSets->flatMap(fn ($set) => $set['questions'] ?? [])->keyBy(fn ($question) => (string) ($question['id'] ?? ''));
                $oldSets = old('sets');
                if (is_array($oldSets) && $oldSets !== []) {
                    // Re-populate after a failed save, keeping ids and the
                    // "answered"/"used" flags the server knows about.
                    $builderSets = collect($oldSets)->values()->map(function ($set) use ($knownSets, $knownQuestions) {
                        $setId = isset($set['id']) && $set['id'] !== '' ? (int) $set['id'] : null;

                        return [
                            'id' => $setId,
                            'name' => (string) ($set['name'] ?? ''),
                            'used' => (bool) ($knownSets[(string) $setId]['used'] ?? false),
                            'questions' => collect($set['questions'] ?? [])->values()->map(function ($question) use ($knownQuestions) {
                                $questionId = isset($question['id']) && $question['id'] !== '' ? (int) $question['id'] : null;
                                $type = $question['type'] ?? 'Multiple Choice';

                                return [
                                    'id' => $questionId,
                                    'text' => (string) ($question['text'] ?? ''),
                                    'type' => $type,
                                    'points' => $question['points'] ?? '',
                                    'choices' => array_values((array) ($question['choices'] ?? [])),
                                    'correct' => $type === 'True or False' ? ($question['tf_correct'] ?? null) : ($question['correct'] ?? null),
                                    'answer' => $question['answer'] ?? '',
                                    'lock_choice_order' => ! empty($question['lock_choice_order']),
                                    'answered' => (bool) ($knownQuestions[(string) $questionId]['answered'] ?? false),
                                ];
                            })->all(),
                        ];
                    })->all();
                } else {
                    $builderSets = $quizSets->isNotEmpty()
                        ? $quizSets->values()->all()
                        : [['id' => null, 'name' => 'Set A', 'used' => false, 'questions' => []]];
                }
                $isNewQuiz = collect($builderSets)->every(fn ($set) => empty($set['questions']));
                $quizHasAttempts = (bool) ($quiz->has_attempts ?? false);
            @endphp

            <style>
                .quiz-sets-layout { display: flex; gap: 20px; align-items: flex-start; }
                .quiz-sets-main { flex: 1 1 auto; min-width: 0; }
                .quiz-set-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin: 0 0 14px; }
                .quiz-set-bar__title { margin: 0; font-size: 1rem; color: #123b7a; }
                .quiz-set-bar__title small { color: #526078; font-weight: 500; }
                .quiz-sets-toggle { border: 1px solid #c9d3e3; background: #fff; color: #123b7a; border-radius: 8px; padding: 7px 12px; font-weight: 700; cursor: pointer; }
                .quiz-sets-drawer { flex: 0 0 280px; position: sticky; top: 16px; border: 1px solid #dfe5ef; border-radius: 12px; background: #fff; padding: 14px; box-shadow: 0 6px 18px rgba(18, 59, 122, .06); }
                .quiz-sets-drawer[hidden] { display: none; }
                .quiz-sets-drawer__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
                .quiz-sets-drawer__head h3 { margin: 0; font-size: 1rem; color: #123b7a; }
                .quiz-sets-drawer__close { border: 0; background: none; font-size: 1.3rem; line-height: 1; cursor: pointer; color: #526078; }
                .quiz-sets-drawer__help { margin: 0 0 12px; color: #526078; font-size: .82rem; line-height: 1.45; }
                .quiz-set-list { list-style: none; margin: 0 0 12px; padding: 0; display: grid; gap: 8px; }
                .quiz-set-item { border: 1px solid #dfe5ef; border-radius: 10px; padding: 8px 10px; }
                .quiz-set-item.is-active { border-color: #123b7a; background: #f3f6fc; }
                .quiz-set-switch { display: flex; width: 100%; justify-content: space-between; gap: 8px; border: 0; background: none; padding: 0; text-align: left; cursor: pointer; font: inherit; font-weight: 700; color: #182b4d; }
                .quiz-set-switch span:last-child { color: #526078; font-weight: 500; font-size: .82rem; white-space: nowrap; }
                .quiz-set-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
                .quiz-set-actions button { border: 1px solid #d8e0eb; background: #fff; border-radius: 6px; padding: 3px 8px; font-size: .78rem; cursor: pointer; color: #182b4d; }
                .quiz-set-actions button.is-danger { color: #b42318; }
                .quiz-set-used { display: block; margin-top: 4px; color: #8a5a00; font-size: .75rem; }
                .quiz-set-add { width: 100%; border: 1px dashed #9fb0cc; background: #f8fafc; border-radius: 10px; padding: 9px; font-weight: 700; color: #123b7a; cursor: pointer; }
                .quiz-set-panel[hidden] { display: none; }
                .quiz-shuffle-options { display: grid; gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px solid #e5eaf1; }
                .quiz-shuffle-options label { display: flex; gap: 10px; align-items: flex-start; cursor: pointer; }
                .quiz-shuffle-options small { display: block; color: #526078; }
                .lock-choice-order { display: flex; gap: 8px; align-items: flex-start; margin-top: 10px; font-size: .88rem; color: #182b4d; cursor: pointer; }
                .lock-choice-order small { display: block; color: #526078; }
                .question-answered-note { margin-top: 10px; color: #8a5a00; font-size: .8rem; }
                .quiz-builder-notice { margin: 0 0 16px; padding: 12px 14px; border-left: 4px solid #dba617; background: #fff7dc; border-radius: 8px; color: #4a3b00; font-size: .9rem; }
                .quiz-builder-errors { margin: 0 0 16px; padding: 12px 14px; border-left: 4px solid #b42318; background: #fee4e2; border-radius: 8px; color: #7a1a12; }
                .quiz-builder-errors ul { margin: 6px 0 0; padding-left: 18px; }
                @media (max-width: 980px) {
                    .quiz-sets-layout { flex-direction: column-reverse; }
                    .quiz-sets-drawer { position: static; width: 100%; flex-basis: auto; box-sizing: border-box; }
                }
            </style>

            @if ($errors->any())
                <div class="quiz-builder-errors" role="alert">
                    <strong>The quiz was not saved.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @if ($quizHasAttempts)
                <div class="quiz-builder-notice" role="note">
                    Students have already taken this quiz. Questions they answered and sets they were served can't be removed, so their attempt history stays intact. Changing question content starts a fresh attempt count; renaming sets or changing the shuffle options does not.
                </div>
            @endif

            @if ($isNewQuiz)
            <div class="quiz-card fac-quiz-intro" id="create-quiz-intro">
                <p class="fac-quiz-intro-copy">
                    Set the {{ $mode === 'final-exam' ? 'final exam' : 'quiz' }} details above &mdash; especially <strong>No. of Items</strong> &mdash; then click
                    <strong>{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</strong>. One question container will be created per item. You can add more sets (Set B, Set C, ...) afterwards.
                </p>
                <button class="btn-save-lesson" type="button" onclick="createQuizQuestions()">{{ $mode === 'final-exam' ? 'Create Final Exam' : 'Create Quiz' }}</button>
            </div>
            @endif

            <div id="questions-area" style="display:{{ $isNewQuiz ? 'none' : 'block' }};">
            <div class="quiz-sets-layout">
                <div class="quiz-sets-main">
                    <div class="quiz-set-bar">
                        <h3 class="quiz-set-bar__title"><span id="active-set-name">Set A</span> <small id="active-set-meta"></small></h3>
                        <button class="quiz-sets-toggle" id="quiz-sets-toggle" type="button" aria-controls="quiz-sets-drawer" aria-expanded="true" onclick="toggleSetsDrawer()">Sets (<span id="quiz-sets-count">1</span>)</button>
                    </div>
                    <div class="question-bulk-tools" id="question-bulk-tools">
                        <label for="select-all-questions">
                            <input class="question-select" id="select-all-questions" type="checkbox" aria-label="Select all questions in this set">
                            <span>Select all questions</span>
                        </label>
                        <span class="question-bulk-status" id="question-bulk-status" aria-live="polite">Select questions to change their type or points together.</span>
                    </div>
                    <div id="questions"></div>

                    {{-- Adds a fresh card at the end of EVERY set (equal counts). --}}
                    <button class="btn-add-choice fac-add-question-spacing" type="button" onclick="addQuestion()">+ Add Question</button>
                </div>

                <aside class="quiz-sets-drawer" id="quiz-sets-drawer" aria-label="Question sets">
                    <div class="quiz-sets-drawer__head">
                        <h3>Sets</h3>
                        <button class="quiz-sets-drawer__close" type="button" aria-label="Collapse sets panel" onclick="toggleSetsDrawer(false)">&times;</button>
                    </div>
                    <p class="quiz-sets-drawer__help">Each student attempt gets one set. All sets have the same number of questions; retakes rotate to the next set.</p>
                    <ul class="quiz-set-list" id="quiz-set-list"></ul>
                    <button class="quiz-set-add" type="button" onclick="addSet()">+ Add set</button>
                </aside>
            </div>
            </div>{{-- /questions-area --}}

            <div class="save-quiz-row" id="save-quiz-row" style="display:{{ $isNewQuiz ? 'none' : 'flex' }};">
                <button class="btn-save-lesson" type="submit">{{ $mode === 'final-exam' ? 'Save Final Exam' : 'Save Quiz' }}</button>
            </div>
            <script>
                var QUIZ_BUILDER_SETS = @json($builderSets);
                var QUIZ_BUILDER_HAS_ATTEMPTS = @json($quizHasAttempts);
            </script>
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

    function toggleModuleActivityForm(moduleId, show) {
        var form = document.getElementById('module-activity-add-' + moduleId);
        if (!form) return;
        form.hidden = typeof show === 'boolean' ? !show : !form.hidden;
        if (!form.hidden) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            form.querySelector('input[name="title"]')?.focus();
        }
    }

    function hideModuleAddMenu(option) {
        var menu = option.closest('.module-add-menu');
        if (menu) menu.open = false;
    }

    function toggleActivityEdit(activityId) {
        var form = document.getElementById('activity-edit-' + activityId);
        if (!form) return;
        form.hidden = !form.hidden;
        if (!form.hidden) form.querySelector('input[name="title"]')?.focus();
    }

    function syncActivityScoringFields(form) {
        var isAssignment = form.querySelector('[name="activity_type"]')?.value === 'assignment';
        var fields = form.querySelector('[data-assignment-fields]');
        if (!fields) return;
        fields.hidden = !isAssignment;
        fields.querySelectorAll('input').forEach(function (input) {
            input.disabled = !isAssignment;
        });
    }

    document.querySelectorAll('.module-activity-create-form, .activity-edit-form').forEach(function (form) {
        var typeSelect = form.querySelector('[name="activity_type"]');
        if (!typeSelect) return;
        typeSelect.addEventListener('change', function () {
            syncActivityScoringFields(form);
        });
        syncActivityScoringFields(form);
    });

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
                    if (status) showReorderToast(error.message || 'Could not save the new order.', 'error');
                    window.setTimeout(function () { window.location.reload(); }, 900);
                    break;
                }
            }

            isSavingOrder = false;
            if (!queuedItems && status && status.textContent === 'Saving new order…') {
                status.textContent = '';
                showReorderToast('Order saved.', 'success');
            }
            if (queuedItems) saveQueuedOrder();
        }

        function showReorderToast(message, type) {
            var toast = document.createElement('div');
            toast.className = 'reorder-toast reorder-toast--' + type;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
            toast.textContent = message;
            document.body.appendChild(toast);
            window.setTimeout(function () { toast.classList.add('is-visible'); }, 10);
            window.setTimeout(function () {
                toast.classList.remove('is-visible');
                window.setTimeout(function () { toast.remove(); }, 200);
            }, 3500);
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
            queuedItems = kind === 'assessments' || kind === 'module-items'
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

    function toggleModuleCollapse(moduleIdx, button) {
        var body = document.getElementById('module-body-' + moduleIdx);
        if (!body) return;
        var isExpanded = button.getAttribute('aria-expanded') === 'true';
        body.hidden = isExpanded;
        button.setAttribute('aria-expanded', String(!isExpanded));
        button.setAttribute('aria-label', isExpanded ? 'Expand module' : 'Collapse module');
    }

    document.querySelectorAll('.module-card').forEach(function (moduleCard) {
        var moduleToggle = moduleCard.querySelector('.module-collapse-toggle');
        var moduleBody = moduleToggle && document.getElementById(moduleToggle.getAttribute('aria-controls'));
        if (!moduleToggle || !moduleBody) return;

        var pendingExpandKey = 'faculty-course-module-expand-' + moduleToggle.getAttribute('aria-controls');
        if (sessionStorage.getItem(pendingExpandKey) === '1') {
            sessionStorage.removeItem(pendingExpandKey);
            moduleBody.hidden = false;
            moduleToggle.setAttribute('aria-expanded', 'true');
            moduleToggle.setAttribute('aria-label', 'Collapse module');
        }

        moduleCard.querySelectorAll('form').forEach(function (moduleForm) {
            moduleForm.addEventListener('submit', function () {
                sessionStorage.setItem(pendingExpandKey, '1');
            });
        });
    });

    function toggleAllModules(button) {
        var shouldExpand = button.getAttribute('aria-expanded') !== 'true';
        document.querySelectorAll('.module-sort-list .module-collapse-toggle').forEach(function (toggle) {
            var body = document.getElementById(toggle.getAttribute('aria-controls'));
            if (!body) return;
            body.hidden = !shouldExpand;
            toggle.setAttribute('aria-expanded', String(shouldExpand));
            toggle.setAttribute('aria-label', shouldExpand ? 'Collapse module' : 'Expand module');
        });
        button.setAttribute('aria-expanded', String(shouldExpand));
        button.textContent = shouldExpand ? 'Collapse All' : 'Expand All';
    }

    /* ── Quiz builder with sets ───────────────────────────────────────────
       Every set is a .quiz-set-panel inside #questions; only the active one
       is visible, but all stay in the DOM so one POST saves everything as
       sets[s][questions][q][...]. Set keys (s) and question keys (q) are
       unique counters that never repeat, so radio groups never collide;
       the server reads them in DOM order. Equal-count rule: adding,
       removing and resizing always apply to every set at the same position. */
    var _setKey = 0;
    var _questionKey = 0;
    var _activeSetKey = null;
    var _lastItems = null;

    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function setPanels() {
        return Array.prototype.slice.call(document.querySelectorAll('#questions .quiz-set-panel'));
    }

    function activePanel() {
        return document.querySelector('#questions .quiz-set-panel[data-set-key="' + _activeSetKey + '"]') || setPanels()[0] || null;
    }

    function panelCards(panel) {
        return panel ? Array.prototype.slice.call(panel.querySelectorAll('.question-card')) : [];
    }

    function questionCount() {
        return panelCards(setPanels()[0]).length;
    }

    function cardIsFilled(card) {
        var text = card.querySelector('.question-input');
        return !!(text && text.value.trim() !== '');
    }

    function nextSetName() {
        var used = {};
        setPanels().forEach(function (panel) { used[panel.querySelector('.set-name-input').value.trim().toLowerCase()] = true; });
        for (var i = 0; i < 702; i++) {
            var name = 'Set ' + (i < 26 ? String.fromCharCode(65 + i) : String.fromCharCode(64 + Math.floor(i / 26)) + String.fromCharCode(65 + (i % 26)));
            if (!used[name.toLowerCase()]) return name;
        }
        return 'Set ' + (setPanels().length + 1);
    }

    function createSetPanel(set) {
        var s = _setKey++;
        var panel = document.createElement('div');
        panel.className = 'quiz-set-panel';
        panel.dataset.setKey = String(s);
        panel.dataset.used = set.used ? '1' : '0';
        panel.innerHTML =
            '<input type="hidden" name="sets[' + s + '][id]" value="' + esc(set.id || '') + '">' +
            '<input type="hidden" class="set-name-input" name="sets[' + s + '][name]" value="' + esc(set.name || nextSetName()) + '">' +
            '<input type="hidden" class="set-position-input" name="sets[' + s + '][position]" value="0">' +
            '<div class="set-cards"></div>';
        document.getElementById('questions').appendChild(panel);
        (set.questions || []).forEach(function (question) { appendCard(panel, question); });
        return panel;
    }

    function choiceRowHtml(prefix, i, value, checked) {
        var letter = String.fromCharCode(65 + i);
        return '<div class="choice-row">' +
            '<span class="choice-letter">' + letter + '.</span>' +
            '<input class="input-outline" type="text" name="' + prefix + '[choices][]" placeholder="Choice ' + letter + '" value="' + esc(value) + '">' +
            '<label class="mark-correct">Mark as Correct' +
            '<input type="radio" name="' + prefix + '[correct]" value="' + i + '"' + (checked ? ' checked' : '') + '>' +
            '<span class="radio-dot"></span></label>' +
            '</div>';
    }

    function appendCard(panel, question) {
        question = question || {};
        var s = panel.dataset.setKey;
        var q = _questionKey++;
        var prefix = 'sets[' + s + '][questions][' + q + ']';
        var type = question.type || 'Multiple Choice';
        var correct = question.correct === null || question.correct === undefined || question.correct === '' ? null : parseInt(question.correct, 10);
        var choices = type === 'Multiple Choice' ? (question.choices || []) : [];
        var choiceCount = Math.max(3, choices.length);

        var choicesHtml = '';
        for (var i = 0; i < choiceCount; i++) {
            choicesHtml += choiceRowHtml(prefix, i, choices[i] || '', type === 'Multiple Choice' && correct === i);
        }
        var tfHtml = '';
        ['True', 'False'].forEach(function (label, i) {
            tfHtml += '<div class="choice-row"><span class="tf-label">' + label + '</span>' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="' + prefix + '[tf_correct]" value="' + i + '"' + (type === 'True or False' && correct === i ? ' checked' : '') + '>' +
                '<span class="radio-dot"></span></label></div>';
        });

        var card = document.createElement('div');
        card.className = 'quiz-card question-card';
        card.dataset.answered = question.answered ? '1' : '0';
        card.innerHTML =
            '<input type="hidden" name="' + prefix + '[id]" value="' + esc(question.id || '') + '">' +
            '<div class="q-head">' +
                '<input class="question-select question-select-item" type="checkbox" aria-label="Select question" onchange="updateQuestionSelectionState()">' +
                '<h3>Question ?</h3>' +
                '<span class="type-label">Type</span>' +
                '<div class="sel-wrap"><select name="' + prefix + '[type]" onchange="questionTypeChanged(this)">' +
                    ['Multiple Choice', 'True or False', 'Identification'].map(function (option) {
                        return '<option value="' + option + '"' + (option === type ? ' selected' : '') + '>' + option + '</option>';
                    }).join('') +
                '</select></div>' +
                '<input class="input-outline points" type="number" name="' + prefix + '[points]" placeholder="Points" min="0" value="' + esc(question.points === 0 ? 0 : (question.points || '')) + '" oninput="syncSelectedQuestionPoints(this)">' +
                '<button class="btn-x" type="button" title="Remove this question from every set" onclick="removeQuestion(this)">&times;</button>' +
            '</div>' +
            '<div class="fac-question-field-spacing">' +
                '<input class="input-outline question-input fac-width-full" type="text" name="' + prefix + '[text]" placeholder="Enter your question" value="' + esc(question.text || '') + '">' +
            '</div>' +
            '<div class="q-mc" style="display:' + (type === 'Multiple Choice' ? 'block' : 'none') + ';">' +
                '<div class="choices" data-prefix="' + prefix + '">' + choicesHtml + '</div>' +
                '<button class="btn-add-choice" type="button" onclick="addChoice(this)">+ Add a Choice</button>' +
                '<label class="lock-choice-order"><input type="checkbox" name="' + prefix + '[lock_choice_order]" value="1"' + (question.lock_choice_order ? ' checked' : '') + '>' +
                    '<span>Lock choice order<small>Keep these choices in the order written even when choice shuffling is on (for options like "All of the above").</small></span></label>' +
            '</div>' +
            '<div class="q-tf" style="display:' + (type === 'True or False' ? 'block' : 'none') + ';">' + tfHtml + '</div>' +
            '<div class="q-id" style="display:' + (type === 'Identification' ? 'block' : 'none') + ';">' +
                '<label class="q-label">Correct Answer</label>' +
                '<input class="input-outline fac-identification-answer" type="text" name="' + prefix + '[answer]" placeholder="Type the correct answer" value="' + esc(type === 'Identification' ? (question.answer || '') : '') + '">' +
            '</div>' +
            (question.answered ? '<div class="question-answered-note">Students have answered this question, so it can\'t be removed.</div>' : '');
        panel.querySelector('.set-cards').appendChild(card);
        return card;
    }

    function addChoice(button) {
        var box = button.closest('.q-mc').querySelector('.choices');
        var i = box.children.length;
        if (i >= 26) return;
        box.insertAdjacentHTML('beforeend', choiceRowHtml(box.dataset.prefix, i, '', false));
    }

    function switchSet(key) {
        _activeSetKey = String(key);
        setPanels().forEach(function (panel) { panel.hidden = panel.dataset.setKey !== _activeSetKey; });
        document.querySelectorAll('#questions .question-select-item').forEach(function (checkbox) { checkbox.checked = false; });
        refreshBuilder();
    }

    function refreshBuilder() {
        var panels = setPanels();
        var active = activePanel();
        if (active && active.dataset.setKey !== _activeSetKey) _activeSetKey = active.dataset.setKey;
        panels.forEach(function (panel, index) {
            panel.querySelector('.set-position-input').value = index;
            panelCards(panel).forEach(function (card, i) {
                var heading = card.querySelector('.q-head h3');
                if (heading) heading.textContent = 'Question ' + (i + 1);
            });
        });

        var count = questionCount();
        var name = active ? active.querySelector('.set-name-input').value : 'Set A';
        document.getElementById('active-set-name').textContent = name;
        document.getElementById('active-set-meta').textContent = '· ' + count + ' question' + (count === 1 ? '' : 's') + (panels.length > 1 ? ' in each of ' + panels.length + ' sets' : '');
        document.getElementById('quiz-sets-count').textContent = panels.length;

        var list = document.getElementById('quiz-set-list');
        list.innerHTML = '';
        panels.forEach(function (panel) {
            var key = panel.dataset.setKey;
            var item = document.createElement('li');
            item.className = 'quiz-set-item' + (key === _activeSetKey ? ' is-active' : '');
            item.innerHTML =
                '<button class="quiz-set-switch" type="button" onclick="switchSet(' + key + ')"' + (key === _activeSetKey ? ' aria-current="true"' : '') + '>' +
                    '<span>' + esc(panel.querySelector('.set-name-input').value) + '</span><span>' + panelCards(panel).length + ' items</span></button>' +
                '<div class="quiz-set-actions">' +
                    '<button type="button" onclick="renameSet(' + key + ')">Rename</button>' +
                    '<button type="button" onclick="duplicateSet(' + key + ')">Duplicate</button>' +
                    '<button type="button" class="is-danger" onclick="deleteSet(' + key + ')">Delete</button>' +
                '</div>' +
                (panel.dataset.used === '1' ? '<span class="quiz-set-used">Served to students, can\'t be deleted</span>' : '');
            list.appendChild(item);
        });

        var itemsInput = document.getElementById('quiz-items');
        if (itemsInput && count > 0) itemsInput.value = count;
        _lastItems = count;
        updateQuestionSelectionState();
    }

    function toggleSetsDrawer(open) {
        var drawer = document.getElementById('quiz-sets-drawer');
        var show = typeof open === 'boolean' ? open : drawer.hidden;
        drawer.hidden = !show;
        document.getElementById('quiz-sets-toggle').setAttribute('aria-expanded', String(show));
    }

    function panelByKey(key) {
        return document.querySelector('#questions .quiz-set-panel[data-set-key="' + key + '"]');
    }

    function addSet() {
        var panel = createSetPanel({ id: null, name: nextSetName(), questions: [] });
        for (var i = 0, n = questionCount() || 1; i < n; i++) appendCard(panel, {});
        // A brand-new quiz still on the "Create Quiz" screen gets its cards now.
        if (questionCount() > 0) revealQuestionsArea();
        switchSet(panel.dataset.setKey);
    }

    function renameSet(key) {
        var panel = panelByKey(key);
        var input = panel.querySelector('.set-name-input');
        var name = window.prompt('Rename set', input.value);
        if (name === null) return;
        name = name.trim().slice(0, 60);
        if (name === '') return;
        input.value = name;
        refreshBuilder();
    }

    function duplicateSet(key) {
        var source = panelByKey(key);
        var copy = createSetPanel({ id: null, name: nextSetName(), questions: [] });
        panelCards(source).forEach(function (card) { appendCard(copy, readCard(card)); });
        switchSet(copy.dataset.setKey);
    }

    function deleteSet(key) {
        var panels = setPanels();
        if (panels.length <= 1) {
            alert('A quiz must always have at least one set.');
            return;
        }
        var panel = panelByKey(key);
        var name = panel.querySelector('.set-name-input').value;
        if (panel.dataset.used === '1' || panelCards(panel).some(function (card) { return card.dataset.answered === '1'; })) {
            alert('"' + name + '" has already been served to students, so it can\'t be deleted. Its attempt history depends on it.');
            return;
        }
        if (!confirm('Delete "' + name + '" and its ' + panelCards(panel).length + ' questions?')) return;
        var wasActive = panel.dataset.setKey === _activeSetKey;
        panel.remove();
        if (wasActive) switchSet(setPanels()[0].dataset.setKey); else refreshBuilder();
    }

    /** The values of a card, for duplicating a set (ids and answered flags are not copied). */
    function readCard(card) {
        var type = card.querySelector('select[name$="[type]"]').value;
        var checked = card.querySelector(type === 'True or False' ? 'input[name$="[tf_correct]"]:checked' : 'input[name$="[correct]"]:checked');
        return {
            id: null,
            type: type,
            text: card.querySelector('.question-input').value,
            points: card.querySelector('.points').value,
            choices: Array.prototype.map.call(card.querySelectorAll('input[name$="[choices][]"]'), function (input) { return input.value; }),
            correct: checked ? checked.value : null,
            answer: card.querySelector('input[name$="[answer]"]').value,
            lock_choice_order: !!card.querySelector('input[name$="[lock_choice_order]"]:checked'),
            answered: false
        };
    }

    function revealQuestionsArea() {
        var intro = document.getElementById('create-quiz-intro');
        if (intro) intro.style.display = 'none';
        var area = document.getElementById('questions-area');
        if (area) area.style.display = 'block';
        var saveRow = document.getElementById('save-quiz-row');
        if (saveRow) saveRow.style.display = 'flex';
    }

    // ── "Create Quiz": N blank cards in every set, N = "No. of Items" ──
    function createQuizQuestions() {
        var itemsInput = document.getElementById('quiz-items');
        var n = parseInt(itemsInput && itemsInput.value, 10);
        if (!n || n < 1) {
            alert('Please set the "No. of Items" first (at least 1) before creating the quiz.');
            if (itemsInput) itemsInput.focus();
            return;
        }
        if (n > 100) n = 100;
        setPanels().forEach(function (panel) {
            panel.querySelector('.set-cards').innerHTML = '';
            for (var i = 0; i < n; i++) appendCard(panel, {});
        });
        revealQuestionsArea();
        refreshBuilder();
        var first = activePanel() && activePanel().querySelector('.question-input');
        if (first) first.focus();
    }

    // ── "No. of Items" resizes EVERY set: append or remove at the end ──
    function itemsChanged(input) {
        if (document.getElementById('questions-area').style.display === 'none') return;
        var target = parseInt(input.value, 10);
        var current = questionCount();
        if (!target || target < 1) { input.value = current; return; }
        if (target > 100) target = 100;
        if (target === current) return;

        if (target > current) {
            setPanels().forEach(function (panel) {
                for (var i = current; i < target; i++) appendCard(panel, {});
            });
            refreshBuilder();
            return;
        }

        var affected = [];
        var blocked = false;
        setPanels().forEach(function (panel) {
            var removed = panelCards(panel).slice(target);
            if (removed.some(function (card) { return card.dataset.answered === '1'; })) blocked = true;
            var filled = removed.filter(cardIsFilled).length;
            if (filled) affected.push(panel.querySelector('.set-name-input').value + ' (' + filled + ' filled)');
        });
        if (blocked) {
            alert('Some of the last ' + (current - target) + ' questions have already been answered by students, so the quiz can\'t be shortened to ' + target + ' items.');
            input.value = current;
            return;
        }
        if (affected.length && !confirm('Reducing to ' + target + ' items removes the last ' + (current - target) + ' question(s) from every set, including filled-in questions in: ' + affected.join(', ') + '. Continue?')) {
            input.value = current;
            return;
        }
        setPanels().forEach(function (panel) {
            panelCards(panel).slice(target).forEach(function (card) { card.remove(); });
        });
        refreshBuilder();
    }

    // ✅ Swaps the question card's display to match the chosen Type
    function questionTypeChanged(sel) {
        var card = sel.closest('.question-card');
        if (!card) return;
        var t = sel.value;
        renderQuestionType(card, t);

        var selection = card.querySelector('.question-select-item');
        if (selection && selection.checked) {
            panelCards(activePanel()).forEach(function (selectedCard) {
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
        var cards = panelCards(activePanel());
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

        panelCards(activePanel()).forEach(function (selectedCard) {
            if (selectedCard === card) return;
            var selected = selectedCard.querySelector('.question-select-item');
            var points = selectedCard.querySelector('.points');
            if (selected && selected.checked && points) points.value = input.value;
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.id === 'select-all-questions') {
            var shouldSelect = event.target.checked;
            panelCards(activePanel()).forEach(function (card) {
                var checkbox = card.querySelector('.question-select-item');
                if (checkbox) checkbox.checked = shouldSelect;
            });
            updateQuestionSelectionState();
        }
    });

    // Adds a blank card at the end of EVERY set, keeping counts equal.
    function addQuestion() {
        setPanels().forEach(function (panel) { appendCard(panel, {}); });
        refreshBuilder();
        var cards = panelCards(activePanel());
        var first = cards.length && cards[cards.length - 1].querySelector('.question-input');
        if (first) first.focus();
    }

    // Removes this POSITION from every set, after listing what it affects.
    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (!card) return;
        var position = panelCards(card.closest('.quiz-set-panel')).indexOf(card);
        var panels = setPanels();
        if (questionCount() <= 1 && QUIZ_BUILDER_HAS_ATTEMPTS) {
            alert('Students have already taken this quiz, so it needs at least one question.');
            return;
        }
        var lines = [];
        var blocked = [];
        panels.forEach(function (panel) {
            var target = panelCards(panel)[position];
            if (!target) return;
            var name = panel.querySelector('.set-name-input').value;
            var text = target.querySelector('.question-input').value.trim();
            lines.push('• ' + name + ': ' + (text ? '"' + (text.length > 60 ? text.slice(0, 57) + '...' : text) + '"' : '(empty)'));
            if (target.dataset.answered === '1') blocked.push(name);
        });
        if (blocked.length) {
            alert('Question ' + (position + 1) + ' has already been answered by students in ' + blocked.join(', ') + ', so it can\'t be removed. Edit its text instead.');
            return;
        }
        var message = panels.length > 1
            ? 'Remove question ' + (position + 1) + ' from every set? This affects:\n' + lines.join('\n')
            : 'Remove this question?';
        if (!confirm(message)) return;
        panels.forEach(function (panel) {
            var target = panelCards(panel)[position];
            if (target) target.remove();
        });
        refreshBuilder();
    }

    function renumberQuestions() {
        refreshBuilder();
    }

    // Build the sets from the server payload (or the old input after a failed save).
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof QUIZ_BUILDER_SETS === 'undefined' || !document.getElementById('questions')) return;
        var sets = Array.isArray(QUIZ_BUILDER_SETS) && QUIZ_BUILDER_SETS.length ? QUIZ_BUILDER_SETS : [{ id: null, name: 'Set A', questions: [] }];
        sets.forEach(function (set) { createSetPanel(set); });
        switchSet(setPanels()[0].dataset.setKey);
        if (window.matchMedia && window.matchMedia('(max-width: 980px)').matches && setPanels().length === 1) toggleSetsDrawer(false);
    });
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
