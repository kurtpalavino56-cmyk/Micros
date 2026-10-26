<x-faculty.layout :shell="false" title="Faculty Register – UpSkill PSU" data-faculty-page="faculty-register">
    <x-slot:head>{{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    </x-slot:head>
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
                            data-password-strength
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
                <ul class="password-checklist" data-password-checklist aria-label="Password requirements">
                    <li class="password-checklist__title">Your password must include:</li>
                    <li data-password-rule="length">At least 8 characters</li>
                    <li data-password-rule="uppercase">At least 1 uppercase letter</li>
                    <li data-password-rule="lowercase">At least 1 lowercase letter</li>
                    <li data-password-rule="number">At least 1 number</li>
                    <li data-password-rule="symbol">At least 1 special character</li>
                </ul>

                <button type="submit" class="register-submit">Sign Up</button>

            </form>

            <div class="register-login">
                Already have an Account? 
                <a href="{{ route('login') }}">Login here</a>
            </div>

            {{-- Not faculty? Use the regular student registration --}}
            <a href="{{ route('register') }}" class="register-faculty">Student Register</a>

        </div>

        {{-- ── RIGHT: Image Slider ── --}}
        <div class="slider" id="slider">

            <div class="slider__brand">UPSKILL</div>

            <div class="slider__seals">
                <div class="slider__seal">
                    <img src="{{ asset('Images/PSU-Logo.png') }}" alt="PSU">
                </div>
            </div>

            <div class="slide slide-1 active" style="background-image: url('{{ asset('Images/Level-Up-Your-Skills.jpg') }}')"></div>
            <div class="slide slide-2" style="background-image: url('{{ asset('Images/Learn-at-your-own-pace.jpg') }}')"></div>
            <div class="slide slide-3" style="background-image: url('{{ asset('Images/Earn-Credentials.jpg') }}')"></div>

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

    const passwordInput = document.querySelector('[data-password-strength]');
    const passwordRules = document.querySelectorAll('[data-password-rule]');
    const passwordChecks = {
        length: value => value.length >= 8,
        uppercase: value => /[A-Z]/.test(value),
        lowercase: value => /[a-z]/.test(value),
        number: value => /\d/.test(value),
        symbol: value => /[^A-Za-z0-9]/.test(value),
    };

    passwordInput.addEventListener('input', () => {
        passwordRules.forEach(rule => {
            const isMet = passwordChecks[rule.dataset.passwordRule](passwordInput.value);
            rule.classList.toggle('is-met', isMet);
            rule.setAttribute('aria-checked', isMet ? 'true' : 'false');
        });
    });
</script>


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</x-faculty.layout>
