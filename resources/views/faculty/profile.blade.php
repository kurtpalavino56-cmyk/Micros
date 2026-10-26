{{--
    resources/views/faculty/profile.blade.php
    Faculty › My Profile — identity header, About me, Settings, and the Edit Profile drawer.

    Expects: $user (name, role, phone, email, joined, location, user_code, birth_date,
             birth_date_raw, gender, education, bio, about, language, timezone, avatar_url),
             $languages, $timezones, $performance, $activity, $profileCourses.
--}}
<x-faculty.layout title="My Profile | Upskill" data-faculty-page="faculty-profile">
    <x-slot:head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>

<x-flash-toast :message="session('success')" />

<main class="main">
        <div class="profile-page">

        <header class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">My Profile</h2>
                <p class="faculty-page-subtitle">Manage your personal information and account preferences</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-edit-profile" type="button" onclick="openEditModal('personal')">Edit profile</button>
            </div>
        </header>

        @php
            $has = fn ($v) => filled($v) && $v !== '-';
            $courseList = collect($profileCourses ?? []);
            $totalCourses = $courseList->count();
            $totalEnrollments = (int) $courseList->sum('students');
            $reviewTotal = (int) $courseList->sum('review_count');
            $avgRating = $reviewTotal > 0 ? round($courseList->sum(fn ($c) => $c->rating * $c->review_count) / $reviewTotal, 1) : null;
            $avgCompletion = $totalCourses ? (int) round($courseList->avg('completion')) : null;
            $checks = [
                ['label' => 'Add a profile photo', 'done' => $has($user->avatar_url ?? null), 'tab' => 'personal'],
                ['label' => 'Add your phone number', 'done' => $has($user->phone ?? null), 'tab' => 'personal'],
                ['label' => 'Add your location', 'done' => $has($user->location ?? null), 'tab' => 'personal'],
                ['label' => 'Write a short introduction', 'done' => $has($user->about ?? null), 'tab' => 'about'],
                ['label' => 'Add your date of birth', 'done' => $has($user->birth_date ?? null), 'tab' => 'about'],
                ['label' => 'Select your gender', 'done' => $has($user->gender ?? null), 'tab' => 'about'],
                ['label' => 'Add your education', 'done' => $has($user->education ?? null), 'tab' => 'about'],
                ['label' => 'Add your bio or skills', 'done' => $has($user->bio ?? null), 'tab' => 'about'],
            ];
            $pct = (int) round(collect($checks)->where('done', true)->count() / count($checks) * 100);
        @endphp

        {{-- Identity --}}
        <section class="hero">
            <div class="hero-cover" aria-hidden="true"></div>
            <div class="hero-body">
                <div class="avatar-wrap">
                    <div class="avatar-ring" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                        @unless($user->avatar_url ?? null)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        @endunless
                    </div>
                    <button class="avatar-edit" type="button" onclick="openEditModal('personal')" title="Change photo" aria-label="Change photo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    </button>
                </div>
                <div class="identity">
                    <h3>{{ $user->name ?? 'Faculty Member' }}<span class="role-pill">{{ $user->role ?? 'Faculty' }}</span></h3>
                    @if($has($user->education ?? null))<p class="headline">{{ $user->education }}</p>@endif
                    <div class="identity-grid">
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>{{ $user->phone ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>{{ $user->email ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $user->location ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Joined on {{ $user->joined ?? '-' }}</span>
                        <span class="id-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>{{ $user->user_code ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Profile completeness (hidden once everything is filled in) --}}
        @if($pct < 100)
            <section class="complete" aria-label="Profile completeness">
                <div class="complete-meter">
                    <div class="meter-top"><span>Your profile is {{ $pct }}% complete</span><span>{{ collect($checks)->where('done', true)->count() }}/{{ count($checks) }}</span></div>
                    <div class="meter-track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"><span class="meter-fill" style="width:{{ $pct }}%"></span></div>
                </div>
                <div class="todo-chips">
                    @foreach($checks as $check)
                        @unless($check['done'])<button type="button" class="chip" onclick="openEditModal('{{ $check['tab'] }}')">+ {{ $check['label'] }}</button>@endunless
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Teaching snapshot --}}
        <section class="stats" aria-label="Teaching snapshot">
            <div class="stat"><b>{{ $totalCourses }}</b><span>Courses created</span></div>
            <div class="stat"><b>{{ $totalEnrollments }}</b><span>Total enrollments</span></div>
            <div class="stat"><b>@if($avgRating !== null)<span class="star fac-profile-star">★</span> {{ number_format($avgRating, 1) }}@else — @endif</b><span>{{ $reviewTotal > 0 ? 'Average rating · '.$reviewTotal.' '.\Illuminate\Support\Str::plural('review', $reviewTotal) : 'No ratings yet' }}</span></div>
            <div class="stat"><b>{{ $avgCompletion !== null ? $avgCompletion.'%' : '—' }}</b><span>Average learner completion</span></div>
        </section>

        <div class="two-col">

            {{-- About me --}}
            <section class="panel profile-info-panel">
                <div class="profile-info-head"><h3>About me</h3></div>
                <div class="profile-info-body">
                @if($has($user->about ?? null))<p class="about-intro">{{ $user->about }}</p>
                @else<p class="about-intro"><button type="button" class="add-link" onclick="openEditModal('about')">Write a short introduction</button> so learners know who you are.</p>@endif
                <div class="row"><span class="row-label">Date of birth</span><span class="row-value">@if($has($user->birth_date ?? null)){{ $user->birth_date }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add date of birth</button>@endif</span></div>
                <div class="row"><span class="row-label">Gender</span><span class="row-value">@if($has($user->gender ?? null)){{ $user->gender }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Select gender</button>@endif</span></div>
                <div class="row"><span class="row-label">Education</span><span class="row-value">@if($has($user->education ?? null)){{ $user->education }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add education</button>@endif</span></div>
                <div class="row stack"><span class="row-label">Bio</span><span class="row-value">@if($has($user->bio ?? null)){{ $user->bio }}@else<button type="button" class="add-link" onclick="openEditModal('about')">Add your bio or skills</button>@endif</span></div>
                </div>
            </section>

            {{-- Settings --}}
            <section class="panel profile-info-panel">
                <div class="profile-info-head"><h3>Settings</h3></div>
                <div class="profile-info-body">
                <div class="row"><span class="row-label">Email</span><span class="row-value">{{ $user->email ?? '-' }}</span></div>
                <div class="row">
                    <span class="row-label">Password</span>
                    <span class="row-end"><span class="row-value">************</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                <div class="row">
                    <span class="row-label">Language</span>
                    <span class="row-end"><span class="row-value">{{ $user->language ?? 'English' }}</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                <div class="row">
                    <span class="row-label">Time zone</span>
                    <span class="row-end"><span class="row-value">{{ ($user->timezone ?? '') !== '' ? $user->timezone : '-' }}</span><button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button></span>
                </div>
                </div>
            </section>

        </div>

        {{-- My courses --}}
        <div class="section-head"><h3>My courses</h3><a href="{{ route('faculty.courses') }}">View all courses</a></div>
        @if($courseList->isNotEmpty())
            <div class="course-grid">
                @foreach($courseList as $course)
                    @php $st = strtolower($course->status ?? ''); $stClass = str_contains($st, 'publish') ? 'ok' : (str_contains($st, 'pending') ? 'warn' : ''); @endphp
                    <article class="course-card">
                        <div class="course-thumb" @if($course->thumbnail_url) style="background-image:url('{{ asset($course->thumbnail_url) }}')" @endif></div>
                        <div class="course-info">
                            <div class="course-meta"><span>{{ $course->category }}</span><span class="status-pill {{ $stClass }}">{{ $course->status }}</span></div>
                            <h4>{{ $course->title }}</h4>
                            <div class="course-stats">
                                <span><b>{{ $course->students }}</b> learners</span>
                                <span>@if($course->review_count > 0)<span class="star">★</span> <b>{{ number_format($course->rating, 1) }}</b> ({{ $course->review_count }})@else No ratings yet @endif</span>
                            </div>
                            <div class="progress" title="Average learner completion">
                                <span class="progress-track"><span class="progress-fill" style="width:{{ min(100, max(0, $course->completion)) }}%"></span></span>
                                <span>{{ $course->completion }}% avg. completion</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">You haven't created a course yet. <a href="{{ route('faculty.create') }}">Create your first course</a> and it will appear here.</div>
        @endif

        {{-- Teaching trends --}}
        @php
            $perf = collect($performance ?? []);
            $act = collect($activity ?? []);
            $actMax = max(1, (int) $act->max('hours'));
        @endphp
        <div class="section-head"><h3>Teaching trends</h3></div>
        <div class="trends">
            <section class="panel">
                <h3>New enrollments</h3>
                <p class="sub">Last 6 months, relative to your busiest month</p>
                @if($perf->sum('percent') > 0)
                    <div class="chart">
                        @foreach($perf as $m)
                            <div class="bar-col" title="{{ $m['label'] }}: {{ $m['percent'] }}% of peak">
                                <div class="bar-area"><span class="bar" style="height:{{ max(2, $m['percent']) }}%"></span></div>
                                <span class="bar-label">{{ $m['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state fac-empty-state-spacing">No enrollments in the last 6 months.</div>
                @endif
            </section>
            <section class="panel">
                <h3>Learner activity this week</h3>
                <p class="sub">Lesson, quiz and enrollment events from your learners</p>
                @if($act->sum('hours') > 0)
                    <div class="chart">
                        @foreach($act as $d)
                            <div class="bar-col" title="{{ $d['label'] }}: {{ $d['hours'] }} events">
                                <span class="bar-val">{{ $d['hours'] ?: '' }}</span>
                                <div class="bar-area"><span class="bar alt" style="height:{{ max(2, (int) round($d['hours'] / $actMax * 100)) }}%"></span></div>
                                <span class="bar-label">{{ $d['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state fac-empty-state-spacing">No learner activity recorded this week yet.</div>
                @endif
            </section>
        </div>

        </div>
    </main>

    <x-slot:scripts>
{{-- ===== EDIT PROFILE DRAWER ===== --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Edit profile">

        <div class="modal-header">
            <div><h3>Edit profile</h3><p>Update your personal information</p></div>
            <button class="modal-close" type="button" onclick="closeEditModal()" title="Close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-tabs" role="tablist">
            <button class="tab-btn active" type="button" role="tab" data-tab="personal" onclick="switchTab('personal')">Personal info</button>
            <button class="tab-btn" type="button" role="tab" data-tab="about" onclick="switchTab('about')">About me</button>
            <button class="tab-btn" type="button" role="tab" data-tab="settings" onclick="switchTab('settings')">Account settings</button>
        </div>

        <form id="editProfileForm" action="{{ route('faculty.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                @if ($errors->any())
                    <div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                {{-- Personal info --}}
                <div id="tab-personal" class="tab-content active">
                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <p>JPG, PNG or GIF, up to 10 MB.<br>Resized to 400 × 400 px automatically.</p>
                            <label class="btn-upload-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload photo
                                <input type="file" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        </div>
                    </div>

                    <p class="form-section-label">Basic information</p>
                    <div class="form-group">
                        <label for="edit_name">Full name</label>
                        <input type="text" id="edit_name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Enter your full name">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone number</label>
                            <input type="tel" id="edit_phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="e.g. 09XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input type="text" id="edit_location" name="location" value="{{ old('location', $user->location ?? '') }}" placeholder="City, Province">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role / title</label>
                        <input class="profile-readonly-field" type="text" id="edit_role" name="role" value="{{ old('role', $user->role ?? '') }}" placeholder="e.g. Faculty, Professor" readonly>
                    </div>
                </div>

                {{-- About me --}}
                <div id="tab-about" class="tab-content">
                    <div class="form-group">
                        <label for="edit_about">About me</label>
                        <textarea id="edit_about" name="about" rows="3" placeholder="Share a little about yourself...">{{ old('about', $user->about ?? '') }}</textarea>
                        <p class="hint">A short description shown on your profile.</p>
                    </div>
                    <hr class="form-divider">
                    <p class="form-section-label">Personal details</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_dob">Date of birth</label>
                            <input type="date" id="edit_dob" name="date_of_birth" value="{{ old('date_of_birth', $user->birth_date_raw ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male" @selected(old('gender', $user->gender ?? '') === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender', $user->gender ?? '') === 'Female')>Female</option>
                                <option value="Other" @selected(old('gender', $user->gender ?? '') === 'Other')>Other</option>
                                <option value="Prefer not to say" @selected(old('gender', $user->gender ?? '') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_education">Education</label>
                        <input type="text" id="edit_education" name="education" value="{{ old('education', $user->education ?? '') }}" placeholder="e.g. BS Information Technology">
                    </div>
                    <div class="form-group">
                        <label for="edit_bio">Bio / skills</label>
                        <textarea id="edit_bio" name="bio" rows="3" placeholder="List your skills, expertise, or career highlights...">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Account settings --}}
                <div id="tab-settings" class="tab-content">
                    <div class="settings-info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Leave the password fields empty if you don't want to change your password.</p>
                    </div>

                    <p class="form-section-label">Email address</p>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input class="profile-readonly-field" type="email" id="edit_email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="you@example.com" readonly>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Change password</p>
                    <div class="form-group">
                        <label for="edit_curr_pwd">Current password</label>
                        <div class="pwd-wrap">
                            <input type="password" id="edit_curr_pwd" name="current_password" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('edit_curr_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_new_pwd">New password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_new_pwd" name="password" placeholder="Min. 8 characters">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_new_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_confirm_pwd">Confirm password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_confirm_pwd" name="password_confirmation" placeholder="Repeat new password">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_confirm_pwd', this)" title="Show or hide password" aria-label="Show or hide password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Preferences</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_language">Language</label>
                            <select id="edit_language" name="language">
                                @foreach($languages ?? ['English'] as $lang)
                                    <option value="{{ $lang }}" @selected(old('language', $user->language ?? 'English') === $lang)>{{ $lang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_timezone">Time zone</label>
                            <select id="edit_timezone" name="timezone">
                                @foreach($timezones ?? [] as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $user->timezone ?? '') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-save">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ====== Edit profile drawer ====== */
let lastFocus = null;
function openEditModal(tab) {
    lastFocus = document.activeElement;
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
    setTimeout(() => { const c = document.querySelector('#editModal .modal-close'); if (c) c.focus(); }, 50);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
}
function handleOverlayClick(e) { if (e.target === document.getElementById('editModal')) closeEditModal(); }
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => { const on = b.dataset.tab === tab; b.classList.toggle('active', on); b.setAttribute('aria-selected', on); });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.toggle('active', c.id === 'tab-' + tab));
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

/* Avatar: resize client-side, then store in the hidden field (avoids PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else       { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';
            document.getElementById('avatarBase64').value = dataUrl;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEditModal(); });

/* Re-open the drawer if the save failed validation */
@if ($errors->any())
openEditModal(@json($errors->has('current_password') || $errors->has('password') || $errors->has('email') ? 'settings' : 'personal'));
@endif
</script>

<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

<script>
    (function () {
        var b = document.getElementById('back-to-top-btn');
        function t() { b.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true }); t();
    })();
</script>

@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
