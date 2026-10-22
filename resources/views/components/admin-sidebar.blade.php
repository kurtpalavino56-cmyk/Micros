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
        /* --admin-charcoal: #36454f; */--admin-charcoal: #f8fafc;
        --admin-navy: #123b7a;
        --admin-text: #334155;
        --admin-muted: #64748b;
        --admin-hover: #e2e8f0;
        --admin-active: #fff4c7;
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

        background: var(--admin-charcoal) !important;
        border: 0 !important;
        border-right: 1px solid #e2e8f0 !important;
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
        gap: 10px !important;

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

    aside.shared-admin-sidebar .sb-item-icon {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        color: inherit;
    }

    aside.shared-admin-sidebar .sb-item-alert-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        margin-left: auto;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, .16);
    }

    aside.shared-admin-sidebar .sb-item:hover {
        background: var(--admin-hover) !important;
        color: var(--admin-text) !important;
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

        background: var(--admin-active) !important;
        color: #1C3552 !important;
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
        color: #000;
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

    /* 32px page titles took two or three lines on a phone. */
    @media (max-width: 600px) {
        main.main .framework-header h1,
        main.main .recognition-head h1,
        main.main .cert-heading h1,
        main.main .page-head > div > h1,
        main.main .page-head > div > h2,
        main.main .page-header .page-title,
        main.main .admin-dashboard .page-heading,
        main.main.admin-dashboard .page-heading,
        .page-wrap .page-head > div > .page-heading,
        .wrap > .page-heading {
            font-size: 24px;
            line-height: 1.2;
        }
    }
</style>

@php
    $adminSidebarUser = auth()->user();
    $adminPageAccessService = app(\App\Services\AdminPageAccessService::class);
@endphp

@if ($adminSidebarUser?->isSubAdmin())
    <aside class="sidebar shared-admin-sidebar" aria-label="Admin navigation">
        @foreach ($adminPageAccessService->sections() as $section => $pages)
            @php
                $accessiblePages = collect($pages)->filter(
                    fn ($label, $page) => $page !== 'profile'
                        && $adminSidebarUser->canAccessAdminPage($page)
                        && $adminPageAccessService->routeForPage($page)
                );
            @endphp

            @if ($accessiblePages->isNotEmpty())
                <div class="sb-section open">
                    <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">{{ $section }}</span></div></div>
                    <div class="sb-items">
                        @foreach ($accessiblePages as $page => $label)
                            @php $pageRoute = $adminPageAccessService->routeForPage($page); @endphp
                            <a href="{{ route($pageRoute) }}" class="sb-item {{ request()->routeIs($pageRoute) ? 'active' : '' }}">
                                <span class="sb-item-text">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </aside>
@else
<aside class="sidebar shared-admin-sidebar" aria-label="Admin navigation">
    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Overview</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.dashboard') }}" class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/></svg><span class="sb-item-text">Home</span></a>        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">People &amp; Access</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.usermanagement') }}" class="sb-item {{ request()->routeIs('admin.usermanagement', 'admin.users.*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z"/></svg><span class="sb-item-text">User Management</span></a>
            <a href="{{ route('admin.facultycodes') }}" class="sb-item {{ request()->routeIs('admin.facultycodes') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M15.75 1.5a6.75 6.75 0 0 0-6.651 7.906c.067.39-.032.717-.221.906l-6.5 6.499a3 3 0 0 0-.878 2.121v2.818c0 .414.336.75.75.75H6a.75.75 0 0 0 .75-.75v-1.5h1.5A.75.75 0 0 0 9 19.5V18h1.5a.75.75 0 0 0 .53-.22l2.658-2.658c.19-.189.517-.288.906-.22A6.75 6.75 0 1 0 15.75 1.5Zm0 3a.75.75 0 0 0 0 1.5A2.25 2.25 0 0 1 18 8.25a.75.75 0 0 0 1.5 0 3.75 3.75 0 0 0-3.75-3.75Z" clip-rule="evenodd"/></svg><span class="sb-item-text">Faculty Codes</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Courses &amp; Credentials</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.courses') }}" class="sb-item {{ request()->routeIs('admin.courses*') && ! request()->routeIs('admin.courses.enrollments') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/></svg><span class="sb-item-text">Course Approvals</span>@if(($adminPendingCourseReviewCount ?? 0) > 0)<span class="sb-item-alert-dot" role="img" aria-label="Pending course reviews"></span>@endif</a>
            <a href="{{ route('admin.enrollments') }}" class="sb-item {{ request()->routeIs('admin.enrollments*', 'admin.courses.enrollments') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/></svg><span class="sb-item-text">Completion Verification</span>@if(($adminPendingCompletionReviewCount ?? 0) > 0)<span class="sb-item-alert-dot" role="img" aria-label="Pending completion verifications"></span>@endif</a>
            <a href="{{ route('admin.certificates') }}" class="sb-item {{ request()->routeIs('admin.certificates*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/><path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286.921.304 1.83.634 2.726.99v1.27a1.5 1.5 0 0 0-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.66a6.727 6.727 0 0 0 .551-1.607 1.5 1.5 0 0 0 .14-2.67v-.645a48.549 48.549 0 0 1 3.44 1.667 2.25 2.25 0 0 0 2.12 0Z"/><path d="M4.462 19.462c.42-.419.753-.89 1-1.395.453.214.902.435 1.347.662a6.742 6.742 0 0 1-1.286 1.794.75.75 0 0 1-1.06-1.06Z"/></svg><span class="sb-item-text">Credentials</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Academic Structure</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.categories') }}" class="sb-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3V6ZM3 15.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2.25Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3v-2.25Z" clip-rule="evenodd"/></svg><span class="sb-item-text">Program Categories</span></a>
            <a href="{{ route('admin.pathways') }}" class="sb-item {{ request()->routeIs('admin.pathways*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437ZM9 6a.75.75 0 0 1 .75.75V15a.75.75 0 0 1-1.5 0V6.75A.75.75 0 0 1 9 6Zm6.75 3a.75.75 0 0 0-1.5 0v8.25a.75.75 0 0 0 1.5 0V9Z" clip-rule="evenodd"/></svg><span class="sb-item-text">Learning Pathways</span></a>
            <a href="{{ route('admin.stacking-frameworks') }}" class="sb-item {{ request()->routeIs('admin.stacking-frameworks*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M5.566 4.657A4.505 4.505 0 0 1 6.75 4.5h10.5c.41 0 .806.055 1.183.157A3 3 0 0 0 15.75 3h-7.5a3 3 0 0 0-2.684 1.657ZM2.25 12a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3v-6ZM5.25 7.5c-.41 0-.806.055-1.184.157A3 3 0 0 1 6.75 6h10.5a3 3 0 0 1 2.683 1.657A4.505 4.505 0 0 0 18.75 7.5H5.25Z"/></svg><span class="sb-item-text">Stacking Frameworks</span></a>
            <a href="{{ route('admin.academic-credit-recognition') }}" class="sb-item {{ request()->routeIs('admin.academic-credit-recognition*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M9 1.5H5.625c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5Zm6.61 10.936a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 14.47a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/></svg><span class="sb-item-text">Legacy Credit Requests</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Reports &amp; Oversight</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.report') }}" class="sb-item {{ request()->routeIs('admin.report') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75ZM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 0 1-1.875-1.875V8.625ZM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 0 1 3 19.875v-6.75Z"/></svg><span class="sb-item-text">Report</span></a>
            <a href="{{ route('admin.audit-logs') }}" class="sb-item {{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 0-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 0-.75-.75Z" clip-rule="evenodd"/></svg><span class="sb-item-text">Audit Logs</span></a>
        </div>
    </div>

    <div class="sb-section open">
        <div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Communication</span></div></div>
        <div class="sb-items">
            <a href="{{ route('admin.announcements') }}" class="sb-item {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}"><svg class="sb-item-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16.881 4.345A23.112 23.112 0 0 1 8.25 6H7.5a5.25 5.25 0 0 0-.88 10.427 21.593 21.593 0 0 0 1.378 3.94c.464 1.004 1.674 1.32 2.582.796l.657-.379c.88-.508 1.165-1.593.772-2.468a17.116 17.116 0 0 1-.628-1.607c1.918.258 3.76.75 5.5 1.446A21.727 21.727 0 0 0 18 11.25c0-2.414-.393-4.735-1.119-6.905ZM18.26 3.74a23.22 23.22 0 0 1 1.24 7.51 23.22 23.22 0 0 1-1.41 7.992.75.75 0 1 0 1.409.516 24.555 24.555 0 0 0 1.415-6.43 2.992 2.992 0 0 0 .836-2.078c0-.807-.319-1.54-.836-2.078a24.65 24.65 0 0 0-1.415-6.43.75.75 0 1 0-1.409.516c.059.16.116.321.17.483Z"/></svg><span class="sb-item-text">Announcements</span></a>
        </div>
    </div>
</aside>
@endif
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
