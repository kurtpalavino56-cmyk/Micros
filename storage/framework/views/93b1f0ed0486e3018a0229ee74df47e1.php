<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – UpSkill PSU</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:         #0a1f6e;
            --navy-dark:    #071550;
            --gold:         #e8a800;
            --gold-light:   #f5c518;
            --white:        #ffffff;
            --font-display: 'Poppins', sans-serif;
            --font-body:    'Inter', sans-serif;
            --transition:   0.22s ease;
        }

        html, body {
            height: 100%;
            font-family: var(--font-body);
            background: #060d2e;
        }

        a { text-decoration: none; color: inherit; }

        /* ── Outer wrapper: centres the card ── */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(ellipse 80% 70% at 50% 0%, #112280 0%, #060d2e 70%);
        }

        /* ── Card ── */
        .login-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 980px;
            min-height: 580px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
        }

        /* ════════════════════════════════
           LEFT — Image Slider
        ════════════════════════════════ */
        .slider {
            position: relative;
            overflow: hidden;
            background: var(--navy-dark);
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1s ease;
            background-size: cover;
            background-position: center;
        }
        .slide.active { opacity: 1; }

        /* Gradient overlay on each slide */
        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(7,21,80,0.35) 0%,
                rgba(7,21,80,0.15) 40%,
                rgba(7,21,80,0.65) 100%
            );
        }

        /* Gradient fallbacks — overridden by inline style when image is present */
        .slide-1 { background-image: linear-gradient(135deg, #1a3080 0%, #0d4080 50%, #0a2850 100%); }
        .slide-2 { background-image: linear-gradient(135deg, #0d2060 0%, #1a4070 50%, #083050 100%); }
        .slide-3 { background-image: linear-gradient(135deg, #071550 0%, #0a3060 50%, #1a2870 100%); }
        .slide[style] { background-size: cover; background-position: center; }

        /* Caption overlay */
        .slider__caption {
            position: absolute;
            bottom: 2.5rem;
            left: 2rem;
            right: 2rem;
            z-index: 2;
            color: var(--white);
        }
        .slider__caption-title {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.6rem;
            line-height: 1.25;
            margin-bottom: 0.4rem;
            text-shadow: 0 2px 12px rgba(0,0,0,0.4);
        }
        .slider__caption-sub {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.7);
        }

        /* Dots */
        .slider__dots {
            position: absolute;
            bottom: 1.2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 3;
        }
        .slider__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            border: none;
        }
        .slider__dot.active {
            background: var(--gold);
            transform: scale(1.3);
        }

        /* Brand badge top-left */
        .slider__brand {
            position: absolute;
            top: 1.5rem;
            left: 1.8rem;
            z-index: 3;
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 1.1rem;
            color: var(--white);
            letter-spacing: 0.05em;
        }

        /* Seals top-right */
        .slider__seals {
            position: absolute;
            top: 1.2rem;
            right: 1.5rem;
            z-index: 3;
            display: flex;
            gap: 0.6rem;
        }
        .slider__seal {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: transparent;
            border: 2px solid rgba(255,255,255,0.25);
            overflow: hidden;
        }
        .slider__seal img { width: 100%; height: 100%; object-fit: cover; padding: 0; display: block; }

        /* ════════════════════════════════
           RIGHT — Form Panel
        ════════════════════════════════ */
        .login-form-panel {
            background: #0e1c5e;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.8rem;
        }

        .login-form-panel__heading {
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 1.9rem;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: -0.01em;
            margin-bottom: 0.4rem;
        }
        .login-form-panel__sub {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.45);
            margin-bottom: 2.2rem;
        }

        /* Fields */
        .field { margin-bottom: 1.3rem; }
        .field label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }
        .field input {
            width: 100%;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0.8rem 1.1rem;
            font-size: 0.92rem;
            font-family: var(--font-body);
            color: var(--white);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field input:focus {
            border-color: var(--gold);
            background: rgba(255,255,255,0.10);
        }
        .field input::placeholder { color: rgba(255,255,255,0.3); }

        /* Forgot */
        .login-forgot {
            display: block;
            text-align: right;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.45);
            margin-top: -0.6rem;
            margin-bottom: 1.8rem;
            transition: color 0.2s;
        }
        .login-forgot:hover { color: var(--gold); }

        /* Login button */
        .login-submit {
            width: 100%;
            padding: 0.85rem;
            background: var(--gold);
            color: var(--navy-dark);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.02em;
        }
        .login-submit:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .login-submit:active { transform: translateY(0); }

        /* Register */
        .login-register {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.83rem;
            color: rgba(255,255,255,0.4);
        }
        .login-register a {
            color: var(--gold);
            font-weight: 700;
            margin-left: 0.3rem;
            transition: opacity 0.2s;
        }
        .login-register a:hover { opacity: 0.75; }

        /* Errors */
        .form-error { color: #ff6b6b; font-size: 0.78rem; margin-top: 0.35rem; }
        .alert-ok {
            background: rgba(52,211,153,0.12);
            border: 1px solid rgba(52,211,153,0.35);
            border-radius: 10px;
            color: #6ee7b7;
            font-size: 0.82rem;
            line-height: 1.55;
            padding: 0.7rem 1rem;
            margin-bottom: 1.4rem;
        }

        .alert-error {
            background: rgba(255,107,107,0.12);
            border: 1px solid rgba(255,107,107,0.35);
            border-radius: 10px;
            color: #ff8080;
            font-size: 0.82rem;
            padding: 0.7rem 1rem;
            margin-bottom: 1.4rem;
        }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .login-card { grid-template-columns: 1fr; }
            .slider { min-height: 220px; position: relative; }
            .login-form-panel { padding: 2rem 1.6rem; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        
        <div class="slider" id="slider">

            <div class="slider__brand">UPSKILL</div>

            <div class="slider__seals">
                <div class="slider__seal">
                    <img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="PSU">
                </div>
                <div class="slider__seal">
                    <img src="<?php echo e(asset('images/CCS-Logo.png')); ?>" alt="CCS">
                </div>
            </div>

            <div class="slide slide-1 active" style="background-image: url('<?php echo e(asset('images/Level-Up-Your-Skills.jpg')); ?>')"></div>
            <div class="slide slide-2" style="background-image: url('<?php echo e(asset('images/Learn-at-your-own-pace.jpg')); ?>')"></div>
            <div class="slide slide-3" style="background-image: url('<?php echo e(asset('images/Earn-Credentials.jpg')); ?>')"></div>

            <div class="slider__caption" id="sliderCaption">
                <div class="slider__caption-title" id="captionTitle">Level Up Your Skills</div>
                <div class="slider__caption-sub" id="captionSub">Earn badges. Shape your future.</div>
            </div>

            <div class="slider__dots" id="sliderDots">
                <button class="slider__dot active" data-index="0"></button>
                <button class="slider__dot" data-index="1"></button>
                <button class="slider__dot" data-index="2"></button>
            </div>

        </div>

        
        <div class="login-form-panel">

            <h1 class="login-form-panel__heading">Welcome</h1>
            <p class="login-form-panel__sub">Sign in to continue your learning journey</p>

            
            <?php if(session('status')): ?>
                <div class="alert-ok"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert-error">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(url('/login')); ?>">
                <?php echo csrf_field(); ?>

                <div class="field">
                    <label for="email">Username</label>
                    <input type="text" id="email" name="email"
                        value="<?php echo e(old('email')); ?>"
                        placeholder="Enter your username or email"
                        autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password" required>
                </div>

                <?php if(Route::has('password.request')): ?>
                    <a href="<?php echo e(route('password.request')); ?>" class="login-forgot">Forgot Password?</a>
                <?php endif; ?>

                <button type="submit" class="login-submit">Login</button>

            </form>

            <div class="login-register">
                Don't have an account?
                <a href="<?php echo e(route('register')); ?>">Register here</a>
            </div>

        </div>

    </div>
</div>

<script>
    const slides   = document.querySelectorAll('.slide');
    const dots     = document.querySelectorAll('.slider__dot');
    const captions = [
        { title: 'Level Up Your Skills',        sub: 'Earn badges. Shape your future.'         },
        { title: 'Learn at Your Own Pace',       sub: 'Access courses anytime, anywhere.'       },
        { title: 'Earn PSU Microcredentials',    sub: 'Recognized by industry and academia.'    },
    ];

    let current = 0;
    let timer;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = index;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
        document.getElementById('captionTitle').textContent = captions[current].title;
        document.getElementById('captionSub').textContent   = captions[current].sub;
    }

    function next() { goTo((current + 1) % slides.length); }

    function startTimer() { timer = setInterval(next, 4000); }
    function resetTimer()  { clearInterval(timer); startTimer(); }

    dots.forEach(dot => {
        dot.addEventListener('click', () => { goTo(+dot.dataset.index); resetTimer(); });
    });

    startTimer();
</script>


    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/auth/login.blade.php ENDPATH**/ ?>