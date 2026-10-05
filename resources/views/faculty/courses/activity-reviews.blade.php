<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Activity Reviews · {{ $course->title }} | UpSkill</title>
    <style>
        :root{--review-navy:#13176b;--review-ink:#172554;--review-muted:#64748b;--review-line:#e2e8f0;--review-bg:#f7f9fc;--review-green:#166534;--review-red:#991b1b;--review-amber:#92400e;}
        *{box-sizing:border-box;}
        body{margin:0;background:var(--review-bg);color:var(--review-ink);font-family:Inter,"Segoe UI",sans-serif;}
        .layout{display:grid;grid-template-columns:264px minmax(0,1fr);min-height:calc(100vh - 74px);align-items:start;}
        .main{min-width:0;padding:30px 36px 60px;}
        .review-page{max-width:1180px;margin:0 auto;}
        .review-breadcrumb{margin-bottom:18px;font-size:12px;color:var(--review-muted);}
        .review-breadcrumb a{color:var(--review-navy);font-weight:700;text-decoration:none;}
        .review-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:22px;}
        .review-header h1{margin:0 0 6px;color:var(--review-navy);font-size:28px;font-weight:800;letter-spacing:-.03em;}
        .review-header p{margin:0;color:var(--review-muted);font-size:14px;line-height:1.5;}
        .back-manage{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cbd5e1;border-radius:999px;background:#fff;color:var(--review-navy);padding:10px 16px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;}
        .back-manage:hover{background:#eef2ff;}
        .review-alert{margin:0 0 18px;padding:12px 16px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:var(--review-green);font-size:13px;font-weight:700;}
        .review-alert.error{border-color:#fecaca;background:#fef2f2;color:var(--review-red);}
        .review-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px;}
        .review-stat{padding:16px 18px;border:1px solid var(--review-line);border-radius:14px;background:#fff;box-shadow:0 8px 20px #1725540a;}
        .review-stat span{display:block;color:var(--review-muted);font-size:12px;font-weight:700;}
        .review-stat strong{display:block;margin-top:5px;color:var(--review-navy);font-size:25px;font-weight:800;}
        .review-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
        .review-filter{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid #cbd5e1;border-radius:999px;background:#fff;color:#475569;font-size:12px;font-weight:700;text-decoration:none;}
        .review-filter[aria-current="page"]{border-color:var(--review-navy);background:var(--review-navy);color:#fff;}
        .review-filter-count{font-size:11px;opacity:.8;}
        .clear-activity-filter{margin-left:auto;color:var(--review-navy);font-size:12px;font-weight:700;text-decoration:underline;text-underline-offset:3px;}
        .submission-list{display:flex;flex-direction:column;gap:14px;}
        .review-submission{padding:18px;border:1px solid var(--review-line);border-radius:16px;background:#fff;box-shadow:0 8px 20px #1725540a;}
        .submission-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid #eef1f5;}
        .submission-course-path{margin-bottom:6px;color:var(--review-muted);font-size:11px;font-weight:600;}
        .submission-course-path span+span:before{content:"/";padding:0 6px;color:#94a3b8;}
        .submission-title{margin:0;color:var(--review-navy);font-size:17px;font-weight:800;}
        .submission-student{margin-top:6px;color:#334155;font-size:13px;font-weight:700;}
        .submission-meta{display:block;margin-top:3px;color:var(--review-muted);font-size:11px;font-weight:500;}
        .submission-status{display:inline-flex;align-items:center;white-space:nowrap;border-radius:999px;padding:7px 11px;background:#fef3c7;color:var(--review-amber);font-size:11px;font-weight:800;}
        .submission-status.is-passed{background:#dcfce7;color:var(--review-green);}
        .submission-status.is-needs-revision{background:#fee2e2;color:var(--review-red);}
        .assignment-instructions{margin-top:13px;padding:11px 13px;border-radius:10px;background:#f8faff;color:#475569;font-size:12px;line-height:1.55;}
        .assignment-instructions strong{display:block;margin-bottom:4px;color:var(--review-navy);font-size:11px;text-transform:uppercase;letter-spacing:.05em;}
        .submission-content{display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,.7fr);gap:16px;padding-top:14px;}
        .submission-response-block h3,.submission-result h3{margin:0 0 7px;color:#475569;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
        .submission-response{white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.6;color:#334155;font-size:13px;}
        .submission-attachment{display:inline-flex;margin-top:10px;color:var(--review-navy);font-size:12px;font-weight:800;text-decoration:underline;text-underline-offset:3px;}
        .submission-result{padding:12px;border-radius:11px;background:#f8faff;}
        .submission-result p{margin:5px 0 0;color:#334155;font-size:12px;line-height:1.5;}
        .review-form{display:grid;grid-template-columns:minmax(130px,180px) minmax(0,1fr);gap:12px;align-items:end;margin-top:14px;padding-top:14px;border-top:1px solid #e8ebf2;}
        .review-form label{display:flex;flex-direction:column;gap:6px;color:#475569;font-size:11px;font-weight:800;}
        .review-form input,.review-form textarea{width:100%;min-width:0;border:1px solid #cbd5e1;border-radius:9px;padding:10px 11px;background:#fff;color:var(--review-ink);font:500 13px Inter,"Segoe UI",sans-serif;}
        .review-form textarea{min-height:42px;resize:vertical;}
        .review-form .review-help{grid-column:1/-1;margin-top:-6px;color:var(--review-muted);font-size:11px;}
        .review-submit{grid-column:1/-1;justify-self:start;border:0;border-radius:999px;background:var(--review-navy);color:#fff;padding:10px 17px;font-size:12px;font-weight:800;cursor:pointer;}
        .review-submit:hover{background:#0c0f4d;}
        .review-empty{padding:36px 22px;border:1px dashed #cbd5e1;border-radius:16px;background:#fff;text-align:center;}
        .review-empty h2{margin:0 0 7px;color:var(--review-navy);font-size:17px;}
        .review-empty p{margin:0;color:var(--review-muted);font-size:13px;line-height:1.5;}
        .review-pagination{margin-top:18px;}
        @media(max-width:980px){.layout{grid-template-columns:1fr;}.main{padding:24px 20px 48px;}}
        @media(max-width:640px){.main{padding:18px 14px 40px;}.review-header h1{font-size:24px;}.review-stats{grid-template-columns:1fr;gap:9px;}.review-stat{padding:12px 14px;}.review-stat strong{font-size:21px;}.review-submission{padding:14px;}.submission-content{grid-template-columns:1fr;}.review-form{grid-template-columns:1fr;}.review-form .review-help,.review-submit{grid-column:1;}.review-submit{justify-self:stretch;}.clear-activity-filter{margin-left:0;}}
    </style>
</head>
<body>
    @include('components.authenticated-topbar')
    <div class="layout faculty-sidebar-layout">
        @include('components.faculty-sidebar')
        <main class="main">
            <div class="review-page">
                <div class="review-breadcrumb">
                    <a href="{{ route('faculty.courses') }}">My Courses</a>
                    <span> / </span>
                    <a href="{{ route('faculty.courses.manage', $course->id) }}">{{ $course->title }}</a>
                    <span> / Activity Reviews</span>
                </div>

                <header class="review-header">
                    <div>
                        <h1>Activity Reviews</h1>
                        <p>Review and grade assignment submissions for <strong>{{ $course->title }}</strong>.</p>
                    </div>
                    <a class="back-manage" href="{{ route('faculty.courses.manage', $course->id) }}">Back to Manage Course</a>
                </header>

                @if(session('success'))
                    <div class="review-alert" role="status">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="review-alert error" role="alert">{{ $errors->first() }}</div>
                @endif

                <section class="review-stats" aria-label="Submission review totals">
                    <div class="review-stat"><span>Awaiting review</span><strong>{{ $pendingCount }}</strong></div>
                    <div class="review-stat"><span>Reviewed</span><strong>{{ $reviewedCount }}</strong></div>
                    <div class="review-stat"><span>All submissions</span><strong>{{ $allCount }}</strong></div>
                </section>

                <nav class="review-filters" aria-label="Filter assignment submissions">
                    @foreach([
                        'pending' => ['label' => 'Needs review', 'count' => $pendingCount],
                        'reviewed' => ['label' => 'Reviewed', 'count' => $reviewedCount],
                        'all' => ['label' => 'All submissions', 'count' => $allCount],
                    ] as $filterKey => $filter)
                        <a class="review-filter" href="{{ route('faculty.activities.reviews', ['id' => $course->id, 'status' => $filterKey, 'activity_id' => $activityId]) }}" @if($status === $filterKey) aria-current="page" @endif>
                            {{ $filter['label'] }} <span class="review-filter-count">{{ $filter['count'] }}</span>
                        </a>
                    @endforeach
                    @if($activityId)
                        <a class="clear-activity-filter" href="{{ route('faculty.activities.reviews', ['id' => $course->id, 'status' => $status]) }}">Clear activity filter</a>
                    @endif
                </nav>

                @if($submissions->isNotEmpty())
                    <div class="submission-list">
                        @foreach($submissions as $submission)
                            @php
                                $activity = $submission->activity;
                                $lesson = $activity->lesson;
                                $module = $lesson->module;
                            @endphp
                            <article class="review-submission">
                                <header class="submission-top">
                                    <div>
                                        <div class="submission-course-path">
                                            <span>{{ $module->title ?? 'Module' }}</span><span>{{ $lesson->title }}</span>
                                        </div>
                                        <h2 class="submission-title">{{ $activity->title }}</h2>
                                        <div class="submission-student">{{ $submission->student->name ?? 'Learner' }}</div>
                                        <span class="submission-meta">
                                            {{ $submission->student->student_id ?? $submission->student->user_code ?? 'Student' }}
                                            · Attempt {{ $submission->attempt_number }}
                                            · Submitted {{ $submission->submitted_at?->format('M j, Y g:i A') ?? $submission->created_at?->format('M j, Y g:i A') }}
                                        </span>
                                    </div>
                                    <span class="submission-status is-{{ Illuminate\Support\Str::slug($submission->status) }}">{{ Illuminate\Support\Str::headline($submission->status) }}</span>
                                </header>

                                @if($activity->instructions)
                                    <div class="assignment-instructions"><strong>Assignment instructions</strong>{{ $activity->instructions }}</div>
                                @endif

                                <div class="submission-content">
                                    <section class="submission-response-block">
                                        <h3>Student response</h3>
                                        @if($submission->response_text)
                                            <div class="submission-response">{{ $submission->response_text }}</div>
                                        @else
                                            <div class="submission-response">The student submitted an attachment without written text.</div>
                                        @endif
                                        @if($submission->submission_path)
                                            <a class="submission-attachment" href="{{ route('faculty.lesson-activities.download', $submission->id) }}">Download submitted file</a>
                                        @endif
                                    </section>
                                    @if($submission->status !== 'submitted')
                                        <section class="submission-result">
                                            <h3>Review result</h3>
                                            @if($submission->score !== null)
                                                <p><strong>Score:</strong> {{ $submission->score }} / {{ $activity->max_points }} points</p>
                                            @endif
                                            @if($submission->feedback)
                                                <p><strong>Feedback:</strong> {{ $submission->feedback }}</p>
                                            @endif
                                            <p><strong>Reviewed:</strong> {{ $submission->reviewed_at?->format('M j, Y g:i A') ?? '—' }}</p>
                                            <p><strong>Reviewed by:</strong> {{ $submission->reviewer->name ?? 'Faculty' }}</p>
                                        </section>
                                    @endif
                                </div>

                                @if($submission->status === 'submitted')
                                    <form class="review-form" method="POST" action="{{ route('faculty.lesson-activity.review', [$course->id, $activity->id, $submission->id]) }}">
                                        @csrf
                                        <label>Score (maximum {{ $activity->max_points ?? 10000 }} points)
                                            <input type="number" name="score" min="0" max="{{ $activity->max_points ?? 10000 }}" step="1" value="{{ old('score') }}" placeholder="Enter score" required>
                                        </label>
                                        <label>Feedback for the student
                                            <textarea name="feedback" maxlength="5000" placeholder="Explain the result or what the student should revise">{{ old('feedback') }}</textarea>
                                        </label>
                                        <div class="review-help">Passing score: {{ $activity->passing_percent ?? 70 }}%. A lower score returns the assignment as needing revision.</div>
                                        <button class="review-submit" type="submit">Save grade and feedback</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div class="review-pagination">{{ $submissions->links() }}</div>
                @else
                    <div class="review-empty">
                        <h2>@if($status === 'pending')Nothing to review right now.@elseif($status === 'reviewed')No reviewed submissions yet.@else No submissions yet.@endif</h2>
                        <p>Assignment submissions for this course will appear here, grouped with their lesson and student details.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>
    @include('components.responsive')
</body>
</html>
