<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completion verification · {{ $course?->title ?? 'All courses' }} | UPSKILL Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        :root{--enrollment-ink:#17212b;--enrollment-muted:#687385;--enrollment-line:#e2e7ef;--enrollment-bg:#f5f7fa;--enrollment-blue:#0b4ea2;--enrollment-navy:#071550;--enrollment-yellow:#f4c430;--enrollment-soft:#eaf2ff;--enrollment-shadow:0 1px 3px rgba(9,37,95,.04);}
        *{box-sizing:border-box;}
        body{margin:0;background:var(--enrollment-bg);color:var(--enrollment-ink);font-family:Inter,'Segoe UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased;}
        .enrollment-main{min-width:0;padding:28px 30px 56px!important;}
        .enrollment-content{max-width:1180px;margin:0 auto;}
        .enrollment-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px;}
        .enrollment-heading h1{margin:0;color:var(--enrollment-navy);font-size:1.6rem;line-height:1.25;overflow-wrap:anywhere;}
        .enrollment-heading p{margin:6px 0 0;color:var(--enrollment-muted);font-size:.9rem;}
        .enrollment-counts{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px;}
        .enrollment-count{display:inline-flex;align-items:center;padding:7px 12px;border:1px solid var(--enrollment-line);border-radius:999px;background:#fff;color:var(--enrollment-muted);font-size:.78rem;font-weight:700;white-space:nowrap;}
        .enrollment-count.pending{border-color:#f2d36b;background:#fff6d6;color:#7a5b00;}
        .enrollment-tabs{display:flex;flex-wrap:wrap;gap:22px;margin:18px 0 20px;border-bottom:1px solid var(--enrollment-line);}
        .enrollment-tab{display:inline-flex;align-items:center;gap:4px;padding:10px 0;border:0;border-bottom:2px solid transparent;background:transparent;color:var(--enrollment-muted);font:inherit;font-size:.84rem;text-decoration:none;cursor:pointer;}
        .enrollment-tab:hover,.enrollment-tab.active{color:var(--enrollment-blue);}
        .enrollment-tab.active{border-bottom-color:var(--enrollment-yellow);font-weight:700;}
        .enrollment-tab-count{font-size:.78rem;color:var(--enrollment-muted);}
        .enrollment-alert{margin-bottom:14px;padding:12px 15px;border:1px solid #f3c8c4;border-radius:10px;background:#fff1f0;color:#c0352d;font-size:.86rem;}
        .enrollment-card{overflow:hidden;border:1px solid var(--enrollment-line);border-radius:12px;background:#fff;box-shadow:var(--enrollment-shadow);}
        .enrollment-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:17px 20px;border-bottom:1px solid var(--enrollment-line);}
        .enrollment-card-head h2{margin:0;color:var(--enrollment-navy);font-size:.98rem;}
        .enrollment-course-filter{display:flex;align-items:center;gap:8px;}
        .enrollment-course-filter label{color:var(--enrollment-muted);font-size:.76rem;font-weight:700;}
        .enrollment-course-filter select{min-width:210px;min-height:36px;padding:7px 30px 7px 10px;border:1px solid var(--enrollment-line);border-radius:7px;background:#fff;color:var(--enrollment-ink);font:inherit;font-size:.78rem;}
        .enrollment-course-filter button{min-height:36px;padding:7px 12px;border:1px solid var(--enrollment-line);border-radius:7px;background:#fff;color:var(--enrollment-blue);font:inherit;font-size:.76rem;font-weight:700;cursor:pointer;}
        .enrollment-course-filter button:hover{background:#f5f7fa;}
        .enrollment-table-wrap{width:100%;overflow-x:auto;}
        .enrollment-table{width:100%;min-width:920px;border-collapse:collapse;}
        .enrollment-table.global{min-width:1080px;}
        .enrollment-table th,.enrollment-table td{padding:13px 15px;border-bottom:1px solid var(--enrollment-line);text-align:left;vertical-align:middle;}
        .enrollment-table th{background:#f8faff;color:var(--enrollment-muted);font-size:.68rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap;}
        .enrollment-table td{font-size:.8rem;}
        .enrollment-table tbody tr:last-child td{border-bottom:0;}
        .enrollment-student{color:var(--enrollment-navy);font-size:.86rem;font-weight:700;overflow-wrap:anywhere;}
        .enrollment-meta{margin-top:4px;color:var(--enrollment-muted);font-size:.75rem;line-height:1.45;}
        .enrollment-status{display:inline-flex;align-items:center;width:max-content;max-width:100%;padding:4px 10px;border-radius:999px;background:#f1f3f7;color:#5b6678;font-size:.72rem;font-weight:700;line-height:1.35;}
        .enrollment-status.pending{background:#fff6d6;color:#7a5b00;}
        .enrollment-status.confirmed{background:#e3f6ea;color:#166534;}
        .enrollment-status.rejected{background:#fff1f0;color:#c0352d;}
        .enrollment-actions{display:flex;align-items:center;gap:7px;white-space:nowrap;}
        .enrollment-actions form{display:flex;gap:7px;margin:0;}
        .enrollment-button.review{border-color:#bcd0ea;color:var(--enrollment-blue);text-decoration:none;}
        .enrollment-button{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:6px 11px;border:1px solid var(--enrollment-line);border-radius:7px;background:#fff;color:var(--enrollment-ink);font:inherit;font-size:.75rem;font-weight:700;cursor:pointer;}
        .enrollment-button.confirm{border-color:var(--enrollment-blue);background:var(--enrollment-blue);color:#fff;}
        .enrollment-button.confirm:hover{background:#093f85;}
        .enrollment-button:hover{background:#f5f7fa;}
        .enrollment-empty{padding:36px 20px;color:var(--enrollment-muted);font-size:.9rem;text-align:center;}
        .enrollment-pagination{display:flex;align-items:center;justify-content:center;gap:7px;padding:13px 18px;border-top:1px solid var(--enrollment-line);}
        .enrollment-page-link{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 11px;border:1px solid var(--enrollment-line);border-radius:5px;background:#fff;color:var(--enrollment-ink);font:inherit;font-size:.78rem;text-decoration:none;white-space:nowrap;}
        .enrollment-page-link:hover{border-color:#c8ced8;background:#f8f9fb;}
        .enrollment-page-link.active{border-color:#111;background:#111;color:#fff;}
        .enrollment-page-link.disabled{color:#9aa1ad;background:#fff;}
        .enrollment-page-ellipsis{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:36px;color:var(--enrollment-ink);font-size:.8rem;}
        @media(max-width:700px){.enrollment-main{padding:22px 14px 40px!important;}.enrollment-heading{flex-direction:column;}.enrollment-heading h1{font-size:1.35rem;}.enrollment-counts{justify-content:flex-start;}.enrollment-card-head{align-items:flex-start;flex-direction:column;}.enrollment-course-filter{width:100%;flex-wrap:wrap;}.enrollment-course-filter label{width:100%;}.enrollment-course-filter select{flex:1;min-width:0;}.enrollment-pagination{gap:4px;padding:12px 8px;flex-wrap:wrap;}.enrollment-page-link{min-width:32px;height:34px;padding:0 8px;font-size:.74rem;}}
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => auth()->user()])
    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main enrollment-main">
            <div class="enrollment-content">
                <x-flash-toast :message="session('success')" />
                @if($errors->any())
                    <div class="enrollment-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <header class="enrollment-heading">
                    <div>
                        <h1>Completion Verification</h1>
                        <p>{{ $course?->title ?? 'Review student enrollment status across all courses.' }}</p>
                    </div>
                    <div class="enrollment-counts">
                        <span class="enrollment-count">{{ number_format($enrollments->total()) }} {{ Illuminate\Support\Str::plural('enrollment', $enrollments->total()) }}</span>
                        <span class="enrollment-count pending">{{ number_format($pendingConfirmations) }} awaiting confirmation</span>
                    </div>
                </header>

                <nav class="enrollment-tabs" aria-label="Enrollment status filters">
                    @foreach(['all' => 'All', 'pending' => 'Pending review', 'approved' => 'Approved', 'denied' => 'Denied'] as $key => $label)
                        @php
                            $tabRoute = $course
                                ? route('admin.courses.enrollments', ['id' => $course->id, 'filter' => $key])
                                : route('admin.enrollments', array_filter(['filter' => $key, 'course_id' => $selectedCourseId], fn ($value) => $value !== null));
                        @endphp
                        <a class="enrollment-tab {{ $filter === $key ? 'active' : '' }}" href="{{ $tabRoute }}" @if($filter === $key) aria-current="page" @endif>
                            {{ $label }} <span class="enrollment-tab-count">{{ $statusCounts[$key] }}</span>
                        </a>
                    @endforeach
                </nav>

                <section class="enrollment-card" aria-label="Course enrollments">
                    <div class="enrollment-card-head">
                        <h2>Student completion and verification status</h2>
                        @unless($course)
                            <form class="enrollment-course-filter" method="GET" action="{{ route('admin.enrollments') }}">
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <label for="enrollment-course-filter">Filter by course</label>
                                <select id="enrollment-course-filter" name="course_id">
                                    <option value="">All courses</option>
                                    @foreach($courses as $filterCourse)
                                        <option value="{{ $filterCourse->id }}" @selected((string) $selectedCourseId === (string) $filterCourse->id)>{{ $filterCourse->title }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">Apply</button>
                            </form>
                        @endunless
                    </div>
                    <div class="enrollment-table-wrap">
                        <table class="enrollment-table {{ $course ? '' : 'global' }}">
                            <thead>
                                <tr>
                                    <th scope="col">Student</th>
                                    @unless($course)<th scope="col">Course</th>@endunless
                                    <th scope="col">Completion</th>
                                    <th scope="col">Faculty verification</th>
                                    <th scope="col">Academic unit</th>
                                    <th scope="col">Completed</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollments as $enrollment)
                                    @php
                                        $unitStatus = $enrollment->academic_unit_confirmation_status;
                                        $unitClass = $enrollment->academic_confirmation_actionable ? 'pending'
                                            : ($unitStatus === 'confirmed' ? 'confirmed' : ($unitStatus === 'rejected' ? 'rejected' : ''));
                                    @endphp
                                    <tr>
                                        <td><div class="enrollment-student">{{ $enrollment->student_name }}</div></td>
                                        @unless($course)<td>{{ $enrollment->course_title }}</td>@endunless
                                        <td>{{ ucfirst(str_replace('_', ' ', $enrollment->completion_status)) }}</td>
                                        <td>
                                            <span class="enrollment-status">{{ ucfirst(str_replace('_', ' ', $enrollment->faculty_verification_status)) }}</span>
                                            @if($enrollment->faculty_verified_by)<div class="enrollment-meta">By {{ $enrollment->faculty_verified_by }}</div>@endif
                                        </td>
                                        <td>
                                            <span class="enrollment-status {{ $unitClass }}">{{ ucfirst(str_replace('_', ' ', $unitStatus)) }}</span>
                                            @if($enrollment->academic_unit_confirmed_by)<div class="enrollment-meta">By {{ $enrollment->academic_unit_confirmed_by }}</div>@endif
                                        </td>
                                        <td>{{ $enrollment->completed_at?->format('M j, Y') ?? '—' }}</td>
                                        <td>
                                            <div class="enrollment-actions">
                                                @if($enrollment->academic_confirmation_actionable)
                                                    <a class="enrollment-button review" href="{{ route('admin.enrollments.show', $enrollment->id) }}">Review</a>
                                                    <form method="POST" action="{{ route('admin.enrollments.academic-confirm', ['id' => $enrollment->course_id, 'enrollment' => $enrollment->id]) }}">
                                                        @csrf
                                                        <button type="submit" name="decision" value="confirmed" class="enrollment-button confirm">Confirm</button>
                                                        <button type="submit" name="decision" value="rejected" class="enrollment-button">Reject</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="enrollment-empty" colspan="{{ $course ? 6 : 7 }}">@if($statusCounts['all'] === 0){{ $course ? 'No enrollments for this course yet.' : 'No course enrollments yet.' }}@else No enrollments match this status.@endif</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($enrollments->hasPages())
                        <nav class="enrollment-pagination" aria-label="Enrollment pagination">
                            @php
                                $currentPage = $enrollments->currentPage();
                                $lastPage = $enrollments->lastPage();
                                $visiblePages = $lastPage <= 5
                                    ? range(1, $lastPage)
                                    : collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                                        ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                                        ->unique()
                                        ->sort()
                                        ->values()
                                        ->all();
                            @endphp
                            @if($enrollments->onFirstPage())
                                <span class="enrollment-page-link disabled" aria-disabled="true">« First</span>
                                <span class="enrollment-page-link disabled" aria-disabled="true">‹ Back</span>
                            @else
                                <a class="enrollment-page-link" href="{{ $enrollments->url(1) }}" aria-label="First page">« First</a>
                                <a class="enrollment-page-link" href="{{ $enrollments->previousPageUrl() }}" rel="prev">‹ Back</a>
                            @endif

                            @php $previousVisiblePage = null; @endphp
                            @foreach($visiblePages as $page)
                                @if($previousVisiblePage !== null && $page > $previousVisiblePage + 1)
                                    <span class="enrollment-page-ellipsis" aria-hidden="true">…</span>
                                @endif
                                @if($page === $currentPage)
                                    <span class="enrollment-page-link active" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="enrollment-page-link" href="{{ $enrollments->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                                @endif
                                @php $previousVisiblePage = $page; @endphp
                            @endforeach

                            @if($enrollments->hasMorePages())
                                <a class="enrollment-page-link" href="{{ $enrollments->nextPageUrl() }}" rel="next">Next ›</a>
                                <a class="enrollment-page-link" href="{{ $enrollments->url($lastPage) }}" aria-label="Last page">Last »</a>
                            @else
                                <span class="enrollment-page-link disabled" aria-disabled="true">Next ›</span>
                                <span class="enrollment-page-link disabled" aria-disabled="true">Last »</span>
                            @endif
                        </nav>
                    @endif
                </section>
            </div>
        </main>
    </div>
    @include('components.responsive')
</body>
</html>
