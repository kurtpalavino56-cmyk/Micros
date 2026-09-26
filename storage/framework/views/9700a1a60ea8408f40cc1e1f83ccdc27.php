<?php $__env->startSection('title', 'UpSkill – The Official Platform for PSU Microcredentials'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .hero {
        position: relative;
        overflow: visible;
        background: #1235c7;
        color: #fff;
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
        background: linear-gradient(90deg, rgba(18, 53, 199, 0.18), rgba(18, 53, 199, 0.04)), url('<?php echo e(asset('Images/legacy-building.jpg')); ?>') center / cover no-repeat;
        -webkit-mask-image: linear-gradient(90deg, transparent 0%, transparent 28%, rgba(0, 0, 0, 0.55) 52%, #000 78%);
        mask-image: linear-gradient(90deg, transparent 0%, transparent 28%, rgba(0, 0, 0, 0.55) 52%, #000 78%);
        z-index: 0;
    }

    .hero::after {
        width: 460px;
        height: 460px;
        background: radial-gradient(circle, rgba(127, 233, 227, 0.14) 0%, rgba(127, 233, 227, 0) 62%);
        bottom: -120px;
        left: -120px;
    }

    .hero__inner {
        position: relative;
        z-index: 50;
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        align-items: center;
        gap: 2.5rem;
    }

    .hero__eyebrow {
        display: inline-flex;
        align-items: left;
        gap: 0.5rem;
        /* background: var(--gold); */
        color: var(--gold);
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
        color: #fff;
        margin: 0 0 1rem;
    }

    .hero__title span {
        color: #FFD501;
    }

    .hero__subtitle {
        max-width: 620px;
        font-size: 1.12rem;
        color: rgba(255,255,255,0.78);
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
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 18px;
        padding: 1.1rem 0.8rem;
        backdrop-filter: blur(10px);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
    }

    .hero__stat-num {
        font-family: var(--font-display);
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .hero__stat-label {
        margin-top: 0.45rem;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255,255,255,0.7);
    }

    .hero__panel {
        position: relative;
        justify-self: end;
        transform: translateX(1.5rem);
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 28px;
        padding: 1.25rem;
        backdrop-filter: blur(10px);
        box-shadow: 0 30px 80px rgba(5, 14, 46, 0.32);
    }

    .hero__panel-shell {
        background: linear-gradient(180deg, rgba(10,31,110, 0.82), rgba(11,20,73,0.92));
        border: 1px solid rgba(255,255,255, 0.08);
        border-radius: 22px;
        overflow: hidden;
    }

    .hero__panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1rem 0.8rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        background: rgba(12,20,60,0.8);
    }

    .hero__panel-title {
        font-size: 0.72rem;
        color: rgba(255,255,255,0.7);
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .hero__panel-dot {
        display: inline-flex;
        gap: 0.38rem;
    }

    .hero__panel-dot span {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: block;
        background: rgba(255,255,255,0.42);
    }

    .hero__panel-body {
        padding: 0;
        position: relative;
    }

    .hero__carousel {
        position: relative;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        min-height: 330px;
        background: #08164f;
    }

    .hero__slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.55s ease, visibility 0.55s ease;
    }

    .hero__slide.active {
        opacity: 1;
        visibility: visible;
    }

    .hero__slide img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .hero__slide::after {
        content: "";
        position: absolute;
        inset: 35% 0 0;
        background: linear-gradient(180deg, transparent, rgba(4, 12, 45, 0.88));
    }

    .hero__slide-caption {
        position: absolute;
        z-index: 1;
        right: 1.2rem;
        bottom: 1.1rem;
        left: 1.2rem;
        color: #fff;
    }

    .hero__slide-caption small {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--gold-light);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .hero__slide-caption strong {
        font-size: 1.2rem;
    }

    .hero__carousel-control {
        position: absolute;
        z-index: 2;
        top: 50%;
        width: auto;
        height: auto;
        padding: 0.2rem;
        border: 0;
        background: transparent;
        color: #fff;
        cursor: pointer;
        font-size: 2.4rem;
        line-height: 1;
        opacity: 0;
        transform: translateY(-50%);
        transition: color var(--transition), opacity var(--transition);
    }

    .hero__carousel:hover .hero__carousel-control,
    .hero__carousel-control:focus-visible {
        opacity: 1;
    }

    .hero__carousel-control:hover {
        color: var(--gold);
    }

    .hero__carousel-control--prev { left: 0.9rem; }
    .hero__carousel-control--next { right: 0.9rem; }

    .hero__carousel-dots {
        position: absolute;
        z-index: 2;
        right: 1.2rem;
        bottom: 1.25rem;
        display: flex;
        gap: 0.4rem;
    }

    .hero__carousel-dot {
        width: 0.48rem;
        height: 0.48rem;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.55);
        cursor: pointer;
    }

    .hero__carousel-dot.active {
        background: var(--gold);
        box-shadow: 0 0 0 3px rgba(245,197,24,0.22);
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
        background: #f4f7ff;
        padding: 5rem 0 4rem;
    }

    .featured > .container {
        position: relative;
        z-index: 50;
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
        background: url('<?php echo e(asset('Images/divider.png')); ?>') center center / 100% auto no-repeat;
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

    .course-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(10,31,110,0.12);
    }

    .course-card__thumb {
        display: block;
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f5c518, #f7d25e);
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
        background: rgba(245,197,24,0.16);
        color: #8a6900;
    }

    .tag-teal {
        background: rgba(127,233,227,0.18);
        color: #065b63;
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
        color: #c58d00;
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
        background: url('<?php echo e(asset('Images/divider 4.png')); ?>') center calc(100% + 150px) / 100% auto no-repeat;
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
        background: rgba(10,31,110,0.08);
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
        background: var(--navy) url('<?php echo e(asset('images/Green_field_PSU.jpg')); ?>') center/cover no-repeat;
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
        border: 2px solid rgba(255,255,255,0.34);
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
        display: flex;
        gap: 1rem;
        border: 1px solid #edf1f7;
        border-left: 5px solid var(--navy);
        background: #f9fbff;
        border-radius: 0 18px 18px 0;
        padding: 1.2rem 1.3rem;
        transition: transform var(--transition), box-shadow var(--transition);
    }

    .ann-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(10,31,110,0.06);
    }

    .ann-card--event { border-left-color: var(--gold); }
    .ann-card--urgent { border-left-color: #cc3b3b; }
    .ann-card--general { border-left-color: #2e8b6d; }

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

    .ann-card--event .ann-card__type { background: rgba(245,197,24,0.14); color: #8a6900; }
    .ann-card--urgent .ann-card__type { background: rgba(204,59,59,0.1); color: #8a1f1f; }
    .ann-card--general .ann-card__type { background: rgba(46,139,109,0.12); color: #165a42; }

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

    .latest {
        background: linear-gradient(180deg, #f5c518 0%, #f2b400 100%);
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
        background: var(--gold-bg) url('<?php echo e(asset('images/PSU_Front_Building.jpg')); ?>') center/cover no-repeat;
    }

    .cta-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(245,194,0,0.9), rgba(212,148,10,0.88));
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
            justify-self: stretch;
            transform: none;
        }
    }

    @media (max-width: 768px) {
        .hero__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<section class="hero">
    <div class="container hero__inner">
        <div>
            <div class="hero__eyebrow">The Official Platform for PSU Microcredential</div>
            <h1 class="hero__title">Learn smarter. <span>UpSkill</span> faster.</h1>
            <p class="hero__subtitle">Explore practical microcredentials designed for students, people, and professionals who want to build real-world skills at PSU.</p>

            <div class="hero__actions">
                <a href="<?php echo e(url('/register')); ?>" class="btn btn-gold hero__cta">Get Started</a>
                <a href="#announcements" class="btn hero__cta" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff;" onclick="smoothScrollTo('announcements', event)">Announcements</a>
            </div>

            <div class="hero__stats">
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="<?php echo e($stats['courses'] ?? 0); ?>">0</div>
                    <div class="hero__stat-label">Courses</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="<?php echo e($stats['learners'] ?? 0); ?>">0</div>
                    <div class="hero__stat-label">Learners</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="<?php echo e($stats['certificates'] ?? 0); ?>">0</div>
                    <div class="hero__stat-label">Certificates</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-num" data-count="<?php echo e($stats['badges'] ?? 0); ?>">0</div>
                    <div class="hero__stat-label">Badges</div>
                </div>
            </div>
        </div>

        <div class="hero__panel" aria-label="Featured PSU images">
            <div class="hero__panel-shell">
                <div class="hero__panel-body">
                    <div class="hero__carousel" data-carousel>
                        <div class="hero__slide active">
                            <img src="<?php echo e(asset('Images/legacy-building.jpg')); ?>" alt="Historic PSU campus building">
                            <div class="hero__slide-caption">
                                <small>PSU campus</small>
                                <strong>Rooted in a tradition of learning</strong>
                            </div>
                        </div>
                        <div class="hero__slide">
                            <img src="<?php echo e(asset('Images/Green_field_PSU.jpg')); ?>" alt="Green field at PSU">
                            <div class="hero__slide-caption">
                                <small>Learn at PSU</small>
                                <strong>Make space for your next skill</strong>
                            </div>
                        </div>
                        <div class="hero__slide">
                            <img src="<?php echo e(asset('Images/Level-Up-Your-Skills.jpg')); ?>" alt="Level up your skills">
                            <div class="hero__slide-caption">
                                <small>Build confidence</small>
                                <strong>Level up your skills</strong>
                            </div>
                        </div>
                        <div class="hero__slide">
                            <img src="<?php echo e(asset('Images/Learn-at-your-own-pace.jpg')); ?>" alt="Learn at your own pace">
                            <div class="hero__slide-caption">
                                <small>Flexible learning</small>
                                <strong>Learn at your own pace</strong>
                            </div>
                        </div>
                        <div class="hero__slide">
                            <img src="<?php echo e(asset('Images/Earn-Credentials.jpg')); ?>" alt="Earn credentials through learning">
                            <div class="hero__slide-caption">
                                <small>Show what you know</small>
                                <strong>Earn credentials that matter</strong>
                            </div>
                        </div>

                        <button class="hero__carousel-control hero__carousel-control--prev" type="button" aria-label="Previous image">&lsaquo;</button>
                        <button class="hero__carousel-control hero__carousel-control--next" type="button" aria-label="Next image">&rsaquo;</button>
                        <div class="hero__carousel-dots" aria-label="Choose carousel image">
                            <button class="hero__carousel-dot active" type="button" aria-label="Show image 1" aria-current="true"></button>
                            <button class="hero__carousel-dot" type="button" aria-label="Show image 2"></button>
                            <button class="hero__carousel-dot" type="button" aria-label="Show image 3"></button>
                            <button class="hero__carousel-dot" type="button" aria-label="Show image 4"></button>
                            <button class="hero__carousel-dot" type="button" aria-label="Show image 5"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="hero-divider" aria-hidden="true"></div>

<?php $featuredCourses = $featuredCourses ?? []; ?>
<section class="featured" id="featured">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured programs</h2>
            <span class="section-tag">Popular picks</span>
        </div>

        <div class="courses-grid <?php echo e(count($featuredCourses) === 1 ? 'courses-grid--single' : ''); ?>">
            <?php $__empty_1 = true; $__currentLoopData = $featuredCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('components.course-card', ['course' => $course], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:2rem 0 0;">No featured courses right now — check out the latest courses below.</p>
            <?php endif; ?>
        </div>

        <div class="featured__footer">
            <a href="<?php echo e(url('/explore')); ?>" class="btn btn-gold">View all courses</a>
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

<section class="how-it-works">
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
            <span class="section-tag">Latest updates</span>
        </div>

        <?php $announcements = $announcements ?? []; ?>
        <div class="announcements-list" id="announcements-list">
            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="ann-card ann-card--<?php echo e($ann['type']); ?>">
                    <div class="ann-card__body">
                        <div class="ann-card__meta">
                            <span class="ann-card__type"><?php echo e($ann['label']); ?></span>
                            <span class="ann-card__date"><?php echo e($ann['date']); ?></span>
                        </div>
                        <div class="ann-card__title"><?php echo e($ann['title']); ?></div>
                        <p class="ann-card__desc"><?php echo e($ann['desc']); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="color:var(--muted);padding:18px 4px;">No announcements yet — check back soon for new courses and site updates.</p>
            <?php endif; ?>
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
            <?php $latestCourses = $latestCourses ?? []; ?>
            <?php $__currentLoopData = $latestCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('components.course-card', ['course' => $course], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2 class="cta-banner__title">Ready to grow with PSU?</h2>
        <p class="cta-banner__sub">Join the UpSkill community and build the next step in your learning journey.</p>
        <a href="<?php echo e(url('/register')); ?>" class="btn btn-navy">Create free account</a>
    </div>
</section>

<?php $__env->startPush('scripts'); ?>
<script>
    function smoothScrollTo(id, e) {
        if (e) e.preventDefault();
        const target = document.getElementById(id);
        if (!target) return;
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.addEventListener('DOMContentLoaded', function () {
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
        const dots = carousel.querySelectorAll('.hero__carousel-dot');
        const previous = carousel.querySelector('.hero__carousel-control--prev');
        const next = carousel.querySelector('.hero__carousel-control--next');
        let current = 0;
        let timer;

        function showSlide(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => slide.classList.toggle('active', slideIndex === current));
            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === current;
                dot.classList.toggle('active', isActive);
                dot.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }

        function startAutoplay() {
            clearInterval(timer);
            timer = setInterval(() => showSlide(current + 1), 5000);
        }

        previous.addEventListener('click', () => {
            showSlide(current - 1);
            startAutoplay();
        });

        next.addEventListener('click', () => {
            showSlide(current + 1);
            startAutoplay();
        });

        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            showSlide(index);
            startAutoplay();
        }));

        carousel.addEventListener('mouseenter', () => clearInterval(timer));
        carousel.addEventListener('mouseleave', startAutoplay);
        showSlide(0);
        startAutoplay();
    })();
</script>
<?php $__env->stopPush(); ?>


<?php if(!empty($scannedCertificate)): ?>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script:wght@600&family=Parisienne&family=Sacramento&family=Homemade+Apple&display=swap" rel="stylesheet">
<div class="certfloat-overlay" id="certFloat" onclick="if(event.target===this)closeCertFloat()">
    <div class="certfloat">
        <button type="button" class="certfloat__close" onclick="closeCertFloat()" aria-label="Close">&times;</button>

        <?php if(($scannedCertificate['status'] ?? 'active') === 'revoked'): ?>
            <div class="certfloat__verified" style="background:#fff1f2;color:#b42318" role="status">Revoked Certificate</div>
        <?php else: ?>
            <div class="certfloat__verified">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Verified Certificate
            </div>
        <?php endif; ?>

        <?php echo $__env->make('components.certificate', ['cert' => $scannedCertificate], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
.certfloat__close:hover{color:#13176b;}
.certfloat__verified{display:inline-flex;align-items:center;gap:7px;background:#ecfdf3;
    border:1px solid #a7f3d0;color:#065f46;border-radius:999px;padding:6px 14px;
    font-size:12.5px;font-weight:800;margin-bottom:16px;}
.certfloat__verified svg{width:14px;height:14px;}
.certfloat__btn{width:100%;margin-top:18px;background:#0a1f6e;color:#fff;border:none;
    border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;}
.certfloat__btn:hover{background:#071550;}
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
<?php endif; ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kurt Palavino\Herd\UpSkill-Microcredential_Platform\resources\views/public/home.blade.php ENDPATH**/ ?>