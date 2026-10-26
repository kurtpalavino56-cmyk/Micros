<x-faculty.layout title="{{ $course->title }} | Learner Progress & Verification | Upskill" data-faculty-page="faculty-students-course">
    <x-slot:head><link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>
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
        @if(session('attemptGrantSummary'))
            <div class="attempt-grant-summary" role="status">
                @foreach(session('attemptGrantSummary') as $grantSummary)
                    <span><strong>{{ $grantSummary['student'] }}</strong> · {{ $grantSummary['quiz'] }}: {{ $grantSummary['used'] }} / {{ $grantSummary['allowed'] }} attempts used / allowed</span>
                @endforeach
            </div>
        @endif

        @if($filter === 'needs_review' && $needsReviewCount > 0)
            <div class="review-summary"><strong>{{ $needsReviewCount }}</strong> {{ Illuminate\Support\Str::plural('learner', $needsReviewCount) }} completed the course requirements and {{ $needsReviewCount === 1 ? 'is' : 'are' }} waiting for your verification.</div>
        @endif

        <div class="tabs-toolbar">
            <nav class="tabs" aria-label="Completion verification filters">
                @if($course->requires_faculty_verification)
                    <a class="tab {{ $filter === 'needs_review' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'needs_review']) }}">Awaiting verification <span>({{ $needsReviewCount }})</span></a>
                @endif
                <a class="tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'all']) }}">All students <span>({{ $totalStudents }})</span></a>
                <a class="tab {{ $filter === 'attempts' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'attempts']) }}">Quiz &amp; Exam Attempts</a>
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

        @if($filter === 'attempts')
            <form id="bulk-attempt-grants" class="attempt-grant-bulk" method="POST" action="{{ route('faculty.students.course.attempt-grants', $course->id) }}" hidden>
                @csrf
                <strong class="attempt-grant-bulk-title">Grant attempts to the selected rows</strong>
                <div class="attempt-grant-field attempt-grant-amount-field"><label for="bulk-attempt-amount">Additional attempts</label><input id="bulk-attempt-amount" type="number" name="amount" min="1" max="10" value="1" required></div>
                <button class="action verify" type="button" data-open-attempt-reason>Grant attempts to selected</button>
            </form>
            <div class="student-table-wrap">
                <table class="student-table attempt-grant-table">
                    <thead><tr><th scope="col"><input class="attempt-grant-select-all" type="checkbox" aria-label="Select all exhausted attempts"></th><th scope="col">Student</th><th scope="col">Quiz / exam</th><th scope="col">Attempts used / allowed</th><th scope="col">Last grant</th><th scope="col">Grant attempts</th></tr></thead>
                    <tbody>
                    @forelse($attemptRows as $index => $row)
                        <tr>
                            <td><input class="attempt-grant-select" type="checkbox" name="selected_pairs[]" value="{{ $row['student']->id }}:{{ $row['quiz']->id }}" form="bulk-attempt-grants" aria-label="Select {{ $row['student']->name }} for bulk grant"></td>
                            <td><div class="student-cell"><div class="avatar">{{ strtoupper(substr($row['student']->name, 0, 1)) }}</div><div class="student-identity"><span class="student-name">{{ $row['student']->name }}</span><small class="student-id">{{ $row['student']->student_id ?? $row['student']->user_code }}</small></div></div></td>
                            <td>{{ $row['quiz']->title }} @if($row['quiz']->module?->is_final)<span class="status final-exam-label">Final exam</span>@endif</td>
                            <td><strong>{{ $row['status']['used'] }} / {{ $row['status']['allowed'] }}</strong></td>
                            <td>@if($row['lastGrant'])<span title="Reason: {{ $row['lastGrant']->reason }}&#10;Granted by: {{ $row['lastGrant']->grantor?->name ?? 'Unknown' }}">{{ $row['lastGrant']->created_at->format('M j, Y') }} · +{{ $row['lastGrant']->extra_attempts }} {{ $row['lastGrant']->extra_attempts === 1 ? 'attempt' : 'attempts' }}</span>@else<span class="student-id">None</span>@endif</td>
                            <td>
                                <form class="attempt-grant-row-form" method="POST" action="{{ route('faculty.students.course.attempt-grants', $course->id) }}">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $row['student']->id }}">
                                    <input type="hidden" name="quiz_id" value="{{ $row['quiz']->id }}">
                                    <div class="attempt-grant-field attempt-grant-amount-field"><input id="attempt-amount-{{ $index }}" type="number" name="amount" min="1" max="10" value="1" aria-label="Additional attempts" required></div>
                                    <button class="action verify" type="button" data-open-attempt-reason>Grant</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty-cell" colspan="6">No students have exhausted their quiz or exam attempts.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div id="attempt-grant-reason-modal" class="attempt-grant-modal" hidden>
                <section class="attempt-grant-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="attempt-grant-modal-title" aria-describedby="attempt-grant-modal-help" tabindex="-1">
                    <h2 id="attempt-grant-modal-title">Reason for attempt grant</h2>
                    <p id="attempt-grant-modal-help">Add a reason to record why these additional attempts are being granted.</p>
                    <form id="attempt-grant-reason-form">
                        <label for="attempt-grant-modal-reason">Reason <span aria-hidden="true">*</span></label>
                        <textarea id="attempt-grant-modal-reason" rows="4" maxlength="1000" required></textarea>
                        <div class="attempt-grant-modal-actions">
                            <button class="action" type="button" data-close-attempt-reason>Cancel</button>
                            <button class="action verify" type="submit">Confirm grant</button>
                        </div>
                    </form>
                </section>
            </div>
            <script>
                (function () {
                    var bulkForm = document.getElementById('bulk-attempt-grants');
                    var selectAll = document.querySelector('.attempt-grant-select-all');
                    var selectors = Array.from(document.querySelectorAll('.attempt-grant-select'));
                    var modal = document.getElementById('attempt-grant-reason-modal');
                    var modalDialog = modal && modal.querySelector('.attempt-grant-modal-dialog');
                    var reasonForm = document.getElementById('attempt-grant-reason-form');
                    var reasonInput = document.getElementById('attempt-grant-modal-reason');
                    var targetForm = null;
                    var previousFocus = null;
                    if (!bulkForm || !modal || !reasonForm || !reasonInput) return;

                    function updateSelection() {
                        var checkedCount = selectors.filter(function (selector) { return selector.checked; }).length;
                        bulkForm.hidden = checkedCount === 0;
                        if (selectAll) {
                            selectAll.disabled = selectors.length === 0;
                            selectAll.checked = selectors.length > 0 && checkedCount === selectors.length;
                            selectAll.indeterminate = checkedCount > 0 && checkedCount < selectors.length;
                        }
                    }

                    function closeModal() {
                        modal.hidden = true;
                        document.body.classList.remove('attempt-grant-modal-open');
                        targetForm = null;
                        if (previousFocus) previousFocus.focus();
                    }

                    selectors.forEach(function (selector) {
                        selector.addEventListener('change', updateSelection);
                    });
                    if (selectAll) {
                        selectAll.addEventListener('change', function () {
                            selectors.forEach(function (selector) { selector.checked = selectAll.checked; });
                            updateSelection();
                        });
                    }
                    updateSelection();
                    reasonInput.addEventListener('input', function () {
                        reasonInput.setCustomValidity('');
                    });

                    document.querySelectorAll('[data-open-attempt-reason]').forEach(function (button) {
                        button.addEventListener('click', function () {
                            var form = button.closest('form');
                            if (!form || !form.reportValidity()) return;
                            targetForm = form;
                            previousFocus = button;
                            reasonInput.value = '';
                            modal.hidden = false;
                            document.body.classList.add('attempt-grant-modal-open');
                            modalDialog.focus();
                            reasonInput.focus();
                        });
                    });

                    reasonForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        if (!targetForm || !reasonForm.reportValidity()) return;
                        var reason = reasonInput.value.trim();
                        if (reason === '') {
                            reasonInput.setCustomValidity('Enter a reason for granting attempts.');
                            reasonInput.reportValidity();
                            return;
                        }
                        var storedReason = targetForm.querySelector('input[name="reason"]');
                        if (!storedReason) {
                            storedReason = document.createElement('input');
                            storedReason.type = 'hidden';
                            storedReason.name = 'reason';
                            targetForm.appendChild(storedReason);
                        }
                        storedReason.value = reason;
                        targetForm.submit();
                    });

                    modal.querySelectorAll('[data-close-attempt-reason]').forEach(function (button) {
                        button.addEventListener('click', closeModal);
                    });
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) closeModal();
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !modal.hidden) closeModal();
                    });
                })();
            </script>
        @else
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
        @endif
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

    <x-slot:scripts>
@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
