<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Pathways | Upskill Admin</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{--navy:#0d1b6e;--gold:#dba617;--text:#101b49;--muted:#68758c;--line:#e2e8f3;}
    *{box-sizing:border-box;}
    body{margin:0;background:#f7f9fc;color:var(--text);font-family:"Segoe UI",Roboto,Arial,sans-serif;}
    .main{padding:36px 32px 60px;min-width:0;}
    .pathway-page{max-width:1180px;margin:0 auto;}
    .page-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:28px;flex-wrap:wrap;}
    h1{font-size:32px;margin:0 0 7px;}
    .lead{color:var(--muted);margin:0;font-size:14px;}
    .alert{padding:12px 14px;border-radius:10px;margin-bottom:18px;font-size:13px;}
    .alert.success{background:#ecfdf3;color:#067647;}
    .alert.error{background:#fff1f2;color:#b42318;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px;margin-bottom:20px;box-shadow:0 12px 30px rgba(10,31,110,.06);}
    .panel h2{font-size:19px;margin:0 0 16px;}
    .form-grid{display:grid;grid-template-columns:1fr 2fr;gap:14px;}
    .pathway-page input[type=text],.pathway-page textarea{width:100%;padding:11px 12px;border:1px solid #dce4f0;border-radius:9px;font:inherit;color:var(--text);}
    .pathway-page textarea{min-height:90px;resize:vertical;}
    .course-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px;}
    .course-option{padding:10px;border:1px solid #e3e8f1;border-radius:9px;font-size:13px;background:#fff;display:flex;gap:8px;align-items:flex-start;}
    .course-option:has(input:checked){background:#eef2ff;border-color:#cbd5f5;}
    .course-option input{margin-top:2px;}
    .btn{background:var(--navy);color:#fff;border:0;border-radius:999px;padding:11px 18px;font-weight:800;cursor:pointer;}
    .btn:hover{background:#172b91;}
    .btn-secondary{background:#fff;color:var(--navy);border:1px solid #c8d2e5;border-radius:10px;padding:10px 15px;font-weight:750;cursor:pointer;}
    .btn-secondary:hover{background:#f2f5fc;}
    .btn-danger{background:#fff1f2;color:#b42318;border:1px solid #fecdd3;border-radius:10px;padding:10px 15px;font-weight:800;cursor:pointer;}
    .btn-danger:hover{background:#ffe4e8;}
    .create-panel .btn{margin-top:18px;}
    .pathway-list{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 12px 30px rgba(10,31,110,.06);overflow:hidden;}
    .list-heading{display:flex;justify-content:space-between;align-items:center;padding:20px 22px;border-bottom:1px solid var(--line);gap:12px;}
    .list-heading h2{margin:0;font-size:19px;}
    .list-heading span{font-size:13px;color:var(--muted);}
    .table-wrap{overflow-x:auto;}
    .pathway-table{width:100%;border-collapse:collapse;min-width:760px;}
    .pathway-table th,.pathway-table td{padding:15px 18px;border-bottom:1px solid #edf0f6;text-align:left;vertical-align:middle;}
    .pathway-table th{font-size:11px;letter-spacing:.07em;text-transform:uppercase;color:#71809a;background:#f8faff;}
    .pathway-table tr:last-child td{border-bottom:0;}
    .pathway-name{font-weight:800;font-size:14px;color:var(--text);display:block;}
    .pathway-description{display:block;max-width:340px;margin-top:4px;font-size:12px;line-height:1.45;color:var(--muted);overflow:hidden;text-overflow:ellipsis;}
    .pathway-status{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:800;}
    .pathway-status.is-active{background:#ecfdf3;color:#067647;}
    .pathway-status.is-inactive{background:#f1f3f8;color:#667085;}
    .pathway-courses{color:#485675;font-size:13px;}
    .row-actions{display:flex;gap:7px;align-items:center;white-space:nowrap;}
    .row-actions button,.row-actions .btn-danger{padding:8px 11px;font-size:12px;}
    .empty-state{padding:42px 20px;text-align:center;color:var(--muted);font-size:14px;}
    .modal-backdrop{position:fixed;inset:0;z-index:11000;display:grid;place-items:center;padding:20px;background:rgba(5,13,48,.62);backdrop-filter:blur(3px);}
    .modal-backdrop[hidden]{display:none;}
    .pathway-modal{width:min(100%,680px);max-height:min(88vh,820px);overflow:auto;background:#fff;border-radius:18px;box-shadow:0 24px 70px rgba(6,16,52,.3);}
    .modal-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:20px 24px;border-bottom:1px solid var(--line);}
    .modal-head h2{margin:0;font-size:20px;}
    .modal-head p{margin:5px 0 0;color:var(--muted);font-size:13px;}
    .modal-close{width:36px;height:36px;flex:0 0 36px;border:0;border-radius:50%;background:#f0f3f9;color:#34415e;font-size:22px;line-height:1;cursor:pointer;}
    .modal-body{padding:22px 24px;}
    .modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid var(--line);}
    .edit-form .field{margin-bottom:16px;}
    .edit-form label.field-label{display:block;margin-bottom:6px;font-size:12px;font-weight:800;color:#3f4c67;}
    .active-edit{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:700;margin:16px 0;}
    .view-meta{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px;}
    .view-meta div{padding:13px;background:#f7f9fd;border:1px solid #e8edf5;border-radius:11px;}
    .view-meta strong{display:block;margin-bottom:5px;color:#71809a;font-size:10px;letter-spacing:.08em;text-transform:uppercase;}
    .view-meta span{font-size:14px;font-weight:700;color:var(--text);}
    .view-description{margin:0 0 18px;color:#4e5b75;font-size:14px;line-height:1.55;white-space:pre-wrap;}
    .view-courses h3{font-size:14px;margin:0 0 10px;}
    .view-course-list{display:flex;flex-wrap:wrap;gap:8px;}
    .view-course-list span{padding:7px 10px;border-radius:8px;background:#eef2ff;color:#263c85;font-size:12px;font-weight:700;}
    .view-course-list .none{background:#f3f4f6;color:#667085;font-weight:500;}
    .modal-actions button:focus-visible,.row-actions button:focus-visible,.modal-close:focus-visible{outline:3px solid #f0bd32;outline-offset:2px;}
    @media(max-width:760px){.main{padding:24px 14px 40px;}.form-grid{grid-template-columns:1fr;}.panel{padding:17px;}.list-heading{align-items:flex-start;flex-direction:column;padding:17px;}.modal-head,.modal-body{padding:18px;}.modal-footer{padding:14px 18px;}.view-meta{grid-template-columns:1fr;}}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => $user])
<div class="layout">
    @include('components.admin-sidebar')
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
                <p style="font-weight:700;font-size:13px;margin:18px 0 9px">Assign courses</p>
                <div class="course-grid">
                    @foreach($courses as $course)
                        <label class="course-option"><input type="checkbox" name="course_ids[]" value="{{ $course->id }}" @checked(!old('pathway_id') && in_array((string) $course->id, array_map('strval', is_array(old('course_ids')) ? old('course_ids') : []), true))> <span>{{ $course->title }}</span></label>
                    @endforeach
                </div>
                <button class="btn" type="submit">Create pathway</button>
            </form>

            <section class="pathway-list" aria-labelledby="pathway-list-title">
                <div class="list-heading">
                    <h2 id="pathway-list-title">Created pathways</h2>
                    <span>{{ $pathways->count() }} {{ $pathways->count() === 1 ? 'pathway' : 'pathways' }}</span>
                </div>
                @if($pathways->isEmpty())
                    <div class="empty-state">No pathways yet. Create one above to get started.</div>
                @else
                    <div class="table-wrap">
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
                                                <form method="POST" action="{{ route('admin.pathways.destroy', $pathway->id) }}" style="margin:0">
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
                @endif
            </section>
        </div>
    </main>
</div>

<div class="modal-backdrop" id="pathway-view-modal" hidden>
    <section class="pathway-modal" role="dialog" aria-modal="true" aria-labelledby="pathway-view-title" tabindex="-1">
        <header class="modal-head">
            <div><h2 id="pathway-view-title">Pathway details</h2><p>Review the courses and status for this pathway.</p></div>
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
                <p style="font-weight:800;font-size:13px;margin:18px 0 9px">Assign courses</p>
                <div class="course-grid">
                    @foreach($courses as $course)
                        <label class="course-option"><input type="checkbox" name="course_ids[]" value="{{ $course->id }}"> <span>{{ $course->title }}</span></label>
                    @endforeach
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
                    courses.forEach(function (courseName) {
                        var item = document.createElement('span');
                        item.textContent = courseName;
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
            editForm.querySelectorAll('input[name="course_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = courseIds.indexOf(checkbox.value) !== -1;
            });
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
                editForm.querySelectorAll('input[name="course_ids[]"]').forEach(function (checkbox) {
                    checkbox.checked = oldCourseIds.indexOf(checkbox.value) !== -1;
                });
                openModal(editModal);
            }
        }
    })();
</script>
@include('components.responsive')
</body>
</html>

