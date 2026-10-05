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
                    <h1>Legacy Academic Credit Requests</h1>
                    <p>Historical requests are retained for reference. UpSkill now prepares evidence reports; academic credit decisions and registrar recording must be completed through institutional processes outside this platform.</p>
                </header>

                @if(session('success'))<div class="notice ok">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="notice err">{{ $errors->first() }}</div>@endif

                <h2>Students ready for an evidence report</h2>
                <p>These stacking records meet the platform’s configured requirements. Generate a report to submit through the institution’s separate credit-review process.</p>
                <section class="recognition-list" style="margin-bottom:24px;">
                    @forelse($eligibleReports as $progress)
                        @if($progress->user && $progress->framework)
                            <article class="recognition-card">
                                <div class="recognition-grid">
                                    <div class="meta"><span>Student</span><strong>{{ $progress->user->name }}</strong></div>
                                    <div class="meta"><span>Framework</span><strong>{{ $progress->framework->name }}</strong></div>
                                    <div class="meta"><span>Platform status</span><strong>Requirements met</strong></div>
                                    <div class="meta"><span>Ready since</span><strong>{{ $progress->requirements_met_at?->format('M j, Y') ?? '—' }}</strong></div>
                                </div>
                                <div class="actions"><a style="display:inline-block;border-radius:9px;padding:9px 14px;background:#0d1b6e;color:#fff;font-size:12px;font-weight:800;" href="{{ route('admin.credit-evidence', [$progress->user->id, $progress->framework->id]) }}" target="_blank" rel="noopener">Generate evidence report</a></div>
                            </article>
                        @endif
                    @empty
                        <div class="empty-card">No student stacking records have met their configured requirements yet.</div>
                    @endforelse
                </section>

                <h2>Historical requests</h2>
                <section class="recognition-list">
                    @forelse($recognitions as $record)
                        <article class="recognition-card">
                            <div class="recognition-grid">
                                <div class="meta"><span>Learner</span><strong>{{ $record->user?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Framework</span><strong>{{ $record->framework?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Equivalent course</span><strong>{{ $record->framework?->equivalent_course ?? '—' }}</strong></div>
                                <div class="meta"><span>Historical status</span><strong><span class="status {{ $record->status === 'registrar_recorded' ? 'done' : '' }}">{{ str_replace('_',' ',ucfirst($record->status)) }}</span></strong></div>
                            </div>
                            <div class="actions">
                                @if($record->user && $record->framework)<a class="actions button" style="display:inline-block;border-radius:9px;padding:9px 14px;background:#0d1b6e;color:#fff;font-size:12px;font-weight:800;" href="{{ route('admin.credit-evidence', [$record->user->id, $record->framework->id]) }}" target="_blank" rel="noopener">Generate evidence report</a>@endif
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
