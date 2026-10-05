{{--
    components/responsive.blade.php — shared mobile/tablet responsiveness layer.
    Included before </body> on every page. Purely additive: all rules live in
    media queries (or are harmless globals), so desktop rendering is unchanged.

    Covers both layout families found across the app:
      A) Student/Faculty pages: .layout grid (264px sidebar) + sticky aside.sidebar
      B) Admin pages: fixed aside.sidebar (var --sidebar-w) + .main margin-left
    On <=1024px the sidebar becomes an off-canvas drawer opened by a floating
    menu button (injected by the script below — no markup changes needed).
--}}
<style>
    /* Student page titles use the Enrolled Courses type scale across pages. */
    .student-page-heading h1,
    .student-page-heading h2 {
        margin: 0 0 6px;
        color: #13176b;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .student-page-heading--inverse h1,
    .student-page-heading--inverse h2 { color:#fff; }
    .student-page-heading > p:not(.eyebrow),
    .student-page-heading.page-header > p:not(.eyebrow),
    .student-page-heading > div > p:not(.eyebrow) {
        margin: 0 0 28px;
        color: #6b7280;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 15px;
        font-weight: 400;
        line-height: 1.5;
    }
    .student-page-heading--inverse > p:not(.eyebrow),
    .student-page-heading--inverse > div > p:not(.eyebrow) { color:rgba(255,255,255,.88); }

    /* Faculty page titles match the dashboard's typography scale. */
    .faculty-sidebar-layout .faculty-page-heading .faculty-page-title,
    .page-shell .faculty-page-heading .faculty-page-title {
        margin: 0 0 6px;
        color: #0e1a3a;
        font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        font-size: 27px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.04em;
    }
    .faculty-sidebar-layout .faculty-page-heading--inverse .faculty-page-title { color:#fff; }
    .faculty-sidebar-layout .faculty-page-heading .faculty-page-subtitle,
    .page-shell .faculty-page-heading .faculty-page-subtitle {
        margin: 0;
        color: #64748b;
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
    }
    .faculty-sidebar-layout .faculty-page-heading--inverse .faculty-page-subtitle { color:rgba(255,255,255,.9); }

    /* ── Global safety (all sizes) ─────────────────────────────────── */
    img, video { max-width: 100%; height: auto; }

    /* Grid and flex children default to min-width:auto, so they refuse to
       shrink below their content and push the page wider than the screen.
       Resetting it here fixes horizontal overflow app-wide. */
    .layout > *, .content-grid > *, .two-col > *, .charts-grid > *,
    .profile-grid > *, .grid2 > *, .grid3 > *, .row2 > *,
    .course-card > *, .quiz-row > *, .student-card > * { min-width: 0; }
    .main, main.main, .panel, .card, .quiz-card, .course-card { min-width: 0; }

    /* Inputs and selects must never be wider than the card holding them. */
    input, select, textarea { max-width: 100%; }

    /* ── iPhone-style floating menu pill (hidden on desktop) ────────── */
    .nav-toggle {
        display: none; position: fixed; right: 16px; bottom: 16px; z-index: 1600;
        width: 54px; height: 54px; border-radius: 50%; border: none; cursor: pointer;
        background: linear-gradient(160deg, #1a2190 0%, #13176b 100%);
        color: #fff; font-size: 23px; line-height: 1;
        align-items: center; justify-content: center;
        box-shadow: 0 10px 26px rgba(10, 15, 60, .42);
        transition: transform .42s cubic-bezier(.34, 1.15, .5, 1), box-shadow .22s ease;
    }
    .nav-toggle:active { transform: scale(.92); }

    /* While a modal drawer is open the pill would sit on top of its footer
       buttons (Save / Cancel). Slide it across to the left instead of
       hiding it, so the menu stays reachable. Uses transform rather than
       animating `left` so it stays on the compositor and reads smoothly. */
    body.modal-is-open .nav-toggle {
        transform: translateX(calc(var(--pill-travel, 0px) * -1));
    }
    body.modal-is-open .nav-toggle:active {
        transform: translateX(calc(var(--pill-travel, 0px) * -1)) scale(.92);
    }
    body.modal-is-open .nav-panel {
        transform: translateY(16px) scale(.95);
        opacity: 0; visibility: hidden;
    }
    /* Back-to-top already moves to the left edge on tablets — fade it out so
       the two don't overlap once the pill arrives. */
    body.modal-is-open #back-to-top-btn,
    body.modal-is-open .back-to-top,
    body.modal-is-open #shared-back-to-top {
        opacity: 0; pointer-events: none;
    }
    #back-to-top-btn, .back-to-top, #shared-back-to-top { transition: opacity .22s ease; }

    .nav-panel {
        position: fixed; right: 16px; bottom: 84px; z-index: 1500;
        width: min(300px, calc(100vw - 32px));
        max-height: min(70vh, 560px); overflow-y: auto;
        background: linear-gradient(165deg, #101a63 0%, #0c0f4d 100%);
        border: 1px solid rgba(127, 233, 227, .20);
        border-radius: 26px;
        padding: 14px;
        box-shadow: 0 22px 50px rgba(2, 6, 32, .55), 0 2px 10px rgba(2, 6, 32, .4);
        opacity: 0; visibility: hidden;
        transform: translateY(16px) scale(.95);
        transform-origin: bottom right;
        transition: opacity .28s ease, transform .3s cubic-bezier(.34, 1.4, .5, 1),
                    visibility 0s linear .28s;
    }
    .nav-panel.show {
        opacity: 1; visibility: visible;
        transform: translateY(0) scale(1);
        transition: opacity .26s ease, transform .34s cubic-bezier(.34, 1.56, .5, 1),
                    visibility 0s;
    }
    .nav-panel a {
        display: flex; align-items: center; gap: 13px;
        padding: 11px 13px; border-radius: 15px;
        color: rgba(255, 255, 255, .92);
        font-weight: 700; font-size: 15px; line-height: 1.25;
        text-decoration: none; white-space: normal;
        transition: background .18s ease, color .18s ease;
    }
    .nav-panel a:hover { background: rgba(255, 255, 255, .09); color: #fff; }
    .nav-panel a.active, .nav-panel a.is-active { background: var(--gold, #dba617); color: #0c0f4d; }
    .nav-panel a svg { width: 22px; height: 22px; flex-shrink: 0; }
    .nav-panel .side-icon-box { width: 34px; height: 34px; }
    /* Admin sidebar sections inside the pill */
    .nav-panel .sb-section-hd { display: none; }
    .nav-panel .sb-section > div { display: block !important; }
    .nav-panel .sb-lesson-count { display: none; }

    /* ── Unread notification dot, injected into every page's bell ────── */
    .notif-dot {
        position: absolute; top: 5px; right: 5px;
        width: 10px; height: 10px; border-radius: 50%;
        background: #ef4444; border: 2px solid #fff;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, .18);
        pointer-events: none;
    }
    .faculty-auth-topbar .notif-dot{background:#ef4444;box-shadow:0 0 0 2px rgba(239,68,68,.18);}
    .student-auth-topbar .notif-dot{background:#f4c430;box-shadow:0 0 0 2px rgba(244,196,48,.24);}
    /* The bell is a circle on every page — keep it that way everywhere. */
    a.notification-btn, a.icon-circle.notification-btn { border-radius: 50% !important; }

    /* ── Shared back-to-top (injected below when a page has none) ───── */
    #shared-back-to-top {
        position: fixed; right: 26px; bottom: 26px; z-index: 1600;
        width: 48px; height: 48px; border-radius: 50%; border: none;
        background: #13176b; color: #fff; cursor: pointer;
        display: none; align-items: center; justify-content: center;
        box-shadow: 0 10px 22px rgba(19, 23, 107, .35);
        transition: transform .15s ease, background .2s ease;
    }
    #shared-back-to-top:hover { background: var(--gold, #dba617); transform: translateY(-3px); }
    #shared-back-to-top svg { width: 22px; height: 22px; }

    /* ── Tablets & small laptops (<=1024px) ────────────────────────── */
    @media (max-width: 1024px) {
        /* Keep the right corner clear for the floating menu — back-to-top goes left */
        #back-to-top-btn, .back-to-top, #shared-back-to-top { right: auto !important; left: 20px !important; bottom: 20px !important; }

        /* Both layout families collapse to a single column */
        .layout { grid-template-columns: 1fr !important; height: auto !important; }

        /* Admin navigation moves into the floating menu at this breakpoint.
           Student and faculty sidebars stay visible as horizontal rows and
           use their own topbar toggle buttons. */
        aside.sidebar, aside[class~="sidebar"] { display: none !important; }
        .nav-toggle { display: flex; }

        /* Admin pages indent .main by the sidebar width — remove it */
        .main, main.main { margin-left: 0 !important; padding-left: 16px !important; padding-right: 16px !important; }

        /* Hide admin header links because the floating menu contains them. */
        header.topbar .nav-pills,
        nav.topbar .nav-pills { display: none !important; }

        /* Course player side navigation stacks above the content */
        /* Stacks above the content on small screens. Keep overflow hidden:
           the container's 22px radius is what clips the navy header and the
           quiz row, and overflow:visible let them render square corners
           straight over it. max-height:none already removes the need to
           scroll, so nothing is cut off. */
        aside.course-nav {
            position: static !important;
            max-height: none !important;
            overflow: hidden !important;
            border-radius: 22px !important;
        }

        /* Two-column content areas stack */
        .content-grid, .two-col, .hero, .panels-row, .path-panels, .comp-columns,
        .profile-grid, .top-row, .bottom-row, .charts-grid, .grid2, .row2,
        .about-row, .am-row, .grid, .analytics-grid, .dash-grid {
            grid-template-columns: 1fr !important;
        }

        /* Stat / info tiles go 2-up */
        .stat-cards, .stats-grid, .stats, .grid3, .info-grid,
        .welcome-stats-strip, .badges-grid, .badges-mini-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    /* ── Phones (<=768px) ──────────────────────────────────────────── */
    @media (max-width: 768px) {
        /* Profile cards: shrink and wrap, but do NOT force a centred column.
           .profile-card is used only by Faculty_Profile; centring it here is
           what stopped that page from matching Student_Profile's layout. */
        .profile-card { padding: 22px 18px !important; gap: 18px !important; flex-wrap: wrap !important; }
        .identity { min-width: 0 !important; }
        .identity h3 { font-size: 22px !important; }
        .id-item { word-break: break-word; }
        /* Inert where .identity-grid / .about-row are flex; kept for any page
           still using the old grid version of these rows. */
        .identity-grid { gap: 10px 18px !important; }
        .about-row { gap: 2px !important; }

        /* Page headers: ONE row. Wrapping pushed the icon cluster onto a
           second line and left a dead band of navy under the wordmark. The
           parts are shrunk instead so brand + icons fit across a phone. */
        header.topbar, nav.topbar {
            flex-wrap: nowrap !important;
            justify-content: space-between !important;
            padding: 10px 12px !important;
            gap: 8px !important;
        }
        header.topbar .brand, nav.topbar .brand {
            gap: 9px !important; min-width: 0 !important; flex: 0 1 auto !important;
        }
        header.topbar .brand h1, nav.topbar .brand h1 {
            font-size: 17px !important; letter-spacing: .5px !important;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        header.topbar .brand .logo, nav.topbar .brand .logo,
        header.topbar .brand-logo, nav.topbar .brand-logo {
            width: 34px !important; height: 34px !important; flex-shrink: 0 !important;
        }
        header.topbar .icon-cluster, nav.topbar .icon-cluster,
        header.topbar .topbar-right, nav.topbar .topbar-right {
            gap: 7px !important; flex-shrink: 0 !important; margin-left: 0 !important;
        }
        header.topbar .icon-circle, nav.topbar .icon-circle,
        header.topbar .avatar-btn, nav.topbar .avatar-btn {
            width: 34px !important; height: 34px !important; flex-shrink: 0 !important;
        }
        header.topbar .icon-circle svg, nav.topbar .icon-circle svg,
        header.topbar .avatar-btn svg, nav.topbar .avatar-btn svg {
            width: 17px !important; height: 17px !important;
        }
        header.topbar .btn-logout, nav.topbar .btn-logout,
        header.topbar form button, nav.topbar form button {
            padding: 7px 10px !important; font-size: 11.5px !important;
            white-space: nowrap; flex-shrink: 0;
        }
        /* Nav pills and search drop to their own full-width row below. */
        header.topbar .search-box, nav.topbar .search-box,
        header.topbar .search-wrap, nav.topbar .search-wrap { display: none !important; }
        header.topbar .nav-pills, nav.topbar .nav-pills { display: none !important; }

        /* Form rows & small grids stack */
        .field-grid, .field-row, .form-row, .set-row, .quick-grid,
        .identity-grid, .course-grid, .login-card, .register-card {
            grid-template-columns: 1fr !important;
        }

        /* Card grids never overflow: columns may shrink to full width */
        .courses-grid, .cert-grid, .students-grid, .steps-grid {
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 250px), 1fr)) !important;
        }

        /* ── Date inputs overflowing their card ──────────────────────────
           Mobile Safari gives input[type=date] an intrinsic width from its
           native picker and ignores width:100% unless the default appearance
           is switched off and min-width is unlocked. Without this the Date
           of Birth field renders wider than the fields above and below it
           and spills past the panel edge. */
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"] {
            -webkit-appearance: none !important;
            appearance: none !important;
            min-width: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        /* iOS centres the native value; match the other text fields. */
        input[type="date"]::-webkit-date-and-time-value { text-align: left !important; }

        /* ── Skill picker trapped inside the sliding modal ───────────────
           .modal-panel animates with transform, and a transformed element
           becomes the containing block for position:fixed descendants. So
           the picker was being centred on the PANEL while still sizing
           itself with vh (the viewport), making it taller than its own
           container — which clipped the search box off the top and the
           Cancel / Save buttons off the bottom.

           Sizing it in % resolves against that same containing block, so it
           fits whatever it is actually anchored to. */
        .skillpick-pop {
            top: 50% !important;
            left: 50% !important;
            width: calc(100% - 24px) !important;
            max-width: none !important;
            height: auto !important;
            max-height: 78% !important;
            padding: 16px 14px !important;
            border-radius: 16px !important;
        }
        .skillpick-hd { margin-bottom: 8px !important; }
        .skillpick-hd strong { font-size: 14px !important; }
        .skillpick-hd span  { font-size: 11px !important; }
        .skillpick-search   { margin-bottom: 8px !important; }
        .skillpick-search input { padding: 8px 12px 8px 32px !important; font-size: 13px !important; }
        .skillpick-list     { gap: 6px !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
        .skillpick-group-hd { margin: 10px 0 6px 4px !important; }
        /* Smaller option rows so more of the list is visible at once. */
        .skillpick-opt {
            padding: 8px 12px !important;
            font-size: 13.5px !important;
            border-radius: 999px !important;
        }
        .skillpick-opt input[type="checkbox"] {
            width: 18px !important; height: 18px !important; flex-shrink: 0 !important;
        }
        /* Cancel / Save must never be squeezed out by a long list. */
        .skillpick-ft {
            margin-top: 10px !important; padding-top: 10px !important;
            flex-shrink: 0 !important;
        }
        .skillpick-btn { flex: 1 1 0; padding: 10px 12px !important; }

        /* Wide tables scroll sideways instead of breaking the page */
        main table, .main table, .page table {
            display: block; overflow-x: auto; -webkit-overflow-scrolling: touch;
        }

        /* Horizontal media cards stack rather than wrap. Wrapping scattered
           the thumbnail, meta and buttons into an unreadable jumble on
           Faculty_dashboard / Student_dashboard, which define no mobile rule
           of their own. Vertical cards (Explore, Homepage) already use
           flex-direction:column, so this is a no-op for them. */
        .course-card { flex-direction: column; align-items: stretch; flex-wrap: wrap; }
        .course-card > .thumb { width: 100% !important; height: 140px !important; }
        .course-card > .course-info { width: 100%; min-width: 0; }
        .course-card .meta-item { text-align: left; }
    }

    /* ── Small phones (<=520px) ────────────────────────────────────── */
    @media (max-width: 520px) {
        .stat-cards, .stats-grid, .grid3, .info-grid,
        .welcome-stats-strip, .badges-grid, .badges-mini-grid {
            grid-template-columns: 1fr !important;
        }

        /* The dashboard stat tiles stay 2x2 all the way down. Stacking them
           1-up pushed "Hours Enrolled" below the fold on a phone, so the
           at-a-glance summary stopped being at a glance. Four short tiles
           fit two-up comfortably once the padding and type scale down. */
        .stats {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
        .stats .stat-card {
            padding: 18px 10px !important;
            min-height: 0 !important;
        }
        .stats .stat-top      { gap: 6px !important; margin-bottom: 8px !important; }
        .stats .stat-top svg  { width: 26px !important; height: 26px !important; }
        .stats .stat-top .num { font-size: 30px !important; }
        .stats .stat-card .label {
            font-size: 13px !important;
            line-height: 1.25 !important;
        }
    }
</style>
<script>
    // iPhone-style floating menu: clone the page's sidebar links into a
    // rounded pill panel (like iOS panels). Original sidebar stays in the
    // DOM for desktop; on small screens it is hidden via CSS.
    var UNREAD_NOTIFICATIONS = {{ (int) ($unreadNotificationCount ?? 0) }};

    document.addEventListener('DOMContentLoaded', function () {
        buildBackToTop();
        buildNavPill();
        watchModals();
        markUnreadBell();
    });

    /* ── Unread dot ─────────────────────────────────────────────────
       Pages wire their bells differently — some render a badge, most
       render none. Add one wherever it's missing so the indicator is
       consistent, and strip it when nothing is unread. */
    function markUnreadBell() {
        var bells = document.querySelectorAll(
            'a[href$="/notifications"], a.notification-btn, .icon-cluster a[title="Notifications"]'
        );

        Array.prototype.forEach.call(bells, function (bell) {
            var existing = bell.querySelector('.notification-badge, .notif-dot');

            if (UNREAD_NOTIFICATIONS > 0) {
                if (existing) return;
                if (window.getComputedStyle(bell).position === 'static') {
                    bell.style.position = 'relative';
                }
                var dot = document.createElement('span');
                dot.className = 'notif-dot';
                dot.setAttribute('aria-hidden', 'true');
                bell.appendChild(dot);
                bell.setAttribute('title', 'Notifications (unread)');
            } else if (existing) {
                existing.remove();
            }
        });
    }

    /* ── Modal awareness ────────────────────────────────────────────
       Flags <body> while any drawer/modal is visible so the floating
       pill can slide out of the way of its footer buttons. Watches
       class changes rather than hooking each page's own modal JS. */
    function watchModals() {
        var overlays = document.querySelectorAll('.modal-overlay, .am-overlay, .overlay');
        if (!overlays.length) return;

        function isOpen(el) {
            var hasClass = el.classList.contains('open') || el.classList.contains('show');

            if (hasClass) { el.setAttribute('data-class-driven', '1'); return true; }

            // Overlay is known to open/close via a class: trust the class.
            // Checking computed style here would read the element as still
            // visible during its 300ms fade-out and latch the flag on.
            if (el.getAttribute('data-class-driven') === '1') return false;

            // Overlays with no toggle class (e.g. the About Me form) are
            // open whenever they are actually painted.
            var cs = window.getComputedStyle(el);
            return cs.display !== 'none' && cs.visibility !== 'hidden';
        }

        function measure() {
            // clientWidth excludes the scrollbar; 100vw does not.
            var travel = document.documentElement.clientWidth - 86;
            document.body.style.setProperty('--pill-travel', Math.max(0, travel) + 'px');
        }

        function sync() {
            measure();
            var open = Array.prototype.some.call(overlays, isOpen);
            document.body.classList.toggle('modal-is-open', open);
        }

        window.addEventListener('resize', measure);

        var observer = new MutationObserver(sync);
        Array.prototype.forEach.call(overlays, function (el) {
            observer.observe(el, { attributes: true, attributeFilter: ['class', 'style'] });
        });

        sync();
    }

    /* ── Back to top ────────────────────────────────────────────────
       21 pages paste this button inline with id="back-to-top-btn".
       Duplicate ids break getElementById, so only inject when the page
       has none — which covers the pages that were missing it entirely. */
    function buildBackToTop() {
        if (document.getElementById('back-to-top-btn')) return;

        var top = document.createElement('button');
        top.id = 'shared-back-to-top';
        top.type = 'button';
        top.title = 'Back to top';
        top.setAttribute('aria-label', 'Back to top');
        top.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
                        'stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>';
        top.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        document.body.appendChild(top);

        function toggleTop() {
            var y = window.scrollY || document.documentElement.scrollTop;
            top.style.display = y > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleTop, { passive: true });
        toggleTop();
    }

    /* ── Floating nav pill ─────────────────────────────────────────── */
    function buildNavPill() {
        // Admin pages use aside.sidebar; student and faculty pages use their
        // own class names. All three feed the floating menu on small screens.
        var sidebar = document.querySelector('aside.sidebar, aside.student-sidebar, aside.faculty-sidebar');

        // Student and faculty sidebars use their topbar hamburger on every
        // page. Do not create a second floating menu for those sidebars.
        if (sidebar && (
            (sidebar.matches('aside.student-sidebar') && document.getElementById('sidebar-toggle')) ||
            (sidebar.matches('aside.faculty-sidebar') && document.getElementById('faculty-sidebar-toggle'))
        )) {
            return;
        }

        // Pages without a sidebar (Admin_Profile, Faculty_Students,
        // Student_Course_Enrollment, Explore_Courses, ...) previously got no
        // menu at all. Fall back to the topbar's own links so every page
        // still has navigation on small screens.
        // Only real navigation entries. The topbar's icon cluster (bell,
        // avatar) is deliberately excluded — those already sit in the header
        // and just added noise to the menu.
        var links = sidebar
            ? sidebar.querySelectorAll('a')
            : document.querySelectorAll('header.topbar .nav-pills a, nav.topbar .nav-pills a');

        if (!links.length) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nav-toggle';
        btn.setAttribute('aria-label', 'Open menu');
        btn.innerHTML = '&#9776;';

        var panel = document.createElement('nav');
        panel.className = 'nav-panel';
        panel.setAttribute('aria-label', 'Menu');

        // Clone the links into the pill (forms are skipped: a cloned logout
        // form would lose its CSRF token context). Anything without a text
        // label is an icon button, not a menu entry, so it is left out.
        Array.prototype.forEach.call(links, function (el) {
            if (!el.textContent.trim()) return;

            var clone = el.cloneNode(true);
            clone.removeAttribute('id');
            clone.classList.remove('sb-pill');
            clone.classList.remove('icon-circle');
            panel.appendChild(clone);
        });

        document.body.appendChild(panel);
        document.body.appendChild(btn);

        function closeNav() {
            panel.classList.remove('show');
            btn.innerHTML = '&#9776;';
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = panel.classList.toggle('show');
            btn.innerHTML = open ? '&times;' : '&#9776;';
        });
        panel.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', closeNav);
        });
        document.addEventListener('click', function (e) {
            if (panel.classList.contains('show') &&
                !panel.contains(e.target) && e.target !== btn) {
                closeNav();
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1024) closeNav();
        });
    }
</script>


<!-- Add near the end of resources/views/components/responsive.blade.php -->
<script>
    (function () {
        var approvedForms = new WeakSet();
        var activeForm = null;
        var activeSubmitter = null;
        var intervalId = null;
        var previousFocus = null;

        var styles = document.createElement('style');
        styles.textContent = [
            '.delete-confirm-overlay{position:fixed;inset:0;z-index:100000;display:none;place-items:center;padding:20px;background:rgba(2,7,25,.72);backdrop-filter:blur(5px)}',
            '.delete-confirm-overlay.is-open{display:grid}',
            '.delete-confirm-dialog{width:min(100%,440px);padding:28px;border:1px solid rgba(255,255,255,.12);border-radius:20px;background:#101d56;color:#f7f8ff;box-shadow:0 24px 70px rgba(0,0,0,.45);font-family:Inter,Arial,sans-serif}',
            '.delete-confirm-dialog h2{margin:0 0 10px;color:#f3b500;font-size:22px;font-weight:800}',
            '.delete-confirm-dialog p{margin:0;color:#bac5e2;font-size:15px;line-height:1.55}',
            '.delete-confirm-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:26px}',
            '.delete-confirm-actions button{min-height:42px;padding:10px 18px;border-radius:10px;font:700 14px Inter,Arial,sans-serif;cursor:pointer}',
            '.delete-confirm-cancel{border:1px solid #536392;background:#17265f;color:#e7ebf7}',
            '.delete-confirm-submit{border:1px solid #e3a900;background:#f3b500;color:#111b51}',
            '.delete-confirm-submit:disabled{border-color:#596484;background:#596484;color:#d4d9e7;cursor:not-allowed;opacity:.85}',
            '.delete-confirm-actions button:focus-visible{outline:3px solid #ffd84a;outline-offset:3px}',
            '@media(prefers-reduced-motion:no-preference){.delete-confirm-dialog{animation:delete-confirm-in .16s ease-out}@keyframes delete-confirm-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}}'
        ].join('');
        document.head.appendChild(styles);

        var overlay = document.createElement('div');
        overlay.className = 'delete-confirm-overlay';
        overlay.innerHTML = '<section class="delete-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="delete-confirm-title" aria-describedby="delete-confirm-message" tabindex="-1">' +
            '<h2 id="delete-confirm-title"></h2>' +
            '<p id="delete-confirm-message"></p>' +
            '<div class="delete-confirm-actions">' +
                '<button type="button" class="delete-confirm-cancel">Cancel</button>' +
                '<button type="button" class="delete-confirm-submit"></button>' +
            '</div>' +
        '</section>';
        document.body.appendChild(overlay);

        var dialog = overlay.querySelector('.delete-confirm-dialog');
        var title = overlay.querySelector('#delete-confirm-title');
        var message = overlay.querySelector('#delete-confirm-message');
        var cancelButton = overlay.querySelector('.delete-confirm-cancel');
        var confirmButton = overlay.querySelector('.delete-confirm-submit');

        function destructiveKind(form, submitter) {
            if (!form) {
                return null;
            }

            var action = form.getAttribute('action') || window.location.href;
            var pathname;
            try {
                pathname = new URL(action, window.location.href).pathname.toLowerCase();
            } catch (error) {
                pathname = action.toLowerCase();
            }

            if (/(^|\/)(revoke)(\/|$)/.test(pathname)) {
                return 'revoke';
            }
            if (/(^|\/)(delete|destroy)(\/|$)/.test(pathname)) {
                return 'delete';
            }

            var label = submitter
                ? [
                    submitter.textContent || '',
                    submitter.value || '',
                    submitter.getAttribute('title') || '',
                    submitter.getAttribute('aria-label') || ''
                ].join(' ').toLowerCase()
                : '';

            if (label.indexOf('unpublish') !== -1) {
                return 'unpublish';
            }
            if (/\brevoke\b/.test(label)) {
                return 'revoke';
            }
            return /\b(delete|remove)\b/.test(label) ? 'delete' : null;
        }

        function requiresTimer(form, kind) {
            if (kind === 'revoke' || kind === 'unpublish') {
                return true;
            }

            var action = form.getAttribute('action') || window.location.href;
            var pathname;
            try {
                pathname = new URL(action, window.location.href).pathname.toLowerCase();
            } catch (error) {
                pathname = action.toLowerCase();
            }

            // These two areas keep confirmation but do not use the delay.
            return !pathname.includes('/admin-facultycodes/')
                && !pathname.includes('/admin-announcements/');
        }

        function closeModal() {
            window.clearInterval(intervalId);
            intervalId = null;
            overlay.classList.remove('is-open');
            activeForm = null;
            activeSubmitter = null;
            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
            }
        }

        function submitApprovedAction() {
            if (!activeForm) {
                return;
            }

            var form = activeForm;
            var submitter = activeSubmitter;
            // The app's existing inline confirm() prompts are replaced by
            // this modal after the user confirms.
            form.removeAttribute('onsubmit');
            approvedForms.add(form);
            closeModal();

            if (submitter && submitter.form === form) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        }

        function openModal(form, submitter, kind) {
            if (activeForm) {
                return;
            }

            activeForm = form;
            activeSubmitter = submitter || null;
            previousFocus = document.activeElement;

            var isRevoke = kind === 'revoke';
            var isUnpublish = kind === 'unpublish';
            var actionLabel = isRevoke ? 'Revoke' : (isUnpublish ? 'Unpublish' : 'Delete');
            var delayed = requiresTimer(form, kind);

            title.textContent = isRevoke
                ? 'Confirm certificate revocation'
                : (isUnpublish ? 'Confirm course unpublishing' : 'Confirm deletion');
            message.textContent = isRevoke
                ? 'Are you sure you want to revoke this certificate? This action cannot be undone.'
                : (isUnpublish
                    ? 'This course will be hidden from new students and new enrollments will be blocked. Students already enrolled can continue learning and complete the course.'
                    : 'Are you sure you want to delete this item? This action cannot be undone.');

            confirmButton.disabled = delayed;
            confirmButton.textContent = delayed ? actionLabel + ' (3)' : actionLabel;
            overlay.classList.add('is-open');
            cancelButton.focus();

            if (delayed) {
                var endsAt = Date.now() + 3000;
                intervalId = window.setInterval(function () {
                    var remaining = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
                    if (remaining <= 0) {
                        window.clearInterval(intervalId);
                        intervalId = null;
                        confirmButton.disabled = false;
                        confirmButton.textContent = actionLabel;
                        return;
                    }
                    confirmButton.textContent = actionLabel + ' (' + remaining + ')';
                }, 100);
            }
        }

        document.addEventListener('click', function (event) {
            var target = event.target;
            var submitter = target && target.closest
                ? target.closest('button[type="submit"],button:not([type]),input[type="submit"]')
                : null;
            var form = submitter ? submitter.form : null;
            var kind = destructiveKind(form, submitter);

            if (!kind) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            openModal(form, submitter, kind);
        }, true);

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (approvedForms.has(form)) {
                approvedForms.delete(form);
                return;
            }

            var kind = destructiveKind(form, event.submitter);
            if (!kind) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            openModal(form, event.submitter || null, kind);
        }, true);

        cancelButton.addEventListener('click', closeModal);
        confirmButton.addEventListener('click', function () {
            if (!confirmButton.disabled) {
                submitApprovedAction();
            }
        });
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeModal();
            }
        });
        dialog.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeModal();
                return;
            }
            if (event.key === 'Tab') {
                var focusable = [cancelButton, confirmButton].filter(function (button) {
                    return !button.disabled;
                });
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    (confirmButton.disabled ? cancelButton : last).focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    })();
</script>



