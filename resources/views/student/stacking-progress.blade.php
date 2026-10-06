<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stacking Progress | Upskill</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#13176b; --blue:#1232d4; --gold:#dba617; --text:#17233c; --muted:#64748b; --page:#f7f9fc; --card:#fff; --line:#e2e8f0; --green:#159a68; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--page); color:var(--text); font-family:Inter,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:1.55; }
        .layout { display:grid; min-height:calc(100vh - 70px); }
        .main { min-width:0; padding:32px clamp(20px,3vw,44px) 52px; }
        .page { width:100%; max-width:1320px; margin:0 auto; }
        .page-head { display:flex; justify-content:space-between; align-items:end; gap:18px; flex-wrap:wrap; padding-bottom:20px; border-bottom:1px solid var(--line); }
        .eyebrow { margin:0 0 7px; color:var(--blue); font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        h1 { margin:0; color:var(--text); font-size:34px; font-weight:700; line-height:1.2; letter-spacing:-.025em; }
        .subtitle { margin:9px 0 0; color:var(--muted); font-size:16px; }
        .framework-card { margin-top:10px; padding:0 22px 22px; background:var(--card); border:1px solid var(--line); border-radius:14px; box-shadow:0 2px 8px rgba(23,35,60,.03); overflow:hidden; }
        .framework-head { display:flex; justify-content:space-between; gap:18px; align-items:center; margin:0 -22px 18px; padding:17px 22px; background:#172f94; }
        .framework-head h2 { margin:0; color:#fff; font-size:20px; font-weight:700; line-height:1.35; }
        .framework-description { margin:6px 0 0; color:var(--muted); font-size:14px; line-height:1.55; }
        .framework-head .framework-description { color:rgba(255,255,255,.82); }
        .status { padding:7px 12px; border-radius:999px; color:#7c2d12; background:#fff7ed; font-size:13px; font-weight:700; white-space:nowrap; }
        .status.met { color:#067647; background:#ecfdf3; }
        .meta { display:flex; gap:10px 22px; flex-wrap:wrap; margin-top:0; color:var(--muted); font-size:14px; }
        .meta strong { color:var(--text); }
        .progress-row { display:flex; align-items:center; gap:14px; margin-top:19px; }
        .track { height:10px; flex:1; overflow:hidden; border-radius:999px; background:#edf1f7; }
        .fill { height:100%; border-radius:inherit; background:var(--blue); }
        .percent { width:48px; color:var(--text); font-size:14px; font-weight:700; text-align:right; }
        .requirements-wrap { width:100%; margin-top:20px; overflow-x:auto; }
        .requirements { width:100%; border-collapse:collapse; font-size:14px; }
        .requirements th { color:#526179; font-size:12px; font-weight:700; letter-spacing:.04em; text-align:left; text-transform:uppercase; }
        .requirements th, .requirements td { padding:13px 10px; border-top:1px solid var(--line); vertical-align:top; }
        .requirements th:first-child, .requirements td:first-child { padding-left:0; }
        .requirements th:last-child, .requirements td:last-child { padding-right:0; text-align:right; }
        .course-title { color:var(--text); font-weight:650; }
        .tag { display:inline-block; margin-left:6px; padding:3px 7px; border-radius:5px; color:#475467; background:#f1f5f9; font-size:12px; font-weight:600; }
        .complete { color:var(--green); font-weight:700; }
        .pending { color:var(--muted); font-weight:600; }
        .empty { padding:34px 24px; margin-top:20px; border:1px dashed #cbd5e1; border-radius:14px; color:var(--muted); background:#fff; font-size:15px; text-align:center; }
        .evidence-report-link { display:inline-block; margin-top:18px; background:#0a1f6e; color:#fff; border-radius:999px; padding:11px 18px; font-size:14px; font-weight:650; text-decoration:none; transition:background-color .15s ease,color .15s ease,transform .15s ease; }
        .evidence-report-link:hover { background:var(--gold); color:var(--navy); transform:translateY(-1px); }
        @media (max-width:760px) {
            .main { padding:24px 18px 42px; }
            h1 { font-size:29px; }
            .subtitle { font-size:15px; }
            .framework-card { padding:0 16px 18px; }
            .framework-head { align-items:flex-start; flex-direction:column; gap:10px; margin:0 -16px 16px; padding:15px 16px; }
            .framework-head h2 { font-size:18px; }
            .meta { gap:8px 15px; font-size:13px; }
            .requirements { min-width:540px; }
        }
        @media (max-width:420px) {
            .main { padding-inline:14px; }
            h1 { font-size:26px; }
            .framework-card { padding-inline:13px; }
            .framework-head { margin-inline:-13px; padding-inline:13px; }
            .status { font-size:12px; }
        }
    </style>
</head>
<body>
    @include('components.student-navigation')
    <div class="layout student-sidebar-layout">
        @include('components.student-sidebar')
        <main class="main">
            <div class="page">
                <header class="page-head student-page-heading">
                    <div>
                        <h1>Stacking Progress</h1>
                        <p class="subtitle">Track officially completed microcredentials toward approved frameworks.</p>
                    </div>
                </header>

                @forelse($frameworks as $item)
                    @php
                        $framework = $item['framework'];
                        $isMet = $item['status'] === 'requirements_met';
                    @endphp
                    <section class="framework-card">
                        <div class="framework-head">
                            <div>
                                <h2>{{ $framework->name }}</h2>
                                @if($framework->description)<p class="framework-description">{{ $framework->description }}</p>@endif
                            </div>
                            <span class="status {{ $isMet ? 'met' : '' }}">{{ $isMet ? 'Requirements Met' : 'In Progress' }}</span>
                        </div>
                        <div class="meta">
                            @if($framework->pqf_level)<span>PQF <strong>{{ $framework->pqf_level }}</strong></span>@endif
                            @if($framework->target_recognition)<span>Target <strong>{{ $framework->target_recognition }}</strong></span>@endif
                            <span><strong>{{ $item['completed_required_count'] }}</strong> of <strong>{{ $item['target_required_count'] }}</strong> required target met</span><span>{{ $item['total_required_count'] }} required configured</span>
                            <span><strong>{{ $item['remaining_required_count'] }}</strong> remaining</span>
                        </div>
                        <div class="progress-row" aria-label="{{ $item['completion_percentage'] }} percent complete">
                            <div class="track"><div class="fill" style="width:{{ $item['completion_percentage'] }}%"></div></div>
                            <div class="percent">{{ $item['completion_percentage'] }}%</div>
                        </div>

                        @if($isMet)
                            <a class="evidence-report-link" href="{{ route('stacking.evidence-report', $framework->id) }}" target="_blank" rel="noopener">Generate academic credit evidence report</a>
                        @endif
                        <div class="requirements-wrap">
                            <table class="requirements">
                                <thead><tr><th>Microcredential</th><th>Order</th><th>Type</th><th>Completion</th></tr></thead>
                                <tbody>
                                    @foreach($item['requirements'] as $detail)
                                        <tr>
                                            <td><span class="course-title">{{ $detail['course']->title }}</span></td>
                                            <td>{{ $framework->sequence_required ? $detail['requirement']->order : '—' }}</td>
                                            <td>{{ $detail['is_required'] ? 'Required' : 'Optional' }}</td>
                                            <td>
                                                @if($detail['is_completed'])
                                                    <span class="complete">Completed</span><br><small>{{ $detail['completed_at']?->format('M j, Y') }}</small>
                                                @else
                                                    <span class="pending">Not completed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @empty
                    <div class="empty">No active stacking frameworks match your enrolled microcredentials yet.</div>
                @endforelse
            </div>
        </main>
    </div>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
