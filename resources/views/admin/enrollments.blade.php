
<x-admin.layout title="Completion verification · {{ $course?->title ?? 'All courses' }} | UPSKILL Admin" page="admin-enrollments">
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
            </div>
        </main>
    </div>
</x-admin.layout>