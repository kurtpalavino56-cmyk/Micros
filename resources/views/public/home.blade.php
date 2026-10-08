@extends('layouts.app')

@section('title', 'UpSkill – The Official Platform for PSU Microcredentials')

@push('styles')
<style>
    :root {
        --landing-royal-blue: #0927d8;
        --landing-navy: #07143f;
        --landing-gold: #f6cb3b;
        --landing-white: #fff;
        --navy: var(--landing-navy);
        --navy-dark: var(--landing-navy);
        --gold: var(--landing-gold);
        --gold-light: var(--landing-gold);
        --gold-bg: var(--landing-gold);
        --white: var(--landing-white);
    }

    .hero {
        position: relative;
        overflow: visible;
        background: var(--landing-white);
        color: var(--landing-navy);
        padding: 5.5rem 0 4.5rem;
    }

    .hero::before,
    .hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .hero::before {
        inset: 0;
        border-radius: 0;
        background: linear-gradient(115deg, rgba(9,39,216,.025) 0%, rgba(9,39,216,.07) 100%);
        z-index: 0;
    }

    .hero::after {
        width: 460px;
        height: 460px;
        background: radial-gradient(circle, rgba(9,39,216,.08) 0%, rgba(9,39,216,0) 62%);
        bottom: -120px;
        left: -120px;
    }

    .hero__inner {
        position: relative;
        z-index: 50;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 2.5rem;
    }

    .container.hero__inner {
        width: min(94%, 1320px);
        max-width: 1320px;
    }

    .hero__eyebrow {
        display: inline-flex;
        align-items: left;
        gap: 0.5rem;
        /* background: var(--gold); */
        color: var(--landing-royal-blue);
        border-radius: 999px;
        padding: 0.58rem 1.2rem;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .hero__title {
        font-family: var(--font-display);
        font-size: clamp(3rem, 6vw, 5.2rem);
        line-height: 0.94;
        letter-spacing: -0.05em;
        color: var(--landing-navy);
        margin: 0 0 1rem;
    }

    .hero__title span {
        color: var(--landing-gold);
    }

    .hero__title .hero__brand-up { color: var(--landing-royal-blue); }
    .hero__title .hero__brand-skill { color: var(--landing-gold); }

    .hero__subtitle {
        max-width: 620px;
        font-size: 1.12rem;
        color: rgba(7,20,63,.78);
        margin-bottom: 2rem;
    }

    .hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2.2rem;
    }

    .hero__cta {
        padding: 0.9rem 1.8rem;
        border-radius: 999px;
        font-size: 0.98rem;
        font-weight: 700;
    }

    .hero__stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        max-width: 700px;
    }

    .hero__stat {
        background: rgba(9,39,216,.05);
        border: 1px solid rgba(9,39,216,.16);
        border-radius: 18px;
        padding: 1.1rem 0.8rem;
        box-shadow: 0 8px 20px rgba(7,20,63,.08);
    }

    .hero__stat-num {
        font-family: var(--font-display);
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
        color: var(--landing-royal-blue);
        line-height: 1;
    }

    .hero__stat-label {
        margin-top: 0.45rem;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(7,20,63,.72);
    }

    .hero__panel {
        position: relative;
        width: 100%;
        max-width: 700px;
        justify-self: end;
        transform: translateX(2rem);
        border-radius: 24px;
    }

    .hero__panel-shell {
        overflow: visible;
    }

    .hero__panel-body {
        position: relative;
    }

    .hero__carousel {
        position: relative;
        overflow: visible;
        aspect-ratio: 4 / 3;
        min-height: 410px;
        border-radius: 24px;
        perspective: 1300px;
        transform-style: preserve-3d;
    }

    .hero__slide {
        position: absolute;
        top: 9px;
        bottom: 9px;
        left: 50%;
        width: min(60%, 420px);
        display: grid;
        grid-template-rows: minmax(150px, 42%) minmax(0, 1fr);
        overflow: hidden;
        border: 1px solid #e3e9f3;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(7,20,63,.16);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateX(-50%) scale(.78);
        transform-origin: center;
        transition: opacity .4s ease, transform .5s cubic-bezier(.2,.75,.25,1), visibility .4s ease;
    }

    .hero__slide.is-active {
        z-index: 3;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(-50%) translateZ(30px) scale(1);
    }

    .hero__slide.is-prev,
    .hero__slide.is-next {
        z-index: 2;
        opacity: .82;
        visibility: visible;
        pointer-events: auto;
        cursor: pointer;
    }

    .hero__slide.is-prev {
        left: 14px;
        transform: translateZ(-34px) rotateY(11deg) rotateZ(-4deg) scale(.88);
        transform-origin: left center;
    }

    .hero__slide.is-next {
        right: 14px;
        left: auto;
        transform: translateZ(-34px) rotateY(-11deg) rotateZ(4deg) scale(.88);
        transform-origin: right center;
    }

    .hero__side-hit {
        position: absolute;
        z-index: 4;
        top: 9px;
        bottom: 9px;
        width: 20%;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .hero__side-hit:focus-visible {
        outline: 2px solid var(--landing-royal-blue);
        outline-offset: -8px;
        border-radius: 18px;
    }

    .hero__side-hit--prev { left: 0; }
    .hero__side-hit--next { right: 0; }

    .hero-course-visual {
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 1.35rem 1.5rem;
        background: linear-gradient(135deg, #0927d8, #07143f);
        background-position: center;
        background-size: cover;
    }

    .hero-course-content {
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 1.2rem 1.5rem 1.35rem;
    }

    .hero-course-tags {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .65rem;
        margin-bottom: .75rem;
        color: var(--landing-royal-blue);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .hero-course-tags span + span {
        color: var(--landing-navy);
    }

    .hero-course-title {
        margin: 0 0 .5rem;
        color: var(--landing-navy);
        font: 700 clamp(1.3rem, 2vw, 1.65rem)/1.2 var(--font-display);
        letter-spacing: -.025em;
    }

    .hero-course-title a {
        color: inherit;
        text-decoration: none;
    }

    .hero-course-title a:hover {
        color: var(--landing-royal-blue);
    }

    .hero-course-description {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        margin: 0;
        color: rgba(7,20,63,.72);
        font-size: .9rem;
        line-height: 1.55;
    }

    .hero-course-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: auto;
        padding-top: 1rem;
    }

    .hero-course-meta {
        overflow: hidden;
        color: #63718d;
        font-size: .78rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hero-course-link {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: .4rem;
        color: var(--landing-royal-blue);
        font-size: .86rem;
        font-weight: 800;
        text-decoration: none;
    }

    .hero-course-link:hover {
        color: var(--landing-navy);
    }

    .hero__carousel-dots {
        display: flex;
        justify-content: center;
        gap: .45rem;
        padding: .9rem 1rem 0;
    }

    .hero__carousel-dot {
        width: .48rem;
        height: .48rem;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: rgba(7,20,63,.22);
        cursor: pointer;
    }

    .hero__carousel-dot.active {
        background: var(--landing-royal-blue);
        box-shadow: 0 0 0 3px rgba(9,39,216,.12);
    }

    .hero-course-empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2rem;
        border: 1px solid #e3e9f3;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(7,20,63,.12);
    }

    .hero-course-empty p {
        max-width: 26rem;
        margin: 0 0 1rem;
        color: #63718d;
        line-height: 1.6;
    }

    .hero-course-empty a {
        color: var(--landing-royal-blue);
        font-weight: 800;
        text-decoration: none;
    }

    .trust-bar {
        background: #fff;
        border-bottom: 1px solid var(--line);
        padding: 1rem 0;
    }

    .trust-bar__wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        color: var(--text-muted);
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .trust-bar__logos {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        color: rgba(10,31,110,0.72);
        letter-spacing: 0.08em;
    }

    .featured {
        position: relative;
        background: rgba(9,39,216,.04);
        padding: 5rem 0 4rem;
    }

    .featured > .container {
        position: relative;
        z-index: 50;
    }

    .featured .courses-grid {
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 360px));
        justify-content: center;
    }

    .hero-divider {
        position: relative;
        z-index: 40;
        width: 100%;
        height: 0;
        line-height: 0;
        margin: 0;
        pointer-events: none;
        text-align: center;
    }

    .hero-divider::before {
        content: "";
        display: block;
        width: 100%;
        height: 78.14vw;
        margin: -31vw 0 0;
        background: url('{{ asset('Images/divider.png') }}') center center / 100% auto no-repeat;
        transform: scaleY(0.7);
        transform-origin: top center;
    }

    .section-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2.25rem;
    }

    .section-title {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 2.7rem);
        color: var(--navy);
        letter-spacing: -0.04em;
        margin: 0;
    }

    .section-tag {
        display: inline-flex;
        align-items: center;
        background: rgba(10,31,110,0.06);
        color: var(--navy);
        border-radius: 999px;
        padding: 0.42rem 0.8rem;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
    }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .courses-grid--single {
        grid-template-columns: minmax(280px, 380px);
    }

    .course-card {
        background: var(--card-bg);
        border: 1px solid rgba(10,31,110,0.08);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 16px 38px rgba(10,31,110,0.06);
        transition: transform var(--transition), box-shadow var(--transition);
        display: flex;
        flex-direction: column;
    }

    .featured .course-card {
        width: 100%;
        max-width: 360px;
    }

    .course-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(10,31,110,0.12);
    }

    .course-card__thumb {
        display: block;
        width: 100%;
        height: 200px;
        background: var(--landing-gold);
        overflow: hidden;
    }

    .course-card__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-card__body {
        padding: 1.2rem 1.1rem 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .course-card__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 0.8rem;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        font-size: 0.68rem;
        line-height: 1;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .tag-gold {
        background: transparent;
        color: var(--landing-navy);
    }

    .tag-teal {
        background: transparent;
        color: var(--landing-royal-blue);
    }

    .course-card__title {
        font-family: var(--font-display);
        color: var(--navy);
        font-weight: 700;
        font-size: 1.14rem;
        line-height: 1.35;
        margin-bottom: 0.5rem;
    }

    .course-card__title a {
        color: inherit;
    }

    .course-card__desc {
        color: var(--text-muted);
        font-size: 0.84rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex: 1;
    }

    .course-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-top: 0.9rem;
        border-top: 1px solid #edf1f8;
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .course-card__rating {
        color: var(--landing-gold);
        font-weight: 700;
    }

    .featured__footer {
        margin-top: 2.3rem;
        text-align: center;
    }

    .why {
        position: relative;
        overflow: hidden;
        padding: 5rem 0;
        background: #fff;
    }

    .why::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('{{ asset('Images/divider 4.png') }}') center calc(100% + 150px) / 100% auto no-repeat;
        pointer-events: none;
    }

    .why > .container {
        position: relative;
        z-index: 1;
    }

    .why__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .why__card {
        background: linear-gradient(180deg, #fff, #f7f9fe);
        border: 1px solid var(--line);
        border-radius: 22px;
        box-shadow: 0 18px 30px rgba(10,31,110,0.04);
        padding: 1.5rem;
    }

    .why__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(9,39,216,.08);
        color: var(--navy);
        display: grid;
        place-items: center;
        margin-bottom: 1rem;
        font-size: 1.4rem;
        font-weight: 800;
    }

    .why__card h3 {
        font-family: var(--font-display);
        font-size: 1.1rem;
        color: var(--navy);
        margin-bottom: 0.55rem;
    }

    .why__card p {
        color: var(--text-muted);
        line-height: 1.65;
        font-size: 0.9rem;
    }

    .how-it-works {
        position: relative;
        background: var(--navy) url('{{ asset('images/Green_field_PSU.jpg') }}') center/cover no-repeat;
        padding: 5rem 0;
    }

    .how-it-works::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(7,21,80,0.88), rgba(10,31,110,0.82));
    }

    .how-it-works > .container {
        position: relative;
        z-index: 1;
    }

    .how-it-works .section-title {
        color: #fff;
        margin-bottom: 2.25rem;
        text-align: center;
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .step-card {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 24px;
        padding: 2rem 1.5rem;
        text-align: center;
        backdrop-filter: blur(4px);
    }

    .step-card__num {
        width: 62px;
        height: 62px;
        border: 2px solid var(--landing-gold);
        border-radius: 50%;
        display: grid;
        place-items: center;
        margin: 0 auto 1.2rem;
        font-family: var(--font-display);
        font-size: 1.5rem;
        color: #fff;
        font-weight: 800;
    }

    .step-card__title {
        font-family: var(--font-display);
        color: #fff;
        font-size: 1.1rem;
        margin-bottom: 0.55rem;
    }

    .step-card__desc {
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        line-height: 1.7;
    }

    .announcements {
        background: #fff;
        padding: 5rem 0;
    }

    .announcements-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ann-card {
        position: relative;
        display: flex;
        gap: 1rem;
        border: 1px solid #edf1f7;
        border-left: 0;
        background: #f9fbff;
        border-radius: 0 18px 18px 0;
        padding: 1.2rem 1.3rem;
        transition: transform var(--transition), box-shadow var(--transition);
    }

    .ann-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--announcement-accent, var(--navy));
        border-radius: 0 6px 6px 0;
    }

    .ann-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(10,31,110,0.06);
    }

    .ann-card--event { --announcement-accent: var(--gold); }
    .ann-card--urgent { --announcement-accent: #cc3b3b; }
    .ann-card--general { --announcement-accent: var(--landing-royal-blue); }

    .ann-card__body {
        flex: 1;
    }

    .ann-card__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.3rem;
    }

    .ann-card__type {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.6rem;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: rgba(10,31,110,0.08);
        color: var(--navy);
    }

    .ann-card--event .ann-card__type { background: rgba(246,203,59,.2); color: var(--landing-navy); }
    .ann-card--urgent .ann-card__type { background: rgba(204,59,59,0.1); color: #8a1f1f; }
    .ann-card--general .ann-card__type { background: rgba(9,39,216,.1); color: var(--landing-royal-blue); }

    .ann-card__date {
        color: var(--text-muted);
        font-size: 0.74rem;
        margin-left: auto;
    }

    .ann-card__title {
        font-family: var(--font-display);
        font-size: 1rem;
        color: var(--navy);
        margin-bottom: 0.25rem;
    }

    .ann-card__desc {
        color: var(--text-muted);
        font-size: 0.86rem;
        line-height: 1.6;
    }

    .ann-card__desc .full-text {
        white-space: pre-wrap;
    }

    .ann-card__more {
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--navy);
        cursor: pointer;
        font: 700 0.82rem var(--font-body);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .latest {
        background: var(--landing-white);
        padding: 5rem 0;
    }

    .latest .section-title {
        color: var(--navy-dark);
    }

    .latest .section-tag {
        background: rgba(10,31,110,0.08);
        color: var(--navy-dark);
    }

    .cta-banner {
        position: relative;
        padding: 5rem 0;
        text-align: center;
        background: var(--landing-gold) url('{{ asset('images/PSU_Front_Building.jpg') }}') center/cover no-repeat;
    }

    .cta-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(246,203,59,.92), rgba(246,203,59,.82));
    }

    .cta-banner > .container {
        position: relative;
        z-index: 1;
    }

    .cta-banner__title {
        font-family: var(--font-display);
        font-size: clamp(2.2rem, 5vw, 3.4rem);
        color: var(--navy-dark);
        margin-bottom: 0.75rem;
        letter-spacing: -0.04em;
    }

    .cta-banner__sub {
        font-size: 1.08rem;
        color: rgba(10,31,110,0.78);
        margin-bottom: 2rem;
    }

    @media (max-width: 980px) {
        .hero__inner {
            grid-template-columns: 1fr;
        }

        .hero__panel {
            justify-self: center;
            transform: none;
        }
    }

    @media (max-width: 768px) {
        .hero__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero__panel {
            max-width: 620px;
        }

        .trust-bar__wrap {
            justify-content: center;
        }

        .ann-card {
            border-radius: 18px;
        }

        .ann-card__date {
            margin-left: 0;
        }
    }
</style>
@endpush

@section('content')
@php
    $featuredCourses = $featuredCourses ?? [];
    $heroCourses = $featuredCourses;
@endphp
<section class="hero">
    <div class="container hero__inner">
        <div>
            <div class="hero__eyebrow">The Official Platform for PSU Microcredential</div>
            <h1 class="hero__title">Learn smarter. <span><span class="hero__brand-up">UP</span><span class="hero__brand-skill">Skill</span></span> faster.</h1>
            <p class="hero__subtitle">Explore practical microcredentials designed for students, people, and professionals who want to build real-world skills at PSU.</p>

            <div class="hero__actions">
                <a href="{{ url('/register') }}" class="btn btn-gold hero__cta">Get Started</a>
                <a href="#announcements" class="btn hero__cta" style="background: rgba(9,39,216,.05); border: 1px solid rgba(9,39,216,.2); color: var(--landing-navy);" onclick="smoothScrollTo('announcements', event)">Announcements</a>
            </div>

            <div class="hero__stats">
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="{{ $stats['courses'] ?? 0 }}">0</div>
                    <div class="hero__stat-label">Courses</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="{{ $stats['learners'] ?? 0 }}">0</div>
                    <div class="hero__stat-label">Learners</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="{{ $stats['certificates'] ?? 0 }}">0</div>
                    <div class="hero__stat-label">Certificates</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="{{ $stats['badges'] ?? 0 }}">0</div>
                    <div class="hero__stat-label">Badges</div>
                </div>
            </div>
        </div>

        <div class="hero__panel" aria-label="Course highlights">
            <div class="hero__panel-shell">
                <div class="hero__panel-body">
                    <div class="hero__carousel" data-carousel role="region" aria-label="Course highlights" aria-roledescription="carousel">
                        @forelse ($heroCourses as $course)
                            <article class="hero__slide {{ $loop->first ? 'is-active' : '' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                <div class="hero-course-visual" @if(!empty($course['image'])) style="background-image: url('{{ $course['image'] }}')" @endif></div>
                                <div class="hero-course-content">
                                    <div class="hero-course-tags">
                                        <span>{{ $course['category'] ?? 'Microcredential' }}</span>
                                        @if(!empty($course['level']))<span>{{ $course['level'] }}</span>@endif
                                    </div>
                                    <h2 class="hero-course-title"><a href="{{ $course['slug'] ?? route('explore') }}">{{ $course['title'] ?? 'Explore our courses' }}</a></h2>
                                    <p class="hero-course-description">{{ $course['description'] ?? 'Build practical skills with flexible microcredentials from PSU.' }}</p>
                                    <div class="hero-course-footer">
                                        <span class="hero-course-meta">{{ $course['professor'] ?? 'PSU Faculty' }}@if(!empty($course['hours'])) · {{ $course['hours'] }} {{ (int) $course['hours'] === 1 ? 'hour' : 'hours' }}@endif</span>
                                        <a class="hero-course-link" href="{{ $course['slug'] ?? route('explore') }}">View course <span aria-hidden="true">→</span></a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="hero-course-empty">
                                <div class="hero-course-tags"><span>PSU Microcredentials</span></div>
                                <h2 class="hero-course-title">Find your next skill</h2>
                                <p>Explore practical courses designed to help you build skills for what comes next.</p>
                                <a class="hero-course-link" href="{{ route('explore') }}">Browse all courses <span aria-hidden="true">→</span></a>
                            </div>
                        @endforelse
                        @if(count($heroCourses) >= 3)
                            <button class="hero__side-hit hero__side-hit--prev" type="button" aria-label="Show previous featured course"></button>
                            <button class="hero__side-hit hero__side-hit--next" type="button" aria-label="Show next featured course"></button>
                        @endif
                    </div>
                    @if(count($heroCourses) > 1)
                        <div class="hero__carousel-dots" role="group" aria-label="Choose a featured course">
                            @foreach($heroCourses as $course)
                                <button class="hero__carousel-dot {{ $loop->first ? 'active' : '' }}" type="button" aria-label="Show course {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<div class="hero-divider" aria-hidden="true"></div>

<section class="featured" id="featured">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured programs</h2>
            <span class="section-tag">Popular picks</span>
        </div>

        <div class="courses-grid {{ count($featuredCourses) === 1 ? 'courses-grid--single' : '' }}">
            @forelse($featuredCourses as $course)
                @include('components.course-card', ['course' => $course])
            @empty
                <p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:2rem 0 0;">No featured courses right now — check out the latest courses below.</p>
            @endforelse
        </div>

        <div class="featured__footer">
            <a href="{{ url('/explore') }}" class="btn btn-gold">View all courses</a>
        </div>
    </div>
</section>

<section class="why">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why choose UpSkill?</h2>
            <span class="section-tag">Built for outcomes</span>
        </div>

        <div class="why__grid">
            <article class="why__card">
                <div class="why__icon">01</div>
                <h3>Career-ready learning</h3>
                <p>Access competency-based courses designed to build practical, job-relevant skills for today’s digital workplace.</p>
            </article>

            <article class="why__card">
                <div class="why__icon">02</div>
                <h3>Flexible study paths</h3>
                <p>Learn at your own pace with modular lessons, guided progress, and clear milestones from start to certification.</p>
            </article>

            <article class="why__card">
                <div class="why__icon">03</div>
                <h3>Recognized credentials</h3>
                <p>Earn badges and certificates that demonstrate achievement and help you stand out in academic and professional settings.</p>
            </article>
        </div>
    </div>
</section>

<section class="how-it-works" id="how-it-works">
    <div class="container">
        <h2 class="section-title">How it works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-card__num">1</div>
                <h3 class="step-card__title">Browse courses</h3>
                <p class="step-card__desc">Choose from curated microcredentials aligned with faculty expertise and student interests.</p>
            </div>
            <div class="step-card">
                <div class="step-card__num">2</div>
                <h3 class="step-card__title">Learn and apply</h3>
                <p class="step-card__desc">Complete lessons, exercises, and assessments using a guided learning workflow.</p>
            </div>
            <div class="step-card">
                <div class="step-card__num">3</div>
                <h3 class="step-card__title">Earn recognition</h3>
                <p class="step-card__desc">Unlock badges, certificates, and meaningful proof of skill growth for your next step.</p>
            </div>
        </div>
    </div>
</section>

<section class="announcements" id="announcements">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Announcements</h2>
            <span class="section-tag">Latest announcements</span>
        </div>

        @php $announcements = $announcements ?? []; @endphp
        <div class="announcements-list" id="announcements-list">
            @forelse($announcements as $ann)
                <div class="ann-card ann-card--{{ $ann['type'] }}">
                    <div class="ann-card__body">
                        <div class="ann-card__meta">
                            <span class="ann-card__type">{{ $ann['label'] }}</span>
                            <span class="ann-card__date">{{ $ann['date'] }}</span>
                        </div>
                        <div class="ann-card__title">{{ $ann['title'] }}</div>
                        <p class="ann-card__desc">
                            <span class="preview-text">{{ $ann['desc'] }}</span>
                            <span class="full-text" hidden>{{ $ann['full_body'] }}</span>
                        </p>
                        @if ($ann['has_more'])
                            <button class="ann-card__more" type="button" aria-expanded="false">More</button>
                        @endif
                    </div>
                </div>
            @empty
                <p style="color:var(--muted);padding:18px 4px;">No announcements have been posted yet.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="latest" id="latest-courses">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Latest courses</h2>
            <span class="section-tag">Freshly added</span>
        </div>

        <div class="courses-grid">
            @php $latestCourses = $latestCourses ?? []; @endphp
            @foreach($latestCourses as $course)
                @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2 class="cta-banner__title">Ready to grow with PSU?</h2>
        <p class="cta-banner__sub">Join the UpSkill community and build the next step in your learning journey.</p>
        <a href="{{ url('/register') }}" class="btn btn-navy">Create free account</a>
    </div>
</section>

@push('scripts')
<script>
    function smoothScrollTo(id, e) {
        if (e) e.preventDefault();
        const target = document.getElementById(id);
        if (!target) return;
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ann-card__more').forEach(function (button) {
            button.addEventListener('click', function () {
                const card = button.closest('.ann-card');
                const previewText = card.querySelector('.preview-text');
                const fullText = card.querySelector('.full-text');
                const isExpanded = button.getAttribute('aria-expanded') === 'true';

                previewText.hidden = !isExpanded;
                fullText.hidden = isExpanded;
                button.setAttribute('aria-expanded', String(!isExpanded));
                button.textContent = isExpanded ? 'More' : 'Less';
                card.classList.toggle('is-expanded', !isExpanded);
            });
        });

        document.querySelectorAll('a[href="#announcements"], a[href*="announcements"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const target = document.getElementById('announcements');
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.replaceState({}, '', '#announcements');
                    window.dispatchEvent(new Event('hashchange'));
                }
            });
        });

        document.querySelectorAll('a[href="#featured"], a[href*="microcredentials"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const target = document.querySelector('.featured');
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.replaceState({}, '', '#featured');
                    window.dispatchEvent(new Event('hashchange'));
                }
            });
        });
    });

    (function () {
        const nums = document.querySelectorAll('.hero__stat-num[data-count]');
        if (!nums.length) return;

        const animate = (el) => {
            const target = parseInt(el.dataset.count, 10) || 0;
            const dur = 1200;
            const start = performance.now();

            function tick(now) {
                const p = Math.min(1, (now - start) / dur);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + (p === 1 && target > 0 ? '+' : '');
                if (p < 1) requestAnimationFrame(tick);
            }

            requestAnimationFrame(tick);
        };

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.45 });

        nums.forEach((n) => io.observe(n));
    })();

    (function () {
        const carousel = document.querySelector('[data-carousel]');
        if (!carousel) return;

        const slides = carousel.querySelectorAll('.hero__slide');
        const dots = carousel.parentElement.querySelectorAll('.hero__carousel-dot');
        const previous = carousel.querySelector('.hero__side-hit--prev');
        const next = carousel.querySelector('.hero__side-hit--next');
        let current = 0;

        function showSlide(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const isActive = slideIndex === current;
                const position = (slideIndex - current + slides.length) % slides.length;
                const hasSideCards = slides.length >= 2;
                const isNext = hasSideCards && position === 1;
                const isPrevious = hasSideCards && position === slides.length - 1;
                slide.classList.toggle('is-active', isActive);
                slide.classList.toggle('is-prev', isPrevious);
                slide.classList.toggle('is-next', isNext);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                slide.querySelectorAll('a, button, [tabindex]').forEach((element) => {
                    element.tabIndex = isActive ? 0 : -1;
                });
            });
            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === current;
                dot.classList.toggle('active', isActive);
                dot.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }

        if (slides.length === 0) return;
        showSlide(0);
        if (slides.length < 2) return;

        carousel.addEventListener('click', (event) => {
            const selectedSlide = event.target.closest('.hero__slide');
            if (!selectedSlide) return;

            const selectedIndex = Array.from(slides).indexOf(selectedSlide);
            if (selectedIndex < 0 || selectedIndex === current) return;

            event.preventDefault();
            showSlide(selectedIndex);
        }, true);
        previous?.addEventListener('click', () => showSlide(current - 1));
        next?.addEventListener('click', () => showSlide(current + 1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));
    })();
</script>
@endpush

{{-- ═══════════ Scanned certificate (from a QR code) ═══════════
     A QR encodes /?certificate=SERIAL, so scanning lands on the real
     homepage with the certificate floating over it. Unknown serials pass
     through silently and the homepage renders as normal. --}}
@if (!empty($scannedCertificate))
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script:wght@600&family=Parisienne&family=Sacramento&family=Homemade+Apple&display=swap" rel="stylesheet">
<div class="certfloat-overlay" id="certFloat" onclick="if(event.target===this)closeCertFloat()">
    <div class="certfloat">
        <button type="button" class="certfloat__close" onclick="closeCertFloat()" aria-label="Close">&times;</button>

        @if(($scannedCertificate['status'] ?? 'active') === 'revoked')
            <div class="certfloat__verified" style="background:#fff1f2;color:#b42318" role="status">Revoked Certificate</div>
        @else
            <div class="certfloat__verified">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Verified Certificate
            </div>
        @endif

        @include('components.certificate', ['cert' => $scannedCertificate])

        <button type="button" class="certfloat__btn" onclick="closeCertFloat()">Continue to UPSKILL</button>
    </div>
</div>

<style>
.certfloat-overlay{position:fixed;inset:0;z-index:4000;background:rgba(6,13,46,.72);
    backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;
    padding:24px 16px;overflow-y:auto;animation:certfade .25s ease;}
@keyframes certfade{from{opacity:0}to{opacity:1}}
.certfloat{position:relative;width:100%;max-width:720px;background:#fff;border-radius:18px;
    padding:22px;box-shadow:0 30px 80px rgba(0,0,0,.5);animation:certrise .3s cubic-bezier(.34,1.4,.5,1);}
@keyframes certrise{from{transform:translateY(24px) scale(.96);opacity:0}to{transform:none;opacity:1}}
.certfloat__close{position:absolute;top:12px;right:14px;background:transparent;border:none;
    font-size:26px;line-height:1;color:#9ca3af;cursor:pointer;}
.certfloat__close:hover{color:var(--landing-royal-blue);}
.certfloat__verified{display:inline-flex;align-items:center;gap:7px;background:#ecfdf3;
    border:1px solid #a7f3d0;color:#065f46;border-radius:999px;padding:6px 14px;
    font-size:12.5px;font-weight:800;margin-bottom:16px;}
.certfloat__verified svg{width:14px;height:14px;}
.certfloat__btn{width:100%;margin-top:18px;background:var(--landing-navy);color:var(--landing-white);border:none;
    border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;}
.certfloat__btn:hover{background:var(--landing-royal-blue);}
@media(max-width:520px){
    .certfloat{padding:14px;}
}
</style>
<script>
function closeCertFloat(){
    var el = document.getElementById('certFloat');
    if (el) el.remove();
    if (window.history.replaceState) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeCertFloat(); });
</script>
@endif

@endsection
