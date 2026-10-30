{{--
    resources/views/terms.blade.php

    Public Terms and Conditions, linked from the footer.

    Extends layouts.app so it carries the same navbar and footer as the
    homepage. The .legal styles are duplicated from privacy.blade.php so
    each page stands alone — keep the two in step if you change one.
--}}
@extends('layouts.app')

@section('title', 'Terms and Conditions – UpSkill PSU')

@push('styles')
<style>
    .legal { padding: 3.5rem 0 4.5rem; background: #f4f6fb; }

    .legal__card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        padding: 2.6rem 2.8rem 3rem;
        max-width: 880px;
        margin: 0 auto;
    }

    .legal__title {
        font-family: var(--font-display);
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        font-weight: 800;
        color: var(--navy);
        line-height: 1.25;
        margin-bottom: 0.9rem;
    }

    .legal__dates {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.5rem;
        padding-bottom: 1.4rem;
        margin-bottom: 1.8rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .legal__dates strong { color: var(--navy); font-weight: 700; }

    .legal h2 {
        font-family: var(--font-display);
        font-size: 1.12rem;
        font-weight: 800;
        color: var(--navy);
        margin: 2rem 0 0.7rem;
    }
    .legal p {
        font-size: 0.93rem;
        line-height: 1.75;
        color: #3f4a63;
        margin-bottom: 0.9rem;
    }
    .legal ul {
        margin: 0 0 1.1rem 1.15rem;
        padding: 0;
    }
    .legal li {
        font-size: 0.93rem;
        line-height: 1.7;
        color: #3f4a63;
        margin-bottom: 0.38rem;
        padding-left: 0.2rem;
    }
    .legal li::marker { color: var(--gold); }

    .legal__note {
        background: #f7f9ff;
        border: 1px solid #dfe4fb;
        border-left: 4px solid var(--navy);
        border-radius: 10px;
        padding: 1rem 1.15rem;
        margin: 1.6rem 0;
    }
    .legal__note p { margin: 0; font-size: 0.9rem; }

    .legal__contact {
        background: #f7f9ff;
        border: 1px solid #dfe4fb;
        border-radius: 12px;
        padding: 1.2rem 1.4rem;
        margin: 1.2rem 0 0;
    }
    .legal__contact p { margin: 0 0 0.3rem; font-size: 0.9rem; }
    .legal__contact strong { color: var(--navy); }


    @media (max-width: 640px) {
        .legal { padding: 2.2rem 0 3rem; }
        .legal__card { padding: 1.6rem 1.3rem 2rem; }
    }
</style>
@endpush

@section('content')
<section class="legal">
    <div class="container">
        <div class="legal__card">

            <h1 class="legal__title">Terms and Conditions<br>UPSKILL: Micro-Credentials</h1>

            {{-- Shared with the sign-up agreement pop-up: edit the wording there. --}}
            @include('public.partials.terms-content')


        </div>
    </div>
</section>
@endsection
