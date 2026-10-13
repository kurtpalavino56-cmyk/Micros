@extends('layouts.app')

@section('title', 'Announcements – UpSkill PSU')

@push('styles')
<style>
    .announcement-directory { flex: 1 0 auto; padding: 3.5rem 0 4.5rem; background: #f4f6fb; }
    .announcement-directory__head { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; }
    .announcement-directory__title { color: var(--navy); font: 800 clamp(1.8rem, 4vw, 2.5rem)/1.2 var(--font-display); }
    .announcement-directory__list { display: flex; flex-direction: column; gap: .85rem; }
    .announcement-directory .ann-card { position: relative; display: flex; align-items: center; gap: 1.25rem; min-height: 120px; border: 1px solid #e3e8f3; border-radius: 14px; padding: 1rem 1.25rem; background: #fff; box-shadow: 0 8px 22px rgba(10,31,110,.05); cursor: pointer; transition: transform var(--transition), box-shadow var(--transition); }
    .ann-card:hover { transform: translateY(-3px); box-shadow: 0 16px 32px rgba(10,31,110,.12); }
    .announcement-directory .ann-card__body { display: flex; min-width: 0; flex: 1; flex-direction: column; }
    .announcement-directory .ann-card__meta { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; margin-bottom: .45rem; }
    .announcement-directory .ann-card__pin { position: absolute; top: 1rem; right: 1rem; display: inline-flex; width: 22px; height: 22px; color: #dc2626; }
    .announcement-directory .ann-card__pin svg { width: 100%; height: 100%; }
    .announcement-directory .ann-card__date { margin-left: 0; color: var(--text-muted); font-size: .75rem; }
    .announcement-directory .ann-card__sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .announcement-directory .ann-card__title { margin-bottom: .2rem; color: var(--navy); font: 800 1rem/1.35 var(--font-display); }
    .announcement-directory .ann-card__desc { color: var(--text-muted); font-size: .84rem; line-height: 1.5; }
    .announcement-directory .ann-card__open-hint { display: inline-flex; align-items: center; gap: .35rem; margin-left: auto; color: #0022ee; font-size: .82rem; font-weight: 700; white-space: nowrap; }
    .announcement-directory .ann-card__open-hint svg { width: 15px; height: 15px; }
    .announcement-directory__empty { grid-column: 1 / -1; padding: 2rem; border: 1px solid #e3e8f3; border-radius: 16px; background: #fff; color: var(--text-muted); text-align: center; }
    @media(max-width: 600px) {
        .announcement-directory .ann-card { align-items: flex-start; flex-wrap: wrap; gap: .7rem; }
        .announcement-directory .ann-card__open-hint { margin-left: auto; }
    }
</style>
@endpush

@section('content')
<section class="announcement-directory">
    <div class="container">
        <header class="announcement-directory__head">
            <h1 class="announcement-directory__title">All Announcements</h1>
        </header>

        <div class="announcement-directory__list">
            @forelse ($announcements as $ann)
                @include('public.partials.announcement-card', ['ann' => $ann])
            @empty
                <p class="announcement-directory__empty">No announcements have been posted yet.</p>
            @endforelse
        </div>
    </div>
</section>
@include('public.partials.announcement-modal')
@endsection
