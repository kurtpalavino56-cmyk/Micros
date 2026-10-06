@extends('layouts.app')

@section('title', $course->title.' – UpSkill PSU')

@push('styles')
<style>
    .public-course{padding:2rem 0 4rem;background:#f5f7fb;min-height:70vh;color:#344054}
    .public-course__hero{background:#13176b;color:#fff;border-radius:16px;padding:2rem;display:grid;grid-template-columns:minmax(0,1.4fr) minmax(220px,.6fr);gap:1.5rem;align-items:center;overflow:hidden}
    .public-course__eyebrow{color:#f7d889;font-size:.76rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin:0 0 .7rem}
    .public-course__hero h1{font-size:clamp(2rem,4vw,3.1rem);line-height:1.08;margin:0 0 .7rem;color:#fff}
    .public-course__sub{font-size:1.05rem;color:rgba(255,255,255,.8);margin:0 0 1rem;max-width:680px}
    .public-course__desc{font-size:.95rem;line-height:1.6;color:rgba(255,255,255,.82);max-width:720px;margin:.4rem 0 0}
    .public-course__full-description{color:#40516e;font-size:.95rem;line-height:1.7}
    .public-course__full-description > :first-child{margin-top:0}
    .public-course__full-description > :last-child{margin-bottom:0}
    .public-course__full-description h2{margin:1.5rem 0 .65rem;color:#08245f;font-size:1.55rem;line-height:1.3}
    .public-course__full-description h1{margin:0 0 .65rem;color:#08245f;font-size:1.55rem;line-height:1.3}
    .public-course__full-description h3{margin:1.25rem 0 .55rem;color:#123b78;font-size:1.28rem;line-height:1.35}
    .public-course__full-description h4{margin:1rem 0 .45rem;color:#214b84;font-size:1.08rem;line-height:1.4}
    .public-course__full-description p{margin:0 0 1rem}
    .public-course__full-description figure.image{max-width:100%;margin:1rem 0}
    .public-course__full-description figure.image img{display:block;width:auto;max-width:100%;height:auto}
    .public-course__full-description ul,.public-course__full-description ol{margin:.6rem 0 1rem;padding-left:1.6rem}
    .public-course__full-description li{margin:.3rem 0}
    .public-course__full-description a{color:#1c5ca8;text-decoration:underline}
    .public-course__thumb{min-height:210px;border-radius:10px;background:url('{{ $course->thumbnail_url ? asset($course->thumbnail_url) : asset('Images/PSU_Front_Building.jpg') }}') center/cover}
    .public-course__actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.4rem}
    .public-course__button{display:inline-flex;align-items:center;justify-content:center;border-radius:8px;padding:.72rem 1rem;font-weight:750;background:#f5c518;color:#13176b}
    .public-course__button--secondary{background:transparent;border:1px solid rgba(255,255,255,.55);color:#fff}
    .public-course__grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:1rem;margin-top:1rem;align-items:start}
    .public-course__main{display:grid;gap:1rem;min-width:0}
    .public-course__panel{background:#fff;border:1px solid #e2e7ef;border-radius:12px;padding:0 1.25rem 1.15rem;overflow:hidden}
    .public-course__panel h2{margin:0 -1.25rem .85rem;padding:.8rem 1.25rem;background:#13176b;color:#fff;font-size:1.1rem}
    .public-course__panel h3{margin:.2rem 0 .45rem;color:#182230;font-size:.98rem;line-height:1.4}
    .public-course__outcome-list{margin:0;padding:0;display:grid;gap:.55rem;line-height:1.55;list-style:none}
    .public-course__outcome-list li{display:grid;grid-template-columns:1.35rem minmax(0,1fr);align-items:start;gap:.55rem}
    .public-course__outcome-list li::before{content:"✓";display:grid;place-items:center;width:1.3rem;height:1.3rem;margin-top:.1rem;border-radius:50%;background:#e4f5f2;color:#16877f;font-size:.82rem;font-weight:800;line-height:1}
    .public-course__module{padding:.75rem 0;border-top:1px solid #edf0f4}
    .public-course__module:first-of-type{border-top:0;padding-top:0}
    .public-course__module-description{margin:.15rem 0 .5rem;color:#667085;font-size:.88rem;line-height:1.55}
    .public-course__lesson{display:flex;align-items:center;gap:.75rem;padding:.48rem 0 .48rem 1rem;border-bottom:0;color:#536583;font-size:.9rem}
    .public-course__lesson:last-child{border-bottom:0}
    .public-course__lesson-icon{width:24px;height:24px;border-radius:6px;display:grid;place-items:center;background:#edf2ff;color:#243c91;font-size:.8rem;font-weight:800;flex:0 0 auto}
    .public-course__meta{display:grid;gap:.65rem}
    .public-course__meta-row{display:flex;justify-content:space-between;gap:1rem;color:#6e7e9b;font-size:.9rem}
    .public-course__meta-row strong{color:#14254b;text-align:right}
    .public-course__note{margin-top:1rem;padding:.9rem 1rem;border-radius:12px;background:#fff5d8;color:#765b10;font-size:.85rem;line-height:1.5}
    @media(max-width:860px){.public-course__grid{grid-template-columns:1fr}.public-course__hero{grid-template-columns:minmax(0,1fr) minmax(180px,.6fr)}}
    @media(max-width:640px){.public-course__hero{grid-template-columns:1fr;padding:1.25rem}.public-course__thumb{min-height:160px;grid-row:1}.public-course{padding:1rem 0 2.5rem}.public-course__panel{padding:0 1rem 1rem}.public-course__panel h2{margin-right:-1rem;margin-left:-1rem;padding:.75rem 1rem}}
</style>
@endpush

@section('content')
<section class="public-course">
    <div class="container">
        <div class="public-course__hero">
            <div>
                <p class="public-course__eyebrow">{{ $course->category ?: 'Microcredential' }}@if($course->level) · {{ $course->level }}@endif</p>
                <h1>{{ $course->title }}</h1>
                @php
                    $shortDescription = trim((string) $course->short_description);
                    if ($shortDescription === '') {
                        $shortDescription = \Illuminate\Support\Str::limit(
                            trim(html_entity_decode(strip_tags((string) $course->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                            120
                        );
                    }
                @endphp
                @if($shortDescription !== '')
                    <p class="public-course__desc">{{ $shortDescription }}</p>
                @endif
                <div class="public-course__actions">
                    <a class="public-course__button" href="{{ $loginUrl }}">Sign in to enroll</a>
                    <a class="public-course__button public-course__button--secondary" href="{{ route('register') }}">Create an account</a>
                </div>
            </div>
            <div class="public-course__thumb" role="img" aria-label="{{ $course->title }} course image"></div>
        </div>

        <div class="public-course__grid">
            <div class="public-course__main">
                @if($course->learningOutcomes->isNotEmpty() || !empty($course->objectives))
                    <article class="public-course__panel">
                        <h2>What you’ll learn</h2>
                        <ul class="public-course__outcome-list">
                            @forelse($course->learningOutcomes as $outcome)
                                <li>{{ $outcome->description }}</li>
                            @empty
                                @foreach($course->objectives ?? [] as $outcome)
                                    <li>{{ $outcome }}</li>
                                @endforeach
                            @endforelse
                        </ul>
                    </article>
                @endif

                @if(trim(strip_tags((string) $course->description)) !== '')
                    <article class="public-course__panel public-course__full-description">
                        <h2>About this course</h2>
                        {!! $course->description !!}
                    </article>
                @endif

                @if($course->assessment_strategy || $course->grading_rubric)
                    <article class="public-course__panel">
                        <h2>Assessment and completion</h2>
                        @if($course->assessment_strategy)
                            <p>{{ $course->assessment_strategy }}</p>
                        @endif
                        @if($course->grading_rubric)
                            <p><strong>Mastery criteria:</strong> {{ $course->grading_rubric }}</p>
                        @endif
                    </article>
                @endif

                <article class="public-course__panel">
                    <h2>Course outline</h2>
                    @forelse($course->modules as $module)
                        <section class="public-course__module">
                            <h3>{{ $module->title }}</h3>
                            @if($module->description)
                                <p class="public-course__module-description">{{ strip_tags($module->description) }}</p>
                            @endif
                            @foreach($module->lessons as $lesson)
                                <div class="public-course__lesson"><span class="public-course__lesson-icon">{{ $loop->iteration }}</span><span><strong>{{ $lesson->title }}</strong><br><small>{{ $lesson->type ?: 'Lesson' }}@if($lesson->duration) · {{ $lesson->duration }}@endif</small></span></div>
                            @endforeach
                        </section>
                    @empty
                        <p>The course outline will be available soon.</p>
                    @endforelse
                </article>
            </div>
            <aside class="public-course__panel">
                <h2>Course details</h2>
                <div class="public-course__meta">
                    @if($course->instructor || $course->creator)<div class="public-course__meta-row"><span>Instructor</span><strong>{{ $course->instructor ?: $course->creator->name }}</strong></div>@endif
                    @if($course->pqf_level)<div class="public-course__meta-row"><span>PQF level</span><strong>Level {{ $course->pqf_level }}</strong></div>@endif
                    @if($course->learning_hours || $course->duration)<div class="public-course__meta-row"><span>Estimated effort</span><strong>{{ $course->learning_hours ? $course->learning_hours.' '.Illuminate\Support\Str::plural('hour', $course->learning_hours) : $course->duration }}</strong></div>@endif
                    @if($course->delivery_mode)<div class="public-course__meta-row"><span>Delivery</span><strong>{{ Illuminate\Support\Str::headline($course->delivery_mode) }}</strong></div>@endif
                    @if($course->credit_bearing)<div class="public-course__meta-row"><span>Credit information</span><strong>{{ $course->credit_equivalency ?: 'Subject to institutional review' }}</strong></div>@endif
                    @if($course->target_learners)<div class="public-course__meta-row" style="align-items:flex-start"><span>Intended learners</span><strong style="text-align:right">{{ $course->target_learners }}</strong></div>@endif
                    @if($course->badge?->is_active)<div class="public-course__meta-row" style="align-items:flex-start"><span>Badge</span><strong style="text-align:right">{{ $course->badge->name }}</strong></div>@endif
                    @if($course->certificate_enabled)<div class="public-course__meta-row"><span>Certificate</span><strong>On completion</strong></div>@endif
                </div>
                <div class="public-course__note">Enrollment requires an UpSkill PSU account. You can review the course details first, then sign in or register when you’re ready to start.</div>
            </aside>
        </div>
    </div>
</section>
@endsection
