<section id="course-evaluation-card" class="course-evaluation-card" style="margin:12px 10px;padding:16px;border:1px solid #dce3ef;border-radius:12px;background:#fff;" aria-labelledby="course-evaluation-title">
    <style>
        .course-evaluation-table-wrap{width:100%;overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px;}
        .course-evaluation-table{width:100%;min-width:650px;border-collapse:collapse;color:#334155;font-size:13px;}
        .course-evaluation-table th,.course-evaluation-table td{padding:11px 10px;border-bottom:1px solid #e5e7eb;}
        .course-evaluation-table thead th{background:#f7f8fa;color:#172554;text-align:center;font-weight:700;line-height:1.3;}
        .course-evaluation-table thead th:first-child{width:48%;text-align:left;}
        .course-evaluation-table tbody th[scope="row"]{text-align:left;font-weight:500;}
        .course-evaluation-table tbody td{text-align:center;width:10.4%;}
        .course-evaluation-table .course-evaluation-group th{background:#f7f8fa;color:#172554;text-align:left;font-weight:700;}
        .course-evaluation-table tbody tr:not(.course-evaluation-group):nth-of-type(even){background:#f7f8fa;}
        .course-evaluation-table input{accent-color:#123b7a;width:15px;height:15px;cursor:pointer;}
        .course-evaluation-table tr:last-child th,.course-evaluation-table tr:last-child td{border-bottom:0;}
        .course-evaluation-overall{min-width:560px;margin-bottom:16px;}
    </style>
    <h3 id="course-evaluation-title" style="margin:0 0 6px;font-size:16px;">Course Evaluation</h3>
    @if($courseReview)
        <p style="margin:0;color:#53627a;">Thank you for sharing your feedback. Your evaluation has been recorded.</p>
    @else
        @php($evaluationScaleLabels = [1 => 'Strongly Disagree', 2 => 'Disagree', 3 => 'Neutral', 4 => 'Agree', 5 => 'Strongly Agree'])
        @php($overallScaleLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'])
        <p id="course-evaluation-locked" @if($courseEvaluationEligible) hidden @endif style="margin:0;color:#667085;">
            {{ $modules->contains('is_final', true) ? 'Complete and pass the Final Exam to unlock the evaluation.' : 'Complete all course requirements to unlock the evaluation.' }}
        </p>
        <form id="course-evaluation-form" method="POST" action="{{ route('courses.evaluation.store', $course->id) }}" @unless($courseEvaluationEligible) hidden @endunless>
            @csrf
            <p id="course-evaluation-submit-error" class="course-evaluation-error" role="alert" hidden></p>
            <div class="course-evaluation-table-wrap">
                <table class="course-evaluation-table course-evaluation-overall">
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
                                <small id="course-evaluation-error-rating" data-evaluation-error="rating" role="alert" style="display:none;color:#b42318;"></small>
                                @error('rating')<small role="alert" style="display:block;color:#b42318;">{{ $message }}</small>@enderror
                            </th>
                            @for($score = 1; $score <= 5; $score++)
                                <td><input type="radio" name="rating" value="{{ $score }}" aria-label="{{ $score }} — {{ $overallScaleLabels[$score] }}" required @checked((int) old('rating') === $score)></td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="course-evaluation-table-wrap">
                <table class="course-evaluation-table">
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
                                        <small id="course-evaluation-error-answers-{{ $group }}-{{ $key }}" data-evaluation-error="answers.{{ $group }}.{{ $key }}" role="alert" style="display:none;color:#b42318;"></small>
                                        @error("answers.$group.$key")<small role="alert" style="display:block;color:#b42318;">{{ $message }}</small>@enderror
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
            <label for="course-evaluation-comment" style="display:block;font-weight:700;margin:12px 0 6px;">Comment (optional)</label>
            <textarea id="course-evaluation-comment" name="comment" rows="3" maxlength="3000" style="width:100%;resize:vertical;">{{ old('comment') }}</textarea>
            <button type="submit" class="btn-quiz-sm unlocked" id="btn-finish-course-review" style="margin-top:12px;" disabled>Finish</button>
        </form>
    @endif
</section>
