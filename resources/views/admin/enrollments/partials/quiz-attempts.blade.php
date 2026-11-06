<div class="record-work">
    <h3>{{ $quiz->title }}</h3>
    @forelse($attempts as $attempt)
        @php
            // The attempt's presentation snapshot records the set and the
            // exact order the student was served, so review stays faithful
            // even after faculty edit the quiz or change the shuffle toggles.
            $presentation = is_array($attempt->presentation) ? $attempt->presentation : [];
            $servedOrder = array_map('intval', $presentation['question_ids'] ?? []);
            $choiceOrders = $presentation['choice_orders'] ?? [];
            $answers = $attempt->answers->sortBy(function ($answer) use ($servedOrder) {
                $position = array_search((int) $answer->question_id, $servedOrder, true);

                return $position === false ? PHP_INT_MAX - 1000000 + $answer->id : $position;
            })->values();
            $setName = $presentation['set_name'] ?? $attempt->set?->name;
        @endphp
        <div class="record-attempt">
            <span class="record-pill {{ $attempt->passed ? 'passed' : 'failed' }}">{{ $attempt->passed ? 'Passed' : 'Not passed' }}</span>
            <span class="record-meta">Score {{ $attempt->score }} · {{ $attempt->submitted_at?->format('M j, Y g:i A') ?? 'Submission time unavailable' }}@if($setName) · {{ $setName }}@endif</span>
            @if($answers->isNotEmpty())
                <ol class="record-answers">
                    @foreach($answers as $answer)
                        @php
                            $question = $answer->question;
                            $options = array_values((array) ($question?->options ?? []));
                            $isChoice = $question && ($question->type ?? 'Multiple Choice') !== 'Identification' && $options !== [];
                            $chosenIndex = $isChoice && is_string($answer->answer) && strlen($answer->answer) === 1 ? ord(strtoupper($answer->answer)) - 65 : null;
                            $chosenText = $chosenIndex !== null ? ($options[$chosenIndex] ?? null) : null;
                            $order = array_map('intval', $choiceOrders[(string) $answer->question_id] ?? array_keys($options));
                            $shownAt = $chosenIndex !== null ? array_search($chosenIndex, $order, true) : false;
                        @endphp
                        <li class="{{ $answer->is_correct ? 'correct' : 'incorrect' }}">
                            <strong>{{ $question->question ?? 'Question' }}</strong><br>
                            Answer:
                            @if($chosenText !== null)
                                {{ $chosenText }}@if($shownAt !== false) (shown as {{ chr(65 + $shownAt) }})@endif
                            @else
                                {{ $answer->answer ?: 'No answer' }}
                            @endif
                            @if(!$answer->is_correct && $question?->correct_answer)
                                · Correct answer: {{ $question->correct_answer }}
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    @empty
        <div class="record-meta">No quiz attempt recorded.</div>
    @endforelse
</div>
