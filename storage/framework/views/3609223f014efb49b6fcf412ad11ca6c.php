
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Program Categories | UPSKILL Admin</title>

<link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">

<style>
    :root {
        --navy: #0d1b6e;
        --navy-deep: #0a1550;
        --gold: #dba617;
        --line: #e5e7eb;
        --muted: #6b7280;
        --bg: #f5f7fb;
        --white: #fff;
        --green: #15803d;
        --red: #dc2626;
        --shadow: 0 10px 26px rgba(13,27,110,.08);
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--navy);
        background: var(--bg);
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    button {
        font: inherit;
        cursor: pointer;
    }

    /* ── Layout ─────────────────────────────────────────────── */

    .layout {
        display: flex;
        align-items: flex-start;
    }

    .main {
        flex: 1;
        min-width: 0;
        padding: 28px 30px 60px;
    }

    /* ── Header ─────────────────────────────────────────────── */

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .page-head h1 {
        margin: 0 0 5px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
        max-width: 560px;
        line-height: 1.5;
    }

    .count-pill {
        background: #eef2ff;
        color: var(--navy);
        border-radius: 999px;
        padding: 7px 16px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    /* ── Alerts ─────────────────────────────────────────────── */

    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 600;
    }

    .alert-ok {
        background: #ecfdf3;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-err {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert ul {
        margin: 6px 0 0;
        padding-left: 18px;
        font-weight: 500;
    }

    /* ── Content Grid ───────────────────────────────────────── */

    .grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }

    /* ── Cards ──────────────────────────────────────────────── */

    .card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-hd {
        padding: 16px 20px;
        border-bottom: 1px solid var(--line);
        font-weight: 800;
        font-size: 15px;
    }

    .card-bd {
        padding: 20px;
    }

    /* ── Table ──────────────────────────────────────────────── */

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--muted);
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
        background: #fafbff;
    }

    td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        font-size: 14px;
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .cat-name {
        font-weight: 800;
    }

    .cat-desc {
        color: var(--muted);
        font-size: 12.5px;
        margin-top: 2px;
    }

    .pill {
        display: inline-block;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 11.5px;
        font-weight: 800;
    }

    .pill-on {
        background: #e8f7ef;
        color: var(--green);
    }

    .pill-off {
        background: #f1f5f9;
        color: #475569;
    }

    .pill-use {
        background: #eef2ff;
        color: var(--navy);
    }

    .row-actions {
        display: flex;
        gap: 7px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn {
        border: 1px solid var(--line);
        background: #fff;
        border-radius: 9px;
        padding: 7px 13px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--navy);
        transition: background .15s, color .15s;
    }

    .btn:hover {
        background: #f3f6ff;
    }

    .btn-danger {
        color: var(--red);
        border-color: #fecaca;
    }

    .btn-danger:hover {
        background: #fef2f2;
    }

    .btn-primary {
        background: var(--navy);
        color: #fff;
        border-color: var(--navy);
    }

    .btn-primary:hover {
        background: var(--navy-deep);
    }

    .empty {
        padding: 36px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    /* ── Form ───────────────────────────────────────────────── */

    label {
        display: block;
        font-size: 12.5px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .input {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px 13px;
        font-size: 14px;
        background: #fbfdff;
        color: var(--navy);
    }

    .input:focus {
        outline: none;
        border-color: var(--navy);
    }

    .field {
        margin-bottom: 14px;
    }

    .hint {
        color: var(--muted);
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.5;
    }

    /* ── Inline Edit Row ────────────────────────────────────── */

    .edit-row {
        display: none;
        background: #fafbff;
    }

    .edit-row.open {
        display: table-row;
    }

    .edit-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: 10px;
        align-items: center;
    }

    .check {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
    }

    /* ── Responsive ────────────────────────────────────────── */

    @media (max-width: 1024px) {
        .grid {
            grid-template-columns: 1fr;
        }

        .main {
            padding: 20px 16px 48px;
        }
    }

    @media (max-width: 640px) {
        .edit-grid {
            grid-template-columns: 1fr;
        }

        table,
        tbody,
        tr,
        td {
            display: block;
            width: 100%;
        }

        thead {
            display: none;
        }

        tr {
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-bottom: 12px;
            padding: 10px;
        }

        td {
            border-bottom: none;
            padding: 6px 4px;
        }

        .row-actions {
            justify-content: flex-start;
        }
    }
</style>
</head>

<body>


<?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout admin-layout">

    
    <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">

        <div class="page-head">
            <div>
                <h1>Program Categories</h1>
                <p>
                    These are the options faculty choose from in the
                    <strong>Category</strong> dropdown when creating or editing a course.
                    Hiding a category keeps existing courses intact but removes it from the form.
                </p>
            </div>

            <span class="count-pill">
                <?php echo e($categories->count()); ?>

                categor<?php echo e($categories->count() === 1 ? 'y' : 'ies'); ?>

            </span>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-ok">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-err">
                Please check the form:

                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid">

            
            <section class="card">

                <div class="card-hd">
                    All categories
                </div>

                <?php if($categories->isEmpty()): ?>

                    <div class="empty">
                        No categories yet. Add the first one using the form.
                    </div>

                <?php else: ?>

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

                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <tr>

                                <td>
                                    <div class="cat-name">
                                        <?php echo e($category->name); ?>

                                    </div>

                                    <?php if($category->description): ?>
                                        <div class="cat-desc">
                                            <?php echo e($category->description); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="pill <?php echo e($category->is_active ? 'pill-on' : 'pill-off'); ?>">
                                        <?php echo e($category->is_active ? 'Visible' : 'Hidden'); ?>

                                    </span>
                                </td>

                                <td>
                                    <span class="pill pill-use">
                                        <?php echo e($category->courses_using); ?>

                                    </span>
                                </td>

                                <td>
                                    <div class="row-actions">

                                        <button
                                            type="button"
                                            class="btn"
                                            onclick="toggleEdit(<?php echo e($category->id); ?>)">
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('admin.categories.toggle', $category->id)); ?>">
                                            <?php echo csrf_field(); ?>

                                            <button type="submit" class="btn">
                                                <?php echo e($category->is_active ? 'Hide' : 'Show'); ?>

                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('admin.categories.destroy', $category->id)); ?>"
                                            onsubmit="return confirm('Delete “<?php echo e($category->name); ?>”?<?php echo e($category->courses_using > 0 ? ' ' . $category->courses_using . ' course(s) still use this label and will keep it until edited.' : ''); ?>');">
                                            <?php echo csrf_field(); ?>

                                            <button type="submit" class="btn btn-danger">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                            
                            <tr
                                class="edit-row"
                                id="edit-<?php echo e($category->id); ?>">

                                <td colspan="4">

                                    <form
                                        method="POST"
                                        action="<?php echo e(route('admin.categories.update', $category->id)); ?>">

                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>

                                        <div class="edit-grid">

                                            <div>
                                                <label for="name-<?php echo e($category->id); ?>">
                                                    Name
                                                </label>

                                                <input
                                                    class="input"
                                                    id="name-<?php echo e($category->id); ?>"
                                                    name="name"
                                                    value="<?php echo e($category->name); ?>"
                                                    required>
                                            </div>

                                            <div>
                                                <label for="desc-<?php echo e($category->id); ?>">
                                                    Description
                                                </label>

                                                <input
                                                    class="input"
                                                    id="desc-<?php echo e($category->id); ?>"
                                                    name="description"
                                                    value="<?php echo e($category->description); ?>"
                                                    placeholder="Optional">
                                            </div>

                                            <label
                                                class="check"
                                                style="margin-top:18px;">

                                                <input
                                                    type="checkbox"
                                                    name="is_active"
                                                    value="1"
                                                    <?php if($category->is_active): echo 'checked'; endif; ?>>

                                                Visible
                                            </label>

                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                                style="margin-top:18px;">
                                                Save
                                            </button>

                                        </div>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>

                <?php endif; ?>

            </section>

            
            <section class="card">

                <div class="card-hd">
                    Add a category
                </div>

                <div class="card-bd">

                    <form
                        method="POST"
                        action="<?php echo e(route('admin.categories.store')); ?>">

                        <?php echo csrf_field(); ?>

                        <div class="field">

                            <label for="new-name">
                                Category name
                            </label>

                            <input
                                class="input"
                                id="new-name"
                                name="name"
                                value="<?php echo e(old('name')); ?>"
                                placeholder="e.g. BS Data Science"
                                required>

                        </div>

                        <div class="field">

                            <label for="new-desc">
                                Description
                            </label>

                            <input
                                class="input"
                                id="new-desc"
                                name="description"
                                value="<?php echo e(old('description')); ?>"
                                placeholder="Optional — shown here only">

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            style="width:100%;padding:11px;">
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
    function toggleEdit(id) {
        var row = document.getElementById('edit-' + id);

        if (row) {
            row.classList.toggle('open');
        }
    }
</script>


<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/program-categories.blade.php ENDPATH**/ ?>