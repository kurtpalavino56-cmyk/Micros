<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – UpSkill PSU</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
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

        .password-checklist {
            margin: -0.2rem 0 0.9rem;
            padding: 0.75rem 0.9rem;
            list-style: none;
            border-radius: 10px;
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.65);
            font-size: 0.76rem;
        }
        .password-checklist__title {
            margin-bottom: 0.35rem;
            color: rgba(255,255,255,0.85);
            font-weight: 600;
        }
        .password-checklist li:not(.password-checklist__title) {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.2rem;
        }
        .password-checklist li:not(.password-checklist__title)::before {
            content: '';
            width: 0.8rem;
            height: 0.8rem;
            flex: 0 0 0.8rem;
            border: 1px solid rgba(255,255,255,0.45);
            border-radius: 50%;
        }
        .password-checklist li.is-met { color: #a9f0c5; }
        .password-checklist li.is-met::before {
            content: '✓';
            display: grid;
            place-items: center;
            border-color: #a9f0c5;
            background: #a9f0c5;
            color: var(--navy-dark);
            font-size: 0.6rem;
            font-weight: 800;
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
        .register-submit:not(:disabled):hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .register-submit:not(:disabled):active { transform: translateY(0); }
        /* Locked until the Terms and Conditions are read and agreed to. */
        .register-submit:disabled {
            background: rgba(232,168,0,0.32);
            color: rgba(7,21,80,0.55);
            cursor: not-allowed;
        }

        /* ── Terms agreement checkbox ── */
        .terms-agree {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin: 0.1rem 0 0.4rem;
        }
        .terms-agree__box {
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            margin-top: 0.12rem;
            accent-color: var(--gold);
            cursor: pointer;
        }
        .terms-agree__box:focus-visible { outline: 2px solid var(--gold); outline-offset: 2px; }
        .terms-agree__label {
            font-size: 0.78rem;
            line-height: 1.5;
            color: rgba(255,255,255,0.72);
            cursor: pointer;
        }
        .terms-agree__link {
            background: none;
            border: 0;
            padding: 0;
            font: inherit;
            color: var(--gold);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
            cursor: pointer;
        }
        .terms-agree__link:hover { color: var(--gold-light); }
        .terms-agree__link:focus-visible { outline: 2px solid var(--gold); outline-offset: 2px; border-radius: 2px; }

        /* ════════════════════════════════
           Terms and Conditions pop-up
        ════════════════════════════════ */
        .terms-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .terms-modal[hidden] { display: none; }
        .terms-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(3,8,32,0.72);
            backdrop-filter: blur(3px);
        }
        .terms-modal__dialog {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 720px;
            height: min(86vh, 760px);
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
            animation: termsIn 0.22s ease;
        }
        .terms-modal__dialog:focus { outline: none; }
        @keyframes termsIn {
            from { opacity: 0; transform: translateY(14px) scale(0.98); }
            to   { opacity: 1; transform: none; }
        }
        .terms-modal__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.2rem 1.5rem 1rem;
            background: var(--navy);
            color: var(--white);
        }
        .terms-modal__eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.15rem;
        }
        .terms-modal__title {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--gold);
        }
        .terms-modal__close {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: var(--white);
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            transition: background var(--transition);
        }
        .terms-modal__close:hover { background: rgba(255,255,255,0.2); }
        .terms-modal__close:focus-visible { outline: 2px solid var(--gold); outline-offset: 2px; }

        /* Reading progress bar under the header */
        .terms-modal__progress { height: 4px; background: #e7eaf5; }
        .terms-modal__progress span {
            display: block;
            height: 100%;
            width: 0;
            background: var(--gold);
            transition: width 0.12s linear;
        }

        /* Holds the scrolling text and the arrows that float over it. */
        .terms-modal__scroll {
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
        }
        .terms-modal__body {
            height: 100%;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 1.4rem 1.6rem 1.6rem;
            color: #3f4a63;
        }
        .terms-modal__body:focus-visible { outline: 2px solid var(--gold); outline-offset: -2px; }
        .terms-modal__body h2 {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 800;
            color: var(--navy);
            margin: 1.6rem 0 0.55rem;
        }
        .terms-modal__body p {
            font-size: 0.88rem;
            line-height: 1.7;
            margin-bottom: 0.8rem;
        }
        .terms-modal__body ul { margin: 0 0 1rem 1.15rem; }
        .terms-modal__body li {
            font-size: 0.88rem;
            line-height: 1.65;
            margin-bottom: 0.3rem;
            padding-left: 0.2rem;
        }
        .terms-modal__body li::marker { color: var(--gold); }
        .terms-modal__body .legal__dates {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem 1.4rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8rem;
            color: #6b7280;
        }
        .terms-modal__body .legal__dates strong { color: var(--navy); }
        .terms-modal__body .legal__contact {
            background: #f7f9ff;
            border: 1px solid #dfe4fb;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            margin-bottom: 0.8rem;
        }
        .terms-modal__body .legal__contact p { margin: 0 0 0.25rem; font-size: 0.85rem; }
        .terms-modal__body .legal__contact strong { color: var(--navy); }

        /* The Agree button sits after the last section, so it only comes
           into view once the reader reaches the bottom. */
        .terms-modal__agree {
            margin-top: 1.6rem;
            padding: 1.2rem;
            border-radius: 14px;
            background: #f7f9ff;
            border: 1px solid #dfe4fb;
            text-align: center;
        }
        .terms-modal__agree p { margin-bottom: 0.8rem; font-size: 0.85rem; }
        .terms-modal__agree-btn {
            min-width: 180px;
            padding: 0.8rem 1.6rem;
            border: 0;
            border-radius: 10px;
            background: var(--gold);
            color: var(--navy-dark);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .terms-modal__agree-btn:not(:disabled):hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .terms-modal__agree-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .terms-modal__agree-btn:focus-visible { outline: 2px solid var(--navy); outline-offset: 3px; }

        /* Fast-scroll arrows floating over the text, bottom-right corner */
        .terms-modal__jump {
            position: absolute;
            right: 1.1rem;
            bottom: 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 2;
            pointer-events: none; /* only the buttons themselves catch clicks */
        }
        .terms-modal__jump-btn {
            pointer-events: auto;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 2px solid var(--white);
            border-radius: 50%;
            background: rgba(10,31,110,0.92);
            color: var(--white);
            box-shadow: 0 8px 22px rgba(10,31,110,0.38), 0 2px 6px rgba(0,0,0,0.18);
            cursor: pointer;
            transition: background var(--transition), opacity var(--transition), transform var(--transition);
        }
        .terms-modal__jump-btn svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
        .terms-modal__jump-btn:not(:disabled):hover { background: var(--gold); color: var(--navy-dark); transform: scale(1.06); }
        .terms-modal__jump-btn:disabled { opacity: 0.4; cursor: default; box-shadow: 0 2px 8px rgba(10,31,110,0.2); }
        .terms-modal__jump-btn:focus-visible { outline: 2px solid var(--gold); outline-offset: 2px; }

        .terms-modal__foot {
            padding: 0.75rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: #fafbff;
            font-size: 0.78rem;
            color: #6b7280;
            text-align: center;
        }
        .terms-modal__foot.is-done { color: #167a3f; font-weight: 600; }

        /* Login link */
        .register-login {
            text-align: center;
            font-size: 0.83rem;
            color:rgba(255,255,255,0.4);
        }
        .register-login a {
            color: var(--gold);
            transition: opacity 0.2s;
            font-weight: 700;
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

        .form-error { color: #ff6b6b; font-size: 0.76rem; margin-top: 0.25rem; }
        /* .alert-error {
            background: rgba(255,107,107,0.12);
            border: 1px solid rgba(255,107,107,0.35);
            border-radius: 10px;
            color: #ff8080;
            font-size: 0.82rem;
            padding: 0.7rem 1rem;
            margin-bottom: 1.2rem;
        } */

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
            .register-wrapper { padding: 1rem; align-items: flex-start; }
            .register-card { grid-template-columns: 1fr; min-height: 0; border-radius: 20px; }
            .slider { min-height: 200px; order: -1; }
            /* Extra top padding keeps the back arrow clear of the heading. */
            .register-form-panel { padding: 3.6rem 1.4rem 2rem; }
            .register-back { top: 1.2rem; left: 1.2rem; }
            .register-form-panel__heading { font-size: 1.35rem; margin-bottom: 1.4rem; }
            .slider__caption-title { font-size: 1.25rem; }
        }
        @media (max-width: 380px) {
            .slider__seal { width: 42px; height: 42px; }
            .slider__caption { left: 1.2rem; right: 1.2rem; }
        }
        @media (max-width: 400px) {
            .field-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .terms-modal { padding: 0.75rem; }
            .terms-modal__dialog { height: 92vh; border-radius: 16px; }
            .terms-modal__head { padding: 1rem 1.1rem 0.85rem; }
            .terms-modal__body { padding: 1.1rem 1.1rem 1.4rem; }
            .terms-modal__jump { right: 0.7rem; bottom: 0.8rem; }
            .terms-modal__jump-btn { width: 38px; height: 38px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .terms-modal__dialog { animation: none; }
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

            <h1 class="register-form-panel__heading">Let's get you started!</h1>

            <!-- @if ($errors->any())
                <div class="alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif -->

            <form method="POST" action="{{ url('/register') }}">
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

                {{-- Terms agreement. Ticking the box opens the Terms pop-up;
                     it only stays ticked after "I Agree" is pressed at the
                     bottom of the Terms. Sign Up stays locked until then. --}}
                <div class="terms-agree">
                    <input type="checkbox" id="terms" name="terms" value="1"
                        class="terms-agree__box"
                        data-terms-checkbox
                        @checked(old('terms'))
                        required>
                    <label for="terms" class="terms-agree__label">
                        I have read and agree to the
                        <button type="button" class="terms-agree__link" data-terms-open>Terms and Conditions</button>
                        and acknowledge that I have read and understood the
                        <a href="{{ route('privacy') }}" class="terms-agree__link" target="_blank" rel="noopener">Privacy Policy</a>
                        of UPSKILL: Micro-Credentials, operated by Pangasinan State University
                    </label>
                </div>
                @error('terms')<p class="form-error">{{ $message }}</p>@enderror

                <button type="submit" class="register-submit" data-register-submit
                    @disabled(! old('terms'))>Sign Up</button>

            </form>

            <div class="register-login">
                Already have an Account?
                <a href="{{ route('login') }}">Login here</a>
            </div>

            {{-- Faculty staff register with an admin-issued Faculty Code --}}
            <a href="{{ route('faculty.register') }}" class="register-faculty">Faculty Register</a>

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

{{-- ── Terms and Conditions pop-up ──
     Same wording as the public /terms page (shared partial). --}}
<div class="terms-modal" id="termsModal" hidden>
    <div class="terms-modal__backdrop" data-terms-close></div>
    <section class="terms-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle" tabindex="-1">
        <header class="terms-modal__head">
            <div>
                <p class="terms-modal__eyebrow">UPSKILL: Micro-Credentials</p>
                <h2 class="terms-modal__title" id="termsModalTitle">Terms and Conditions</h2>
            </div>
            <button type="button" class="terms-modal__close" data-terms-close aria-label="Close Terms and Conditions">&times;</button>
        </header>

        <div class="terms-modal__progress" aria-hidden="true"><span data-terms-progress></span></div>

        {{-- The arrows float over the scrolling text, in its bottom-right corner. --}}
        <div class="terms-modal__scroll">
            <div class="terms-modal__body" data-terms-body tabindex="0" aria-label="Terms and Conditions text">
                @include('public.partials.terms-content')

                <div class="terms-modal__agree">
                    <p>You have reached the end of the Terms and Conditions.</p>
                    <button type="button" class="terms-modal__agree-btn" data-terms-agree disabled>I Agree</button>
                </div>
            </div>

            <div class="terms-modal__jump">
                <button type="button" class="terms-modal__jump-btn" data-terms-jump="up" aria-label="Scroll up" title="Scroll up">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <button type="button" class="terms-modal__jump-btn" data-terms-jump="down" aria-label="Scroll down" title="Scroll down">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
            </div>
        </div>

        <p class="terms-modal__foot" data-terms-hint aria-live="polite">Scroll to the bottom to agree to the Terms and Conditions.</p>
    </section>
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

    /* ── Terms agreement ───────────────────────────────────────────────
       The checkbox can only become ticked through "I Agree" at the bottom
       of the Terms pop-up. Sign Up is enabled only while it is ticked.
       Once the Terms have been agreed to, re-ticking the box (after
       unticking it) does not force the pop-up open again. */
    (function () {
        const form      = document.querySelector('.register-form-panel form');
        const checkbox  = document.querySelector('[data-terms-checkbox]');
        const submit    = document.querySelector('[data-register-submit]');
        const modal     = document.getElementById('termsModal');
        if (!form || !checkbox || !submit || !modal) return;

        const dialog    = modal.querySelector('[role="dialog"]');
        const body      = modal.querySelector('[data-terms-body]');
        const agreeBtn  = modal.querySelector('[data-terms-agree]');
        const upBtn     = modal.querySelector('[data-terms-jump="up"]');
        const downBtn   = modal.querySelector('[data-terms-jump="down"]');
        const progress  = modal.querySelector('[data-terms-progress]');
        const hint      = modal.querySelector('[data-terms-hint]');

        // A ticked box on page load means the Terms were already agreed to
        // before a validation error sent the form back.
        let hasAgreed   = checkbox.checked;
        let reachedEnd  = false;
        let opener      = null;

        // Links inside the Terms (e.g. Privacy Policy) open in a new tab so
        // the half-filled sign-up form is not lost.
        body.querySelectorAll('a[href]').forEach(link => {
            link.target = '_blank';
            link.rel = 'noopener';
        });

        function syncSubmit() {
            submit.disabled = !checkbox.checked;
        }

        function updateScrollState() {
            // Hiding the pop-up fires a final scroll event while the text has
            // no size, which would otherwise look like "reached the bottom".
            if (modal.hidden || body.clientHeight === 0) return;

            const max = body.scrollHeight - body.clientHeight;
            const top = body.scrollTop;
            const atTop = top <= 2;
            const atEnd = max <= 2 || top >= max - 2;

            progress.style.width = (max <= 0 ? 100 : Math.min(100, (top / max) * 100)) + '%';

            // A focused button that becomes disabled drops focus out of the
            // pop-up, so hand focus to the other arrow (or the text) first.
            if (atTop && document.activeElement === upBtn) (atEnd ? body : downBtn).focus();
            if (atEnd && document.activeElement === downBtn) (atTop ? body : upBtn).focus();
            upBtn.disabled = atTop;
            downBtn.disabled = atEnd;

            if (atEnd && !reachedEnd) {
                reachedEnd = true;
                agreeBtn.disabled = false;
                hint.textContent = 'You have reached the end. Select "I Agree" to continue.';
                hint.classList.add('is-done');
            }
        }

        function focusables() {
            return Array.from(dialog.querySelectorAll('button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'))
                .filter(el => el.offsetParent !== null);
        }

        function openTerms() {
            opener = document.activeElement;
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            body.scrollTop = 0;
            updateScrollState();
            dialog.focus();
        }

        function closeTerms() {
            modal.hidden = true;
            document.body.style.overflow = '';
            (opener && opener.focus ? opener : checkbox).focus();
        }

        function agree() {
            if (!reachedEnd) return;
            hasAgreed = true;
            checkbox.checked = true;
            syncSubmit();
            opener = checkbox;
            closeTerms();
        }

        // Fast scroll: each click moves most of a screenful.
        function jump(direction) {
            const step = Math.max(120, body.clientHeight * 0.85);
            body.scrollBy({ top: direction === 'up' ? -step : step, behavior: 'smooth' });
        }

        checkbox.addEventListener('click', event => {
            // Ticking before agreeing: keep it unticked and show the Terms.
            if (checkbox.checked && !hasAgreed) {
                event.preventDefault();
                openTerms();
            }
        });
        checkbox.addEventListener('change', syncSubmit);

        document.querySelector('[data-terms-open]').addEventListener('click', openTerms);
        modal.querySelectorAll('[data-terms-close]').forEach(el => el.addEventListener('click', closeTerms));
        agreeBtn.addEventListener('click', agree);
        upBtn.addEventListener('click', () => jump('up'));
        downBtn.addEventListener('click', () => jump('down'));
        body.addEventListener('scroll', updateScrollState, { passive: true });
        window.addEventListener('resize', () => { if (!modal.hidden) updateScrollState(); });

        // On document (not the pop-up) so Escape still works if focus has
        // slipped out of the pop-up.
        document.addEventListener('keydown', event => {
            if (modal.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeTerms();
                return;
            }
            if (event.key !== 'Tab') return;

            // Keep keyboard focus inside the pop-up while it is open.
            const items = focusables();
            if (!items.length) return;
            const first = items[0];
            const last = items[items.length - 1];
            if (!dialog.contains(document.activeElement)) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();
            } else if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        // Belt and braces: never submit without the agreement.
        form.addEventListener('submit', event => {
            if (!checkbox.checked) {
                event.preventDefault();
                openTerms();
            }
        });

        syncSubmit();
    })();
</script>


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>