@php
    $latestSubmission = $submissions->first();
    $activityAccepted = in_array($latestSubmission?->status, ['completed', 'passed'], true);
    $activityPendingReview = $latestSubmission?->status === 'submitted';
    $canContinue = $lesson_completed || $activityAccepted || $activityPendingReview;
@endphp
<section class="inline-assessment-panel" data-assessment-kind="activity" data-assessment-id="{{ $activity->id }}" data-can-continue="{{ $canContinue ? '1' : '0' }}">
    <header class="inline-assessment-header">
        <span class="inline-assessment-eyebrow">{{ $activity->is_required ? 'Required activity' : 'Optional activity' }}</span>
        <h2>{{ $activity->title }}</h2>
        <p>{{ $activity->lesson->title }} · {{ Illuminate\Support\Str::headline($activity->activity_type) }}@if($activity->activity_type === 'assignment') · {{ $activity->max_points ?? 0 }} points · pass {{ $activity->passing_percent ?? 70 }}%@endif</p>
    </header>

    @if($activity->instructions)
        <div class="inline-assessment-instructions up-ckeditor-wrapper"><div class="ck-content">{!! \App\Support\RichTextSanitizer::sanitize($activity->instructions) !!}</div></div>
    @endif

    @if($lesson_completed)
        <div class="inline-assessment-feedback is-success" role="status">This lesson is complete.</div>
    @elseif($activityPendingReview)
        <div class="inline-assessment-feedback is-pending" role="status">
            Your assignment is waiting for faculty review. Continue will check the result; the lesson completes when it passes.
        </div>
    @elseif($activityAccepted)
        <div class="inline-assessment-feedback is-success" role="status">
            Activity accepted. Continue to the next required item.
        </div>
    @elseif($latestSubmission?->status === 'needs_revision')
        <div class="inline-assessment-feedback is-warning" role="status">
            Your instructor requested a revision. Review the feedback below and submit an updated response.
        </div>
    @endif

    @if(! $lesson_completed && ! $activityAccepted && ! $activityPendingReview)
        <form class="inline-assessment-form" data-assessment-form method="POST" action="{{ route('lesson-activities.submit', $activity->id) }}" enctype="multipart/form-data">
            @csrf
            <label for="inline-response-{{ $activity->id }}">Your response</label>
            <textarea id="inline-response-{{ $activity->id }}" name="response_text" rows="6" maxlength="20000"></textarea>
            <label for="inline-file-{{ $activity->id }}">Attach a file (optional)</label>
            <input id="inline-file-{{ $activity->id }}" type="file" name="submission_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.zip">
            <p class="inline-assessment-error" data-assessment-error role="alert" hidden></p>
            <button class="inline-assessment-submit" type="submit">{{ $activity->activity_type === 'assignment' ? 'Submit assignment' : 'Submit activity' }}</button>
        </form>
    @endif

    @if($submissions->isNotEmpty())
        <details class="inline-assessment-attempts">
            <summary>Previous submissions ({{ $submissions->count() }})</summary>
            @foreach($submissions as $submission)
                <div class="inline-assessment-attempt">
                    <strong>Attempt {{ $submission->attempt_number }} · {{ Illuminate\Support\Str::headline($submission->status) }}</strong>
                    @if($submission->score !== null)<span>{{ $submission->score }} / {{ $activity->max_points ?? '—' }}</span>@endif
                    @if($submission->feedback)<p>{{ $submission->feedback }}</p>@endif
                    @if($submission->submission_path)
                        <a href="{{ route('lesson-activities.download', $submission->id) }}">Download submitted file</a>
                    @endif
                </div>
            @endforeach
        </details>
    @endif
</section>
