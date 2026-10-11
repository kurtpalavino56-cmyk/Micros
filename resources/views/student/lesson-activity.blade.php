<x-layout title="{{ $activity->title }} | UpSkill" shell="bare" page="lesson-activity">
<section class="activity-shell">
    <p><a href="{{ route('courses.learn', $activity->lesson->course_id) }}">← Back to course</a></p>
    <section class="activity-card">
        <p>{{ $activity->lesson->course->title }} · {{ $activity->lesson->title }}</p>
        <h1>{{ $activity->title }}</h1>
        <p>{{ Illuminate\Support\Str::headline($activity->activity_type) }}@if($activity->is_required) · Required for lesson completion @else · Optional @endif</p>
        @if($activity->instructions)<div class="up-ckeditor-wrapper"><div class="ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($activity->instructions) !!}</div></div>@endif
        @if($activity->activity_type === 'assignment')<div class="activity-note">Your instructor will review this submission. A passing result is required to complete this lesson activity.</div>@endif
        @if(session('activity_result'))
            @php($activityResult = session('activity_result'))
            <div class="activity-note" role="status">
                <p>{{ $activityResult['message'] }}</p>
                @if($activityResult['lesson_completed'])
                    <strong>This lesson is complete.</strong>
                @elseif($activityResult['pending_review'])
                    <strong>Your assignment is waiting for faculty review. The lesson will complete automatically if it passes.</strong>
                @endif
                @if($activityResult['next_requirement_url'])
                    <p><a href="{{ $activityResult['next_requirement_url'] }}">Continue to the next activity or assessment</a></p>
                @endif
                <p><a href="{{ route('courses.learn', $activity->lesson->course_id) }}">Return to course</a></p>
            </div>
        @elseif($lesson_completed)
            <p class="activity-note" role="status"><strong>This lesson is complete.</strong> <a href="{{ route('courses.learn', $activity->lesson->course_id) }}">Return to course</a></p>
        @elseif($submissions->first()?->status === 'submitted')
            <p class="activity-note" role="status">Your assignment is waiting for faculty review. The lesson will complete automatically if it passes.</p>
        @endif
        @if($errors->any())<div class="activity-error" role="alert">{{ $errors->first() }}</div>@endif
        @php($latestSubmission = $submissions->first())
        @if($latestSubmission?->status === 'passed' && ! $lesson_completed)
            <p class="activity-note" role="status">This activity passed. Finish any remaining lesson assessment to complete the lesson.</p>
        @elseif($latestSubmission?->status === 'needs_revision')
            <p class="activity-note" role="status">Your instructor requested a revision. Update your response and submit it again.</p>
        @endif
        @if(! $lesson_completed && $latestSubmission?->status !== 'passed' && $latestSubmission?->status !== 'submitted')
            <form method="POST" action="{{ route('lesson-activities.submit', $activity->id) }}" enctype="multipart/form-data">
                @csrf
                <p><label for="response_text">Your response</label></p>
                <textarea class="activity-input" id="response_text" name="response_text" rows="7" maxlength="20000">{{ old('response_text') }}</textarea>
                <p><label for="submission_file">Attach a file (optional)</label></p>
                <input type="file" id="submission_file" name="submission_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.zip">
                <p><button class="activity-submit" type="submit">Submit activity</button></p>
            </form>
        @endif
    </section>
    <section class="activity-card"><h2>Your attempts</h2>
        @forelse($submissions as $submission)
            <div class="activity-row" id="submission-{{ $submission->id }}"><strong>Attempt {{ $submission->attempt_number }} · {{ Illuminate\Support\Str::headline($submission->status) }}</strong>
                @if($submission->score !== null)<p>Score: {{ $submission->score }} / {{ $activity->max_points ?? '—' }}</p>@endif
                @if($submission->feedback)<p>{{ $submission->feedback }}</p>@endif
                @if($submission->submission_path)<p><a href="{{ route('lesson-activities.download', $submission->id) }}">Download your submitted file</a></p>@endif
                <small>{{ $submission->submitted_at?->format('M j, Y g:i A') }}</small>
            </div>
        @empty<p>No submissions yet.</p>@endforelse
    </section>
</section>
</x-layout>
