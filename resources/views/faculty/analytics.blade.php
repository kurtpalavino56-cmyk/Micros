<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --cyan-deep:#2fb3ab;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,0.14);}
    .side-icon-box svg{color:#fff;width:22px;height:22px;transition:color .15s ease;}
    .side-link.active .side-icon-box{background:var(--navy);}
    .side-link.plain .side-icon-box{background:transparent;}
    .side-link.plain .side-icon-box svg{color:#fff;width:26px;height:26px;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head{margin-bottom:26px;}
    .page-head h2{font-size:30px;margin:0 0 8px;color:var(--navy);}
    .page-head .live-line{margin:0 0 6px;color:var(--ink);font-size:16px;font-weight:800;}
    .page-head .live-line .count{color:var(--gold-dark);text-decoration:underline;}
    .page-head .sub-line{margin:0;color:var(--muted);font-size:14px;font-weight:600;}
    .page-head .sub-line b{color:var(--navy);}

    /* Gradient stat cards */
    .stat-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin-bottom:32px;}
    .g-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 26px;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}
    .g-card.navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .g-card.gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .g-card.cyan{background:linear-gradient(135deg,var(--cyan-deep) 0%,var(--cyan) 100%);}
    .g-card .num{font-size:44px;font-weight:800;line-height:1;margin-bottom:8px;position:relative;z-index:1;}
    .g-card .label{font-size:17px;font-weight:800;position:relative;z-index:1;}
    .g-card .watermark{position:absolute;left:-14px;top:50%;transform:translateY(-50%) rotate(-8deg);opacity:.18;z-index:0;}
    .g-card .watermark svg{width:130px;height:130px;color:#fff;}

    /* Chart panels (gold heads like the rest of the faculty pages) */
    .charts-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:28px;}
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;background:#fff;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:20px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .panel-head .range{font-size:12px;font-weight:700;background:rgba(255,255,255,0.55);padding:6px 14px;border-radius:999px;}
    .panel-body{padding:22px;}

    .area-chart-wrap{width:100%;overflow-x:auto;}
    .area-chart-wrap svg{display:block;width:100%;height:auto;min-width:520px;}

    /* Donut + legend */
    .donut-flex{display:flex;align-items:center;gap:22px;flex-wrap:wrap;justify-content:center;}
    .donut-wrap{flex-shrink:0;}
    .legend-box{background:#fafbff;border:1px solid var(--line);border-radius:14px;padding:16px 18px;min-width:200px;flex:1;}
    .legend-item{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px dashed var(--line);}
    .legend-item:last-child{border-bottom:none;}
    .legend-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:4px;}
    .legend-item .l-num{font-size:18px;font-weight:800;color:var(--navy);line-height:1.2;}
    .legend-item .l-text{font-size:12px;color:var(--muted);font-weight:700;line-height:1.4;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);font-size:14px;}

    @media (max-width:1180px){
        .charts-grid{grid-template-columns:1fr;}
    }
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .stat-cards{grid-template-columns:1fr;}
    }
</style>
</head>
<body>

@include('components.authenticated-topbar')

<div class="layout faculty-sidebar-layout">

    {{-- Sidebar â€” Analytics is this page; everything else connected --}}
    @include('components.faculty-sidebar')

    {{-- Main content --}}
    <main class="main">

        <div class="page-head">
            <h2>Analytics</h2>
            <p class="live-line">Last hour - <span class="count">{{ $onlineNow ?? 0 }}</span> learners online</p>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• GRADIENT STAT CARDS â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <section class="stat-cards">
            <div class="g-card navy">
                <span class="watermark">
                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="8" r="4"/><path d="M1 21c0-4 3.5-7 8-7s8 3 8 7"/><circle cx="17" cy="9" r="3"/><path d="M17 14c3.3 0 6 2.2 6 5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                </span>
                <span class="num" id="faculty-live-active-users">{{ $onlineNow ?? 0 }}</span>
                <span class="label">Live Learners</span>
            </div>
            <div class="g-card gold">
                <span class="watermark">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/><path d="M8.2 13.9 7 23l5-3 5 3-1.2-9.1"/></svg>
                </span>
                <span class="num">{{ $stats['certificates'] ?? 0 }}</span>
                <span class="label">Learners with Certificates</span>
            </div>
            <div class="g-card cyan">
                <span class="watermark">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </span>
                <span class="num">{{ $stats['lessons_done'] ?? 0 }}</span>
                <span class="label">Lessons Completed</span>
            </div>
        </section>

        <section class="panel" style="margin-bottom: 24px;">
            <div class="panel-head">
                <span>Live Monitoring Feed</span>
                <span class="range">Auto-refreshing</span>
            </div>
            <div class="panel-body">
                <div id="faculty-live-activity" class="legend-box" style="min-width: unset;"></div>
            </div>
        </section>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â• CHARTS â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div class="charts-grid">

            {{-- Total Academy Statistics â€” SVG area chart --}}
            <section class="panel">
                <div class="panel-head">
                    <span>Total Academy Statistics</span>
                    {{-- âš  Not connected â€” timeline filter does nothing yet --}}
                    <span class="range">Graph timeline: All time â–¾</span>
                </div>
                <div class="panel-body">
                    @php
                        $points = collect($academyStats ?? []);
                        // Chart geometry
                        $W = 720;  $H = 260;
                        $padL = 40; $padR = 30; $padT = 34; $padB = 44;
                        $plotW = $W - $padL - $padR;
                        $plotH = $H - $padT - $padB;
                        $maxV  = max(30, (int) ceil(($points->max('value') ?? 0) / 10) * 10);
                        $n     = max($points->count() - 1, 1);
                        $coords = $points->values()->map(function ($p, $i) use ($padL, $padT, $plotW, $plotH, $maxV, $n) {
                            $val = is_array($p) ? ($p['value'] ?? 0) : ($p->value ?? 0);
                            $label = is_array($p) ? ($p['label'] ?? '') : ($p->label ?? '');
                            return [
                                'x' => $padL + ($plotW * $i / $n),
                                'y' => $padT + $plotH - ($plotH * ($val) / $maxV),
                                'label' => $label,
                                'value' => $val,
                            ];
                        });
                        $lineStr = $coords->map(fn ($c) => round($c['x'], 1).','.round($c['y'], 1))->implode(' ');
                        $areaStr = $lineStr.' '.round($padL + $plotW, 1).','.($padT + $plotH).' '.$padL.','.($padT + $plotH);
                        $peak = $coords->sortByDesc('value')->first();
                    @endphp

                    @if($points->isNotEmpty())
                        <div class="area-chart-wrap">
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Total academy statistics over time">
                            {{-- horizontal gridlines + y labels --}}
                            @for($v = 0; $v <= $maxV; $v += 10)
                                @php $gy = $padT + $plotH - ($plotH * $v / $maxV); @endphp
                                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1"/>
                                <text x="{{ $padL - 10 }}" y="{{ $gy + 4 }}" text-anchor="end" font-size="11" font-weight="700" fill="#6b7280">{{ $v }}</text>
                            @endfor

                            {{-- filled area --}}
                            <polygon points="{{ $areaStr }}" fill="#13176b" opacity="0.12"/>
                            {{-- line --}}
                            <polyline points="{{ $lineStr }}" fill="none" stroke="#13176b" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                            {{-- dots + x labels --}}
                            @foreach($coords as $c)
                                <circle cx="{{ round($c['x'],1) }}" cy="{{ round($c['y'],1) }}" r="3.5" fill="#fff" stroke="#13176b" stroke-width="2"/>
                                <text x="{{ round($c['x'],1) }}" y="{{ $padT + $plotH + 18 }}" text-anchor="middle" font-size="10" font-weight="700" fill="#6b7280">
                                    @foreach(explode("\n", $c['label']) as $li => $lineTxt)
                                        <tspan x="{{ round($c['x'],1) }}" dy="{{ $li === 0 ? 0 : 12 }}">{{ $lineTxt }}</tspan>
                                    @endforeach
                                </text>
                            @endforeach

                            {{-- gold highlight + callout on the peak point --}}
                            @if($peak)
                                <circle cx="{{ round($peak['x'],1) }}" cy="{{ round($peak['y'],1) }}" r="5" fill="#dba617" stroke="#fff" stroke-width="2"/>
                                @php
                                    $bxW = 120; $bxH = 40;
                                    $bx = min(max($peak['x'] - $bxW - 14, $padL), $padL + $plotW - $bxW);
                                    $by = max($peak['y'] - $bxH - 6, 4);
                                @endphp
                                <rect x="{{ round($bx,1) }}" y="{{ round($by,1) }}" width="{{ $bxW }}" height="{{ $bxH }}" rx="8" fill="#fff" stroke="#e5e7eb"/>
                                <text x="{{ round($bx + 12,1) }}" y="{{ round($by + 17,1) }}" font-size="11" font-weight="800" fill="#13176b">{{ str_replace("\n", ' ', $peak['label']) }}</text>
                                <text x="{{ round($bx + 12,1) }}" y="{{ round($by + 32,1) }}" font-size="11" font-weight="700" fill="#6b7280">Amount: {{ $peak['value'] }}</text>
                            @endif
                        </svg>
                        </div>
                    @else
                        <div class="empty-state">No statistics yet.</div>
                    @endif
                </div>
            </section>

            {{-- Learner Success â€” SVG donut chart --}}
            <section class="panel">
                <div class="panel-head">
                    <span>Learner Success</span>
                    {{-- âš  Not connected â€” timeline filter does nothing yet --}}
                    <span class="range">All time â–¾</span>
                </div>
                <div class="panel-body">
                    @php
                        $success = collect($learnerSuccess ?? []);
                        $total   = max($success->sum('count'), 1);
                        $donutColors = ['#13176b', '#dba617', '#7fe9e3'];
                        $R = 70; $CIRC = 2 * pi() * $R;
                        $offset = 0;
                        $segments = $success->values()->map(function ($seg, $i) use ($total, $CIRC, $donutColors, &$offset) {
                            $len = $CIRC * ($seg['count'] ?? 0) / $total;
                            $s = [
                                'color'  => $donutColors[$i % count($donutColors)],
                                'dash'   => $len,
                                'gap'    => $CIRC - $len,
                                'offset' => -$offset,
                                'count'  => $seg['count'] ?? 0,
                                'label'  => $seg['label'] ?? '',
                            ];
                            $offset += $len;
                            return $s;
                        });
                    @endphp

                    @if($success->isNotEmpty())
                        <div class="donut-flex">
                            <div class="donut-wrap">
                                <svg width="190" height="190" viewBox="0 0 190 190" role="img" aria-label="Learner success breakdown">
                                    <g transform="rotate(-90 95 95)">
                                        @foreach($segments as $seg)
                                            <circle cx="95" cy="95" r="{{ $R }}" fill="none"
                                                stroke="{{ $seg['color'] }}" stroke-width="34"
                                                stroke-dasharray="{{ round($seg['dash'],2) }} {{ round($seg['gap'],2) }}"
                                                stroke-dashoffset="{{ round($seg['offset'],2) }}"/>
                                        @endforeach
                                    </g>
                                    <text x="95" y="90" text-anchor="middle" font-size="26" font-weight="800" fill="#13176b">{{ $success->sum('count') }}</text>
                                    <text x="95" y="110" text-anchor="middle" font-size="11" font-weight="700" fill="#6b7280">Learners</text>
                                </svg>
                            </div>
                            <div class="legend-box">
                                @foreach($segments as $seg)
                                    <div class="legend-item">
                                        <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                                        <span>
                                            <span class="l-num">{{ $seg['count'] }}</span><br>
                                            <span class="l-text">{{ $seg['label'] }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state">No learner data yet.</div>
                    @endif
                </div>
            </section>

        </div>

    </main>
</div>

<script>
    function refreshFacultyMonitoring() {
        fetch('{{ route('monitoring.live') }}')
            .then(response => response.json())
            .then(data => {
                const active = document.getElementById('faculty-live-active-users');
                if (active) active.textContent = data.stats.active_users;

                const container = document.getElementById('faculty-live-activity');
                if (!container) return;

                container.innerHTML = '';
                const items = (data.activity || []).slice(0, 5);
                if (items.length === 0) {
                    container.innerHTML = '<div class="empty-state">No recent activity yet - events will appear here as they happen.</div>';
                    return;
                }
                items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'legend-item';
                    row.innerHTML = `<div class="legend-dot" style="background:${item.type === 'event' ? '#13176b' : '#dba617'}"></div><div><div class="l-num">${item.title}</div><div class="l-text">${item.detail} - ${item.time}</div></div>`;
                    container.appendChild(row);
                });
            })
            .catch(() => {
                const container = document.getElementById('faculty-live-activity');
                if (container) {
                    container.innerHTML = '<div class="empty-state">Monitoring feed unavailable.</div>';
                }
            });
    }

    refreshFacultyMonitoring();
    setInterval(refreshFacultyMonitoring, 15000);
</script>

{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
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

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>


