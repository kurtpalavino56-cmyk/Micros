
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – User Management</title>

    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">

    <style>
        /* =========================================================
           UPSKILL ADMIN — USER MANAGEMENT
           Blue / White / Yellow theme
           Design-only changes
        ========================================================= */

        :root {
            --navy: #09255f;
            --blue: #0b4ea2;
            --blue-light: #eaf2ff;
            --blue-soft: #f4f7fc;
            --yellow: #f4c430;
            --yellow-soft: #fff8dc;
            --white: #fff;
            --surface: #f5f7fa;
            --border: #e2e7ef;
            --border-light: #edf0f5;
            --text-main: #17212b;
            --text-muted: #687385;
            --text-sub: #929baa;
            --shadow-sm: 0 1px 3px rgba(9, 37, 95, .05);
            --shadow-md: 0 5px 18px rgba(9, 37, 95, .08);
            --radius-card: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .layout {
            display: flex;
            margin-top: 0;
            min-height: calc(100vh - 66px);
        }

        .main {
            margin-left: 0;
            width: 100%;
            flex: 1;
            min-width: 0;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        /* Toolbar */
        .user-management-heading {
            margin-bottom: 20px;
        }
        .user-management-heading h1 {
            color: #000;
        }

        .user-tools {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        /* Student / Faculty tabs */
        .role-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px;
            background: #eaf0f8;
            border: 1px solid #e0e7f1;
            border-radius: 8px;
        }

        .role-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 8px 18px;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #536175;
            font-size: .82rem;
            font-weight: 500;
            text-decoration: none;
            transition: background .18s ease, color .18s ease;
        }

        .role-tab:hover {
            background: rgba(255, 255, 255, .7);
            color: var(--navy);
            transform: none;
        }

        .role-tab.active {
            background: var(--navy);
            color: var(--white);
            border-color: var(--navy);
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(9, 37, 95, .12);
        }

        .create-user-panel {
            width: 100%;
            margin: 0 0 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .create-user-panel summary {
            padding: 15px 18px;
            color: var(--navy);
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
        }

        .create-user-panel[open] summary {
            border-bottom: 1px solid var(--border-light);
        }

        .create-user-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 18px;
        }

        .create-user-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .create-user-field label {
            color: #536175;
            font-size: .75rem;
            font-weight: 600;
        }

        .create-user-field input,
        .create-user-field select {
            min-height: 40px;
            padding: 9px 11px;
            border: 1px solid #d9e1ec;
            border-radius: 7px;
            background: var(--white);
            color: var(--text-main);
            font: inherit;
            font-size: .82rem;
        }

        .create-user-field input:focus,
        .create-user-field select:focus {
            border-color: var(--blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(11, 78, 162, .09);
        }

        .create-user-field-wide {
            grid-column: 1 / -1;
        }

        .create-user-error {
            color: #b42318;
            font-size: .72rem;
        }

        .create-user-errors {
            grid-column: 1 / -1;
            margin: 0;
            padding: 11px 14px;
            border: 1px solid #f3c8c4;
            border-radius: 7px;
            background: #fff1f0;
            color: #a63830;
            font-size: .8rem;
        }

        .admin-page-access {
            grid-column: 1 / -1;
            padding: 14px;
            border: 1px solid var(--border-light);
            border-radius: 7px;
            background: var(--blue-soft);
        }

        .admin-page-access[hidden] {
            display: none;
        }

        .admin-page-access legend {
            padding: 0 5px;
            color: var(--navy);
            font-size: .8rem;
            font-weight: 700;
        }

        .admin-page-access-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px 14px;
        }

        .admin-page-access-options label {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #536175;
            font-size: .75rem;
        }

        .admin-page-access-options input {
            accent-color: var(--navy);
        }

        .manage-access-button {
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--white);
            color: var(--navy);
            font: inherit;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
        }

        .manage-access-button:hover {
            border-color: var(--blue);
            background: var(--blue-light);
        }

        .manage-access-dialog {
            width: min(560px, calc(100% - 32px));
            max-height: min(85vh, 720px);
            padding: 0;
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--white);
            color: var(--text-main);
            box-shadow: var(--shadow-md);
        }

        .manage-access-dialog::backdrop {
            background: rgba(9, 37, 95, .48);
        }

        .manage-access-dialog-content {
            padding: 22px;
        }

        .manage-access-dialog h2 {
            margin: 0 0 5px;
            color: var(--navy);
            font-size: 1.05rem;
        }

        .manage-access-dialog-description {
            margin: 0 0 18px;
            color: var(--text-muted);
            font-size: .8rem;
        }

        .manage-access-dialog .admin-page-access {
            margin: 0 0 16px;
        }

        .manage-access-dialog-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
        }

        .manage-access-cancel {
            min-height: 38px;
            padding: 9px 16px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--white);
            color: var(--text-main);
            font: inherit;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
        }

        .create-user-submit {
            justify-self: start;
            min-height: 38px;
            padding: 9px 17px;
            border: 1px solid var(--yellow);
            border-radius: 7px;
            background: var(--yellow);
            color: #17212b;
            font: inherit;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .create-user-submit:hover {
            background: #e8b91f;
        }

        /* Search and filters */
        .user-search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            align-items: center;
            flex: 1;
            max-width: 700px;
            justify-content: flex-end;
        }

        .user-search-form input,
        .user-search-form select {
            min-height: 38px;
            border: 1px solid #d9e1ec;
            border-radius: 7px;
            padding: 9px 12px;
            font-family: inherit;
            font-size: .8rem;
            color: var(--text-main);
            background: var(--white);
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .user-search-form input[name="q"] {
            flex: 1;
            min-width: 190px;
        }

        .user-search-form input:focus,
        .user-search-form select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(11, 78, 162, .09);
        }

        .user-search-form input::placeholder {
            color: var(--text-sub);
        }

        .user-search-form button {
            min-height: 38px;
            padding: 9px 17px;
            border: 1px solid var(--yellow);
            border-radius: 7px;
            background: var(--yellow);
            color: #17212b;
            font-family: inherit;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .18s ease, transform .18s ease;
        }

        .user-search-form button:hover {
            background: #e8b91f;
            transform: translateY(-1px);
        }

        /* Table container */
        .table-card {
            width: 100%;
            max-width: none;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
        }

        .users-pagination { margin-top: 18px; }

        /* Table */
        .um-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 760px;
        }

        .um-table thead tr {
            background: #f7f9fc;
        }

        .um-table thead th {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: #f7f9fc;
            text-align: left;
            font-size: .7rem;
            font-weight: 600;
            color: #5c687a;
            text-transform: uppercase;
            letter-spacing: .045em;
            white-space: nowrap;
        }

        .um-table thead th:first-child {
            padding-left: 20px;
        }

        .um-table thead th:last-child {
            padding-right: 20px;
        }

        .um-table tbody tr {
            border: 0;
            transition: background .15s ease;
        }

        .um-table tbody tr:hover {
            background: #f7faff;
        }

        .um-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            font-size: .8rem;
            line-height: 1.45;
            color: #344054;
            font-weight: 400;
            vertical-align: middle;
        }

        .um-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .um-table tbody td:first-child {
            padding-left: 20px;
            color: var(--navy);
            font-weight: 600;
        }

        .um-table tbody td:last-child {
            padding-right: 20px;
        }

        /* Clickable user rows */
        .um-row-click {
            cursor: pointer;
            transition: background .15s ease;
        }

        .um-row-click:hover {
            background: #f5f8fd;
        }

        /* User code */
        .code-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border: 1px solid #dce6f5;
            border-radius: 5px;
            background: var(--blue-light);
            color: var(--blue);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Status */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .75rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-active {
            color: #237a50;
        }

        .status-pending {
            color: #9a6700;
        }

        .status-published {
            color: var(--blue);
        }

        .status-draft {
            color: var(--text-muted);
        }

        /* Delete button */
        .um-table button[type="submit"] {
            background: #fff1f0 !important;
            border: 1px solid #f3c8c4 !important;
            color: #a63830 !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            font-family: inherit;
            font-size: .73rem !important;
            font-weight: 600 !important;
            transition: background .18s ease, border-color .18s ease;
        }

        .um-table button[type="submit"]:hover {
            background: #ffe4e1 !important;
            border-color: #e9aaa4 !important;
        }

        /* Empty state */
        .empty-state {
            width: 100%;
            max-width: none;
            padding: 44px 24px;
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            background: var(--white);
            color: var(--text-muted);
            text-align: center;
            font-size: .85rem;
            box-shadow: var(--shadow-sm);
        }

        /* Back-to-top button */
        #back-to-top-btn {
            background: var(--navy) !important;
            color: var(--white) !important;
            box-shadow: 0 5px 16px rgba(9, 37, 95, .2) !important;
        }

        #back-to-top-btn:hover {
            background: var(--yellow) !important;
            color: var(--navy) !important;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 1000px) {
            .main {
                padding: 24px 20px;
            }

            .user-tools {
                align-items: stretch;
            }

            .user-search-form {
                max-width: none;
                justify-content: flex-start;
            }
        }

        @media (max-width: 850px) {
            .main {
                margin-left: 0;
                padding: 22px 16px;
            }
        }

        @media (max-width: 560px) {
            .main {
                padding: 18px 12px;
            }

            .user-tools {
                flex-direction: column;
                align-items: stretch;
            }

            .role-tabs {
                width: 100%;
            }

            .role-tab {
                flex: 1;
            }

            .create-user-form {
                grid-template-columns: 1fr;
            }

            .create-user-field-wide {
                grid-column: auto;
            }

            .admin-page-access,
            .admin-page-access-options {
                grid-template-columns: 1fr;
            }

            .user-search-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .user-search-form input[name="q"] {
                grid-column: 1 / -1;
                min-width: 0;
            }

            .user-search-form button {
                width: 100%;
            }

            .table-card {
                border-radius: 7px;
            }
        }
    </style>
</head>

<body>
    {{-- TOP NAV --}}
    @include('components.authenticated-topbar', ['user' => auth()->user()])

    <div class="layout admin-layout">

        {{-- SHARED ADMIN SIDEBAR --}}
        @include('components.admin-sidebar')

        {{-- MAIN CONTENT --}}
        <main class="main">

            <x-flash-toast :message="session('success')" />

            <header class="page-head user-management-heading">
                <div>
                    <h1>User Management</h1>
                    <p>Manage learner, faculty, and administrator accounts, roles, and platform access.</p>
                </div>
            </header>

            <details class="create-user-panel" @if ($errors->any()) open @endif>
                <summary>Create account</summary>
                <form class="create-user-form" action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="create-user-errors" role="alert">
                            <strong>There was a problem creating the account.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="create-user-field">
                        <label for="create-user-first-name">First name</label>
                        <input id="create-user-first-name" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required>
                        @error('first_name')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="create-user-field">
                        <label for="create-user-last-name">Last name</label>
                        <input id="create-user-last-name" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required>
                        @error('last_name')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="create-user-field">
                        <label for="create-user-email">Email</label>
                        <input id="create-user-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="create-user-field">
                        <label for="create-user-username">Username</label>
                        <input id="create-user-username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required>
                        @error('username')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="create-user-field">
                        <label for="create-user-role">Account role</label>
                        <select id="create-user-role" name="role_id" required>
                            @foreach ($accountRoles as $roleId => $roleName)
                                <option value="{{ $roleId }}" @selected((string) old('role_id', \App\Models\User::ROLE_STUDENT) === (string) $roleId)>
                                    {{ $roleName }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    @if ($isSuperAdmin)
                        <fieldset class="admin-page-access" id="admin-page-access" @if ((string) old('role_id', \App\Models\User::ROLE_STUDENT) !== (string) \App\Models\User::ROLE_SUBADMIN) hidden @endif>
                            <legend>Sub-admin page access</legend>
                            <div class="admin-page-access-options">
                                @foreach ($adminPageAccessOptions as $pageKey => $pageLabel)
                                    <label>
                                        <input type="checkbox" name="admin_page_access[]" value="{{ $pageKey }}" @checked(in_array($pageKey, old('admin_page_access', []), true))>
                                        {{ $pageLabel }}
                                    </label>
                                @endforeach
                            </div>
                            @error('admin_page_access')<span class="create-user-error">{{ $message }}</span>@enderror
                            @error('admin_page_access.*')<span class="create-user-error">{{ $message }}</span>@enderror
                        </fieldset>
                    @endif

                    <div class="create-user-field">
                        <label for="create-user-password">Temporary password</label>
                        <input id="create-user-password" name="password" type="password" autocomplete="new-password" required>
                        @error('password')<span class="create-user-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="create-user-field">
                        <label for="create-user-password-confirmation">Confirm password</label>
                        <input id="create-user-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>

                    <button class="create-user-submit create-user-field-wide" type="submit">Create account</button>
                </form>
            </details>

            <div class="user-tools">
                @php $roleTab = $roleTab ?? 'students'; @endphp

                <div class="role-tabs">
                    <a href="{{ route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'students'])) }}"
                       class="role-tab {{ $roleTab === 'students' ? 'active' : '' }}">
                        Students
                    </a>

                    <a href="{{ route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'faculty'])) }}"
                       class="role-tab {{ $roleTab === 'faculty' ? 'active' : '' }}">
                        Faculty
                    </a>
                </div>

                    <a href="{{ route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'administrators'])) }}"
                       class="role-tab {{ $roleTab === 'administrators' ? 'active' : '' }}">
                        Administrators
                    </a>

                <form class="user-search-form" action="{{ route('admin.usermanagement') }}" method="GET">
                    <input type="hidden" name="role" value="{{ $roleTab }}">

                    <input type="text"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Search by name, email, or user code">

                    <select name="sort">
                        <option value="created_at" {{ ($sort ?? 'created_at') === 'created_at' ? 'selected' : '' }}>
                            Newest
                        </option>
                        <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>
                            Name
                        </option>
                        <option value="user_code" {{ ($sort ?? '') === 'user_code' ? 'selected' : '' }}>
                            User Code
                        </option>
                    </select>

                    <input type="hidden" name="direction" value="{{ $direction === 'asc' ? 'desc' : 'asc' }}">

                    <button type="submit">Filter</button>
                </form>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    No users found for the current filter.
                </div>
            @else
                <div class="table-card paginated-table-scroll">
                    <table class="um-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>User Code</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($users as $user)
                                <tr class="um-row-click"
                                    onclick="window.location='{{ route('admin.users.show', $user->id) }}'"
                                    title="View details">

                                    <td>
                                        {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? '—') }}
                                    </td>

                                    <td>{{ $user->email }}</td>

                                    <td>{{ $user->displayRole() }}</td>

                                    <td>
                                        <span class="code-pill">{{ $user->user_code ?? '—' }}</span>
                                    </td>

                                    <td>
                                        <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-pending' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    {{-- Delete action --}}
                                    <td style="text-align:right;" onclick="event.stopPropagation();">
                                        @if ($isSuperAdmin && $user->isSubAdmin())
                                            <button class="manage-access-button" type="button" data-open-access-dialog="manage-access-{{ $user->id }}">
                                                Manage access
                                            </button>
                                        @elseif ($user->isAdmin())
                                            <span style="color:#9ca3af;font-size:12px;">&mdash;</span>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $user->id) }}"
                                                  style="display:inline;"
                                                  onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) }}? This cannot be undone.');">

                                                @csrf

                                                <button type="submit"
                                                        onclick="event.stopPropagation();"
                                                        style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:6px 14px;font-weight:700;font-size:12px;cursor:pointer;">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($isSuperAdmin)
                        @foreach ($users->where('role_id', \App\Models\User::ROLE_SUBADMIN) as $user)
                            @php
                                $assignedPages = (string) old('access_user_id') === (string) $user->id
                                    ? old('page_access', $user->admin_page_access ?? [])
                                    : ($user->admin_page_access ?? []);
                            @endphp
                            <dialog class="manage-access-dialog" id="manage-access-{{ $user->id }}" aria-labelledby="manage-access-title-{{ $user->id }}">
                                <div class="manage-access-dialog-content">
                                    <h2 id="manage-access-title-{{ $user->id }}">Manage Sub-admin access</h2>
                                    <p class="manage-access-dialog-description">
                                        Choose additional pages for {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username }}. Dashboard and Profile are always included.
                                    </p>
                                    <form method="POST" action="{{ route('admin.users.access.update', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="access_user_id" value="{{ $user->id }}">
                                        <fieldset class="admin-page-access">
                                            <legend>Assigned pages</legend>
                                            <div class="admin-page-access-options">
                                                @foreach ($adminPageAccessOptions as $pageKey => $pageLabel)
                                                    <label>
                                                        <input type="checkbox" name="page_access[]" value="{{ $pageKey }}" @checked(in_array($pageKey, $assignedPages, true))>
                                                        {{ $pageLabel }}
                                                    </label>
                                                @endforeach
                                            </div>
                                            @if ((string) old('access_user_id') === (string) $user->id)
                                                @error('page_access', 'access')<span class="create-user-error">{{ $message }}</span>@enderror
                                                @error('page_access.*', 'access')<span class="create-user-error">{{ $message }}</span>@enderror
                                            @endif
                                            <div class="manage-access-dialog-actions">
                                                <button class="manage-access-cancel" type="button" data-close-access-dialog>Cancel</button>
                                                <button class="create-user-submit" type="submit">Save access</button>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </dialog>
                        @endforeach
                    @endif
                </div>

                @include('components.pagination', [
                    'paginator' => $users,
                    'summary' => $users->firstItem() ? $users->firstItem().'–'.$users->lastItem().' of '.$users->total().' · Page '.$users->currentPage().' of '.$users->lastPage() : null,
                    'ariaLabel' => 'User list pages',
                ])
            @endif

        </main>
    </div>

    {{-- SCRIPTS --}}
    <script>
        (function () {
            document.querySelectorAll('[data-open-access-dialog]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const dialog = document.getElementById(button.dataset.openAccessDialog);
                    if (dialog) dialog.showModal();
                });
            });

            document.querySelectorAll('.manage-access-dialog').forEach(function (dialog) {
                dialog.addEventListener('click', function (event) {
                    if (event.target === dialog || event.target.closest('[data-close-access-dialog]')) {
                        dialog.close();
                    }
                });
            });

            const failedAccessUserId = @json(old('access_user_id'));
            if (failedAccessUserId) {
                const failedDialog = document.getElementById('manage-access-' + failedAccessUserId);
                if (failedDialog) failedDialog.showModal();
            }
        })();

        function toggleSection(id) {
            const section = document.getElementById(id);
            if (!section || section.classList.contains('locked')) return;
            section.classList.toggle('open');
        }

        (function () {
            const roleSelect = document.getElementById('create-user-role');
            const pageAccess = document.getElementById('admin-page-access');
            if (!roleSelect || !pageAccess) return;

            const checkboxes = pageAccess.querySelectorAll('input[type="checkbox"]');
            function syncPageAccess() {
                const isSubAdmin = roleSelect.value === '{{ \App\Models\User::ROLE_SUBADMIN }}';
                pageAccess.hidden = !isSubAdmin;
                checkboxes.forEach(function (checkbox) {
                    checkbox.disabled = !isSubAdmin;
                });
            }

            roleSelect.addEventListener('change', syncPageAccess);
            syncPageAccess();
        })();
            
    </script>

    {{-- Back to top --}}
    <button id="back-to-top-btn"
            type="button"
            title="Back to top"
            aria-label="Back to top"
            onclick="window.scrollTo({top:0,behavior:'smooth'});">

        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 19V5"/>
            <path d="M5 12l7-7 7 7"/>
        </svg>
    </button>

    <style>
        #back-to-top-btn {
            position: fixed;
            right: 26px;
            bottom: 26px;
            z-index: 2000;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #13176b;
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(19,23,107,.35);
            transition: transform .15s ease, background .2s ease;
        }

        #back-to-top-btn:hover {
            background: #dba617;
            transform: translateY(-3px);
        }

        #back-to-top-btn svg {
            width: 22px;
            height: 22px;
        }
    </style>

    <script>
        (function () {
            var btn = document.getElementById('back-to-top-btn');
            if (!btn) return;

            function toggleBackToTop() {
                btn.style.display =
                    (window.scrollY || document.documentElement.scrollTop) > 400
                        ? 'flex'
                        : 'none';
            }

            window.addEventListener('scroll', toggleBackToTop, { passive: true });
            toggleBackToTop();
        })();
    </script>

    {{-- Shared responsiveness layer --}}
    @include('components.responsive')
</body>
</html>
