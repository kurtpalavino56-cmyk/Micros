<style>
    /* ================================
       SIDEBAR LAYOUT
       ================================ */

    html,
    body {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    .layout.student-sidebar-layout {
        display: grid;
        grid-template-columns: 244px minmax(0, 1fr);
        align-items: stretch;
        min-height: calc(100vh - 66px);
        transition: grid-template-columns .25s ease;
    }

    .layout.student-sidebar-layout.sidebar-collapsed {
        grid-template-columns: 78px minmax(0, 1fr);
    }


    /* ================================
       MAIN SIDEBAR
       ================================ */

    .layout.student-sidebar-layout .student-sidebar {
        background: #fff;

        padding: 24px 10px 20px;

        display: flex;
        flex-direction: column;
        gap: 5px;

        border-right: 1px solid #e5e7eb;

        height: calc(100vh - 70px);

        position: sticky;
        top: 66px;
        z-index: 900;

        align-self: start;

        overflow-x: hidden;
        overflow-y: auto;

        transition:
            padding .25s ease,
            width .25s ease;
    }


    /* ================================
       SIDEBAR LINKS
       ================================ */

    .student-sidebar .side-link {
        display: flex;
        align-items: center;
        flex: 0 0 56px;

        gap: 15px;

        padding: 10px 14px;

        border-radius: 13px;

        color: #061a45;

        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.25;

        text-decoration: none;

        white-space: nowrap;
        overflow: hidden;

        transition:
            background-color .2s ease,
            color .2s ease,
            transform .15s ease;
    }


    /* Hover */

    .student-sidebar .side-link:not(.active):hover {
        background: #f2f7ff;
        color: #0d1b6e;
    }

    .student-sidebar .side-link:not(.active):active {
        transform: scale(.98);
    }


    /* ================================
       ACTIVE ITEM
       ================================ */

    .student-sidebar .side-link.active {
        position: relative;
        margin-left: -10px;
        padding: 7px 14px 7px 24px;
        background: transparent;
        color: #071550;
        font-weight: 600;
        box-shadow: none;
    }

    .student-sidebar .side-link.active::before {
        position: absolute;
        z-index: 0;
        top: 5px;
        right: 0;
        bottom: 5px;
        left: 0;
        border-left: 3px solid #2447d4;
        border-radius: 0 9px 9px 0;
        background: #dbeafe;
        content: "";
    }

    .student-sidebar .side-link.active > * {
        position: relative;
        z-index: 1;
    }

    .student-sidebar .side-link.active:hover {
        background: transparent;
        color: #071550;
    }


    /* ================================
       ICON CONTAINER
       ================================ */

    .student-sidebar .side-icon-box {
        width: 30px;
        height: 30px;

        flex: 0 0 30px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: transparent !important;
        border: 0;
        border-radius: 0;
    }

    .student-sidebar .side-icon-box svg {
        width: 24px;
        height: 24px;

        stroke-width: 1.9;

        transition:
            color .2s ease,
            transform .2s ease;
    }

    .student-sidebar .side-link:not(.active):hover .side-icon-box svg {
        color: #2447d4;
    }


    /* Normal icon */

    .student-sidebar
    .side-link:not(.active)
    .side-icon-box svg {
        color: #061a45;
    }


    /* Active icon */

    .student-sidebar
    .side-link.active
    .side-icon-box svg {
        color: #071550;
    }


    /* ================================
       LABEL
       ================================ */

    .student-sidebar .side-label {
        overflow: hidden;
        flex: 0 0 auto;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.25;

        opacity: 1;

        transition:
            opacity .18s ease,
            width .25s ease;
    }

    .student-sidebar .sidebar-section-label {
        flex: 0 0 auto;
        margin: 12px 14px 3px;
        color: #56627a;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .09em;
        line-height: 1.3;
        text-transform: uppercase;
        white-space: nowrap;
    }


    /* ================================
       COLLAPSED SIDEBAR
       ================================ */

    .student-sidebar-layout.sidebar-collapsed
    .student-sidebar .side-link {
        justify-content: center;

        gap: 0;

        padding-left: 8px;
        padding-right: 8px;
    }


    .student-sidebar-layout.sidebar-collapsed
    .student-sidebar .side-label {
        width: 0;

        opacity: 0;
    }

    .student-sidebar-layout.sidebar-collapsed
    .student-sidebar .sidebar-section-label {
        display: none;
    }


    /* ================================
       MAIN CONTENT
       ================================ */

    .student-sidebar-layout > .main {
        padding: 28px 32px 48px;

        min-width: 0;

        transition: padding .25s ease;
    }


    .student-sidebar-layout .page-head h1,
    .student-sidebar-layout .page-head h2,
    .student-sidebar-layout .profile-head h1,
    .student-sidebar-layout .profile-head h2,
    .student-sidebar-layout .page-header h1,
    .student-sidebar-layout .analytics-header h1,
    .student-sidebar-layout main.student-dashboard .hero-copy > h2,
    .student-sidebar-layout .hero-title {
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 30px;
        font-weight: 700;
    }

    .student-sidebar-layout .page-head p:not(.eyebrow),
    .student-sidebar-layout .profile-head p,
    .student-sidebar-layout .page-header > p:last-child,
    .student-sidebar-layout .analytics-header .subtitle,
    .student-sidebar-layout main.student-dashboard .hero-copy > p:not(.eyebrow),
    .student-sidebar-layout .hero-subheading {
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 15px;
        font-weight: 400;
    }


    /* ================================
       MOBILE
       ================================ */

    @media (max-width: 1024px) {

        .layout.student-sidebar-layout,
        .layout.student-sidebar-layout.sidebar-collapsed {
            grid-template-columns: 1fr;
        }

        .layout.student-sidebar-layout .student-sidebar {
            position: static;

            height: auto;

            flex-direction: row;
            align-items: center;

            overflow-x: auto;

            padding: 10px 14px;

            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .student-sidebar-layout.sidebar-collapsed .student-sidebar {
            display: none;
        }

        .student-sidebar .side-link {
            flex: 0 0 auto;

            min-height: 48px;
        }

        .student-sidebar .side-link.active {
            min-height: 42px;
            margin-left: 0;
            padding: 6px 14px;
            border-radius: 9px;
        }

        .student-sidebar .side-link.active::before {
            border-left: 3px solid #2447d4;
            border-radius: 9px;
        }

        .student-sidebar .side-label,
        .student-sidebar-layout.sidebar-collapsed
        .student-sidebar .side-label {
            width: auto;
            opacity: 1;
        }

        .student-sidebar .sidebar-section-label {
            display: none;
        }

        .student-sidebar-layout.sidebar-collapsed
        .student-sidebar .side-link {
            justify-content: flex-start;

            gap: 15px;

            padding: 10px 14px;
        }

        .student-sidebar-layout > .main {
            padding: 24px 22px 42px;
        }
    }
</style>

<aside class="student-sidebar" id="student-sidebar">
    <span class="sidebar-section-label">Overview</span>
    <a href="{{ route('dashboard') }}" class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg></span>
        <span class="side-label">Dashboard</span>
    </a>
    <span class="sidebar-section-label">Learning</span>
    <a href="{{ route('courses.browse') }}" class="side-link {{ request()->routeIs('courses.browse') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.2C9.8 4.7 7 4 3.5 4v14c3.5 0 6.3.7 8.5 2.2 2.2-1.5 5-2.2 8.5-2.2V4c-3.5 0-6.3.7-8.5 2.2Z"/><path d="M12 6.5v13.2M6.5 8h2.8M15 8h2.8M6.5 11h2.8M15 11h2.8"/></svg></span>
        <span class="side-label">Browse Courses</span>
    </a>
    <a href="{{ route('courses.enrolled') }}" class="side-link {{ request()->routeIs('courses.enrolled') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3.5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10Z"/><path d="M13 3.5V10h7M8 14h3M8 17h3M14.5 15.5l1.5 1.5 3-3"/></svg></span>
        <span class="side-label">Enrolled Courses</span>
    </a>
    <span class="sidebar-section-label">Achievements</span>
    <a href="{{ route('badges.index') }}" class="side-link {{ request()->routeIs('badges.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8.5" r="5.2"/><path d="m8.5 13-1.2 7.5 4.7-2.6 4.7 2.6-1.2-7.5M10 8.5l1.3 1.3L14 7"/></svg></span>
        <span class="side-label">My Badges</span>
    </a>
    <a href="{{ route('certificates.index') }}" class="side-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h12v11H6z"/><path d="M9 7h6M9 10h4M9 14.5v6l3-1.7 3 1.7v-6"/><path d="m15.5 7.5.8.8 1.5-1.6"/></svg></span>
        <span class="side-label">Certificates</span>
    </a>
    <span class="sidebar-section-label">Progress</span>
    <a href="{{ route('pathways.index') }}" class="side-link {{ request()->routeIs('pathways.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="5" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 5h3a4 4 0 0 1 4 4v1a2 2 0 0 0 2 2h1M7 19h3a4 4 0 0 0 4-4v-1a2 2 0 0 1 2-2h1"/></svg></span>
        <span class="side-label">My Pathways</span>
    </a>
    <a href="{{ route('stacking.progress') }}" class="side-link {{ request()->routeIs('stacking.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5h16M4.5 19V5"/><path d="M8 16v-3M12 16V9M16 16V6"/><path d="M7 9.5 11 7l4 1 4-4"/></svg></span>
        <span class="side-label">Stacking Progress</span>
    </a>
    <a href="{{ route('analytics.index') }}" class="side-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5h16M4.5 19V5"/><path d="m7 15 4-4 3 2 5-7"/><circle cx="7" cy="15" r="1"/><circle cx="11" cy="11" r="1"/><circle cx="14" cy="13" r="1"/><circle cx="19" cy="6" r="1"/></svg></span>
        <span class="side-label">Analytics</span>
    </a>
</aside>

<script>
    (function () {
        var layout = document.querySelector('.student-sidebar-layout');
        var toggle = document.getElementById('sidebar-toggle');
        if (!layout || !toggle) return;

        var collapsed = window.localStorage.getItem('student-sidebar-collapsed') === 'true';
        function updateSidebar() {
            layout.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            toggle.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
        }
        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            window.localStorage.setItem('student-sidebar-collapsed', String(collapsed));
            updateSidebar();
        });
        updateSidebar();
    })();
</script>
