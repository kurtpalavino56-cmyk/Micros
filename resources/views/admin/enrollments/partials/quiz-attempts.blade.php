<div class="record-work">
    <h3>{{ $quiz->title }}</h3>
    @forelse($attempts as $attempt)
        <div class="record-attempt">
            <span class="record-pill {{ $attempt->passed ? 'passed' : 'failed' }}">{{ $attempt->passed ? 'Passed' : 'Not passed' }}</span>
            <span class="record-meta">Score {{ $attempt->score }} · {{ $attempt->submitted_at?->format('M j, Y g:i A') ?? 'Submission time unavailable' }}</span>
            @if($attempt->answers->isNotEmpty())
                <ul class="record-answers">
                    @foreach($attempt->answers as $answer)
                        <li class="{{ $answer->is_correct ? 'correct' : 'incorrect' }}">
                            <strong>{{ $answer->question->question ?? 'Question' }}</strong><br>
                            Answer: {{ $answer->answer ?: 'No answer' }}
                            @if(!$answer->is_correct && $answer->question?->correct_answer)
                                · Correct answer: {{ $answer->question->correct_answer }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @empty
        <div class="record-meta">No quiz attempt recorded.</div>
    @endforelse
</div>
