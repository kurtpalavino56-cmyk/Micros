{{--
    resources/views/admin/Program_Categories.blade.php
    Admin › Management › Program Categories

    The list faculty choose from in the Category dropdown on the Create /
    Edit Course form. Expects: $user (UserPresenter::admin), $categories
    (CourseCategory collection, each with ->courses_using).
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Program Categories | UPSKILL Admin</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#0d1b6e; --navy-deep:#0a1550; --gold:#dba617;
        --line:#e5e7eb; --muted:#6b7280; --bg:#f5f7fb; --white:#fff;
        --green:#15803d; --red:#dc2626;
        --shadow:0 10px 26px rgba(13,27,110,.08);
        --sidebar-w:225px;
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);background:var(--bg);}
    a{text-decoration:none;color:inherit;}
    button{font:inherit;cursor:pointer;}

    /* ── Topbar ─────────────────────────────────────────────── */
    .topbar{position:sticky;top:0;z-index:200;height:60px;background:var(--navy);
        display:flex;align-items:center;justify-content:space-between;padding:0 22px;
        box-shadow:0 2px 10px rgba(0,0,0,.2);}
    .topbar-brand{display:flex;align-items:center;gap:11px;color:#fff;font-weight:800;
        letter-spacing:2px;text-transform:uppercase;font-size:.95rem;}
    .brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;overflow:hidden;}
    .brand-logo img{width:100%;height:100%;object-fit:contain;padding:3px;}
    .topbar-right{display:flex;align-items:center;gap:10px;}
    .avatar-btn{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.16);
        border:1px solid rgba(255,255,255,.24);display:inline-flex;align-items:center;
        justify-content:center;color:#fff;}

    /* ── Layout ─────────────────────────────────────────────── */
    .layout{display:flex;align-items:flex-start;}
    .sidebar{width:var(--sidebar-w);flex-shrink:0;background:#fff;border-right:1px solid var(--line);
        min-height:calc(100vh - 60px);padding:18px 0;}
    .sb-section{margin-bottom:6px;}
    .sb-section-hd{display:flex;align-items:center;justify-content:space-between;
        padding:11px 20px;font-size:.7rem;font-weight:800;letter-spacing:.09em;
        text-transform:uppercase;color:var(--muted);border-left:3px solid transparent;}
    .sb-items{display:flex;flex-direction:column;}
    .sb-item{padding:10px 20px 10px 26px;font-size:.9rem;font-weight:600;color:#374151;
        border-left:3px solid transparent;transition:background .15s,color .15s;}
    .sb-item:hover{background:#f7f9ff;color:var(--navy);}
    .sb-item.active{background:#eef2ff;color:var(--navy);font-weight:800;border-left-color:var(--navy);}

    .main{flex:1;min-width:0;padding:28px 30px 60px;}

    /* ── Header ─────────────────────────────────────────────── */
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;
        gap:16px;flex-wrap:wrap;margin-bottom:22px;}
    .page-head h1{margin:0 0 5px;font-size:26px;}
    .page-head p{margin:0;color:var(--muted);font-size:14px;max-width:560px;line-height:1.5;}
    .count-pill{background:#eef2ff;color:var(--navy);border-radius:999px;padding:7px 16px;
        font-size:13px;font-weight:800;white-space:nowrap;}

    .alert{padding:12px 16px;border-radius:12px;margin-bottom:18px;font-size:14px;font-weight:600;}
    .alert-ok{background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .alert-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
    .alert ul{margin:6px 0 0;padding-left:18px;font-weight:500;}

    .grid{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;}

    .card{background:#fff;border:1px solid var(--line);border-radius:18px;
        box-shadow:var(--shadow);overflow:hidden;}
    .card-hd{padding:16px 20px;border-bottom:1px solid var(--line);font-weight:800;font-size:15px;}
    .card-bd{padding:20px;}

    /* ── Table ──────────────────────────────────────────────── */
    table{width:100%;border-collapse:collapse;}
    th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;
        color:var(--muted);padding:12px 16px;border-bottom:1px solid var(--line);background:#fafbff;}
    td{padding:14px 16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle;}
    tr:last-child td{border-bottom:none;}
    .cat-name{font-weight:800;}
    .cat-desc{color:var(--muted);font-size:12.5px;margin-top:2px;}
    .pill{display:inline-block;border-radius:999px;padding:4px 12px;font-size:11.5px;font-weight:800;}
    .pill-on{background:#e8f7ef;color:var(--green);}
    .pill-off{background:#f1f5f9;color:#475569;}
    .pill-use{background:#eef2ff;color:var(--navy);}
    .row-actions{display:flex;gap:7px;justify-content:flex-end;flex-wrap:wrap;}
    .btn{border:1px solid var(--line);background:#fff;border-radius:9px;padding:7px 13px;
        font-size:12.5px;font-weight:700;color:var(--navy);transition:background .15s,color .15s;}
    .btn:hover{background:#f3f6ff;}
    .btn-danger{color:var(--red);border-color:#fecaca;}
    .btn-danger:hover{background:#fef2f2;}
    .btn-primary{background:var(--navy);color:#fff;border-color:var(--navy);}
    .btn-primary:hover{background:var(--navy-deep);}
    .empty{padding:36px 20px;text-align:center;color:var(--muted);font-size:14px;}

    /* ── Form ───────────────────────────────────────────────── */
    label{display:block;font-size:12.5px;font-weight:800;margin-bottom:6px;}
    .input{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px 13px;
        font-size:14px;background:#fbfdff;color:var(--navy);}
    .input:focus{outline:none;border-color:var(--navy);}
    .field{margin-bottom:14px;}
    .hint{color:var(--muted);font-size:12px;margin-top:6px;line-height:1.5;}

    /* ── Inline edit row ────────────────────────────────────── */
    .edit-row{display:none;background:#fafbff;}
    .edit-row.open{display:table-row;}
    .edit-grid{display:grid;grid-template-columns:1fr 1fr auto auto;gap:10px;align-items:center;}
    .check{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:700;}

    @media (max-width:1024px){
        .sidebar{display:none;}
        .grid{grid-template-columns:1fr;}
        .main{padding:20px 16px 48px;}
    }
    @media (max-width:640px){
        .edit-grid{grid-template-columns:1fr;}
        table, tbody, tr, td{display:block;width:100%;}
        thead{display:none;}
        tr{border:1px solid var(--line);border-radius:12px;margin-bottom:12px;padding:10px;}
        td{border-bottom:none;padding:6px 4px;}
        .row-actions{justify-content:flex-start;}
    }
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<style>.topbar{display:none!important;}.wrap{margin-top:0!important;}</style>
@if(false)

<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('admin.dashboard') }}" class="topbar-brand">
        <span class="brand-logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"
                 width="38" height="38"
                 style="width:38px;height:38px;object-fit:contain;border-radius:50%;"></span>
        UPSKILL
    </a>
    <div class="topbar-right">
        <a href="{{ route('notifications.index') }}" class="avatar-btn" title="Notifications">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
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
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;align-items:center;">
            @csrf
            <button type="submit" style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:8px 12px;font-weight:700;">Logout</button>
        </form>
    </div>
</nav>
@endif

<div class="layout">

    {{-- Same structure as every other admin page; styling comes from
         components/admin-sidebar. --}}
<main class="main">

        <div class="page-head">
            <div>
                <h1>Program Categories</h1>
                <p>These are the options faculty choose from in the <strong>Category</strong>
                   dropdown when creating or editing a course. Hiding a category keeps
                   existing courses intact but removes it from the form.</p>
            </div>
            <span class="count-pill">{{ $categories->count() }} categor{{ $categories->count() === 1 ? 'y' : 'ies' }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-ok">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-err">
                Please check the form:
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid">

            {{-- ── List ────────────────────────────────────────── --}}
            <section class="card">
                <div class="card-hd">All categories</div>

                @if ($categories->isEmpty())
                    <div class="empty">No categories yet. Add the first one using the form.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Courses</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <div class="cat-name">{{ $category->name }}</div>
                                    @if ($category->description)
                                        <div class="cat-desc">{{ $category->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="pill {{ $category->is_active ? 'pill-on' : 'pill-off' }}">
                                        {{ $category->is_active ? 'Visible' : 'Hidden' }}
                                    </span>
                                </td>
                                <td><span class="pill pill-use">{{ $category->courses_using }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="btn"
                                                onclick="toggleEdit({{ $category->id }})">Edit</button>

                                        <form method="POST" action="{{ route('admin.categories.toggle', $category->id) }}">
                                            @csrf
                                            <button type="submit" class="btn">
                                                {{ $category->is_active ? 'Hide' : 'Show' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}"
                                              onsubmit="return confirm('Delete “{{ $category->name }}”?{{ $category->courses_using > 0 ? ' ' . $category->courses_using . ' course(s) still use this label and will keep it until edited.' : '' }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Inline edit --}}
                            <tr class="edit-row" id="edit-{{ $category->id }}">
                                <td colspan="4">
                                    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="edit-grid">
                                            <div>
                                                <label for="name-{{ $category->id }}">Name</label>
                                                <input class="input" id="name-{{ $category->id }}" name="name"
                                                       value="{{ $category->name }}" required>
                                            </div>
                                            <div>
                                                <label for="desc-{{ $category->id }}">Description</label>
                                                <input class="input" id="desc-{{ $category->id }}" name="description"
                                                       value="{{ $category->description }}"
                                                       placeholder="Optional">
                                            </div>
                                            <label class="check" style="margin-top:18px;">
                                                <input type="checkbox" name="is_active" value="1"
                                                       @checked($category->is_active)>
                                                Visible
                                            </label>
                                            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- ── Add ─────────────────────────────────────────── --}}
            <section class="card">
                <div class="card-hd">Add a category</div>
                <div class="card-bd">
                    <form method="POST" action="{{ route('admin.categories.store') }}">
                        @csrf
                        <div class="field">
                            <label for="new-name">Category name</label>
                            <input class="input" id="new-name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g. BS Data Science" required>
                        </div>
                        <div class="field">
                            <label for="new-desc">Description</label>
                            <input class="input" id="new-desc" name="description"
                                   value="{{ old('description') }}"
                                   placeholder="Optional — shown here only">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;">
                            Add category
                        </button>
                        <p class="hint">
                            New categories appear immediately in the faculty course form.
                            Renaming one also updates every course already filed under it.
                        </p>
                    </form>
                </div>
            </section>

        </div>
    </main>
</div>

<script>
    function toggleSection(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('open');
    }

    function toggleEdit(id) {
        var row = document.getElementById('edit-' + id);
        if (row) row.classList.toggle('open');
    }
</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
@include('components.admin-sidebar')

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')
</body>
</html>