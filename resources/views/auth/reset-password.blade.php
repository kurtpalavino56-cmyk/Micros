{{--
    resources/views/auth/reset-password.blade.php

    Step 2 of the reset flow: choose the new password.

    Reached only from the emailed link, which carries the token and email.
    Both travel as hidden fields so the broker can verify them on submit.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – UpSkill PSU</title>
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
        }

        html, body { height: 100%; font-family: var(--font-body); background: #060d2e; }
        a { text-decoration: none; color: inherit; }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.2rem;
            background: radial-gradient(ellipse 80% 70% at 50% 0%, #112280 0%, #060d2e 70%);
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: #0e1c5e;
            border-radius: 24px;
            padding: 2.6rem 2.4rem 2.4rem;
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
        }

        .auth-seal {
            width: 62px; height: 62px;
            border-radius: 50%;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            margin-bottom: 1.4rem;
        }
        .auth-seal img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }

        .auth-heading {
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 1.65rem;
            color: var(--gold);
            text-transform: uppercase;
            margin-bottom: 0.45rem;
        }
        .auth-sub {
            font-size: 0.86rem;
            line-height: 1.6;
            color: rgba(255,255,255,0.5);
            margin-bottom: 1.9rem;
        }

        .field { margin-bottom: 1.3rem; }
        .field label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }
        .field input {
            width: 100%;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0.8rem 3.6rem 0.8rem 1.1rem;
            font-size: 0.92rem;
            font-family: var(--font-body);
            color: var(--white);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field input:focus { border-color: var(--gold); background: rgba(255,255,255,0.10); }
        .field input::placeholder { color: rgba(255,255,255,0.3); }

        .pw-wrap { position: relative; display: flex; align-items: center; }
        .pw-eye {
            position: absolute; right: 10px;
            background: transparent; border: none; cursor: pointer;
            color: var(--gold); font-size: 0.75rem; font-weight: 700;
            padding: 6px 8px; border-radius: 8px;
            font-family: var(--font-body);
        }
        .pw-eye:hover { background: rgba(255,255,255,0.08); }

        .field-hint {
            display: block;
            margin-top: 0.4rem;
            font-size: 0.74rem;
            color: rgba(255,255,255,0.38);
        }

        .auth-submit {
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
        }
        .auth-submit:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .auth-submit:active { transform: translateY(0); }

        .auth-foot {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.83rem;
            color: rgba(255,255,255,0.4);
        }
        .auth-foot a { color: var(--gold); font-weight: 700; margin-left: 0.3rem; }
        .auth-foot a:hover { opacity: 0.75; }

        .alert {
            border-radius: 10px;
            font-size: 0.82rem;
            line-height: 1.55;
            padding: 0.8rem 1rem;
            margin-bottom: 1.4rem;
            background: rgba(255,107,107,0.12);
            border: 1px solid rgba(255,107,107,0.35);
            color: #ff8080;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 2rem 1.4rem 1.8rem; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-seal">
            <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo">
        </div>

        <h1 class="auth-heading">Reset Password</h1>
        <p class="auth-sub">Email verified. Choose a new password for your UPSKILL account.</p>

        @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- No token or email field: the verified address is held in
                 the session by the code step, so it cannot be swapped for
                 somebody else's by editing the form. --}}
            <div class="field">
                <label>Account</label>
                <input type="email" value="{{ $email }}" disabled
                       style="opacity:.65;cursor:not-allowed;">
            </div>

            <div class="field">
                <label for="password">New Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="At least 8 characters"
                           autocomplete="new-password" required autofocus>
                    <button type="button" class="pw-eye" onclick="togglePwd('password', this)">Show</button>
                </div>
                <span class="field-hint">Use at least 8 characters.</span>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm New Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Repeat the new password"
                           autocomplete="new-password" required>
                    <button type="button" class="pw-eye" onclick="togglePwd('password_confirmation', this)">Show</button>
                </div>
            </div>

            <button type="submit" class="auth-submit">Reset Password</button>
        </form>

        <div class="auth-foot">
            Changed your mind?
            <a href="{{ route('login') }}">Back to Login</a>
        </div>

    </div>
</div>

<script>
    function togglePwd(id, btn) {
        var input = document.getElementById(id);
        if (!input) return;
        var hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        btn.textContent = hidden ? 'Hide' : 'Show';
    }
</script>


    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
