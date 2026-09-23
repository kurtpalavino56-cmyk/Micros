<?php
    $authUser = auth()->user();
    $isFaculty = $authUser?->isFaculty() ?? false;
    $isAdmin = $authUser?->isAdmin() ?? false;
    $profileRoute = $isAdmin ? 'admin.profile' : ($isFaculty ? 'faculty.profile' : 'profile.show');
    $inboxRoute = $isFaculty ? 'faculty.inbox' : 'inbox.index';
?>

<header class="auth-topbar">
    <div class="auth-topbar-brand">
        <span class="auth-topbar-logo"><img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="PSU Logo"></span>
        <strong>UPSKILL</strong>
    </div>

    <div class="auth-topbar-actions">
        <div class="auth-notifications" id="auth-notifications">
        <button type="button" class="auth-topbar-icon" id="auth-notifications-trigger" title="Notifications" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            <?php if(($unreadNotificationCount ?? 0) > 0): ?>
                <span class="auth-topbar-badge"><?php echo e($unreadNotificationCount); ?></span>
            <?php endif; ?>
        </button>
        <div class="auth-notifications-panel" id="auth-notifications-panel" role="region" aria-label="Recent notifications" hidden>
            <div class="auth-notifications-head"><strong>Notifications</strong><span>Recent activity</span></div>
            <div class="auth-notifications-list" id="auth-notifications-list"><p class="auth-notifications-status">Loading notifications...</p></div>
            <a class="auth-notifications-all" href="<?php echo e(route('notifications.index')); ?>">View all notifications</a>
        </div>
        </div>
        <?php if (! ($isAdmin)): ?>
        <a href="<?php echo e(route($inboxRoute)); ?>" class="auth-topbar-icon auth-topbar-inbox" title="Inbox" aria-label="Inbox">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg>
            <?php if(($unreadInboxCount ?? 0) > 0): ?>
                <span class="auth-topbar-badge"><?php echo e($unreadInboxCount); ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <div class="auth-profile" id="auth-profile-menu">
            <button type="button" class="auth-profile-trigger" id="auth-profile-trigger" aria-haspopup="true" aria-expanded="false" title="Account menu">
                <span class="auth-profile-avatar" <?php if($user->avatar_url ?? null): ?> style="background-image:url('<?php echo e($user->avatar_url); ?>');" <?php endif; ?>>
                    <?php if (! ($user->avatar_url ?? null)): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                    <?php endif; ?>
                </span>
                <span class="auth-profile-name"><?php echo e($user->name ?? 'Account'); ?></span>
                <svg class="auth-profile-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="auth-profile-dropdown" id="auth-profile-dropdown" role="menu" hidden>
                <div class="auth-profile-summary">
                    <strong><?php echo e($user->name ?? 'Account'); ?></strong>
                    <span><?php echo e($user->email ?? ''); ?></span>
                </div>
                <a href="<?php echo e(route($profileRoute)); ?>" role="menuitem">Profile</a>
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" role="menuitem">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
    .auth-topbar{--navy:#0d1b6e;--gold:#dba617;--gold-light:#ffd84a;background:linear-gradient(115deg,#071550 0%,#0d1b6e 58%,#102b83 100%);display:flex;align-items:center;justify-content:space-between;padding:12px 28px;gap:20px;position:sticky;top:0;z-index:1000;border-bottom:1px solid rgba(255,255,255,.1);box-shadow:0 8px 24px rgba(7,21,80,.2);}
    .auth-topbar-brand{display:flex;align-items:center;gap:12px;color:#fff;white-space:nowrap;}
    .auth-topbar-logo{width:42px;height:42px;border-radius:50%;background:transparent;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;}
    .auth-topbar-logo img{width:100%;height:100%;object-fit:contain;padding:3px;}
    .auth-topbar-brand strong{font-size:22px;letter-spacing:1px;}
    .auth-topbar-nav{display:flex;gap:8px;margin-left:auto;}
    .auth-topbar-nav a{color:rgba(255,255,255,.86);font-weight:700;padding:9px 16px;border-radius:10px;font-size:14px;transition:color .18s ease,background .18s ease;}
    .auth-topbar-nav a:hover,.auth-topbar-nav a.is-active{color:var(--gold-light);background:rgba(255,255,255,.1);}
    .auth-topbar-actions{display:flex;align-items:center;gap:18px;}
    .auth-topbar-icon{width:38px;height:38px;border:0;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#fff;position:relative;background:transparent;padding:0;transition:color .15s ease;}
    .auth-topbar-icon:hover{background:transparent;}
    .auth-topbar-icon:hover{color:#FFD501;}
    .auth-notifications{position:relative;}
    .auth-notifications-panel{position:absolute;right:0;top:calc(100% + 12px);width:340px;background:#fff;color:var(--navy);border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 16px 35px rgba(12,15,77,.22);overflow:hidden;}
    .auth-notifications-panel[hidden]{display:none;}
    .auth-notifications-head{display:flex;align-items:baseline;justify-content:space-between;padding:14px 16px 11px;border-bottom:1px solid #eef0f5;}
    .auth-notifications-head strong{font-size:15px;}.auth-notifications-head span{font-size:11px;color:#6b7280;}
    .auth-notifications-list{max-height:300px;overflow:auto;padding:4px 0;}
    .auth-notifications-item{display:block;padding:11px 16px;border-bottom:1px solid #f0f2f6;}.auth-notifications-item:hover{background:#f7f9ff;}
    .auth-notifications-item strong{display:block;font-size:12px;}.auth-notifications-item p{margin:3px 0 0;color:#5f6b85;font-size:11px;line-height:1.4;}.auth-notifications-item time{display:block;margin-top:4px;color:#9aa3b5;font-size:10px;}.auth-notifications-item.is-unread{border-left:3px solid var(--gold);padding-left:13px;}
    .auth-notifications-status{padding:18px 16px;margin:0;color:#6b7280;font-size:12px;text-align:center;}
    .auth-notifications-all{display:block;padding:11px 16px;background:#f7f9ff;color:var(--navy);font-size:12px;font-weight:800;text-align:center;}
    .auth-topbar-icon svg{width:22px;height:22px;}
    .auth-topbar-badge{position:absolute;top:-9px;right:-10px;background:#ef4444;color:#fff;border-radius:999px;font-size:10px;font-weight:800;padding:2px 5px;line-height:1.3;box-shadow:0 0 0 2px var(--navy);}
    .auth-profile{position:relative;}
    .auth-profile-trigger{display:inline-flex;align-items:center;gap:7px;border:0;background:transparent;color:#fff;padding:0;cursor:pointer;}
    .auth-profile-avatar{width:38px;height:38px;border-radius:50%;background:#fff center/cover no-repeat;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .auth-profile-avatar svg{width:21px;height:21px;color:var(--navy);}
    .auth-profile-name{max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:700;}
    .auth-profile-chevron{width:15px;height:15px;transition:transform .15s ease;}
    .auth-profile.is-open .auth-profile-chevron{transform:rotate(180deg);}
    .auth-profile-dropdown{position:absolute;right:0;top:calc(100% + 12px);width:220px;background:#fff;color:var(--navy);border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 16px 35px rgba(12,15,77,.22);padding:8px;}
    .auth-profile-dropdown[hidden]{display:none;}
    .auth-profile-summary{padding:10px 11px 9px;border-bottom:1px solid #eef0f5;margin-bottom:5px;overflow:hidden;}
    .auth-profile-summary strong,.auth-profile-summary span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .auth-profile-summary strong{font-size:14px;}
    .auth-profile-summary span{font-size:11px;color:#6b7280;margin-top:3px;}
    .auth-profile-dropdown a,.auth-profile-dropdown form button{display:block;width:100%;border:0;background:transparent;color:var(--navy);text-align:left;border-radius:8px;padding:10px 11px;font:inherit;font-size:13px;font-weight:700;cursor:pointer;}
    .auth-profile-dropdown form button{color:#dc2626;}
    .auth-profile-dropdown a:hover{background:#f2f5fb;color:var(--navy);}
    .auth-profile-dropdown form button:hover{background:#fff1f2;color:#b91c1c;}
    .auth-profile-dropdown form{margin:0;border-top:1px solid #eef0f5;margin-top:5px;padding-top:5px;}
    @media (max-width:700px){
        .auth-topbar{padding:11px 16px;}
        .auth-topbar-brand strong{font-size:18px;}
        .auth-topbar-logo{width:36px;height:36px;}
        .auth-topbar-nav{display:none;}
        .auth-topbar-actions{margin-left:auto;gap:14px;}
        .auth-profile-name{max-width:100px;}
        .auth-notifications-panel{position:fixed;top:64px;right:12px;left:12px;width:auto;}
    }
</style>

<script>
    (function () {
        var notifications = document.getElementById('auth-notifications');
        var notificationsTrigger = document.getElementById('auth-notifications-trigger');
        var notificationsPanel = document.getElementById('auth-notifications-panel');
        var notificationsList = document.getElementById('auth-notifications-list');
        if (notifications && notificationsTrigger && notificationsPanel) {
            var loaded = false;
            function closeNotifications() {
                notificationsPanel.hidden = true;
                notificationsTrigger.setAttribute('aria-expanded', 'false');
            }
            function renderNotifications(items) {
                if (!items.length) {
                    notificationsList.innerHTML = '<p class="auth-notifications-status">You are all caught up.</p>';
                    return;
                }
                function escapeHtml(value) {
                    return String(value || '').replace(/[&<>'"]/g, function (character) {
                        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'}[character];
                    });
                }
                notificationsList.innerHTML = items.map(function (item) {
                    return '<div class="auth-notifications-item ' + (item.unread ? 'is-unread' : '') + '"><strong>' + escapeHtml(item.title) + '</strong><p>' + escapeHtml(item.message) + '</p><time>' + escapeHtml(item.time) + '</time></div>';
                }).join('');
            }
            notificationsTrigger.addEventListener('click', function () {
                var isOpen = !notificationsPanel.hidden;
                notificationsPanel.hidden = isOpen;
                notificationsTrigger.setAttribute('aria-expanded', String(!isOpen));
                if (!isOpen && !loaded) {
                    fetch('<?php echo e(route('notifications.preview')); ?>', { headers: { 'Accept': 'application/json' } })
                        .then(function (response) { return response.json(); })
                        .then(function (data) { loaded = true; renderNotifications(data.notifications || []); })
                        .catch(function () { notificationsList.innerHTML = '<p class="auth-notifications-status">Unable to load notifications.</p>'; });
                }
            });
            document.addEventListener('click', function (event) { if (!notifications.contains(event.target)) closeNotifications(); });
            document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeNotifications(); });
        }

        var menu = document.getElementById('auth-profile-menu');
        var trigger = document.getElementById('auth-profile-trigger');
        var dropdown = document.getElementById('auth-profile-dropdown');
        if (!menu || !trigger || !dropdown) return;

        function closeMenu() {
            menu.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            dropdown.hidden = true;
        }
        trigger.addEventListener('click', function () {
            var isOpen = menu.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', String(isOpen));
            dropdown.hidden = !isOpen;
        });
        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target)) closeMenu();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMenu();
        });
    })();
</script>

<style id="upskill-ui-tokens">
:root{--up-primary:#0d1b6e;--up-primary-hover:#1232d4;--up-secondary:#eef2ff;--up-secondary-text:#24346f;--up-accent:#dba617;--up-success:#159a68;--up-progress:#1232d4;--up-border:#dbe2ee;}
.btn-save,.btn-primary,.btn-navy,.btn-action.btn-primary{background:var(--up-primary)!important;color:#fff!important;border-color:var(--up-primary)!important}
.btn-save:hover,.btn-primary:hover,.btn-navy:hover,.btn-action.btn-primary:hover{background:var(--up-primary-hover)!important;border-color:var(--up-primary-hover)!important}
.btn-secondary,.btn-outline-navy,.btn-action.btn-secondary{background:var(--up-secondary)!important;color:var(--up-secondary-text)!important;border-color:var(--up-border)!important}
.btn-cancel{background:#fff!important;color:var(--up-secondary-text)!important;border:1px solid var(--up-border)!important}
.progress-fill,.bar-fill,.chart-line,.nav-progress-fill,.fill{background:var(--up-progress)!important;stroke:var(--up-progress)!important}
.progress-track,.bar-track,.track{background:#e8edf6!important}
</style>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/components/authenticated-topbar.blade.php ENDPATH**/ ?>