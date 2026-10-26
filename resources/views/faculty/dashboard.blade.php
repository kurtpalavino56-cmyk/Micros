{{--
    resources/views/faculty/dashboard.blade.php

    Faculty Dashboard â€” single self-contained Blade view (no layout file).

    Expected data from the route, e.g.:

    return view('faculty.dashboard', [
        'user' => $user,
        'stats' => [
            'total_courses' => 5,
            'published' => 3,
            'total_students' => 5,
            'to_grade' => 2,
            'awaiting_verification' => 1,
        ],
        'monthlyEnrollments' => $monthlyEnrollments,
        'courses' => $facultyCourses,
        'attentionItems' => $attentionItems,
    ]);

    Each $course is expected to expose:
        ->id, ->title, ->status, ->status_class, ->approval_status,
        ->denial_feedback, ->to_grade, ->awaiting_verification,
        ->students_count, ->modules_count, ->thumbnail_url
--}}
<x-faculty.layout title="Faculty Dashboard | Upskill" data-faculty-page="faculty-dashboard">
    <x-slot:head>{{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>



    {{-- Faculty navigation --}}


    {{-- Main content --}}
    <main class="main faculty-dashboard">

        <div class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">Faculty Dashboard</h2>
                <p class="faculty-page-subtitle">Manage your Courses and Track student Performance</p>
                <p class="fac-user-code">User Code: {{ $user->user_code ?? '-' }}</p>
            </div>
            {{-- âœ… Connected â€” goes to Faculty â€º Create Courses only --}}
            <a href="{{ route('faculty.create') }}"><button class="btn-outline" type="button">Create Courses</button></a>
        </div>

        {{-- Stat cards --}}
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4H5a2 2 0 0 0-2 2v14h18V6a2 2 0 0 0-2-2h-3"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="m8 13 2.5 2.5L16 10"/></svg>
                    <span class="num">{{ $stats['to_grade'] ?? 0 }}</span>
                </div>
                <div class="label">To grade</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span class="num">{{ $stats['awaiting_verification'] ?? 0 }}</span>
                </div>
                <div class="label">Awaiting verification</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="num">{{ $stats['total_students'] ?? 0 }}</span>
                </div>
                <div class="label">Total Students</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h8"/></svg>
                    <span class="num">{{ $stats['published'] ?? 0 }} of {{ $stats['total_courses'] ?? 0 }}</span>
                </div>
                <div class="label">Courses published</div>
            </div>
        </section>

        {{-- Courses + side panels --}}
        <div class="content-grid">

            <section class="courses-col">
                <div class="courses-head">
                    <h3>My Courses</h3>
                </div>

                @forelse ($courses as $course)
                    <div class="course-card">
                        <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                        <div class="course-info">
                            <h4>{{ $course->title }}</h4>
                            <div class="course-meta">
                                <span class="status-pill {{ $course->status_class }}">
                                    {{ $course->status }}
                                </span>
                                @if ($course->to_grade > 0)
                                    <a class="to-grade-badge" href="{{ route('faculty.activities.reviews', $course->id) }}">
                                        {{ $course->to_grade }} to grade
                                    </a>
                                @endif
                                <span class="meta-item">
                                    <span class="meta-num">{{ $course->students_count ?? 0 }}</span>
                                    Students
                                </span>
                                <span class="meta-item">
                                    <span class="meta-num">{{ $course->modules_count ?? 0 }}</span>
                                    Modules
                                </span>
                            </div>
                        </div>
                        <div class="card-actions">
                            {{-- âœ… Connected â€” goes to Faculty â€º My Courses Manage (per-course) --}}
                            <a href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">
                                <button class="btn-manage" type="button">Manage</button>
                            </a>
                            {{-- Analytics button: open faculty analytics scoped to this course --}}
                            <a href="{{ route('faculty.analytics', ['course_id' => $course->id ?? null]) }}">
                                <button class="btn-manage" type="button">Analytics</button>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        You haven't created any courses yet.
                    </div>
                @endforelse
            </section>

            <aside class="side-col">
                <div class="panel">
                    <div class="panel-head">
                        <span>Needs attention</span>
                        @if ($attentionItems->isNotEmpty())
                            <span class="attention-warning" role="img" aria-label="Attention required" title="Attention required">
                                {{-- Font Awesome Free solid triangle-exclamation, CC BY 4.0: https://github.com/FortAwesome/Font-Awesome/blob/7.x/svgs/solid/triangle-exclamation.svg --}}
                                <svg viewBox="0 0 576 512" aria-hidden="true">
                                    <path fill="currentColor" d="M569.517 440.013 329.577 23.985c-18.434-32.009-64.687-32.057-83.154 0L6.483 440.013C-11.975 472.007 11.194 512 48.06 512h479.88c36.866 0 60.035-39.993 41.577-71.987ZM288 448a32 32 0 1 1 0-64 32 32 0 0 1 0 64Zm24-160c0 13.255-10.745 24-24 24s-24-10.745-24-24V192c0-13.255 10.745-24 24-24s24 10.745 24 24v96Z" />
                                </svg>
                            </span>
                        @endif
                    </div>
                    <div class="panel-body">
                        @if ($attentionItems->isNotEmpty())
                            <div class="attention-list">
                                @foreach ($attentionItems as $item)
                                    <a class="attention-item" href="{{ $item->url }}">
                                        <span class="attention-course">{{ $item->course_title }}</span>
                                        <span class="attention-label">{{ $item->label }}</span>
                                        @if ($item->feedback)
                                            <span class="attention-feedback">{{ \Illuminate\Support\Str::limit($item->feedback, 120) }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="attention-empty">You're all caught up.</div>
                        @endif
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <span>Monthly Enrollments</span>
                    </div>
                    <div class="panel-body">
                        @php $monthlyEnrollments = $monthlyEnrollments ?? collect(); @endphp
                        @if ($monthlyEnrollments->contains(fn ($month) => $month->count > 0))
                            <div class="enrollment-bars" role="img" aria-label="Enrollments for the last six months">
                                @foreach ($monthlyEnrollments as $month)
                                    <div class="enrollment-month">
                                        <span class="enrollment-bar" style="height: {{ max(6, $month->percent) }}%" title="{{ $month->count }} enrollment{{ $month->count === 1 ? '' : 's' }}"></span>
                                        <small>{{ $month->label }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <div class="enrollment-empty-note">Enrollment totals for the last six months.</div>
                        @else
                            <div class="enrollment-bars is-empty" aria-hidden="true">
                                <span></span><span></span><span></span><span></span><span></span><span></span>
                            </div>
                            <div class="enrollment-empty-note">No enrollment data yet. Trends will appear once students join.</div>
                        @endif
                    </div>
                </div>
            </aside>

        </div>

    </main>

    <x-slot:scripts>
{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
