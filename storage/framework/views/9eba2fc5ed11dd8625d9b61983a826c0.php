<style>
    :root{--faculty-topbar-height:66px;}
html,body{scrollbar-width:none;-ms-overflow-style:none;}
    html::-webkit-scrollbar,body::-webkit-scrollbar{width:0;height:0;display:none;}
    .layout.faculty-sidebar-layout{display:grid;grid-template-columns:244px minmax(0,1fr);min-height:calc(100vh - var(--faculty-topbar-height));align-items:start;transition:grid-template-columns .25s ease;}
    .layout.faculty-sidebar-layout.sidebar-collapsed{grid-template-columns:78px minmax(0,1fr);}
    .layout.faculty-sidebar-layout .faculty-sidebar{background:#09255f;padding:24px 10px 20px;display:flex;flex-direction:column;gap:5px;border-right:1px solid rgba(255,255,255,.08);height:calc(100vh - var(--faculty-topbar-height));position:sticky;top:var(--faculty-topbar-height);z-index:900;align-self:start;overflow:hidden;transition:padding .25s ease,width .25s ease;}
    .faculty-sidebar .sidebar-toggle{align-self:flex-end;width:38px;height:38px;border:0;border-radius:10px;background:transparent;color:rgba(255,255,255,.75);display:flex;align-items:center;justify-content:center;margin:0 6px 14px;cursor:pointer;transition:background-color .2s ease,color .2s ease;}
    .faculty-sidebar .sidebar-toggle:hover{background:rgba(255,255,255,.10);color:#fff;}
    .faculty-sidebar .sidebar-toggle svg{width:20px;height:20px;transition:transform .25s ease;}
    .faculty-sidebar .side-link{display:flex;align-items:center;gap:15px;min-height:56px;padding:10px 14px;border-radius:13px;color:rgba(255,255,255,.78);font-size:14px;font-weight:600;text-decoration:none;white-space:nowrap;overflow:hidden;transition:background-color .2s ease,color .2s ease,transform .15s ease;}
    .faculty-sidebar .side-link:not(.active):hover{background:rgba(255,255,255,.07);color:#fff;}
    .faculty-sidebar .side-link:not(.active):active{transform:scale(.98);}
    .faculty-sidebar .side-link.active{background:#efbb35;border-left:4px solid #9a7000;color:#09255f;font-weight:700;box-shadow:0 4px 10px rgba(0,0,0,.10);}
    .faculty-sidebar .side-link.active:hover{background:#efbb35;color:#09255f;}
    .faculty-sidebar .side-icon-box{width:30px;height:30px;flex:0 0 30px;display:flex;align-items:center;justify-content:center;background:transparent !important;border:0;border-radius:0;}
    .faculty-sidebar .side-icon-box svg{width:24px;height:24px;stroke-width:1.9;transition:color .2s ease,transform .2s ease;}
    .faculty-sidebar .side-link:not(.active) .side-icon-box svg{color:rgba(255,255,255,.82);}
    .faculty-sidebar .side-link.active .side-icon-box svg{color:#09255f;}
    .faculty-sidebar .side-label{overflow:hidden;opacity:1;transition:opacity .18s ease,width .25s ease;}
    .faculty-sidebar .side-divider{width:100%;border:0;border-top:1px solid rgba(255,255,255,.16);margin:10px 0;}
    .faculty-sidebar-layout.sidebar-collapsed .sidebar-toggle{align-self:center;margin-left:auto;margin-right:auto;}
    .faculty-sidebar-layout.sidebar-collapsed .sidebar-toggle svg{transform:rotate(180deg);}
    .faculty-sidebar-layout.sidebar-collapsed .side-link{justify-content:center;gap:0;padding-left:8px;padding-right:8px;}
    .faculty-sidebar-layout.sidebar-collapsed .side-label{width:0;opacity:0;}
    .faculty-sidebar-layout > .main{padding:28px 32px 48px;min-width:0;transition:padding .25s ease;}
    .faculty-sidebar-layout .page-head h1,.faculty-sidebar-layout .page-head h2{font-size:26px;}
    .faculty-sidebar-layout .page-head p{font-size:14px;}
    @media (max-width:1024px){
        .layout.faculty-sidebar-layout,.layout.faculty-sidebar-layout.sidebar-collapsed{grid-template-columns:1fr;}
        .layout.faculty-sidebar-layout .faculty-sidebar{flex-direction:row;align-items:center;overflow-x:auto;position:static;height:auto;padding:10px 14px;border-right:0;border-bottom:1px solid var(--line);}
        .faculty-sidebar .sidebar-toggle{flex:0 0 38px;margin:0 8px 0 0;}
        .faculty-sidebar .side-link{flex:0 0 auto;min-height:48px;}
        .faculty-sidebar .side-divider{width:1px;height:30px;border-top:0;border-left:1px solid rgba(255,255,255,.16);margin:0 4px;}
        .faculty-sidebar .side-label,.faculty-sidebar-layout.sidebar-collapsed .side-label{width:auto;opacity:1;}
        .faculty-sidebar-layout.sidebar-collapsed .side-link{justify-content:flex-start;gap:15px;padding:10px 14px;}
        .faculty-sidebar-layout > .main{padding:24px 22px 42px;}
        .faculty-sidebar-layout.sidebar-collapsed .sidebar-toggle svg{transform:none;}
    }
</style>

<aside class="faculty-sidebar" id="faculty-sidebar">
    <button class="sidebar-toggle" id="faculty-sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/><path d="M3 5v14"/></svg>
    </button>
    <a href="<?php echo e(route('faculty.dashboard')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.dashboard') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h4v8H3zM10 9h4v12h-4zM17 3h4v18h-4z"/><path d="M3 21h18"/></svg></span>
        <span class="side-label">Dashboard</span>
    </a>
    <a href="<?php echo e(route('faculty.courses')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.courses') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 6h8M8 10h6"/></svg></span>
        <span class="side-label">My Courses</span>
    </a>
    <a href="<?php echo e(route('faculty.create')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.create') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h14v16H5z"/><path d="M9 4v16M12 8h4M12 12h4M12 16h3"/><path d="M7 8h.01M7 12h.01M7 16h.01"/></svg></span>
        <span class="side-label">Create Courses</span>
    </a>
    <hr class="side-divider">
    <a href="<?php echo e(route('faculty.profile')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.profile') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M4 21c.7-4 3.4-6 8-6s7.3 2 8 6"/><path d="M18 5.5h3M19.5 4v3"/></svg></span>
        <span class="side-label">Profile</span>
    </a>
    <a href="<?php echo e(route('faculty.inbox')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.inbox*') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg></span><span class="side-label">Inbox</span>
    </a>
    <a href="<?php echo e(route('faculty.students')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.students') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3"/><path d="M3 20c.7-3.6 2.7-5.5 6-5.5s5.3 1.9 6 5.5"/><path d="M15 5.5a3 3 0 0 1 0 5.8M16 14.5c2.4.7 3.9 2.4 4.5 5.5"/></svg></span><span class="side-label">Students</span>
    </a>
    <a href="<?php echo e(route('faculty.analytics')); ?>" class="side-link <?php echo e(request()->routeIs('faculty.analytics') ? 'active' : ''); ?>">
        <span class="side-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V5M4 19h16"/><path d="m7 15 4-4 3 2 5-7"/><circle cx="7" cy="15" r="1"/><circle cx="11" cy="11" r="1"/><circle cx="14" cy="13" r="1"/><circle cx="19" cy="6" r="1"/></svg></span>
        <span class="side-label">Analytics</span>
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
<?php /**PATH C:\Users\Beerus\Herd\UpSkill-Microcredential_Platform\resources\views/components/faculty-sidebar.blade.php ENDPATH**/ ?>