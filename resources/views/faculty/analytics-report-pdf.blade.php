<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Analytics Report | Upskill</title>
<style>
    /*
     * dompdf-safe stylesheet: table layout, block elements, inline-friendly
     * properties only. No flex/grid, no JS, no external assets.
     */
    @page { margin: 46px 42px 60px 42px; }

    * { box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", sans-serif;
        color: #13176b;
        font-size: 12px;
        line-height: 1.45;
        margin: 0;
    }

    /* ── Header ─────────────────────────────────────────────── */
    .report-header {
        background-color: #13176b;
        color: #ffffff;
        padding: 18px 22px;
        border-radius: 6px;
    }
    .report-header .brand {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 2px;
        margin: 0;
    }
    .report-header .brand span { color: #dba617; }
    .report-header .doc-title {
        font-size: 13px;
        font-weight: bold;
        color: #7fe9e3;
        margin: 6px 0 0 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .meta-table td {
        font-size: 10px;
        color: #4b5563;
        padding: 2px 0;
    }
    .meta-table td.right { text-align: right; }
    .meta-table strong { color: #13176b; }

    /* ── Section headings ───────────────────────────────────── */
    .section { margin-top: 22px; }
    .section-head {
        background-color: #dba617;
        color: #13176b;
        font-size: 13px;
        font-weight: bold;
        padding: 7px 12px;
        border-radius: 4px 4px 0 0;
    }
    .section-body {
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 4px 4px;
        padding: 12px 14px;
    }

    /* ── Summary stat cards (table for dompdf) ──────────────── */
    .stat-table { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 18px; }
    .stat-cell {
        width: 33.33%;
        border-radius: 6px;
        padding: 16px 10px;
        text-align: center;
        color: #ffffff;
    }
    .stat-cell.navy { background-color: #13176b; }
    .stat-cell.gold { background-color: #c4930f; }
    .stat-cell.cyan { background-color: #2fb3ab; }
    .stat-cell .num { font-size: 26px; font-weight: bold; display: block; }
    .stat-cell .label { font-size: 10px; font-weight: bold; display: block; margin-top: 4px; }
    .stat-note { font-size: 9px; color: #6b7280; margin-top: 6px; }

    /* ── Data tables ────────────────────────────────────────── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        padding: 4px 6px 6px 6px;
    }
    .data-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #f0f2f8;
        font-size: 11px;
        color: #1f2a5a;
        vertical-align: middle;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table td.num, .data-table th.num { text-align: right; width: 90px; }

    /* Horizontal bar rows (Total Academy Statistics) */
    .bar-track {
        background-color: #eef1fa;
        border-radius: 3px;
        width: 100%;
        height: 9px;
    }
    .bar-fill {
        background-color: #13176b;
        border-radius: 3px;
        height: 9px;
    }
    .bar-fill.peak { background-color: #dba617; }

    /* Learner success legend dots */
    .dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        margin-right: 6px;
    }

    /* Activity feed */
    .activity-title { font-weight: bold; color: #13176b; font-size: 11px; }
    .activity-detail { color: #4b5563; font-size: 10px; }
    .activity-time { color: #9ca3af; font-size: 9px; white-space: nowrap; text-align: right; }

    .empty-state {
        border: 1px dashed #d1d5db;
        border-radius: 6px;
        padding: 16px;
        text-align: center;
        color: #6b7280;
        font-size: 10px;
    }

    /* ── Footer ─────────────────────────────────────────────── */
    .report-footer {
        margin-top: 26px;
        border-top: 2px solid #13176b;
        padding-top: 8px;
        font-size: 9px;
        color: #6b7280;
    }
    .report-footer .right { text-align: right; }
</style>
</head>
<body>

@php
    $stats         = is_array($stats ?? null) ? $stats : [];
    $points        = collect($academyStats ?? []);
    $success       = collect($learnerSuccess ?? []);
    $successTotal  = max($success->sum('count'), 1);
    $donutColors   = ['#13176b', '#dba617', '#2fb3ab', '#7fe9e3'];
    $maxPointValue = max(1, (int) ($points->max('value') ?? 0));
    $peakValue     = $points->max('value');
    $preparedName  = $preparedBy
        ? trim(($preparedBy->first_name ?? '').' '.($preparedBy->last_name ?? ''))
        : '';
@endphp

{{-- ═══════════════ HEADER ═══════════════ --}}
<div class="report-header">
    <p class="brand">UPSKILL<span>.</span></p>
    <p class="doc-title">Faculty Analytics Report</p>
</div>

<table class="meta-table">
    <tr>
        <td>
            @if($preparedName !== '')
                Prepared by: <strong>{{ $preparedName }}</strong> (Faculty)
            @endif
        </td>
        <td class="right">
            Generated: <strong>{{ ($generatedAt ?? now())->format('F j, Y — g:i A') }}</strong>
        </td>
    </tr>
</table>

{{-- ═══════════════ SUMMARY STAT CARDS ═══════════════ --}}
<table class="stat-table">
    <tr>
        <td class="stat-cell navy">
            <span class="num">{{ $onlineNow ?? 0 }}</span>
            <span class="label">Live Learners</span>
        </td>
        <td class="stat-cell gold">
            <span class="num">{{ $stats['certificates'] ?? 0 }}</span>
            <span class="label">Learners with Certificates</span>
        </td>
        <td class="stat-cell cyan">
            <span class="num">{{ $stats['lessons_done'] ?? 0 }}</span>
            <span class="label">Lessons Completed</span>
        </td>
    </tr>
</table>
<p class="stat-note">Live Learners reflects learners online in the last hour at the moment this report was generated. Certificate and lesson figures are all-time totals.</p>

{{-- ═══════════════ TOTAL ACADEMY STATISTICS ═══════════════ --}}
<div class="section">
    <div class="section-head">Total Academy Statistics</div>
    <div class="section-body">
        @if($points->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 34%;">Period</th>
                        <th style="width: 46%;">Activity</th>
                        <th class="num">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($points as $point)
                        @php
                            $value = is_array($point) ? ($point['value'] ?? 0) : ($point->value ?? 0);
                            $label = is_array($point) ? ($point['label'] ?? '') : ($point->label ?? '');
                            $label = str_replace("\n", ' ', $label);
                            $width = max(2, round(($value / $maxPointValue) * 100));
                            $isPeak = $value === $peakValue;
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>
                                <div class="bar-track">
                                    <div class="bar-fill {{ $isPeak ? 'peak' : '' }}" style="width: {{ $width }}%;"></div>
                                </div>
                            </td>
                            <td class="num"><strong>{{ $value }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="stat-note">Gold bar marks the peak period ({{ $peakValue }}).</p>
        @else
            <div class="empty-state">No statistics yet.</div>
        @endif
    </div>
</div>

{{-- ═══════════════ LEARNER SUCCESS ═══════════════ --}}
<div class="section">
    <div class="section-head">Learner Success</div>
    <div class="section-body">
        @if($success->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Segment</th>
                        <th class="num">Learners</th>
                        <th class="num">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($success->values() as $i => $segment)
                        @php
                            $count = $segment['count'] ?? 0;
                            $share = round(($count / $successTotal) * 100, 1);
                        @endphp
                        <tr>
                            <td>
                                <span class="dot" style="background-color: {{ $donutColors[$i % count($donutColors)] }};"></span>
                                {{ $segment['label'] ?? '' }}
                            </td>
                            <td class="num"><strong>{{ $count }}</strong></td>
                            <td class="num">{{ $share }}%</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="num"><strong>{{ $success->sum('count') }}</strong></td>
                        <td class="num">100%</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="empty-state">No learner data yet.</div>
        @endif
    </div>
</div>

{{-- ═══════════════ LIVE MONITORING FEED SNAPSHOT ═══════════════ --}}
<div class="section">
    <div class="section-head">Recent Activity (Live Monitoring Feed Snapshot)</div>
    <div class="section-body">
        @if(!empty($recentActivity))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Detail</th>
                        <th class="num">When</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivity as $event)
                        <tr>
                            <td class="activity-title">{{ $event['title'] }}</td>
                            <td class="activity-detail">{{ $event['detail'] }}</td>
                            <td class="activity-time">{{ $event['time'] }}<br>{{ $event['date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">No recent activity recorded.</div>
        @endif
    </div>
</div>

{{-- ═══════════════ FOOTER ═══════════════ --}}
<table class="meta-table report-footer">
    <tr>
        <td>Upskill — Faculty Analytics Report · Generated from the live Faculty Analytics dashboard.</td>
        <td class="right">Page generated {{ ($generatedAt ?? now())->format('Y-m-d H:i') }}</td>
    </tr>
</table>

</body>
</html>
