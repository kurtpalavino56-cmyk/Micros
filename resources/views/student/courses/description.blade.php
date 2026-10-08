{{--
    resources/views/student/Course_Description.blade.php

    Expected data from the controller, e.g.:

    return view('student.courses.description', [
        'user'              => $user,               // Auth::user() - needs ->name
        'course'            => $course,             // single Course model
        //   ->id, ->title, ->short_description, ->description, ->category, ->level, ->is_featured
        //   ->instructor (string name), ->duration, ->learning_hours, ->enrolled_count
        //   ->thumbnail_url, ->skills (array), ->learning_outcomes (array)
        //   ->pqf_level, ->target_learners, ->delivery_mode, ->credit_bearing,
        //   ->credit_equivalency, ->equivalent_course, ->badge_name, ->badge_description,
        //   ->assessment_strategy, ->grading_rubric
        'instructor_detail' => $instructorDetail,   // ->name, ->department, ->bio, ->avatar_url
        'modules'           => $modules,            // each: ->title, ->description, ->lessons (->title, ->type, ->duration)
        'quiz'              => $quiz ?? null,       // ->id, ->title, ->questions_count, ->passing_score
        'prerequisites'     => $prerequisites,      // each: ->title, ->completed
        'prerequisites_met' => $prerequisitesMet,
        'missing_prerequisite_titles' => $missing,
        'is_enrolled'       => $isEnrolled,
        'is_completed'      => $isCompleted,
        'completion_ready'  => $completionReady,
        'progress_percent'  => $progressPercent,    // int 0-100
    ]);

    Layout: hero + content on the left, sticky enroll card on the right.
    Below 1100px the card moves directly under the hero.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
<title>{{ $course->title ?? 'Course' }} | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        /* Shared with the navigation / sidebar components */
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --gold-bg:#f5c518;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow:0 10px 25px rgba(19,23,107,0.08);

        /* Page tokens */
        --c-strong:#101828;
        --c-text:#344054;
        --c-muted:#667085;
        --c-border:#e4e7ec;
        --c-surface:#fff;
        --c-surface-alt:#f9fafb;
        --c-accent:#172b7a;
        --c-accent-hover:#10185f;
        --c-focus:rgba(52,72,165,.2);
        --c-good:#067647;
        --c-good-bg:#ecfdf3;
        --c-danger:#b42318;
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--ink);margin:0;background:#f8f9fb;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* ── Page layout ─────────────────────────────────────────── */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}
    .main{min-width:0;padding:0 0 60px;font-family:Inter,Arial,sans-serif;font-size:14px;line-height:1.55;color:var(--c-text);}
    .main button{font-family:inherit;}
    .material-symbols-rounded{font-family:"Material Symbols Rounded";font-weight:normal;font-style:normal;display:inline-block;line-height:1;text-transform:none;letter-spacing:normal;word-wrap:normal;white-space:nowrap;direction:ltr;font-feature-settings:"liga";-webkit-font-smoothing:antialiased;}

    .course-shell{
        display:grid;
        grid-template-columns:minmax(0,1fr) 340px;
        grid-template-areas:"hero aside" "content aside";
        gap:20px 28px;align-items:start;
        padding:0 36px;
    }
    .hero{grid-area:hero;}
    .enroll-card{grid-area:aside;}
    .course-content{grid-area:content;min-width:0;display:flex;flex-direction:column;gap:20px;}

    /* ── Hero ────────────────────────────────────────────────── */
    .hero{background:#172f94;border-radius:16px;padding:30px 32px 26px;color:#fff;box-shadow:0 10px 28px rgba(19,23,107,.14);min-width:0;}
    .hero-tags{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
    .tag{padding:4px 12px;border-radius:6px;font-size:12px;font-weight:600;line-height:1.5;}
    .tag-cat{background:#e8edf7;color:var(--navy);}
    .tag-level{background:var(--gold-bg);color:var(--navy);}
    .tag-feat{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.45);}
    .hero-title{margin:0 0 12px;font-size:28px;font-weight:700;line-height:1.25;letter-spacing:-.02em;color:#fff;}
    .hero-desc{margin:0;max-width:62ch;font-size:15px;line-height:1.65;color:rgba(255,255,255,.86);}

    .skills-label{margin:22px 0 8px;font-size:13px;font-weight:600;color:rgba(255,255,255,.7);}
    .skill-tags{display:flex;gap:8px;flex-wrap:wrap;}
    .skill-tag{padding:5px 12px;border-radius:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:13px;font-weight:500;line-height:1.4;}

    .hero-meta{display:flex;align-items:center;gap:8px 24px;flex-wrap:wrap;margin-top:24px;padding-top:18px;border-top:1px solid rgba(255,255,255,.18);font-size:14px;font-weight:500;color:rgba(255,255,255,.9);}
    .hero-meta span{display:inline-flex;align-items:center;gap:7px;}
    .hero-meta .material-symbols-rounded{width:18px;height:18px;font-size:18px;flex-shrink:0;opacity:.85;}

    /* ── Enroll card ─────────────────────────────────────────── */
    .enroll-card{position:sticky;top:82px;background:var(--c-surface);border:1px solid var(--c-border);border-radius:16px;box-shadow:0 8px 24px rgba(16,24,40,.08);overflow:hidden;}
    .enroll-thumb{aspect-ratio:16/9;background:var(--thumb);background-size:cover;background-position:center;}
    .enroll-body{padding:20px;}
    .progress-label{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;font-weight:600;color:var(--c-strong);}
    .progress-track{width:100%;height:8px;margin-bottom:18px;background:#e9ecf5;border-radius:999px;overflow:hidden;}
    .progress-fill{height:100%;background:var(--navy);border-radius:999px;transition:width .3s;}

    .enroll-actions{display:flex;flex-direction:column;gap:10px;margin-bottom:18px;}
    .enroll-form{display:block;width:100%;margin:0;}
    .btn-enroll,.btn-continue,.btn-complete{
        display:flex;align-items:center;justify-content:center;width:100%;min-height:46px;padding:11px 16px;
        border:1px solid transparent;border-radius:10px;
        font-size:15px;font-weight:600;line-height:1.2;text-align:center;cursor:pointer;
        transition:background .15s,border-color .15s,box-shadow .15s;
    }
    .btn-enroll{background:var(--navy);color:#fff;}
    .btn-enroll:hover{background:var(--navy-deep);}
    .btn-enroll:disabled{background:#e4e7ec;color:#667085;cursor:not-allowed;}
    .btn-continue{background:var(--gold);color:var(--navy);}
    .btn-continue:hover{background:var(--gold-dark);}
    .btn-complete{background:#fff;color:var(--c-good);border-color:#a6dfc0;}
    .btn-complete:hover{background:var(--c-good-bg);}
    .btn-complete:disabled{background:var(--c-surface-alt);border-color:var(--c-border);color:#98a2b3;cursor:not-allowed;}
    .btn-enroll:focus-visible,.btn-continue:focus-visible,.btn-complete:focus-visible{outline:none;box-shadow:0 0 0 3px var(--c-focus);}
    .completion-state{display:flex;align-items:center;justify-content:center;margin-bottom:18px;padding:12px;border-radius:10px;background:var(--c-good-bg);color:var(--c-good);font-size:14px;font-weight:600;text-align:center;}
    .completion-help{display:block;margin:0;color:var(--c-muted);font-size:12px;line-height:1.5;text-align:center;}

    .enroll-perks{display:flex;flex-direction:column;gap:10px;padding-top:16px;border-top:1px solid var(--c-border);}
    .perk{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;color:var(--c-text);}
    .perk .material-symbols-rounded{width:18px;height:18px;font-size:18px;color:var(--gold-dark);flex-shrink:0;}

    /* Prerequisites inside the card */
    .prereq-box{margin:0 0 16px;padding:14px;border:1px solid var(--c-border);border-radius:10px;background:var(--c-surface-alt);}
    .prereq-box strong{display:block;margin-bottom:8px;font-size:13px;font-weight:600;color:var(--c-strong);}
    .prereq-box ul{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;}
    .prereq-box li{display:flex;align-items:baseline;justify-content:space-between;gap:10px;font-size:13px;line-height:1.4;color:var(--c-text);}
    .prereq-state{flex-shrink:0;font-size:12px;font-weight:600;}
    .prereq-state.is-done{color:var(--c-good);}
    .prereq-state.is-todo{color:var(--c-muted);}
    .form-error{margin:0 0 12px;color:var(--c-danger);font-size:13px;font-weight:500;}

    /* ── Content cards ───────────────────────────────────────── */
    .info-panel{background:var(--c-surface);border:1px solid var(--c-border);border-radius:14px;box-shadow:0 1px 2px rgba(16,24,40,.04);overflow:hidden;}
    .info-panel-head{padding:16px 22px;background:#172f94;border-bottom:1px solid #172f94;font-size:16px;font-weight:600;line-height:1.4;color:#fff;}
    .info-panel-body{padding:20px 22px;}
    .empty-note{margin:0;font-size:14px;color:var(--c-muted);}

    .course-overview__body{width:100%;color:var(--c-text);font-size:15px;line-height:1.7;overflow-wrap:anywhere;}
    .course-overview__body>:first-child{margin-top:0;}
    .course-overview__body>:last-child{margin-bottom:0;}
    .course-overview__body h2,.course-overview__body h3,.course-overview__body h4{color:var(--c-strong);line-height:1.35;margin:1.4em 0 .5em;}
    .course-overview__body ul,.course-overview__body ol{padding-left:22px;}
    .course-overview__body img{display:block;width:100%;max-width:100%;height:auto;border-radius:8px;}
    .course-overview__body table{display:block;max-width:100%;overflow-x:auto;}

    .objectives-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px;}
    .objectives-list li{display:flex;align-items:flex-start;gap:12px;font-size:14px;line-height:1.55;color:var(--c-text);}
    .objectives-list li .material-symbols-rounded{width:18px;height:18px;margin-top:2px;font-size:18px;color:var(--navy);flex-shrink:0;}

    .detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px 24px;margin:0;}
    .detail-grid>div{min-width:0;}
    .detail-grid dt{display:flex;align-items:center;gap:8px;margin:0 0 6px;font-size:12px;font-weight:600;color:var(--c-muted);}
    .detail-grid dt .material-symbols-rounded{font-size:24px;color:var(--navy);flex-shrink:0;}
    .detail-grid dd{margin:0;font-size:14px;line-height:1.55;color:var(--c-strong);overflow-wrap:anywhere;}
    .detail-grid .is-wide{grid-column:1/-1;}

    /* ── Course content (modules) ────────────────────────────── */
    .module{border-bottom:1px solid var(--c-border);}
    .module:last-of-type{border-bottom:none;}
    .module-item{display:flex;align-items:center;gap:14px;padding:16px 22px 10px;}
    .module-num{width:32px;height:32px;border-radius:8px;background:#eef1fb;color:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:600;font-variant-numeric:tabular-nums;}
    .module-info{flex:1;min-width:0;}
    .module-title{margin:0;font-size:15px;font-weight:600;line-height:1.4;color:var(--c-strong);}
    .module-sub{margin:2px 0 0;font-size:13px;line-height:1.5;color:var(--c-muted);}
    .module-count{flex-shrink:0;font-size:13px;color:var(--c-muted);}
    .course-module-lessons{padding:0 22px 14px 68px;}
    .course-module-lesson{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;font-size:13px;color:var(--c-text);}
    .course-module-lesson+.course-module-lesson{border-top:1px solid #f0f2f5;}
    .course-module-lesson small{color:var(--c-muted);font-size:12px;white-space:nowrap;}

    .quiz-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 22px;background:var(--c-surface-alt);border-top:1px solid var(--c-border);}
    .quiz-title{margin:0 0 2px;font-size:15px;font-weight:600;color:var(--c-strong);}
    .quiz-sub{margin:0;font-size:13px;color:var(--c-muted);}

    /* ── Instructor ──────────────────────────────────────────── */
    .instructor-wrap{display:flex;align-items:center;gap:14px;}
    .instructor-wrap+.instructor-bio{margin-top:14px;}
    .instructor-avatar{width:52px;height:52px;border-radius:50%;background:var(--thumb);color:var(--navy);flex-shrink:0;background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;}
    .instructor-name{margin:0;font-size:15px;font-weight:600;color:var(--c-strong);}
    .instructor-dept{margin:0;font-size:13px;color:var(--c-muted);}
    .instructor-bio{margin:0;max-width:70ch;font-size:14px;line-height:1.65;color:var(--c-text);}

    /* ── Back to top ─────────────────────────────────────────── */
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:44px;height:44px;border-radius:12px;border:none;background:var(--navy);color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(19,23,107,.28);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:var(--gold);transform:translateY(-2px);}
    #back-to-top-btn .material-symbols-rounded{width:20px;height:20px;font-size:20px;}

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width:1100px){
        .course-shell{grid-template-columns:minmax(0,1fr);grid-template-areas:"hero" "aside" "content";}
        .enroll-card{position:static;}
    }
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .course-shell{padding:0 18px;}
    }
    @media (max-width:640px){
        .course-shell{padding:0 14px;gap:16px;}
        .course-content{gap:16px;}
        .hero{padding:22px 18px 20px;border-radius:14px;}
        .hero-title{font-size:23px;}
        .hero-desc{font-size:14px;}
        .info-panel-head{padding:14px 16px;}
        .info-panel-body{padding:16px;}
        .module-item{padding:14px 16px 8px;}
        .course-module-lessons{padding:0 16px 12px 16px;}
        .quiz-row{flex-direction:column;align-items:stretch;padding:16px;}
    }
    @media (prefers-reduced-motion:reduce){
        *{transition:none!important;}
    }
</style>
</head>
<body>

@include('components.student-navigation')

<div class="layout student-sidebar-layout">

    @include('components.student-sidebar')

    <main class="main">

        {{-- Breadcrumbs --}}
        @php
            $cameFromEnrollments = request()->query('from') === 'enrollments';
            $prerequisites = $prerequisites ?? collect();
        @endphp
        @include('components.breadcrumbs', ['breadcrumbClass' => 'student-breadcrumbs--detail', 'items' => [
            [
                'label' => $cameFromEnrollments ? 'Enrolled Courses' : 'Browse Courses',
                'url' => $cameFromEnrollments ? route('courses.enrolled') : route('courses.browse'),
            ],
            ['label' => $course->title ?? 'Course'],
        ]])

        <div class="course-shell">

            {{-- ── Hero: title, summary, skills, meta ── --}}
            <section class="hero">
                <div class="hero-tags">
                    @if($course->category)
                        <span class="tag tag-cat">{{ $course->category }}</span>
                    @endif
                    @if($course->level)
                        <span class="tag tag-level">{{ $course->level }}</span>
                    @endif
                    @if($course->is_featured ?? false)
                        <span class="tag tag-feat">Featured</span>
                    @endif
                </div>

                <h1 class="hero-title">{{ $course->title }}</h1>
                @php
                    $courseSummary = trim((string) ($course->short_description ?? ''));
                    if ($courseSummary === '') {
                        $courseSummary = \Illuminate\Support\Str::limit(
                            trim(html_entity_decode(strip_tags((string) $course->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                            180
                        );
                    }
                @endphp
                @if(($courseSummary ?? '') !== '')
                    <p class="hero-desc">{{ $courseSummary }}</p>
                @endif

                @if(!empty($course->skills))
                    <div class="skills-label">Skills you'll gain</div>
                    <div class="skill-tags">
                        @foreach($course->skills as $skill)
                            <span class="skill-tag">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="hero-meta">
                    @if($course->instructor)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">person</span>
                            {{ $course->instructor }}
                        </span>
                    @endif
                    @if($course->learning_hours || $course->duration)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">schedule</span>
                            {{ $course->learning_hours ? $course->learning_hours.' '.Str::plural('hour', $course->learning_hours) : $course->duration }}
                        </span>
                    @endif
                    @if($modules->isNotEmpty())
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">menu_book</span>
                            {{ $modules->count() }} {{ Str::plural('module', $modules->count()) }}
                        </span>
                    @endif
                    @if($course->enrolled_count ?? false)
                        <span>
                            <span class="material-symbols-rounded" aria-hidden="true">groups</span>
                            {{ $course->enrolled_count }} enrolled
                        </span>
                    @endif
                </div>
            </section>

            {{-- ── Enroll card (sticky on the right) ── --}}
            <aside class="enroll-card" aria-label="Enrollment">
                <div class="enroll-thumb"
                     @if($course->thumbnail_url)
                         style="background-image:url('{{ $course->thumbnail_url }}')"
                     @endif>
                </div>
                <div class="enroll-body">
                    <div class="progress-label">
                        <span>Your progress</span>
                        <span>{{ $progress_percent ?? 0 }}%</span>
                    </div>
                    <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress_percent ?? 0 }}">
                        <div class="progress-fill" style="width:{{ $progress_percent ?? 0 }}%"></div>
                    </div>

                    @if($prerequisites->isNotEmpty())
                        <div class="prereq-box course-prerequisites">
                            <strong>Prerequisites</strong>
                            <ul>
                                @foreach($prerequisites as $prerequisite)
                                    <li>
                                        <span>{{ $prerequisite->title }}</span>
                                        <span class="prereq-state {{ $prerequisite->completed ? 'is-done' : 'is-todo' }}">{{ $prerequisite->completed ? 'Completed' : 'Not completed' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($errors->has('prerequisites'))
                        <p role="alert" class="form-error">{{ $errors->first('prerequisites') }}</p>
                    @endif

                    @if($is_completed ?? false)
                        <div class="completion-state" role="status">Course completed ✓</div>
                    @elseif($is_enrolled ?? false)
                        <div class="enroll-actions">
                            <a href="{{ route('courses.learn', $course->id) }}" class="btn-continue">
                                {{ ($progress_percent ?? 0) > 0 ? 'Continue learning' : 'Start course' }}
                            </a>
                            <button class="btn-complete" id="complete-course-btn" type="button" {{ ($completion_ready ?? false) ? '' : 'disabled' }}>
                                {{ ($completion_ready ?? false) ? 'Mark course complete' : 'Complete the course first' }}
                            </button>
                            @unless($completion_ready ?? false)
                                <small class="completion-help">Finish the required lessons and quizzes to unlock completion.</small>
                            @endunless
                        </div>
                    @elseif(!($prerequisites_met ?? true))
                        <div class="enroll-actions">
                            <button type="button" class="btn-enroll" disabled aria-disabled="true">
                                Complete prerequisites first
                            </button>
                            <small class="completion-help">
                                Complete: {{ ($missing_prerequisite_titles ?? collect())->implode(', ') }}
                            </small>
                        </div>
                    @else
                        <div class="enroll-actions">
                            <form action="{{ route('courses.enroll', $course->id) }}" method="POST" class="enroll-form">
                                @csrf
                                <button type="submit" class="btn-enroll">Enroll now</button>
                            </form>
                        </div>
                    @endif

                    <div class="enroll-perks">
                        <div class="perk">
                            <span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span>
                            Earn a digital certificate
                        </div>
                    </div>
                </div>
            </aside>

            {{-- ── Main content column ── --}}
            <div class="course-content">

                {{-- About --}}
                @if(trim(strip_tags((string) $course->description)) !== '')
                    <section class="info-panel course-overview" aria-labelledby="course-overview-title">
                        <div class="info-panel-head course-overview__title" id="course-overview-title">About this course</div>
                        <div class="info-panel-body">
                            <div class="course-overview__body">{!! $course->description !!}</div>
                        </div>
                    </section>
                @endif

                {{-- Learning outcomes --}}
                <section class="info-panel">
                    <div class="info-panel-head">Learning outcomes</div>
                    <div class="info-panel-body">
                        @if(!empty($course->learning_outcomes))
                            <ul class="objectives-list">
                                @foreach($course->learning_outcomes as $objective)
                                    <li>
                                        <span class="material-symbols-rounded" aria-hidden="true">check</span>
                                        <span>{{ $objective }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="empty-note">No measurable outcomes listed yet.</p>
                        @endif
                    </div>
                </section>

                {{-- Micro-credential details (only rendered when something is set) --}}
                @if($course->pqf_level || $course->target_learners || $course->delivery_mode || $course->credit_bearing || $course->badge_name || $course->assessment_strategy || $course->grading_rubric)
                    <section class="info-panel">
                        <div class="info-panel-head">Micro-credential details</div>
                        <div class="info-panel-body">
                            <dl class="detail-grid">
                                @if($course->pqf_level)<div><dt><span class="material-symbols-rounded" aria-hidden="true">school</span>PQF level</dt><dd>Level {{ $course->pqf_level }}</dd></div>@endif
                                @if($course->delivery_mode)<div><dt><span class="material-symbols-rounded" aria-hidden="true">devices</span>Delivery</dt><dd>{{ Str::headline($course->delivery_mode) }}</dd></div>@endif
                                @if($course->target_learners)<div><dt><span class="material-symbols-rounded" aria-hidden="true">groups</span>Intended learners</dt><dd>{{ $course->target_learners }}</dd></div>@endif
                                @if($course->credit_bearing)<div><dt><span class="material-symbols-rounded" aria-hidden="true">account_balance</span>Credit information</dt><dd>{{ $course->credit_equivalency ?: 'Proposed for institutional review' }}@if($course->equivalent_course) &middot; {{ $course->equivalent_course }}@endif</dd></div>@endif
                                @if($course->badge_name)<div><dt><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span>Completion badge</dt><dd>{{ $course->badge_name }}@if($course->badge_description) &middot; {{ $course->badge_description }}@endif</dd></div>@endif
                                @if($course->assessment_strategy)<div class="is-wide"><dt><span class="material-symbols-rounded" aria-hidden="true">fact_check</span>Assessment strategy</dt><dd>{{ $course->assessment_strategy }}</dd></div>@endif
                                @if($course->grading_rubric)<div class="is-wide"><dt><span class="material-symbols-rounded" aria-hidden="true">rule</span>Mastery criteria</dt><dd>{{ $course->grading_rubric }}</dd></div>@endif
                            </dl>
                        </div>
                    </section>
                @endif

                {{-- Course content --}}
                @if(($modules ?? collect())->count())
                    <section class="info-panel">
                        <div class="info-panel-head">Course content</div>

                        @foreach($modules as $module)
                            <div class="module">
                                <div class="module-item">
                                    <div class="module-num" aria-hidden="true">{{ $loop->iteration }}</div>
                                    <div class="module-info">
                                        <p class="module-title"><span class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Module {{ $loop->iteration }}: </span>{{ $module->title }}</p>
                                        @if($module->description ?? false)
                                            <p class="module-sub">{{ $module->description }}</p>
                                        @endif
                                    </div>
                                    <span class="module-count">{{ $module->lessons->count() }} {{ Str::plural('lesson', $module->lessons->count()) }}</span>
                                </div>
                                @if($module->lessons->isNotEmpty())
                                    <div class="course-module-lessons">
                                        @foreach($module->lessons as $lesson)
                                            <div class="course-module-lesson">
                                                <span>{{ $lesson->title }}</span>
                                                <small>{{ $lesson->type ?: 'Lesson' }}@if($lesson->duration) &middot; {{ $lesson->duration }}@endif</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Inline quiz (bottom of the content list) --}}
                        @if($quiz ?? false)
                            <div class="quiz-row">
                                <div class="quiz-info">
                                    <p class="quiz-title">{{ $quiz->title }}</p>
                                    <p class="quiz-sub">
                                        {{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}
                                        @if($quiz->passing_score ?? false)
                                            &middot; Pass {{ $quiz->passing_score }}%
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Instructor --}}
                <section class="info-panel">
                    <div class="info-panel-head">About the instructor</div>
                    <div class="info-panel-body">
                        @if($instructor_detail ?? false)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar"
                                     @if($instructor_detail->avatar_url)
                                         style="background-image:url('{{ $instructor_detail->avatar_url }}')"
                                     @endif>
                                    @unless($instructor_detail->avatar_url){{ Str::upper(Str::substr($instructor_detail->name, 0, 1)) }}@endunless
                                </div>
                                <div>
                                    <p class="instructor-name">{{ $instructor_detail->name }}</p>
                                    @if($instructor_detail->department ?? false)
                                        <p class="instructor-dept">{{ $instructor_detail->department }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($instructor_detail->bio ?? false)
                                <p class="instructor-bio">{{ $instructor_detail->bio }}</p>
                            @endif
                        @elseif($course->instructor)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar">{{ Str::upper(Str::substr($course->instructor, 0, 1)) }}</div>
                                <div>
                                    <p class="instructor-name">{{ $course->instructor }}</p>
                                    <p class="instructor-dept">Instructor</p>
                                </div>
                            </div>
                        @else
                            <p class="empty-note">No instructor information available.</p>
                        @endif
                    </div>
                </section>

            </div>{{-- /.course-content --}}

        </div>{{-- /.course-shell --}}

    </main>
</div>

{{-- Back to top (appears on long pages) --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
</button>

<script>
    (function () {
        var completeButton = document.getElementById('complete-course-btn');
        if (!completeButton || completeButton.disabled) return;
        completeButton.addEventListener('click', function () {
            completeButton.disabled = true;
            completeButton.textContent = 'Saving completion…';
            fetch('{{ route('courses.complete', $course->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data.completed) {
                    throw new Error(result.data.message || 'Completion could not be saved.');
                }
                var actions = completeButton.closest('.enroll-actions');
                var done = document.createElement('div');
                done.className = 'completion-state';
                done.setAttribute('role', 'status');
                done.textContent = 'Course completed ✓';
                if (actions) { actions.replaceWith(done); } else { completeButton.replaceWith(done); }

                var progressPercent = Math.max(0, Math.min(100, Number(result.data.progress_percent) || 0));
                var progressLabel = document.querySelector('.progress-label span:last-child');
                var progressFill = document.querySelector('.progress-fill');
                if (progressLabel) progressLabel.textContent = progressPercent + '%';
                if (progressFill) progressFill.style.width = progressPercent + '%';
            })
            .catch(function (error) {
                completeButton.disabled = false;
                completeButton.textContent = 'Mark course complete';
                window.alert(error.message);
            });
        });
    })();

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
</body>
</html>
