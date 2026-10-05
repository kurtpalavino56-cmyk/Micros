<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $course->title }} | Learner Progress &amp; Verification | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{--navy:#13176b;--ink:#0e1a3a;--muted:#64748b;--line:#e5e7eb;--gold:#dba617;--green:#15803d;--red:#b91c1c;--shadow:0 8px 22px rgba(19,23,107,.06);}
    *{box-sizing:border-box;}
    body{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:var(--ink);margin:0;background:#f8faff;}
    a{text-decoration:none;color:inherit;}button{font:inherit;cursor:pointer;}
    .main{padding:28px 32px 48px;min-width:0;width:100%;max-width:1440px;margin-inline:auto;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
    main .page-head h2{font-family:'Plus Jakarta Sans',Inter,ui-sans-serif,system-ui,sans-serif;font-size:27px;font-weight:800;letter-spacing:-.04em;color:var(--ink);margin:0 0 6px;}
    main .page-head p{font-size:14px;font-weight:400;color:var(--muted);margin:0;}
    .course-summary{display:flex;flex-wrap:wrap;gap:10px 18px;margin-top:11px;color:#475569;font-size:13px;}
    .course-summary strong{color:var(--ink);font-weight:700;}
    .review-summary{display:flex;align-items:center;gap:8px;padding:12px 15px;margin:0 0 16px;border:1px solid #fed7aa;border-radius:10px;background:#fff7ed;color:#9a3412;font-size:13px;font-weight:600;}
    .review-summary strong{font-size:15px;}
    .tabs{display:flex;gap:8px;border-bottom:1px solid var(--line);margin-bottom:14px;}
    .tab{padding:10px 13px;color:#64748b;border-bottom:2px solid transparent;font-size:13px;font-weight:700;}
    .tab.active{color:var(--navy);border-bottom-color:var(--navy);}
    .student-list{display:grid;gap:10px;}
    .student-row{display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px 16px;box-shadow:var(--shadow);}
    .avatar{width:42px;height:42px;flex:0 0 42px;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#e8edfb;color:var(--navy);font-weight:800;background-size:cover;background-position:center;}
    .student-copy{min-width:0;flex:1;}
    .student-copy h3{margin:0 0 4px;font-size:14px;font-weight:700;color:var(--ink);}
    .student-meta{display:flex;flex-wrap:wrap;gap:5px 13px;color:var(--muted);font-size:12px;}
    .status{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 9px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;white-space:nowrap;}
    .status.needs-review{background:#fff7ed;color:#c2410c;}.status.completed{background:#dcfce7;color:#166534;}.status.rejected{background:#fee2e2;color:#b91c1c;}
    .progress{width:96px;color:#475569;font-size:12px;font-weight:700;text-align:right;white-space:nowrap;}
    .review-actions{display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end;}
    .action{padding:7px 10px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:var(--navy);font-size:12px;font-weight:700;}
    .action:hover{background:#f8fafc;}.action.verify{background:var(--navy);border-color:var(--navy);color:#fff;}.action.verify:hover{background:#20278a;}.action.reject{color:var(--red);border-color:#fecaca;}
    .empty-state{padding:32px;background:#fff;border:1px dashed var(--line);border-radius:10px;text-align:center;color:var(--muted);font-size:14px;}
    .pagination{margin-top:16px;}
    .flash{padding:11px 14px;margin-bottom:14px;border-radius:8px;background:#ecfdf3;color:#166534;font-size:13px;font-weight:600;}
    @media(max-width:760px){.main{padding:22px 16px 36px;}.student-row{align-items:flex-start;flex-wrap:wrap;}.student-copy{flex-basis:calc(100% - 58px);}.progress{width:auto;text-align:left;margin-left:55px;}.status{margin-left:55px;}.review-actions{margin-left:55px;justify-content:flex-start;}}
</style>
</head>
<body>
@include('components.authenticated-topbar')
<div class="layout faculty-sidebar-layout">
    @include('components.faculty-sidebar')
    <main class="main">
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'Learner Progress & Verification', 'url' => route('faculty.students')],
            ['label' => $course->title],
        ]])
        <div class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">{{ $course->title }}</h2>
                <p class="faculty-page-subtitle">Review course progress and verify learners who have completed all requirements.</p>
                <div class="course-summary">
                    <span><strong>{{ $totalStudents }}</strong> {{ Illuminate\Support\Str::plural('student', $totalStudents) }} enrolled</span>
                    <span><strong>{{ $needsReviewCount }}</strong> need faculty review</span>
                    @unless($course->requires_faculty_verification)<span>Faculty verification is not required for this course</span>@endunless
                </div>
            </div>
            <a class="action" href="{{ route('faculty.courses.manage', $course->id) }}">Manage Course</a>
        </div>

        @if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif

        @if($filter === 'needs_review' && $needsReviewCount > 0)
            <div class="review-summary"><strong>{{ $needsReviewCount }}</strong> {{ Illuminate\Support\Str::plural('learner', $needsReviewCount) }} completed the course requirements and {{ $needsReviewCount === 1 ? 'is' : 'are' }} waiting for your verification.</div>
        @endif

        <nav class="tabs" aria-label="Enrollment filters">
            <a class="tab {{ $filter === 'needs_review' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'needs_review']) }}">Needs review <span>({{ $needsReviewCount }})</span></a>
            <a class="tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('faculty.students.course', ['id' => $course->id, 'filter' => 'all']) }}">All students <span>({{ $totalStudents }})</span></a>
        </nav>

        <div class="student-list">
        @forelse($enrollments as $enrollment)
            @php
                $user = $enrollment->user;
                $needsReview = $course->requires_faculty_verification
                    && $enrollment->faculty_verification_status === 'pending'
                    && $enrollment->completion_status === \App\Services\MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION;
                $institutionalStatus = \App\Support\CompletionStatusPresenter::institutionalLabel(
                    $enrollment->completion_status,
                    $enrollment->faculty_verification_status,
                    $enrollment->academic_unit_confirmation_status,
                );
            @endphp
            <article class="student-row">
                <div class="avatar" @if($user?->avatar_url) style="background-image:url('{{ $user->avatar_url }}')" @endif>@unless($user?->avatar_url){{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}@endunless</div>
                <div class="student-copy">
                    <h3>{{ $user->name ?? 'Student' }}</h3>
                    <div class="student-meta">
                        <span>{{ $user->student_id ?? $user->user_code ?? 'Student ID not provided' }}</span>
                        <span>{{ $institutionalStatus }}</span>
                    </div>
                </div>
                <span class="progress">{{ (int) $enrollment->progress_percent }}% progress</span>
                <span class="status {{ $needsReview ? 'needs-review' : ($institutionalStatus === 'Completed' ? 'completed' : ($institutionalStatus === 'Rejected' ? 'rejected' : '')) }}">{{ $needsReview ? 'Needs review' : $institutionalStatus }}</span>
                @if($needsReview)
                    <div class="review-actions">
                        <form method="POST" action="{{ route('faculty.enrollments.verify', $enrollment->id) }}" onsubmit="return confirm('Verify that this learner completed the course requirements?');">
                            @csrf
                            <button class="action verify" type="submit" name="decision" value="verified">Verify completion</button>
                        </form>
                        <form method="POST" action="{{ route('faculty.enrollments.verify', $enrollment->id) }}" onsubmit="return confirm('Reject this course completion for this learner?');">
                            @csrf
                            <button class="action reject" type="submit" name="decision" value="rejected">Reject</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="empty-state">
                @if($filter === 'needs_review')
                    No learners are waiting for faculty verification in this course.
                @else
                    No students are enrolled in this course yet.
                @endif
            </div>
        @endforelse
        </div>
        @if($enrollments->hasPages())<div class="pagination">{{ $enrollments->links() }}</div>@endif
    </main>
</div>
@include('components.responsive')
</body>
</html>
