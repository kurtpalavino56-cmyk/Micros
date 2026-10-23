<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,1,0" rel="stylesheet">


<aside class="faculty-sidebar" id="faculty-sidebar">
    <span class="sidebar-section-label">Overview</span>
    <a href="{{ route('faculty.dashboard') }}" class="side-link {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">dashboard</span></span>
        <span class="side-label">Dashboard</span>
    </a>
    <span class="sidebar-section-label">Courses</span>
    <a href="{{ route('faculty.courses') }}" class="side-link {{ request()->routeIs('faculty.courses') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">menu_book</span></span>
        <span class="side-label">My Courses</span>
    </a>
    <a href="{{ route('faculty.create') }}" class="side-link {{ request()->routeIs('faculty.create') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">post_add</span></span>
        <span class="side-label">Create Courses</span>
    </a>
    <span class="sidebar-section-label">People</span>
    <a href="{{ route('faculty.students') }}" class="side-link {{ request()->routeIs('faculty.students*') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">groups</span></span><span class="side-label">Learner Progress</span>
    </a>
    <span class="sidebar-section-label">Insights</span>
    <a href="{{ route('faculty.analytics') }}" class="side-link {{ request()->routeIs('faculty.analytics') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">monitoring</span></span>
        <span class="side-label">Analytics</span>
    </a>
    <span class="sidebar-section-label">Account</span>
    <a href="{{ route('faculty.profile') }}" class="side-link {{ request()->routeIs('faculty.profile') ? 'active' : '' }}">
        <span class="side-icon-box"><span class="material-symbols-rounded" aria-hidden="true">account_circle</span></span>
        <span class="side-label">Profile</span>
    </a>
</aside>

<script>
    (function () {
        var layout = document.querySelector('.faculty-sidebar-layout');
        var toggle = document.getElementById('faculty-sidebar-toggle');
        if (!layout || !toggle) return;

        var collapsed = window.localStorage.getItem('faculty-sidebar-collapsed') === 'true';
        function updateSidebar() {
            layout.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            toggle.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
        }
        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            window.localStorage.setItem('faculty-sidebar-collapsed', String(collapsed));
            updateSidebar();
        });
        updateSidebar();
    })();
</script>
