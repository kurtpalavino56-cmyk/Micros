<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | UPSKILL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#13176b; --navy-dark:#0c0f4d; --gold:#FFD501; --gold-light:#FFE36B; --line:#e5e7eb; --muted:#6b7280; --bg:#f7f8fc; --white:#fff; --red:#ef4444; --shadow:0 12px 30px rgba(19,23,107,.08); --transition:.22s ease; }
        *,*::before,*::after{box-sizing:border-box;}
        html{scrollbar-width:none;-ms-overflow-style:none;}
        html::-webkit-scrollbar{display:none;width:0;height:0;}
        body{margin:0;font-family:"Inter",sans-serif;color:var(--navy);background:linear-gradient(135deg,#f8faff 0%,var(--bg) 100%);}
        a{text-decoration:none;color:inherit;} button{font:inherit;cursor:pointer;}
        .page{max-width:1500px;margin:0 auto;padding:40px 36px 64px;}
        .hero{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:24px;padding:24px 28px;background:var(--white);border:1px solid var(--line);border-radius:24px;box-shadow:var(--shadow);}
        .hero h2{margin:0 0 6px;font-size:28px;}.hero p{margin:0;color:var(--muted);}.hero-badges{display:flex;gap:10px;flex-wrap:wrap;}
        .pill{background:#f3f6ff;color:var(--navy);border:1px solid #dfe8ff;padding:8px 14px;border-radius:999px;font-size:13px;font-weight:700;}.pill strong{margin-right:4px;}
        .btn-home{display:inline-flex;align-items:center;gap:8px;margin-bottom:18px;padding:10px 18px 10px 14px;background:var(--white);border:1px solid var(--line);border-radius:999px;color:var(--navy);font-size:14px;font-weight:700;box-shadow:var(--shadow);}.btn-home:hover{background:var(--navy);border-color:var(--navy);color:#fff;}.btn-home svg{width:17px;height:17px;}
        .content-grid{display:grid;grid-template-columns:280px minmax(0,1fr);gap:28px;align-items:start;}.sidebar-card,.list-card{background:var(--white);border:1px solid var(--line);border-radius:22px;box-shadow:var(--shadow);}.sidebar-card{padding:24px;}.sidebar-card h3{margin:0 0 14px;font-size:18px;}.filter-item{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line);color:var(--muted);font-size:14px;font-weight:600;}.filter-item:last-child{border-bottom:0;}.filter-item:hover,.filter-item.active{color:var(--navy);}.filter-item.active{font-weight:800;}.filter-item .count{padding:4px 8px;border-radius:999px;background:#eef2ff;color:var(--navy);font-size:12px;}.filter-item.active .count{background:var(--navy);color:#fff;}
        .list-card{min-height:520px;padding:28px;}.list-card-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px;}.list-card-head h3{margin:0;font-size:18px;}.list-card-head button{padding:8px 14px;border:1px solid var(--line);border-radius:999px;background:#fff;color:var(--navy);font-weight:700;}.list-card-head button:disabled{opacity:.5;cursor:not-allowed;}
        .notif-item{display:flex;align-items:flex-start;gap:14px;padding:16px 0;border-bottom:1px solid var(--line);}.notif-item:last-child{border-bottom:0;}.notif-item.unread{padding-left:8px;padding-right:8px;background:#f9fbff;border-radius:16px;}.notif-icon{display:flex;align-items:center;justify-content:center;flex:0 0 44px;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#eef4ff 0%,#dbeafe 100%);}.notif-icon svg{width:22px;height:22px;color:var(--navy);}.notif-title{display:flex;align-items:center;gap:8px;margin-bottom:4px;}.notif-title strong{font-size:15px;}.unread-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--red);}.notif-meta{margin-top:4px;color:var(--muted);font-size:13px;}.notif-action{margin-left:auto;color:var(--navy);font-size:13px;font-weight:700;white-space:nowrap;}
        .notif-message{color:#4b5563;font-size:14px;line-height:1.5;overflow-wrap:anywhere;}
        .notif-view{display:inline-flex;align-items:center;margin-top:8px;padding:6px 12px;border:1px solid #dfe4f0;border-radius:999px;background:#fff;color:var(--navy);font-size:12px;font-weight:700;}
        .notif-view:hover{border-color:var(--navy);background:#f7f9ff;}
        .announcement-modal{position:fixed;inset:0;z-index:4000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(12,15,45,.56);}
        .announcement-modal[hidden]{display:none;}
        .announcement-dialog{display:flex;flex-direction:column;width:min(680px,100%);max-height:min(80vh,760px);overflow:hidden;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 24px 70px rgba(12,15,77,.28);}
        .announcement-dialog-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid var(--line);}
        .announcement-dialog-head h3{margin:0;color:var(--navy);font-size:19px;}
        .announcement-dialog-head time{display:block;margin-top:5px;color:var(--muted);font-size:12px;}
        .announcement-dialog-close{width:34px;height:34px;flex:0 0 34px;border:0;border-radius:9px;background:#f1f3f8;color:var(--navy);font-size:21px;}
        .announcement-dialog-content{overflow:auto;padding:22px;color:#374151;font-size:14px;line-height:1.65;}
        .announcement-dialog-content p{margin:0 0 12px;}.announcement-dialog-content p:last-child{margin-bottom:0;}
        .announcement-dialog-content h1,.announcement-dialog-content h2,.announcement-dialog-content h3,.announcement-dialog-content h4{margin:18px 0 9px;line-height:1.3;color:var(--navy);}
        .announcement-dialog-content ul,.announcement-dialog-content ol{padding-left:24px;}
        .announcement-dialog-content blockquote{margin:12px 0;padding-left:14px;border-left:3px solid var(--gold);color:#5f6b85;}
        .announcement-dialog-content img{max-width:100%;height:auto;}
        .announcement-dialog-content a{color:var(--navy);text-decoration:underline;}
        .announcement-dialog-footer{display:flex;justify-content:flex-end;padding:14px 22px;border-top:1px solid var(--line);}
        .announcement-dialog-footer button{padding:8px 16px;border:0;border-radius:8px;background:var(--navy);color:#fff;font-size:13px;font-weight:700;}
        @media (max-width:900px){.content-grid{grid-template-columns:1fr;}}
        @media (max-width:700px){.page{padding:28px 16px 48px;}.list-card{padding:20px 16px;}.notif-action{display:none;}}
    </style>
</head>
<body>
    @php $user = auth()->user(); @endphp
    @include('components.authenticated-topbar', ['user' => $user])

    <main class="page">
        <x-flash-toast :message="session('success')" />

        @php
            $all = collect($notifications);
            $unreadCount = $unreadNotificationCount ?? $all->where('unread', true)->count();
            $totalCount = $all->count();
            $active = $filter ?? 'all';
            $visible = match ($active) {
                'unread' => $all->where('unread', true),
                'course' => $all->where('type', 'course'),
                'enrollment' => $all->where('type', 'enrollment'),
                'assessment' => $all->where('type', 'assessment'),
                'credential' => $all->whereIn('type', ['badge', 'certificate']),
                'announcement' => $all->where('type', 'announcement'),
                'system' => $all->where('type', 'system'),
                default => $all,
            };
            $homeRoute = auth()->check()
                ? (auth()->user()->isAdmin() ? 'admin.dashboard' : (auth()->user()->isFaculty() ? 'faculty.dashboard' : 'dashboard'))
                : 'Homepage';
        @endphp

        <section class="hero">
            <div><h2>Notifications</h2><p>Updates about your courses, reviews, credentials, and announcements.</p></div>
            <div class="hero-badges"><span class="pill"><strong>{{ $unreadCount }}</strong>Unread</span><span class="pill"><strong>{{ $totalCount }}</strong>Total</span></div>
        </section>

        <a href="{{ route($homeRoute) }}" class="btn-home"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.8V20h14V9.8"/></svg>Home</a>

        <div class="content-grid">
            <aside class="sidebar-card">
                <h3>Filter</h3>
                <a class="filter-item {{ $active === 'all' ? 'active' : '' }}" href="{{ route('notifications.index') }}">All notifications <span class="count">{{ $totalCount }}</span></a>
                <a class="filter-item {{ $active === 'unread' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">Unread <span class="count">{{ $unreadCount }}</span></a>
                <a class="filter-item {{ $active === 'course' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'course']) }}">Courses <span class="count">{{ $all->where('type', 'course')->count() }}</span></a>
                <a class="filter-item {{ $active === 'enrollment' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'enrollment']) }}">Enrollments <span class="count">{{ $all->where('type', 'enrollment')->count() }}</span></a>
                <a class="filter-item {{ $active === 'assessment' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'assessment']) }}">Assessments <span class="count">{{ $all->where('type', 'assessment')->count() }}</span></a>
                <a class="filter-item {{ $active === 'credential' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'credential']) }}">Credentials <span class="count">{{ $all->whereIn('type', ['badge', 'certificate'])->count() }}</span></a>
                <a class="filter-item {{ $active === 'announcement' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'announcement']) }}">Announcements <span class="count">{{ $all->where('type', 'announcement')->count() }}</span></a>
                <a class="filter-item {{ $active === 'system' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'system']) }}">System <span class="count">{{ $all->where('type', 'system')->count() }}</span></a>
            </aside>

            <section class="list-card">
            <div class="list-card-head"><h3>Recent updates</h3>
                @auth
                    <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button type="submit" @disabled($unreadCount === 0)>Mark all as read</button></form>
                @endauth
            </div>
            @forelse ($visible as $notification)
                <div class="notif-item {{ $notification->unread ? 'unread' : '' }}">
                    <div class="notif-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></div>
                    <div style="flex:1;min-width:0;"><div class="notif-title"><strong>{{ $notification->title }}</strong>@if ($notification->unread)<span class="unread-dot" aria-hidden="true"></span>@endif</div><div class="notif-message">{{ $notification->message }}</div><div class="notif-meta">{{ $notification->time }} · {{ ucfirst($notification->type) }}</div>
                        @if ($notification->type === 'announcement')
                            <button class="notif-view" type="button" data-view-announcement="{{ $notification->entity_id }}" data-title="{{ $notification->title }}" data-time="{{ $notification->time }}">View</button>
                            <template id="announcement-content-{{ $notification->entity_id }}"><div class="ck-content">{!! $notification->content !!}</div></template>
                        @endif
                    </div>
                    @if (!empty($notification->url))<a href="{{ $notification->url }}" class="notif-action">View</a>@endif
                </div>
            @empty
                <div style="padding:34px 8px;text-align:center;color:var(--muted);font-size:14px;">{{ $active === 'unread' ? "You're all caught up — nothing unread." : 'Nothing to show in this filter.' }}</div>
            @endforelse
            </section>
        </div>
    </main>

    <div class="announcement-modal" id="announcement-modal" hidden>
        <section class="announcement-dialog" role="dialog" aria-modal="true" aria-labelledby="announcement-dialog-title">
            <header class="announcement-dialog-head">
                <div><h3 id="announcement-dialog-title"></h3><time id="announcement-dialog-time"></time></div>
                <button class="announcement-dialog-close" type="button" data-close-announcement aria-label="Close announcement">&times;</button>
            </header>
            <div class="announcement-dialog-content ck-content" id="announcement-dialog-content"></div>
            <footer class="announcement-dialog-footer"><button type="button" data-close-announcement>Close</button></footer>
        </section>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('announcement-modal');
            var title = document.getElementById('announcement-dialog-title');
            var time = document.getElementById('announcement-dialog-time');
            var content = document.getElementById('announcement-dialog-content');

            function closeAnnouncement() {
                modal.hidden = true;
                content.replaceChildren();
            }

            document.querySelectorAll('[data-view-announcement]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var template = document.getElementById('announcement-content-' + button.dataset.viewAnnouncement);
                    if (!template) return;

                    title.textContent = button.dataset.title || 'Announcement';
                    time.textContent = button.dataset.time || '';
                    content.replaceChildren(template.content.cloneNode(true));
                    modal.hidden = false;
                    modal.querySelector('[data-close-announcement]').focus();
                });
            });

            modal.querySelectorAll('[data-close-announcement]').forEach(function (button) {
                button.addEventListener('click', closeAnnouncement);
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeAnnouncement();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) closeAnnouncement();
            });
        })();
    </script>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
