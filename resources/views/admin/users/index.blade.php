
<x-admin.layout title="Upskill – User Management" page="admin-users-index">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
{{-- TOP NAV --}}
    <div class="layout admin-layout">

        {{-- SHARED ADMIN SIDEBAR --}}
        {{-- MAIN CONTENT --}}
        <main class="main">

            <x-flash-toast :message="session('success')" />

            <header class="page-head user-management-heading">
                <div>
                    <h1>User Management</h1>
                    <p>Manage learner, faculty, and administrator accounts, roles, and platform access.</p>
                </div>
                <button class="create-user-open" id="open-create-user" type="button" onclick="openCreateUserDrawer()">Create account</button>
            </header>

            <div class="create-user-overlay" id="create-user-overlay" onclick="handleCreateUserOverlayClick(event)">
                <section class="create-user-drawer" role="dialog" aria-modal="true" aria-labelledby="create-user-title" aria-describedby="create-user-description">
                    <header class="create-user-drawer-header">
                        <div>
                            <h2 id="create-user-title">Create account</h2>
                            <p id="create-user-description">Add a learner, faculty member, or administrator.</p>
                        </div>
                        <button class="create-user-close" type="button" onclick="closeCreateUserDrawer()" aria-label="Close create account drawer">&times;</button>
                    </header>
                    <form class="create-user-form" action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div class="create-user-form-body">
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

                        </div>
                        <footer class="create-user-form-footer">
                            <button class="create-user-cancel" type="button" onclick="closeCreateUserDrawer()">Cancel</button>
                            <button class="create-user-submit" type="submit">Create account</button>
                        </footer>
                    </form>
                </section>
            </div>

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

                    <a href="{{ route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'administrators'])) }}"
                       class="role-tab {{ $roleTab === 'administrators' ? 'active' : '' }}">
                        Administrators
                    </a>
                </div>

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
                                <th class="adm-inline-admin-users-index-1">Actions</th>
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
                                    <td onclick="event.stopPropagation();" class="adm-inline-admin-users-index-2">
                                        @if ($isSuperAdmin && $user->isSubAdmin())
                                            <button class="manage-access-button" type="button" data-open-access-dialog="manage-access-{{ $user->id }}">
                                                Manage access
                                            </button>
                                        @elseif ($user->isAdmin())
                                            <span class="adm-inline-admin-users-index-3">&mdash;</span>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $user->id) }}"

                                                  onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) }}? This cannot be undone.');" class="adm-inline-admin-users-index-4">

                                                @csrf

                                                <button type="submit"
                                                        onclick="event.stopPropagation();" class="adm-inline-admin-users-index-5"
                                                       >
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
        function openCreateUserDrawer() {
            const drawer = document.getElementById('create-user-overlay');
            if (!drawer) return;

            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';
            window.setTimeout(function () {
                const firstField = document.getElementById('create-user-first-name');
                if (firstField) firstField.focus();
            }, 50);
        }

        function closeCreateUserDrawer() {
            const drawer = document.getElementById('create-user-overlay');
            if (!drawer) return;

            drawer.classList.remove('open');
            document.body.style.overflow = '';
            const trigger = document.getElementById('open-create-user');
            if (trigger) trigger.focus();
        }

        function handleCreateUserOverlayClick(event) {
            if (event.target === document.getElementById('create-user-overlay')) {
                closeCreateUserDrawer();
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && document.getElementById('create-user-overlay')?.classList.contains('open')) {
                closeCreateUserDrawer();
            }
        });

        @if ($errors->any())
            openCreateUserDrawer();
        @endif

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
</x-admin.layout>