<x-faculty.layout title="Activity Reviews · {{ $course->title }} | UpSkill" data-faculty-page="faculty-activity-reviews" use-app-css use-app-js>
    <x-slot:head><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>
    <main class="main">
            <div class="review-page">
                @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
                    ['label' => 'My Courses', 'url' => route('faculty.courses')],
                    ['label' => $course->title, 'url' => route('faculty.courses.manage', $course->id)],
                    ['label' => 'Activity Reviews'],
                ]])

                <header class="review-header">
                    <div>
                        <h1>Activity Reviews</h1>
                        <p>Review and grade assignment submissions for <strong>{{ $course->title }}</strong>.</p>
                    </div>
                    <a class="back-manage" href="{{ route('faculty.courses.manage', $course->id) }}">Back to Manage Course</a>
                </header>

                <x-flash-toast :message="session('success')" />
                @if($errors->any())
                    <div class="review-alert error" role="alert">{{ $errors->first() }}</div>
                @endif

                <section class="review-stats" aria-label="Submission review totals">
                    <div class="review-stat"><span>Awaiting review</span><strong>{{ $pendingCount }}</strong></div>
                    <div class="review-stat"><span>Reviewed</span><strong>{{ $reviewedCount }}</strong></div>
                    <div class="review-stat"><span>All submissions</span><strong>{{ $allCount }}</strong></div>
                </section>

                <nav class="review-filters" aria-label="Filter assignment submissions">
                    @foreach([
                        'pending' => ['label' => 'Needs review', 'count' => $pendingCount],
                        'reviewed' => ['label' => 'Reviewed', 'count' => $reviewedCount],
                        'all' => ['label' => 'All submissions', 'count' => $allCount],
                    ] as $filterKey => $filter)
                        <a class="review-filter" href="{{ route('faculty.activities.reviews', ['id' => $course->id, 'status' => $filterKey, 'activity_id' => $activityId]) }}" @if($status === $filterKey) aria-current="page" @endif>
                            {{ $filter['label'] }} <span class="review-filter-count">{{ $filter['count'] }}</span>
                        </a>
                    @endforeach
                    @if($activityId)
                        <a class="clear-activity-filter" href="{{ route('faculty.activities.reviews', ['id' => $course->id, 'status' => $status]) }}">Clear activity filter</a>
                    @endif
                </nav>

                @if($submissions->isNotEmpty())
                    <div class="submission-list">
                        @foreach($submissions as $submission)
                            @php
                                $activity = $submission->activity;
                                $lesson = $activity->lesson;
                                $module = $lesson->module;
                            @endphp
                            <article class="review-submission" id="submission-{{ $submission->id }}">
                                <header class="submission-top">
                                    <div>
                                        <div class="submission-course-path">
                                            <span>{{ $module->title ?? 'Module' }}</span><span>{{ $lesson->title }}</span>
                                        </div>
                                        <h2 class="submission-title">{{ $activity->title }}</h2>
                                        <div class="submission-student">{{ $submission->student->name ?? 'Learner' }}</div>
                                        <span class="submission-meta">
                                            {{ $submission->student->student_id ?? $submission->student->user_code ?? 'Student' }}
                                            · Attempt {{ $submission->attempt_number }}
                                            · Submitted {{ $submission->submitted_at?->format('M j, Y g:i A') ?? $submission->created_at?->format('M j, Y g:i A') }}
                                        </span>
                                    </div>
                                    <span class="submission-status is-{{ Illuminate\Support\Str::slug($submission->status) }}">{{ Illuminate\Support\Str::headline($submission->status) }}</span>
                                </header>

                                @if($activity->instructions)
                                    <div class="assignment-instructions"><strong>Assignment instructions</strong><div class="up-ckeditor-wrapper"><div class="ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($activity->instructions) !!}</div></div></div>
                                @endif

                                <div class="submission-content">
                                    <section class="submission-response-block">
                                        <h3>Student response</h3>
                                        @if($submission->response_text)
                                            <div class="submission-response">{{ $submission->response_text }}</div>
                                        @else
                                            <div class="submission-response">The student submitted an attachment without written text.</div>
                                        @endif
                                        @if($submission->submission_path)
                                            <a class="submission-attachment" href="{{ route('faculty.lesson-activities.download', $submission->id) }}">Download submitted file</a>
                                        @endif
                                    </section>
                                    @if($submission->status !== 'submitted')
                                        <section class="submission-result">
                                            <h3>Review result</h3>
                                            @if($submission->score !== null)
                                                <p><strong>Score:</strong> {{ $submission->score }} / {{ $activity->max_points }} points</p>
                                            @endif
                                            @if($submission->feedback)
                                                <p><strong>Feedback:</strong> {{ $submission->feedback }}</p>
                                            @endif
                                            <p><strong>Reviewed:</strong> {{ $submission->reviewed_at?->format('M j, Y g:i A') ?? '—' }}</p>
                                            <p><strong>Reviewed by:</strong> {{ $submission->reviewer->name ?? 'Faculty' }}</p>
                                        </section>
                                    @endif
                                </div>

                                @if($submission->status === 'submitted')
                                    <form class="review-form" method="POST" action="{{ route('faculty.lesson-activity.review', [$course->id, $activity->id, $submission->id]) }}">
                                        @csrf
                                        <label>Score (maximum {{ $activity->max_points ?? 10000 }} points)
                                            <input type="number" name="score" min="0" max="{{ $activity->max_points ?? 10000 }}" step="1" value="{{ old('score') }}" placeholder="Enter score" required>
                                        </label>
                                        <label>Feedback for the student
                                            <textarea name="feedback" maxlength="5000" placeholder="Explain the result or what the student should revise">{{ old('feedback') }}</textarea>
                                        </label>
                                        <div class="review-help">Passing score: {{ $activity->passing_percent ?? 70 }}%. A lower score returns the assignment as needing revision.</div>
                                        <button class="review-submit" type="submit">Save grade and feedback</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div class="review-pagination">
                        @include('components.pagination', ['paginator' => $submissions, 'ariaLabel' => 'Activity submission pages'])
                    </div>
                @else
                    <div class="review-empty">
                        <h2>@if($status === 'pending')Nothing to review right now.@elseif($status === 'reviewed')No reviewed submissions yet.@else No submissions yet.@endif</h2>
                        <p>Assignment submissions for this course will appear here, grouped with their lesson and student details.</p>
                    </div>
                @endif
            </div>
        </main>

    <x-slot:scripts>
@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
