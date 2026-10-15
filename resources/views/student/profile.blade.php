{{--
    resources/views/student/Profile.blade.php

    Expected data from the controller, e.g.:

    return view('student.profile', [
        'user' => $user, // Auth::user() with the fields used below
        'stats' => [
            'courses_enrolled' => $coursesEnrolledCount,
            'badges_earned'    => $badgesEarnedCount,
            'certificates'     => $certificatesCount,
            'hours_learned'    => $hoursLearned,
        ],
        'progress' => [
            'completed' => $completedCoursesCount,
            'total'     => $enrolledCoursesCount,
        ],
        'achievements' => $achievements, // collection, can be empty
        'activities'   => $recentActivities, // collection, can be empty
    ]);

    $user is expected to expose:
        ->name, ->role, ->phone, ->email, ->joined_at (Carbon), ->location,
        ->avatar_url, ->about, ->date_of_birth, ->gender, ->education, ->bio,
        ->language, ->timezone

    Styling note: the page heading block (.profile-head) is intentionally
    left exactly as it was. Everything else uses the Inter-based card system.
--}}
<x-layout title="My Profile | Upskill" shell="student" page="profile">




{{-- ===== SUCCESS / ERROR TOAST ===== --}}
<x-flash-toast :message="session('success')" />
{{-- Sidebar --}}
{{-- Main content --}}
    <div class="page-content">

        <div class="profile-head student-page-heading page-header ui-page-header">
            <div>
                <h2 class="ui-page-title">My Profile</h2>
                <p class="ui-page-subtitle">Manage your Personal Information and Account Preferences</p>
            </div>
            <button class="btn-outline ui-button ui-button--secondary" type="button" onclick="openEditModal('personal')">Edit Profile</button>
        </div>

        <div class="profile-grid">

            {{-- Left column --}}
            <div class="profile-col">

                {{-- Profile info card --}}
                <div class="card-block ui-card-surface profile-card">
                    <div class="profile-cover" aria-hidden="true"></div>
                    <div class="profile-info">
                        <div class="avatar-circle" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="profile-meta">
                            <h3>{{ $user->name ?? 'Student' }}</h3>
                            <div class="meta-row">
                                @if($user->role ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg>
                                        {{ $user->role }}
                                    </span>
                                @endif
                                @if($user->phone ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.84.57 2.8.7A2 2 0 0 1 22 16.92z"/></svg>
                                        {{ $user->phone }}
                                    </span>
                                @endif
                                @if($user->email ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 6 10-6"/></svg>
                                        {{ $user->email }}
                                    </span>
                                @endif
                                @if($user->joined_at ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        Joined on {{ $user->joined_at->format('M d, Y') }}
                                    </span>
                                @endif
                                @if($user->location ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        {{ $user->location }}
                                    </span>
                                @endif
                                @if($user->student_id ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>
                                        {{ $user->student_id }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- About me card --}}
                <div class="card-block ui-card-surface">
                    <div class="ui-card-header">
                        <h4 class="section-title ui-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            About me
                        </h4>
                    </div>
                    @if($user->about ?? null)
                        <p class="about-text">{{ $user->about }}</p>
                    @endif
                    @if($user->date_of_birth ?? null)
                        <div class="detail-row">
                            <span class="label">Date of birth</span>
                            <span class="value">{{ $user->date_of_birth }}</span>
                        </div>
                    @endif
                    @if($user->gender ?? null)
                        <div class="detail-row">
                            <span class="label">Gender</span>
                            <span class="value">{{ $user->gender }}</span>
                        </div>
                    @endif
                    @if($user->education ?? null)
                        <div class="detail-row">
                            <span class="label">Education</span>
                            <span class="value">{{ $user->education }}</span>
                        </div>
                    @endif
                    @if($user->school ?? null)
                        <div class="detail-row">
                            <span class="label">School</span>
                            <span class="value">{{ $user->school }}</span>
                        </div>
                    @endif
                    @if($user->bio ?? null)
                        <div class="detail-row">
                            <span class="label">Bio</span>
                            <span class="value">{{ $user->bio }}</span>
                        </div>
                    @endif

                    {{-- Skills list (from the About Me form) --}}
                    <div class="skills-block">
                        <div class="skills-group">
                            <span class="skills-title">Skills I have</span>
                            <div class="skills-chips">
                                @forelse ($user->skills_have ?? [] as $skill)
                                    <span class="skill-chip skill-chip-have">{{ $skill }}</span>
                                @empty
                                    <span class="skills-empty">No skills listed yet.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="skills-group">
                            <span class="skills-title">Skills I want to learn</span>
                            <div class="skills-chips">
                                @forelse ($user->skills_want ?? [] as $skill)
                                    <span class="skill-chip skill-chip-want">{{ $skill }}</span>
                                @empty
                                    <span class="skills-empty">No learning goals listed yet.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="profile-side">

                <div class="quick-grid">
                    <a href="{{ route('courses.enrolled') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Courses Enrolled
                    </a>
                    <a href="{{ route('badges.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z"/></svg>
                        Badges Earned
                    </a>
                    <a href="{{ route('certificates.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M8 13l-2 8 6-3 6 3-2-8"/></svg>
                        Certificates
                    </a>
                    <a href="{{ route('analytics.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        Hours Learned
                    </a>
                </div>

                <div class="panel-card">
                    <div class="panel-card-head ui-card-header">
                        <h4 class="ui-card-header__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10l-1 8a4 4 0 0 1-8 0L7 4z"/></svg>
                            Achievements
                        </h4>
                        <a href="{{ route('badges.index') }}">View all</a>
                    </div>
                    <div class="panel-card-body">
                        @forelse ($achievements ?? [] as $achievement)
                            <p>{{ $achievement }}</p>
                        @empty
                            No achievements to show yet.
                        @endforelse
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-head ui-card-header">
                        <h4 class="ui-card-header__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                            Recent activities
                        </h4>
                        <a href="{{ route('analytics.index') }}">View all</a>
                    </div>
                    <div class="panel-card-body">
                        @forelse ($activities ?? [] as $activity)
                            <p>{{ $activity }}</p>
                        @empty
                            No recent activity yet.
                        @endforelse
                    </div>
                </div>

                {{-- ===== STUDENT ID ===== --}}
                @php
                    // The student's real ID number stored on the users table.
                    $studentId = $user->student_id ?? '-';
                @endphp
                <div class="panel-card">
                    <div class="panel-card-head ui-card-header">
                        <h4 class="ui-card-header__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><path d="M5.5 17a3 3 0 0 1 5 0"/><path d="M14 9h5M14 13h5"/></svg>
                            Student ID
                        </h4>
                    </div>
                    <div class="panel-card-body student-id-body">
                        <div>
                            <div class="student-id-number">{{ $studentId }}</div>
                            <div class="student-id-label">Your unique student identification number</div>
                        </div>
                        <button type="button" class="student-id-copy ui-button ui-button--ghost ui-button--sm" onclick="copyStudentId(this, '{{ $studentId }}')">Copy</button>
                    </div>
                </div>

            </div>

        </div>

    </div>

{{-- ===== EDIT PROFILE MODAL ===== --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Edit Profile">

        {{-- Header --}}
        <div class="modal-header ui-card-header">
            <div class="modal-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <div>
                    <h3>Edit profile</h3>
                    <p>Update your personal information</p>
                </div>
            </div>
            <button class="modal-close ui-button ui-button--ghost ui-button--sm" onclick="closeEditModal()" title="Close" aria-label="Close">&times;</button>
        </div>

        {{-- Tabs --}}
        <div class="modal-tabs ui-tabs">
            <button class="tab-btn ui-tab active" data-tab="personal" onclick="switchTab('personal')">
                Personal Info
            </button>
            <button class="tab-btn ui-tab" data-tab="about" onclick="switchTab('about')">
                About Me
            </button>
            <button class="tab-btn ui-tab" data-tab="settings" onclick="switchTab('settings')">
                Account Settings
            </button>
        </div>

        {{-- Form --}}
        <form id="editProfileForm"
              action="{{ route('profile.update') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                {{-- TAB: Personal Info --}}
                <div id="tab-personal" class="tab-content active">

                    {{-- Avatar Upload --}}
                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview"
                            @if($user->avatar_url ?? null)
                                style="background-image:url('{{ $user->avatar_url }}')"
                            @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <p>JPG, PNG or GIF - Up to 10 MB<br>Auto-resized to 400 x 400 px</p>
                            <label class="btn-upload-avatar ui-button ui-button--secondary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Photo
                                {{-- No name="avatar": JS resizes and stores the image in the hidden field below --}}
                                <input type="file" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            {{-- Client-resized base64 image sent here instead of raw file --}}
                            <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        </div>
                    </div>

                    <p class="form-section-label">Basic information</p>

                    <div class="form-group">
                        <label for="edit_name">Full Name</label>
                        <input class="ui-input" type="text" id="edit_name" name="name"
                            value="{{ old('name', $user->name ?? '') }}"
                            placeholder="Enter your full name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone Number</label>
                            <input class="ui-input" type="tel" id="edit_phone" name="phone"
                                value="{{ old('phone', $user->phone ?? '') }}"
                                placeholder="e.g. 09XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input class="ui-input" type="text" id="edit_location" name="location"
                                value="{{ old('location', $user->location ?? '') }}"
                                placeholder="City, Province">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_role">Role / Title</label>
                        <input class="ui-input profile-readonly-field" type="text" id="edit_role" name="role"
                            value="{{ old('role', $user->role ?? '') }}"
                            placeholder="e.g. Student, Developer" readonly>
                    </div>
                </div>

                {{-- TAB: About Me --}}
                <div id="tab-about" class="tab-content">

                    <div class="form-group">
                        <label for="edit_about">About Me</label>
                        <textarea id="edit_about" name="about" rows="3"
                            placeholder="Share a little about yourself..." class="ui-textarea">{{ old('about', $user->about ?? '') }}</textarea>
                        <p class="hint">Brief description shown on your profile card.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Personal details</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_dob">Date of Birth</label>
                            <input class="ui-input" type="date" id="edit_dob" name="date_of_birth"
                                value="{{ old('date_of_birth', isset($user->date_of_birth) ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender" class="ui-select">
                                <option value="">Select...</option>
                                <option value="Male"   @selected(($user->gender ?? '') === 'Male')>Male</option>
                                <option value="Female" @selected(($user->gender ?? '') === 'Female')>Female</option>
                                <option value="Other"  @selected(($user->gender ?? '') === 'Other')>Other</option>
                                <option value="Prefer not to say" @selected(($user->gender ?? '') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_education">Education</label>
                        <input class="ui-input" type="text" id="edit_education" name="education"
                            value="{{ old('education', $user->education ?? '') }}"
                            placeholder="e.g. BS Information Technology">
                    </div>

                    <div class="form-group">
                        <label for="edit_school">Last school, college, or university you attended</label>
                        @php
                            $storedSchool   = old('school', \App\Support\SchoolCatalog::selectedOption($user->school ?? null));
                            $storedSchoolOther = old('school_other', \App\Support\SchoolCatalog::otherText($user->school ?? null));
                        @endphp
                        <select id="edit_school" name="school" onchange="editSchoolChanged(this)" class="ui-select">
                            <option value="">Select your school...</option>
                            @foreach (\App\Support\SchoolCatalog::all() as $school)
                                <option value="{{ $school }}" {{ $storedSchool === $school ? 'selected' : '' }}>{{ $school }}</option>
                            @endforeach
                            <option value="{{ \App\Support\SchoolCatalog::OTHER }}" {{ $storedSchool === \App\Support\SchoolCatalog::OTHER ? 'selected' : '' }}>Other (please specify)</option>
                        </select>
                        <input class="ui-input" type="text" id="edit_school_other" name="school_other"
                            value="{{ $storedSchoolOther }}"
                            placeholder="Type the name of your school"
                            style="margin-top:8px;{{ $storedSchool === \App\Support\SchoolCatalog::OTHER ? '' : 'display:none;' }}">
                    </div>

                    <div class="form-group">
                        <label for="edit_bio">Bio</label>
                        <textarea id="edit_bio" name="bio" rows="3"
                            placeholder="Share a short bio..." class="ui-textarea">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>

                {{-- Skills pickers: same floating, searchable modal used by
                     the About Me form and the faculty course form. Posting
                     arrays is fine: updateProfile()'s splitSkills() accepts
                     either an array or a comma-separated string. --}}
                @php
                    $haveSaved = old('skills_have', $user->skills_have ?? []);
                    $wantSaved = old('skills_want', $user->skills_want ?? []);
                    $haveSaved = is_array($haveSaved) ? $haveSaved : array_filter(array_map('trim', explode(',', $haveSaved)));
                    $wantSaved = is_array($wantSaved) ? $wantSaved : array_filter(array_map('trim', explode(',', $wantSaved)));
                @endphp

                @foreach ([
                    ['key' => 'have', 'field' => 'skills_have', 'label' => 'Skills I Have',
                     'saved' => $haveSaved, 'title' => 'Skills I have',
                     'hint' => 'Shown on your profile skills list.'],
                    ['key' => 'want', 'field' => 'skills_want', 'label' => 'Skills I Want to Learn',
                     'saved' => $wantSaved, 'title' => 'Skills I want to learn',
                     'hint' => 'Used to personalize your course recommendations.'],
                ] as $picker)
                    <div class="form-group">
                        <label>{{ $picker['label'] }}</label>

                        <input type="hidden" name="{{ $picker['field'] }}_submitted" value="1">

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
                                    <input class="ui-input" type="text" autocomplete="off"
                                           placeholder="Search {{ count($skill_options ?? []) }} skills..."
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

                        <p class="hint">{{ $picker['hint'] }}</p>
                    </div>
                @endforeach
                </div>

                {{-- TAB: Account Settings --}}
                <div id="tab-settings" class="tab-content">

                    <div class="settings-info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Leave the password fields empty if you don't want to change your password.</p>
                    </div>

                    <p class="form-section-label">Email address</p>

                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input class="ui-input profile-readonly-field" type="email" id="edit_email" name="email"
                            value="{{ old('email', $user->email ?? '') }}"
                            placeholder="you@example.com" readonly>
                        <p class="hint">Email changes are managed by an administrator.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Change password</p>

                    <div class="form-group">
                        <label for="edit_curr_pwd">Current Password</label>
                        <div class="pwd-wrap">
                            <input class="ui-input" type="password" id="edit_curr_pwd" name="current_password" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle ui-button ui-button--ghost ui-button--sm" onclick="togglePwd('edit_curr_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_new_pwd">New Password</label>
                            <div class="pwd-wrap">
                                <input class="ui-input" type="password" id="edit_new_pwd" name="password" placeholder="Min. 8 characters">
                                <button type="button" class="pwd-toggle ui-button ui-button--ghost ui-button--sm" onclick="togglePwd('edit_new_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_confirm_pwd">Confirm Password</label>
                            <div class="pwd-wrap">
                                <input class="ui-input" type="password" id="edit_confirm_pwd" name="password_confirmation" placeholder="Repeat new password">
                                <button type="button" class="pwd-toggle ui-button ui-button--ghost ui-button--sm" onclick="togglePwd('edit_confirm_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Preferences</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_language">Language</label>
                            <select id="edit_language" name="language" class="ui-select">
                                <option value="English"  @selected(($user->language ?? 'English') === 'English')>English</option>
                                <option value="Filipino" @selected(($user->language ?? '') === 'Filipino')>Filipino</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_timezone">Time Zone</label>
                            <select id="edit_timezone" name="timezone" class="ui-select">
                                <option value="Asia/Manila"      @selected(($user->timezone ?? 'Asia/Manila') === 'Asia/Manila')>(GMT+8:00) Asia/Manila</option>
                                <option value="UTC"              @selected(($user->timezone ?? '') === 'UTC')>UTC</option>
                                <option value="America/New_York" @selected(($user->timezone ?? '') === 'America/New_York')>(GMT-5:00) New York</option>
                                <option value="Europe/London"    @selected(($user->timezone ?? '') === 'Europe/London')>(GMT+0:00) London</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel ui-button ui-button--secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save ui-button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>

        </form>{{-- /form --}}
    </div>{{-- /.modal-panel --}}
</div>{{-- /.modal-overlay --}}

<script>
/* ====== Student ID copy ====== */
function copyStudentId(btn, id) {
    const done = () => {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = original; }, 1500);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(id).then(done);
    } else {
        const ta = document.createElement('textarea');
        ta.value = id;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        done();
    }
}

/* ====== Edit Profile Modal ====== */
function openEditModal(tab) {
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
}
function handleOverlayClick(e) {
    if (e.target === document.getElementById('editModal')) closeEditModal();
}
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    const panel = document.getElementById(`tab-${tab}`);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');
}

/* Password show/hide */
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
}

/* Avatar: resize client-side then store in hidden field (bypasses PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Resize to max 400 x 400 keeping aspect ratio
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else        { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }

            const canvas = document.createElement('canvas');
            canvas.width  = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            // Compress to JPEG 85%: typically < 50 KB regardless of original size
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            // Live preview in modal
            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';

            // Put compressed base64 in hidden field so it's submitted with the form
            document.getElementById('avatarBase64').value = dataUrl;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

/* Keyboard: Esc closes modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeEditModal();
});

</script>


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

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
<script>
    /* ── Skills pickers ──────────────────────────────────────────────
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
       Manufacturing, Therapy): keep both copies in step. */
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


    /* School picker: the "please specify" box only shows for Other. */
    function editSchoolChanged(sel) {
        var other = document.getElementById('edit_school_other');
        if (!other) return;
        var isOther = sel.value === @json(\App\Support\SchoolCatalog::OTHER);
        other.style.display = isOther ? 'block' : 'none';
        if (!isOther) other.value = '';
        else other.focus();
    }
</script>

</x-layout>
