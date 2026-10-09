<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
<style>
    :root{--faculty-topbar-height:66px;}
html,body{scrollbar-width:none;-ms-overflow-style:none;}
    html::-webkit-scrollbar,body::-webkit-scrollbar{width:0;height:0;display:none;}
    .layout.faculty-sidebar-layout{display:grid;grid-template-columns:244px minmax(0,1fr);min-height:calc(100vh - var(--faculty-topbar-height));align-items:start;transition:grid-template-columns .25s ease;}
    .layout.faculty-sidebar-layout.sidebar-collapsed{grid-template-columns:78px minmax(0,1fr);}
    .layout.faculty-sidebar-layout .faculty-sidebar{background:#fff;padding:24px 10px 20px;display:flex;flex-direction:column;gap:5px;border-right:1px solid #e5e7eb;height:calc(100vh - var(--faculty-topbar-height));position:sticky;top:var(--faculty-topbar-height);z-index:900;align-self:start;overflow-x:hidden;overflow-y:auto;transition:padding .25s ease,width .25s ease;}
    .faculty-sidebar .side-link{display:flex;align-items:center;flex:0 0 56px;gap:15px;padding:10px 14px;border-radius:13px;color:#061a45;font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;line-height:1.25;text-decoration:none;white-space:nowrap;overflow:hidden;transition:background-color .2s ease,color .2s ease,transform .15s ease;}
    .faculty-sidebar .side-link:not(.active):hover{background:#fffbe6;color:#061a45;}
    .faculty-sidebar .side-link:not(.active):active{transform:scale(.98);}
    .faculty-sidebar .side-link.active{position:relative;margin-left:-10px;padding:7px 14px 7px 24px;border-radius:0 13px 13px 0;background:transparent;color:#071550;font-weight:600;box-shadow:none;overflow:visible;}
    .faculty-sidebar .side-link.active::before{position:absolute;z-index:0;top:5px;right:0;bottom:5px;left:0;border-radius:0 9px 9px 0;background:#fff1b8;box-shadow:0 0 24px 6px rgba(244,196,48,.12);content:"";}
    .faculty-sidebar .side-link.active::after{position:absolute;z-index:1;top:5px;bottom:5px;left:0;width:5px;border-radius:0 5px 5px 0;background:#c4930f;content:"";}
    .faculty-sidebar .side-link.active>*{position:relative;z-index:2;}
    .faculty-sidebar .side-link.active:hover{background:transparent;color:#071550;}
    .faculty-sidebar .side-icon-box{width:30px;height:30px;flex:0 0 30px;display:flex;align-items:center;justify-content:center;background:transparent !important;border:0;border-radius:0;}
    .faculty-sidebar .side-icon-box .material-symbols-rounded{font-family:"Material Symbols Rounded";font-weight:normal;font-style:normal;width:24px;height:24px;font-size:24px;line-height:1;font-feature-settings:"liga";-webkit-font-smoothing:antialiased;transition:color .2s ease,transform .2s ease;}
    .faculty-sidebar .side-link:not(.active) .side-icon-box .material-symbols-rounded{color:#061a45;}
    .faculty-sidebar .side-link:not(.active):hover .side-icon-box .material-symbols-rounded{color:#061a45;}
    .faculty-sidebar .side-link.active .side-icon-box .material-symbols-rounded{color:#061a45;}
    .faculty-sidebar .side-label{flex:0 0 auto;overflow:hidden;opacity:1;font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;line-height:1.25;transition:opacity .18s ease,width .25s ease;}
    .faculty-sidebar .sidebar-section-label{flex:0 0 auto;margin:12px 14px 3px;color:#56627a;font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:10px;font-weight:800;letter-spacing:.09em;line-height:1.3;text-transform:uppercase;white-space:nowrap;}
    .faculty-sidebar .side-divider{width:100%;border:0;border-top:1px solid rgba(9,37,95,.22);margin:10px 0;}
    .faculty-sidebar-layout.sidebar-collapsed .side-link{justify-content:center;gap:0;padding-left:8px;padding-right:8px;}
    .faculty-sidebar-layout.sidebar-collapsed .side-label{width:0;opacity:0;}
    .faculty-sidebar-layout.sidebar-collapsed .sidebar-section-label{display:none;}
    .faculty-sidebar-layout > .main{padding:28px 32px 48px;min-width:0;transition:padding .25s ease;}
    .faculty-sidebar-layout .page-head h1,.faculty-sidebar-layout .page-head h2{font-size:26px;}
    .faculty-sidebar-layout .page-head p{font-size:14px;}
    @media (max-width:1024px){
        .layout.faculty-sidebar-layout,.layout.faculty-sidebar-layout.sidebar-collapsed{grid-template-columns:1fr;}
        .layout.faculty-sidebar-layout .faculty-sidebar{flex-direction:row;align-items:center;overflow-x:auto;position:static;height:auto;padding:10px 14px;border-right:0;border-bottom:1px solid var(--line);}
        .faculty-sidebar-layout.sidebar-collapsed .faculty-sidebar{display:none;}
        .faculty-sidebar .side-link{flex:0 0 auto;min-height:48px;}
        .faculty-sidebar .side-link.active{min-height:42px;margin-left:0;padding:6px 14px;border-radius:0 9px 9px 0;}
        .faculty-sidebar .side-link.active::before{border-radius:0 9px 9px 0;}
        .faculty-sidebar .side-divider{width:1px;height:30px;border-top:0;border-left:1px solid rgba(9,37,95,.22);margin:0 4px;}
        .faculty-sidebar .sidebar-section-label{display:none;}
        .faculty-sidebar .side-label,.faculty-sidebar-layout.sidebar-collapsed .side-label{width:auto;opacity:1;}
        .faculty-sidebar-layout.sidebar-collapsed .side-link{justify-content:flex-start;gap:15px;padding:10px 14px;}
        .faculty-sidebar-layout > .main{padding:24px 22px 42px;}
    }
</style>

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
