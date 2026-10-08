@if (request()->routeIs('Homepage', 'students.index'))
    <div class="landing-announcement-bar" role="region" aria-label="UpSkill announcement">
        <p>UpSkill microcredentials: focused learning for what’s next</p>
        <a href="{{ request()->routeIs('Homepage') ? '#featured' : url('/explore') }}">Explore microcredentials</a>
    </div>
@endif

<nav class="navbar {{ request()->routeIs('Homepage', 'students.index', 'explore', 'public.courses.show', 'help', 'privacy', 'terms') ? 'navbar--light' : '' }}" id="navbar">
    <div class="container navbar__inner">

        {{-- Brand --}}
        <a href="{{ url('/') }}" class="navbar__brand">
            <span class="navbar__brand-logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
            <span class="navbar__brand-name"><span>UP</span><span class="navbar__brand-skill">Skill</span></span>
        </a>

        {{-- Desktop Nav Links --}}
        <div class="navbar__mobile-panel" id="navPanel"><ul class="navbar__links" id="navLinks">
            <li><a href="{{ url('/') }}"                  class="navbar__link {{ request()->is('/') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('explore') }}" class="navbar__link {{ request()->routeIs('explore', 'public.courses.show') ? 'active' : '' }}">Microcredentials</a></li>
            <li><a href="{{ request()->is('/') ? '#how-it-works' : url('/#how-it-works') }}" data-nav-target="how-it-works" class="navbar__link">How it works</a></li>
            <li><a href="{{ url('/announcements') }}" data-nav-target="announcements" class="navbar__link {{ request()->is('announcements') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/students') }}"          class="navbar__link {{ request()->is('students') ? 'active' : '' }}">Verify</a></li>
        </ul><div class="navbar__panel-divider"></div><div class="navbar__actions" id="navActions">
            <a href="{{ url('/login') }}"    class="navbar__action-link">Login</a>
            <a href="{{ url('/register') }}" class="btn btn-gold navbar__enroll-btn">Enroll Now</a>
        </div></div>

        {{-- Hamburger (mobile) --}}
        <button class="navbar__hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>

<style>
/* Landing page announcement strip */
.landing-announcement-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-height: 30px;
    padding: 0.35rem 1rem;
    background: #000;
    color: #fff;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.35;
}
.landing-announcement-bar p { margin: 0; }
.landing-announcement-bar a { color: #fff; text-decoration: underline; text-underline-offset: 2px; }
.landing-announcement-bar a:hover { color: var(--landing-gold, #f6cb3b); }

/* ── Navbar ── */
.navbar {
    --gold: #f6cb3b;
    --gold-light: #f6cb3b;
    background: #07143f;
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid rgba(255,255,255,.10);
    box-shadow: 0 8px 24px rgba(7,20,63,.28);
    backdrop-filter: blur(14px);
}
.navbar--light {
    background: var(--landing-white, #fff);
    border-bottom-color: #e5e8f0;
    box-shadow: 0 6px 20px rgba(7,20,63,.1);
}
.navbar--light .navbar__brand-name { color: var(--landing-royal-blue, #0927d8); }
.navbar--light .navbar__brand-logo { box-shadow: 0 0 0 3px rgba(9,39,216,.12); }
.navbar--light .navbar__link,
.navbar--light .navbar__action-link { color: var(--landing-navy, #07143f); }
.navbar--light .navbar__link { position: relative; }
.navbar--light .navbar__link:hover,
.navbar--light .navbar__link.active,
.navbar--light .navbar__action-link:hover {
    color: var(--landing-royal-blue, #0927d8);
    background: transparent;
}
.navbar--light .navbar__link::after {
    position: absolute;
    right: 0.8rem;
    bottom: 0.06rem;
    left: 0.8rem;
    height: 3px;
    border-radius: 2px;
    background: var(--landing-royal-blue, #0927d8);
    box-shadow: 0 0 6px rgba(9,39,216,.24);
    content: "";
    opacity: 0;
    transform: scaleX(.72);
    transition: opacity var(--transition), transform var(--transition);
}
.navbar--light .navbar__link:hover::after,
.navbar--light .navbar__link.active::after { opacity: .8; transform: scaleX(1); }
.navbar--light .navbar__hamburger span { background: var(--landing-royal-blue, #0927d8); }

.navbar .container {
    width: 100%;
    max-width: none;
    padding-left: 18px;
    padding-right: 18px;
}

.navbar__inner {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    min-height: 70px;
    padding: 0.72rem 0;
}

/* Brand */
.navbar__brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
    margin-right: 0;
    justify-self: flex-start;
}
.navbar__brand-logo {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    overflow: hidden;
    border-radius: 50%;
    background: transparent;
    box-shadow: 0 0 0 3px rgba(255,255,255,.12);
}
.navbar__brand-logo img { width: 100%; height: 100%; object-fit: contain; }
.navbar__brand-name {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.4rem;
    color: var(--white);
    letter-spacing: 0.04em;
}
.navbar__brand-skill { color: var(--gold-light); }

/* Links */
.navbar__mobile-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1 1 auto;
    gap: 0.4rem;
}

.navbar__links {
    display: flex;
    list-style: none;
    gap: clamp(0.6rem, 1.6vw, 1.6rem);
    margin: 0 auto;
    padding: 0;
    justify-content: center;
    flex: 1 1 auto;
}
.navbar__link {
    color: rgba(255,255,255,0.82);
    font-size: 1rem;
    font-weight: 500;
    padding: 0.42rem 0.8rem;
    border-radius: 10px;
    transition: color var(--transition), background var(--transition);
    white-space: nowrap;
}
.navbar__link:hover {
    color: var(--gold-light);
    background: rgba(246,203,59,0.12);
}
.navbar__link.active {
    color: var(--gold-light);
    background: transparent;
}

/* Actions */
.navbar__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
    margin-left: 0.4rem;
}
.navbar__action-link {
    color: rgba(255,255,255,0.85);
    font-size: 1rem;
    font-weight: 500;
    transition: color var(--transition);
    white-space: nowrap;
}
.navbar__action-link:hover { color: var(--gold-light); }
.navbar__enroll-btn { font-size: 0.95rem; padding: 0.5rem 1.4rem; }

/* Hamburger */
.navbar__hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    margin-left: auto;
}
.navbar__hamburger span {
    display: block;
    width: 24px;
    height: 2px;
    background: var(--white);
    border-radius: 2px;
    transition: transform 0.25s, opacity 0.25s;
}
.navbar__hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar__hamburger.open span:nth-child(2) { opacity: 0; }
.navbar__hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile floating panel — passthrough on desktop */
.navbar__mobile-panel { display: contents; }
.navbar__panel-divider { display: none; }

/* ── Responsive ── */
@media (max-width: 960px) {
    .navbar__links { gap: 0.4rem; }
    .navbar__link { font-size: 0.9rem; padding: 0.4rem 0.6rem; }
}

@media (max-width: 768px) {
    .landing-announcement-bar { flex-wrap: wrap; gap: 0.1rem 0.35rem; padding: 0.4rem 0.75rem; }
    .navbar__links   { display: none; }
    .navbar__actions { display: none; }
    .navbar__hamburger { display: flex; }

    /* iPhone-style floating panel */
    .navbar__mobile-panel {
        display: flex; flex-direction: column; align-items: stretch;
        position: absolute; top: calc(100% + 12px);
        left: 14px; right: 14px;
        background: linear-gradient(165deg, #101a63 0%, var(--navy-dark) 100%);
        border: 1px solid rgba(9,39,216,.2);
        border-radius: 26px;
        padding: 0.9rem 1.1rem 1.1rem;
        box-shadow: 0 18px 44px rgba(2, 6, 32, 0.55), 0 2px 8px rgba(2, 6, 32, 0.4);
        gap: 0.15rem;

        opacity: 0; visibility: hidden;
        transform: translateY(-14px) scale(0.97);
        transform-origin: top center;
        transition: opacity 0.28s ease, transform 0.28s cubic-bezier(0.34, 1.4, 0.5, 1),
                    visibility 0s linear 0.28s;
    }
    .navbar__mobile-panel.open {
        opacity: 1; visibility: visible;
        transform: translateY(0) scale(1);
        transition: opacity 0.28s ease, transform 0.32s cubic-bezier(0.34, 1.56, 0.5, 1),
                    visibility 0s;
    }

    /* Links stacked inside the pill */
    .navbar__mobile-panel .navbar__links {
        display: flex; flex-direction: column; gap: 0.15rem; width: 100%;
        list-style: none; margin: 0; padding: 0;
    }
    .navbar__mobile-panel .navbar__link {
        display: flex; align-items: center;
        padding: 0.75rem 0.9rem;
        border-radius: 14px;
        font-size: 1rem; font-weight: 600;
        color: rgba(255,255,255,0.9);
        transition: background 0.18s ease, color 0.18s ease;
    }
    .navbar__mobile-panel .navbar__link:hover { background: transparent; color: var(--gold-light); }
    .navbar__mobile-panel .navbar__link.active { background: transparent; color: var(--gold-light); }
    .navbar--light .navbar__mobile-panel .navbar__link:hover,
    .navbar--light .navbar__mobile-panel .navbar__link.active { color: var(--landing-royal-blue, #0927d8); }

    .navbar__panel-divider {
        display: block; height: 1px;
        background: rgba(255,255,255,0.12);
        margin: 0.55rem 0.2rem;
    }

    /* Actions sit side-by-side at the bottom of the pill */
    .navbar__mobile-panel .navbar__actions {
        display: flex; align-items: center; justify-content: center;
        gap: 1rem; width: 100%; padding-top: 0.35rem;
    }
    .navbar__mobile-panel .navbar__action-link { font-size: 1rem; font-weight: 600; }
    .navbar__mobile-panel .navbar__enroll-btn { padding: 0.6rem 1.6rem; }
}
</style>

<script>
    const hamburger = document.getElementById('hamburger');
    const navPanel  = document.getElementById('navPanel');

    hamburger.addEventListener('click', () => {
        const isOpen = navPanel.classList.toggle('open');
        hamburger.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen);
    });

    // Close the floating panel when a link inside it is tapped
    navPanel.querySelectorAll('a').forEach((a) => {
        a.addEventListener('click', () => {
            navPanel.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });

    const publicNavLinks = document.querySelectorAll('.navbar__link');
    const syncPublicNavState = () => {
        const target = window.location.hash.slice(1);
        if (!target) return;

        publicNavLinks.forEach((link) => link.classList.toggle(
            'active',
            link.dataset.navTarget === target
        ));
    };

    syncPublicNavState();
    window.addEventListener('hashchange', syncPublicNavState);
</script>
