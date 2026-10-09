<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student->name ?? 'Student' }} · Learning review | UPSKILL Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        :root{--record-ink:#17212b;--record-navy:#071550;--record-blue:#0b4ea2;--record-muted:#687385;--record-line:#e2e7ef;--record-bg:#f5f7fa;--record-yellow:#f4c430;--record-soft:#eaf2ff;--record-shadow:0 1px 3px rgba(9,37,95,.04);}
        *{box-sizing:border-box;}
        body{margin:0;background:var(--record-bg);color:var(--record-ink);font-family:Inter,'Segoe UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased;}
        .record-main{min-width:0;padding:28px 30px 56px!important;}
        .record-content{max-width:1180px;margin:0 auto;}
        .record-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:15px;color:var(--record-blue);font-size:.84rem;font-weight:600;text-decoration:none;}
        .record-back:hover{color:var(--record-navy);}
        .record-back svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
        .record-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px;padding:20px;border:1px solid var(--record-line);border-radius:12px;background:#fff;box-shadow:var(--record-shadow);}
        .record-identity{display:flex;align-items:center;gap:14px;min-width:0;}
        .record-avatar{display:grid;place-items:center;width:52px;height:52px;flex:0 0 52px;border-radius:50%;background:var(--record-soft);color:var(--record-blue);font-size:20px;font-weight:700;overflow:hidden;background-size:cover;background-position:center;}
        .record-header h1{margin:0;color:var(--record-navy);font-size:1.35rem;line-height:1.3;overflow-wrap:anywhere;}
        .record-header p{margin:5px 0 0;color:var(--record-muted);font-size:.88rem;line-height:1.5;}
        .record-status{display:inline-flex;flex:0 0 auto;padding:7px 11px;border-radius:999px;background:#f1f3f7;color:#5b6678;font-size:.74rem;font-weight:700;}
        .record-status.pending{background:#fff6d6;color:#7a5b00;}.record-status.approved{background:#e3f6ea;color:#166534;}.record-status.denied{background:#fff1f0;color:#c0352d;}
        .record-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px;}
        .record-stat{padding:16px;border:1px solid var(--record-line);border-radius:11px;background:#fff;box-shadow:var(--record-shadow);}
        .record-stat strong{display:block;color:var(--record-navy);font-size:1.15rem;}
        .record-stat span{display:block;margin-top:4px;color:var(--record-muted);font-size:.76rem;}
        .record-progress{height:8px;margin-top:11px;border-radius:999px;background:#edf0f5;overflow:hidden;}
        .record-progress span{display:block;height:100%;border-radius:inherit;background:var(--record-yellow);}
        .record-section{margin-bottom:16px;padding:19px 20px;border:1px solid var(--record-line);border-radius:12px;background:#fff;box-shadow:var(--record-shadow);}
        .record-section h2{margin:0 0 13px;color:var(--record-navy);font-size:1rem;}
        .record-empty{padding:16px;border:1px dashed var(--record-line);border-radius:8px;color:var(--record-muted);font-size:.84rem;}
        .record-module{margin-top:13px;border:1px solid var(--record-line);border-radius:9px;overflow:hidden;}
        .record-module:first-of-type{margin-top:0;}
        .record-module-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;background:#f8faff;color:var(--record-navy);font-size:.88rem;font-weight:700;}
        .record-module-content{padding:0 14px;}
        .record-lesson{padding:14px 0;border-top:1px solid var(--record-line);}
        .record-lesson:first-child{border-top:0;}
        .record-lesson-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
        .record-lesson-title{color:var(--record-ink);font-size:.86rem;font-weight:700;}
        .record-meta{margin-top:4px;color:var(--record-muted);font-size:.76rem;line-height:1.5;overflow-wrap:anywhere;}
        .record-pill{display:inline-flex;align-items:center;width:max-content;padding:4px 9px;border-radius:999px;background:#f1f3f7;color:#5b6678;font-size:.7rem;font-weight:700;}
        .record-pill.done,.record-pill.passed{background:#e3f6ea;color:#166534;}.record-pill.failed{background:#fff1f0;color:#c0352d;}.record-pill.waiting{background:#fff6d6;color:#7a5b00;}
        .record-work{margin-top:11px;padding:11px 12px;border-left:3px solid #d8deea;border-radius:0 8px 8px 0;background:#fafbfc;}
        .record-work h3{margin:0;color:var(--record-ink);font-size:.8rem;}
        .record-work p{margin:7px 0 0;color:#475467;font-size:.78rem;line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere;}
        .record-work a{display:inline-flex;margin-top:8px;color:var(--record-blue);font-size:.78rem;font-weight:700;text-decoration:none;}
        .record-work a:hover{text-decoration:underline;}
        .record-attempt{margin-top:9px;padding-top:9px;border-top:1px solid var(--record-line);}
        .record-answers{margin:8px 0 0;padding-left:18px;color:#475467;font-size:.76rem;line-height:1.5;}
        .record-answers li{margin-top:5px;}
        .record-answers .correct{color:#166534;}.record-answers .incorrect{color:#b42318;}
        .record-footer{display:flex;justify-content:flex-end;margin-top:16px;}
        .record-button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 14px;border:1px solid var(--record-blue);border-radius:8px;background:var(--record-blue);color:#fff;font:inherit;font-size:.8rem;font-weight:700;text-decoration:none;}
        .record-button:hover{background:#093f85;}
        @media(max-width:1000px){.record-stats{grid-template-columns:repeat(2,minmax(0,1fr));}}
        @media(max-width:700px){.record-main{padding:22px 14px 40px!important;}.record-header{flex-direction:column;}.record-stats{gap:9px;}.record-section{padding:16px 14px;}}
        @media(max-width:420px){.record-stats{grid-template-columns:1fr 1fr;}.record-identity{align-items:flex-start;}.record-avatar{width:42px;height:42px;flex-basis:42px;font-size:17px;}}
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => auth()->user()])
    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main record-main">
            <div class="record-content">
                <a class="record-back" href="{{ route('admin.enrollments') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                    Back to completion verification
                </a>

                <header class="record-header">
                    <div class="record-identity">
                        <div class="record-avatar" @if($student?->avatar_url) style="background-image:url('{{ $student->avatar_url }}')" @endif>@unless($student?->avatar_url){{ strtoupper(substr($student->name ?? 'S', 0, 1)) }}@endunless</div>
                        <div>
                            <h1>{{ $student->name ?? 'Student' }}</h1>
                            <p>{{ $course->title }}@if($student?->student_id) · {{ $student->student_id }}@elseif($student?->user_code) · {{ $student->user_code }}@endif</p>
                        </div>
                    </div>
                    <span class="record-status {{ $enrollment->review_status ?? '' }}">{{ $enrollment->institutional_label }}</span>
                </header>

                <section class="record-stats" aria-label="Student course progress summary">
                    <div class="record-stat"><strong>{{ (int) $enrollment->progress_percent }}%</strong><span>Course progress</span><div class="record-progress"><span style="width:{{ max(0, min(100, (int) $enrollment->progress_percent)) }}%"></span></div></div>
                    <div class="record-stat"><strong>{{ $completedLessons }} / {{ $totalLessons }}</strong><span>Lessons completed</span></div>
                    <div class="record-stat"><strong>{{ $completedActivities }} / {{ $totalActivities }}</strong><span>Activities submitted</span></div>
                    <div class="record-stat"><strong>{{ $passedQuizzes }} / {{ $totalQuizzes }}</strong><span>Quizzes passed</span></div>
                </section>

                <section class="record-section" aria-labelledby="record-learning-title">
                    <h2 id="record-learning-title">Learning activity</h2>
                    @forelse($modules as $module)
                        @php $moduleQuiz = $moduleQuizzes->get($module->id); @endphp
                        <article class="record-module">
                            <header class="record-module-head">
                                <span>{{ $module->title }}</span>
                                @if($moduleQuiz)<span>{{ $moduleQuiz->title }}</span>@endif
                            </header>
                            <div class="record-module-content">
                                @forelse($module->lessons as $lesson)
                                    @php $completion = $lessonCompletions->get($lesson->id); @endphp
                                    <section class="record-lesson">
                                        <div class="record-lesson-head">
                                            <div>
                                                <div class="record-lesson-title">{{ $lesson->title }}</div>
                                                <div class="record-meta">{{ $lesson->type }}@if($lesson->duration) · {{ $lesson->duration }} min @endif</div>
                                            </div>
                                            <span class="record-pill {{ $completion ? 'done' : '' }}">{{ $completion ? 'Completed' : 'Not completed' }}</span>
                                        </div>
                                        @if($completion)<div class="record-meta">Completed {{ \Illuminate\Support\Carbon::parse($completion->completed_at)->format('M j, Y g:i A') }}</div>@endif

                                        @foreach($lesson->activities as $activity)
                                            @php $submissions = $activitySubmissions->get($activity->id, collect()); @endphp
                                            <div class="record-work">
                                                <h3>{{ $activity->title }} · {{ Illuminate\Support\Str::headline($activity->activity_type) }}</h3>
                                                @forelse($submissions as $submission)
                                                    <div class="record-attempt">
                                                        <span class="record-pill {{ $submission->status === 'reviewed' ? 'done' : 'waiting' }}">{{ ucfirst($submission->status) }}</span>
                                                        <span class="record-meta">Attempt {{ $submission->attempt_number }} · {{ $submission->submitted_at?->format('M j, Y g:i A') ?? 'Submission time unavailable' }}
                                                            @if($submission->score !== null)
                                                                · {{ $submission->score }}{{ $activity->max_points ? ' / '.$activity->max_points : '' }} points
                                                            @endif
                                                        </span>
                                                        @if($submission->response_text)<p>{{ $submission->response_text }}</p>@endif
                                                        @if($submission->feedback)<p><strong>Feedback:</strong> {{ $submission->feedback }}</p>@endif
                                                        @if($submission->submission_path)
                                                            <a href="{{ route('admin.enrollments.submissions.download', $submission->id) }}">Download submitted file</a>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="record-meta">No submission recorded.</div>
                                                @endforelse
                                            </div>
                                        @endforeach

                                        @foreach($lessonQuizzes->get($lesson->id, collect()) as $quiz)
                                            @include('admin.enrollments.partials.quiz-attempts', ['quiz' => $quiz, 'attempts' => $quizAttempts->get($quiz->id, collect())])
                                        @endforeach
                                    </section>
                                @empty
                                    @if(!$moduleQuiz)<div class="record-empty">No lessons or assessments in this module.</div>@endif
                                @endforelse

                                @if($moduleQuiz)
                                    @include('admin.enrollments.partials.quiz-attempts', ['quiz' => $moduleQuiz, 'attempts' => $quizAttempts->get($moduleQuiz->id, collect())])
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="record-empty">This course has no modules yet.</div>
                    @endforelse
                </section>

                <footer class="record-footer">
                    <a class="record-button" href="{{ route('admin.enrollments') }}">Back to review queue</a>
                </footer>
            </div>
        </main>
    </div>
    @include('components.responsive')
</body>
</html>
