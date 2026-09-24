
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – User Management</title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">

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
    
    <?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="layout admin-layout">

        
        <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <main class="main">

            <div class="user-tools">
                <?php $roleTab = $roleTab ?? 'students'; ?>

                <div class="role-tabs">
                    <a href="<?php echo e(route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'students']))); ?>"
                       class="role-tab <?php echo e($roleTab === 'students' ? 'active' : ''); ?>">
                        Students
                    </a>

                    <a href="<?php echo e(route('admin.usermanagement', array_merge(request()->except('role','page'), ['role' => 'faculty']))); ?>"
                       class="role-tab <?php echo e($roleTab === 'faculty' ? 'active' : ''); ?>">
                        Faculty
                    </a>
                </div>

                <form class="user-search-form" action="<?php echo e(route('admin.usermanagement')); ?>" method="GET">
                    <input type="hidden" name="role" value="<?php echo e($roleTab); ?>">

                    <input type="text"
                           name="q"
                           value="<?php echo e($q ?? ''); ?>"
                           placeholder="Search by name, email, or user code">

                    <select name="sort">
                        <option value="created_at" <?php echo e(($sort ?? 'created_at') === 'created_at' ? 'selected' : ''); ?>>
                            Newest
                        </option>
                        <option value="name" <?php echo e(($sort ?? '') === 'name' ? 'selected' : ''); ?>>
                            Name
                        </option>
                        <option value="user_code" <?php echo e(($sort ?? '') === 'user_code' ? 'selected' : ''); ?>>
                            User Code
                        </option>
                    </select>

                    <input type="hidden" name="direction" value="<?php echo e($direction === 'asc' ? 'desc' : 'asc'); ?>">

                    <button type="submit">Filter</button>
                </form>
            </div>

            <?php if($users->isEmpty()): ?>
                <div class="empty-state">
                    No users found for the current filter.
                </div>
            <?php else: ?>
                <div class="table-card">
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
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="um-row-click"
                                    onclick="window.location='<?php echo e(route('admin.users.show', $user->id)); ?>'"
                                    title="View details">

                                    <td>
                                        <?php echo e(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? '—')); ?>

                                    </td>

                                    <td><?php echo e($user->email); ?></td>

                                    <td>
                                        <?php echo e(match ((int) ($user->role_id ?? 3)) {
                                            1 => 'Administrator',
                                            2 => 'Faculty',
                                            3 => 'Learner',
                                            default => 'Learner'
                                        }); ?>

                                    </td>

                                    <td>
                                        <span class="code-pill"><?php echo e($user->user_code ?? '—'); ?></span>
                                    </td>

                                    <td>
                                        <span class="status-badge <?php echo e($user->is_active ? 'status-active' : 'status-pending'); ?>">
                                            <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>

                                    
                                    <td style="text-align:right;" onclick="event.stopPropagation();">
                                        <?php if((int) ($user->role_id ?? 3) === 1): ?>
                                            <span style="color:#9ca3af;font-size:12px;">&mdash;</span>
                                        <?php else: ?>
                                            <form method="POST"
                                                  action="<?php echo e(route('admin.users.destroy', $user->id)); ?>"
                                                  style="display:inline;"
                                                  onsubmit="event.stopPropagation(); return confirm('Delete <?php echo e(addslashes(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')))); ?>? This cannot be undone.');">

                                                <?php echo csrf_field(); ?>

                                                <button type="submit"
                                                        onclick="event.stopPropagation();"
                                                        style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:6px 14px;font-weight:700;font-size:12px;cursor:pointer;">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </main>
    </div>

    
    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            if (!section || section.classList.contains('locked')) return;
            section.classList.toggle('open');
        }
    </script>

    
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

    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/users/index.blade.php ENDPATH**/ ?>