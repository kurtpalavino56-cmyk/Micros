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
                    <button class="btn-primary-action" type="button" aria-haspopup="dialog" aria-controls="course-approval-dialog" onclick="document.getElementById('course-approval-dialog').showModal()" @disabled(!$canSubmitForApproval)>Submit for approval</button>
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
        @if($course->approval_status === 'draft')
            <dialog class="course-approval-dialog" id="course-approval-dialog" aria-labelledby="course-approval-title">
                <form method="POST" action="{{ route('faculty.courses.submit-for-approval', $course->id) }}">
                    @csrf
                    <div class="course-approval-dialog__body">
                        <h2 id="course-approval-title">Submit course for approval</h2>
                        <p>Include a note for the administrator reviewing this course. This is optional.</p>
                        <label for="course-change-note">Review notes</label>
                        <textarea class="textarea-outline" id="course-change-note" name="change_note" rows="4" maxlength="2000" placeholder="e.g. Rewrote Module 2 lessons and lowered the passing score to 70%.">{{ old('change_note') }}</textarea>
                        @error('change_note')<small class="course-approval-dialog__error" role="alert">{{ $message }}</small>@enderror
                        <div class="course-approval-dialog__actions">
                            <button class="btn-cancel-outline" type="button" onclick="this.closest('dialog').close()">Cancel</button>
                            <button class="btn-primary-action" type="submit">Submit for approval</button>
                        </div>
                    </div>
                </form>
            </dialog>
            @if($errors->has('change_note'))
                <script>document.getElementById('course-approval-dialog').showModal();</script>
            @endif
        @endif
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
                            @include('components.rich-text-editor', ['name' => 'lesson_content', 'id' => 'lesson-content-add-'.$module->idx, 'value' => old('lesson_content'), 'placeholder' => 'Write the lesson content...', 'preset' => 'content-table'])
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
                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'module-activity-instructions-add-'.$module->idx, 'value' => old('instructions'), 'placeholder' => 'Instructions and criteria', 'preset' => 'content-table'])
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
                                            <span class="module-content-meta">{{ $item->questions_count }} questions · Pass {{ $item->passing_score }}%</span>
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
                                            @include('components.rich-text-editor', ['name' => 'lesson_content', 'id' => 'lesson-content-edit-'.$item->id, 'value' => old('lesson_content', $item->content), 'placeholder' => 'Write the lesson content...', 'preset' => 'content-table'])
                                            @if ($errors->has('lesson_content') && (int) session('open_lesson_edit') === (int) $item->id)<div class="fac-course-inline-error">{{ $errors->first('lesson_content') }}</div>@endif
                                            <div class="actions"><input class="input-outline" type="number" name="duration" placeholder="Duration ( Min )" min="0" value="{{ old('duration', $item->duration_raw ?: '') }}"><button class="btn-save-lesson" type="submit">Save Lesson</button><button class="btn-cancel-outline" type="button" onclick="toggleLessonEdit({{ $item->id }})">Cancel</button></div>
                                        </form>
                                    @elseif($moduleItem->type === 'activity')
                                        <form class="activity-form activity-edit-form module-content-edit-form" id="activity-edit-{{ $item->id }}" hidden method="POST" action="{{ route('faculty.lesson-activity.update', [$course->id, $item->id]) }}">@csrf
                                            <input name="title" maxlength="255" value="{{ $item->title }}" placeholder="Activity title" required>
                                            <select name="activity_type">@foreach(['practice'=>'Practice','reflection'=>'Reflection','assignment'=>'Assignment (faculty reviewed)'] as $type=>$label)<option value="{{ $type }}" @selected($item->activity_type === $type)>{{ $label }}</option>@endforeach</select>
                                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'module-activity-instructions-edit-'.$item->id, 'value' => $item->instructions, 'placeholder' => 'Instructions and criteria', 'preset' => 'content-table'])
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
                                    <div class="sub">{{ $module->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $module->quiz->questions_count) }} · Pass {{ $module->quiz->passing_score }}%</div>
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
                                    <div class="fac-final-exam-meta">{{ $finalModule->quiz->title }} · {{ $finalModule->quiz->questions_count }} {{ Illuminate\Support\Str::plural('question', $finalModule->quiz->questions_count) }} · Pass {{ $finalModule->quiz->passing_score }}% · {{ $finalModule->quiz->attempts ?: 'Unlimited' }} attempts</div>
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
        <div class="faculty-page-heading"><h2 class="quiz-head faculty-page-title">@if($mode === 'final-exam'){{ ($quiz ?? null) ? 'Edit' : 'Create' }} Final Exam: {{ $course->title }}@elseif(!empty($lessonId)){{ ($quiz ?? null) ? 'Edit' : 'Add' }} Lesson Quiz: {{ $moduleTitle }}@else{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz: {{ $moduleTitle }}@endif</h2></div>
        <x-flash-toast :message="session('success')" />
        {{-- âœ… Real form â€” Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form id="quiz-builder-form" method="POST" novalidate action="{{ $mode === 'final-exam' ? route('faculty.final-exam.update', [$course->id, $moduleIdx]) : ($mode === 'module-quiz' ? (!empty($moduleQuizId) ? route('faculty.module-quiz.update', [$course->id, $moduleIdx, $moduleQuizId]) : route('faculty.module-quiz.store', [$course->id, $moduleIdx])) : (!empty($lessonId) ? route('faculty.lesson-quiz.store', [$course->id, $lessonId]) : route('faculty.quiz.store', [$course->id, $moduleIdx]))) }}">
            @csrf
            @if(request()->boolean('inline'))<input type="hidden" name="inline" value="1">@endif

            @php
                $quizSets = collect($quiz->sets ?? [])->sortBy([['position', 'asc'], ['id', 'asc']])->values();
                $knownQuestions = $quizSets->flatMap(fn ($set) => $set['questions'] ?? [])->keyBy(fn ($question) => (string) ($question['id'] ?? ''));
                $oldSets = old('sets');
                $primarySet = $quizSets->first();
                $builderSet = [
                    'id' => $primarySet['id'] ?? null,
                    'name' => $primarySet['name'] ?? 'Set A',
                    'used' => (bool) ($primarySet['used'] ?? false),
                    'questions' => [],
                ];
                if (is_array($oldSets) && $oldSets !== []) {
                    $oldSets = array_values($oldSets);
                    $firstOldSet = (array) ($oldSets[0] ?? []);
                    $setId = isset($firstOldSet['id']) && $firstOldSet['id'] !== '' ? (int) $firstOldSet['id'] : $builderSet['id'];
                    $knownSet = $quizSets->firstWhere('id', $setId);
                    $builderSet['id'] = $setId;
                    $builderSet['name'] = $firstOldSet['name'] ?? ($knownSet['name'] ?? $builderSet['name']);
                    $builderSet['used'] = (bool) ($knownSet['used'] ?? $builderSet['used']);
                    $builderSet['questions'] = collect($oldSets)
                        ->flatMap(fn ($oldSet) => array_values((array) (((array) $oldSet)['questions'] ?? [])))
                        ->values()
                        ->map(function ($question) use ($knownQuestions) {
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
                        })->all();
                } elseif ($quizSets->isNotEmpty()) {
                    $builderSet['questions'] = $quizSets
                        ->flatMap(fn ($set) => $set['questions'] ?? [])
                        ->values()
                        ->all();
                }
                $builderSets = [$builderSet];
                $isNewQuiz = empty($quiz->id);
                $quizHasAttempts = (bool) ($quiz->has_attempts ?? false);
                $questionCount = collect($builderSet['questions'])
                    ->filter(fn ($question) => trim((string) ($question['text'] ?? '')) !== '')
                    ->count();
                $firstErrorKey = $errors->keys()[0] ?? '';
                $initialQuizTab = in_array($firstErrorKey, ['quiz_title', 'passing_score', 'instructions', 'attempts', 'time_limit', 'questions_per_attempt'], true)
                    ? 'settings'
                    : 'questions';
            @endphp

            <style>
                .quiz-builder-tabs { display: flex; gap: 8px; border-bottom: 1px solid #dfe5ef; margin: 0 0 18px; }
                .quiz-builder-tab { border: 0; border-bottom: 3px solid transparent; background: transparent; padding: 10px 14px; color: #526078; font: inherit; font-weight: 700; cursor: pointer; }
                .quiz-builder-tab[aria-selected="true"] { border-color: #123b7a; color: #123b7a; }
                .quiz-builder-panel[hidden], .quiz-set-panel[hidden] { display: none; }
                .quiz-builder-notice { margin: 0 0 16px; padding: 12px 14px; border-left: 4px solid #dba617; background: #fff7dc; border-radius: 8px; color: #4a3b00; font-size: .9rem; }
                .quiz-builder-errors { margin: 0 0 16px; padding: 12px 14px; border-left: 4px solid #b42318; background: #fee4e2; border-radius: 8px; color: #7a1a12; }
                .quiz-builder-errors ul { margin: 6px 0 0; padding-left: 18px; }
                .quiz-question-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
                .quiz-add-menu { position: relative; }
                .quiz-add-menu summary { list-style: none; cursor: pointer; }
                .quiz-add-menu summary::-webkit-details-marker { display: none; }
                .quiz-add-menu__items { position: absolute; z-index: 20; top: calc(100% + 6px); right: 0; display: grid; min-width: 190px; padding: 6px; border: 1px solid #dfe5ef; border-radius: 8px; background: #fff; box-shadow: 0 8px 24px rgba(20, 35, 60, .12); }
                .quiz-add-menu__items button { border: 0; background: transparent; padding: 9px 10px; text-align: left; cursor: pointer; color: #182b4d; font: inherit; }
                .quiz-add-menu__items button:hover { background: #f3f6fc; }
                .quiz-empty-state { padding: 24px; border: 1px dashed #c8d2e1; border-radius: 10px; color: #526078; text-align: center; }
                .quiz-shuffle-options { display: grid; gap: 10px; margin: 14px 0; padding-top: 14px; border-top: 1px solid #e5eaf1; }
                .quiz-shuffle-options label { display: flex; gap: 10px; align-items: flex-start; cursor: pointer; }
                .quiz-field-label-row { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
                .quiz-field-label-row .q-label { margin: 0; }
                .quiz-help-icon { position: relative; display: grid; width: 18px; height: 18px; flex: 0 0 18px; place-items: center; padding: 0; border: 1px solid #123b7a; border-radius: 50%; background: #fff; color: #123b7a; font: 700 12px/1 Inter, sans-serif; cursor: help; }
                .quiz-help-tooltip { display: none; position: absolute; z-index: 30; top: calc(100% + 8px); left: 0; width: max-content; max-width: min(300px, 80vw); padding: 9px 11px; border-radius: 7px; background: #182b4d; box-shadow: 0 6px 18px rgba(16, 24, 40, .18); color: #fff; font: 400 12px/1.45 Inter, sans-serif; text-align: left; white-space: normal; }
                .quiz-help-icon--align-right .quiz-help-tooltip { right: 0; left: auto; }
                .quiz-help-icon:hover .quiz-help-tooltip, .quiz-help-icon:focus .quiz-help-tooltip { display: block; }
                .quiz-shuffle-setting { display: flex; align-items: flex-start; gap: 4px; }
                .lock-choice-order { display: flex; gap: 8px; align-items: flex-start; margin-top: 10px; font-size: .88rem; color: #182b4d; cursor: pointer; }
                .lock-choice-order small { display: block; color: #526078; }
                .question-answered-note { margin-top: 10px; color: #8a5a00; font-size: .8rem; }
            </style>

            @if ($errors->any())
                <div class="quiz-builder-errors" role="alert">
                    <strong>The quiz was not saved.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if ($isNewQuiz)
                <div class="quiz-card">
                    <div>
                        <label class="q-label" for="quiz-title">Quiz title <span class="req">*</span></label>
                        <input class="input-outline fac-width-full" id="quiz-title" type="text" name="quiz_title" placeholder="MySQL Fundamentals Quiz" value="{{ old('quiz_title', $quiz->title ?? '') }}" required>
                        @error('quiz_title')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                    </div>
                    <div style="margin-top:14px;">
                        <label class="q-label" for="passing-score">Passing score (%)</label>
                        <input class="input-outline fac-width-full" id="passing-score" type="number" name="passing_score" min="0" max="100" value="{{ old('passing_score', $quiz->passing_score ?? 75) }}">
                        @error('passing_score')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                    </div>
                    <div style="margin-top:14px;">
                        <label class="q-label">Instructions (optional)</label>
                        @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'assessment-instructions', 'value' => old('instructions', $quiz->instructions ?? ''), 'placeholder' => 'Add instructions for students...'])
                    </div>
                </div>
                <div class="save-quiz-row">
                    <button class="btn-save-lesson" type="submit">{{ $mode === 'final-exam' ? 'Create Final Exam' : (!empty($lessonId) ? 'Create lesson quiz' : 'Create quiz') }}</button>
                </div>
            @else
                <div class="quiz-builder-tabs" role="tablist" aria-label="Quiz editor tabs">
                    <button class="quiz-builder-tab" id="quiz-tab-questions" type="button" role="tab" aria-controls="quiz-panel-questions" aria-selected="{{ $initialQuizTab === 'questions' ? 'true' : 'false' }}" onclick="switchQuizTab('questions')">Questions</button>
                    <button class="quiz-builder-tab" id="quiz-tab-settings" type="button" role="tab" aria-controls="quiz-panel-settings" aria-selected="{{ $initialQuizTab === 'settings' ? 'true' : 'false' }}" onclick="switchQuizTab('settings')">Settings</button>
                </div>

                <section class="quiz-builder-panel" id="quiz-panel-questions" role="tabpanel" aria-labelledby="quiz-tab-questions" data-quiz-tab-panel="questions" @if($initialQuizTab !== 'questions') hidden @endif>
                    <div class="quiz-builder-notice" role="note">
                        Editing question content may reset learners' attempt counting. Changes to quiz settings do not.
                    </div>
                    @if ($quizHasAttempts)
                        <div class="quiz-builder-notice" role="note">
                            Students have already taken this quiz. Questions they answered cannot be removed.
                        </div>
                    @endif

                    <div class="quiz-bank-toolbar">
                        <h3>Questions in pool: <span id="question-bank-count">{{ $questionCount }}</span></h3>
                        <div class="quiz-question-actions">
                            <button type="button" id="toggle-question-cards" aria-expanded="false" onclick="toggleAllQuestionCards(this)">Expand All</button>
                            <details class="quiz-add-menu">
                                <summary class="btn-save-lesson">Add</summary>
                                <div class="quiz-add-menu__items">
                                    <button type="button" onclick="addQuestion()">New question (manual)</button>
                                    <button type="submit" name="continue_to_import" value="1" data-csv-submit>Upload file</button>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="question-bulk-tools" id="question-bulk-tools">
                        <label for="select-all-questions">
                            <input class="question-select" id="select-all-questions" type="checkbox" aria-label="Select all questions in the quiz">
                            <span>Select all questions in the quiz</span>
                        </label>
                        <span class="question-bulk-status" id="question-bulk-status" aria-live="polite">Select questions to change their type or points together.</span>
                    </div>
                    <div class="quiz-empty-state" id="quiz-empty-state" @if($questionCount > 0) hidden @endif>No questions yet. Use Add to create a question manually or upload a CSV.</div>
                    <div id="questions"></div>
                </section>

                <section class="quiz-builder-panel" id="quiz-panel-settings" role="tabpanel" aria-labelledby="quiz-tab-settings" data-quiz-tab-panel="settings" @if($initialQuizTab !== 'settings') hidden @endif>
                    <div class="quiz-card">
                        <div>
                            <label class="q-label" for="quiz-title">Quiz title <span class="req">*</span></label>
                            <input class="input-outline fac-width-full" id="quiz-title" type="text" name="quiz_title" placeholder="MySQL Fundamentals Quiz" value="{{ old('quiz_title', $quiz->title ?? '') }}" required>
                            @error('quiz_title')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                        </div>
                        <div class="grid3" style="margin-top:14px;">
                            <div>
                                <label class="q-label" for="passing-score">Passing score (%)</label>
                                <input class="input-outline fac-width-full" id="passing-score" type="number" name="passing_score" min="0" max="100" value="{{ old('passing_score', $quiz->passing_score ?? 75) }}">
                                @error('passing_score')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                            </div>
                            <div>
                                <div class="quiz-field-label-row">
                                    <label class="q-label" for="questions-per-attempt">Questions shown per attempt (blank = all)</label>
                                    <button class="quiz-help-icon" type="button" aria-label="Help: questions shown per attempt" aria-describedby="questions-per-attempt-help">?<span class="quiz-help-tooltip" id="questions-per-attempt-help" role="tooltip">Questions in pool: <span id="question-pool-size">{{ $questionCount }}</span>. Add more questions than you show, so learners get different sets.</span></button>
                                </div>
                                <input class="input-outline fac-width-full" type="number" name="questions_per_attempt" id="questions-per-attempt" min="1" value="{{ old('questions_per_attempt', $quiz->questions_per_attempt ?? '') }}" oninput="updateDrawSetting()">
                                <small class="field-help" id="draw-setting-warning" role="status" hidden></small>
                                @error('questions_per_attempt')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                            </div>
                            <div>
                                <div class="quiz-field-label-row">
                                    <label class="q-label" for="attempts">Attempts allowed</label>
                                    <button class="quiz-help-icon quiz-help-icon--align-right" type="button" aria-label="Help: attempts allowed" aria-describedby="attempts-help">?<span class="quiz-help-tooltip" id="attempts-help" role="tooltip">Leave blank for unlimited attempts. Enter a number to set a limit.</span></button>
                                </div>
                                <input class="input-outline fac-width-full" id="attempts" type="text" name="attempts" placeholder="Unlimited" value="{{ old('attempts', $quiz->attempts ?? '') }}">
                            </div>
                        </div>
                        <div class="grid2">
                            <div>
                                <div class="quiz-field-label-row">
                                    <label class="q-label" for="time-limit">Time limit (min)</label>
                                    <button class="quiz-help-icon" type="button" aria-label="Help: time limit" aria-describedby="time-limit-help">?<span class="quiz-help-tooltip" id="time-limit-help" role="tooltip">Leave blank to allow unlimited time.</span></button>
                                </div>
                                <input class="input-outline fac-width-full" id="time-limit" type="number" name="time_limit" placeholder="No time limit" min="1" value="{{ old('time_limit', ($quiz->time_limit ?? 0) > 0 ? $quiz->time_limit : '') }}">
                                @error('time_limit')<small class="field-help" role="alert">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="quiz-shuffle-options">
                            <div class="quiz-shuffle-setting">
                                <label>
                                    <input type="checkbox" name="shuffle_questions" value="1" @checked(old('_token') ? old('shuffle_questions') : ($quiz->shuffle_questions ?? false))>
                                    <span><strong>Shuffle question order</strong></span>
                                </label>
                                <button class="quiz-help-icon" type="button" aria-label="Help: shuffle question order" aria-describedby="shuffle-questions-help">?<span class="quiz-help-tooltip" id="shuffle-questions-help" role="tooltip">Questions from this pool appear in a different order on each attempt.</span></button>
                            </div>
                            <div class="quiz-shuffle-setting">
                                <label>
                                    <input type="checkbox" name="shuffle_choices" value="1" @checked(old('_token') ? old('shuffle_choices') : ($quiz->shuffle_choices ?? false))>
                                    <span><strong>Shuffle choices order</strong></span>
                                </label>
                                <button class="quiz-help-icon" type="button" aria-label="Help: shuffle choices order" aria-describedby="shuffle-choices-help">?<span class="quiz-help-tooltip" id="shuffle-choices-help" role="tooltip">Multiple-choice options appear in a different order per attempt. True or False and locked questions keep their order.</span></button>
                            </div>
                        </div>
                        <div>
                            <label class="q-label">Instructions (optional)</label>
                            @include('components.rich-text-editor', ['name' => 'instructions', 'id' => 'assessment-instructions', 'value' => old('instructions', $quiz->instructions ?? ''), 'placeholder' => 'Add instructions for students...'])
                        </div>
                    </div>
                </section>

                <div class="save-quiz-row" id="save-quiz-row">
                    <button class="btn-save-lesson" type="submit">{{ $mode === 'final-exam' ? 'Save Final Exam' : 'Save Quiz' }}</button>
                </div>
            @endif
            <script>
                var QUIZ_BUILDER_SETS = @json($builderSets);
                var QUIZ_BUILDER_HAS_ATTEMPTS = @json($quizHasAttempts);
                var QUIZ_BUILDER_INITIAL_TAB = @json($initialQuizTab);
                var QUIZ_BUILDER_ERRORS = @json($errors->keys());
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

        moduleCard.addEventListener('click', function (event) {
            if (event.target.closest('button, a, input, select, textarea, form, [contenteditable="true"]')) return;
            if (!moduleBody.hidden && !event.target.closest('.module-head')) return;
            toggleModuleCollapse(moduleCard.dataset.orderId, moduleToggle);
        });

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

    /* ── Quiz question bank ─────────────────────────────────────────────── */
    var _questionKey = 0;

    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function setPanels() {
        return Array.prototype.slice.call(document.querySelectorAll('#questions .quiz-set-panel'));
    }

    function activePanel() {
        return setPanels()[0] || null;
    }

    function panelCards(panel) {
        return panel ? Array.prototype.slice.call(panel.querySelectorAll('.question-card')) : [];
    }

    function questionCount() {
        return panelCards(activePanel()).length;
    }

    function filledQuestionCount() {
        return panelCards(activePanel()).filter(cardIsFilled).length;
    }

    function cardIsFilled(card) {
        var text = card.querySelector('.question-input');
        return !!(text && text.value.trim() !== '');
    }

    function createSetPanel(set) {
        var s = 0;
        var panel = document.createElement('div');
        panel.className = 'quiz-set-panel';
        panel.dataset.setKey = String(s);
        panel.innerHTML =
            '<input type="hidden" name="sets[' + s + '][id]" value="' + esc(set.id || '') + '">' +
            '<input type="hidden" class="set-name-input" name="sets[' + s + '][name]" value="' + esc(set.name || 'Set A') + '">' +
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
        var alternativeAnswersHtml = (question.alternative_answers || []).map(function (answer) {
            return alternativeAnswerRowHtml(prefix, answer);
        }).join('');
        var tfHtml = '';
        ['True', 'False'].forEach(function (label, i) {
            tfHtml += '<div class="choice-row"><span class="tf-label">' + label + '</span>' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="' + prefix + '[tf_correct]" value="' + i + '"' + (type === 'True or False' && correct === i ? ' checked' : '') + '>' +
                '<span class="radio-dot"></span></label></div>';
        });

        var card = document.createElement('div');
        card.className = 'quiz-card question-card';
        card.dataset.collapsed = 'true';
        card.dataset.answered = question.answered ? '1' : '0';
        card.innerHTML =
            '<input type="hidden" name="' + prefix + '[id]" value="' + esc(question.id || '') + '">' +
            '<div class="question-card-summary-row">' +
                '<input class="question-select question-select-item" type="checkbox" aria-label="Select question" onchange="updateQuestionSelectionState()">' +
                '<button class="question-card-summary" type="button" aria-expanded="false" aria-controls="question-body-' + q + '" onclick="toggleQuestionCard(this)">' +
                    '<span class="question-card-summary__copy">' +
                        '<span class="question-card-summary__text" data-question-summary-text>' + esc(question.text || 'Question text needed') + '</span>' +
                        '<span class="question-card-summary__meta"><span data-question-summary-type>' + esc(type) + '</span><span data-question-summary-points>' + esc(question.points || 1) + ' points</span></span>' +
                    '</span>' +
                '</button>' +
                '<div class="question-card-actions">' +
                    '<button class="btn-x" type="button" aria-label="Delete question" title="Delete question" onclick="removeQuestion(this)">&times;</button>' +
                    '<button class="question-card-toggle" type="button" aria-label="Expand question" title="Expand question" aria-expanded="false" aria-controls="question-body-' + q + '" onclick="toggleQuestionCard(this)">' +
                        '<svg aria-hidden="true" viewBox="0 0 20 20" fill="none"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="question-card-body" id="question-body-' + q + '" hidden>' +
                '<div class="q-head">' +
                    '<h3>Question ?</h3>' +
                    '<span class="type-label">Type</span>' +
                    '<div class="sel-wrap"><select name="' + prefix + '[type]" onchange="questionTypeChanged(this)">' +
                        ['Multiple Choice', 'True or False', 'Identification'].map(function (option) {
                            return '<option value="' + option + '"' + (option === type ? ' selected' : '') + '>' + option + '</option>';
                        }).join('') +
                    '</select></div>' +
                    '<input class="input-outline points" type="number" name="' + prefix + '[points]" placeholder="Points" min="0" value="' + esc(question.points === 0 ? 0 : (question.points || 1)) + '" oninput="syncSelectedQuestionPoints(this); refreshQuestionSummary(this)">' +
                    '<small class="field-help">0 or blank counts as 1</small>' +
                '</div>' +
                '<div class="fac-question-field-spacing">' +
                    '<input class="input-outline question-input fac-width-full" type="text" name="' + prefix + '[text]" placeholder="Enter your question" value="' + esc(question.text || '') + '" oninput="refreshQuestionSummary(this)">' +
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
                    '<div class="alternative-answers">' +
                        '<label class="q-label">Alternative answers <span>(optional)</span></label>' +
                        '<div class="alternative-answer-list" data-prefix="' + prefix + '">' + alternativeAnswersHtml + '</div>' +
                        '<button class="btn-add-choice" type="button" onclick="addAlternativeAnswer(this)">+ Add alternative answer</button>' +
                    '</div>' +
                '</div>' +
                (question.answered ? '<div class="question-answered-note">Students have answered this question, so it can\'t be removed.</div>' : '') +
            '</div>';
        panel.querySelector('.set-cards').appendChild(card);
        return card;
    }

    function refreshQuestionSummary(element) {
        var card = element.closest('.question-card');
        if (!card) return;
        var question = card.querySelector('.question-input');
        var type = card.querySelector('select[name$="[type]"]');
        var points = card.querySelector('.points');
        card.querySelector('[data-question-summary-text]').textContent = question && question.value.trim() ? question.value.trim() : 'Question text needed';
        card.querySelector('[data-question-summary-type]').textContent = type ? type.value : 'Multiple Choice';
        card.querySelector('[data-question-summary-points]').textContent = (points && points.value ? points.value : '1') + ' points';
    }

    function toggleQuestionCard(button, expanded) {
        var isExpanded = typeof expanded === 'boolean' ? expanded : button.getAttribute('aria-expanded') !== 'true';
        var summary = button.closest('.question-card').querySelector('.question-card-summary');
        var toggle = button.closest('.question-card').querySelector('.question-card-toggle');
        var body = document.getElementById(button.getAttribute('aria-controls'));
        if (!body) return;
        button.closest('.question-card').dataset.collapsed = String(!isExpanded);
        body.hidden = !isExpanded;
        summary.setAttribute('aria-expanded', String(isExpanded));
        toggle.setAttribute('aria-expanded', String(isExpanded));
        toggle.setAttribute('aria-label', isExpanded ? 'Collapse question' : 'Expand question');
        toggle.setAttribute('title', isExpanded ? 'Collapse question' : 'Expand question');
    }

    function toggleAllQuestionCards(button) {
        var cards = panelCards(activePanel());
        var shouldExpand = cards.some(function (card) {
            return card.querySelector('.question-card-toggle').getAttribute('aria-expanded') !== 'true';
        });
        cards.forEach(function (card) {
            toggleQuestionCard(card.querySelector('.question-card-toggle'), shouldExpand);
        });
        button.setAttribute('aria-expanded', String(shouldExpand));
        button.textContent = shouldExpand ? 'Collapse All' : 'Expand All';
    }

    function addChoice(button) {
        var box = button.closest('.q-mc').querySelector('.choices');
        var i = box.children.length;
        if (i >= 26) return;
        box.insertAdjacentHTML('beforeend', choiceRowHtml(box.dataset.prefix, i, '', false));
    }

    function refreshBuilder() {
        var panels = setPanels();
        panels.forEach(function (panel) {
            panel.querySelector('.set-position-input').value = 0;
            panelCards(panel).forEach(function (card, index) {
                var heading = card.querySelector('.q-head h3');
                if (heading) heading.textContent = 'Question ' + (index + 1);
                refreshQuestionSummary(card.querySelector('.question-input'));
            });
        });

        var count = filledQuestionCount();
        var countOutput = document.getElementById('question-bank-count');
        if (countOutput) countOutput.textContent = count;
        var emptyState = document.getElementById('quiz-empty-state');
        if (emptyState) emptyState.hidden = panels.some(function (panel) { return panelCards(panel).length > 0; });

        updateDrawSetting();
        updateQuestionSelectionState();
    }

    function updateDrawSetting() {
        var field = document.getElementById('questions-per-attempt');
        var size = document.getElementById('question-pool-size');
        var warning = document.getElementById('draw-setting-warning');
        if (!field || !size || !warning) return;
        var poolSize = filledQuestionCount();
        size.textContent = String(poolSize);
        var requested = Number(field.value);
        var message = '';
        if (field.value.trim() !== '' && (!Number.isInteger(requested) || requested < 1 || requested > poolSize)) {
            message = 'Questions shown per attempt must be from 1 to ' + poolSize + '.';
        }
        warning.textContent = message;
        warning.hidden = message === '';
        field.setCustomValidity(message);
    }

    function switchQuizTab(name) {
        document.querySelectorAll('[data-quiz-tab-panel]').forEach(function (panel) {
            panel.hidden = panel.dataset.quizTabPanel !== name;
        });
        document.querySelectorAll('.quiz-builder-tab').forEach(function (tab) {
            var selected = tab.id === 'quiz-tab-' + name;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
    }

    function readQuestionCard(card) {
        var correct = card.querySelector('input[type="radio"][name$="[correct]"]:checked');
        var tfCorrect = card.querySelector('input[type="radio"][name$="[tf_correct]"]:checked');
        return {
            text: card.querySelector('.question-input').value,
            type: card.querySelector('select[name$="[type]"]').value,
            points: card.querySelector('.points').value,
            choices: Array.from(card.querySelectorAll('.q-mc .choices input[type="text"]')).map(function (input) { return input.value; }),
            correct: tfCorrect ? tfCorrect.value : (correct ? correct.value : null),
            answer: card.querySelector('.fac-identification-answer').value,
            alternative_answers: Array.from(card.querySelectorAll('.alternative-answer-input')).map(function (input) { return input.value; }),
            lock_choice_order: card.querySelector('.lock-choice-order input').checked
        };
    }

    function alternativeAnswerRowHtml(prefix, value) {
        return '<div class="alternative-answer-row">' +
            '<input class="input-outline alternative-answer-input" type="text" name="' + prefix + '[alternative_answers][]" placeholder="Alternative answer" value="' + esc(value || '') + '">' +
            '<button class="btn-remove-alternative" type="button" aria-label="Remove alternative answer" onclick="removeAlternativeAnswer(this)">Remove</button>' +
            '</div>';
    }

    function addAlternativeAnswer(button) {
        var list = button.closest('.q-id').querySelector('.alternative-answer-list');
        list.insertAdjacentHTML('beforeend', alternativeAnswerRowHtml(list.dataset.prefix, ''));
        list.lastElementChild.querySelector('input').focus();
    }

    function removeAlternativeAnswer(button) {
        button.closest('.alternative-answer-row').remove();
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
                refreshQuestionSummary(selectedCard.querySelector('.question-input'));
            });
        }
        refreshQuestionSummary(card.querySelector('.question-input'));
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
            if (selected && selected.checked && points) {
                points.value = input.value;
                refreshQuestionSummary(points);
            }
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

    document.addEventListener('input', function (event) {
        if (event.target.matches('#questions .question-input')) refreshBuilder();
    });

    // Adds a blank card to the question bank.
    function addQuestion() {
        switchQuizTab('questions');
        if (activePanel()) appendCard(activePanel(), {});
        refreshBuilder();
        var cards = panelCards(activePanel());
        var card = cards.length ? cards[cards.length - 1] : null;
        if (card) {
            toggleQuestionCard(card.querySelector('.question-card-toggle'), true);
            var first = card.querySelector('.question-input');
            if (first) first.focus();
        }
    }

    // Remove a question only when it has not been answered by a student.
    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (!card) return;
        if (questionCount() <= 1 && QUIZ_BUILDER_HAS_ATTEMPTS) {
            alert('Students have already taken this quiz, so it needs at least one question.');
            return;
        }
        if (card.dataset.answered === '1') {
            alert('Students have answered this question, so it can\'t be removed. Edit its text instead.');
            return;
        }
        if (!confirm('Remove this question?')) return;
        card.remove();
        refreshBuilder();
    }

    function renumberQuestions() {
        refreshBuilder();
    }

    function activateQuizField(field) {
        var panel = field.closest('[data-quiz-tab-panel]');
        if (panel) switchQuizTab(panel.dataset.quizTabPanel);

        var card = field.closest('.question-card');
        if (card) {
            var toggle = card.querySelector('.question-card-toggle');
            if (toggle) toggleQuestionCard(toggle, true);
        }
    }

    function syncQuizRichText(form) {
        form.querySelectorAll('[data-ckeditor]').forEach(function (editorElement) {
            var editor = window.upskillEditorInstances && window.upskillEditorInstances.get(editorElement);
            if (editor) editorElement.value = editor.getData();
        });
    }

    function quizFormSnapshot(form) {
        syncQuizRichText(form);
        var values = [];
        new FormData(form).forEach(function (value, key) {
            values.push([key, value instanceof File ? value.name : value]);
        });
        return JSON.stringify(values);
    }

    function focusFirstServerError(form) {
        var errors = typeof QUIZ_BUILDER_ERRORS === 'undefined' ? [] : QUIZ_BUILDER_ERRORS;
        if (!errors.length) return;

        switchQuizTab(QUIZ_BUILDER_INITIAL_TAB || 'questions');
        var firstError = errors[0];
        var setQuestion = /^sets\.(\d+)\.questions\.(\d+)/.exec(firstError);
        if (setQuestion) {
            var card = panelCards(activePanel())[Number(setQuestion[2])];
            if (card) {
                var toggle = card.querySelector('.question-card-toggle');
                if (toggle) toggleQuestionCard(toggle, true);
                var input = card.querySelector('.question-input');
                if (input) input.focus();
            }
            return;
        }

        var field = form.querySelector('[name="' + firstError + '"]');
        if (field) {
            activateQuizField(field);
            field.focus();
        } else if (firstError === 'sets') {
            switchQuizTab('questions');
            var firstQuestion = activePanel() && activePanel().querySelector('.question-input');
            if (firstQuestion) firstQuestion.focus();
        }
    }

    // Keep the legacy nested request shape while showing one question pool.
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof QUIZ_BUILDER_SETS === 'undefined') return;
        var form = document.getElementById('quiz-builder-form');
        if (!form) return;
        if (document.getElementById('questions')) {
            var sets = Array.isArray(QUIZ_BUILDER_SETS) && QUIZ_BUILDER_SETS.length
                ? QUIZ_BUILDER_SETS
                : [{ id: null, name: 'Set A', questions: [] }];
            createSetPanel(sets[0]);
            switchQuizTab(QUIZ_BUILDER_INITIAL_TAB || 'questions');
            refreshBuilder();
        }

        focusFirstServerError(form);
        var initialSnapshot = quizFormSnapshot(form);
        var allowNavigation = false;
        var lastSubmitter = null;
        var firstInvalidField = null;
        form.addEventListener('invalid', function (event) {
            if (firstInvalidField) return;
            var field = event.target;
            firstInvalidField = field;
            activateQuizField(field);
            window.requestAnimationFrame(function () {
                field.focus();
            });
            window.setTimeout(function () {
                if (firstInvalidField === field) firstInvalidField = null;
            }, 0);
        }, true);
        form.addEventListener('click', function (event) {
            var button = event.target.closest('button[type="submit"]');
            if (button) lastSubmitter = button;
        });
        form.addEventListener('submit', function (event) {
            var submitter = event.submitter || lastSubmitter;
            syncQuizRichText(form);
            if (submitter && submitter.hasAttribute('data-csv-submit')
                && quizFormSnapshot(form) !== initialSnapshot
                && !confirm('You have unsaved changes. Save them before continuing to CSV import?')) {
                event.preventDefault();
                return;
            }

            updateDrawSetting();
            var invalidField = Array.from(form.elements).find(function (field) {
                return field.willValidate && !field.checkValidity();
            });
            if (invalidField) {
                event.preventDefault();
                activateQuizField(invalidField);
                window.requestAnimationFrame(function () {
                    invalidField.focus();
                    invalidField.reportValidity();
                });
                return;
            }
            allowNavigation = true;
        });
        window.addEventListener('beforeunload', function (event) {
            if (allowNavigation || quizFormSnapshot(form) === initialSnapshot) return;
            event.preventDefault();
            event.returnValue = '';
        });
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
