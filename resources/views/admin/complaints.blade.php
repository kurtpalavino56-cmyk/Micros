{{--
    resources/views/admin/Complaints.blade.php
    Admin › Inbox — Help Center messages from students, faculty, and visitors.
    Expects: $threads, $selected (Complaint|null), $unreadCount.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inbox | UPSKILL Admin</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    @vite('resources/css/inbox-reply.css')
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;
          --red:#ef4444;--shadow:0 10px 25px rgba(19,23,107,.08);--topbar-h:60px;}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f5f7ff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    
    /* Matches the padding the other admin pages use (30px 34px 44px), so
       the heading is not flush against the sidebar. */
    .wrap{max-width:1440px;margin:0 auto;padding:20px 34px 42px;}
    .page-heading{font-size:1.6rem;font-weight:800;margin:0 0 4px;}
    .page-sub{color:var(--muted);font-size:.92rem;margin:0 0 16px;}
    .grid{display:grid;grid-template-columns:minmax(300px,360px) minmax(0,1fr);gap:16px;align-items:start;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 24px rgba(19,23,107,.06);overflow:hidden;}
    .panel-hd{min-height:54px;padding:14px 18px;background:#f8faff;border-bottom:1px solid #e8ebf2;font-weight:800;font-size:14px;
        display:flex;align-items:center;justify-content:space-between;}
    .count-pill{background:var(--red);color:#fff;border-radius:999px;font-size:11px;
        font-weight:800;padding:3px 9px;}
    .thread{display:block;padding:15px 17px;border-left:3px solid transparent;border-bottom:1px solid var(--line);transition:background .15s ease,border-color .15s ease;}
    .thread:last-child{border-bottom:none;}
    .thread:hover{background:#f7f9ff;border-left-color:#c5d0ee;}
    .thread.active{background:#eef2ff;border-left-color:var(--navy);}
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
    .origin.faculty{background:#e7f5f3;color:#12635e;border:1px solid #b9e2dc;}
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
    .msgs{padding:22px 24px;min-height:250px;max-height:520px;overflow-y:auto;}
    .msg{margin-bottom:16px;display:flex;}
    .msg .bubble{max-width:76%;padding:12px 15px;border-radius:14px;font-size:14px;
        line-height:1.55;white-space:pre-wrap;}
    .msg.from-student .bubble{background:#f1f3fb;color:#1f2937;border-bottom-left-radius:4px;}
    .msg.from-admin{justify-content:flex-end;}
    .msg.from-admin .bubble{background:var(--navy);color:#fff;border-bottom-right-radius:4px;}
    .msg-who{font-size:11.5px;color:var(--muted);margin-bottom:4px;font-weight:700;}
    .reply-box{border-top:1px solid var(--line);padding:16px 24px 20px;}
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
    .thread-filters{display:grid;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line);background:#fff;}
    .thread-search,.thread-filter-select{width:100%;min-height:40px;padding:9px 11px;border:1px solid #cbd5e1;
        border-radius:9px;background:#fff;color:#172554;font:inherit;font-size:13px;}
    .thread-search:focus,.thread-filter-select:focus{outline:2px solid rgba(19,23,107,.2);border-color:var(--navy);}
    .thread-filter-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
    .thread-filter-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--muted);font-size:11.5px;}
    .thread-filter-clear{border:0;padding:0;background:none;color:var(--navy);font-size:11.5px;font-weight:700;text-decoration:underline;}
    .thread-filter-empty{display:none;padding:20px 14px;color:var(--muted);font-size:13px;text-align:center;}
    .thread-filter-empty.is-visible{display:block;}
    .thread[hidden]{display:none;}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
    .inbox-page .page-heading{font-size:1.75rem;letter-spacing:-.035em;color:#111a3c;}
    .inbox-page .page-sub{margin-bottom:22px;line-height:1.55;}
    .inbox-page .detail-hd{background:#f8faff;}
    .inbox-page .detail-hd h2{font-size:18px;line-height:1.35;color:#111a3c;}
    .inbox-page .btn-navy{border-radius:9px;transition:background .15s ease,transform .15s ease;}
    .inbox-page .btn-navy:hover{background:#20278a;transform:translateY(-1px);}
    @media(max-width:1000px){.inbox-page .grid{grid-template-columns:1fr;}}
    @media(max-width:680px){
        .wrap{padding:20px 14px 32px;}
        .page-heading{font-size:1.45rem;}
        .thread-filter-row{grid-template-columns:1fr;}
        .msgs{min-height:180px;max-height:55vh;padding:17px 15px;}
        .detail-hd,.reply-box{padding-right:16px;padding-left:16px;}
    }
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])

<div class="wrap inbox-page">
    <h1 class="page-heading">Inbox</h1>
    <p class="page-sub">Message threads from students, faculty, and website visitors. Reply here to continue the conversation.</p>

    <x-flash-toast :message="session('success')" />

    {{-- Delivery failed: the reply is safely stored, but nothing was sent. --}}
    @error('mail')
        <div class="alert alert-warn">{{ $message }}</div>
    @enderror

    <div class="grid">
        {{-- ── Thread list ── --}}
        <nav class="panel" aria-label="Complaint threads">
            <div class="panel-hd">
                <span>Complaints</span>
                @if ($unreadCount > 0)<span class="count-pill">{{ $unreadCount }} new</span>@endif
            </div>
            <div class="thread-filters" role="search" aria-label="Filter complaint threads">
                <label for="thread-search" class="sr-only">Search complaints</label>
                <input class="thread-search" id="thread-search" type="search" placeholder="Search subject or sender…" autocomplete="off">
                <div class="thread-filter-row">
                    <label for="thread-status" class="sr-only">Filter by status</label>
                    <select class="thread-filter-select" id="thread-status">
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <label for="thread-origin" class="sr-only">Filter by sender type</label>
                    <select class="thread-filter-select" id="thread-origin">
                        <option value="">All senders</option>
                        <option value="student">Students</option>
                        <option value="faculty">Faculty</option>
                        <option value="visitor">Visitors</option>
                    </select>
                </div>
                <div class="thread-filter-meta">
                    <span id="thread-result-count" aria-live="polite"></span>
                    <button class="thread-filter-clear" id="thread-filter-clear" type="button">Clear filters</button>
                </div>
            </div>

            @forelse ($threads as $t)
                <a class="thread {{ $selected && $selected->id === $t->id ? 'active' : '' }}"
                   data-subject="{{ $t->subject }}"
                   data-sender="{{ $t->senderName() }} {{ $t->senderContact() }}"
                   data-status="{{ strtolower($t->status) }}"
                   data-origin="{{ strtolower($t->senderRoleLabel()) }}"
                   @if($selected && $selected->id === $t->id) aria-current="page" @endif
                   href="{{ route('admin.complaints', ['thread' => $t->id]) }}">
                    <div class="thread-top">
                        <span class="thread-subject">{{ $t->subject }}</span>
                        @if ($t->unreadForAdmin())<span class="dot"></span>@endif
                    </div>
                    {{-- Badges on their own line, then the sender and time.
                         Mixing all four inline made them wrap mid-row. --}}
                    <div class="thread-badges">
                        <span class="origin {{ strtolower($t->senderRoleLabel()) }}">
                            {{ $t->senderRoleLabel() }}
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
            <div class="thread-filter-empty" id="thread-filter-empty">No complaints match these filters.</div>
        </nav>

        {{-- ── Selected thread ── --}}
        <div class="panel">
            @if ($selected)
                <div class="detail-hd">
                    <h2>{{ $selected->subject }}</h2>
                    <div class="who">
                        <span class="origin {{ strtolower($selected->senderRoleLabel()) }}">
                            {{ $selected->senderRoleLabel() }}
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
                                        <img src="{{ route('complaints.attachment', $selected->id) }}"
                                             alt="{{ $selected->attachment_name }}"
                                             onclick="openLightbox(this.src)">
                                    @else
                                        <a class="attach-file" target="_blank"
                                           href="{{ route('complaints.attachment', $selected->id) }}">
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
                    <form class="inbox-reply-compose" method="POST" action="{{ route('admin.complaints.reply', $selected->id) }}">
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
    (function () {
        var search = document.getElementById('thread-search');
        var status = document.getElementById('thread-status');
        var origin = document.getElementById('thread-origin');
        var clear = document.getElementById('thread-filter-clear');
        var count = document.getElementById('thread-result-count');
        var noResults = document.getElementById('thread-filter-empty');
        var threads = Array.prototype.slice.call(document.querySelectorAll('.thread[data-status]'));

        function applyThreadFilters() {
            var query = search.value.trim().toLocaleLowerCase();
            var visibleCount = 0;

            threads.forEach(function (thread) {
                var matchesQuery = (thread.dataset.subject + ' ' + thread.dataset.sender)
                    .toLocaleLowerCase().indexOf(query) !== -1;
                var matchesStatus = !status.value || thread.dataset.status === status.value;
                var matchesOrigin = !origin.value || thread.dataset.origin === origin.value;
                var isVisible = matchesQuery && matchesStatus && matchesOrigin;

                thread.hidden = !isVisible;
                if (isVisible) visibleCount += 1;
            });

            count.textContent = 'Showing ' + visibleCount + ' of ' + threads.length;
            noResults.classList.toggle('is-visible', threads.length > 0 && visibleCount === 0);
        }

        search.addEventListener('input', applyThreadFilters);
        status.addEventListener('change', applyThreadFilters);
        origin.addEventListener('change', applyThreadFilters);
        clear.addEventListener('click', function () {
            search.value = '';
            status.value = '';
            origin.value = '';
            applyThreadFilters();
            search.focus();
        });

        applyThreadFilters();
    }());

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
