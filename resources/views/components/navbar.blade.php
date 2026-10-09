@if (request()->routeIs('Homepage', 'students.index'))
    <div class="landing-announcement-bar" role="region" aria-label="UpSkill announcement">
        <p>UpSkill microcredentials: focused learning for what’s next</p>
        <a href="{{ route('explore') }}">Explore microcredentials</a>
    </div>
@endif

<nav class="navbar navbar--public" id="navbar">
    <div class="container navbar__inner">

        {{-- Brand --}}
        <a href="{{ url('/') }}" class="navbar__brand">
            <span class="navbar__brand-logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
            <span class="navbar__brand-copy">
                <span class="navbar__brand-name"><span class="navbar__brand-up">UP</span><span class="navbar__brand-skill">Skill</span></span>
                <span class="navbar__brand-tagline">PSU Microcredentials</span>
            </span>
        </a>

        {{-- Desktop Nav Links --}}
        <div class="navbar__mobile-panel" id="navPanel"><ul class="navbar__links" id="navLinks">
            <li><a href="{{ url('/') }}"                  class="navbar__link {{ request()->is('/') ? 'active' : '' }}">Home</a></li>
            <li class="navbar__item--categories">
                <a href="{{ route('explore') }}" class="navbar__link {{ request()->routeIs('explore', 'public.courses.show') ? 'active' : '' }}">
                    Microcredentials<span class="navbar__dropdown-indicator" aria-hidden="true"></span>
                </a>
                @if(count($publicCourseCategories ?? []))
                    <ul class="navbar__category-menu" aria-label="Microcredential categories">
                        @foreach($publicCourseCategories as $categoryItem)
                            <li>
                                <a class="navbar__category-link {{ request()->routeIs('explore') && request('category') === $categoryItem ? 'active' : '' }}"
                                   href="{{ route('explore', ['category' => $categoryItem]) }}">
                                    {{ $categoryItem }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
            <li><a href="{{ request()->is('/') ? '#how-it-works' : url('/#how-it-works') }}" data-nav-target="how-it-works" class="navbar__link">How it works</a></li>
            <li><a href="{{ url('/announcements') }}" data-nav-target="announcements" class="navbar__link {{ request()->is('announcements') ? 'active' : '' }}">Announcements</a></li>
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
.navbar--public {
    background: linear-gradient(180deg, #041aa0 0%, #030351 100%);
    border-bottom-color: rgba(255,255,255,.16);
    box-shadow: 0 8px 24px rgba(3,3,81,.28);
}
.navbar--public .navbar__brand-name,
.navbar--public .navbar__brand-tagline,
.navbar--public .navbar__brand-up { color: #fff; }
.navbar--public .navbar__link,
.navbar--public .navbar__action-link { color: rgba(255,255,255,.9); }
.navbar--public .navbar__link:hover,
.navbar--public .navbar__link.active,
.navbar--public .navbar__action-link:hover {
    color: #fff;
    background: transparent;
}
.navbar--public .navbar__link { position: relative; }
.navbar--public .navbar__link::after {
    position: absolute;
    right: 0.8rem;
    bottom: 0.06rem;
    left: 0.8rem;
    height: 3px;
    border-radius: 2px;
    background: var(--gold);
    content: "";
    opacity: 0;
    transform: scaleX(.72);
    transition: opacity var(--transition), transform var(--transition);
}
.navbar--public .navbar__link:hover::after,
.navbar--public .navbar__link.active::after { opacity: .9; transform: scaleX(1); }
.navbar--public .navbar__hamburger span { background: #fff; }

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
.navbar__brand-copy { display: flex; flex-direction: column; gap: 1px; }
.navbar__brand-name {
    display: block;
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.25rem;
    color: var(--white);
    line-height: 1;
    letter-spacing: 0.04em;
}
.navbar__brand-tagline {
    color: rgba(255,255,255,.8);
    font-size: .62rem;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: .025em;
    white-space: nowrap;
}
.navbar__brand-up { color: inherit; }
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
.navbar__item--categories { position: relative; }
.navbar__dropdown-indicator {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin: 0 0 3px 8px;
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: rotate(45deg);
    transition: transform var(--transition);
}
.navbar__item--categories:hover .navbar__dropdown-indicator,
.navbar__item--categories:focus-within .navbar__dropdown-indicator {
    transform: translateY(3px) rotate(225deg);
}
.navbar__category-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1100;
    display: grid;
    min-width: 220px;
    max-height: min(60vh, 420px);
    gap: 2px;
    overflow-y: auto;
    margin: 0;
    padding: 8px;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    background: rgba(3,3,81,.88);
    box-shadow: 0 14px 32px rgba(3,3,81,.22);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);
    list-style: none;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: opacity .18s ease, transform .18s ease, visibility .18s;
}
.navbar__item--categories:hover .navbar__category-menu,
.navbar__item--categories:focus-within .navbar__category-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.navbar__category-link {
    display: block;
    padding: 9px 12px;
    border-radius: 8px;
    color: rgba(255,255,255,.9);
    font-size: .88rem;
    font-weight: 600;
    white-space: nowrap;
}
.navbar__category-link:hover,
.navbar__category-link.active {
    background: rgba(246,203,59,.14);
    color: var(--gold-light);
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
.navbar__enroll-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50px;
    background: var(--gold);
    color: var(--navy);
    font-size: 0.95rem;
    font-weight: 700;
    padding: 0.5rem 1.4rem;
}
.navbar__enroll-btn:hover {
    background: var(--gold-light);
    color: var(--navy);
    box-shadow: 0 6px 18px rgba(255,213,1,0.4);
}

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
    .navbar__mobile-panel .navbar__item--categories { width: 100%; }
    .navbar__mobile-panel .navbar__category-menu {
        position: static;
        display: grid;
        max-height: none;
        gap: 0;
        margin: 0 0 0.25rem 0.8rem;
        padding: 0 0 0 0.65rem;
        border: 0;
        border-left: 1px solid rgba(255,255,255,.2);
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        opacity: 1;
        visibility: visible;
        transform: none;
    }
    .navbar__mobile-panel .navbar__category-link {
        padding: 0.55rem 0.75rem;
        border-radius: 10px;
        font-size: .9rem;
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
