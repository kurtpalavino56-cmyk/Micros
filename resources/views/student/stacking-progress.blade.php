<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stacking Progress | Upskill</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        :root { --navy:#13176b; --blue:#1232d4; --gold:#dba617; --text:#17233c; --muted:#718096; --page:#f7f9fc; --card:#fff; --line:#e2e8f0; --green:#159a68; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--page); color:var(--text); font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
        .layout { display:grid; min-height:calc(100vh - 70px); }
        .main { min-width:0; padding:28px clamp(18px,3vw,42px) 48px; }
        .page { max-width:1120px; margin:0 auto; }
        .page-head { display:flex; justify-content:space-between; align-items:end; gap:18px; flex-wrap:wrap; padding-bottom:18px; border-bottom:1px solid var(--line); }
        .eyebrow { margin:0 0 7px; color:var(--blue); font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        h1 { margin:0; color:var(--text); font-size:32px; line-height:1.15; }
        .subtitle { margin:8px 0 0; color:var(--muted); font-size:14px; }
        .framework-card { margin-top:16px; padding:18px; background:var(--card); border:1px solid var(--line); border-radius:14px; box-shadow:0 2px 8px rgba(23,35,60,.03); }
        .framework-head { display:flex; justify-content:space-between; gap:14px; align-items:start; }
        .framework-head h2 { margin:0; color:var(--text); font-size:18px; }
        .framework-description { margin:5px 0 0; color:var(--muted); font-size:12px; }
        .status { padding:6px 10px; border-radius:999px; color:#7c2d12; background:#fff7ed; font-size:11px; font-weight:800; white-space:nowrap; }
        .status.met { color:#067647; background:#ecfdf3; }
        .meta { display:flex; gap:8px 18px; flex-wrap:wrap; margin-top:12px; color:var(--muted); font-size:12px; }
        .meta strong { color:var(--text); }
        .progress-row { display:flex; align-items:center; gap:12px; margin-top:16px; }
        .track { height:8px; flex:1; overflow:hidden; border-radius:999px; background:#edf1f7; }
        .fill { height:100%; border-radius:inherit; background:var(--blue); }
        .percent { width:42px; color:var(--text); font-size:12px; font-weight:800; text-align:right; }
        .requirements { width:100%; margin-top:16px; border-collapse:collapse; font-size:12px; }
        .requirements th { color:var(--muted); font-size:10px; letter-spacing:.06em; text-align:left; text-transform:uppercase; }
        .requirements th, .requirements td { padding:10px 8px; border-top:1px solid var(--line); vertical-align:top; }
        .requirements th:first-child, .requirements td:first-child { padding-left:0; }
        .requirements th:last-child, .requirements td:last-child { padding-right:0; text-align:right; }
        .course-title { color:var(--text); font-weight:750; }
        .tag { display:inline-block; margin-left:6px; padding:3px 6px; border-radius:5px; color:#475467; background:#f1f5f9; font-size:10px; font-weight:700; }
        .complete { color:var(--green); font-weight:800; }
        .pending { color:var(--muted); font-weight:700; }
        .empty { padding:28px; margin-top:16px; border:1px dashed #cbd5e1; border-radius:14px; color:var(--muted); background:#fff; text-align:center; }
        @media (max-width:650px) { .framework-head { flex-direction:column; } .requirements th:nth-child(2), .requirements td:nth-child(2) { display:none; } .requirements th, .requirements td { padding:9px 4px; } }
    </style>
</head>
<body>
    @include('components.student-navigation')
    <div class="layout student-sidebar-layout">
        @include('components.student-sidebar')
        <main class="main">
            <div class="page">
                <header class="page-head">
                    <div>
                        <p class="eyebrow">Approved learning combinations</p>
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

                        @if($item['can_request_recognition'])
                            <form method="POST" action="{{ route('stacking.recognition.request', $framework->id) }}" style="margin-top:16px;">
                                @csrf
                                <button type="submit" style="background:#0a1f6e;color:#fff;border:0;border-radius:999px;padding:10px 16px;font-weight:800;cursor:pointer;">Request Academic Credit Recognition</button>
                            </form>
                        @elseif($item['recognition'] ?? null)
                            <div style="margin-top:14px;padding:10px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#344054;font-weight:700;">
                                Recognition status: {{ ucfirst(str_replace('_', ' ', $item['recognition']->status)) }}
                            </div>
                        @endif
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
