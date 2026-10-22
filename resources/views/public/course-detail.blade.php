@extends('layouts.app')

@section('title', $course->title.' – UpSkill PSU')

@push('styles')
<style>
    .pc{--pc-blue:#172f94;--pc-navy:#0f1f63;--pc-gold:#f5c518;--pc-ink:#1d2433;--pc-muted:#5b667a;--pc-line:#dfe4ee;--pc-tint:#f3f5fb;--pc-teal:#16877f;
        --pc-navbar-h:5rem; /* set to your navbar's height */
        --pc-sticky-top:calc(var(--pc-navbar-h) + 1.5rem);
        background:#fff;color:var(--pc-ink);padding-bottom:4rem;overflow-x:clip}
    .pc-hero h1,.pc-section > h2{font-family:inherit;letter-spacing:-.015em}

    /* Shell: hero + tabs on the left, card on the right starting at the hero's top */
    .pc-shell{display:grid;grid-template-columns:minmax(0,1fr) 340px;grid-template-areas:"hero aside" "main aside";column-gap:3rem;align-items:start}

    /* Hero: navy band runs full width behind the left column */
    .pc-hero{grid-area:hero;position:relative;z-index:1;background:var(--pc-navy);color:#fff;padding:2.5rem 0 3rem;box-shadow:0 0 0 100vmax var(--pc-navy);clip-path:inset(0 -100vmax)}
    .pc-hero__category{display:inline-block;margin:0 0 1rem;padding:.25rem .7rem;border-left:3px solid var(--pc-gold);font-size:.9rem;color:#f7e3a0}
    .pc-hero h1{font-size:clamp(2rem,4.2vw,3rem);line-height:1.12;font-weight:700;margin:0 0 1rem;color:#fff}
    .pc-hero__desc{font-size:1.08rem;line-height:1.6;color:rgba(255,255,255,.85);margin:0 0 1.5rem;max-width:60ch}
    .pc-hero__facts{display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;margin:0;padding:0;list-style:none;font-size:.92rem;color:rgba(255,255,255,.85)}
    .pc-hero__facts strong{color:#fff;font-weight:700}

    /* Card */
    .pc-aside{grid-area:aside;position:sticky;top:var(--pc-sticky-top);z-index:2;margin-top:2.5rem;background:#fff;border:1px solid var(--pc-line);border-radius:12px;box-shadow:0 12px 32px rgba(15,31,99,.16);overflow:hidden}
    .pc-aside__img{aspect-ratio:16/9;background:#e9edf7 center/cover no-repeat}
    .pc-aside__body{padding:1.25rem}
    .pc-btn{display:flex;align-items:center;justify-content:center;width:100%;padding:.8rem 1rem;border-radius:8px;font-weight:700;text-decoration:none;border:2px solid transparent;transition:background-color .15s}
    .pc-btn--primary{background:var(--pc-gold);color:#13176b}
    .pc-btn--primary:hover{background:#e6b800;color:#13176b}
    .pc-btn--ghost{margin-top:.6rem;border-color:var(--pc-blue);color:var(--pc-blue);background:#fff}
    .pc-btn--ghost:hover{background:var(--pc-tint);color:var(--pc-blue)}
    .pc a:focus-visible,.pc summary:focus-visible,.pc-tab:focus-visible{outline:3px solid #2f6bff;outline-offset:2px}
    .pc-details{margin:1.25rem 0 0;padding:0;border-top:1px solid var(--pc-line)}
    .pc-details div{display:flex;justify-content:space-between;gap:1rem;padding:.65rem 0;border-bottom:1px solid var(--pc-line);font-size:.9rem}
    .pc-details dt{color:var(--pc-muted);font-weight:400}
    .pc-details dd{margin:0;text-align:right;font-weight:600;color:var(--pc-ink)}
    .pc-aside__note{margin:1rem 0 0;font-size:.82rem;line-height:1.5;color:var(--pc-muted)}

    /* Tabs */
    .pc-main{grid-area:main;min-width:0}
    .pc-tabs{display:flex;gap:2rem;border-bottom:1px solid var(--pc-line);margin:0 0 2rem}
    .pc:not(.pc--js) .pc-tabs{display:none}
    .pc-tab{appearance:none;background:none;border:0;border-bottom:2px solid transparent;margin-bottom:-1px;padding:1.1rem 0 .85rem;font:inherit;font-weight:600;color:var(--pc-muted);cursor:pointer}
    .pc-tab:hover{color:var(--pc-ink)}
    .pc-tab[aria-selected="true"]{color:var(--pc-blue);border-bottom-color:var(--pc-blue)}
    .pc-tab__count{margin-left:.35rem;font-weight:400;font-size:.85rem;color:var(--pc-muted)}
    .pc-panel[hidden]{display:none}

    /* Sections */
    .pc-section{padding:0 0 2.25rem;margin:0 0 2.25rem;border-bottom:1px solid var(--pc-line)}
    .pc-section:last-child{border-bottom:0;margin-bottom:0}
    .pc-section > h2{font-size:1.5rem;font-weight:700;line-height:1.25;margin:0 0 1.1rem;color:var(--pc-navy)}
    .pc-outcomes{display:grid;grid-template-columns:1fr;gap:.85rem;margin:0;padding:0;list-style:none;line-height:1.5}
    .pc-outcomes li{display:grid;grid-template-columns:1.4rem minmax(0,1fr);gap:.6rem}
    .pc-outcomes li::before{content:"✓";display:grid;place-items:center;width:1.4rem;height:1.4rem;border-radius:50%;background:#e4f5f2;color:var(--pc-teal);font-size:.8rem;font-weight:800}
    .pc-prose{line-height:1.7;max-width:70ch}
    .pc-prose p{margin:0 0 1rem}
    .pc-prose > :first-child{margin-top:0}
    .pc-callout{background:var(--pc-tint);border-left:4px solid var(--pc-blue);border-radius:0 8px 8px 0;padding:1rem 1.15rem;margin-top:1rem;line-height:1.6}
    .pc-callout strong{display:block;margin-bottom:.2rem;color:var(--pc-navy)}

    /* Outline accordion */
    .pc-outline-meta{margin:-.5rem 0 1rem;color:var(--pc-muted);font-size:.92rem}
    .pc-module{border:1px solid var(--pc-line);border-radius:10px;margin-bottom:.6rem;background:#fff}
    .pc-module--final{padding:.9rem 1.1rem;font-weight:700;color:var(--pc-ink)}
    .pc-module summary{display:flex;align-items:center;gap:1rem;padding:.9rem 1.1rem;cursor:pointer;list-style:none;font-weight:700;color:var(--pc-ink)}
    .pc-module summary::-webkit-details-marker{display:none}
    .pc-module summary::after{content:"+";flex:0 0 auto;font-size:1.3rem;line-height:1;color:var(--pc-blue)}
    .pc-module[open] summary::after{content:"–"}
    .pc-module[open] summary{border-bottom:1px solid var(--pc-line)}
    .pc-module__count{margin-left:auto;font-weight:400;font-size:.85rem;color:var(--pc-muted);white-space:nowrap}
    .pc-module__desc{margin:0;padding:.85rem 1.1rem 0;color:var(--pc-muted);font-size:.9rem;line-height:1.55}
    .pc-lessons{margin:0;padding:.4rem 1.1rem .6rem;list-style:none}
    .pc-lessons li{display:flex;align-items:baseline;gap:.75rem;padding:.55rem 0;border-bottom:1px solid #eef1f6}
    .pc-lessons li:last-child{border-bottom:0}
    .pc-lessons__title{flex:1 1 auto;min-width:0}
    .pc-lessons__meta{flex:0 0 auto;font-size:.82rem;color:var(--pc-muted);text-align:right}

    @media(max-width:900px){
        .pc-shell{grid-template-columns:minmax(0,1fr);grid-template-areas:"hero" "aside" "main";column-gap:0}
        .pc-hero{padding:2rem 0}
        .pc-aside{position:static;margin-top:1.5rem}
        .pc-aside__img{display:none}
        .pc-tabs{margin-top:1.5rem}
    }
    @media(max-width:640px){.pc-section > h2{font-size:1.3rem}}
</style>
@endpush

@section('content')
@php
    $shortDescription = trim((string) $course->short_description);
    if ($shortDescription === '') {
        $shortDescription = \Illuminate\Support\Str::limit(
            trim(html_entity_decode(strip_tags((string) $course->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            160
        );
    }
    $instructorName = $course->instructor ?: $course->creator?->name;
    $moduleCount = $course->modules->count();
    $lessonCount = $course->modules->sum(fn ($m) => $m->lessons->count());
    $hasOutcomes = $course->learningOutcomes->isNotEmpty() || !empty($course->objectives);
    $hasDescription = trim(strip_tags((string) $course->description)) !== '';
    $thumb = $course->thumbnail_url ? asset($course->thumbnail_url) : asset('Images/PSU_Front_Building.jpg');
@endphp

<div class="pc">
    <div class="container">
         <div class="pc-shell">
            <header class="pc-hero">
                <p class="pc-hero__category">{{ $course->category ?: 'Microcredential' }}@if($course->level) &middot; {{ $course->level }}@endif</p>
                <h1>{{ $course->title }}</h1>
                @if($shortDescription !== '')
                    <p class="pc-hero__desc">{{ $shortDescription }}</p>
                @endif
                <ul class="pc-hero__facts">
                    @if($instructorName)<li>Taught by <strong>{{ $instructorName }}</strong></li>@endif
                    @if($moduleCount)<li><strong>{{ $moduleCount }}</strong> {{ \Illuminate\Support\Str::plural('module', $moduleCount) }}</li>@endif
                    @if($lessonCount)<li><strong>{{ $lessonCount }}</strong> {{ \Illuminate\Support\Str::plural('lesson', $lessonCount) }}</li>@endif
                </ul>
            </header>

            <aside class="pc-aside" aria-label="Enroll and course details">
                <div class="pc-aside__img" role="img" aria-label="{{ $course->title }} course image" style="background-image:url('{{ $thumb }}')"></div>
                <div class="pc-aside__body">
                    <a class="pc-btn pc-btn--primary" href="{{ $loginUrl }}">Sign in to enroll</a>
                    <a class="pc-btn pc-btn--ghost" href="{{ route('register') }}">Create an account</a>

                    <dl class="pc-details">
                        @if($course->pqf_level)<div><dt>PQF level</dt><dd>Level {{ $course->pqf_level }}</dd></div>@endif
                        @if($course->learning_hours || $course->duration)<div><dt>Estimated effort</dt><dd>{{ $course->learning_hours ? $course->learning_hours.' '.\Illuminate\Support\Str::plural('hour', $course->learning_hours) : $course->duration }}</dd></div>@endif
                        @if($course->delivery_mode)<div><dt>Delivery</dt><dd>{{ \Illuminate\Support\Str::headline($course->delivery_mode) }}</dd></div>@endif
                        @if($course->credit_bearing)<div><dt>Credit</dt><dd>{{ $course->credit_equivalency ?: 'Subject to institutional review' }}</dd></div>@endif
                        @if($course->target_learners)<div><dt>Intended for</dt><dd>{{ $course->target_learners }}</dd></div>@endif
                        @if($course->badge?->is_active)<div><dt>Badge</dt><dd>{{ $course->badge->name }}</dd></div>@endif
                        @if($course->certificate_enabled)<div><dt>Certificate</dt><dd>On completion</dd></div>@endif
                    </dl>

                    <p class="pc-aside__note">You need an UpSkill PSU account to enroll. Look through the course first, then sign in or register when you’re ready.</p>
                </div>
            </aside>

        <main class="pc-main">
                <div class="pc-tabs" role="tablist" aria-label="Course sections">
                    <button class="pc-tab" type="button" role="tab" id="tab-overview" aria-controls="panel-overview" aria-selected="true">Overview</button>
                    <button class="pc-tab" type="button" role="tab" id="tab-lessons" aria-controls="panel-lessons" aria-selected="false" tabindex="-1">Lessons @if($lessonCount)<span class="pc-tab__count">{{ $lessonCount }}</span>@endif</button>
                </div>

                <div class="pc-panel" id="panel-overview" role="tabpanel" aria-labelledby="tab-overview">
                    @if($hasDescription)
                        <section class="pc-section">
                            <h2>About this course</h2>
                            <div class="pc-prose rich-content">{!! $course->description !!}</div>
                        </section>
                    @endif

                @if($hasOutcomes)
                        <section class="pc-section">
                            <h2>What you’ll learn</h2>
                            @if($course->learningOutcomes->count() === 1)
                                <div class="pc-prose rich-content">{!! \App\Support\RichTextSanitizer::sanitize($course->learningOutcomes->first()->description) !!}</div>
                            @elseif($course->learningOutcomes->isNotEmpty())
                                <ul class="pc-outcomes">
                                    @foreach($course->learningOutcomes as $outcome)
                                        <li>{{ $outcome->description }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <ul class="pc-outcomes">
                                    @foreach($course->objectives ?? [] as $outcome)
                                        <li>{{ $outcome }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endif

                @if($course->assessment_strategy || $course->grading_rubric)
                        <section class="pc-section">
                            <h2>How you’ll be assessed</h2>
                            @if($course->assessment_strategy)
                                <p class="pc-prose">{{ $course->assessment_strategy }}</p>
                            @endif
                            @if($course->grading_rubric)
                                <div class="pc-callout"><strong>Mastery criteria</strong>{{ $course->grading_rubric }}</div>
                            @endif
                        </section>
                     @endif
                </div>
                <div class="pc-panel" id="panel-lessons" role="tabpanel" aria-labelledby="tab-lessons">
                    <section class="pc-section">
                        <h2>Course outline</h2>
                        @if($moduleCount)
                            <p class="pc-outline-meta">{{ $moduleCount }} {{ \Illuminate\Support\Str::plural('module', $moduleCount) }} &middot; {{ $lessonCount }} {{ \Illuminate\Support\Str::plural('lesson', $lessonCount) }}</p>
                        @endif
                        @forelse($course->modules as $module)
                            @if($module->is_final)
                                <div class="pc-module pc-module--final">Final Exam</div>
                            @else
                                <details class="pc-module" @if($loop->first) open @endif>
                                    <summary>
                                        <span>{{ $module->title }}</span>
                                        <span class="pc-module__count">{{ $module->lessons->count() }} {{ \Illuminate\Support\Str::plural('lesson', $module->lessons->count()) }}</span>
                                    </summary>
                                    @if($module->description)
                                        <p class="pc-module__desc">{{ strip_tags($module->description) }}</p>
                                    @endif
                                    <ul class="pc-lessons">
                                        @foreach($module->lessons as $lesson)
                                            <li>
                                                <span class="pc-lessons__title">{{ $lesson->title }}</span>
                                                <span class="pc-lessons__meta">{{ $lesson->type ?: 'Lesson' }}@if($lesson->duration) &middot; {{ $lesson->duration }}@endif</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        @empty
                            <p>The course outline will be available soon.</p>
                        @endforelse
                    </section>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.querySelector('.pc');
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = tabs.map(function (t) { return document.getElementById(t.getAttribute('aria-controls')); });
    root.classList.add('pc--js');

    function show(i, focus) {
        tabs.forEach(function (t, j) {
            var on = i === j;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            panels[j].hidden = !on;
        });
        if (focus) tabs[i].focus();
    }

    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () {
            show(i);
            if (history.replaceState) history.replaceState(null, '', '#' + t.id.replace('tab-', ''));
        });
        t.addEventListener('keydown', function (e) {
            var n = null;
            if (e.key === 'ArrowRight') n = (i + 1) % tabs.length;
            else if (e.key === 'ArrowLeft') n = (i - 1 + tabs.length) % tabs.length;
            else if (e.key === 'Home') n = 0;
            else if (e.key === 'End') n = tabs.length - 1;
            if (n !== null) { e.preventDefault(); show(n, true); }
        });
    });

    show(location.hash === '#lessons' ? 1 : 0);
})();
</script>
@endsection