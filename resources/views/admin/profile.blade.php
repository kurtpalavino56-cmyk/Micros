{{--
    resources/views/admin/Profile.blade.php

    Admin > My Profile.

    Rebuilt to use the SAME design language as Faculty_Profile: identity
    card, panel sections, and a slide-in Edit Profile drawer with tabbed
    sections. The stylesheet below is the faculty one, so the two pages
    stay visually identical.

    Differences from the faculty page, all content rather than design:
      - name is edited as first / middle / last / suffix, because
        AdminController::updateProfile validates the parts separately
      - Account Settings holds USERNAME as well as email and password
      - no courses table or performance charts

    Expects: $user (UserPresenter::admin)
--}}
<x-admin.layout title="Admin Profile | Upskill" page="admin-profile">
    <x-slot:head>
        {{-- Browser tab icon (favicon) --}}
            <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
{{-- ===== SUCCESS TOAST ===== --}}
<x-flash-toast :message="session('success')" />
<div class="layout">

    {{-- Admin sidebar — same collapsible sections as every other admin page --}}
{{-- Main content --}}
    <main class="main">
      <div class="admin-profile-page">

        <div class="page-head">
            <div>
                <h2>Admin Profile</h2>
                <p>Manage your administrative information and account details</p>
            </div>
            <div class="page-actions">
                <form action="{{ route('logout') }}" method="POST" class="adm-inline-admin-profile-1">
                    @csrf
                    <button class="btn-logout" type="submit">Logout</button>
                </form>
                <button class="btn-edit-profile" type="button" onclick="openEditModal('personal')">Edit Profile</button>
            </div>
        </div>

        {{-- ══════════════ IDENTITY CARD ══════════════ --}}
        <section class="profile-hero">
            <div class="profile-hero-cover" aria-hidden="true"></div>
            <div class="profile-hero-body">
                <div class="admin-avatar-wrap">
                    <div class="avatar-ring"
                         @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                        @unless($user->avatar_url ?? null)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        @endunless
                    </div>
                    <button class="avatar-edit" type="button" onclick="openEditModal('personal')" title="Change photo" aria-label="Change profile photo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    </button>
                </div>
                <div class="identity">
                <h3>{{ $user->name ?? 'Administrator' }}<span class="role-pill">{{ $user->role ?? 'Administrator' }}</span></h3>
                <div class="identity-grid">
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $user->phone ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                        {{ $user->email ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        {{ $user->username ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $user->location ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>
                        {{ $user->user_code ?? '—' }}
                    </span>
                </div>
                </div>
            </div>
        </section>

        {{-- ══════════════ ABOUT + ACCOUNT ══════════════ --}}
        <div class="two-col">

            <section class="panel profile-info-panel">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    </span>
                    <h4>About Me</h4>
                </div>
                <div class="panel-body">
                    <p class="about-intro">{{ $user->about ?: 'No summary provided yet.' }}</p>
                    <div class="about-row">
                        <span class="about-label">Bio</span>
                        <span class="about-value">{{ $user->bio ?: 'No bio provided yet.' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Role</span>
                        <span class="about-value">{{ $user->role ?? 'Administrator' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Member Since</span>
                        <span class="about-value">{{ optional($user->created_at)->format('M j, Y') ?? '—' }}</span>
                    </div>
                </div>
            </section>

            <section class="panel profile-info-panel">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <h4>Account</h4>
                </div>
                <div class="panel-body">
                    <div class="about-row">
                        <span class="about-label">Username</span>
                        <span class="about-value">{{ $user->username ?: '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Email</span>
                        <span class="about-value">{{ $user->email ?? '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Password</span>
                        <span class="about-value">••••••••</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Status</span>
                        <span class="about-value">{{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </section>

        </div>
      </div>
    </main>
</div>

{{-- ══════════════ EDIT PROFILE DRAWER ══════════════ --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel">

        <div class="modal-header">
            <div class="modal-header-left">
                <span class="head-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                </span>
                <div>
                    <h3>Edit Profile</h3>
                    <p>Update your personal information</p>
                </div>
            </div>
            <button class="modal-close" type="button" onclick="closeEditModal()" aria-label="Close">&times;</button>
        </div>

        <div class="modal-tabs">
            <button class="tab-btn active" data-tab="personal" onclick="switchTab('personal')">Personal Info</button>
            <button class="tab-btn" data-tab="about" onclick="switchTab('about')">About Me</button>
            <button class="tab-btn" data-tab="settings" onclick="switchTab('settings')">Account Settings</button>
        </div>

        <form id="editProfileForm" action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                @if ($errors->any())
                    <div class="form-errors">
                        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                {{-- ─────────── PERSONAL INFO ─────────── --}}
                <div id="tab-personal" class="tab-content active">

                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview"
                             @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <label class="btn-upload-avatar">
                                Change Photo
                                <input type="file" name="avatar" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            <button type="button" class="btn-remove-avatar" onclick="clearAvatar()">Remove</button>
                            <p class="hint">JPG or PNG. Resized automatically, so any size is fine.</p>
                        </div>
                        <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        <input type="hidden" id="removeAvatar" name="remove_avatar" value="0">
                    </div>

                    <div class="form-divider"></div>

                    {{-- The admin record stores the name in parts. Editing a
                         single combined field folded middle_name and suffix
                         into last_name on every save. --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
                            @error('first_name')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
                            @error('last_name')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <input id="suffix" name="suffix" value="{{ old('suffix', $user->suffix ?? '') }}" placeholder="Jr., III, ...">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input id="location" name="location" value="{{ old('location', $user->location ?? '') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role / Title</label>
                        <input class="profile-readonly-field" id="role" name="role" value="{{ old('role', $user->role ?? 'Administrator') }}" readonly>
                    </div>
                </div>

                {{-- ─────────── ABOUT ME ─────────── --}}
                <div id="tab-about" class="tab-content">
                    <div class="form-group">
                        <label for="about">About Me</label>
                        <textarea id="about" name="about" rows="4" placeholder="A short summary shown on your profile.">{{ old('about', $user->about ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Anything else worth noting.">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ─────────── ACCOUNT SETTINGS ─────────── --}}
                <div id="tab-settings" class="tab-content">

                    <div class="form-section-label">Sign-in Details</div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" value="{{ old('username', $user->username ?? '') }}" autocomplete="username" required>
                        <p class="hint-muted">Letters, numbers, dashes and underscores. You can sign in with this or your email.</p>
                        @error('username')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="profile-readonly-field" id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" readonly>
                    </div>

                    <div class="form-divider"></div>

                    <div class="form-section-label">Change Password</div>
                    <p class="hint-muted adm-inline-admin-profile-2">Leave these blank to keep your current password.</p>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="adm-inline-admin-profile-3">
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('current_password', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('current_password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="adm-inline-admin-profile-4">
                            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('password', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <div class="adm-inline-admin-profile-5">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Repeat the new password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('password_confirmation', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>{{-- /.modal-overlay --}}

<script>
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

/* Avatar — resize client-side then store in hidden field (bypasses PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else        { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }

            const canvas = document.createElement('canvas');
            canvas.width  = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';

            document.getElementById('avatarBase64').value = dataUrl;
            document.getElementById('removeAvatar').value = '0';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

/* Remove the photo — falls back to the silhouette used when none is set. */
function clearAvatar() {
    const preview = document.getElementById('avatarPreview');
    preview.style.backgroundImage = '';
    preview.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>';
    document.getElementById('avatarBase64').value = '';
    document.getElementById('removeAvatar').value = '1';
}

/* Keyboard: Esc closes modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeEditModal();
});

/* Re-open the drawer automatically if the save failed validation, on the
   tab that actually holds the offending field. */
@if ($errors->any())
openEditModal(@json(
    $errors->has('current_password') || $errors->has('password')
        || $errors->has('email') || $errors->has('username')
    ? 'settings' : 'personal'
));
@endif

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
</script>

<script>
    /* Collapsible sidebar sections — same helper the other admin pages use. */
    function toggleSection(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('open');
    }
</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-admin.layout>