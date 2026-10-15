{{--
    resources/views/admin/Announcements.blade.php
    Admin › Announcements (sidebar item under Analytics).
    Expects: $announcements (collection of objects).
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Announcements | UPSKILL Admin</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;
          --shadow:0 10px 25px rgba(19,23,107,.08);--topbar-h:60px;}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f5f7ff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .topbar{position:sticky;top:0;height:var(--topbar-h);background:var(--navy);display:flex;
        align-items:center;justify-content:space-between;padding:0 22px;z-index:200;
        box-shadow:0 2px 8px rgba(0,0,0,.25);}
    .topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:1.05rem;
        font-weight:800;letter-spacing:2px;text-transform:uppercase;}
    .brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;overflow:hidden;}
    .brand-logo img{width:100%;height:100%;object-fit:contain;}
    .topbar-right{display:flex;align-items:center;gap:10px;}
    .avatar-btn{width:40px;height:40px;border-radius:50%;background:#fff;color:var(--navy);
        display:inline-flex;align-items:center;justify-content:center;}
    /* Matches the padding the other admin pages use (30px 34px 44px). */
    .wrap{max-width:1080px;margin:0 auto;padding:34px 40px 52px;}
    .page-intro{margin-bottom:32px;}
    .page-heading{font-size:1.6rem;font-weight:800;margin:0 0 4px;color:#000;}
    .page-sub{color:var(--muted);font-size:.92rem;margin:0;}
    .card{background:#fff;border:1px solid var(--line);border-radius:18px;
        box-shadow:var(--shadow);padding:24px 26px;margin-bottom:22px;}
    .card h2{margin:0 0 16px;font-size:1.05rem;}
    label{display:block;font-size:12px;font-weight:800;letter-spacing:.4px;
        text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    input[type=text],textarea{width:100%;border:1.5px solid #c9ccdb;border-radius:10px;
        padding:11px 13px;font-size:14px;font-family:inherit;color:var(--navy);background:#fff;}
    textarea{min-height:110px;resize:vertical;}
    .announcement-editor .ck-editor__main>.ck-editor__editable{min-height:150px;padding:12px 14px;color:var(--navy);font-family:inherit;font-size:14px;line-height:1.6;}
    .announcement-editor .ck.ck-toolbar{border-color:#c9ccdb;background:#f7f9ff;}
    .announcement-editor .ck.ck-editor__main>.ck-editor__editable{border-color:#c9ccdb;}
    input[type=text]:focus,textarea:focus{outline:none;border-color:var(--navy);}
    .field{margin-bottom:16px;}
    /* "Who Can See Your Announcement" — audience checkboxes */
    .audience-row{display:flex;align-items:center;gap:18px;flex-wrap:wrap;
        background:#f7f9ff;border:1px solid #dfe4fb;border-radius:12px;padding:14px 16px;}
    .audience-label{font-weight:800;font-size:13.5px;color:var(--navy);}
    .audience-opts{display:flex;gap:16px;flex-wrap:wrap;}
    .audience-opts label{display:inline-flex;align-items:center;gap:7px;margin:0;
        font-size:13.5px;font-weight:700;text-transform:none;letter-spacing:0;color:var(--navy);cursor:pointer;}
    .audience-opts input{width:16px;height:16px;accent-color:var(--navy);cursor:pointer;}
    .btn{border:none;border-radius:999px;padding:11px 22px;font-weight:800;font-size:13.5px;}
    .btn-navy{background:var(--navy);color:#fff;}
    .btn-ghost{background:#fff;border:1.5px solid var(--line);color:var(--navy);padding:8px 16px;
        border-radius:999px;font-weight:700;font-size:12.5px;}
    .btn-danger{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;padding:8px 16px;
        border-radius:999px;font-weight:700;font-size:12.5px;}
    .ann{border:1px solid var(--line);border-radius:12px;padding:14px 18px;margin-bottom:10px;background:#fff;}
    .ann:last-child{margin-bottom:0;}
    .ann-head{display:flex;justify-content:space-between;align-items:center;gap:18px;}
    .ann-title{font-size:15px;font-weight:800;margin:0 0 8px;color:#17212b;}
    .ann-copy{min-width:0;}
    .ann-body{color:#4b5563;font-size:14px;line-height:1.55;margin:0 0 10px;overflow-wrap:anywhere;}
    .ann-body p{margin:0 0 8px;}
    .ann-body p:last-child{margin-bottom:0;}
    .ann-meta{color:var(--muted);font-size:12px;}
    .pill{display:inline-block;background:#eef1fb;color:var(--navy);font-size:11.5px;
        font-weight:800;padding:4px 11px;border-radius:999px;margin-right:6px;}
    .pill.faculty{background:#fdf3d7;color:#c4930f;}
    .pill.public{background:#e8f5ee;color:#136c3e;}
    .pill.pinned{background:#fff4cf;color:#806208;}
    .alert{padding:11px 14px;border-radius:12px;margin-bottom:18px;font-size:14px;}
    .alert-ok{background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .alert-err{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;}
    .empty{color:var(--muted);text-align:center;padding:34px 8px;font-size:14px;}
    .actions{display:flex;gap:8px;flex-shrink:0;}
    .btn-view{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;
        border:1.5px solid var(--line);border-radius:999px;background:#fff;color:var(--navy);}
    .btn-view:hover{background:#f7f9ff;border-color:#c9ccdb;}
    .btn-view svg{width:18px;height:18px;fill:currentColor;}
    .btn-view:focus-visible,.btn-ghost:focus-visible,.btn-pin:focus-visible,.btn-danger:focus-visible,
    .announcement-dialog-close:focus-visible{outline:3px solid rgba(11,78,162,.28);outline-offset:2px;}
    .btn-pin{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid var(--line);
        color:var(--navy);padding:8px 14px;border-radius:999px;font-weight:700;font-size:12.5px;}
    .btn-pin.is-pinned{background:#fff8df;border-color:#e9cf72;color:#806208;}
    .btn-pin svg{width:16px;height:16px;flex:0 0 16px;}
    .announcement-modal[hidden]{display:none;}
    .announcement-modal{position:fixed;inset:0;z-index:12000;display:grid;place-items:center;padding:16px;}
    .announcement-modal-backdrop{position:absolute;inset:0;background:rgba(7,20,63,.68);backdrop-filter:blur(3px);}
    .announcement-dialog{position:relative;width:min(100%,760px);max-height:min(85vh,820px);overflow-y:auto;
        padding:24px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(6,16,52,.3);}
    .announcement-dialog-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;
        padding-bottom:16px;border-bottom:1px solid var(--line);}
    .announcement-dialog-head h2{margin:0;color:#000;font-size:22px;line-height:1.3;overflow-wrap:anywhere;}
    .announcement-dialog-meta{margin:6px 0 0;color:var(--muted);font-size:12px;}
    .announcement-dialog-close{width:36px;height:36px;flex:0 0 36px;border:0;border-radius:50%;
        background:#f1f3f8;color:var(--navy);font-size:24px;line-height:1;cursor:pointer;}
    .announcement-dialog-content{padding-top:18px;color:#4b5563;font-size:14px;line-height:1.65;overflow-wrap:anywhere;}
    .announcement-dialog-content > :first-child{margin-top:0;}
    .announcement-dialog-content img{max-width:100%;height:auto;}
    .announcement-edit-modal[hidden]{display:none;}
    .announcement-edit-modal{position:fixed;inset:0;z-index:12000;display:grid;place-items:center;padding:16px;}
    .announcement-edit-dialog{position:relative;width:min(100%,760px);max-height:min(90vh,900px);overflow-y:auto;
        padding:24px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(6,16,52,.3);}
    .announcement-edit-dialog .announcement-dialog-head{align-items:center;}
    .announcement-edit-dialog .field{margin-top:18px;}
    .announcement-edit-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;}
    .btn-cancel{background:#fff;border:1.5px solid var(--line);color:var(--navy);padding:10px 18px;
        border-radius:999px;font-weight:700;font-size:13px;}
    .btn-cancel:hover{background:#f7f9ff;}
    body.announcement-modal-open{overflow:hidden;}
    @media(max-width:640px){.ann-head{align-items:flex-start;flex-direction:column;}.actions{flex-wrap:wrap;}.announcement-edit-actions{flex-direction:column-reverse;}.announcement-edit-actions button{width:100%;}}
    @media(max-width:600px){.announcement-dialog,.announcement-edit-dialog{padding:18px;}.announcement-dialog-head h2{font-size:19px;}}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<style>.topbar{display:none!important;}.wrap{margin-top:0!important;}</style>
@if(false)
<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('admin.dashboard') }}" class="topbar-brand">
        <span class="brand-logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
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
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;">
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
                        <div class="ann-meta" style="margin-top:8px;">
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
@include('components.admin-sidebar')

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')
</body>
</html>
