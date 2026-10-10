<section id="course-evaluation-card" class="course-evaluation-card ui-card-surface" aria-labelledby="course-evaluation-title">
    <h3 id="course-evaluation-title" class="course-evaluation-title ui-section-title">Course Evaluation</h3>
    @if($courseReview)
        <p class="course-evaluation-copy">Thank you for sharing your feedback. Your evaluation has been recorded.</p>
    @else
        @php($evaluationScaleLabels = [1 => 'Strongly Disagree', 2 => 'Disagree', 3 => 'Neutral', 4 => 'Agree', 5 => 'Strongly Agree'])
        @php($overallScaleLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'])
        <p id="course-evaluation-locked" class="course-evaluation-copy" @if($courseEvaluationEligible) hidden @endif>
            {{ $modules->contains('is_final', true) ? 'Complete and pass the Final Exam to unlock the evaluation.' : 'Complete all course requirements to unlock the evaluation.' }}
        </p>
        <form id="course-evaluation-form" method="POST" action="{{ route('courses.evaluation.store', $course->id) }}" @unless($courseEvaluationEligible) hidden @endunless>
            @csrf
            <p id="course-evaluation-submit-error" class="course-evaluation-error ui-alert ui-alert--danger" role="alert" hidden></p>
            <div class="course-evaluation-table-wrap ui-table-wrap">
                <table class="course-evaluation-table course-evaluation-overall ui-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ $courseEvaluationQuestions['overall'] }}</th>
                            @foreach($overallScaleLabels as $score => $label)
                                <th scope="col">{{ $score }} – {{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">
                                Your rating
                                <small id="course-evaluation-error-rating" class="course-evaluation-field-error" data-evaluation-error="rating" role="alert" style="display:none;"></small>
                                @error('rating')<small class="course-evaluation-field-error is-visible" role="alert" style="display:block;">{{ $message }}</small>@enderror
                            </th>
                            @for($score = 1; $score <= 5; $score++)
                                <td><input type="radio" name="rating" value="{{ $score }}" aria-label="{{ $score }} — {{ $overallScaleLabels[$score] }}" required @checked((int) old('rating') === $score)></td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="course-evaluation-table-wrap ui-table-wrap">
                <table class="course-evaluation-table ui-table">
                    <thead>
                        <tr>
                            <th scope="col">Please rate each statement</th>
                            @foreach($evaluationScaleLabels as $score => $label)
                                <th scope="col">{{ $score }} – {{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['course' => 'Course experience', 'platform' => 'Platform experience'] as $group => $heading)
                            <tr class="course-evaluation-group"><th scope="colgroup" colspan="6">{{ $heading }}</th></tr>
                            @foreach($courseEvaluationQuestions[$group] as $key => $question)
                                <tr>
                                    <th scope="row">
                                        {{ $question }}
                                        <small id="course-evaluation-error-answers-{{ $group }}-{{ $key }}" class="course-evaluation-field-error" data-evaluation-error="answers.{{ $group }}.{{ $key }}" role="alert" style="display:none;"></small>
                                        @error("answers.$group.$key")<small class="course-evaluation-field-error is-visible" role="alert" style="display:block;">{{ $message }}</small>@enderror
                                    </th>
                                    @for($score = 1; $score <= 5; $score++)
                                        <td>
                                            <input type="radio" name="answers[{{ $group }}][{{ $key }}]" value="{{ $score }}" aria-label="{{ $question }} — {{ $score }}: {{ $evaluationScaleLabels[$score] }}" required @checked((int) old("answers.$group.$key") === $score)>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <label for="course-evaluation-comment" class="course-evaluation-comment-label ui-label">Comment (optional)</label>
            <textarea id="course-evaluation-comment" class="ui-textarea" name="comment" rows="3" maxlength="3000">{{ old('comment') }}</textarea>
            <button type="submit" class="btn-quiz-sm unlocked ui-button ui-button--primary" id="btn-finish-course-review" disabled>Finish</button>
        </form>
    @endif
</section>
