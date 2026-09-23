@extends('layouts.app')

@section('title', ($course->heading ?: $course->title).' – UpSkill PSU')

@push('styles')
<style>
    .public-course{padding:3rem 0 5rem;background:#f5f8fd;min-height:70vh}
    .public-course__hero{background:linear-gradient(115deg,#08245f,#174d96);color:#fff;border-radius:22px;padding:2.4rem;display:grid;grid-template-columns:minmax(0,1.4fr) minmax(240px,.6fr);gap:2rem;overflow:hidden;box-shadow:0 16px 36px rgba(8,36,95,.16)}
    .public-course__eyebrow{color:#f7d889;font-size:.76rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin:0 0 .7rem}
    .public-course h1{font-size:clamp(2rem,4vw,3.1rem);line-height:1.08;margin:0 0 .7rem;color:#fff}
    .public-course__sub{font-size:1.05rem;color:rgba(255,255,255,.8);margin:0 0 1rem;max-width:680px}
    .public-course__desc{font-size:.95rem;line-height:1.7;color:rgba(255,255,255,.78);max-width:720px}
    .public-course__thumb{min-height:210px;border-radius:16px;background:linear-gradient(135deg,rgba(255,255,255,.14),rgba(255,255,255,.03)),url('{{ $course->thumbnail_url ? asset($course->thumbnail_url) : asset('Images/PSU_Front_Building.jpg') }}') center/cover}
    .public-course__actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.4rem}
    .public-course__button{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:.8rem 1.2rem;font-weight:800;background:#e5b43c;color:#08245f}
    .public-course__button--secondary{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.35);color:#fff}
    .public-course__grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(260px,.65fr);gap:1.25rem;margin-top:1.25rem}
    .public-course__panel{background:#fff;border:1px solid #e2e9f4;border-radius:16px;padding:1.4rem;box-shadow:0 10px 26px rgba(8,36,95,.06)}
    .public-course__panel h2{margin:0 0 1rem;color:#08245f;font-size:1.2rem}
    .public-course__lesson{display:flex;align-items:center;gap:.75rem;padding:.8rem 0;border-bottom:1px solid #edf1f7;color:#536583}
    .public-course__lesson:last-child{border-bottom:0}
    .public-course__lesson-icon{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:#eef5ff;color:#2f68ba;font-size:.8rem;font-weight:800;flex:0 0 auto}
    .public-course__meta{display:grid;gap:.75rem}
    .public-course__meta-row{display:flex;justify-content:space-between;gap:1rem;color:#6e7e9b;font-size:.9rem}
    .public-course__meta-row strong{color:#14254b;text-align:right}
    .public-course__note{margin-top:1rem;padding:.9rem 1rem;border-radius:12px;background:#fff5d8;color:#765b10;font-size:.85rem;line-height:1.5}
    @media(max-width:780px){.public-course__hero,.public-course__grid{grid-template-columns:1fr}.public-course__thumb{min-height:170px;order:-1}.public-course{padding:1.5rem 0 3rem}.public-course__hero{padding:1.5rem}}
</style>
@endpush

@section('content')
<section class="public-course">
    <div class="container">
        <div class="public-course__hero">
            <div>
                <p class="public-course__eyebrow">{{ $course->category ?: 'Microcredential' }} · {{ $course->level ?: 'Beginner' }}</p>
                <h1>{{ $course->heading ?: $course->title }}</h1>
                @if($course->subheading)<p class="public-course__sub">{{ $course->subheading }}</p>@endif
                <p class="public-course__desc">{!! $course->description ?: 'Explore this microcredential and discover the skills you can build with UpSkill PSU.' !!}</p>
                <div class="public-course__actions">
                    <a class="public-course__button" href="{{ $loginUrl }}">Sign in to enroll →</a>
                    <a class="public-course__button public-course__button--secondary" href="{{ route('register') }}">Create an account</a>
                </div>
            </div>
            <div class="public-course__thumb" role="img" aria-label="{{ $course->title }} course image"></div>
        </div>

        <div class="public-course__grid">
            <article class="public-course__panel">
                <h2>What you’ll learn</h2>
                @forelse($course->lessons as $lesson)
                    <div class="public-course__lesson"><span class="public-course__lesson-icon">{{ $loop->iteration }}</span><span><strong>{{ $lesson->title }}</strong><br><small>{{ $lesson->type ?: 'Lesson' }}@if($lesson->duration) · {{ $lesson->duration }}@endif</small></span></div>
                @empty
                    <p>No lesson outline has been published yet. Sign in to enroll and follow the course updates.</p>
                @endforelse
            </article>
            <aside class="public-course__panel">
                <h2>Course details</h2>
                <div class="public-course__meta">
                    <div class="public-course__meta-row"><span>Instructor</span><strong>{{ $course->instructor ?: ($course->creator->name ?? 'PSU Faculty') }}</strong></div>
                    <div class="public-course__meta-row"><span>Level</span><strong>{{ $course->level ?: 'Beginner' }}</strong></div>
                    <div class="public-course__meta-row"><span>Duration</span><strong>{{ $course->duration ?: 'Self-paced' }}</strong></div>
                    <div class="public-course__meta-row"><span>Lessons</span><strong>{{ $course->lessons_count ?: $course->lessons->count() }}</strong></div>
                </div>
                <div class="public-course__note">Enrollment requires an UpSkill PSU account. You can review the course details first, then sign in or register when you’re ready to start.</div>
            </aside>
        </div>
    </div>
</section>
@endsection
