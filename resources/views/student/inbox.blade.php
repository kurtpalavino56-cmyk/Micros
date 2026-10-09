@php
    $isFacultyInbox = $isFacultyInbox ?? false;
    $inboxIndexRoute = $isFacultyInbox ? 'faculty.inbox' : 'inbox.index';
    $inboxReplyRoute = $isFacultyInbox ? 'faculty.inbox.reply' : 'inbox.reply';
    $newMessageRoute = $isFacultyInbox ? 'help.store' : 'inbox.store';
@endphp
{{--
    Shared student/faculty Inbox UI for private support threads with admins.
    Expects: $user, $threads, $selected, $unreadCount, and optional $isFacultyInbox.
--}}
<x-layout :title="$isFacultyInbox ? 'Inbox | Upskill Faculty' : 'My Inbox | Upskill'" :shell="$isFacultyInbox ? 'faculty' : 'student'" page="inbox">
    @vite('resources/css/inbox-reply.css')
    <div class="inbox-page {{ $isFacultyInbox ? 'faculty-inbox-page' : 'student-inbox-page' }}">
    <div class="wrap">
    <div class="page-head ui-page-header {{ $isFacultyInbox ? 'faculty-page-heading' : 'student-page-heading' }}">
        <div class="ui-page-header__content">
            <h2 class="ui-page-title">{{ $isFacultyInbox ? 'Inbox' : 'My Inbox' }}</h2>
            <p class="ui-page-subtitle">Private message threads with administrators appear here.</p>
        </div>
        <details class="new-message-menu">
            <summary class="new-message-toggle"><span aria-hidden="true">+</span> New Message</summary>
            <div class="new-message-body panel ui-card-surface">
                <form method="POST" action="{{ route($newMessageRoute) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                               placeholder="What is this about?" required>
                    </div>
                    <div class="field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Describe your concern..." required>{{ old('message') }}</textarea>
                    </div>
                    <div class="field">
                        <label>Attach an Image (optional)</label>
                        <div class="file-picker">
                            <select class="file-type-select" id="attType" onchange="attTypeChanged()">
                                <option value="">Select file type...</option>
                                <option value="JPG">JPG / JPEG</option>
                                <option value="PNG">PNG</option>
                                <option value="WEBP">WEBP</option>
                                <option value="GIF">GIF</option>
                            </select>
                            <label class="file-row is-disabled" id="attRow">
                                <span class="file-btn">Choose File</span>
                                <span class="file-name" id="attName">Select a file type first</span>
                                <input type="file" name="attachment" id="attInput" disabled
                                       onchange="attFileChanged(this)">
                            </label>
                        </div>
                        <p class="att-hint">A screenshot helps the administrators understand faster. Max 10 MB.</p>
                        <div class="att-preview" id="attPreview"><img id="attPreviewImg" alt="Selected attachment"></div>
                    </div>

                    <button type="submit" class="btn-navy ui-button ui-button--primary">Send</button>
                </form>
            </div>
        </details>
    </div>

    <x-flash-toast :message="session('success')" />
    @if ($errors->any())
        <div class="alert alert-err">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid">
        {{-- â”€â”€ Threads â”€â”€ --}}
        <nav class="panel ui-card-surface" aria-label="Your message threads">
            <div class="panel-hd ui-card-header">
                <span class="ui-card-header__title">My Messages</span>
                @if (($unreadCount ?? 0) > 0)<span class="count-pill">{{ $unreadCount }}</span>@endif
            </div>
            @forelse ($threads as $t)
                <a class="thread {{ $selected && $selected->id === $t->id ? 'active' : '' }}"
                   @if($selected && $selected->id === $t->id) aria-current="page" @endif
                   href="{{ route($inboxIndexRoute, ['thread' => $t->id]) }}">
                    <div class="thread-top">
                        <span class="thread-subject">{{ $t->subject }}</span>
                        @if ($t->unreadForStudent())<span class="dot" role="img" aria-label="Unread message" title="Unread message"></span>@endif
                    </div>
                    <div class="thread-meta">
                        {{ $t->lastActivityAt()?->diffForHumans() }}
                        <span class="status ui-badge {{ $t->status }} {{ $t->status === 'resolved' ? 'ui-badge--success' : ($t->status === 'open' ? 'ui-badge--warning' : '') }}">{{ ucfirst($t->status) }}</span>
                    </div>
                </a>
            @empty
                <div class="empty ui-empty-state">You haven't sent any messages yet.</div>
            @endforelse
        </nav>

        {{-- â”€â”€ Conversation â”€â”€ --}}
        <div class="panel ui-card-surface">
            @if ($selected)
                <div class="detail-hd ui-card-header">
                    <div class="ui-card-header__content">
                        <h3 class="ui-section-title">{{ $selected->subject }}</h3>
                    <div class="who">Sent {{ $selected->created_at?->format('M j, Y g:i A') }}</div>
                    </div>
                </div>

                <div class="msgs">
                    <div class="msg from-me">
                        <div>
                            <div class="msg-who text-right">You</div>
                            <div class="bubble">{{ $selected->message }}</div>
                            @if ($selected->attachment_url)
                                <div class="msg-attach">
                                    @if ($selected->attachmentIsImage())
                                        <img src="{{ route('complaints.attachment', $selected->id) }}"
                                             alt="{{ $selected->attachment_name }}"
                                             onclick="openLightbox(this.src)">
                                    @else
                                        <a class="message-attachment-link" href="{{ route('complaints.attachment', $selected->id) }}" target="_blank">
                                            ðŸ“Ž {{ $selected->attachment_name ?? 'Attachment' }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @foreach ($selected->replies as $reply)
                        <div class="msg {{ $reply->is_admin ? 'from-admin' : 'from-me' }}">
                            <div>
                                <div class="msg-who {{ $reply->is_admin ? '' : 'text-right' }}">
                                    {{ $reply->is_admin ? 'Administrator' : 'You' }}
                                    - {{ $reply->created_at?->diffForHumans() }}
                                </div>
                                <div class="bubble">{{ $reply->body }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reply-box">
                    <form class="inbox-reply-compose" method="POST" action="{{ route($inboxReplyRoute, $selected->id) }}">
                        @csrf
                        <textarea name="body" placeholder="Write a reply..." required></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn-navy ui-button ui-button--primary">Send Reply</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="empty ui-empty-state">Select a message on the left, or start a new message above.</div>
            @endif
        </div>
    </div>

</div>

{{-- Full-size attachment viewer --}}
<div id="lightbox" onclick="closeLightbox(event)">
    <button type="button" class="lb-close" onclick="closeLightbox(event, true)" aria-label="Close">&times;</button>
    <img id="lightbox-img" src="" alt="Attachment">
</div>
    </div>

<script>
    var ATT_TYPES = { JPG: ['jpg','jpeg'], PNG: ['png'], WEBP: ['webp'], GIF: ['gif'] };

    function attTypeChanged() {
        var sel = document.getElementById('attType'), input = document.getElementById('attInput'),
            row = document.getElementById('attRow'),  span  = document.getElementById('attName');
        if (!sel || !input || !row || !span) return;

        var exts = ATT_TYPES[sel.value];
        input.value = '';
        document.getElementById('attPreview').style.display = 'none';

        if (!exts) {
            input.disabled = true;
            input.removeAttribute('accept');
            row.classList.add('is-disabled');
            span.textContent = 'Select a file type first';
            return;
        }

        input.disabled = false;
        input.setAttribute('accept', exts.map(function (e) { return '.' + e; }).join(','));
        row.classList.remove('is-disabled');
        span.textContent = 'No File Chosen';
    }

    function attFileChanged(input) {
        var span = document.getElementById('attName'), sel = document.getElementById('attType'),
            preview = document.getElementById('attPreview'), img = document.getElementById('attPreviewImg');
        if (!span) return;

        if (!input.files || !input.files.length) {
            span.textContent = sel && sel.value ? 'No File Chosen' : 'Select a file type first';
            preview.style.display = 'none';
            return;
        }

        var file = input.files[0];
        var ext  = (file.name.split('.').pop() || '').toLowerCase();
        var exts = sel ? ATT_TYPES[sel.value] : null;

        if (exts && exts.indexOf(ext) === -1) {
            alert('"' + file.name + '" is not a ' + sel.options[sel.selectedIndex].text + ' file.');
            input.value = '';
            span.textContent = 'No File Chosen';
            preview.style.display = 'none';
            return;
        }

        span.textContent = file.name;
        img.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox(e, force) {
        if (!force && e && e.target.id === 'lightbox-img') return;
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox(null, true);
    });
</script>

</x-layout>
