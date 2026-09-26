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
        --admin-navy: #0d1b6e;
        --admin-text: #f3f6ff;
        --admin-muted: rgba(255,255,255,.68);
        --admin-hover: rgba(255,255,255,.08);
        --admin-font: 'Inter', sans-serif;
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

        background: #071550 !important;
        border: 0 !important;
        border-right: 1px solid rgba(255,255,255,.08) !important;
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

        color: rgba(255,255,255,.65) !important;

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
        color: #f4c430  !important;
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

        background: #ffd864 !important;
        color: #071550 !important;
        font-weight: 600 !important;

        border-radius: 0 !important;
    }

    aside.shared-admin-sidebar .sb-item.active {
        margin-left: -12px !important;
        padding-left: 24px !important;
        margin-right: 0 !important;

        border-radius: 0 8px 8px 0 !important;
    }

    /* Yellow highlight at the left edge */
    aside.shared-admin-sidebar .sb-item.active::before {
        content: "" !important;

        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;

        width: 4px !important;

        background: #f4c430 !important;
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
</style>

<aside class="sidebar shared-admin-sidebar" aria-label="Admin navigation">
    <div class="sb-section open"><div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Main</span></div></div><div class="sb-items">
        <a href="{{ route('admin.dashboard') }}" class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="sb-item-text">Home</span></a>
    </div></div>
    <div class="sb-section open"><div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Management</span></div></div><div class="sb-items">
        <a href="{{ route('admin.usermanagement') }}" class="sb-item {{ request()->routeIs('admin.usermanagement', 'admin.users.*') ? 'active' : '' }}"><span class="sb-item-text">User Management</span></a>
        <a href="{{ route('admin.courses') }}" class="sb-item {{ request()->routeIs('admin.courses*') ? 'active' : '' }}"><span class="sb-item-text">Courses &amp; Badges</span></a>
        <a href="{{ route('admin.certificates') }}" class="sb-item {{ request()->routeIs('admin.certificates*') ? 'active' : '' }}"><span class="sb-item-text">Certificates</span></a>
        <a href="{{ route('admin.facultycodes') }}" class="sb-item {{ request()->routeIs('admin.facultycodes') ? 'active' : '' }}"><span class="sb-item-text">Faculty Codes</span></a>
        <a href="{{ route('admin.categories') }}" class="sb-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}"><span class="sb-item-text">Program Categories</span></a>
        <a href="{{ route('admin.pathways') }}" class="sb-item {{ request()->routeIs('admin.pathways*') ? 'active' : '' }}"><span class="sb-item-text">Learning Pathways</span></a>
        <a href="{{ route('admin.stacking-frameworks') }}" class="sb-item {{ request()->routeIs('admin.stacking-frameworks*') ? 'active' : '' }}"><span class="sb-item-text">Stacking Frameworks</span></a>
        <a href="{{ route('admin.academic-credit-recognition') }}" class="sb-item {{ request()->routeIs('admin.academic-credit-recognition*') ? 'active' : '' }}"><span class="sb-item-text">Academic Credit Recognition</span></a>
    </div></div>
    <div class="sb-section open"><div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Analytics</span></div></div><div class="sb-items">
        <a href="{{ route('admin.report') }}" class="sb-item {{ request()->routeIs('admin.report') ? 'active' : '' }}"><span class="sb-item-text">Report</span></a>
    </div></div>
    <div class="sb-section open"><div class="sb-section-hd"><div class="sb-hd-left"><span class="sb-section-label">Communication</span></div></div><div class="sb-items">
        <a href="{{ route('admin.announcements') }}" class="sb-item {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}"><span class="sb-item-text">Announcements</span></a>
        <a href="{{ route('admin.complaints') }}" class="sb-item {{ request()->routeIs('admin.complaints*') ? 'active' : '' }}"><span class="sb-item-text">Complaint Inbox</span></a>
    </div></div>
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
