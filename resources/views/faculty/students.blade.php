<x-faculty.layout title="Learner Progress & Verification | Upskill" data-faculty-page="faculty-students">
    <x-slot:head><link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>
<main class="main">
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'Learner Progress & Verification'],
        ]])
        <div class="page-head faculty-page-heading">
            <div><h2 class="faculty-page-title">Learner Progress &amp; Verification</h2><p class="faculty-page-subtitle">Choose a course to review learner progress and verify completed requirements.</p></div>
        </div>

        @if($courses->total())
            <div class="course-table-wrap paginated-table-scroll">
                <table class="course-table" aria-label="Courses and learner verification status">
                    <thead>
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Learners</th>
                            <th scope="col">Course status</th>
                            <th scope="col">Verification</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <td class="course-title">{{ $course->title }}</td>
                                <td class="course-learners">{{ $course->students_count }} {{ Illuminate\Support\Str::plural('student', $course->students_count) }}</td>
                                <td><span class="course-status {{ Illuminate\Support\Str::slug($course->status) }}">{{ $course->status }}</span></td>
                                <td>
                                    @if($course->requires_faculty_verification)
                                        <span class="review-count {{ $course->faculty_reviews_count === 0 ? 'zero' : '' }}">
                                            {{ $course->faculty_reviews_count > 0 ? 'Needs review · '.$course->faculty_reviews_count : 'No pending review' }}
                                        </span>
                                    @else
                                        <span class="review-count zero">Faculty verification not required</span>
                                    @endif
                                </td>
                                <td><a class="manage-link" href="{{ route('faculty.students.course', $course->id) }}">Manage</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('components.pagination', ['paginator' => $courses])
        @else
            <div class="empty-state">You don’t have any courses yet.</div>
        @endif
    </main>

    <x-slot:scripts>
@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
