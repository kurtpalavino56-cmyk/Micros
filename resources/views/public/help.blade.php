{{--
    resources/views/help.blade.php

    Public Help Center, reached from the footer. Anyone can send a message —
    a visitor supplies a name and email so the admin can reply by mail, while
    a signed-in student's thread appears in their inbox.

    Extends layouts.app so the header and footer match the homepage.
--}}
@extends('layouts.app')

@section('title', 'Help Center – UpSkill PSU')

@push('styles')
<style>
    .help { padding: 3.5rem 0 4.5rem; background: #f4f6fb; }

    .help__head { text-align: center; margin-bottom: 2.2rem; }
    .help__title {
        font-family: var(--font-display);
        font-size: clamp(1.7rem, 3vw, 2.2rem);
        font-weight: 800;
        color: var(--navy);
        margin-bottom: 0.4rem;
    }
    .help__sub { color: var(--text-muted); font-size: 0.95rem; }

    .help__card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        padding: 2rem 2.1rem 2.2rem;
        max-width: 720px;
        margin: 0 auto;
    }

    .help__field { margin-bottom: 1.15rem; }
    /* Direct children only. The file picker's <label class="file-row"> also
       sits inside .help__field, and this rule was overriding its
       display:flex (stacking the two halves) and shouting its text in
       uppercase. */
    .help__field > label {
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.4rem;
    }
    .help__field input[type=text],
    .help__field input[type=email],
    .help__field textarea {
        width: 100%;
        border: 1.5px solid #c9ccdb;
        border-radius: 10px;
        padding: 0.7rem 0.9rem;
        font-size: 0.92rem;
        font-family: var(--font-body);
        color: var(--navy);
        background: var(--white);
    }
    .help__field textarea { min-height: 150px; resize: vertical; }
    .help__field input:focus,
    .help__field textarea:focus { outline: none; border-color: var(--navy); }

    .help__row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    /* ── File type picker — same pattern as the faculty course form ── */
    /* One shared height keeps the dropdown and the file box identical —
       relying on padding alone let them drift, because a <select> and a
       flex row compute their intrinsic height differently. */
    .file-picker { --picker-h: 46px; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
    .file-picker > .file-type-select { flex: 0 0 auto; }
    .file-type-select {
        border: 1.5px solid #c9ccdb;
        border-radius: 10px;
        background: var(--white);
        color: var(--navy);
        font-size: 0.82rem;
        font-weight: 700;
        height: var(--picker-h);
        padding: 0 1.75rem 0 0.95rem;
        cursor: pointer;
        width: auto;
        -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230a1f6e' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 9px center;
        background-size: 11px 11px;
    }
    .file-type-select::-ms-expand { display: none; }
    .file-type-select:focus { outline: none; border-color: var(--navy); }

    /* Same box as .file-type-select — matching border, radius and height —
       and it grows to take the rest of the row. */
    .file-row {
        display: flex;
        align-items: stretch;
        border: 1.5px solid #c9ccdb;
        border-radius: 10px;
        overflow: hidden;
        background: var(--white);
        cursor: pointer;
        flex: 1 1 240px;
        min-width: 0;
        height: var(--picker-h);
        margin: 0;
        transition: border-color var(--transition), box-shadow var(--transition);
    }
    .file-row:not(.is-disabled):hover {
        border-color: var(--navy);
        box-shadow: 0 2px 10px rgba(10,31,110,0.10);
    }
    .file-row .file-btn {
        display: flex;
        align-items: center;
        text-transform: none;
        letter-spacing: 0;
        margin: 0;
        background: var(--white);
        border-right: 1.5px solid #dfe2ee;
        padding: 0 1.2rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--navy);
        white-space: nowrap;
    }
    .file-row .file-name {
        display: flex;
        align-items: center;
        text-transform: none;
        letter-spacing: 0;
        margin: 0;
        padding: 0 1.05rem;
        font-size: 0.82rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-row input[type=file] { display: none; }
    /* Locked until a file type is chosen. The control stays crisp — only
       the placeholder text is muted — because dimming the whole thing with
       opacity made it look broken. pointer-events:none is what actually
       blocks the click: a disabled <input> inside a <label> still lets the
       click through in some browsers. */
    .file-row.is-disabled {
        cursor: not-allowed;
        pointer-events: none;
    }
    .file-row.is-disabled .file-btn { color: var(--navy); }
    .file-row.is-disabled .file-name { color: #aab0c0; }

    .help__hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; }

    .help__preview { margin-top: 0.8rem; display: none; }
    .help__preview img {
        max-width: 220px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .help__alert {
        border-radius: 12px;
        padding: 0.8rem 1rem;
        font-size: 0.88rem;
        margin-bottom: 1.4rem;
    }
    .help__alert--ok  { background: #ecfdf3; border: 1px solid #a7f3d0; color: #065f46; }
    .help__alert--err { background: #fff1f2; border: 1px solid #fecdd3; color: #b91c1c; }

    .help__note {
        background: #f7f9ff;
        border: 1px solid #dfe4fb;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        font-size: 0.83rem;
        color: var(--navy);
        margin-bottom: 1.4rem;
    }

    @media (max-width: 560px) {
        .help__row { grid-template-columns: 1fr; }
        .help__card { padding: 1.5rem 1.2rem 1.8rem; }
    }
</style>
@endpush

@section('content')
<section class="help">
    <div class="container">

        <div class="help__head">
            <h1 class="help__title">Help Center</h1>
            <p class="help__sub">Send us a message and we will get back to you soon.</p>
        </div>

        <div class="help__card">

            @if (session('success'))
                <div class="help__alert help__alert--ok">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="help__alert help__alert--err">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            @if ($auth)
                <div class="help__note">
                    Signed in as <strong>{{ $auth->name }}</strong>.
                    @if ($auth->isStudent())
                        Replies will appear in your
                        <a href="{{ route('inbox.index') }}" style="text-decoration:underline;font-weight:700;">inbox</a>.
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('help.store') }}" enctype="multipart/form-data">
                @csrf

                @guest
                    {{-- A visitor has no account, so we need a way to reply. --}}
                    <div class="help__row">
                        <div class="help__field">
                            <label for="guest_name">Your Name</label>
                            <input type="text" id="guest_name" name="guest_name"
                                   value="{{ old('guest_name') }}" placeholder="Juan Dela Cruz" required>
                        </div>
                        <div class="help__field">
                            <label for="guest_email">Your Email</label>
                            <input type="email" id="guest_email" name="guest_email"
                                   value="{{ old('guest_email') }}" placeholder="you@example.com" required>
                        </div>
                    </div>
                @endguest

                <div class="help__field">
                    <label for="subject">Title</label>
                    <input type="text" id="subject" name="subject"
                           value="{{ old('subject') }}" placeholder="What is this about?" required>
                </div>

                <div class="help__field">
                    <label for="message">Description</label>
                    <textarea id="message" name="message"
                              placeholder="Describe your concern in detail…" required>{{ old('message') }}</textarea>
                </div>

                <div class="help__field">
                    <label>Attach an Image (optional)</label>
                    <div class="file-picker">
                        <select class="file-type-select" id="helpType" onchange="helpTypeChanged()">
                            <option value="">Select file type…</option>
                            <option value="JPG">JPG / JPEG</option>
                            <option value="PNG">PNG</option>
                            <option value="WEBP">WEBP</option>
                            <option value="GIF">GIF</option>
                        </select>
                        <label class="file-row is-disabled" id="helpRow">
                            <span class="file-btn">Choose File</span>
                            <span class="file-name" id="helpName">Select a file type first</span>
                            <input type="file" name="attachment" id="helpInput" disabled
                                   onchange="helpFileChanged(this)">
                        </label>
                    </div>
                    <p class="help__hint">A screenshot helps us understand the problem faster. Max 10 MB.</p>
                    <div class="help__preview" id="helpPreview"><img id="helpPreviewImg" alt="Selected attachment"></div>
                </div>

                <button type="submit" class="btn btn-navy">Send</button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Type-gated attachment picker: the file input stays locked until an
    // image type is chosen, then only accepts that extension.
    var HELP_TYPES = {
        JPG:  ['jpg', 'jpeg'],
        PNG:  ['png'],
        WEBP: ['webp'],
        GIF:  ['gif']
    };

    function helpTypeChanged() {
        var sel   = document.getElementById('helpType');
        var input = document.getElementById('helpInput');
        var row   = document.getElementById('helpRow');
        var span  = document.getElementById('helpName');
        if (!sel || !input || !row || !span) return;

        var exts = HELP_TYPES[sel.value];

        // Switching type always clears the choice.
        input.value = '';
        document.getElementById('helpPreview').style.display = 'none';

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

    function helpFileChanged(input) {
        var span    = document.getElementById('helpName');
        var sel     = document.getElementById('helpType');
        var preview = document.getElementById('helpPreview');
        var img     = document.getElementById('helpPreviewImg');
        if (!span) return;

        if (!input.files || !input.files.length) {
            span.textContent = sel && sel.value ? 'No File Chosen' : 'Select a file type first';
            preview.style.display = 'none';
            return;
        }

        var file = input.files[0];
        var ext  = (file.name.split('.').pop() || '').toLowerCase();
        var exts = sel ? HELP_TYPES[sel.value] : null;

        if (exts && exts.indexOf(ext) === -1) {
            alert('"' + file.name + '" is not a ' + sel.options[sel.selectedIndex].text + ' file.\n\n'
                + 'Choose a file ending in ' + exts.map(function (e) { return '.' + e; }).join(' or ')
                + ', or change the file type above.');
            input.value = '';
            span.textContent = 'No File Chosen';
            preview.style.display = 'none';
            return;
        }

        span.textContent = file.name;

        // Show what they picked, so a wrong screenshot is obvious before sending.
        img.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
</script>
@endpush