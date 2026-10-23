{{--
    resources/views/admin/Announcements.blade.php
    Admin › Announcements (sidebar item under Analytics).
    Expects: $announcements (collection of objects).
--}}
<x-admin.layout title="Announcements | UPSKILL Admin" page="admin-announcements">
    <x-slot:head>
        {{-- Browser tab icon (favicon) --}}
            <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">

            @vite(['resources/js/app.js'])
    </x-slot:head>
@if(false)
<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('admin.dashboard') }}" class="topbar-brand">
        <span class="brand-logo"><img src="{{ asset('Images/PSU-Logo.png') }}" alt="PSU Logo"></span>
        UPSKILL
    </a>
    <div class="topbar-right">
        <a href="{{ route('notifications.index') }}" class="avatar-btn" title="Notifications">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
        </a>
        <a href="{{ route('admin.complaints') }}" class="avatar-btn" title="Complaint Inbox">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/>
            </svg>
        </a>
        <a href="{{ route('admin.profile') }}" class="avatar-btn" title="My Profile"
           @if(auth()->user()?->avatar_url)
               style="background-image:url('{{ auth()->user()->avatar_url }}');background-size:cover;background-position:center;overflow:hidden;"
           @endif>
            @unless(auth()->user()?->avatar_url)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V21.6h19.2V19.2c0-3.2-6.4-4.8-9.6-4.8z"/>
            </svg>
            @endunless
        </a>
        <form action="{{ route('logout') }}" method="POST" class="adm-inline-admin-announcements-1">
            @csrf
            <button type="submit" class="btn-danger">Logout</button>
        </form>
    </div>
</nav>
@endif

<div class="wrap">
    <div class="page-intro">
        <h1 class="page-heading">Announcements</h1>
        <p class="page-sub">Post an announcement and choose who is allowed to see it.</p>
    </div>

    <x-flash-toast :message="session('success')" />
    @if ($errors->any())
        <div class="alert alert-err">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    {{-- ── New announcement ── --}}
    <div class="card">
        <h2>New Announcement</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}">
            @csrf
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                       placeholder="Announcement title" required>
            </div>
            <div class="field">
                <label for="body">Description</label>
                <div class="announcement-editor">
                    <x-rich-text-editor id="announcement-body" name="body" :value="old('body')" placeholder="What do you want to announce?" preset="announcement" />
                </div>
            </div>
            <div class="field">
                <div class="audience-row">
                    <span class="audience-label">Who Can See Your Announcement</span>
                    <div class="audience-opts">
                        <label><input type="checkbox" name="audience[]" value="public"
                            @checked(in_array('public', old('audience', ['public']), true))> Public</label>
                        <label><input type="checkbox" name="audience[]" value="student"
                            @checked(in_array('student', old('audience', ['public']), true))> Student</label>
                        <label><input type="checkbox" name="audience[]" value="faculty"
                            @checked(in_array('faculty', old('audience', ['public']), true))> Faculty</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-navy">Post Announcement</button>
        </form>
    </div>

    {{-- ── Existing announcements ── --}}
    <div class="card">
        <h2>Posted Announcements</h2>

        @forelse ($announcements as $a)
            <div class="ann">
                <div class="ann-head">
                    <div class="ann-copy">
                        <h3 class="ann-title">{{ $a->title }}</h3>
                        <template id="announcement-content-{{ $a->id }}"><div class="ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($a->body) !!}</div></template>
                        <div>
                            @foreach ($a->audience as $role)
                                <span class="pill {{ $role }}">{{ ucfirst($role) }}</span>
                            @endforeach
                            @if ($a->is_pinned)
                                <span class="pill pinned">Pinned</span>
                            @endif
                        </div>
                        <div class="ann-meta adm-inline-admin-announcements-2">
                            By {{ $a->author }} · {{ $a->created_at?->format('M j, Y g:i A') }}
                        </div>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn-view" data-view-announcement="{{ $a->id }}"
                                data-title="{{ $a->title }}"
                                data-meta="By {{ $a->author }} · {{ $a->created_at?->format('M j, Y g:i A') }}"
                                aria-label="View full announcement: {{ $a->title }}"
                                title="View full announcement">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5C6.5 4.5 2.3 8.1.7 12c1.6 3.9 5.8 7.5 11.3 7.5s9.7-3.6 11.3-7.5C21.7 8.1 17.5 4.5 12 4.5zm0 12a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z"/><circle cx="12" cy="12" r="2.2" fill="#fff"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.announcements.pin', $a->id) }}">
                            @csrf
                            <button type="submit" class="btn-pin {{ $a->is_pinned ? 'is-pinned' : '' }}"
                                aria-label="{{ $a->is_pinned ? 'Unpin' : 'Pin' }} {{ $a->title }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 9V4l1-1V2H7v1l1 1v5c0 1-2 2-3 3v1h6v8h2v-8h6v-1c-1-1-3-2-3-3Z"/></svg>
                                {{ $a->is_pinned ? 'Unpin' : 'Pin' }}
                            </button>
                        </form>
                        <button type="button" class="btn-ghost" data-edit-announcement="{{ $a->id }}"
                                aria-label="Edit announcement: {{ $a->title }}">Edit</button>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $a->id) }}"
                              onsubmit="return confirm('Delete this announcement?');">
                            @csrf
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="announcement-edit-modal" id="edit-modal-{{ $a->id }}" hidden>
                    <div class="announcement-modal-backdrop" data-close-edit></div>
                    <section class="announcement-edit-dialog" role="dialog" aria-modal="true"
                             aria-labelledby="edit-dialog-title-{{ $a->id }}" tabindex="-1">
                        <header class="announcement-dialog-head">
                            <h2 id="edit-dialog-title-{{ $a->id }}">Edit Announcement</h2>
                            <button class="announcement-dialog-close" type="button" data-close-edit
                                    aria-label="Close edit announcement form">&times;</button>
                        </header>
                        <form method="POST" action="{{ route('admin.announcements.update', $a->id) }}"
                              id="edit-{{ $a->id }}" data-ckeditor-deferred>
                            @csrf
                            @method('PATCH')
                            <div class="field">
                                <label for="edit-title-{{ $a->id }}">Title</label>
                                <input type="text" id="edit-title-{{ $a->id }}" name="title" value="{{ $a->title }}" required>
                            </div>
                            <div class="field">
                                <label for="announcement-body-{{ $a->id }}">Description</label>
                                <div class="announcement-editor">
                                    <x-rich-text-editor :id="'announcement-body-'.$a->id" name="body" :value="$a->body" placeholder="What do you want to announce?" preset="announcement" />
                                </div>
                            </div>
                            <div class="field">
                                <div class="audience-row">
                                    <span class="audience-label">Who Can See Your Announcement</span>
                                    <div class="audience-opts">
                                        <label><input type="checkbox" name="audience[]" value="public"
                                            @checked(in_array('public', $a->audience, true))> Public</label>
                                        <label><input type="checkbox" name="audience[]" value="student"
                                            @checked(in_array('student', $a->audience, true))> Student</label>
                                        <label><input type="checkbox" name="audience[]" value="faculty"
                                            @checked(in_array('faculty', $a->audience, true))> Faculty</label>
                                    </div>
                                </div>
                            </div>
                            <div class="announcement-edit-actions">
                                <button type="button" class="btn-cancel" data-close-edit>Cancel</button>
                                <button type="submit" class="btn btn-navy">Save Changes</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        @empty
            <div class="empty">No announcements yet.</div>
        @endforelse
    </div>
</div>

<div class="announcement-modal" id="announcement-modal" hidden>
    <div class="announcement-modal-backdrop" data-close-announcement></div>
    <section class="announcement-dialog" role="dialog" aria-modal="true" aria-labelledby="announcement-dialog-title" tabindex="-1">
        <header class="announcement-dialog-head">
            <div>
                <h2 id="announcement-dialog-title"></h2>
                <p class="announcement-dialog-meta" id="announcement-dialog-meta"></p>
            </div>
            <button class="announcement-dialog-close" type="button" data-close-announcement aria-label="Close announcement">&times;</button>
        </header>
        <div class="announcement-dialog-content ck-content rich-content" id="announcement-dialog-content"></div>
    </section>
</div>

<script>
    (function () {
        var modal = document.getElementById('announcement-modal');
        var dialog = modal.querySelector('.announcement-dialog');
        var title = document.getElementById('announcement-dialog-title');
        var meta = document.getElementById('announcement-dialog-meta');
        var content = document.getElementById('announcement-dialog-content');
        var previousFocus = null;
        var activeEditModal = null;

        function closeEditModal(modal) {
            if (!modal || modal.hidden) return;

            modal.hidden = true;
            document.body.classList.remove('announcement-modal-open');
            if (previousFocus) previousFocus.focus();
            activeEditModal = null;
        }

        function closeAnnouncement() {
            modal.hidden = true;
            document.body.classList.remove('announcement-modal-open');
            content.replaceChildren();
            if (previousFocus) previousFocus.focus();
        }

        document.querySelectorAll('[data-view-announcement]').forEach(function (button) {
            button.addEventListener('click', function () {
                var template = document.getElementById('announcement-content-' + button.dataset.viewAnnouncement);
                if (!template) return;

                previousFocus = button;
                title.textContent = button.dataset.title || 'Announcement';
                meta.textContent = button.dataset.meta || '';
                content.replaceChildren(template.content.cloneNode(true));
                modal.hidden = false;
                document.body.classList.add('announcement-modal-open');
                dialog.focus();
            });
        });

        modal.querySelectorAll('[data-close-announcement]').forEach(function (button) {
            button.addEventListener('click', closeAnnouncement);
        });

        document.querySelectorAll('[data-edit-announcement]').forEach(function (button) {
            button.addEventListener('click', function () {
                var editModal = document.getElementById('edit-modal-' + button.dataset.editAnnouncement);
                var form = editModal && editModal.querySelector('form');
                var editDialog = editModal && editModal.querySelector('[role="dialog"]');

                if (!editModal || !form || !editDialog) return;

                previousFocus = button;
                activeEditModal = editModal;
                editModal.hidden = false;
                document.body.classList.add('announcement-modal-open');
                form.removeAttribute('data-ckeditor-deferred');
                window.initializeUpskillEditors(form);
                editDialog.focus();
            });
        });

        document.querySelectorAll('[data-close-edit]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeEditModal(button.closest('.announcement-edit-modal'));
            });
        });

        document.querySelectorAll('.announcement-edit-modal').forEach(function (editModal) {
            editModal.addEventListener('click', function (event) {
                if (event.target === editModal.querySelector('.announcement-modal-backdrop')) {
                    closeEditModal(editModal);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            if (activeEditModal) {
                closeEditModal(activeEditModal);
            } else if (!modal.hidden) {
                closeAnnouncement();
            }
        });
    })();

    document.querySelectorAll('.audience-opts').forEach(function (group) {
        var publicOption = group.querySelector('input[value="public"]');
        var roleOptions = group.querySelectorAll('input[value="student"], input[value="faculty"]');

        if (!publicOption) return;

        publicOption.addEventListener('change', function () {
            if (publicOption.checked) {
                roleOptions.forEach(function (option) { option.checked = false; });
            }
        });

        roleOptions.forEach(function (option) {
            option.addEventListener('change', function () {
                if (option.checked) publicOption.checked = false;
            });
        });
    });

</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-admin.layout>