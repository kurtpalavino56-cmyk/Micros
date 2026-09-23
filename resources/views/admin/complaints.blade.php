{{--
    resources/views/admin/Complaints.blade.php
    Admin › Complaint Inbox — Help Center messages raised by students.
    Expects: $threads, $selected (Complaint|null), $unreadCount.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complaint Inbox | UPSKILL Admin</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;
          --red:#ef4444;--shadow:0 10px 25px rgba(19,23,107,.08);--topbar-h:60px;}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f5f7ff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .topbar{position:sticky;top:0;height:var(--topbar-h);background:var(--navy);display:flex;
        align-items:center;justify-content:space-between;padding:0 22px;z-index:200;
        box-shadow:0 2px 8px rgba(0,0,0,.25);}
    .topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:1.05rem;
        font-weight:800;letter-spacing:2px;text-transform:uppercase;}
    .brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;display:flex;
        align-items:center;justify-content:center;overflow:hidden;}
    .brand-logo img{width:100%;height:100%;object-fit:contain;}
    .topbar-right{display:flex;align-items:center;gap:10px;}
    /* White circular icon buttons, matching every other admin page. */
    .avatar-btn{width:40px;height:40px;border-radius:50%;background:#fff;color:var(--navy);
        display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
        border:1px solid rgba(255,255,255,.28);transition:transform .15s ease,box-shadow .15s ease;}
    .avatar-btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(0,0,0,.22);}
    .btn-danger{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;padding:8px 14px;
        border-radius:999px;font-weight:700;font-size:12.5px;}
    .btn-home{display:inline-flex;align-items:center;gap:7px;background:#fff;
        border:1px solid var(--line);color:var(--navy);padding:9px 17px;border-radius:999px;
        font-weight:700;font-size:12.5px;margin-bottom:12px;box-shadow:var(--shadow);
        transition:background .18s ease,color .18s ease,transform .18s ease;}
    .btn-home:hover{background:var(--navy);color:#fff;transform:translateY(-1px);}
    /* Matches the padding the other admin pages use (30px 34px 44px), so
       the heading is not flush against the sidebar. */
    .wrap{max-width:1180px;margin:0 auto;padding:34px 40px 52px;}
    .page-heading{font-size:1.6rem;font-weight:800;margin:0 0 5px;}
    .page-sub{color:var(--muted);font-size:.92rem;margin:0 0 20px;}
    .grid{display:grid;grid-template-columns:minmax(300px,360px) 1fr;gap:22px;align-items:start;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);}
    .panel-hd{padding:16px 20px;border-bottom:1px solid var(--line);font-weight:800;font-size:14px;
        display:flex;align-items:center;justify-content:space-between;}
    .count-pill{background:var(--red);color:#fff;border-radius:999px;font-size:11px;
        font-weight:800;padding:3px 9px;}
    .thread{display:block;padding:14px 18px;border-bottom:1px solid var(--line);}
    .thread:last-child{border-bottom:none;}
    .thread:hover{background:#f7f9ff;}
    .thread.active{background:#eef1fb;border-left:3px solid var(--navy);}
    .thread-top{display:flex;align-items:center;gap:8px;margin-bottom:3px;}
    .thread-subject{font-weight:800;font-size:14px;flex:1;min-width:0;overflow:hidden;
        text-overflow:ellipsis;white-space:nowrap;}
    .dot{width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;}
    .thread-badges{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:5px;}
    .thread-meta{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted);
        min-width:0;}
    .thread-who{font-weight:700;color:#4b5563;overflow:hidden;text-overflow:ellipsis;
        white-space:nowrap;max-width:150px;}
    .thread-dot{opacity:.6;}
    .clip{font-size:12px;line-height:1;}
    .status{display:inline-block;font-size:10px;font-weight:800;padding:3px 9px;
        border-radius:999px;line-height:1.3;white-space:nowrap;}
    .status.open{background:#fef3c7;color:#92400e;}
    .status.resolved{background:#e8f7ef;color:var(--green);}
    /* Where the message came from */
    .origin{display:inline-block;font-size:10px;font-weight:800;padding:3px 9px;
        border-radius:999px;letter-spacing:.4px;text-transform:uppercase;line-height:1.3;
        white-space:nowrap;}
    .origin.student{background:#e9ebfb;color:var(--navy);border:1px solid #c9cdf0;}
    .origin.visitor{background:#fdf3d7;color:#a16207;border:1px solid #f0dc9a;}
    /* Attachment preview */
    .attach{margin-top:10px;}
    .attach img{max-width:260px;max-height:200px;border-radius:12px;border:1px solid var(--line);
        cursor:zoom-in;display:block;transition:transform .15s ease;}
    .attach img:hover{transform:scale(1.02);}
    .attach-name{font-size:11.5px;color:var(--muted);margin-top:5px;}
    .attach-file{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--line);
        border-radius:10px;padding:9px 13px;font-size:12.5px;font-weight:700;color:var(--navy);
        background:#f7f9ff;}
    /* Lightbox */
    #lightbox{position:fixed;inset:0;background:rgba(8,11,45,.88);display:none;
        align-items:center;justify-content:center;z-index:9999;padding:32px;}
    #lightbox.open{display:flex;}
    #lightbox img{max-width:92vw;max-height:88vh;border-radius:10px;box-shadow:0 24px 60px rgba(0,0,0,.5);}
    #lightbox .lb-close{position:absolute;top:20px;right:26px;background:#fff;border:none;
        border-radius:999px;width:40px;height:40px;font-size:20px;font-weight:800;color:var(--navy);
        cursor:pointer;line-height:1;}
    .msgs{padding:22px 24px;max-height:520px;overflow-y:auto;}
    .msg{margin-bottom:16px;display:flex;}
    .msg .bubble{max-width:76%;padding:12px 15px;border-radius:14px;font-size:14px;
        line-height:1.55;white-space:pre-wrap;}
    .msg.from-student .bubble{background:#f1f3fb;color:#1f2937;border-bottom-left-radius:4px;}
    .msg.from-admin{justify-content:flex-end;}
    .msg.from-admin .bubble{background:var(--navy);color:#fff;border-bottom-right-radius:4px;}
    .msg-who{font-size:11.5px;color:var(--muted);margin-bottom:4px;font-weight:700;}
    .reply-box{border-top:1px solid var(--line);padding:18px 24px;}
    textarea{width:100%;border:1.5px solid #c9ccdb;border-radius:12px;padding:12px 14px;
        font-size:14px;font-family:inherit;min-height:96px;resize:vertical;color:var(--navy);}
    textarea:focus{outline:none;border-color:var(--navy);}
    .reply-actions{display:flex;justify-content:space-between;align-items:center;
        gap:10px;margin-top:12px;flex-wrap:wrap;}
    .btn-navy{background:var(--navy);color:#fff;border:none;border-radius:999px;
        padding:11px 24px;font-weight:800;font-size:13.5px;}
    .btn-ghost{background:#fff;border:1.5px solid var(--line);color:var(--navy);
        border-radius:999px;padding:10px 18px;font-weight:700;font-size:13px;}
    .alert{padding:11px 14px;border-radius:12px;margin-bottom:18px;font-size:14px;
        background:#ecfdf3;border:1px solid #a7f3d0;color:#065f46;}
    .alert-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
    .mail-note{font-size:12px;color:var(--muted);margin-top:8px;}
    .empty{color:var(--muted);text-align:center;padding:44px 12px;font-size:14px;}
    .detail-hd{padding:18px 24px;border-bottom:1px solid var(--line);}
    .detail-hd h2{margin:0 0 5px;font-size:17px;}
    .detail-hd .who{font-size:12.5px;color:var(--muted);}
    @media(max-width:1000px){.grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<style>.topbar{display:none!important;}.wrap{margin-top:0!important;}</style>
@if(false)
<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('admin.dashboard') }}" class="topbar-brand">
        <span class="brand-logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
        UPSKILL
    </a>
    <div class="topbar-right">
        <a href="{{ route('notifications.index') }}" class="avatar-btn" title="Notifications">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
        </a>
        <a href="{{ route('admin.profile') }}" class="avatar-btn" title="My Profile"
           @if(auth()->user()?->avatar_url)
               style="background-image:url('{{ auth()->user()->avatar_url }}');background-size:cover;background-position:center;overflow:hidden;"
           @endif>
            @unless(auth()->user()?->avatar_url)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V21.6h19.2V19.2c0-3.2-6.4-4.8-9.6-4.8z"/>
            </svg>
            @endunless
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;">
            @csrf
            <button type="submit" class="btn-danger">Logout</button>
        </form>
    </div>
</nav>
@endif

<div class="wrap">
    <h1 class="page-heading">Complaint Inbox</h1>
    <p class="page-sub">Messages sent by students through the Help Center. Reply and they will see it in their inbox.</p>

    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    {{-- Delivery failed: the reply is safely stored, but nothing was sent. --}}
    @error('mail')
        <div class="alert alert-warn">{{ $message }}</div>
    @enderror

    <div class="grid">
        {{-- ── Thread list ── --}}
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn-home">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.8V20h14V9.8"/>
                </svg>
                Home
            </a>

            <div class="panel">
            <div class="panel-hd">
                <span>Complaints</span>
                @if ($unreadCount > 0)<span class="count-pill">{{ $unreadCount }} new</span>@endif
            </div>

            @forelse ($threads as $t)
                <a class="thread {{ $selected && $selected->id === $t->id ? 'active' : '' }}"
                   href="{{ route('admin.complaints', ['thread' => $t->id]) }}">
                    <div class="thread-top">
                        <span class="thread-subject">{{ $t->subject }}</span>
                        @if ($t->unreadForAdmin())<span class="dot"></span>@endif
                    </div>
                    {{-- Badges on their own line, then the sender and time.
                         Mixing all four inline made them wrap mid-row. --}}
                    <div class="thread-badges">
                        <span class="origin {{ $t->isFromVisitor() ? 'visitor' : 'student' }}">
                            {{ $t->isFromVisitor() ? 'Visitor' : 'Student' }}
                        </span>
                        <span class="status {{ $t->status }}">{{ ucfirst($t->status) }}</span>
                        @if ($t->attachment_url)
                            <span class="clip" title="Has an attachment">📎</span>
                        @endif
                    </div>
                    <div class="thread-meta">
                        <span class="thread-who">{{ $t->senderName() }}</span>
                        <span class="thread-dot">·</span>
                        <span>{{ $t->lastActivityAt()?->diffForHumans() }}</span>
                    </div>
                </a>
            @empty
                <div class="empty">No complaints yet.</div>
            @endforelse
            </div>
        </div>

        {{-- ── Selected thread ── --}}
        <div class="panel">
            @if ($selected)
                <div class="detail-hd">
                    <h2>{{ $selected->subject }}</h2>
                    <div class="who">
                        <span class="origin {{ $selected->isFromVisitor() ? 'visitor' : 'student' }}">
                            {{ $selected->isFromVisitor() ? 'Website Visitor' : 'Student' }}
                        </span>
                        From {{ $selected->senderName() }}
                        @if ($selected->isFromVisitor())
                            @if ($selected->guest_email)
                                (<a href="mailto:{{ $selected->guest_email }}"
                                    style="text-decoration:underline;">{{ $selected->guest_email }}</a>)
                            @endif
                        @else
                            ({{ $selected->user->user_code ?? '—' }})
                        @endif
                        · {{ $selected->created_at?->format('M j, Y g:i A') }}
                    </div>
                </div>

                <div class="msgs">
                    {{-- Opening message --}}
                    <div class="msg from-student">
                        <div>
                            <div class="msg-who">{{ $selected->senderName() }}</div>
                            <div class="bubble">{{ $selected->message }}</div>

                            @if ($selected->attachment_url)
                                <div class="attach">
                                    @if ($selected->attachmentIsImage())
                                        {{-- Click to open full size --}}
                                        <img src="{{ asset($selected->attachment_url) }}"
                                             alt="{{ $selected->attachment_name }}"
                                             onclick="openLightbox(this.src)">
                                    @else
                                        <a class="attach-file" target="_blank"
                                           href="{{ asset($selected->attachment_url) }}">
                                            📎 {{ $selected->attachment_name ?? 'Attachment' }}
                                        </a>
                                    @endif
                                    <div class="attach-name">{{ $selected->attachment_name }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @foreach ($selected->replies as $reply)
                        <div class="msg {{ $reply->is_admin ? 'from-admin' : 'from-student' }}">
                            <div>
                                <div class="msg-who" style="{{ $reply->is_admin ? 'text-align:right;' : '' }}">
                                    {{ $reply->is_admin ? 'Administrator' : ($reply->author->name ?? 'Student') }}
                                    · {{ $reply->created_at?->diffForHumans() }}
                                </div>
                                <div class="bubble">{{ $reply->body }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reply-box">
                    <form method="POST" action="{{ route('admin.complaints.reply', $selected->id) }}">
                        @csrf
                        <textarea name="body"
                            placeholder="{{ $selected->isFromVisitor() ? 'Write your reply — it will be emailed to them…' : 'Write your reply to the student…' }}"
                            required></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn-navy">Send Reply</button>
                        </div>
                        @if ($selected->isFromVisitor() && $selected->guest_email)
                            <div class="mail-note">
                                ✉ This reply will be emailed to {{ $selected->guest_email }}.
                            </div>
                        @endif
                    </form>
                    <form method="POST" action="{{ route('admin.complaints.resolve', $selected->id) }}"
                          style="margin-top:10px;">
                        @csrf
                        <button type="submit" class="btn-ghost">
                            {{ $selected->status === 'resolved' ? 'Reopen complaint' : 'Mark as resolved' }}
                        </button>
                    </form>
                </div>
            @else
                <div class="empty">Select a complaint on the left to read and reply.</div>
            @endif
        </div>
    </div>
</div>

{{-- Full-size attachment viewer --}}
<div id="lightbox" onclick="closeLightbox(event)">
    <button type="button" class="lb-close" onclick="closeLightbox(event, true)" aria-label="Close">&times;</button>
    <img id="lightbox-img" src="" alt="Attachment">
</div>

<script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    // Clicking the backdrop or the close button dismisses it; clicking the
    // image itself does not.
    function closeLightbox(e, force) {
        if (!force && e && e.target.id === 'lightbox-img') return;
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox(null, true);
    });
</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
@include('components.admin-sidebar')

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')
</body>
</html>