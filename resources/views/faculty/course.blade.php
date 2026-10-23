<x-faculty.layout title="{{ $course->title }} | Learner Progress & Verification | Upskill" data-faculty-page="faculty-course">
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
                <div class="course-summary">
                    <span><strong>{{ $totalStudents }}</strong> {{ Illuminate\Support\Str::plural('student', $totalStudents) }} enrolled</span>
                    <span><strong>{{ $needsReviewCount }}</strong> awaiting faculty verification</span>
                    @unless($course->requires_faculty_verification)<span>Faculty verification is not required for this course</span>@endunless
                </div>
            </div>
            <a class="action" href="{{ route('faculty.courses.manage', $course->id) }}">Manage Course</a>
        </div>

        <x-flash-toast :message="session('success')" />

        @if($filter === 'needs_review' && $needsReviewCount > 0)
            <div class="review-summary"><strong>{{ $needsReviewCount }}</strong> {{ Illuminate\Support\Str::plural('learner', $needsReviewCount) }} completed the course requirements and {{ $needsReviewCount === 1 ? 'is' : 'are' }} waiting for your verification.</div>
        @endif

        <nav class="tabs" aria-label="Completion verification filters">
            <a class="tab {{ $filter === 'needs_review' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'needs_review']) }}">Awaiting verification <span>({{ $needsReviewCount }})</span></a>
            <a class="tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'all']) }}">All students <span>({{ $totalStudents }})</span></a>
        </nav>

        <div class="student-list">
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
            <article class="student-row">
                <div class="avatar" @if($user?->avatar_url) style="background-image:url('{{ $user->avatar_url }}')" @endif>@unless($user?->avatar_url){{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}@endunless</div>
                <div class="student-copy">
                    <h3>{{ $user->name ?? 'Student' }}</h3>
                    <div class="student-meta">
                        <span>{{ $user->student_id ?? $user->user_code ?? 'Student ID not provided' }}</span>
                        <span>{{ $institutionalStatus }}</span>
                    </div>
                </div>
                <span class="progress">{{ (int) $enrollment->progress_percent }}% progress</span>
                <span class="status {{ $needsReview ? 'needs-review' : ($institutionalStatus === 'Completed' ? 'completed' : ($institutionalStatus === 'Rejected' ? 'rejected' : '')) }}">{{ $needsReview ? 'Awaiting verification' : $institutionalStatus }}</span>
                @if($needsReview)
                    <div class="review-actions">
                        <a class="action verify" href="{{ route('faculty.students.enrollments.review', ['id' => $course->id, 'enrollment' => $enrollment->id]) }}">Review</a>
                    </div>
                @endif
            </article>
        @empty
            <div class="empty-state">
                @if($filter === 'needs_review')
                    No learners are waiting for faculty verification in this course.
                @else
                    No students are enrolled in this course yet.
                @endif
            </div>
        @endforelse
        </div>
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
