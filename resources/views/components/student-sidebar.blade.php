<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
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
        border-radius: 0 13px 13px 0;
        background: transparent;
        color: #071550;
        font-weight: 600;
        box-shadow: none;
        overflow: visible;
    }

    .student-sidebar .side-link.active::before {
        position: absolute;
        z-index: 0;
        top: 5px;
        right: 0;
        bottom: 5px;
        left: 0;
        border-radius: 0 9px 9px 0;
        background: #dbeafe;
        box-shadow: 0 0 24px 6px rgba(36, 71, 212, .12);
        content: "";
    }

    .student-sidebar .side-link.active::after {
        position: absolute;
        z-index: 1;
        top: 5px;
        bottom: 5px;
        left: 0;
        width: 5px;
        border-radius: 0 5px 5px 0;
        background: #2447d4;
        content: "";
    }

    .student-sidebar .side-link.active > * {
        position: relative;
        z-index: 2;
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

    .student-sidebar .side-icon-box .material-symbols-rounded {
        font-family: "Material Symbols Rounded";
        font-weight: normal;
        font-style: normal;
        width: 24px;
        height: 24px;
        font-size: 24px;
        line-height: 1;
        font-feature-settings: "liga";
        -webkit-font-smoothing: antialiased;

        transition:
            color .2s ease,
            transform .2s ease;
    }

    .student-sidebar .side-link:not(.active):hover .side-icon-box .material-symbols-rounded {
        color: #2447d4;
    }


    /* Normal icon */

    .student-sidebar
    .side-link:not(.active)
    .side-icon-box .material-symbols-rounded {
        color: #061a45;
    }


    /* Active icon */

    .student-sidebar
    .side-link.active
    .side-icon-box .material-symbols-rounded {
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
            border-radius: 0 9px 9px 0;
        }

        .student-sidebar .side-link.active::before {
            border-radius: 0 9px 9px 0;
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
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">dashboard</span></span>
        <span class="side-label">Dashboard</span>
    </a>
    <span class="sidebar-section-label">Learning</span>
    <a href="{{ route('courses.browse') }}" class="side-link {{ request()->routeIs('courses.browse') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">explore</span></span>
        <span class="side-label">Browse Courses</span>
    </a>
    <a href="{{ route('courses.enrolled') }}" class="side-link {{ request()->routeIs('courses.enrolled') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">menu_book</span></span>
        <span class="side-label">Enrolled Courses</span>
    </a>
    <span class="sidebar-section-label">Achievements</span>
    <a href="{{ route('badges.index') }}" class="side-link {{ request()->routeIs('badges.*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span></span>
        <span class="side-label">My Badges</span>
    </a>
    <a href="{{ route('certificates.index') }}" class="side-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">verified</span></span>
        <span class="side-label">Certificates</span>
    </a>
    <span class="sidebar-section-label">Progress</span>
    <a href="{{ route('pathways.index') }}" class="side-link {{ request()->routeIs('pathways.*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">conversion_path</span></span>
        <span class="side-label">My Pathways</span>
    </a>
    <a href="{{ route('stacking.progress') }}" class="side-link {{ request()->routeIs('stacking.*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">stacked_bar_chart</span></span>
        <span class="side-label">Stacking Progress</span>
    </a>
    <a href="{{ route('analytics.index') }}" class="side-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">monitoring</span></span>
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
