
<x-admin.layout title="Learning Pathways | Upskill Admin" page="admin-pathways" :user="$user" :use-auth-user="false">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout">
    <main class="main">
        <div class="pathway-page">
            <div class="page-head">
                <div><h1>Learning pathways</h1><p class="lead">Group published courses into guided journeys for students.</p></div>
            </div>
            <x-flash-toast :message="session('success')" />
            @if($errors->any())<div class="alert error" role="alert">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('admin.pathways.store') }}" class="panel create-panel">
                @csrf
                <h2>Create a pathway</h2>
                <div class="form-grid">
                    <input type="text" name="name" required maxlength="150" value="{{ old('pathway_id') ? '' : old('name') }}" placeholder="Pathway name" aria-label="Pathway name">
                    <input type="text" name="description" maxlength="1000" value="{{ old('pathway_id') ? '' : old('description') }}" placeholder="Short description" aria-label="Short description">
                </div>
                <p class="adm-inline-admin-pathways-1">Assign courses</p>
                <div class="course-grid">
                    @foreach($courses as $course)
                        <label class="course-option"><input type="checkbox" value="{{ $course->id }}" data-course-title="{{ $course->title }}" @checked(!old('pathway_id') && in_array((string) $course->id, array_map('strval', is_array(old('course_ids')) ? old('course_ids') : []), true))> <span>{{ $course->title }}</span></label>
                    @endforeach
                </div>
                <div class="course-sequence" data-course-sequence>
                    <h3>Course sequence</h3>
                    <p class="course-sequence-help">Use the arrows to set the order students should follow.</p>
                    <ol class="course-sequence-list" aria-live="polite"></ol>
                </div>
                <button class="btn" type="submit">Create pathway</button>
            </form>

            <section class="pathway-list" aria-labelledby="pathway-list-title">
                <div class="list-heading">
                    <h2 id="pathway-list-title">Created pathways</h2>
                    <span>{{ $pathways->total() }} {{ $pathways->total() === 1 ? 'pathway' : 'pathways' }}</span>
                </div>
                @if($pathways->isEmpty())
                    <div class="empty-state">No pathways yet. Create one above to get started.</div>
                @else
                    <div class="table-wrap paginated-table-scroll">
                        <table class="pathway-table">
                            <thead><tr><th>Pathway</th><th>Courses</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @foreach($pathways as $pathway)
                                    <tr>
                                        <td>
                                            <span class="pathway-name">{{ $pathway->name }}</span>
                                            <span class="pathway-description">{{ $pathway->description ?: 'No description added.' }}</span>
                                        </td>
                                        <td class="pathway-courses">{{ $pathway->courses->count() }} {{ $pathway->courses->count() === 1 ? 'course' : 'courses' }}</td>
                                        <td><span class="pathway-status {{ $pathway->is_active ? 'is-active' : 'is-inactive' }}">{{ $pathway->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td>
                                            <div class="row-actions">
                                                <button type="button" class="btn-secondary" data-view-pathway
                                                    data-id="{{ $pathway->id }}"
                                                    data-name="{{ $pathway->name }}"
                                                    data-description="{{ $pathway->description ?? '' }}"
                                                    data-status="{{ $pathway->is_active ? 'Active' : 'Inactive' }}"
                                                    data-courses="{{ $pathway->courses->pluck('title')->values()->toJson() }}">View</button>
                                                <button type="button" class="btn-secondary" data-edit-pathway
                                                    data-id="{{ $pathway->id }}"
                                                    data-name="{{ $pathway->name }}"
                                                    data-description="{{ $pathway->description ?? '' }}"
                                                    data-active="{{ $pathway->is_active ? '1' : '0' }}"
                                                    data-course-ids="{{ $pathway->courses->pluck('id')->implode(',') }}"
                                                    data-update-url="{{ route('admin.pathways.update', $pathway->id) }}">Edit</button>
                                                <form method="POST" action="{{ route('admin.pathways.destroy', $pathway->id) }}" class="adm-inline-admin-pathways-2">
                                                    @csrf
                                                    <button type="submit" class="btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @include('components.pagination', ['paginator' => $pathways])
                @endif
            </section>
        </div>
    </main>
</div>

<div class="modal-backdrop" id="pathway-view-modal" hidden>
    <section class="pathway-modal" role="dialog" aria-modal="true" aria-labelledby="pathway-view-title" tabindex="-1">
        <header class="modal-head">
            <div><h2 id="pathway-view-title">Pathway details</h2><p>Review the course sequence and status for this pathway.</p></div>
            <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
        </header>
        <div class="modal-body">
            <div class="view-meta">
                <div><strong>Status</strong><span id="pathway-view-status"></span></div>
                <div><strong>Courses</strong><span id="pathway-view-course-count"></span></div>
            </div>
            <p class="view-description" id="pathway-view-description"></p>
            <div class="view-courses"><h3>Courses in this pathway</h3><div class="view-course-list" id="pathway-view-courses"></div></div>
        </div>
        <footer class="modal-footer"><button type="button" class="btn-secondary" data-close-modal>Close</button></footer>
    </section>
</div>

<div class="modal-backdrop" id="pathway-edit-modal" hidden>
    <section class="pathway-modal" role="dialog" aria-modal="true" aria-labelledby="pathway-edit-title" tabindex="-1">
        <header class="modal-head">
            <div><h2 id="pathway-edit-title">Edit pathway</h2><p>Update the pathway details and assigned courses.</p></div>
            <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>
        </header>
        <form method="POST" class="edit-form" id="pathway-edit-form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="pathway_id" id="pathway-edit-id">
            <div class="modal-body">
                <div class="field"><label class="field-label" for="pathway-edit-name">Pathway name</label><input id="pathway-edit-name" type="text" name="name" maxlength="150" required></div>
                <div class="field"><label class="field-label" for="pathway-edit-description">Description</label><textarea id="pathway-edit-description" name="description" maxlength="1000"></textarea></div>
                <label class="active-edit"><input type="checkbox" id="pathway-edit-active" name="is_active" value="1"> Pathway is active</label>
                <p class="adm-inline-admin-pathways-3">Assign courses</p>
                <div class="course-grid">
                    @foreach($courses as $course)
                        <label class="course-option"><input type="checkbox" value="{{ $course->id }}" data-course-title="{{ $course->title }}"> <span>{{ $course->title }}</span></label>
                    @endforeach
                </div>
                <div class="course-sequence" data-course-sequence>
                    <h3>Course sequence</h3>
                    <p class="course-sequence-help">Use the arrows to set the order students should follow.</p>
                    <ol class="course-sequence-list" aria-live="polite"></ol>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="btn">Save changes</button>
            </footer>
        </form>
    </section>
</div>

<script>
    (function () {
        var viewModal = document.getElementById('pathway-view-modal');
        var editModal = document.getElementById('pathway-edit-modal');
        var editForm = document.getElementById('pathway-edit-form');
        var previousFocus = null;

        function renderCourseSequence(form, preferredIds) {
            var sequenceList = form.querySelector('.course-sequence-list');
            var checkboxes = Array.from(form.querySelectorAll('.course-grid input[type="checkbox"]'));
            var selectedIds = new Set(checkboxes.filter(function (checkbox) { return checkbox.checked; }).map(function (checkbox) { return checkbox.value; }));
            var currentIds = Array.from(sequenceList.querySelectorAll('input[name="course_ids[]"]')).map(function (input) { return input.value; });
            var orderedIds = [];
            var seenIds = new Set();

            [preferredIds || [], currentIds, checkboxes.filter(function (checkbox) { return checkbox.checked; }).map(function (checkbox) { return checkbox.value; })]
                .flat()
                .forEach(function (courseId) {
                    courseId = String(courseId);
                    if (selectedIds.has(courseId) && !seenIds.has(courseId)) {
                        seenIds.add(courseId);
                        orderedIds.push(courseId);
                    }
                });

            sequenceList.replaceChildren();
            orderedIds.forEach(function (courseId, index) {
                var checkbox = checkboxes.find(function (item) { return item.value === courseId; });
                if (!checkbox) return;

                var item = document.createElement('li');
                item.className = 'course-sequence-item';
                var title = document.createElement('span');
                title.textContent = (index + 1) + '. ' + (checkbox.dataset.courseTitle || checkbox.closest('label').textContent.trim());

                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'course_ids[]';
                hiddenInput.value = courseId;

                var actions = document.createElement('div');
                actions.className = 'course-sequence-actions';
                var moveUp = document.createElement('button');
                moveUp.type = 'button';
                moveUp.textContent = '↑';
                moveUp.setAttribute('aria-label', 'Move ' + title.textContent + ' earlier');
                moveUp.disabled = index === 0;
                moveUp.addEventListener('click', function () {
                    var nextIds = orderedIds.slice();
                    nextIds.splice(index, 1);
                    nextIds.splice(index - 1, 0, courseId);
                    renderCourseSequence(form, nextIds);
                });

                var moveDown = document.createElement('button');
                moveDown.type = 'button';
                moveDown.textContent = '↓';
                moveDown.setAttribute('aria-label', 'Move ' + title.textContent + ' later');
                moveDown.disabled = index === orderedIds.length - 1;
                moveDown.addEventListener('click', function () {
                    var nextIds = orderedIds.slice();
                    nextIds.splice(index, 1);
                    nextIds.splice(index + 1, 0, courseId);
                    renderCourseSequence(form, nextIds);
                });

                actions.append(moveUp, moveDown);
                item.append(title, hiddenInput, actions);
                sequenceList.appendChild(item);
            });
        }

        document.querySelectorAll('form').forEach(function (form) {
            if (!form.querySelector('.course-sequence-list')) return;
            form.querySelectorAll('.course-grid input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    renderCourseSequence(form);
                });
            });
            renderCourseSequence(form);
        });

        var createForm = document.querySelector('.create-panel');
        if (createForm) {
            renderCourseSequence(createForm, @json(!old('pathway_id') && is_array(old('course_ids')) ? array_map('strval', old('course_ids')) : []));
        }

        function openModal(modal) {
            previousFocus = document.activeElement;
            modal.hidden = false;
            modal.querySelector('[role="dialog"]').focus();
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.hidden = true;
            document.body.style.overflow = '';
            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
            }
        }

        function closeAllModals() {
            [viewModal, editModal].forEach(function (modal) {
                if (!modal.hidden) {
                    closeModal(modal);
                }
            });
        }

        document.querySelectorAll('[data-view-pathway]').forEach(function (button) {
            button.addEventListener('click', function () {
                var courses = [];
                try {
                    courses = JSON.parse(button.dataset.courses || '[]');
                } catch (error) {
                    courses = [];
                }

                document.getElementById('pathway-view-title').textContent = button.dataset.name || 'Pathway details';
                document.getElementById('pathway-view-status').textContent = button.dataset.status || 'Unknown';
                document.getElementById('pathway-view-course-count').textContent = courses.length + (courses.length === 1 ? ' course' : ' courses');
                document.getElementById('pathway-view-description').textContent = button.dataset.description || 'No description added.';

                var courseList = document.getElementById('pathway-view-courses');
                courseList.replaceChildren();
                if (!courses.length) {
                    var empty = document.createElement('span');
                    empty.className = 'none';
                    empty.textContent = 'No courses assigned.';
                    courseList.appendChild(empty);
                } else {
                    courses.forEach(function (courseName, index) {
                        var item = document.createElement('span');
                        item.textContent = (index + 1) + '. ' + courseName;
                        courseList.appendChild(item);
                    });
                }

                openModal(viewModal);
            });
        });

        function fillEditForm(button) {
            var courseIds = (button.dataset.courseIds || '').split(',').filter(Boolean);
            editForm.action = button.dataset.updateUrl;
            document.getElementById('pathway-edit-id').value = button.dataset.id || '';
            document.getElementById('pathway-edit-name').value = button.dataset.name || '';
            document.getElementById('pathway-edit-description').value = button.dataset.description || '';
            document.getElementById('pathway-edit-active').checked = button.dataset.active === '1';
            editForm.querySelectorAll('.course-grid input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.checked = courseIds.indexOf(checkbox.value) !== -1;
            });
            renderCourseSequence(editForm, courseIds);
        }

        document.querySelectorAll('[data-edit-pathway]').forEach(function (button) {
            button.addEventListener('click', function () {
                fillEditForm(button);
                openModal(editModal);
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeAllModals();
            });
        });

        [viewModal, editModal].forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });

        var oldPathwayId = @json(old('pathway_id'));
        if (oldPathwayId) {
            var editButton = Array.from(document.querySelectorAll('[data-edit-pathway]')).find(function (button) {
                return button.dataset.id === String(oldPathwayId);
            });
            if (editButton) {
                fillEditForm(editButton);
                document.getElementById('pathway-edit-name').value = @json(old('name'));
                document.getElementById('pathway-edit-description').value = @json(old('description'));
                document.getElementById('pathway-edit-active').checked = @json((bool) old('is_active'));
                var oldCourseIds = @json(array_map('strval', is_array(old('course_ids')) ? old('course_ids') : []));
                editForm.querySelectorAll('.course-grid input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = oldCourseIds.indexOf(checkbox.value) !== -1;
                });
                renderCourseSequence(editForm, oldCourseIds);
                openModal(editModal);
            }
        }
    })();
</script>
</x-admin.layout>
