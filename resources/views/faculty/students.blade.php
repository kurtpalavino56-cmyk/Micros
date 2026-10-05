<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learner Progress &amp; Verification | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{--navy:#13176b;--ink:#0e1a3a;--muted:#64748b;--line:#e5e7eb;--gold:#dba617;--green:#15803d;--shadow:0 8px 22px rgba(19,23,107,.06);}
    *{box-sizing:border-box;}
    body{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:var(--ink);margin:0;background:#f8faff;}
    a{text-decoration:none;color:inherit;}button{font:inherit;cursor:pointer;}
    .main{padding:28px 32px 48px;min-width:0;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px;}
    main .page-head h2{font-family:'Plus Jakarta Sans',Inter,ui-sans-serif,system-ui,sans-serif;font-size:27px;font-weight:800;letter-spacing:-.04em;color:var(--ink);margin:0 0 6px;}
    main .page-head p{font-size:14px;font-weight:400;color:var(--muted);margin:0;}
    .btn-outline{display:inline-flex;align-items:center;justify-content:center;background:#fff;border:1px solid #cbd5e1;color:var(--navy);font-weight:700;padding:9px 15px;border-radius:8px;font-size:13px;white-space:nowrap;}
    .btn-outline:hover{background:#f8fafc;border-color:#94a3b8;}
    .course-list{display:grid;gap:12px;max-width:1180px;}
    .course-row{display:flex;align-items:center;gap:18px;padding:17px 20px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow);}
    .course-copy{flex:1;min-width:0;}
    .course-copy h3{font-family:'Plus Jakarta Sans',Inter,sans-serif;font-size:16px;line-height:1.35;font-weight:700;color:var(--ink);margin:0 0 7px;}
    .course-meta{display:flex;align-items:center;gap:9px 18px;flex-wrap:wrap;color:var(--muted);font-size:13px;}
    .course-meta strong{color:#334155;font-weight:600;}
    .course-status{font-size:11px;font-weight:700;line-height:1;border-radius:999px;padding:6px 9px;background:#f1f5f9;color:#475569;}
    .course-status.published{background:#dcfce7;color:#166534;}.course-status.pending{background:#fff7ed;color:#c2410c;}.course-status.denied{background:#fee2e2;color:#b91c1c;}
    .review-count{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:700;white-space:nowrap;}
    .review-count.zero{background:#f1f5f9;color:#64748b;}
    .manage-link{display:inline-flex;justify-content:center;align-items:center;min-width:92px;padding:9px 14px;border-radius:8px;background:var(--navy);border:1px solid var(--navy);color:#fff;font-size:13px;font-weight:700;white-space:nowrap;}
    .manage-link:hover{background:#20278a;border-color:#20278a;}
    .empty-state{background:#fff;border:1px dashed var(--line);border-radius:12px;padding:38px;text-align:center;color:var(--muted);font-size:14px;}
    @media(max-width:680px){.main{padding:22px 16px 36px;}.course-row{align-items:flex-start;gap:12px;flex-wrap:wrap;padding:15px;}.course-copy{flex-basis:calc(100% - 4px);}.course-row .manage-link{margin-left:auto;}.course-meta{gap:8px 12px;}}
</style>
</head>
<body>
@include('components.authenticated-topbar')
<div class="layout faculty-sidebar-layout">
    @include('components.faculty-sidebar')
    <main class="main">
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'Learner Progress & Verification'],
        ]])
        <div class="page-head faculty-page-heading">
            <div><h2 class="faculty-page-title">Learner Progress &amp; Verification</h2><p class="faculty-page-subtitle">Choose a course to review learner progress and verify completed requirements.</p></div>
            <a href="{{ route('faculty.courses') }}" class="btn-outline">My Courses</a>
        </div>

        <div class="course-list">
        @forelse ($courses as $course)
            <article class="course-row">
                <div class="course-copy">
                    <h3>{{ $course->title }}</h3>
                    <div class="course-meta">
                        <span><strong>{{ $course->students_count }}</strong> {{ Illuminate\Support\Str::plural('student', $course->students_count) }} enrolled</span>
                        <span class="course-status {{ Illuminate\Support\Str::slug($course->status) }}">{{ $course->status }}</span>
                        @if($course->requires_faculty_verification)
                            <span class="review-count {{ $course->faculty_reviews_count === 0 ? 'zero' : '' }}">
                                {{ $course->faculty_reviews_count > 0 ? 'Needs review · '.$course->faculty_reviews_count : 'No pending review' }}
                            </span>
                        @else
                            <span class="review-count zero">Faculty verification not required</span>
                        @endif
                    </div>
                </div>
                <a class="manage-link" href="{{ route('faculty.students.course', $course->id) }}">Manage</a>
            </article>
        @empty
            <div class="empty-state">You don’t have any courses yet.</div>
        @endforelse
        </div>
    </main>
</div>
@include('components.responsive')
</body>
</html>
