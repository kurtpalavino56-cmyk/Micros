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
    .faculty-sidebar .side-link.active{position:relative;margin-left:-10px;padding:7px 14px 7px 24px;background:transparent;color:#071550;font-weight:600;box-shadow:none;}
    .faculty-sidebar .side-link.active::before{position:absolute;z-index:0;top:5px;right:0;bottom:5px;left:0;border-left:3px solid #f4c430;border-radius:0 9px 9px 0;background:#fff1b8;content:"";}
    .faculty-sidebar .side-link.active>*{position:relative;z-index:1;}
    .faculty-sidebar .side-link.active:hover{background:transparent;color:#071550;}
    .faculty-sidebar .side-icon-box{width:30px;height:30px;flex:0 0 30px;display:flex;align-items:center;justify-content:center;background:transparent !important;border:0;border-radius:0;}
    .faculty-sidebar .side-icon-box svg{width:24px;height:24px;stroke-width:1.9;transition:color .2s ease,transform .2s ease;}
    .faculty-sidebar .side-link:not(.active) .side-icon-box svg{color:#061a45;}
    .faculty-sidebar .side-link:not(.active):hover .side-icon-box svg{color:#061a45;}
    .faculty-sidebar .side-link.active .side-icon-box svg{color:#061a45;}
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
        .faculty-sidebar .side-link.active{min-height:42px;margin-left:0;padding:6px 14px;border-radius:9px;}
        .faculty-sidebar .side-link.active::before{border-left:3px solid #f4c430;border-radius:9px;}
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
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg></span>
        <span class="side-label">Dashboard</span>
    </a>
    <span class="sidebar-section-label">Courses</span>
    <a href="{{ route('faculty.courses') }}" class="side-link {{ request()->routeIs('faculty.courses') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.2C9.8 4.7 7 4 3.5 4v14c3.5 0 6.3.7 8.5 2.2 2.2-1.5 5-2.2 8.5-2.2V4c-3.5 0-6.3.7-8.5 2.2Z"/><path d="M12 6.5v13.2M6.5 8h2.8M15 8h2.8M6.5 11h2.8M15 11h2.8"/></svg></span>
        <span class="side-label">My Courses</span>
    </a>
    <a href="{{ route('faculty.create') }}" class="side-link {{ request()->routeIs('faculty.create') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3.5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10Z"/><path d="M13 3.5V10h7M12 13v5M9.5 15.5h5"/></svg></span>
        <span class="side-label">Create Courses</span>
    </a>
    <span class="sidebar-section-label">People</span>
    <a href="{{ route('faculty.inbox') }}" class="side-link {{ request()->routeIs('faculty.inbox*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5h16v13H4z"/><path d="M4 14h4l1.5 2h5L16 14h4"/><path d="M8 9.5h8"/></svg></span><span class="side-label">Inbox</span>
    </a>
    <a href="{{ route('faculty.students') }}" class="side-link {{ request()->routeIs('faculty.students*') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.8M16.5 14.5a5 5 0 0 1 4 4.8"/></svg></span><span class="side-label">Learner Progress</span>
    </a>
    <span class="sidebar-section-label">Insights</span>
    <a href="{{ route('faculty.analytics') }}" class="side-link {{ request()->routeIs('faculty.analytics') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5h16M4.5 19V5"/><path d="m7 15 4-4 3 2 5-7"/><circle cx="7" cy="15" r="1"/><circle cx="11" cy="11" r="1"/><circle cx="14" cy="13" r="1"/><circle cx="19" cy="6" r="1"/></svg></span>
        <span class="side-label">Analytics</span>
    </a>
    <span class="sidebar-section-label">Account</span>
    <a href="{{ route('faculty.profile') }}" class="side-link {{ request()->routeIs('faculty.profile') ? 'active' : '' }}">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/><circle cx="12" cy="12" r="9.2"/></svg></span>
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
