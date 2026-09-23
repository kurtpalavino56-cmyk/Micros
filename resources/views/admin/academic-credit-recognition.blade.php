<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Credit Recognition | UPSKILL</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        body { margin:0; background:#f6f8fc; color:#17233c; font-family:"Segoe UI",system-ui,-apple-system,sans-serif; }
        .recognition-main { padding:30px 32px 48px !important; box-sizing:border-box; }
        .recognition-content { max-width:1180px; margin:0 auto; }
        .recognition-head { margin-bottom:22px; }
        .recognition-eyebrow { color:#a06f00; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        .recognition-head h1 { margin:5px 0; color:#0d1b6e; font-size:32px; line-height:1.15; }
        .recognition-head p { margin:0; color:#68758c; font-size:14px; }
        .notice { padding:12px 14px; border-radius:10px; margin-bottom:16px; font-size:13px; }
        .ok { background:#ecfdf3; color:#067647; }
        .err { background:#fff1f2; color:#b42318; }
        .recognition-list { display:grid; gap:12px; }
        .recognition-card { background:#fff; border:1px solid #e2e8f3; border-radius:14px; padding:18px 20px; box-shadow:0 6px 18px rgba(13,27,110,.05); }
        .recognition-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
        .meta { background:#f8fafc; border-radius:9px; padding:10px; min-width:0; }
        .meta span { display:block; color:#718096; font-size:11px; }
        .meta strong { display:block; margin-top:3px; color:#0d1b6e; font-size:13px; overflow-wrap:anywhere; }
        .status { display:inline-block; padding:5px 9px; border-radius:999px; background:#fff7ed; color:#9a3412; font-size:11px; font-weight:800; }
        .status.done { background:#ecfdf3; color:#067647; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
        .actions button { border:0; border-radius:9px; padding:9px 14px; font:inherit; font-size:12px; font-weight:800; cursor:pointer; background:#0d1b6e; color:#fff; }
        .actions button:hover { background:#1232d4; }
        .actions button.danger { background:#fff1f2; color:#b42318; border:1px solid #fecdd3; }
        .empty-card { background:#fff; border:1px solid #e2e8f3; border-radius:14px; padding:22px; color:#68758c; box-shadow:0 6px 18px rgba(13,27,110,.05); }
        @media(max-width:900px){ .recognition-main{padding:24px 18px 40px !important;} .recognition-grid{grid-template-columns:1fr 1fr;} }
        @media(max-width:600px){ .recognition-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => $user])

    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main recognition-main">
            <div class="recognition-content">
                <header class="recognition-head">
                    <div class="recognition-eyebrow">Academic recognition</div>
                    <h1>Academic Credit Recognition</h1>
                    <p>Review completed stacking frameworks before academic credit is formally recorded.</p>
                </header>

                @if(session('success'))<div class="notice ok">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="notice err">{{ $errors->first() }}</div>@endif

                <section class="recognition-list">
                    @forelse($recognitions as $record)
                        <article class="recognition-card">
                            <div class="recognition-grid">
                                <div class="meta"><span>Learner</span><strong>{{ $record->user?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Framework</span><strong>{{ $record->framework?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Equivalent course</span><strong>{{ $record->framework?->equivalent_course ?? '—' }}</strong></div>
                                <div class="meta"><span>Status</span><strong><span class="status {{ $record->status === 'registrar_recorded' ? 'done' : '' }}">{{ str_replace('_',' ',ucfirst($record->status)) }}</span></strong></div>
                            </div>
                            <div class="actions">
                                @if($record->status==='pending')<form method="POST" action="{{ route('admin.academic-credit-recognition.recommend',$record->id) }}">@csrf<button>Recommend</button></form>@endif
                                @if($record->status==='unit_recommended')<form method="POST" action="{{ route('admin.academic-credit-recognition.endorse',$record->id) }}">@csrf<button>Endorse</button></form>@endif
                                @if($record->status==='dean_endorsed')<form method="POST" action="{{ route('admin.academic-credit-recognition.record',$record->id) }}">@csrf<button>Record as Registrar</button></form>@endif
                                @if(in_array($record->status,['pending','unit_recommended','dean_endorsed'],true))<form method="POST" action="{{ route('admin.academic-credit-recognition.deny',$record->id) }}">@csrf<button class="danger">Deny</button></form>@endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-card">No academic credit recognition requests are currently available.</div>
                    @endforelse
                </section>
            </div>
        </main>
    </div>

    @include('components.responsive')
</body>
</html>
