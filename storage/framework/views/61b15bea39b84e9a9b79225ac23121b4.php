
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification – UpSkill PSU</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #0a1f6e; --navy-dark: #071550;
            --gold: #e8a800; --gold-light: #f5c518;
            --white: #ffffff;
            --font-display: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        html, body { height: 100%; font-family: var(--font-body); background: #060d2e; }
        a { text-decoration: none; color: inherit; }

        .auth-wrapper {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1.2rem;
            background: radial-gradient(ellipse 80% 70% at 50% 0%, #112280 0%, #060d2e 70%);
        }

        .auth-card {
            width: 100%; max-width: 460px;
            background: #0e1c5e;
            border-radius: 24px;
            padding: 2.6rem 2.4rem 2.4rem;
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
        }

        .auth-seal {
            width: 62px; height: 62px; border-radius: 50%;
            background: #fff; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.4rem;
        }
        .auth-seal img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }

        .auth-heading {
            font-family: var(--font-display);
            font-weight: 900; font-size: 1.6rem;
            color: var(--gold); text-transform: uppercase;
            margin-bottom: 0.45rem;
        }
        .auth-sub {
            font-size: 0.86rem; line-height: 1.6;
            color: rgba(255,255,255,0.5);
            margin-bottom: 1.6rem;
        }
        .auth-sub strong { color: rgba(255,255,255,0.85); font-weight: 600; }

        .field { margin-bottom: 1.3rem; }
        .field label {
            display: block; font-size: 0.82rem; font-weight: 600;
            color: var(--gold); margin-bottom: 0.5rem;
        }

        /* Wide letter-spacing so a 6-digit code reads as digits, not a word. */
        .code-input {
            width: 100%;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0.9rem 1.1rem;
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.55em;
            text-indent: 0.55em;
            text-align: center;
            color: var(--white);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .code-input:focus { border-color: var(--gold); background: rgba(255,255,255,0.10); }
        .code-input::placeholder { color: rgba(255,255,255,0.22); letter-spacing: 0.4em; }

        .auth-submit {
            width: 100%; padding: 0.85rem;
            background: var(--gold); color: var(--navy-dark);
            font-family: var(--font-display); font-weight: 700; font-size: 0.95rem;
            border: none; border-radius: 10px; cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .auth-submit:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232,168,0,0.35);
        }
        .auth-submit:active { transform: translateY(0); }

        .auth-foot {
            margin-top: 1.4rem; text-align: center;
            font-size: 0.83rem; color: rgba(255,255,255,0.4);
            line-height: 1.9;
        }
        .auth-foot a { color: var(--gold); font-weight: 700; }
        .auth-foot a:hover { opacity: 0.75; }

        .link-btn {
            background: none; border: none; cursor: pointer;
            color: var(--gold); font-weight: 700; font-size: 0.83rem;
            font-family: var(--font-body); padding: 0;
        }
        .link-btn:hover { opacity: 0.75; }

        .alert {
            border-radius: 10px; font-size: 0.82rem; line-height: 1.55;
            padding: 0.8rem 1rem; margin-bottom: 1.4rem;
        }
        .alert-ok {
            background: rgba(52,211,153,0.12);
            border: 1px solid rgba(52,211,153,0.35);
            color: #6ee7b7;
        }
        .alert-err {
            background: rgba(255,107,107,0.12);
            border: 1px solid rgba(255,107,107,0.35);
            color: #ff8080;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 2rem 1.4rem 1.8rem; }
            .code-input { font-size: 1.25rem; letter-spacing: 0.4em; text-indent: 0.4em; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-seal">
            <img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="PSU Logo">
        </div>

        <h1 class="auth-heading">Email Verification</h1>
        <p class="auth-sub">
            We sent a 6-digit verification code to <strong><?php echo e($email); ?></strong>.
            Enter it below to continue. The code expires in
            <?php echo e($ttl); ?> minutes.
        </p>

        <?php if(session('status')): ?>
            <div class="alert alert-ok"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-err">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.verify.submit')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" class="code-input"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       placeholder="000000" autocomplete="one-time-code"
                       required autofocus>
            </div>

            <button type="submit" class="auth-submit">Verify Email</button>
        </form>

        <div class="auth-foot">
            Didn't get it?
            <form method="POST" action="<?php echo e(route('password.resend')); ?>" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="link-btn">Send a new code</button>
            </form>
            <br>
            <a href="<?php echo e(route('password.request')); ?>">Use a different email</a>
        </div>

    </div>
</div>

<script>
    // Digits only, and submit as soon as six are entered.
    var code = document.getElementById('code');
    code.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        if (this.value.length === 6) this.form.submit();
    });
</script>


    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\Kurt Palavino\Herd\UpSkill-Microcredential_Platform\resources\views/auth/verify-code.blade.php ENDPATH**/ ?>