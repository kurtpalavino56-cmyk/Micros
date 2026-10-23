{{--
    resources/views/admin/Program_Categories.blade.php
    Admin › Management › Program Categories

    The list faculty choose from in the Category dropdown on the Create /
    Edit Course form. Expects: $user (UserPresenter::admin), $categories
    (CourseCategory collection, each with ->courses_using).
--}}
<x-admin.layout title="Program Categories | UPSKILL Admin" page="admin-program-categories">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
{{-- Shared authenticated Admin navbar --}}
<div class="layout admin-layout">

    {{-- Shared Admin sidebar --}}
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
                {{ $totalCategories }}
                categor{{ $totalCategories === 1 ? 'y' : 'ies' }}
            </span>
        </div>

        <x-flash-toast :message="session('success')" />

        @if ($errors->any())
            <div class="alert alert-err">
                Please check the form:

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid">

            {{-- ── List ──────────────────────────────────────── --}}
            <section class="card">

                <div class="card-hd">
                    All categories
                </div>

                @if ($categories->isEmpty())

                    <div class="empty">
                        No categories yet. Add the first one using the form.
                    </div>

                @else

                    <div class="paginated-table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Courses</th>
                                <th class="adm-inline-admin-program-categories-1">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        @foreach ($categories as $category)

                            <tr>

                                <td>
                                    <div class="cat-name">
                                        {{ $category->name }}
                                    </div>

                                    @if ($category->description)
                                        <div class="cat-desc">
                                            {{ $category->description }}
                                        </div>
                                    @endif
                                </td>

                                <td data-label="Status">
                                    <span class="pill {{ $category->is_active ? 'pill-on' : 'pill-off' }}">
                                        {{ $category->is_active ? 'Visible' : 'Hidden' }}
                                    </span>
                                </td>

                                <td data-label="Courses">
                                    <span class="pill pill-use">
                                        {{ $category->courses_using }}
                                    </span>
                                </td>

                                <td>
                                    <div class="row-actions">

                                        <button
                                            type="button"
                                            class="btn"
                                            onclick="toggleEdit({{ $category->id }})">
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.categories.toggle', $category->id) }}">
                                            @csrf

                                            <button type="submit" class="btn">
                                                {{ $category->is_active ? 'Hide' : 'Show' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.categories.destroy', $category->id) }}"
                                            onsubmit="return confirm('Delete “{{ $category->name }}”?{{ $category->courses_using > 0 ? ' ' . $category->courses_using . ' course(s) still use this label and will keep it until edited.' : '' }}');">
                                            @csrf

                                            <button type="submit" class="btn btn-danger">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                            {{-- Inline edit --}}
                            <tr
                                class="edit-row"
                                id="edit-{{ $category->id }}">

                                <td colspan="4">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.categories.update', $category->id) }}">

                                        @csrf
                                        @method('PATCH')

                                        <div class="edit-grid">

                                            <div>
                                                <label for="name-{{ $category->id }}">
                                                    Name
                                                </label>

                                                <input
                                                    class="input"
                                                    id="name-{{ $category->id }}"
                                                    name="name"
                                                    value="{{ $category->name }}"
                                                    required>
                                            </div>

                                            <div>
                                                <label for="desc-{{ $category->id }}">
                                                    Description
                                                </label>

                                                <input
                                                    class="input"
                                                    id="desc-{{ $category->id }}"
                                                    name="description"
                                                    value="{{ $category->description }}"
                                                    placeholder="Optional">
                                            </div>

                                            <label
                                                class="check adm-inline-admin-program-categories-2"
                                               >

                                                <input
                                                    type="checkbox"
                                                    name="is_active"
                                                    value="1"
                                                    @checked($category->is_active)>

                                                Visible
                                            </label>

                                            <button
                                                type="submit"
                                                class="btn btn-primary adm-inline-admin-program-categories-3"
                                               >
                                                Save
                                            </button>

                                        </div>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>
                    </table>
                    </div>
                    @include('components.pagination', ['paginator' => $categories])

                @endif

            </section>

            {{-- ── Add ────────────────────────────────────────── --}}
            <section class="card">

                <div class="card-hd">
                    Add a category
                </div>

                <div class="card-bd">

                    <form
                        method="POST"
                        action="{{ route('admin.categories.store') }}">

                        @csrf

                        <div class="field">

                            <label for="new-name">
                                Category name
                            </label>

                            <input
                                class="input"
                                id="new-name"
                                name="name"
                                value="{{ old('name') }}"
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
                                value="{{ old('description') }}"
                                placeholder="Optional — shown here only">

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary adm-inline-admin-program-categories-4"
                           >
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

{{-- Shared responsiveness layer --}}
</x-admin.layout>