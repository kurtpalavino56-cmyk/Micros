<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quiz->title }} | UpSkill</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body{margin:0;background:#f4f6fb;color:#172554;font-family:inherit}.quiz-shell{max-width:900px;margin:48px auto;padding:24px}.quiz-card{background:#fff;border:1px solid #e3e8f2;border-radius:18px;padding:28px;box-shadow:0 14px 35px #17255412}.quiz-question{padding:20px 0;border-bottom:1px solid #e7ebf3}.quiz-question:last-child{border:0}.quiz-question label{display:block;font-weight:700;margin-bottom:12px}.quiz-options{display:grid;gap:9px}.quiz-option{display:flex;gap:10px;align-items:flex-start;background:#f7f9fd;padding:11px 14px;border-radius:10px}.quiz-option input{margin-top:4px}.quiz-input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:12px;font:inherit}.quiz-action{display:inline-block;border:0;border-radius:10px;background:#172554;color:#fff;padding:12px 20px;font:inherit;font-weight:700;cursor:pointer}.quiz-notice{padding:14px;border-radius:10px;margin:14px 0}.quiz-pass{background:#dcfce7;color:#166534}.quiz-fail{background:#fff7ed;color:#9a3412}.quiz-error{background:#fee2e2;color:#991b1b}
    </style>
</head>
<body>
<main class="quiz-shell">
    <p><a href="{{ route('courses.learn', $quiz->course_id) }}">← Back to course</a></p>
    <section class="quiz-card">
        <p>{{ $quiz->course->title }}</p>
        <h1>{{ $quiz->title }}</h1>
        @if($quiz->instructions)<p>{{ $quiz->instructions }}</p>@endif
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
            <div class="quiz-notice quiz-fail">You can retake this quiz after the 24-hour cooldown.</div>
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
</main>
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
</body>
</html>
