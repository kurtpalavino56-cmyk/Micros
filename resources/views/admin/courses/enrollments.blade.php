
<x-admin.layout title="Completion verification · {{ $course?->title ?? 'All courses' }} | UPSKILL Admin" page="admin-courses-enrollments">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">
        <main class="main enrollment-main">
            <div class="enrollment-content">
                <x-flash-toast :message="session('success')" />
                @if($errors->any())
                    <div class="enrollment-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <header class="enrollment-heading">
                    <div>
                        <h1>Completion Verification</h1>
                        <p>{{ $course?->title ?? 'Verify that learners completed all required course work across all courses.' }}</p>
                    </div>
                    <div class="enrollment-counts">
                        <span class="enrollment-count">{{ number_format($enrollments->total()) }} {{ Illuminate\Support\Str::plural('learner', $enrollments->total()) }}</span>
                        <span class="enrollment-count pending">{{ number_format($pendingConfirmations) }} awaiting confirmation</span>
                    </div>
                </header>

                <div class="enrollment-filter-row">
                    <nav class="enrollment-tabs" aria-label="Completion verification status filters">
                        @foreach(['all' => 'All', 'pending' => 'Awaiting verification', 'approved' => 'Verified', 'denied' => 'Not approved'] as $key => $label)
                            @php
                                $tabRoute = $course
                                    ? route('admin.courses.enrollments', ['id' => $course->id, 'filter' => $key])
                                    : route('admin.enrollments', array_filter(['filter' => $key, 'course_id' => $selectedCourseId], fn ($value) => $value !== null));
                            @endphp
                            <a class="enrollment-tab {{ $filter === $key ? 'active' : '' }}" href="{{ $tabRoute }}" @if($filter === $key) aria-current="page" @endif>
                                {{ $label }} <span class="enrollment-tab-count">{{ $statusCounts[$key] }}</span>
                            </a>
                        @endforeach
                    </nav>
                    @unless($course)
                        <form class="enrollment-course-filter" method="GET" action="{{ route('admin.enrollments') }}">
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <label for="enrollment-course-filter">Filter by course</label>
                            <select id="enrollment-course-filter" name="course_id">
                                <option value="">All courses</option>
                                @foreach($courses as $filterCourse)
                                    <option value="{{ $filterCourse->id }}" @selected((string) $selectedCourseId === (string) $filterCourse->id)>{{ $filterCourse->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit">Apply</button>
                        </form>
                    @endunless
                </div>

                <section class="enrollment-card" aria-label="Learner completion verification">
                    <div class="enrollment-table-wrap paginated-table-scroll">
                        <table class="enrollment-table {{ $course ? '' : 'global' }}">
                            <thead>
                                <tr>
                                    <th scope="col">Student</th>
                                    @unless($course)<th scope="col">Course</th>@endunless
                                    <th scope="col">Completion</th>
                                    <th scope="col">Faculty verification</th>
                                    <th scope="col">Academic unit</th>
                                    <th scope="col">Completed</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollments as $enrollment)
                                    @php
                                        $unitStatus = $enrollment->academic_unit_confirmation_status;
                                        $unitClass = $enrollment->academic_confirmation_actionable ? 'pending'
                                            : ($unitStatus === 'confirmed' ? 'confirmed' : ($unitStatus === 'rejected' ? 'rejected' : ''));
                                    @endphp
                                    <tr>
                                        <td><div class="enrollment-student">{{ $enrollment->student_name }}</div></td>
                                        @unless($course)<td>{{ $enrollment->course_title }}</td>@endunless
                                        <td>{{ ucfirst(str_replace('_', ' ', $enrollment->completion_status)) }}</td>
                                        <td>
                                            <span class="enrollment-status">{{ ucfirst(str_replace('_', ' ', $enrollment->faculty_verification_status)) }}</span>
                                            @if($enrollment->faculty_verified_by)<div class="enrollment-meta">By {{ $enrollment->faculty_verified_by }}</div>@endif
                                        </td>
                                        <td>
                                            <span class="enrollment-status {{ $unitClass }}">{{ ucfirst(str_replace('_', ' ', $unitStatus)) }}</span>
                                            @if($enrollment->academic_unit_confirmed_by)<div class="enrollment-meta">By {{ $enrollment->academic_unit_confirmed_by }}</div>@endif
                                        </td>
                                        <td>{{ $enrollment->completed_at?->format('M j, Y') ?? '—' }}</td>
                                        <td>
                                            <div class="enrollment-actions">
                                                @if($enrollment->academic_confirmation_actionable)
                                                    <a class="enrollment-button review" href="{{ route('admin.enrollments.show', $enrollment->id) }}">Review</a>
                                                @endif
                                                @if($filter === 'denied' && $enrollment->grant_redo_actionable)
                                                    <button class="enrollment-button grant" type="button" data-grant-redo-url="{{ route('admin.enrollments.grant-redo', $enrollment->id) }}" data-student-name="{{ $enrollment->student_name }}" data-course-title="{{ $enrollment->course_title }}">Grant another chance</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="enrollment-empty" colspan="{{ $course ? 6 : 7 }}">@if($statusCounts['all'] === 0){{ $course ? 'No enrollments for this course yet.' : 'No course enrollments yet.' }}@else No enrollments match this status.@endif</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('components.pagination', ['paginator' => $enrollments, 'ariaLabel' => 'Enrollment pagination'])
                </section>
                <dialog class="grant-redo-dialog" id="grant-redo-dialog" aria-labelledby="grant-redo-title" aria-describedby="grant-redo-description">
                    <form method="POST" id="grant-redo-form" action="">
                        @csrf
                        <h2 id="grant-redo-title">Grant another chance</h2>
                        <p id="grant-redo-description"></p>
                        <label for="grant-redo-reason">Reason for granting another chance</label>
                        <textarea id="grant-redo-reason" name="reason" rows="4" maxlength="1000" required></textarea>
                        <div class="grant-redo-actions">
                            <button class="enrollment-button" type="button" id="grant-redo-cancel">Cancel</button>
                            <button class="enrollment-button grant" type="submit">Grant and reset progress</button>
                        </div>
                    </form>
                </dialog>
            </div>
        </main>
    </div>
    <script>
        (function () {
            const dialog = document.getElementById('grant-redo-dialog');
            const form = document.getElementById('grant-redo-form');
            const description = document.getElementById('grant-redo-description');
            const reason = document.getElementById('grant-redo-reason');

            document.querySelectorAll('[data-grant-redo-url]').forEach(function (button) {
                button.addEventListener('click', function () {
                    form.action = button.dataset.grantRedoUrl;
                    description.textContent = button.dataset.studentName + ' will have to restart ' + button.dataset.courseTitle + ' from the beginning. Explain why this new attempt is being granted.';
                    reason.value = '';
                    dialog.showModal();
                    reason.focus();
                });
            });

            document.getElementById('grant-redo-cancel').addEventListener('click', function () {
                dialog.close();
            });
        }());
    </script>
</x-admin.layout>
