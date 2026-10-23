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

    <x-slot:scripts>
@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
