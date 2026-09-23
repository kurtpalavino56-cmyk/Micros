<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Register – UpSkill PSU</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
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

        /* ── Outer wrapper ── */
        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(ellipse 80% 70% at 50% 0%, #112280 0%, #060d2e 70%);
        }

        /* ── Card ── */
        .register-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 980px;
            min-height: 620px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
        }

        /* ════════════════════════════════
           LEFT — Form Panel
        ════════════════════════════════ */
        .register-form-panel {
            background: #0e1c5e;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2.8rem;
            position: relative;
        }

        /* Back button */
        .register-back {
            position: absolute;
            top: 1.4rem;
            left: 1.8rem;
            color: rgba(255,255,255,0.55);
            font-size: 1.3rem;
            line-height: 1;
            transition: color var(--transition);
            cursor: pointer;
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .register-back:hover { color: var(--gold); }
        .register-back svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }

        .register-form-panel__heading {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--gold);
            margin-bottom: 1.8rem;
            text-align: center;
        }

        /* Two-col row */
        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
            margin-bottom: 0.85rem;
        }

        /* Single field */
        .field { margin-bottom: 0.85rem; }

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
        .field input::placeholder { color: rgba(255,255,255,0.3); }
        .field input:focus {
            border-color: var(--gold);
            background: rgba(255,255,255,0.10);
        }

        /* Sign Up button */
        .register-submit {
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
            margin-top: 0.5rem;
            margin-bottom: 1.2rem;
        }
        .register-submit:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .register-submit:active { transform: translateY(0); }

        /* Login link */
        .register-login {
            text-align: center;
            font-size: 0.83rem;
            color: var(--gold);
            font-weight: 700;
        }
        .register-login a {
            color: var(--gold);
            transition: opacity 0.2s;
        }
        .register-login a:hover { opacity: 0.75; }

        /* Faculty Register button */
        .register-faculty {
            display: block;
            text-align: center;
            margin-top: 0.9rem;
            padding: 0.7rem;
            border: 1px solid rgba(232,168,0,0.55);
            border-radius: 10px;
            color: var(--gold);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            transition: background 0.2s, color 0.2s, transform 0.2s;
        }
        .register-faculty:hover {
            background: rgba(232,168,0,0.14);
            transform: translateY(-1px);
        }

        /* Errors */
        .form-error { color: #ff6b6b; font-size: 0.76rem; margin-top: 0.25rem; }
        .alert-error {
            background: rgba(255,107,107,0.12);
            border: 1px solid rgba(255,107,107,0.35);
            border-radius: 10px;
            color: #ff8080;
            font-size: 0.82rem;
            padding: 0.7rem 1rem;
            margin-bottom: 1.2rem;
        }

        /* ════════════════════════════════
           RIGHT — Image Panel (same slider as login)
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

        .slide-1 { background-image: linear-gradient(135deg, #1a3080 0%, #0d4080 50%, #0a2850 100%); }
        .slide-2 { background-image: linear-gradient(135deg, #0d2060 0%, #1a4070 50%, #083050 100%); }
        .slide-3 { background-image: linear-gradient(135deg, #071550 0%, #0a3060 50%, #1a2870 100%); }

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
        .slider__seal img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .register-card { grid-template-columns: 1fr; }
            .slider { min-height: 200px; order: -1; }
            .register-form-panel { padding: 2rem 1.6rem; }
        }
        @media (max-width: 400px) {
            .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="register-wrapper">
    <div class="register-card">

        {{-- ── LEFT: Register Form ── --}}
        <div class="register-form-panel">

            <a href="{{ url('/') }}" class="register-back" title="Back to Home">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </a>

            <h1 class="register-form-panel__heading">Faculty Registration</h1>

            @if ($errors->any())
                <div class="alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('faculty.register.store') }}">
                @csrf

                {{-- Name row --}}
                <div class="field-row">
                    <div class="field">
                        <input type="text" name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="First Name"
                            required>
                        @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <input type="text" name="middle_name"
                            value="{{ old('middle_name') }}"
                            placeholder="Middle Name">
                        @error('middle_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Last Name + Suffix --}}
                <div class="field-row">
                    <div class="field">
                        <input type="text" name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="Last Name"
                            required>
                        @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <input type="text" name="suffix"
                            value="{{ old('suffix') }}"
                            placeholder="Suffix ( optional )">
                        @error('suffix')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="field">
                    <input type="email" name="email"
                        value="{{ old('email') }}"
                        placeholder="Email"
                        autocomplete="email"
                        required>
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Username --}}
                <div class="field">
                    <input type="text" name="username"
                        value="{{ old('username') }}"
                        placeholder="Username"
                        autocomplete="username"
                        required>
                    @error('username')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Faculty Code Number (issued by the Admin) --}}
                <div class="field">
                    <input type="text" name="faculty_code"
                        value="{{ old('faculty_code') }}"
                        placeholder="Faculty Code Number"
                        autocomplete="off"
                        required>
                    @error('faculty_code')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Password + Confirm --}}
                <div class="field-row">
                    <div class="field">
                        <input type="password" name="password"
                            placeholder="Password"
                            autocomplete="new-password"
                            required>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <input type="password" name="password_confirmation"
                            placeholder="Confirm Password"
                            autocomplete="new-password"
                            required>
                    </div>
                </div>

                <button type="submit" class="register-submit">Sign Up</button>

            </form>

            <div class="register-login">
                <a href="{{ route('login') }}">Already have an Account? Login here</a>
            </div>

            {{-- Not faculty? Use the regular student registration --}}
            <a href="{{ route('register') }}" class="register-faculty">Student Register</a>

        </div>

        {{-- ── RIGHT: Image Slider ── --}}
        <div class="slider" id="slider">

            <div class="slider__brand">UPSKILL</div>

            <div class="slider__seals">
                <div class="slider__seal">
                    <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU">
                </div>
                <div class="slider__seal">
                    <img src="{{ asset('images/CCS-Logo.png') }}" alt="CCS">
                </div>
            </div>

            <div class="slide slide-1 active" style="background-image: url('{{ asset('images/Level-Up-Your-Skills.jpg') }}')"></div>
            <div class="slide slide-2" style="background-image: url('{{ asset('images/Learn-at-your-own-pace.jpg') }}')"></div>
            <div class="slide slide-3" style="background-image: url('{{ asset('images/Earn-Credentials.jpg') }}')"></div>

            <div class="slider__caption">
                <div class="slider__caption-title" id="captionTitle">Level Up Your Skills</div>
                <div class="slider__caption-sub" id="captionSub">Earn badges. Shape your future.</div>
            </div>

            <div class="slider__dots" id="sliderDots">
                <button class="slider__dot active" data-index="0"></button>
                <button class="slider__dot" data-index="1"></button>
                <button class="slider__dot" data-index="2"></button>
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


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>