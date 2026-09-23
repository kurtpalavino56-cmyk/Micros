{{--
    resources/views/certificates/pdf.blade.php

    The OFFICIAL issued-certificate PDF template, rendered server-side by
    dompdf via CertificateBuilder::renderPdf().

    This is deliberately a SEPARATE template from
    resources/views/components/certificate.blade.php (the interactive
    faculty-preview component). That component relies on CSS clip-path,
    conic-gradient, a <script> for responsive scaling, and an external
    Google Fonts <link> — none of which a PDF rendering engine like
    dompdf supports (no JS execution, limited CSS3). Reusing it as-is
    here would silently produce a broken/garbled PDF, so this template
    reproduces the same PSU navy/gold design language and the same
    content model using dompdf-safe CSS only (borders, tables, absolute
    positioning; no clip-path, no gradients, no JS, no external font
    CDN).

    Data: passed as $cert, built exclusively by
    CertificateBuilder::pdfData() from the Certificate's own snapshot
    columns (never the live course) — see that method's docblock.
--}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    body {
        margin: 0;
        font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
        color: #12225c;
    }
    .sheet {
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        border: 10px solid #16357a;
        padding: 26px 40px;
    }
    .inner-rule {
        border: 2px solid #d4a017;
        padding: 24px 36px;
    }
    .institution {
        text-align: center;
        font-size: 16px;
        letter-spacing: 1px;
        color: #0f1f4d;
        font-weight: bold;
    }
    .word {
        text-align: center;
        font-size: 34px;
        font-weight: bold;
        color: #0f2461;
        letter-spacing: 2px;
        margin-top: 4px;
    }
    .of {
        text-align: center;
        font-size: 18px;
        color: #d4a017;
        font-weight: bold;
        margin-bottom: 14px;
    }
    .awarded { text-align: center; font-size: 13px; color: #22304f; }
    .name {
        text-align: center;
        font-size: 30px;
        font-weight: bold;
        color: #12225c;
        margin: 4px 0;
    }
    .for { text-align: center; font-size: 13px; font-style: italic; color: #22304f; }
    .course {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        color: #1a4fd6;
        margin: 6px 0 14px;
    }
    table.meta { width: 100%; margin-top: 10px; font-size: 11px; }
    table.meta td { padding: 2px 6px; vertical-align: top; }
    table.meta td.k { font-weight: bold; width: 160px; color: #12225c; }
    .section-title { font-size: 12px; font-weight: bold; color: #12225c; margin-top: 12px; }
    ul.plain { margin: 4px 0 0 16px; padding: 0; font-size: 11px; }
    ul.plain li { margin-bottom: 2px; }
    table.footer { width: 100%; margin-top: 22px; }
    table.footer td { vertical-align: bottom; }
    .qr-cell { width: 110px; text-align: center; font-size: 10px; }
    .qr-cell img { width: 90px; height: 90px; }
    .sign-cell { text-align: center; font-size: 11px; }
    .sign-rule { border-top: 1px solid #12225c; width: 220px; margin: 30px auto 4px; }
    .sign-name { font-weight: bold; }
    .status-revoked {
        text-align: center;
        color: #a11d1d;
        font-weight: bold;
        font-size: 12px;
        margin-top: 8px;
    }
</style>
</head>
<body>
<div class="sheet">
    <div class="inner-rule">
        <div class="institution">{{ $cert['institution'] }}</div>
        <div class="word">CERTIFICATE</div>
        <div class="of">of Completion</div>

        <div class="awarded">This certificate is awarded to</div>
        <div class="name">{{ $cert['student_name'] }}</div>
        <div class="for">for successfully completing the Microcredential</div>
        <div class="course">{{ $cert['course_title'] }}</div>

        <table class="meta">
            <tr>
                <td class="k">Date Completed</td>
                <td>{{ $cert['date_completed'] ?? '—' }}</td>
                <td class="k">Credential ID</td>
                <td>{{ $cert['serial'] }}</td>
            </tr>
            <tr>
                <td class="k">Learning Hours</td>
                <td>{{ $cert['learning_hours'] ?? '—' }}</td>
                <td class="k">PQF Level</td>
                <td>{{ $cert['pqf_level'] ?? '—' }}</td>
            </tr>
            @if (!empty($cert['credit_equivalency']))
            <tr>
                <td class="k">Credit Equivalency</td>
                <td colspan="3">{{ $cert['credit_equivalency'] }}</td>
            </tr>
            @endif
        </table>

        @if (!empty($cert['learning_outcomes']))
        <div class="section-title">Learning Outcomes Achieved</div>
        <ul class="plain">
            @foreach ($cert['learning_outcomes'] as $lo)
                <li>@if(!empty($lo['code'])){{ $lo['code'] }}: @endif{{ $lo['description'] ?? '' }}</li>
            @endforeach
        </ul>
        @endif

        @if (!empty($cert['competencies']))
        <div class="section-title">Competencies Achieved</div>
        <ul class="plain">
            @foreach ($cert['competencies'] as $unit)
                <li>{{ $unit['title'] ?? '' }}</li>
            @endforeach
        </ul>
        @endif

        <table class="footer">
            <tr>
                <td class="qr-cell">
                    @if (!empty($cert['qr_url']))
                        <img src="{{ $cert['qr_url'] }}" alt="Verify">
                    @endif
                    <div>Scan to verify</div>
                </td>
                <td></td>
                <td class="sign-cell">
                    <div class="sign-rule"></div>
                    <div class="sign-name">{{ $cert['issuer_name'] }}</div>
                    <div>{{ $cert['issuer_role'] }}</div>
                </td>
            </tr>
        </table>

        @if (($cert['status'] ?? 'active') === 'revoked')
            <div class="status-revoked">THIS CERTIFICATE HAS BEEN REVOKED</div>
        @endif
    </div>
</div>
</body>
</html>
