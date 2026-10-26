<x-layout title="{{ $quiz->title }} | UpSkill" shell="bare" page="quiz">
<section class="quiz-shell">
    <p><a href="{{ route('courses.learn', $quiz->course_id) }}">← Back to course</a></p>
    <section class="quiz-card">
        <p>{{ $quiz->course->title }}</p>
        <h1>{{ $quiz->title }}</h1>
        @if($quiz->instructions)<div class="quiz-instructions up-ckeditor-wrapper"><div class="ck-content rich-content">{!! \App\Support\RichTextSanitizer::sanitize($quiz->instructions) !!}</div></div>@endif
        <p>{{ $quiz->questions->count() }} questions · pass {{ $quiz->passing_score }}% · {{ $status['allowed'] === 0 ? 'unlimited' : max(0, $status['allowed'] - $status['used']).' attempt(s) remaining' }}</p>
        @if(($time_limit_seconds ?? 0) > 0 && ! $status['exhausted'] && ! $status['unlock'])
            <p class="quiz-notice quiz-timer" id="quiz-timer" role="timer" aria-live="polite"></p>
        @endif

        @if(session('quiz_result'))
            <div class="quiz-notice {{ session('quiz_result.passed') ? 'quiz-pass' : 'quiz-fail' }}" role="status">
                Attempt scored {{ session('quiz_result.score') }}%. {{ session('quiz_result.passed') ? 'You passed.' : 'You did not meet the passing score.' }}
                @if(session('quiz_result.passed') && session('quiz_result.lesson_completed'))
                    <p><strong>This lesson is complete.</strong></p>
                @elseif(session('quiz_result.passed') && session('quiz_result.pending_review'))
                    <p>Your assignment is waiting for faculty review. The lesson will complete automatically if it passes.</p>
                @endif
                @if(session('quiz_result.passed') && session('quiz_result.next_requirement_url'))
                    <p><a href="{{ session('quiz_result.next_requirement_url') }}">Continue to the next activity or assessment</a></p>
                @endif
                @if(session('quiz_result.passed'))
                    <p><a href="{{ route('courses.learn', $quiz->course_id) }}">Return to course</a></p>
                @endif
            </div>
        @endif
        @error('quiz')<div class="quiz-notice quiz-error" role="alert">{{ $message }}</div>@enderror

        @if($status['exhausted'])
            <div class="quiz-notice quiz-fail">You have used all allowed attempts for this quiz.</div>
        @elseif($status['unlock'])
            <div class="quiz-notice quiz-fail" id="quiz-cooldown" data-unlock-at="{{ (int) $status['unlock'] }}" role="status" aria-live="polite"></div>
        @else
            <form id="quiz-attempt-form" method="POST" action="{{ route('quiz.submit', $quiz->id) }}">
                @csrf
                @foreach($quiz->questions as $question)
                    <fieldset class="quiz-question">
                        <legend><strong>{{ $loop->iteration }}. {{ $question->question }}</strong></legend>
                        @if($question->type === 'Identification')
                            <input class="quiz-input" name="answers[{{ $question->id }}]" required maxlength="2000">
                        @else
                            <div class="quiz-options">
                                @foreach($question->options ?? [] as $option)
                                    <label class="quiz-option"><input type="radio" name="answers[{{ $question->id }}]" value="{{ chr(65 + $loop->index) }}" required><span>{{ $option }}</span></label>
                                @endforeach
                            </div>
                        @endif
                    </fieldset>
                @endforeach
                <button class="quiz-action" type="submit">Submit answers</button>
            </form>
        @endif
    </section>
</section>
@if(($time_limit_seconds ?? 0) > 0 && ! $status['exhausted'] && ! $status['unlock'])
    <script>
        (() => {
            const timer = document.getElementById('quiz-timer');
            const form = document.getElementById('quiz-attempt-form');
            let remaining = {{ (int) ($timer_remaining_seconds ?? 0) }};
            let interval = null;
            const render = () => {
                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;
                timer.textContent = `Time remaining: ${minutes}:${String(seconds).padStart(2, '0')}`;
                if (remaining <= 60) timer.classList.add('quiz-fail');
                if (remaining <= 0) {
                    window.clearInterval(interval);
                    form.submit();
                    return;
                }
                remaining -= 1;
            };
            render();
            interval = window.setInterval(render, 1000);
        })();
    </script>
@endif
@if($status['unlock'])
    <script>
        (() => {
            const notice = document.getElementById('quiz-cooldown');
            if (!notice) return;
            const unlockAt = Number(notice.dataset.unlockAt || 0);
            let interval = null;
            const render = () => {
                const remaining = Math.max(0, unlockAt - Date.now());
                if (remaining <= 0) {
                    window.clearInterval(interval);
                    window.location.reload();
                    return;
                }
                const totalSeconds = Math.ceil(remaining / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                notice.textContent = `Retake available in ${minutes}m ${String(seconds).padStart(2, '0')}s.`;
            };
            render();
            interval = window.setInterval(render, 1000);
        })();
    </script>
@endif
</x-layout>
