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
        grid-template-columns: 244px minmax(0, 1fr);
        align-items: stretch;
        transition: grid-template-columns .25s ease;
    }

    .layout.student-sidebar-layout.sidebar-collapsed {
        grid-template-columns: 78px minmax(0, 1fr);
    }


    /* ================================
       MAIN SIDEBAR
       ================================ */

    .layout.student-sidebar-layout .student-sidebar {
        background: #09255f;

        padding: 24px 10px 20px;

        display: flex;
        flex-direction: column;
        gap: 5px;

        border-right: 1px solid rgba(255,255,255,.08);

        height: calc(100vh - 70px);

        position: sticky;
        top: 66px;
        z-index: 900;

        align-self: start;

        overflow: hidden;

        transition:
            padding .25s ease,
            width .25s ease;
    }


    /* ================================
       TOGGLE
       ================================ */

    .student-sidebar .sidebar-toggle {
        align-self: flex-end;

        width: 38px;
        height: 38px;

        border: 0;
        border-radius: 10px;

        background: transparent;

        color: rgba(255,255,255,.75);

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0 6px 14px;

        cursor: pointer;

        transition:
            background-color .2s ease,
            color .2s ease;
    }

    .student-sidebar .sidebar-toggle:hover {
        background: rgba(255,255,255,.10);
        color: #fff;
    }

    .student-sidebar .sidebar-toggle svg {
        width: 20px;
        height: 20px;

        transition: transform .25s ease;
    }


    /* ================================
       SIDEBAR LINKS
       ================================ */

    .student-sidebar .side-link {
        display: flex;
        align-items: center;

        gap: 15px;

        min-height: 56px;

        padding: 10px 14px;

        border-radius: 13px;

        color: rgba(255,255,255,.78);

        font-size: 14px;
        font-weight: 600;

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
        background: rgba(255,255,255,.07);
        color: #fff;
    }

    .student-sidebar .side-link:not(.active):active {
        transform: scale(.98);
    }


    /* ================================
       ACTIVE ITEM
       ================================ */

    .student-sidebar .side-link.active {
        background: #efbb35;

        color: #09255f;

        font-weight: 700;

        box-shadow:
            0 4px 10px rgba(0,0,0,.10);
    }

    .student-sidebar .side-link.active:hover {
        background: #efbb35;
        color: #09255f;
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


    /* Normal icon */

    .student-sidebar
    .side-link:not(.active)
    .side-icon-box svg {
        color: rgba(255,255,255,.82);
    }


    /* Active icon */

    .student-sidebar
    .side-link.active
    .side-icon-box svg {
        color: #09255f;
    }


    /* ================================
       LABEL
       ================================ */

    .student-sidebar .side-label {
        overflow: hidden;

        opacity: 1;

        transition:
            opacity .18s ease,
            width .25s ease;
    }


    /* ================================
       COLLAPSED SIDEBAR
       ================================ */

    .student-sidebar-layout.sidebar-collapsed
    .student-sidebar .sidebar-toggle {
        align-self: center;
        margin-left: auto;
        margin-right: auto;
    }

    .student-sidebar-layout.sidebar-collapsed
    .student-sidebar .sidebar-toggle svg {
        transform: rotate(180deg);
    }


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
    .student-sidebar-layout .profile-head h2 {
        font-size: 26px;
    }

    .student-sidebar-layout .page-head p,
    .student-sidebar-layout .profile-head p {
        font-size: 14px;
    }


    /* ================================
       MOBILE
       ================================ */

    @media (max-width: 980px) {

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

        .student-sidebar .sidebar-toggle {
            flex: 0 0 38px;

            margin: 0 8px 0 0;
        }

        .student-sidebar .side-link {
            flex: 0 0 auto;

            min-height: 48px;
        }

        .student-sidebar .side-label,
        .student-sidebar-layout.sidebar-collapsed
        .student-sidebar .side-label {
            width: auto;
            opacity: 1;
        }

        .student-sidebar-layout.sidebar-collapsed
        .student-sidebar .side-link {
            justify-content: flex-start;

            gap: 15px;

            padding: 10px 14px;
        }

        .student-sidebar-layout.sidebar-collapsed
        .student-sidebar .sidebar-toggle svg {
            transform: none;
        }

        .student-sidebar-layout > .main {
            padding: 24px 22px 42px;
        }
    }
</style>

<aside class="student-sidebar" id="student-sidebar">
    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/><path d="M3 5v14"/></svg>
    </button>
    <a href="{{ route('dashboard') }}" class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h4v8H3zM10 9h4v12h-4zM17 3h4v18h-4z"/><path d="M3 21h18"/></svg></span>
        <span class="side-label">Dashboard</span>
    </a>
    <a href="{{ route('courses.browse') }}" class="side-link {{ request()->routeIs('courses.browse') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 6h8M8 10h6"/></svg></span>
        <span class="side-label">Browse Courses</span>
    </a>
    <a href="{{ route('courses.enrolled') }}" class="side-link {{ request()->routeIs('courses.enrolled') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15l2 2 4-4"/></svg></span>
        <span class="side-label">Enrolled Courses</span>
    </a>
    <a href="{{ route('badges.index') }}" class="side-link {{ request()->routeIs('badges.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3z"/><path d="m9.5 13 1.7 1.7 3.4-3.4"/></svg></span>
        <span class="side-label">My Badges</span>
    </a>
    <a href="{{ route('certificates.index') }}" class="side-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3h14v18H5z"/><path d="M8 7h8M8 11h6M8 15h4"/><path d="m15 18 2 2 3-4"/></svg></span>
        <span class="side-label">Certificates</span>
    </a>
    <a href="{{ route('pathways.index') }}" class="side-link {{ request()->routeIs('pathways.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="5" r="2.5"/><circle cx="5" cy="19" r="2.5"/><circle cx="19" cy="12" r="2.5"/><path d="M7.5 5H12a3 3 0 0 1 3 3v1a3 3 0 0 0 3 3h1"/><path d="M7.5 19H12a3 3 0 0 0 3-3v-1a3 3 0 0 1 3-3"/></svg></span>
        <span class="side-label">My Pathways</span>
    </a>
    <a href="{{ route('stacking.progress') }}" class="side-link {{ request()->routeIs('stacking.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16M4 12h16M4 19h16"/><path d="M8 3v4M16 10v4M8 17v4"/></svg></span>
        <span class="side-label">Stacking Progress</span>
    </a>
    <a href="{{ route('analytics.index') }}" class="side-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V5M4 19h16"/><path d="m7 15 4-4 3 2 5-7"/><circle cx="7" cy="15" r="1"/><circle cx="11" cy="11" r="1"/><circle cx="14" cy="13" r="1"/><circle cx="19" cy="6" r="1"/></svg></span>
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
