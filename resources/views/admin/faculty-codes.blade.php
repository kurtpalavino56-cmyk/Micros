{{--
    resources/views/admin/Faculty_Codes.blade.php

    Admin › Faculty Codes — /Admin-facultycodes (admin.facultycodes)

    Shareable registration codes for faculty staff:
      • GREEN status = code is still available (can be shared)
      • RED status   = code has already been used for a faculty account

    Expected data from AdminController::facultyCodes():
        'codes' => collection of objects, each exposing:
            ->id, ->code, ->is_used (bool), ->used_by_name (string|null),
            ->used_at (Carbon|null), ->created_at (Carbon)
--}}
<x-admin.layout title="Faculty Codes | Upskill Admin" page="admin-faculty-codes">
    <x-slot:head>
        {{-- Browser tab icon (favicon) --}}
            <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
@if(false)

{{-- ════════════════════════════════════ TOP NAV ══════════════════════════════════ --}}
<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('Homepage') }}" class="topbar-brand">
        <div class="brand-logo">
            <img src="{{ asset('Images/PSU-Logo.png') }}" alt="PSU Logo"
                 width="38" height="38" class="adm-inline-admin-faculty-codes-1"
                >
        </div>
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
        <form action="{{ route('logout') }}" method="POST" class="adm-inline-admin-faculty-codes-2">
            @csrf
            <button type="submit" class="adm-inline-admin-faculty-codes-3">Logout</button>
        </form>
    </div>
</nav>
@endif

<div class="layout">

{{-- ════════════════════════════════════ SIDEBAR ══════════════════════════════════ --}}
{{-- ════════════════════════════════════ MAIN CONTENT ═════════════════════════════ --}}
<main class="main">

    <div class="page-head">
        <div>
            <h2 class="page-title">Faculty Codes</h2>
            <p class="page-sub">Generate and share registration codes with faculty staff. Green = available · Red = already used.</p>
        </div>
        <form method="POST" action="{{ route('admin.facultycodes.generate') }}">
            @csrf
            <button type="submit" class="btn-generate">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Generate New Code
            </button>
        </form>
    </div>

    <x-flash-toast :message="session('success')" />

    <div class="code-stats">
        <div class="code-stat">
            <div class="num">{{ $totalCodes }}</div>
            <div class="lbl">Total Codes</div>
        </div>
        <div class="code-stat">
            <div class="num green">{{ $availableCount }}</div>
            <div class="lbl">Available</div>
        </div>
        <div class="code-stat">
            <div class="num red">{{ $usedCount }}</div>
            <div class="lbl">Used</div>
        </div>
    </div>

    @if ($codes->isEmpty())
        <div class="empty-state">
            <h4>No faculty codes yet</h4>
            <p>Click "Generate New Code" above to create the first one.</p>
        </div>
    @else
        <div class="table-card paginated-table-scroll">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Faculty Code</th>
                        <th>Status</th>
                        <th>Used By</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($codes as $code)
                    <tr>
                        <td><span class="code-pill">{{ $code->code }}</span></td>
                        <td>
                            @if ($code->is_used)
                                <span class="status-badge status-used">
                                    <span class="status-dot"></span> Used
                                </span>
                            @else
                                <span class="status-badge status-available">
                                    <span class="status-dot"></span> Available
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($code->is_used)
                                {{ $code->used_by_name ?? '—' }}
                                <div class="adm-inline-admin-faculty-codes-4">{{ $code->used_at?->format('M j, Y g:i A') }}</div>
                            @else
                                <span class="adm-inline-admin-faculty-codes-5">—</span>
                            @endif
                        </td>
                        <td>{{ $code->created_at?->format('M j, Y') }}</td>
                        <td>
                            <div class="adm-inline-admin-faculty-codes-6">
                                <button type="button" class="btn-copy" onclick="copyCode(this, '{{ $code->code }}')">Copy</button>
                                @unless ($code->is_used)
                                    <form method="POST" action="{{ route('admin.facultycodes.delete', $code->id) }}"
                                          onsubmit="return confirm('Delete this unused code?');" class="adm-inline-admin-faculty-codes-7">
                                        @csrf
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('components.pagination', ['paginator' => $codes])
    @endif

</main>
</div>{{-- /layout --}}

<script>
    function toggleSection(id) {
        const section = document.getElementById(id);
        if (!section || section.classList.contains('locked')) return;
        section.classList.toggle('open');
    }

    function copyCode(btn, code) {
        const done = () => {
            const original = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => { btn.textContent = original; }, 1500);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(done).catch(done);
        } else {
            const tmp = document.createElement('textarea');
            tmp.value = code;
            document.body.appendChild(tmp);
            tmp.select();
            document.execCommand('copy');
            document.body.removeChild(tmp);
            done();
        }
    }
</script>


{{-- ── Back to top (appears on long pages) ── --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
</x-admin.layout>