<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $course->title }} | Learner Progress &amp; Verification | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{--navy:#13176b;--ink:#0e1a3a;--muted:#64748b;--line:#e5e7eb;--gold:#dba617;--green:#15803d;--red:#b91c1c;--shadow:0 8px 22px rgba(19,23,107,.06);}
    *{box-sizing:border-box;}
    body{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:var(--ink);margin:0;background:#f8faff;}
    a{text-decoration:none;color:inherit;}button{font:inherit;cursor:pointer;}
    .main{padding:28px 32px 48px;min-width:0;width:100%;max-width:1440px;margin-inline:auto;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
    main .page-head h2{font-family:'Plus Jakarta Sans',Inter,ui-sans-serif,system-ui,sans-serif;font-size:27px;font-weight:800;letter-spacing:-.04em;color:var(--ink);margin:0 0 6px;}
    main .page-head p{font-size:14px;font-weight:400;color:var(--muted);margin:0;}
    .review-summary{display:flex;align-items:center;gap:8px;padding:12px 15px;margin:0 0 16px;border:1px solid #fed7aa;border-radius:10px;background:#fff7ed;color:#9a3412;font-size:13px;font-weight:600;}
    .review-summary strong{font-size:15px;}
    .tabs-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px;}
    .tabs{display:flex;flex:1;min-width:0;gap:8px;border-bottom:1px solid var(--line);margin:0;}
    .tab{padding:10px 13px;color:#64748b;border-bottom:2px solid transparent;font-size:13px;font-weight:700;}
    .tab.active{color:var(--navy);border-bottom-color:var(--navy);}
    .course-head-actions{display:flex;align-items:flex-start;gap:9px;}
    .course-head-actions>.action,.course-head-actions .export-dropdown{width:156px;}
    .course-head-actions>.action,.course-head-actions .export-dropdown summary{min-height:40px;padding:8px 12px;font-size:12px;}
    .course-head-actions>.action{background:var(--navy);border-color:var(--navy);color:#fff;}
    .course-head-actions>.action:hover{background:#20278a;}
    .export-dropdown{position:relative;}
    .export-dropdown summary{display:flex;align-items:center;justify-content:center;gap:7px;list-style:none;cursor:pointer;}
    .export-dropdown summary::-webkit-details-marker{display:none;}
    .download-icon,.sort-icon{width:15px;height:15px;flex:0 0 15px;}
    .export-dropdown summary::after{content:"";width:7px;height:7px;margin:0 0 3px 1px;border-right:1.5px solid currentColor;border-bottom:1.5px solid currentColor;display:inline-block;transform:rotate(45deg);}
    .export-menu{position:absolute;top:calc(100% + 6px);right:0;z-index:10;display:grid;gap:5px;width:100%;min-width:0;padding:7px;background:#fff;border:1px solid var(--line);border-radius:9px;box-shadow:0 8px 22px rgba(19,23,107,.12);}
    .export-menu .action{justify-content:flex-start;}
    .table-toolbar{display:flex;flex:0 0 auto;justify-content:flex-end;margin:0;}
    .table-toolbar form{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;}
    .table-search{position:relative;display:flex;align-items:center;}
    .table-search input,.table-sort select{min-height:36px;padding:7px 10px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:var(--ink);font:inherit;font-size:12px;}
    .table-search input{width:min(240px,50vw);padding-left:33px;}
    .table-search-icon{position:absolute;left:11px;width:15px;height:15px;color:#64748b;pointer-events:none;}
    .table-sort{display:block;}
    .sort-select-wrap{position:relative;display:block;}
    .sort-select-wrap select{padding-left:31px;}
    .sort-select-wrap .sort-icon{position:absolute;top:50%;left:10px;transform:translateY(-50%);color:#64748b;pointer-events:none;}
    .table-toolbar form>.action{min-height:36px;padding:7px 12px;background:#facc15;border-color:#eab308;color:#713f12;}
    .table-toolbar form>.action:hover{background:#eab308;}
    .action.remove{background:#fff;border-color:#fecaca;color:#b91c1c;}
    .action.remove:hover{background:#fef2f2;}
    .student-table-wrap{max-height:max(180px,calc(100vh - 340px));overflow:auto;overscroll-behavior:contain;background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:var(--shadow);}
    .student-table{width:100%;min-width:760px;border-collapse:collapse;text-align:left;}
    .student-table th,.student-table td{padding:13px 16px;border-bottom:1px solid var(--line);vertical-align:middle;}
    .student-table th{position:sticky;top:0;z-index:1;background:#f1f4fb;color:#475569;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;}
    .student-table td{font-size:13px;}
    .student-table tbody tr:last-child td{border-bottom:0;}
    .student-table tbody tr:hover{background:#fafbff;}
    .student-table .student-cell{display:flex;align-items:center;gap:11px;min-width:190px;}
    .student-table .student-cell .student-name{font-weight:700;color:var(--ink);}
    .student-table .student-id{color:var(--muted);white-space:nowrap;}
    .student-table .progress{width:auto;text-align:left;}
    .student-table .review-actions{justify-content:flex-start;}
    .student-table .empty-cell{padding:32px;text-align:center;color:var(--muted);}
    .avatar{width:42px;height:42px;flex:0 0 42px;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#e8edfb;color:var(--navy);font-weight:800;background-size:cover;background-position:center;}
    .status{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 9px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;white-space:nowrap;}
    .status.needs-review{background:#fff7ed;color:#c2410c;}.status.completed{background:#dcfce7;color:#166534;}.status.rejected{background:#fee2e2;color:#b91c1c;}
    .progress{width:96px;color:#475569;font-size:12px;font-weight:700;text-align:right;white-space:nowrap;}
    .review-actions{display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end;}
    .action{display:inline-flex;align-items:center;justify-content:center;padding:7px 10px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:var(--navy);font-size:12px;font-weight:700;text-decoration:none;}
    .action:hover{background:#f8fafc;}.action.verify{background:var(--navy);border-color:var(--navy);color:#fff;}.action.verify:hover{background:#20278a;}.action.reject{color:var(--red);border-color:#fecaca;}
    .action.export-pdf{background:#fff1f2;border-color:#fecdd3;color:#be123c;}
    .action.export-word{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
    .action.export-excel{background:#f0fdf4;border-color:#bbf7d0;color:#15803d;}
    .empty-state{padding:32px;background:#fff;border:1px dashed var(--line);border-radius:10px;text-align:center;color:var(--muted);font-size:14px;}
    .student-removal-modal{position:fixed;inset:0;z-index:5000;display:flex;align-items:center;justify-content:center;padding:18px;background:rgba(15,23,42,.55);}
    .student-removal-modal[hidden]{display:none;}
    .student-removal-dialog{width:min(500px,100%);padding:23px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.25);outline:none;}
    .student-removal-dialog h2{margin:0 0 9px;color:var(--ink);font-size:19px;}
    .student-removal-dialog>p{margin:0 0 17px;color:var(--muted);font-size:13px;line-height:1.55;}
    .student-removal-dialog form>label{display:block;margin-bottom:6px;color:var(--ink);font-size:12px;font-weight:800;}
    .student-removal-dialog textarea{display:block;width:100%;min-height:100px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;color:var(--ink);font:inherit;font-size:13px;resize:vertical;}
    .student-removal-dialog textarea:focus{outline:3px solid rgba(19,23,107,.16);border-color:var(--navy);}
    .student-removal-error{margin:6px 0 0;color:#b91c1c;font-size:12px;}
    .student-removal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:17px;}
    body.student-removal-modal-open{overflow:hidden;}
    .pagination{margin-top:16px;}
    .flash{padding:11px 14px;margin-bottom:14px;border-radius:8px;background:#ecfdf3;color:#166534;font-size:13px;font-weight:600;}
    @media(max-width:760px){.main{padding:22px 16px 36px;}.course-head-actions{flex-wrap:wrap;}.tabs-toolbar{align-items:stretch;flex-direction:column;}.tabs{overflow-x:auto;scrollbar-width:none;}.tab{flex:0 0 auto;white-space:nowrap;}.table-toolbar,.table-toolbar form{width:100%;}.table-toolbar form{justify-content:flex-start;}.review-summary{align-items:flex-start;}}
</style>
</head>
<body>
@include('components.authenticated-topbar')
<div class="layout faculty-sidebar-layout">
    @include('components.faculty-sidebar')
    <main class="main">
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'Learner Progress & Verification', 'url' => route('faculty.students')],
            ['label' => $course->title],
        ]])
        <div class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">{{ $course->title }}</h2>
                <p class="faculty-page-subtitle">Review course progress and verify learners who have completed all requirements.</p>
            </div>
            <div class="course-head-actions">
                <a class="action" href="{{ route('faculty.courses.manage', $course->id) }}">Go to course</a>
                @if($filter === 'all')
                    <details class="export-dropdown">
                        <summary class="action"><svg class="download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M5 20h14"></path></svg><span>Download</span></summary>
                        <div class="export-menu">
                            <a class="action export-pdf" href="{{ route('faculty.students.course.export', ['id' => $course->id, 'format' => 'pdf']) }}">PDF</a>
                            <a class="action export-word" href="{{ route('faculty.students.course.export', ['id' => $course->id, 'format' => 'word']) }}">Word (.doc)</a>
                            <a class="action export-excel" href="{{ route('faculty.students.course.export', ['id' => $course->id, 'format' => 'excel']) }}">Excel (.csv)</a>
                        </div>
                    </details>
                @endif
            </div>
        </div>

        <x-flash-toast :message="session('success')" />

        @if($filter === 'needs_review' && $needsReviewCount > 0)
            <div class="review-summary"><strong>{{ $needsReviewCount }}</strong> {{ Illuminate\Support\Str::plural('learner', $needsReviewCount) }} completed the course requirements and {{ $needsReviewCount === 1 ? 'is' : 'are' }} waiting for your verification.</div>
        @endif

        <div class="tabs-toolbar">
            <nav class="tabs" aria-label="Completion verification filters">
                @if($course->requires_faculty_verification)
                    <a class="tab {{ $filter === 'needs_review' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'needs_review']) }}">Awaiting verification <span>({{ $needsReviewCount }})</span></a>
                @endif
                <a class="tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'all']) }}">All students <span>({{ $totalStudents }})</span></a>
            </nav>

            <div class="table-toolbar">
                <form method="GET" action="{{ route('faculty.students.course', $course->id) }}">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="table-search">
                        <svg class="table-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                        <input id="student-search" type="search" name="q" value="{{ $search }}" placeholder="Search students" aria-label="Search students">
                    </div>
                    <label class="table-sort" for="student-sort">
                        <span class="sort-select-wrap">
                            <svg class="sort-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4v16"></path><path d="m4.5 7.5 3.5-3.5 3.5 3.5"></path><path d="M16 20V4"></path><path d="m12.5 16.5 3.5 3.5 3.5-3.5"></path></svg>
                            <select id="student-sort" name="sort" aria-label="Sort students">
                                <option value="name_asc" @selected($sort === 'name_asc')>Name A–Z</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Name Z–A</option>
                            </select>
                        </span>
                    </label>
                    <button class="action" type="submit">Filter</button>
                </form>
            </div>
        </div>

        <script>
            (function () {
                var searchInput = document.getElementById('student-search');
                if (!searchInput || !searchInput.form) return;

                var searchTimer;
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(searchTimer);
                    searchTimer = window.setTimeout(function () {
                        searchInput.form.requestSubmit();
                    }, 350);
                });
            })();
        </script>

        <div class="student-table-wrap">
            <table class="student-table">
                <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Student ID</th>
                        <th scope="col">Completion status</th>
                        <th scope="col">Progress</th>
                        <th scope="col">Verification status</th>
                        <th scope="col">{{ $filter === 'all' ? 'Action' : 'Review' }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($enrollments as $enrollment)
                    @php
                        $user = $enrollment->user;
                        $needsReview = $course->requires_faculty_verification
                            && in_array($enrollment->faculty_verification_status, ['pending', 'not_required'], true)
                            && $enrollment->completion_status === \App\Services\MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION;
                        $institutionalStatus = $needsReview
                            ? 'Awaiting Faculty Verification'
                            : \App\Support\CompletionStatusPresenter::institutionalLabel(
                                $enrollment->completion_status,
                                $enrollment->faculty_verification_status,
                                $enrollment->academic_unit_confirmation_status,
                            );
                    @endphp
                    <tr>
                        <td>
                            <div class="student-cell">
                                <div class="avatar" @if($user?->avatar_url) style="background-image:url('{{ $user->avatar_url }}')" @endif>@unless($user?->avatar_url){{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}@endunless</div>
                                <span class="student-name">{{ $user->name ?? 'Student' }}</span>
                            </div>
                        </td>
                        <td class="student-id">{{ $user->student_id ?? $user->user_code ?? 'Not provided' }}</td>
                        <td>{{ $institutionalStatus }}</td>
                        <td><span class="progress">{{ (int) $enrollment->progress_percent }}% progress</span></td>
                        <td><span class="status {{ $needsReview ? 'needs-review' : ($institutionalStatus === 'Completed' ? 'completed' : ($institutionalStatus === 'Rejected' ? 'rejected' : '')) }}">{{ $needsReview ? 'Awaiting verification' : $institutionalStatus }}</span></td>
                        <td>
                            @if($filter === 'all')
                                <button
                                    class="action remove student-remove-button"
                                    type="button"
                                    data-remove-url="{{ route('faculty.students.enrollments.remove', ['id' => $course->id, 'enrollment' => $enrollment->id]) }}"
                                    data-student-name="{{ $user->name ?? 'Student' }}"
                                    data-enrollment-id="{{ $enrollment->id }}"
                                >Remove</button>
                            @elseif($needsReview)
                                <div class="review-actions">
                                    <a class="action verify" href="{{ route('faculty.students.enrollments.review', ['id' => $course->id, 'enrollment' => $enrollment->id]) }}">Review</a>
                                </div>
                            @else
                                <span aria-hidden="true">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-cell" colspan="6">
                            @if($filter === 'needs_review')
                                No learners are waiting for faculty verification in this course.
                            @else
                                No students are enrolled in this course yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($filter === 'all')
            <div class="student-removal-modal" id="student-removal-modal" hidden>
                <section class="student-removal-dialog" role="dialog" aria-modal="true" aria-labelledby="student-removal-title" aria-describedby="student-removal-help" tabindex="-1">
                    <h2 id="student-removal-title">Remove student from course?</h2>
                    <p id="student-removal-help">This removes <strong id="student-removal-name"></strong> from {{ $course->title }}. They may enroll again later. Enter the reason that will be shared with the student.</p>
                    <form id="student-removal-form" method="POST">
                        @csrf
                        <input id="student-removal-enrollment" type="hidden" name="target_enrollment" value="{{ old('target_enrollment') }}">
                        <label for="student-removal-reason">Reason for removal <span aria-hidden="true">*</span></label>
                        <textarea id="student-removal-reason" name="reason" rows="4" maxlength="1000" required>{{ old('reason') }}</textarea>
                        @error('reason')<p class="student-removal-error" role="alert">{{ $message }}</p>@enderror
                        <div class="student-removal-actions">
                            <button class="action" type="button" data-close-student-removal>Cancel</button>
                            <button class="action remove" type="submit">Confirm removal</button>
                        </div>
                    </form>
                </section>
            </div>
            <script>
                (function () {
                    var modal = document.getElementById('student-removal-modal');
                    var form = document.getElementById('student-removal-form');
                    var name = document.getElementById('student-removal-name');
                    var reason = document.getElementById('student-removal-reason');
                    var enrollmentInput = document.getElementById('student-removal-enrollment');
                    if (!modal || !form || !name || !reason || !enrollmentInput) return;

                    function closeModal() {
                        modal.hidden = true;
                        document.body.classList.remove('student-removal-modal-open');
                    }

                    document.querySelectorAll('.student-remove-button').forEach(function (button) {
                        button.addEventListener('click', function () {
                            form.action = button.dataset.removeUrl;
                            name.textContent = button.dataset.studentName || 'this student';
                            reason.value = '';
                            enrollmentInput.value = button.dataset.enrollmentId || '';
                            modal.hidden = false;
                            document.body.classList.add('student-removal-modal-open');
                            modal.querySelector('.student-removal-dialog').focus();
                            reason.focus();
                        });
                    });

                    modal.querySelectorAll('[data-close-student-removal]').forEach(function (button) {
                        button.addEventListener('click', closeModal);
                    });
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) closeModal();
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !modal.hidden) closeModal();
                    });

                    if (enrollmentInput.value) {
                        var failedButton = Array.from(document.querySelectorAll('.student-remove-button'))
                            .find(function (button) { return button.dataset.enrollmentId === enrollmentInput.value; });
                        if (failedButton) {
                            form.action = failedButton.dataset.removeUrl;
                            name.textContent = failedButton.dataset.studentName || 'this student';
                            modal.hidden = false;
                            document.body.classList.add('student-removal-modal-open');
                            modal.querySelector('.student-removal-dialog').focus();
                            reason.focus();
                        }
                    }
                })();
            </script>
        @endif
        @if($enrollments->hasPages())
            <div class="pagination">
                @include('components.pagination', ['paginator' => $enrollments, 'ariaLabel' => 'Learner pages'])
            </div>
        @endif
    </main>
</div>
@include('components.responsive')
</body>
</html>
