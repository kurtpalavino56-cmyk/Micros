{{--
    Issued certificate PDF template. Uses DomPDF-supported CSS and the same
    navy, gold, QR, and signature layout as the interactive certificate.
--}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; size: a4 landscape; }
    body {
        margin: 0;
        font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
        color: #103a78;
    }
    .sheet {
        position: relative;
        width: 1122px;
        height: 790px;
        overflow: hidden;
        background: #fff;
    }
    .corner {
        position: absolute;
        width: 0;
        height: 0;
    }
    .corner-top-gold {
        top: 0;
        left: 0;
        border-top: 122px solid #f4b900;
        border-right: 122px solid transparent;
    }
    .corner-top-navy {
        top: 0;
        left: 0;
        border-top: 101px solid #103a78;
        border-right: 101px solid transparent;
    }
    .corner-bottom-gold {
        right: 0;
        bottom: 0;
        border-bottom: 122px solid #f4b900;
        border-left: 122px solid transparent;
    }
    .corner-bottom-navy {
        right: 0;
        bottom: 0;
        border-bottom: 101px solid #103a78;
        border-left: 101px solid transparent;
    }
    .crest {
        margin: 21px auto 4px;
        width: 48px;
        height: 48px;
    }
    .crest img { width: 48px; height: 48px; }
    .qr {
        position: absolute;
        top: 27px;
        right: 34px;
        width: 88px;
        text-align: center;
        font-size: 10px;
    }
    .qr img { width: 88px; height: 88px; }
    .qr div { margin-top: 3px; }
    .header {
        text-align: center;
    }
    .institution {
        color: #0f1f4d;
        font-family: 'DejaVu Serif', 'Times New Roman', serif;
        font-size: 17px;
        font-weight: bold;
    }
    .word {
        margin-top: 8px;
        color: #f4b900;
        font-size: 43px;
        font-weight: bold;
        letter-spacing: 2px;
        line-height: 1.1;
    }
    .of {
        margin-top: 1px;
        color: #103a78;
        font-size: 19px;
        font-weight: bold;
        letter-spacing: 5px;
        text-transform: uppercase;
    }
    .body {
        margin: 23px 65px 0;
        text-align: center;
    }
    .awarded { color: #24436f; font-size: 14px; }
    .name {
        margin: 3px 0 5px;
        color: #103a78;
        font-size: 37px;
        font-weight: bold;
    }
    .for { color: #24436f; font-size: 13px; font-style: italic; }
    .course {
        margin: 5px 0 9px;
        color: #1760d5;
        font-size: 21px;
        font-weight: bold;
    }
    .description {
        max-width: 570px;
        margin: 0 auto;
        color: #36577f;
        font-size: 11px;
        line-height: 1.55;
    }
    .footer {
        margin: 105px auto 0;
        width: 990px;
        border-collapse: collapse;
    }
    .footer td { width: 50%; vertical-align: bottom; }
    .meta { width: 100%; border-collapse: collapse; }
    .meta td { padding: 3px 4px; vertical-align: baseline; }
    .meta .key {
        width: 172px;
        color: #103a78;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 1.5px;
    }
    .meta .colon { width: 20px; color: #103a78; font-size: 14px; }
    .meta .value { color: #36577f; font-size: 14px; }
    .sign-cell { padding-left: 30px; text-align: center; }
    .sign-image { display: block; max-width: 250px; max-height: 52px; margin: 0 auto; }
    .sign-text { height: 52px; color: #103a78; font-size: 26px; font-style: italic; }
    .sign-spacer { height: 52px; }
    .sign-rule { width: 300px; margin: 4px auto 5px; border-top: 2px solid #103a78; }
    .sign-name { color: #103a78; font-size: 18px; font-weight: bold; }
    .sign-role { color: #36577f; font-size: 16px; }
    .status-revoked {
        position: absolute;
        top: 48%;
        left: 34%;
        color: #a11d1d;
        font-size: 28px;
        font-weight: bold;
        transform: rotate(-18deg);
    }
</style>
</head>
<body>
<div class="sheet">
    <div class="corner corner-top-gold"></div>
    <div class="corner corner-top-navy"></div>
    <div class="corner corner-bottom-gold"></div>
    <div class="corner corner-bottom-navy"></div>

    <div class="crest"><img src="{{ public_path('images/PSU-Logo.png') }}" alt="Pangasinan State University"></div>
    <div class="qr">
        @if (!empty($cert['qr_url']))
            <img src="{{ $cert['qr_url'] }}" alt="Verify">
        @endif
        <div>Verify</div>
    </div>

    <div class="header">
        <div class="institution">Pangasinan State University</div>
        <div class="word">CERTIFICATE</div>
        <div class="of">of Completion</div>
    </div>

    <div class="body">
        <div class="awarded">This certificate is awarded to</div>
        <div class="name">{{ $cert['student_name'] }}</div>
        <div class="for">for successfully completing the Microcredential</div>
        <div class="course">{{ $cert['course_title'] }}</div>
        <div class="description">This certificate acknowledges that the above-named individual has demonstrated proficiency in the competencies covered by this micro-credential.</div>
    </div>

    <table class="footer">
        <tr>
            <td>
                <table class="meta">
                    <tr>
                        <td class="key">DATE COMPLETED</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $cert['date_completed'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="key">LEARNING HOURS</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $cert['learning_hours'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="key">CREDENTIAL ID</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $cert['serial'] }}</td>
                    </tr>
                </table>
            </td>
            <td class="sign-cell">
                @if (!empty($cert['signature_img']))
                    <img class="sign-image" src="{{ $cert['signature_img'] }}" alt="Signature">
                @elseif (!empty($cert['signature_text']))
                    <div class="sign-text">{{ $cert['signature_text'] }}</div>
                @else
                    <div class="sign-spacer"></div>
                @endif
                <div class="sign-rule"></div>
                <div class="sign-name">{{ $cert['issuer_name'] }}</div>
                <div class="sign-role">{{ $cert['issuer_role'] }}</div>
            </td>
        </tr>
    </table>

    @if (($cert['status'] ?? 'active') === 'revoked')
        <div class="status-revoked">REVOKED</div>
    @endif
</div>
</body>
</html>
