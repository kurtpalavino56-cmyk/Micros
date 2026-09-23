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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Codes | Upskill Admin</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --ink:#13176b;
        --text-muted:#6b7280;
        --white:#ffffff;
        --border:#e5e7eb;
        --radius-card:16px;
        --sidebar-w:225px;
        --shadow:0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#f5f7fe;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* ── Topbar ── */
    .topbar{background:var(--white);display:flex;align-items:center;justify-content:space-between;padding:12px 28px;gap:20px;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:50;}
    .topbar-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.15rem;letter-spacing:0.04em;color:var(--navy);}
    .brand-logo{width:36px;height:36px;border-radius:10px;background:#eef1ff;display:flex;align-items:center;justify-content:center;}
    .brand-logo svg{width:22px;height:22px;}
    .topbar-right{display:flex;align-items:center;gap:14px;}
    .search-wrap{display:flex;align-items:center;gap:8px;background:#f1f3fb;border:1px solid var(--border);border-radius:999px;padding:7px 14px;color:var(--text-muted);}
    .search-wrap input{border:none;background:transparent;outline:none;font-size:0.88rem;color:var(--ink);width:150px;}
    .avatar-btn{width:38px;height:38px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;}
    .avatar-btn:hover{background:var(--navy-deep);}

    /* ── Layout / Sidebar ── */
    .layout{display:flex;min-height:calc(100vh - 63px);}
    .sidebar{width:var(--sidebar-w);background:var(--white);border-right:1px solid var(--border);padding:18px 12px;flex-shrink:0;position:fixed;top:63px;bottom:0;overflow-y:auto;}
    .sb-section{margin-bottom:10px;border:1px solid var(--border);border-radius:12px;overflow:hidden;}
    .sb-section-hd{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;cursor:pointer;background:#fafbff;user-select:none;}
    .sb-hd-left{display:flex;flex-direction:column;gap:2px;}
    .sb-section-label{font-weight:700;font-size:0.88rem;}
    .sb-lesson-count{font-size:0.72rem;color:var(--text-muted);}
    .sb-chevron{color:var(--text-muted);transition:transform 0.2s;}
    .sb-section.open .sb-chevron{transform:rotate(180deg);}
    .sb-section .sb-items{display:none;padding:6px;}
    .sb-section.open .sb-items{display:block;}
    .sb-item{display:block;padding:9px 12px;border-radius:8px;font-size:0.86rem;color:var(--text-muted);font-weight:600;}
    .sb-item:hover{background:#f1f3fb;color:var(--ink);}
    .sb-item.active{background:var(--navy);color:#fff;}

    /* ── Main ── */
    .main{flex:1;margin-left:var(--sidebar-w);padding:28px;display:flex;flex-direction:column;gap:18px;}

    .page-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
    .page-title{font-size:1.35rem;font-weight:800;margin:0;}
    .page-sub{font-size:0.85rem;color:var(--text-muted);margin:4px 0 0;}

    .btn-generate{display:inline-flex;align-items:center;gap:8px;background:var(--navy);color:#fff;border:none;border-radius:10px;padding:11px 20px;font-weight:700;font-size:0.9rem;transition:background 0.2s, transform 0.2s;}
    .btn-generate:hover{background:var(--navy-deep);transform:translateY(-1px);}

    .alert-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;border-radius:10px;padding:11px 16px;font-size:0.88rem;font-weight:600;max-width:780px;}

    /* Stats strip */
    .code-stats{display:flex;gap:14px;flex-wrap:wrap;}
    .code-stat{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-card);box-shadow:var(--shadow);padding:16px 22px;min-width:150px;}
    .code-stat .num{font-size:1.6rem;font-weight:800;line-height:1.1;}
    .code-stat .lbl{font-size:0.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;}
    .code-stat .num.green{color:#15803d;}
    .code-stat .num.red{color:#b91c1c;}

    /* Table */
    .table-card{width:100%;max-width:980px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-card);box-shadow:var(--shadow);overflow:hidden;}
    .um-table{width:100%;border-collapse:collapse;}
    .um-table thead tr{background:#fafbff;border-bottom:1px solid var(--border);}
    .um-table thead th{text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);padding:13px 16px;font-weight:700;}
    .um-table thead th:first-child{padding-left:22px;}
    .um-table thead th:last-child{padding-right:22px;}
    .um-table tbody tr{border-bottom:1px solid #f1f3fb;}
    .um-table tbody tr:last-child{border-bottom:none;}
    .um-table tbody tr:hover{background:#f7f9ff;}
    .um-table tbody td{padding:13px 16px;font-size:0.88rem;}
    .um-table tbody td:first-child{padding-left:22px;}
    .um-table tbody td:last-child{padding-right:22px;}

    .code-pill{font-family:Consolas, "Courier New", monospace;font-weight:700;background:#eef1ff;color:var(--navy);padding:4px 12px;border-radius:999px;font-size:0.85rem;letter-spacing:0.05em;}

    /* Green / Red usage indicator */
    .status-badge{display:inline-flex;align-items:center;gap:7px;font-weight:700;font-size:0.82rem;}
    .status-dot{width:10px;height:10px;border-radius:50%;display:inline-block;}
    .status-available{color:#15803d;}
    .status-available .status-dot{background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,0.15);}
    .status-used{color:#b91c1c;}
    .status-used .status-dot{background:#ef4444;box-shadow:0 0 0 4px rgba(239,68,68,0.15);}

    .btn-copy{background:#eef1ff;border:1px solid #dbe2ff;color:var(--navy);border-radius:8px;padding:6px 12px;font-size:0.78rem;font-weight:700;transition:background 0.2s;}
    .btn-copy:hover{background:#dbe2ff;}
    .btn-delete{background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:8px;padding:6px 12px;font-size:0.78rem;font-weight:700;transition:background 0.2s;}
    .btn-delete:hover{background:#ffe4e6;}

    .empty-state{width:100%;max-width:980px;padding:32px 24px;border-radius:var(--radius-card);background:var(--white);border:1.5px solid var(--border);color:var(--text-muted);text-align:center;}
    .empty-state h4{margin:0 0 6px;color:var(--ink);}
    .empty-state p{margin:0;font-size:0.88rem;}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<style>.topbar{display:none!important;}.wrap{margin-top:0!important;}</style>
@if(false)

{{-- ════════════════════════════════════ TOP NAV ══════════════════════════════════ --}}
<nav class="topbar" hidden aria-hidden="true">
    <a href="{{ route('Homepage') }}" class="topbar-brand">
        <div class="brand-logo">
            <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"
                 width="38" height="38"
                 style="width:38px;height:38px;object-fit:contain;border-radius:50%;">
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
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;align-items:center;">
            @csrf
            <button type="submit" style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:8px 12px;font-weight:700;cursor:pointer;">Logout</button>
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

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @php
        $availableCount = $codes->where('is_used', false)->count();
        $usedCount      = $codes->where('is_used', true)->count();
    @endphp

    <div class="code-stats">
        <div class="code-stat">
            <div class="num">{{ $codes->count() }}</div>
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
        <div class="table-card">
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
                                <div style="font-size:0.75rem;color:var(--text-muted);">{{ $code->used_at?->format('M j, Y g:i A') }}</div>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>{{ $code->created_at?->format('M j, Y') }}</td>
                        <td>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <button type="button" class="btn-copy" onclick="copyCode(this, '{{ $code->code }}')">Copy</button>
                                @unless ($code->is_used)
                                    <form method="POST" action="{{ route('admin.facultycodes.delete', $code->id) }}"
                                          onsubmit="return confirm('Delete this unused code?');" style="margin:0;">
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
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    #back-to-top-btn svg{width:22px;height:22px;}
</style>
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
    @include('components.admin-sidebar')
    
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>