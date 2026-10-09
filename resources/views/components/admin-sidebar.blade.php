{{--
    components/admin-sidebar.blade.php

        Shared look for the Admin sidebar across every admin page:

            · flat, edge-aligned navigation panel
            · always-visible sections with labels and counts
            · a dark navy pill on the active item

    Included after each page's own stylesheet, so these rules win.
    Each page keeps its existing markup; only the presentation changes.
--}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    :root {
        --admin-sidebar-width: 238px;
        --admin-topbar-height: 66px;
        --admin-border: #e5e7eb;
        --admin-navy: #123b7a;
        --admin-text: #111111;
        --admin-muted: #6b7280;
        --admin-hover: #f7f8fa;
        --admin-font: 'Inter', sans-serif;

        /* Shared aliases used by page-level admin styles. */
        --navy: #123b7a;
        --navy-light: #123b7a;
        --navy-pale: #f7f8fa;
        --title: #123b7a;
        --blue: #123b7a;
        --blue-light: #f7f8fa;
        --blue-soft: #f7f8fa;
        --soft: #f7f8fa;
        --navy-deep: #111111;
        --ink: #111111;
        --text: #111111;
        --text-main: #111111;
        --text-sub: #6b7280;
        --muted: #6b7280;
        --line: #e5e7eb;
        --border: #e5e7eb;
        --border-light: #e5e7eb;
        --gold: #f9c21b;
        --yellow: #f9c21b;
        --yellow-dark: #f9c21b;
        --yellow-text: #111111;
        --yellow-soft: #f7f8fa;
        --gold-dark: #123b7a;
        --bg: #f7f8fa;
        --surface: #f7f8fa;
        --white: #ffffff;
        --cyan: #f9c21b;
        --thumb: #f7f8fa;
        --framework-navy: #123b7a;
        --framework-blue: #123b7a;
        --framework-gold: #f9c21b;
        --framework-text: #111111;
        --framework-muted: #6b7280;
        --framework-border: #e5e7eb;
        --framework-surface: #f7f8fa;
        --dash-navy: #111111;
        --dash-navy-deep: #111111;
        --dash-royal: #123b7a;
        --dash-gold: #f9c21b;
        --dash-gold-soft: #f7f8fa;
        --dash-canvas: #f7f8fa;
        --dash-ink: #111111;
        --dash-muted: #6b7280;
        --dash-line: #e5e7eb;
    }

    body:has(aside.shared-admin-sidebar) {
        background: #f7f8fa !important;
        color: #111111 !important;
    }

    body:has(aside.shared-admin-sidebar) .layout,
    body:has(aside.shared-admin-sidebar) main.main {
        background-color: #f7f8fa;
        color: #111111;
    }

    /* =========================================================
       SHARED ADMIN SIDEBAR
       ========================================================= */

        aside.shared-admin-sidebar {
        position: fixed !important;
        top: var(--admin-topbar-height) !important;
        left: 0 !important;
        bottom: 0 !important;

        width: var(--admin-sidebar-width) !important;
        height: calc(100vh - var(--admin-topbar-height)) !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 18px 12px 24px !important;

        background: #ffffff !important;
        border: 0 !important;
        border-right: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        box-shadow: none !important;

        overflow-x: hidden !important;
        overflow-y: auto !important;

        /* Hide scrollbar while keeping scrolling */
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;

        z-index: 900 !important;
        font-family: 'Inter', sans-serif !important;
    }

    aside.shared-admin-sidebar::-webkit-scrollbar {
        display: none !important;
    }


    /* Sidebar sections */

    aside.shared-admin-sidebar .sb-section {
        margin: 0 0 18px !important;
        padding: 0 !important;

        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        overflow: visible !important;
    }

    aside.shared-admin-sidebar .sb-section:last-child {
        margin-bottom: 0 !important;
    }

    /* Section heading */

    aside.shared-admin-sidebar .sb-section-hd {
        display: flex !important;
        align-items: center !important;

        padding: 7px 10px 8px !important;

        background: transparent !important;
        border: 0 !important;

        cursor: default !important;
        user-select: none;
    }

    aside.shared-admin-sidebar .sb-hd-left {
        display: block !important;
    }

    aside.shared-admin-sidebar .sb-section-label {
        display: block !important;

        color: var(--admin-muted) !important;

        font-size: 11px !important;
        font-weight: 600 !important;

        line-height: 1.3 !important;

        letter-spacing: .08em !important;
        text-transform: uppercase !important;
    }

    aside.shared-admin-sidebar .sb-lesson-count,
    aside.shared-admin-sidebar .sb-chevron {
        display: none !important;
    }

    /* Navigation items */

    aside.shared-admin-sidebar .sb-items,
    aside.shared-admin-sidebar .sb-section.open .sb-items {
        display: flex !important;
        flex-direction: column !important;

        gap: 3px !important;

        padding: 0 !important;
    }

    aside.shared-admin-sidebar .sb-item {
        display: flex !important;
        align-items: center !important;
        box-sizing: border-box !important;

        min-height: 40px;

        padding: 9px 12px !important;

        border: 0 !important;
        border-radius: 9px !important;

        background: transparent !important;
        color: var(--admin-text) !important;

        font-size: 13px !important;
        font-weight: 500 !important;
        line-height: 1.35 !important;

        text-decoration: none !important;

        transition:
            background .15s ease,
            color .15s ease,
            transform .15s ease;
    }

    aside.shared-admin-sidebar .sb-item:hover {
        background: var(--admin-hover) !important;
        color: var(--admin-navy) !important;
    }

    /* aside.shared-admin-sidebar .sb-item.active,
    aside.shared-admin-sidebar .sb-item.active:hover,
    aside.shared-admin-sidebar .sb-item.active:focus {
        background: #f4c430 !important;
        color: #071550 !important;
        font-weight: 700 !important;
    }  */

    /* ACTIVE ITEM */
    aside.shared-admin-sidebar .sb-item.active,
    aside.shared-admin-sidebar .sb-item.active:hover,
    aside.shared-admin-sidebar .sb-item.active:focus {
        position: relative !important;

        background: #f1f2f4 !important;
        color: #123b7a !important;
        font-weight: 600 !important;

        border-radius: 0 !important;
    }

    aside.shared-admin-sidebar .sb-item.active {
        margin-left: -12px !important;
        padding-left: 24px !important;
        margin-right: 0 !important;

        border-radius: 0 8px 8px 0 !important;
    }

    /* Yellow highlight accent at the left edge. */
    aside.shared-admin-sidebar .sb-item.active::before {
        content: "" !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #f9c21b !important;
        border-radius: 0 3px 3px 0 !important;
    }

    aside.shared-admin-sidebar .sb-item-text {
        color: inherit !important;
    }

    /* Main content offset for the shared fixed sidebar */

    body:has(aside.shared-admin-sidebar) .layout {
        display: block !important;
        min-height: calc(100vh - var(--admin-topbar-height));
    }

    body:has(aside.shared-admin-sidebar) .layout > .main,
    body:has(aside.shared-admin-sidebar) main.main {
        margin-left: var(--admin-sidebar-width) !important;

        width: calc(100% - var(--admin-sidebar-width)) !important;

        min-width: 0 !important;

        box-sizing: border-box !important;
    }

    /* Pages using a .wrap instead of .main */

    body:has(aside.shared-admin-sidebar) .wrap {
        margin-left: calc(var(--admin-sidebar-width) + 30px) !important;
        margin-right: 30px !important;
        max-width: none !important;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 1024px) {
        :root {
            --admin-sidebar-width: 0px;
        }

        aside.shared-admin-sidebar {
            display: none !important;
        }

        body:has(aside.shared-admin-sidebar) .layout > .main,
        body:has(aside.shared-admin-sidebar) main.main {
            margin-left: 0 !important;
            width: 100% !important;
        }

        body:has(aside.shared-admin-sidebar) .wrap {
            margin-left: 16px !important;
            margin-right: 16px !important;
        }
    }

    /* Shared page heading scale, matched to Stacking Frameworks. */
    main.main .framework-header h1,
    main.main .recognition-head h1,
    main.main .cert-heading h1,
    main.main .page-head > div > h1,
    main.main .page-head > div > h2,
    main.main .page-header .page-title,
    main.main .admin-dashboard .page-heading,
    main.main .user-detail-wrap .profile-main h1,
    .page-wrap .page-head > div > .page-heading,
    .wrap > .page-heading {
        margin: 0 0 6px;
        color: var(--admin-navy);
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        font-size: 32px;
        font-weight: 700;
        line-height: 1.15;
    }

    main.main .framework-header > div > p,
    main.main .recognition-head > p,
    main.main .cert-heading > p,
    main.main .page-head > div > p,
    main.main .page-head > div > .lead,
    main.main .page-header > div > .page-subtitle,
    main.main .admin-dashboard > .page-sub,
    main.main .user-detail-wrap .profile-main .profile-role,
    .page-wrap .page-head > div > .page-sub,
    .wrap > .page-sub {
        margin: 0;
        color: #68758c;
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
    }

    main.main.admin-dashboard .page-heading {
        margin-bottom: 6px;
    }
</style>

<aside class="sidebar shared-admin-sidebar" aria-label="Admin navigation">
    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Overview</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.dashboard') }}" class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="sb-item-text">Home</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">People &amp; Access</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.usermanagement') }}" class="sb-item {{ request()->routeIs('admin.usermanagement', 'admin.users.*') ? 'active' : '' }}"><span class="sb-item-text">User Management</span></a>
            <a href="{{ route('admin.facultycodes') }}" class="sb-item {{ request()->routeIs('admin.facultycodes') ? 'active' : '' }}"><span class="sb-item-text">Faculty Codes</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Courses &amp; Credentials</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.courses') }}" class="sb-item {{ request()->routeIs('admin.courses*') && ! request()->routeIs('admin.courses.enrollments') ? 'active' : '' }}"><span class="sb-item-text">Courses</span></a>
            <a href="{{ route('admin.enrollments') }}" class="sb-item {{ request()->routeIs('admin.enrollments*', 'admin.courses.enrollments') ? 'active' : '' }}"><span class="sb-item-text">Completion Verification</span></a>
            <a href="{{ route('admin.certificates') }}" class="sb-item {{ request()->routeIs('admin.certificates*') ? 'active' : '' }}"><span class="sb-item-text">Credentials</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Academic Structure</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.categories') }}" class="sb-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}"><span class="sb-item-text">Program Categories</span></a>
            <a href="{{ route('admin.pathways') }}" class="sb-item {{ request()->routeIs('admin.pathways*') ? 'active' : '' }}"><span class="sb-item-text">Learning Pathways</span></a>
            <a href="{{ route('admin.stacking-frameworks') }}" class="sb-item {{ request()->routeIs('admin.stacking-frameworks*') ? 'active' : '' }}"><span class="sb-item-text">Stacking Frameworks</span></a>
            <a href="{{ route('admin.academic-credit-recognition') }}" class="sb-item {{ request()->routeIs('admin.academic-credit-recognition*') ? 'active' : '' }}"><span class="sb-item-text">Legacy Credit Requests</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Reports &amp; Oversight</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.report') }}" class="sb-item {{ request()->routeIs('admin.report') ? 'active' : '' }}"><span class="sb-item-text">Report</span></a>
            <a href="{{ route('admin.audit-logs') }}" class="sb-item {{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}"><span class="sb-item-text">Audit Logs</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Communication</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.announcements') }}" class="sb-item {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}"><span class="sb-item-text">Announcements</span></a>
        </div>
    </div>
</aside>
<script>
    // Open the section containing the active item, and keep the collapse
    // behaviour working on pages whose own toggleSection() expects it.
    document.addEventListener('DOMContentLoaded', function () {
        var active = document.querySelector('.sidebar .sb-item.active');
        if (active) {
            var section = active.closest('.sb-section');
            if (section) section.classList.add('open');
        }

        // Any section with no active item still opens on click; if a page
        // has no toggleSection() of its own, wire a default one.
        if (typeof window.toggleSection !== 'function') {
            document.querySelectorAll('.sidebar .sb-section-hd').forEach(function (hd) {
                hd.addEventListener('click', function () {
                    var s = hd.closest('.sb-section');
                    if (s) s.classList.toggle('open');
                });
            });
        }
    });
</script>
