{{--
    resources/views/faculty/Create_Courses.blade.php

    Faculty > Create / Edit Course. Single self-contained Blade view.

    Links back to the Faculty Dashboard (route 'faculty.dashboard') and
    Faculty My Courses (route 'faculty.courses'). Saving POSTs to
    'faculty.create.store' (or PATCHes 'faculty.courses.update' when editing).

    Expected data from the route:

    return view('faculty.courses.create', [
        'user'                  => $user,
        'categories'            => ['Web Development', ...],
        'levels'                => ['Beginner', 'Intermediate', 'Advanced'],
        'skillGroups'           => ['Field' => ['Skill', ...], ...],
        'skillOptions'          => [...],
        'competencyUnitOptions' => $units,   // ->id, ->title
        'prereqOptions'         => $courses, // ->id, ->title, ->category
        'editing'               => false,
        'course'                => null,
    ]);

    Section order: Overview > Image > Skills & recommendations >
    Learning design > Prerequisites > Credential & completion >
    Review notes (edit only).
--}}
<x-faculty.layout title="{{ ($editing ?? false) ? 'Edit Course' : 'Create Course' }} | Upskill" data-faculty-page="faculty-create" use-app-css use-app-js>
    <x-slot:head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">





<link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>



    {{-- Sidebar: Dashboard & My Courses are linked; Create Courses is this page --}}


    <main class="main">
    <div class="build-shell">
        <div class="build-form">

            @php $isEdit = ($editing ?? false) && ($course ?? null); @endphp

            @if ($isEdit)
                @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
                    ['label' => 'My Courses', 'url' => route('faculty.courses')],
                    ['label' => $course->title ?? 'Course', 'url' => route('faculty.courses.manage', $course->id)],
                    ['label' => 'Edit Course'],
                ]])
            @endif

            <div class="page-head faculty-page-heading">
                <h2 class="faculty-page-title">{{ ($editing ?? false) ? 'Edit course' : 'Create new course' }}</h2>
                <p class="faculty-page-subtitle">{{ ($editing ?? false)
                        ? 'Save your changes as a draft, then submit for review from the course builder when it is ready.'
                        : 'Fill in the details below to create your course.' }}</p>
            </div>

            <form method="POST"
                  action="{{ $isEdit ? route('faculty.courses.update', $course->id) : route('faculty.create.store') }}"
                  enctype="multipart/form-data" id="create-course-form">
                @csrf
                @if ($isEdit) @method('PATCH') @endif

                {{-- Validation / save errors --}}
                @if ($errors->any())
                    <div class="form-errors" role="alert">
                        <strong>The course couldn't be saved. Please fix the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $skillList    = $skillOptions ?? [];
                    $relatedSaved = old('related_skills');
                    if (! is_array($relatedSaved)) {
                        // Decoded defensively: works whether or not the Course
                        // model casts related_skills to an array.
                        $relatedSaved = ($course ?? null)
                            ? \App\Http\Controllers\FacultyController::relatedSkillsOf($course)
                            : [];
                    }

                    $savedCompetencies = old('competencies', isset($course) && $course ? ($course->skills ?? []) : []);
                    $savedCompetencies = is_array($savedCompetencies) ? $savedCompetencies : [];

                    $savedOutcomes = old('learning_outcomes');
                    if (! is_array($savedOutcomes)) {
                        $savedOutcomes = [];
                        if (isset($course) && $course) {
                            $savedOutcomes = $course->learningOutcomes
                                ->map(fn ($outcome) => ['description' => $outcome->description, 'competency_unit_id' => $outcome->competency_unit_id])
                                ->all();
                            if ($savedOutcomes === []) {
                                $savedOutcomes = collect($course->objectives ?? [])
                                    ->map(fn ($objective) => ['description' => $objective, 'competency_unit_id' => null])
                                    ->all();
                            }
                        }
                    }

                    $selectedPrereqs = collect(old('prerequisite_ids', $course->prerequisite_ids ?? []))
                        ->map(fn ($v) => (int) $v)->all();

                    $learningHours = old('learning_hours', $course->learning_hours ?? (int) filter_var((string) ($course->duration ?? ''), FILTER_SANITIZE_NUMBER_INT));
                @endphp

                {{-- ── 1. Course overview ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Course overview</h3>
                        <p>The basics learners see first.</p>
                    </div>
                    <div class="form-card-body">

                        <div class="field">
                            <label for="courseTitleInput">Course title <span class="req">*</span></label>
                            <input class="input" type="text" name="title" id="courseTitleInput"
                                   placeholder="e.g. Introduction to Web Development"
                                   value="{{ old('title', $course->title ?? '') }}" required>
                        </div>

                        <div class="field field-grid cols-3">
                            <div>
                                <label for="courseCategory">Category <span class="req">*</span></label>
                                <div class="select-wrap">
                                    <select class="select" name="category" id="courseCategory">
                                        <option value="">Select a category</option>
                                        @foreach ($categories ?? [] as $category)
                                            <option value="{{ $category }}" @selected(old('category', $course->category ?? '') === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="courseLevel">Level <span class="req">*</span></label>
                                <div class="select-wrap">
                                    <select class="select" name="level" id="courseLevel">
                                        @foreach ($levels ?? ['Beginner'] as $level)
                                            <option value="{{ $level }}" @selected(old('level', $course->level ?? 'Beginner') === $level)>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="coursePqf">PQF level alignment</label>
                                <div class="select-wrap">
                                    <select class="select" name="pqf_level" id="coursePqf">
                                        <option value="">Select PQF level</option>
                                        @foreach ([5,6,7,8] as $pqfLevel)
                                            <option value="{{ $pqfLevel }}" @selected((string) old('pqf_level', $course->pqf_level ?? '') === (string) $pqfLevel)>PQF Level {{ $pqfLevel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label for="course-short-description">Short summary <span class="req">*</span></label>
                            <textarea class="textarea" id="course-short-description" name="short_description"
                                      maxlength="120" rows="2" required
                                      placeholder="A brief plain-text summary shown on course cards.">{{ old('short_description', $course->short_description ?? '') }}</textarea>
                            <div class="field-foot">
                                <small class="field-hint">Appears on course cards and near the course title on the public page.</small>
                                <span class="field-count" id="shortSummaryCount" aria-live="polite">0 / 120</span>
                            </div>
                        </div>

                        <div class="field">
                            <label>Description <span class="req">*</span></label>
                            @include('components.rich-text-editor', ['name' => 'description', 'id' => 'course-description-editor', 'value' => old('description', $course->description ?? ''), 'placeholder' => 'Describe the micro-credential, its purpose, and what learners will achieve.'])
                        </div>

                        <input type="hidden" name="duration" id="legacyCourseDuration" value="{{ $learningHours }}">
                        <input type="hidden" name="passing_score" value="{{ old('passing_score', $course->passing_score ?? 75) }}">
                    </div>
                </section>

                {{-- ── 2. Course image ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Course image</h3>
                        <p>Optional. Choose the image type first, then pick the file.</p>
                    </div>
                    <div class="form-card-body">
                        {{-- Uploaded by FacultyController::storeThumbnail() into public/uploads/thumbnails --}}
                        <div class="file-picker">
                            <select class="file-type-select" id="thumbType" onchange="thumbTypeChanged()" aria-label="Image file type">
                                <option value="">Select file type...</option>
                                <option value="JPG">JPG / JPEG</option>
                                <option value="PNG">PNG</option>
                                <option value="WEBP">WEBP</option>
                                <option value="GIF">GIF</option>
                            </select>
                            <label class="file-row is-disabled" id="thumbRow">
                                <span class="file-btn">Choose file</span>
                                <span class="file-name" id="thumbName">Select a file type first</span>
                                <input type="file" name="thumbnail" id="thumbInput" disabled
                                       onchange="thumbFileChanged(this)">
                            </label>
                        </div>

                        @if (($editing ?? false) && ($course->thumbnail_url ?? null))
                            <div class="thumb-current">
                                Current thumbnail: <strong>{{ basename($course->thumbnail_url) }}</strong>
                                &mdash; leave the picker empty to keep it.
                            </div>
                        @endif
                    </div>
                </section>

                {{-- ── 3. Skills & recommendations ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Skills and recommendations</h3>
                        <p>What learners will see, and how this course is matched to student profiles.</p>
                    </div>
                    <div class="form-card-body">
                        <div class="field field-grid">

                            <div>
                                <label for="competencyInput">Skills shown to learners</label>
                                <div class="competency-entry">
                                    <input class="input" type="text" id="competencyInput" placeholder="Enter a skill, then press Enter">
                                    <button type="button" class="competency-add" id="addCompetency">Add</button>
                                </div>
                                <div class="competency-list" id="competencyList">
                                    @foreach ($savedCompetencies as $competency)
                                        <span class="competency-chip"><input type="hidden" name="competencies[]" value="{{ $competency }}"><span>{{ $competency }}</span><button type="button" class="competency-remove" aria-label="Remove {{ $competency }}">&times;</button></span>
                                    @endforeach
                                </div>
                                <small class="field-hint">Use the learning outcomes below to map assessed skills to formal competency units.</small>
                            </div>

                            {{-- Internal only. Drives the Recommended toggle on the
                                 student Browse page; never shown to students. --}}
                            <div>
                                <span class="label fac-recommendation-label">Recommendation topics</span>

                                {{-- Mirrors the ticked boxes as plain text. The checkbox
                                     array is still posted; this guarantees the value
                                     survives even if the boxes are not submitted. --}}
                                <input type="hidden" name="related_skills_csv" id="relatedCsv"
                                       value="{{ implode('||', $relatedSaved) }}">

                                @if (($editing ?? false) && empty($relatedSaved))
                                    <p class="related-none-note">Nothing saved for this course yet.</p>
                                @endif

                                <div class="related-wrap" id="relatedWrap">
                                    <button type="button" class="related-mini {{ count($relatedSaved) ? 'has-value' : '' }}"
                                            id="relatedMini" onclick="openRelated()" aria-haspopup="dialog">
                                        <span id="relatedMiniLabel">{{ count($relatedSaved) ? count($relatedSaved) . ' selected' : 'None selected' }}</span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </button>

                                    <div class="related-backdrop" id="relatedBackdrop" hidden></div>

                                    <div class="related-pop" id="relatedPop" hidden role="dialog"
                                         aria-modal="true" aria-label="Related skills">
                                        <div class="related-pop-hd">
                                            <strong>Related skills</strong>
                                            <span>Used to recommend this course to students</span>
                                        </div>

                                        <div class="related-search">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                                            </svg>
                                            <input type="text" id="relatedSearch" autocomplete="off"
                                                   placeholder="Search {{ count($skillList) }} skills..."
                                                   oninput="filterRelated(this.value)">
                                            <button type="button" class="related-clear" onclick="clearRelatedSearch()"
                                                    title="Clear search" aria-label="Clear search">&times;</button>
                                        </div>

                                        <div class="related-selected-line">
                                            <span id="relatedCountLine">No skills selected</span>
                                            <button type="button" class="related-clearall" onclick="clearRelatedAll()">Clear all</button>
                                        </div>

                                        <div class="related-list" id="relatedList">
                                            @foreach (($skillGroups ?? []) as $groupName => $groupSkills)
                                                <div class="related-group" data-group="{{ $groupName }}">
                                                    <div class="related-group-hd">{{ $groupName }}</div>
                                                    @foreach ($groupSkills as $skill)
                                                        <label class="related-opt {{ in_array($skill, $relatedSaved) ? 'on' : '' }}"
                                                               data-skill="{{ strtolower($skill) }}"
                                                               data-groupname="{{ strtolower($groupName) }}">
                                                            <input type="checkbox" name="related_skills[]"
                                                                   value="{{ $skill }}"
                                                                   @checked(in_array($skill, $relatedSaved))>
                                                            <span class="related-tick">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2">
                                                                    <path d="M5 12.5l5 5 9-10"/>
                                                                </svg>
                                                            </span>
                                                            <span class="related-name">{{ $skill }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                            <div class="related-empty" id="relatedEmpty" hidden>No skills match that search.</div>
                                        </div>

                                        <div class="related-pop-ft">
                                            <button type="button" class="related-btn" onclick="cancelRelated()">Cancel</button>
                                            <button type="button" class="related-btn primary" onclick="saveRelated()">Save</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="related-chips" id="relatedChips">
                                    @foreach ($relatedSaved as $skill)
                                        <span class="related-chip">{{ $skill }}</span>
                                    @endforeach
                                </div>
                                <small class="field-hint">Matches this course with skills in learner profiles.</small>
                            </div>

                        </div>
                    </div>
                </section>

                {{-- ── 4. Learning design ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Learning design</h3>
                        <p>Outcomes, audience, delivery and assessment.</p>
                    </div>
                    <div class="form-card-body">

                        <div class="field">
                            <span class="label">Measurable learning outcomes</span>

                            <div class="outcome-head" aria-hidden="true">
                                <span>Outcome</span>
                                <span>Competency unit</span>
                                <span></span>
                            </div>
                            <div id="learning-outcomes-list">
                                @foreach ($savedOutcomes as $outcomeIndex => $outcome)
                                    <div class="learning-outcome-row">
                                        <input class="input" name="learning_outcomes[{{ $outcomeIndex }}][description]"
                                               value="{{ $outcome['description'] ?? '' }}" maxlength="2000"
                                               aria-label="Learning outcome"
                                               placeholder="Observable outcome (e.g. Build a responsive page)" required>
                                        <div class="select-wrap">
                                            <select class="select" name="learning_outcomes[{{ $outcomeIndex }}][competency_unit_id]" aria-label="Competency unit">
                                                <option value="">No competency mapping</option>
                                                @foreach ($competencyUnitOptions ?? [] as $unit)
                                                    <option value="{{ $unit->id }}" @selected((string) ($outcome['competency_unit_id'] ?? '') === (string) $unit->id)>{{ $unit->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="competency-remove" type="button" aria-label="Remove outcome" onclick="this.closest('.learning-outcome-row').remove()">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="outcome-empty">No outcomes yet. Add the first one below.</div>
                            <button class="competency-add btn-ghost" type="button" id="add-learning-outcome">Add outcome</button>
                            <small class="field-hint">Write outcomes as observable actions. Map each to a competency unit when one applies. Outcomes appear to students and in credential records.</small>
                        </div>

                        <div class="field field-grid">
                            <div>
                                <label for="targetLearners">Intended learners</label>
                                <textarea class="textarea" id="targetLearners" name="target_learners" rows="3" placeholder="Who should take this micro-credential?">{{ old('target_learners', $course->target_learners ?? '') }}</textarea>
                            </div>
                            <div>
                                <label for="deliveryMode">Delivery mode</label>
                                <div class="select-wrap">
                                    <select class="select" name="delivery_mode" id="deliveryMode">
                                        <option value="">Select delivery mode</option>
                                        @foreach (['online' => 'Online', 'blended' => 'Blended', 'face_to_face' => 'Face to face', 'self_paced' => 'Self-paced'] as $deliveryValue => $deliveryLabel)
                                            <option value="{{ $deliveryValue }}" @selected(old('delivery_mode', $course->delivery_mode ?? '') === $deliveryValue)>{{ $deliveryLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="fac-section-spacing">
                                    <label for="estimatedLearningHours">Estimated learner effort (hours)</label>
                                    <input class="input" type="number" name="learning_hours" id="estimatedLearningHours"
                                           min="1" max="10000" value="{{ $learningHours }}" placeholder="e.g. 24"
                                           oninput="document.getElementById('legacyCourseDuration').value = this.value">
                                    <small class="field-hint">Total time a learner is expected to spend on the course.</small>
                                </div>
                            </div>
                        </div>

                        <div class="field field-grid">
                            <div>
                                <label for="assessmentStrategy">Assessment strategy</label>
                                <textarea class="textarea" id="assessmentStrategy" name="assessment_strategy" rows="3" placeholder="Describe formative and summative assessment.">{{ old('assessment_strategy', $course->assessment_strategy ?? '') }}</textarea>
                                <small class="field-hint">Pass criteria are set on each quiz or activity.</small>
                            </div>
                            <div>
                                <label for="gradingRubric">Grading rubric / mastery standard</label>
                                <textarea class="textarea" id="gradingRubric" name="grading_rubric" rows="3" placeholder="State the criteria and threshold used to judge mastery.">{{ old('grading_rubric', $course->grading_rubric ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ── 5. Prerequisites: courses already live on the site ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Prerequisites</h3>
                        <p>Courses a student should complete before taking this one. Optional.</p>
                    </div>
                    <div class="form-card-body">
                        @if (($prereqOptions ?? collect())->isEmpty())
                            <div class="prereq-empty">No other published courses are available yet.</div>
                        @else
                            <input type="text" id="prereq-search" class="input prereq-search"
                                   placeholder="Search courses..." aria-label="Search courses" autocomplete="off">
                            <div id="prereq-list" class="prereq-list">
                                @foreach ($prereqOptions as $opt)
                                    <label class="prereq-item"
                                           data-name="{{ strtolower($opt->title . ' ' . $opt->category) }}">
                                        <input type="checkbox" name="prerequisite_ids[]" value="{{ $opt->id }}"
                                               @checked(in_array($opt->id, $selectedPrereqs, true))>
                                        <span class="prereq-title">{{ $opt->title }}</span>
                                        @if ($opt->category)
                                            <span class="prereq-cat">&middot; {{ $opt->category }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                @if ($editing ?? false)
                    {{-- Keep previously saved legacy credit metadata when editing. --}}
                    <input type="hidden" name="credit_bearing" value="{{ old('credit_bearing', (int) ($course->credit_bearing ?? false)) }}">
                    <input type="hidden" name="credit_equivalency" value="{{ old('credit_equivalency', $course->credit_equivalency ?? '') }}">
                    <input type="hidden" name="equivalent_course" value="{{ old('equivalent_course', $course->equivalent_course ?? '') }}">
                @endif

                {{-- ── 6. Credential & completion ── --}}
                <section class="form-card">
                    <div class="form-card-head">
                        <h3>Credential and completion</h3>
                        <p>Configure the badge and what a learner must finish to earn it.</p>
                    </div>
                    <div class="form-card-body">
                        @include('components.inline-badge-builder', ['course' => $course ?? null])

                        <div class="auto-credential-note">
                            <strong>Certificate of completion:</strong> issued automatically once all required learning, mastery, faculty verification (if enabled), and academic-unit approval are satisfied. No separate certificate setup is needed.
                        </div>

                        <hr class="form-divider">

                        <div class="field">
                            <label class="check-item">
                                <input type="hidden" name="requires_faculty_verification" value="0">
                                <input type="checkbox" name="requires_faculty_verification" value="1" @checked(old('requires_faculty_verification', $course->requires_faculty_verification ?? false))>
                                <span>Require faculty verification before this microcredential is officially completed</span>
                            </label>
                            <small class="field-hint fac-field-hint-indent">When enabled, the assigned faculty member must verify the learner before the badge and certificate are released.</small>
                        </div>
                    </div>
                </section>

                {{-- ── 7. Review notes (edit only) ── --}}
                @if ($editing ?? false)
                    <section class="form-card">
                        <div class="form-card-head">
                            <h3>Review notes</h3>
                            <p>Shown to the admin when this course is reviewed.</p>
                        </div>
                        <div class="form-card-body">
                            <div class="field">
                                <label for="changeNote">What did you change?</label>
                                <textarea class="textarea" id="changeNote" name="change_note" rows="4"
                                          placeholder="e.g. Rewrote Module 2 lessons and lowered the passing score to 70%.">{{ old('change_note', $course->change_note ?? '') }}</textarea>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Save goes to the Managing Course screen. Cancel leaves without saving. --}}
                <div class="form-actions">
                    <button class="btn-save" type="submit">{{ ($editing ?? false) ? 'Save details' : 'Save and continue' }}</button>
                    <a class="btn-cancel" href="{{ $isEdit ? route('faculty.courses.manage', $course->id) : route('faculty.dashboard') }}">Cancel</a>
                </div>

            </form>
        </div>{{-- /.build-form --}}
    </div>{{-- /.build-shell --}}
    </main>


    <x-slot:scripts>
{{-- Back to top (appears on long pages) --}}
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

<script>
    // ── Skills shown to learners (chip entry) ───────────────────────
    (function () {
        var input = document.getElementById('competencyInput');
        var addButton = document.getElementById('addCompetency');
        var list = document.getElementById('competencyList');
        if (!input || !addButton || !list) return;

        function addCompetency() {
            var value = input.value.trim();
            if (!value) return;
            var existing = Array.prototype.slice.call(list.querySelectorAll('input[name="competencies[]"]'));
            if (existing.some(function (field) { return field.value.toLowerCase() === value.toLowerCase(); })) {
                input.value = '';
                return;
            }

            var chip = document.createElement('span');
            chip.className = 'competency-chip';
            chip.innerHTML = '<input type="hidden" name="competencies[]"><span></span><button type="button" class="competency-remove" aria-label="Remove skill">&times;</button>';
            chip.querySelector('input').value = value;
            chip.querySelector('span').textContent = value;
            chip.querySelector('button').addEventListener('click', function () { chip.remove(); });
            list.appendChild(chip);
            input.value = '';
            input.focus();
        }

        addButton.addEventListener('click', addCompetency);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addCompetency();
            }
        });
        list.querySelectorAll('.competency-remove').forEach(function (button) {
            button.addEventListener('click', function () { button.closest('.competency-chip').remove(); });
        });
    })();

    // ── Recommendation topics picker ────────────────────────────────
    //    Cancel restores whatever was ticked when the panel was opened.
    var _relatedSnapshot = [];

    function relatedBoxes() {
        return Array.prototype.slice.call(
            document.querySelectorAll('#relatedPop input[name="related_skills[]"]')
        );
    }

    function syncRelatedRows() {
        relatedBoxes().forEach(function (box) {
            box.closest('.related-opt').classList.toggle('on', box.checked);
        });
    }

    /* A handful of skills sit under two fields (Statistics in Economics and
       Mathematics, Therapy in Nutrition and Social Work, and so on). Ticking
       one copy ticks the other, so the count and the rows always agree. */
    function mirrorRelated(box) {
        relatedBoxes().forEach(function (other) {
            if (other !== box && other.value === box.value) {
                other.checked = box.checked;
            }
        });
    }

    function refreshRelatedLabel() {
        // Distinct values: a skill listed under two fields must count once.
        var picked = {};
        relatedBoxes().forEach(function (b) { if (b.checked) picked[b.value] = true; });
        var names = Object.keys(picked);
        var count = names.length;

        // Mirror into the hidden text field that always posts.
        var csv = document.getElementById('relatedCsv');
        if (csv) csv.value = names.join('||');

        var mini  = document.getElementById('relatedMini');
        var label = document.getElementById('relatedMiniLabel');
        var line  = document.getElementById('relatedCountLine');
        var chips = document.getElementById('relatedChips');

        if (line) {
            line.textContent = count
                ? count + (count === 1 ? ' skill selected' : ' skills selected')
                : 'No skills selected';
        }

        // Keep the chips under the button in step with the selection.
        if (chips) {
            chips.innerHTML = '';
            names.forEach(function (name) {
                var chip = document.createElement('span');
                chip.className = 'related-chip';
                chip.textContent = name;
                chips.appendChild(chip);
            });
        }

        if (!mini || !label) return;
        label.textContent = count ? count + ' selected' : 'None selected';
        mini.classList.toggle('has-value', count > 0);
    }

    /* Filter by skill name or group name. Ticked skills always stay
       visible, so you never lose track of a selection while searching. */
    function filterRelated(term) {
        term = (term || '').trim().toLowerCase();
        var anyVisible = false;

        document.querySelectorAll('#relatedList .related-group').forEach(function (group) {
            var groupVisible = false;

            group.querySelectorAll('.related-opt').forEach(function (opt) {
                var box     = opt.querySelector('input');
                var name    = opt.getAttribute('data-skill') || '';
                var gname   = opt.getAttribute('data-groupname') || '';
                var matches = !term
                    || name.indexOf(term) !== -1
                    || gname.indexOf(term) !== -1
                    || (box && box.checked);

                opt.hidden = !matches;
                if (matches) groupVisible = true;
            });

            group.hidden = !groupVisible;
            if (groupVisible) anyVisible = true;
        });

        var empty = document.getElementById('relatedEmpty');
        if (empty) empty.hidden = anyVisible;
    }

    function clearRelatedSearch() {
        var input = document.getElementById('relatedSearch');
        if (input) { input.value = ''; input.focus(); }
        filterRelated('');
    }

    function clearRelatedAll() {
        relatedBoxes().forEach(function (b) { b.checked = false; });
        syncRelatedRows();
        refreshRelatedLabel();
        var search = document.getElementById('relatedSearch');
        filterRelated(search ? search.value : '');
    }

    function openRelated() {
        var pop  = document.getElementById('relatedPop');
        var back = document.getElementById('relatedBackdrop');
        if (!pop) return;

        // Remember the current state so Cancel can put it back.
        _relatedSnapshot = relatedBoxes().filter(function (b) { return b.checked; })
                                         .map(function (b) { return b.value; });
        pop.hidden = false;
        if (back) back.hidden = false;

        // Stop the page behind the modal from scrolling.
        document.body.style.overflow = 'hidden';

        syncRelatedRows();
        clearRelatedSearch();

        var input = document.getElementById('relatedSearch');
        if (input) input.focus();
    }

    function closeRelated() {
        var pop  = document.getElementById('relatedPop');
        var back = document.getElementById('relatedBackdrop');
        if (pop)  pop.hidden = true;
        if (back) back.hidden = true;
        document.body.style.overflow = '';
        var mini = document.getElementById('relatedMini');
        if (mini) mini.focus();
    }

    function saveRelated() {
        refreshRelatedLabel();
        closeRelated();
    }

    function cancelRelated() {
        var pop = document.getElementById('relatedPop');
        if (!pop || pop.hidden) return;   // nothing open, nothing to undo

        relatedBoxes().forEach(function (box) {
            box.checked = _relatedSnapshot.indexOf(box.value) !== -1;
        });
        syncRelatedRows();
        refreshRelatedLabel();
        closeRelated();
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncRelatedRows();
        refreshRelatedLabel();

        // Final sync immediately before the form posts.
        var courseForm = document.getElementById('create-course-form');
        if (courseForm) {
            courseForm.addEventListener('submit', function () { refreshRelatedLabel(); });
        }

        relatedBoxes().forEach(function (box) {
            box.addEventListener('change', function () {
                mirrorRelated(box);
                syncRelatedRows();
                refreshRelatedLabel();
            });
        });

        // Clicking the backdrop behaves like Cancel
        var back = document.getElementById('relatedBackdrop');
        if (back) back.addEventListener('click', cancelRelated);

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var pop = document.getElementById('relatedPop');
            if (pop && !pop.hidden) cancelRelated();
        });
    });

    // ── Draft persistence: keep filled fields when leaving & returning ──
    (function () {
        var form = document.getElementById('create-course-form');
        if (!form) return;

        // Editing an existing course: fields are already populated from the
        // database, and a stale "new course" draft would overwrite them.
        var IS_EDIT = {{ ($editing ?? false) ? 'true' : 'false' }};
        if (IS_EDIT) return;

        var KEY = 'upskill_create_course_draft';

        // Drop drafts older than a day so an abandoned draft cannot make a
        // fresh Create form look half-loaded.
        try {
            var stamp = parseInt(localStorage.getItem(KEY + '_at') || '0', 10);
            if (stamp && Date.now() - stamp > 86400000) {
                localStorage.removeItem(KEY);
                localStorage.removeItem(KEY + '_at');
            }
        } catch (e) {}

        // Only plain single-value fields are drafted. Array fields
        // (competencies[], related_skills[], learning_outcomes[...]) and hidden
        // inputs are skipped so restoring cannot overwrite checkbox values.
        function isDraftable(f) {
            if (!f.name || f.name.indexOf('[') !== -1) return false;
            return ['file', 'password', 'hidden'].indexOf(f.type) === -1;
        }

        // Restore saved values on load (server-rendered old() values win when
        // the form bounced back from a validation error).
        try {
            var saved = JSON.parse(localStorage.getItem(KEY) || '{}');
            var hasServerValues = {{ $errors->any() ? 'true' : 'false' }};
            if (!hasServerValues) {
                Object.keys(saved).forEach(function (name) {
                    var field = form.querySelector('[name="' + name + '"]:not([type="hidden"])');
                    if (!field || !isDraftable(field)) return;
                    if (field.type === 'radio') {
                        var r = form.querySelector('[name="' + name + '"][value="' + saved[name] + '"]');
                        if (r) r.checked = true;
                    } else if (field.type === 'checkbox') {
                        field.checked = saved[name] === '1';
                    } else {
                        field.value = saved[name];
                    }
                });
            }
        } catch (e) {}

        function saveDraft() {
            var data = {};
            form.querySelectorAll('input, select, textarea').forEach(function (f) {
                if (!isDraftable(f)) return;
                if (f.type === 'radio') { if (f.checked) data[f.name] = f.value; return; }
                if (f.type === 'checkbox') { data[f.name] = f.checked ? '1' : ''; return; }
                data[f.name] = f.value;
            });
            try {
                localStorage.setItem(KEY, JSON.stringify(data));
                localStorage.setItem(KEY + '_at', String(Date.now()));
            } catch (e) {}
        }
        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);

        // Clear the draft once the course is actually created.
        form.addEventListener('submit', function () {
            try {
                localStorage.removeItem(KEY);
                localStorage.removeItem(KEY + '_at');
            } catch (e) {}
        });
    })();
</script>

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')

<script>
    // Short summary: live character count.
    (function () {
        var field = document.getElementById('course-short-description');
        var count = document.getElementById('shortSummaryCount');
        if (!field || !count) return;
        function update() {
            var max = parseInt(field.getAttribute('maxlength') || '120', 10);
            var len = field.value.length;
            count.textContent = len + ' / ' + max;
            count.classList.toggle('is-near', len >= max - 15);
        }
        field.addEventListener('input', update);
        update();
    })();

    // Filter the prerequisite course list as you type.
    (function () {
        var box = document.getElementById('prereq-search');
        if (!box) return;
        box.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            document.querySelectorAll('#prereq-list .prereq-item').forEach(function (row) {
                row.style.display = !q || row.dataset.name.indexOf(q) !== -1 ? 'flex' : 'none';
            });
        });
    })();
</script>

<script>
    // Thumbnail picker: the file input stays locked until an image type is
    // chosen, then only accepts that extension.
    var THUMB_TYPES = {
        JPG:  ['jpg', 'jpeg'],
        PNG:  ['png'],
        WEBP: ['webp'],
        GIF:  ['gif']
    };

    function thumbTypeChanged() {
        var sel   = document.getElementById('thumbType');
        var input = document.getElementById('thumbInput');
        var row   = document.getElementById('thumbRow');
        var span  = document.getElementById('thumbName');
        if (!sel || !input || !row || !span) return;

        var exts = THUMB_TYPES[sel.value];

        // Switching type always clears the choice, so a PNG picked under
        // "PNG" cannot survive a switch to "GIF".
        input.value = '';

        if (!exts) {
            input.disabled = true;
            input.removeAttribute('accept');
            row.classList.add('is-disabled');
            span.textContent = 'Select a file type first';
            return;
        }

        input.disabled = false;
        input.setAttribute('accept', exts.map(function (e) { return '.' + e; }).join(','));
        row.classList.remove('is-disabled');
        span.textContent = 'No file chosen';
    }

    function thumbFileChanged(input) {
        var span = document.getElementById('thumbName');
        var sel  = document.getElementById('thumbType');
        if (!span) return;

        if (!input.files || !input.files.length) {
            span.textContent = sel && sel.value ? 'No file chosen' : 'Select a file type first';
            return;
        }

        var name = input.files[0].name;
        var ext  = (name.split('.').pop() || '').toLowerCase();
        var exts = sel ? THUMB_TYPES[sel.value] : null;

        if (exts && exts.indexOf(ext) === -1) {
            alert('"' + name + '" is not a ' + sel.options[sel.selectedIndex].text + ' file.\n\n'
                + 'Choose a file ending in ' + exts.map(function (e) { return '.' + e; }).join(' or ')
                + ', or change the file type above.');
            input.value = '';
            span.textContent = 'No file chosen';
            return;
        }

        span.textContent = name;
    }
</script>

<script>
(() => {
    const addButton = document.getElementById('add-learning-outcome');
    const list = document.getElementById('learning-outcomes-list');
    const competencyUnits = @json(($competencyUnitOptions ?? collect())->map(fn ($unit) => ['id' => $unit->id, 'title' => $unit->title])->values());
    if (!addButton || !list) return;

    addButton.addEventListener('click', () => {
        const indexes = Array.from(list.querySelectorAll('input[name$="[description]"]'))
            .map((input) => Number((input.name.match(/learning_outcomes\[(\d+)\]/) || [])[1] || -1));
        const index = Math.max(-1, ...indexes) + 1;

        const row = document.createElement('div');
        row.className = 'learning-outcome-row';

        const description = document.createElement('input');
        description.className = 'input';
        description.name = `learning_outcomes[${index}][description]`;
        description.maxLength = 2000;
        description.required = true;
        description.setAttribute('aria-label', 'Learning outcome');
        description.placeholder = 'Observable outcome (e.g. Build a responsive page)';

        const wrap = document.createElement('div');
        wrap.className = 'select-wrap';
        const select = document.createElement('select');
        select.className = 'select';
        select.name = `learning_outcomes[${index}][competency_unit_id]`;
        select.setAttribute('aria-label', 'Competency unit');
        select.add(new Option('No competency mapping', ''));
        competencyUnits.forEach((unit) => select.add(new Option(unit.title, unit.id)));
        wrap.appendChild(select);

        const remove = document.createElement('button');
        remove.className = 'competency-remove';
        remove.type = 'button';
        remove.setAttribute('aria-label', 'Remove outcome');
        remove.textContent = '\u00d7';
        remove.addEventListener('click', () => row.remove());

        row.append(description, wrap, remove);
        list.appendChild(row);
        description.focus();
    });
})();
</script>
    </x-slot:scripts>
</x-faculty.layout>
