@php
    $latestAttemptPassed = (bool) $latestAttempt?->passed;
    $canContinue = $latestAttemptPassed || (! $status['exhausted'] && (bool) $status['unlock']);
    $quizAvailable = ! $status['exhausted'] && ! $status['unlock'] && ! $latestAttemptPassed;
@endphp
<section class="inline-assessment-panel" data-assessment-kind="quiz" data-assessment-id="{{ $quiz->id }}" data-can-continue="{{ $canContinue ? '1' : '0' }}" data-timer-remaining="{{ (int) ($timer_remaining_seconds ?? 0) }}">
    <header class="inline-assessment-header">
        <span class="inline-assessment-eyebrow">Lesson assessment</span>
        <h2>{{ $quiz->title }}</h2>
        <p>{{ $quiz->lesson?->title ?? '' }} · {{ $quiz->questions->count() }} questions · Pass {{ $quiz->passing_score }}%</p>
    </header>
    @if($quiz->instructions)<div class="inline-assessment-instructions">{{ $quiz->instructions }}</div>@endif
    @if(($time_limit_seconds ?? 0) > 0 && $quizAvailable)
        <p class="inline-quiz-timer" data-inline-quiz-timer role="timer" aria-live="polite"></p>
    @endif

    @if($latestAttemptPassed)
        <div class="inline-assessment-feedback is-success" role="status">
            Latest attempt: {{ $latestAttempt->score }}%. You passed. Continue to the next required item.
        </div>
    @elseif($status['exhausted'])
        <div class="inline-assessment-feedback is-warning" role="status">You have used all allowed attempts for this quiz.</div>
    @elseif($status['unlock'])
        <div class="inline-assessment-feedback is-warning quiz-cooldown" role="status">You can retake this quiz after the 24-hour cooldown.</div>
    @else
        <form class="inline-assessment-form inline-quiz-form" data-assessment-form method="POST" action="{{ route('quiz.submit', $quiz->id) }}">
            @csrf
            @foreach($quiz->questions as $question)
                <fieldset class="inline-quiz-question">
                    <legend>{{ $loop->iteration }}. {{ $question->question }}</legend>
                    @if($question->type === 'Identification')
                        <input class="inline-quiz-input" name="answers[{{ $question->id }}]" required maxlength="2000">
                    @else
                        <div class="inline-quiz-options">
                            @foreach($question->options ?? [] as $option)
                                <label><input type="radio" name="answers[{{ $question->id }}]" value="{{ chr(65 + $loop->index) }}" required><span>{{ $option }}</span></label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>
            @endforeach
            <p class="inline-assessment-error" data-assessment-error role="alert" hidden></p>
            <button class="inline-assessment-submit" type="submit">Submit lesson quiz</button>
        </form>
    @endif
</section>
