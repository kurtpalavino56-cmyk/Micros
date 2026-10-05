<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $activity->title }} | UpSkill</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{margin:0;background:#f4f6fb;color:#172554;font-family:inherit}.activity-shell{max-width:900px;margin:48px auto;padding:24px}.activity-card{background:#fff;border:1px solid #e3e8f2;border-radius:18px;padding:28px;margin-bottom:18px;box-shadow:0 14px 35px #17255412}.activity-input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:12px;font:inherit}.activity-submit{border:0;border-radius:10px;background:#172554;color:#fff;padding:12px 20px;font:inherit;font-weight:700}.activity-row{padding:14px 0;border-bottom:1px solid #e7ebf3}.activity-note{padding:12px;border-radius:10px;background:#eff6ff}.activity-error{color:#991b1b}</style>
</head>
<body><main class="activity-shell">
    <p><a href="{{ route('courses.learn', $activity->lesson->course_id) }}">← Back to course</a></p>
    <section class="activity-card">
        <p>{{ $activity->lesson->course->title }} · {{ $activity->lesson->title }}</p>
        <h1>{{ $activity->title }}</h1>
        <p>{{ Illuminate\Support\Str::headline($activity->activity_type) }}@if($activity->is_required) · Required for lesson completion @else · Optional @endif</p>
        @if($activity->instructions)<div>{!! nl2br(e($activity->instructions)) !!}</div>@endif
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
            <div class="activity-row"><strong>Attempt {{ $submission->attempt_number }} · {{ Illuminate\Support\Str::headline($submission->status) }}</strong>
                @if($submission->score !== null)<p>Score: {{ $submission->score }} / {{ $activity->max_points ?? '—' }}</p>@endif
                @if($submission->feedback)<p>{{ $submission->feedback }}</p>@endif
                @if($submission->submission_path)<p><a href="{{ route('lesson-activities.download', $submission->id) }}">Download your submitted file</a></p>@endif
                <small>{{ $submission->submitted_at?->format('M j, Y g:i A') }}</small>
            </div>
        @empty<p>No submissions yet.</p>@endforelse
    </section>
</main></body></html>
