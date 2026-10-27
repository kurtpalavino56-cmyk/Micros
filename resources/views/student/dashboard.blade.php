{{--
    resources/views/dashboard.blade.php

    Student dashboard content rendered inside the shared student shell.

    Expected data from the controller, e.g.:

    return view('student.dashboard', [
        'user'   => $user,                 // Auth::user() - needs ->name
        'stats'  => [
            'active_courses' => $activeCoursesCount,
            'completed'      => $completedCount,
            'badges_earned'  => $badgesEarnedCount,
            'certificates'   => $certificatesCount,
        ],
        'courses'  => $enrolledCourses,     // collection of Course models
        'badges'   => $earnedBadges,        // collection for the "Badges" side panel
        'activityWeeks' => $weeklyActivityGrid,
        'activityTotal' => $activityCount,
    ]);

    Each $course is expected to expose:
        ->id, ->title, ->category, ->thumbnail_url, ->progress_percent
--}}
<x-layout title="Dashboard | Upskill" shell="student" page="dashboard">



<div class="page-content student-dashboard">
        @php
            $greetingHour = now('Asia/Manila')->hour;
            $timeGreeting = $greetingHour < 12
                ? 'Good morning'
                : ($greetingHour < 17 ? 'Good afternoon' : 'Good evening');
        @endphp
        <header class="page-header ui-page-header student-page-heading">
            <div class="ui-page-header__content">
                <h2 class="ui-page-title">{{ $timeGreeting }}, {{ $user->username ?: ($user->name ?? 'Student') }}!</h2>
                <p class="ui-page-subtitle">Build in-demand skills, earn microcredentials, and take the next step in your Microcredential journey.</p>
                <p class="student-dashboard__student-id">Student ID: {{ $user->student_id ?? '—' }}</p>
            </div>
            <a href="{{ route('courses.browse') }}" class="ui-button ui-button--primary">Explore courses</a>
        </header>

        <section class="stats" aria-label="Learning summary">
            <!-- <div class="stat-card c-navy"><div class="stat-top"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg><span class="num">{{ $stats['active_courses'] ?? 0 }}</span></div><div class="label">Courses in progress</div></div>
            <div class="stat-card c-gold"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg><span class="num">{{ $stats['completed'] ?? 0 }}</span></div><div class="label">Completed credentials</div></div>
            <div class="stat-card c-cyan"><div class="stat-top"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2"/></svg><span class="num">{{ $stats['badges_earned'] ?? 0 }}</span></div><div class="label">Skills and badges</div></div>
            <div class="stat-card c-deep"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z" fill="currentColor"/></svg><span class="num">{{ $stats['certificates'] ?? 0 }}</span></div><div class="label">Certificates earned</div></div> -->
            <div class="stat-card c-navy"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4z"/></svg><span class="num">{{ $stats['active_courses'] ?? 0 }}</span></div><div class="label">Courses in progress</div></div>
            <div class="stat-card c-gold"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg><span class="num">{{ $stats['completed'] ?? 0 }}</span></div><div class="label">Completed credentials</div></div>
            <div class="stat-card c-cyan"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 16 9 5 9-5"/></svg><span class="num">{{ $stats['badges_earned'] ?? 0 }}</span></div><div class="label">Skills and badges</div></div>
            <div class="stat-card c-deep"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/></svg><span class="num">{{ $stats['certificates'] ?? 0 }}</span></div><div class="label">Certificates earned</div></div>
        </section>

        <div class="content-grid">
            <section class="courses-col">
                <div class="courses-head"><h3>Your course progress</h3><a href="{{ route('courses.enrolled') }}" class="enroll-more">View all courses <span class="dashboard-chevron">&rsaquo;</span></a></div>
                @php $inProgressCourses = $inProgressCourses ?? collect(); $completedCourses = $completedCourses ?? collect(); @endphp
                @if ($inProgressCourses->isEmpty() && $completedCourses->isEmpty())
                    <div class="empty-state ui-empty-state">You haven't enrolled in any courses yet.<br><a href="{{ route('courses.browse') }}" class="enroll-more">Browse courses to get started</a></div>
                @else
                    @forelse ($inProgressCourses as $course)
                    <div class="course-card ui-card-surface"><div class="thumb">@if($course->thumbnail_url)<img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }} course thumbnail" loading="lazy" onerror="this.remove()">@endif</div><div class="course-info"><h4>{{ $course->title }}</h4>@if($course->category)<div class="cat">{{ $course->category }}</div>@endif<div class="progress-track ui-progress-track"><div class="progress-fill progress-in-progress ui-progress-fill" style="width:{{ $course->progress_percent ?? 0 }}%"></div></div><div class="pct">{{ $course->progress_percent ?? 0 }}% complete</div></div><a class="btn-start {{ ($course->progress_percent ?? 0) > 0 ? 'btn-start--continue' : '' }} ui-button ui-button--primary" href="{{ route('courses.show', $course->id) }}">{{ ($course->progress_percent ?? 0) > 0 ? 'Continue' : 'Start' }}</a></div>
                    @empty <div class="empty-state empty-state-slim ui-empty-state">Nothing in progress right now.</div> @endforelse
                    @if($completedCourses->isNotEmpty())<h4 class="courses-area-title">Completed courses</h4>@endif
                    @foreach ($completedCourses as $course)
                    <div class="course-card ui-card-surface"><div class="thumb">@if($course->thumbnail_url)<img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }} course thumbnail" loading="lazy" onerror="this.remove()">@endif</div><div class="course-info"><h4>{{ $course->title }}</h4>@if($course->category)<div class="cat">{{ $course->category }}</div>@endif<div class="progress-track ui-progress-track"><div class="progress-fill progress-completed ui-progress-fill" style="width:100%"></div></div><div class="pct">Completed</div></div><a class="btn-start btn-completed ui-button ui-button--secondary" href="{{ route('courses.show', $course->id) }}">View</a></div>
                    @endforeach
                @endif
            </section>

            <aside class="side-col">
                <section class="learning-activity panel ui-card-surface" aria-labelledby="learning-activity-title">
                    <div class="panel-head ui-card-header">
                        <div>
                            <h3 id="learning-activity-title" class="ui-card-header__title">Your learning actions</h3>
                            <p class="learning-activity__period">Past 12 months</p>
                        </div>
                        <span class="learning-activity__year">{{ $activityMonths[array_key_last($activityMonths ?? [])]['year'] ?? '' }}</span>
                    </div>
                    <div class="learning-activity__summary">
                        <strong>{{ number_format($activityTotal ?? 0) }}</strong>
                        <span>logged learning actions in the past 12 months</span>
                    </div>
                    <div class="learning-activity__navigation">
                        <button class="learning-activity__arrow" type="button" data-activity-scroll="left" aria-label="Scroll to older months">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m12.5 4.5-5 5 5 5"/></svg>
                        </button>
                        <div class="learning-activity__timeline">
                            <div class="learning-activity__weekdays" aria-hidden="true">
                                <span></span>
                                @foreach (['Mon', '', 'Wed', '', 'Fri', '', ''] as $weekday)
                                    <span>{{ $weekday }}</span>
                                @endforeach
                            </div>
                            <div class="learning-activity__scroll" data-activity-months tabindex="0" aria-label="Scrollable learning activity by month">
                                <div class="learning-activity__months">
                                    @foreach ($activityMonths ?? [] as $month)
                                        <section class="learning-activity__month" aria-label="{{ $month['label'] }} {{ $month['year'] }}: {{ $month['total'] }} {{ \Illuminate\Support\Str::plural('activity', $month['total']) }}">
                                            <div class="learning-activity__month-heading">
                                                <strong>{{ $month['label'] }}</strong>
                                                <span>{{ $month['total'] }}</span>
                                            </div>
                                            <div class="learning-activity__weeks" role="grid" aria-label="Learning actions for {{ $month['label'] }} {{ $month['year'] }}">
                                                @foreach ($month['weeks'] as $week)
                                                    <div class="learning-activity__week" role="row">
                                                        @foreach ($week as $day)
                                                            @if ($day['placeholder'])
                                                                <span class="learning-activity__placeholder" aria-hidden="true"></span>
                                                            @else
                                                                <span
                                                                    class="learning-activity__day learning-activity__day--{{ $day['level'] }}"
                                                                    role="gridcell"
                                                                    aria-label="{{ $day['count'] }} activities on {{ $day['date'] }}"
                                                                    title="{{ $day['count'] }} {{ \Illuminate\Support\Str::plural('activity', $day['count']) }} on {{ $day['date'] }}"
                                                                ></span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button class="learning-activity__arrow" type="button" data-activity-scroll="right" aria-label="Scroll to newer months">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m7.5 4.5 5 5-5 5"/></svg>
                        </button>
                    </div>
                    <div class="learning-activity__legend" aria-hidden="true">
                        <span>0</span>
                        @foreach (range(0, 4) as $level)
                            <span class="learning-activity__day learning-activity__day--{{ $level }}"></span>
                        @endforeach
                        <span>5+</span>
                    </div>
                </section>
                @if(($stackingFrameworks ?? collect())->isNotEmpty())
                    <div class="panel ui-card-surface"><div class="panel-head ui-card-header"><span class="ui-card-header__title">Stacking progress</span><a href="{{ route('stacking.progress') }}">View all <span class="dashboard-chevron">&rsaquo;</span></a></div><div class="panel-body">
                        @foreach($stackingFrameworks->take(2) as $stacking)
                            <p style="margin:0 0 8px;font-weight:700;">{{ \Illuminate\Support\Str::limit($stacking['framework']->name, 28) }}</p>
                            <p style="margin:0 0 12px;color:var(--ui-text-body);font-size:var(--text-xs);">{{ $stacking['completed_required_count'] }} of {{ $stacking['total_required_count'] }} required microcredentials completed</p>
                        @endforeach
                    </div></div>
                @endif
                <div class="panel ui-card-surface"><div class="panel-head ui-card-header"><span class="ui-card-header__title">Badges earned</span><a href="{{ route('badges.index') }}">View all <span class="dashboard-chevron">&rsaquo;</span></a></div><div class="panel-body badges-earned-list">@forelse ($badges ?? [] as $badge)<div class="earned-badge-row"><span class="earned-badge-icon" aria-hidden="true">@if($badge->icon_url ?? null)<img src="{{ $badge->icon_url }}" alt="" loading="lazy">@else<span>✦</span>@endif</span><span>{{ $badge->name ?? $badge }}</span></div>@empty<p>No badges earned yet.</p>@endforelse</div></div>
            </aside>
        </div>
    </div>

@php
    // Only this form's own validation failures should re-open it. Checking
    // $errors->any() re-opened the modal for completed profiles whenever any
    // other form in the app flashed an error.
    $aboutFields = ['date_of_birth', 'gender', 'education', 'bio', 'skills_have', 'skills_want'];
    $aboutHasErrors = $errors->hasAny($aboutFields);
@endphp

@if (($show_about_form ?? false) || $aboutHasErrors)
<div class="am-overlay" id="about-me-overlay">
    <div class="am-card">
        <div class="am-head ui-card-header">
            <h3>About Me</h3>
            <p>Tell us a bit about yourself — this personalizes your course recommendations.</p>
        </div>
        <div class="am-body">
            @if ($aboutHasErrors)
                <div class="am-errors">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:6px 0 0;padding-left:18px;">
                        @foreach ($aboutFields as $aboutField)
                            @foreach ($errors->get($aboutField) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('profile.complete') }}"
                  id="aboutMeForm" novalidate>
                @csrf
                <div class="am-row">
                    <div class="am-field">
                        <label for="am-dob">Date of Birth</label>
                        <input type="date" id="am-dob" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" required>
                        <p class="am-needs" data-for="date_of_birth">Needs to Fill</p>
                    </div>
                    <div class="am-field">
                        <label for="am-gender">Gender</label>
                        <select id="am-gender" name="gender" required>
                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select…</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Prefer not to say" {{ old('gender') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                        <p class="am-needs">Needs to Fill</p>
                    </div>
                </div>
                <div class="am-field">
                    <label for="am-education">Education</label>
                    <select id="am-education" name="education" required>
                        <option value="" disabled {{ old('education') ? '' : 'selected' }}>Select your highest attainment…</option>
                        @foreach (['Senior High School', 'High School Graduate', 'Vocational / Technical', 'Undergraduate (College)', "Bachelor's Degree", "Master's Degree", 'Doctorate / PhD'] as $level)
                            <option value="{{ $level }}" {{ old('education') === $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    <p class="am-needs">Needs to Fill</p>
                </div>
                <div class="am-field">
                    <label for="am-bio">Bio</label>
                    <textarea id="am-bio" name="bio" required placeholder="A short introduction about yourself…">{{ old('bio') }}</textarea>
                    <p class="am-needs">Needs to Fill</p>
                </div>
                {{-- Last school attended. "Other" reveals the text box below;
                     what is typed there is what gets stored. --}}
                <div class="am-field">
                    <label for="am-school">Last school, college, or university you attended</label>
                    <select id="am-school" name="school" required onchange="amSchoolChanged(this)">
                        <option value="" disabled {{ old('school') ? '' : 'selected' }}>Select your school…</option>
                        @foreach (\App\Support\SchoolCatalog::all() as $school)
                            <option value="{{ $school }}" {{ old('school') === $school ? 'selected' : '' }}>{{ $school }}</option>
                        @endforeach
                        <option value="{{ \App\Support\SchoolCatalog::OTHER }}" {{ old('school') === \App\Support\SchoolCatalog::OTHER ? 'selected' : '' }}>Other (please specify)</option>
                    </select>
                    <input type="text" id="am-school-other" name="school_other"
                           placeholder="Type the name of your school"
                           value="{{ old('school_other') }}"
                           style="margin-top:8px;{{ old('school') === \App\Support\SchoolCatalog::OTHER ? '' : 'display:none;' }}">
                    <p class="am-needs">Needs to Fill</p>
                </div>
                @php
                    $skillOptions = $skill_options ?? [
                        'HTML', 'CSS', 'JavaScript', 'Python', 'Java', 'PHP', 'SQL',
                        'Computer Networking', 'Video Editing', 'Photo Editing', 'Graphic Design',
                        'Data Analysis', 'Excel', 'Digital Marketing', 'Writing', 'Public Speaking',
                        'Communication', 'Leadership', 'Singing', 'Dancing', 'Drawing', 'Cooking',
                        'Hacking', 'Baking',
                    ];
                    $oldHave = (array) old('skills_have', []);
                    $oldWant = (array) old('skills_want', []);
                @endphp
                {{-- Skills pickers — same design as the faculty
                     "Subject is Related on" modal: a mini button that opens
                     a floating, searchable, grouped list with Save/Cancel. --}}
                @foreach ([
                    ['key' => 'have', 'field' => 'skills_have',  'label' => 'Skills You already Have?',
                     'saved' => $oldHave, 'hint' => 'Tick all that apply. These will also appear on your Profile skills list.',
                     'title' => 'Skills you already have'],
                    ['key' => 'want', 'field' => 'skills_want', 'label' => 'Skills You want to learn',
                     'saved' => $oldWant, 'hint' => 'We use this to recommend the most suitable courses for you.',
                     'title' => 'Skills you want to learn'],
                ] as $picker)
                    <div class="am-field">
                        <label>{{ $picker['label'] }}</label>

                        <div class="skillpick" data-picker="{{ $picker['key'] }}">
                            <button type="button"
                                    class="skillpick-mini ui-button ui-button--secondary {{ count($picker['saved']) ? 'has-value' : '' }}"
                                    onclick="openSkillPick('{{ $picker['key'] }}')">
                                <span class="skillpick-mini-label">
                                    {{ count($picker['saved']) ? count($picker['saved']) . ' selected' : 'None' }}
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>

                            <div class="skillpick-backdrop" hidden
                                 onclick="cancelSkillPick('{{ $picker['key'] }}')"></div>

                            <div class="skillpick-pop" hidden role="dialog" aria-modal="true"
                                 aria-label="{{ $picker['title'] }}">
                                <div class="skillpick-hd">
                                    <strong>{{ $picker['title'] }}</strong>
                                    <span>{{ $picker['hint'] }}</span>
                                </div>

                                <div class="skillpick-search">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                                    </svg>
                                    <input type="text" autocomplete="off"
                                           placeholder="Search {{ count($skillOptions) }} skills…"
                                           oninput="filterSkillPick('{{ $picker['key'] }}', this.value)">
                                </div>

                                <div class="skillpick-count">
                                    <span class="skillpick-count-line">No skills selected</span>
                                    <button type="button" class="skillpick-clearall ui-button ui-button--ghost ui-button--sm"
                                            onclick="clearSkillPick('{{ $picker['key'] }}')">Clear all</button>
                                </div>

                                <div class="skillpick-list">
                                    @foreach (($skill_groups ?? []) as $groupName => $groupSkills)
                                        <div class="skillpick-group">
                                            <div class="skillpick-group-hd">{{ $groupName }}</div>
                                            @foreach ($groupSkills as $skill)
                                                <label class="skillpick-opt {{ in_array($skill, $picker['saved']) ? 'on' : '' }}"
                                                       data-skill="{{ strtolower($skill) }}"
                                                       data-groupname="{{ strtolower($groupName) }}">
                                                    <input type="checkbox" name="{{ $picker['field'] }}[]"
                                                           value="{{ $skill }}"
                                                           @checked(in_array($skill, $picker['saved']))>
                                                    <span class="skillpick-tick">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2">
                                                            <path d="M5 12.5l5 5 9-10"/>
                                                        </svg>
                                                    </span>
                                                    <span class="skillpick-name">{{ $skill }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <div class="skillpick-empty" hidden>No skills match that search.</div>
                                </div>

                                <div class="skillpick-ft">
                                    <button type="button" class="skillpick-btn ui-button ui-button--secondary"
                                            onclick="cancelSkillPick('{{ $picker['key'] }}')">Cancel</button>
                                    <button type="button" class="skillpick-btn primary ui-button ui-button--primary"
                                            onclick="saveSkillPick('{{ $picker['key'] }}')">Save</button>
                                </div>
                            </div>
                        </div>

                        <div class="am-hint">{{ $picker['hint'] }}</div>
                        <p class="am-needs">Needs to Fill</p>
                    </div>
                @endforeach

                <div class="am-actions">
                    <button type="submit" class="am-save ui-button ui-button--primary">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


<script>
    /* ── Skills pickers ────────────────────────────────────────────────
       Two independent modals ("have" and "want") sharing one set of
       functions, keyed by data-picker. Cancel restores whatever was ticked
       when the modal opened, so it never loses an existing selection. */
    var _skillSnapshots = {};

    function skillRoot(key) {
        return document.querySelector('.skillpick[data-picker="' + key + '"]');
    }

    function skillBoxes(key) {
        var root = skillRoot(key);
        return root ? Array.prototype.slice.call(root.querySelectorAll('input[type="checkbox"]')) : [];
    }

    function syncSkillRows(key) {
        skillBoxes(key).forEach(function (box) {
            box.closest('.skillpick-opt').classList.toggle('on', box.checked);
        });
    }

    /* A few skills sit under two fields (Statistics, Logic, Policy,
       Manufacturing, Therapy) — keep both copies in step. */
    function mirrorSkill(key, box) {
        skillBoxes(key).forEach(function (other) {
            if (other !== box && other.value === box.value) other.checked = box.checked;
        });
    }

    function refreshSkillLabel(key) {
        var root = skillRoot(key);
        if (!root) return;

        var picked = {};
        skillBoxes(key).forEach(function (b) { if (b.checked) picked[b.value] = true; });
        var count = Object.keys(picked).length;

        var mini  = root.querySelector('.skillpick-mini');
        var label = root.querySelector('.skillpick-mini-label');
        var line  = root.querySelector('.skillpick-count-line');

        if (label) label.textContent = count ? count + ' selected' : 'None';
        if (mini)  mini.classList.toggle('has-value', count > 0);
        if (line)  line.textContent = count
            ? count + (count === 1 ? ' skill selected' : ' skills selected')
            : 'No skills selected';
    }

    function filterSkillPick(key, term) {
        var root = skillRoot(key);
        if (!root) return;
        term = (term || '').trim().toLowerCase();

        var anyVisible = false;

        root.querySelectorAll('.skillpick-group').forEach(function (group) {
            var groupVisible = false;

            group.querySelectorAll('.skillpick-opt').forEach(function (opt) {
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

        var empty = root.querySelector('.skillpick-empty');
        if (empty) empty.hidden = anyVisible;
    }

    function openSkillPick(key) {
        var root = skillRoot(key);
        if (!root) return;

        _skillSnapshots[key] = skillBoxes(key).filter(function (b) { return b.checked; })
                                              .map(function (b) { return b.value; });

        root.querySelector('.skillpick-pop').hidden = false;
        root.querySelector('.skillpick-backdrop').hidden = false;
        document.body.style.overflow = 'hidden';

        var search = root.querySelector('.skillpick-search input');
        if (search) { search.value = ''; }
        filterSkillPick(key, '');
        syncSkillRows(key);
        refreshSkillLabel(key);
        if (search) search.focus();
    }

    function closeSkillPick(key) {
        var root = skillRoot(key);
        if (!root) return;
        root.querySelector('.skillpick-pop').hidden = true;
        root.querySelector('.skillpick-backdrop').hidden = true;
        document.body.style.overflow = '';
    }

    function saveSkillPick(key) {
        refreshSkillLabel(key);
        closeSkillPick(key);
        if (window.revalidateAboutMe) window.revalidateAboutMe();
    }

    function cancelSkillPick(key) {
        var root = skillRoot(key);
        if (!root || root.querySelector('.skillpick-pop').hidden) return;

        var snap = _skillSnapshots[key] || [];
        skillBoxes(key).forEach(function (box) {
            box.checked = snap.indexOf(box.value) !== -1;
        });
        syncSkillRows(key);
        refreshSkillLabel(key);
        closeSkillPick(key);
    }

    function clearSkillPick(key) {
        skillBoxes(key).forEach(function (b) { b.checked = false; });
        syncSkillRows(key);
        refreshSkillLabel(key);
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['have', 'want'].forEach(function (key) {
            if (!skillRoot(key)) return;
            syncSkillRows(key);
            refreshSkillLabel(key);

            skillBoxes(key).forEach(function (box) {
                box.addEventListener('change', function () {
                    mirrorSkill(key, box);
                    syncSkillRows(key);
                    refreshSkillLabel(key);
                });
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            ['have', 'want'].forEach(function (key) {
                var root = skillRoot(key);
                if (root && !root.querySelector('.skillpick-pop').hidden) cancelSkillPick(key);
            });
        });
    });
</script>

<script>
    // Every About Me field is required. Native bubbles only surface one
    // field at a time and vanish on scroll, so mark each empty field with
    // its own "Needs to Fill" line instead.
    (function () {
        var form = document.getElementById('aboutMeForm');
        if (!form) return;

        function fieldOf(el) { return el.closest('.am-field'); }

        function markEmpty(wrap, empty) {
            if (!wrap) return;
            wrap.classList.toggle('invalid', empty);
        }

        function validate() {
            var firstBad = null;

            // Text, date, select and textarea inputs
            form.querySelectorAll('input[required], select[required], textarea[required]')
                .forEach(function (el) {
                    var empty = !String(el.value || '').trim();
                    markEmpty(fieldOf(el), empty);
                    if (empty && !firstBad) firstBad = el;
                });

            // The two skill pickers need at least one box ticked
            [['skills_have[]', 0], ['skills_want[]', 1]].forEach(function (pair) {
                var boxes = form.querySelectorAll('input[name="' + pair[0] + '"]');
                if (!boxes.length) return;
                var any = Array.prototype.some.call(boxes, function (b) { return b.checked; });
                var wrap = boxes[0].closest('.am-field');
                markEmpty(wrap, !any);
                if (!any && !firstBad) firstBad = boxes[0];
            });

            return firstBad;
        }

        form.addEventListener('submit', function (e) {
            var bad = validate();
            if (bad) {
                e.preventDefault();
                if (bad.focus) bad.focus();
                var wrap = bad.closest('.am-field');
                if (wrap && wrap.scrollIntoView) {
                    wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Clear a field's warning as soon as it is filled in
        form.addEventListener('input',  function () { validate(); });
        form.addEventListener('change', function () { validate(); });

        // Saving a skills modal should clear its warning immediately.
        window.revalidateAboutMe = validate;
    })();
</script>

{{-- ── Back to top (appears on long pages) ── --}}
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

    /* School picker — reveal the "please specify" box only for Other.
       The box is also marked required only while visible, so a hidden
       empty field can never block the form from submitting. */
    function amSchoolChanged(sel) {
        var other = document.getElementById('am-school-other');
        if (!other) return;

        var isOther = sel.value === @json(\App\Support\SchoolCatalog::OTHER);
        other.style.display = isOther ? 'block' : 'none';
        other.required = isOther;
        if (!isOther) other.value = '';
        else other.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var sel = document.getElementById('am-school');
        if (sel) amSchoolChanged(sel);
    });
</script>


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-layout>
