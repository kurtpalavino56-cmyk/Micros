@php
    $latestAttemptPassed = (bool) $latestAttempt?->passed;
    $canContinue = $latestAttemptPassed || (! $status['exhausted'] && (bool) $status['unlock']);
    $quizAvailable = ! $status['exhausted'] && ! $status['unlock'] && ! $latestAttemptPassed;
@endphp
<section class="inline-assessment-panel" data-assessment-kind="quiz" data-assessment-id="{{ $quiz->id }}" data-module-order="{{ $module_order ?? '' }}" data-module-content-step="{{ ! empty($module_content_step) ? '1' : '0' }}" data-retry-required="{{ ! empty($module_content_step) && $latestAttempt && ! $latestAttemptPassed ? '1' : '0' }}" data-next-url="{{ $module_next_url ?? '' }}" data-assessment-url="{{ route('quiz.show', $quiz->id) }}" data-cooldown-until="{{ (int) ($status['unlock'] ?? 0) }}" data-can-continue="{{ $canContinue ? '1' : '0' }}" data-timer-remaining="{{ (int) ($timer_remaining_seconds ?? 0) }}">
    <header class="inline-assessment-header">
        <div class="inline-assessment-header-top">
            <span class="inline-assessment-eyebrow">{{ ! empty($module_content_step) ? 'Module quiz' : 'Lesson assessment' }}</span>
            @if($latestAttemptPassed)
                <span class="inline-assessment-feedback inline-assessment-feedback--compact is-success" role="status">
                    Latest attempt: {{ $latestAttempt->score }}%. You passed. Continue to the next required item.
                </span>
            @else
                <span class="inline-quiz-cooldown" data-quiz-cooldown role="timer" aria-live="polite" @unless($status['unlock'] && ! $status['exhausted']) hidden @endunless></span>
            @endif
        </div>
        <h2>{{ $quiz->title }}</h2>
        <p>{{ $quiz->lesson?->title ?? $quiz->module?->title ?? '' }} · {{ $quiz->questionsPerSetCount() }} questions · Pass {{ $quiz->passing_score }}%</p>
    </header>
    @if($quiz->instructions)<div class="inline-assessment-instructions up-ckeditor-wrapper"><div class="ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($quiz->instructions) !!}</div></div>@endif
    @if(($time_limit_seconds ?? 0) > 0 && $quizAvailable)
        <p class="inline-quiz-timer" data-inline-quiz-timer role="timer" aria-live="polite"></p>
    @endif

    @if(! $latestAttemptPassed && $status['exhausted'])
        <div class="inline-assessment-feedback is-warning" role="status">You have used all allowed attempts for this quiz.</div>
    @elseif($quizAvailable)
        <form class="inline-assessment-form inline-quiz-form" data-assessment-form method="POST" action="{{ route('quiz.submit', $quiz->id) }}">
            @csrf
            {{-- Assigned set only, served order; radios submit the ORIGINAL letter. --}}
            @foreach($presented_questions ?? [] as $item)
                @php($question = $item['question'])
                <fieldset class="inline-quiz-question">
                    <legend>{{ $loop->iteration }}. {{ $question->question }}</legend>
                    @if($question->type === 'Identification')
                        <input class="inline-quiz-input" name="answers[{{ $question->id }}]" required maxlength="2000">
                    @else
                        <div class="inline-quiz-options">
                            @foreach($item['options'] as $option)
                                <label><input type="radio" name="answers[{{ $question->id }}]" value="{{ $option['value'] }}" required><span>{{ $option['text'] }}</span></label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>
            @endforeach
            <p class="inline-assessment-error" data-assessment-error role="alert" hidden></p>
            <button class="inline-assessment-submit" type="submit">Submit lesson quiz</button>
        </form>
    @endif
    @if($latestAttemptPassed && ! empty($review_questions))
        <section class="inline-quiz-review">
            <h3>Correct answers</h3>
            @foreach($review_questions as $reviewQuestion)
                <fieldset class="inline-quiz-review-question">
                    <legend>{{ $loop->iteration }}. {{ $reviewQuestion['question'] }}</legend>
                    @if($reviewQuestion['type'] === 'Identification')
                        <p>Correct answer: {{ $reviewQuestion['review_answer'] }}</p>
                    @else
                        <div class="inline-quiz-review-options">
                            @foreach($reviewQuestion['options'] as $option)
                                <p class="{{ $option['is_correct'] ? 'is-correct' : '' }}">
                                    {{ $option['label'] }}. {{ $option['text'] }} @if($option['is_correct']) (Correct answer) @endif
                                </p>
                            @endforeach
                        </div>
                    @endif
                </fieldset>
            @endforeach
        </section>
    @endif
</section>
