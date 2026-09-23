<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="UpSkill – The Official Platform for PSU Microcredentials">
    <title>@yield('title', 'UpSkill – PSU Microcredentials')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">


    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:       #0a1f6e;
            --navy-dark:  #071550;
            --navy-ink:   #111a46;
            --blue-soft:  #eef3ff;
            --gold:       #FFD501;
            --gold-light: #FFE36B;
            --gold-bg:    #FFD501;
            --white:      #ffffff;
            --surface:    #f6f8fc;
            --line:       #e3e8f3;
            --text-muted: #5a6a8a;
            --card-bg:    #ffffff;
            --radius-lg:  20px;
            --radius-md:  14px;
            --radius-sm:  8px;
            --font-display: 'Poppins', sans-serif;
            --font-body:    'Inter', sans-serif;
            --shadow-card:  0 10px 30px rgba(10,31,110,0.08);
            --shadow-hover: 0 18px 42px rgba(10,31,110,0.14);
            --transition:   0.22s cubic-bezier(.2,.8,.2,1);
        }

        html { scroll-behavior: smooth; }

        /* Sticky footer: body is a flex column and <main> absorbs the free
           space, so the footer sits at the bottom of the viewport on short
           pages instead of leaving a white gap beneath it. */
        body {
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--navy-ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.55;
        }
        /* main is itself a column so a page's last section can absorb the
           slack — otherwise the gap simply moves above the footer. */
        body > main { flex: 1 0 auto; display: flex; flex-direction: column; }
        body > main > *:last-child { flex: 1 0 auto; }
        body > .footer { flex-shrink: 0; }

        img { max-width: 100%; display: block; }
        a  { text-decoration: none; color: inherit; }
        button, input, select, textarea { font: inherit; }
        ::selection { background: rgba(255,213,1,.28); color: var(--navy-dark); }
        :focus-visible { outline: 3px solid rgba(255,213,1,.8); outline-offset: 3px; }

        .btn {
            display: inline-block;
            padding: 0.72rem 1.35rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            transition: transform var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .btn:hover  { transform: translateY(-2px); }
        .btn:active { transform: translateY(0); }
        .btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

        .btn-gold {
            background: var(--gold);
            color: var(--navy-dark);
        }
        .btn-gold:hover { background: var(--gold-light); box-shadow: 0 6px 18px rgba(255,213,1,0.4); }

        .btn-navy {
            background: var(--navy);
            color: var(--white);
        }
        .btn-navy:hover { background: var(--navy-dark); box-shadow: 0 6px 18px rgba(10,31,110,0.35); }

        .btn-outline-navy {
            background: transparent;
            color: var(--navy);
            border: 2px solid var(--navy);
        }
        .btn-outline-navy:hover { background: var(--navy); color: var(--white); }

        .container {
            width: min(92%, 1180px);
            margin: 0 auto;
        }

        .section { padding: clamp(3.5rem, 7vw, 5.5rem) 0; }

        .section-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
        }

        /* Shared polish for pages that use conventional form/card markup. */
        .card, .panel, .content-card, .form-card {
            background: var(--card-bg);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
        }
        input:not([type="checkbox"]):not([type="radio"]),
        select, textarea {
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            background: var(--white);
            color: var(--navy-ink);
            transition: border-color var(--transition), box-shadow var(--transition);
        }
        input:not([type="checkbox"]):not([type="radio"]):focus,
        select:focus, textarea:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 4px rgba(10,31,110,.10);
            outline: none;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }

        .tag {
            display: inline-block;
            padding: 0.25rem 0.85rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .tag-teal   { background: #00bcd4; color: var(--white); }
        .tag-gold   { background: var(--gold); color: var(--navy-dark); }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>

    {{-- Navigation --}}
    @include('components.navbar')

    {{-- Page Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    <script>
        // No page-fade transitions — navigation is instant. This also fixes
        // the "stuck on a white screen" issue when using the browser Back
        // button: a bfcache-restored page is always shown again.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                document.body.style.opacity = '1';
                document.body.classList.remove('page-transition-leave');
            }
        });
    </script>


    {{-- ── Back to top (appears on long pages) ── --}}
    <button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
            onclick="window.scrollTo({top:0,behavior:'smooth'});">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="22" height="22"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
    </button>
    <style>
        #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
        @media (max-width: 1024px){#back-to-top-btn{right:auto;left:20px;bottom:20px;}}
        #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    </style>
    <script>
        (function () {
            var btn = document.getElementById('back-to-top-btn');
            if (!btn) return;
            function toggleBackToTop() {
                btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
            }
            window.addEventListener('scroll', toggleBackToTop, { passive: true });
            toggleBackToTop();
        })();
    </script>

    @stack('scripts')

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
