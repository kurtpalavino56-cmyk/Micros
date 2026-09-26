
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password – UpSkill PSU</title>
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
            padding: 0.8rem 1.1rem;
            font-size: 0.92rem;
            font-family: var(--font-body);
            color: var(--white);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field input:focus { border-color: var(--gold); background: rgba(255,255,255,0.10); }
        .field input::placeholder { color: rgba(255,255,255,0.3); }

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
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-seal">
            <img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="PSU Logo">
        </div>

        <h1 class="auth-heading">Forgot Password</h1>
        <p class="auth-sub">
            Enter the email address on your UPSKILL account. We will send a 6-digit
            verification code to confirm it is you.
        </p>

        <?php if(session('status')): ?>
            <div class="alert alert-ok"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-err">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.email')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e(old('email')); ?>"
                       placeholder="you@example.com"
                       autocomplete="email" required autofocus>
            </div>

            <button type="submit" class="auth-submit">Send Verification Code</button>
        </form>

        <div class="auth-foot">
            Remembered it?
            <a href="<?php echo e(route('login')); ?>">Back to Login</a>
        </div>

    </div>
</div>


    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\Kurt Palavino\Herd\UpSkill-Microcredential_Platform\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>