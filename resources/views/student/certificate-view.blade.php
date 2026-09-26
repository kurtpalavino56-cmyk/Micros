{{--
    resources/views/student/certificate-view.blade.php

    Browser view of a single already-issued certificate (Phase 3, Step 6).
    $cert comes from CertificateBuilder::pdfData() — snapshot data only,
    the same data the PDF and public verification use.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $cert['certificate_title'] ?: $cert['course_title'] }} — Certificate | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{ --navy:#13176b; --gold:#dba617; }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;margin:0;background:#f4f6fb;color:#13176b;}
    .bar{background:var(--navy);padding:16px 24px;display:flex;align-items:center;justify-content:space-between;}
    .bar a{color:#fff;text-decoration:none;font-weight:700;}
    .bar .actions{display:flex;gap:10px;}
    .btn{display:inline-block;padding:10px 18px;border-radius:10px;font-weight:700;text-decoration:none;}
    .btn-gold{background:var(--gold);color:#13176b;}
    .btn-outline{border:1px solid rgba(255,255,255,.5);color:#fff;}
    .wrap{max-width:1080px;margin:32px auto;padding:0 20px;}
    .status-pill{display:inline-block;margin-bottom:14px;padding:4px 14px;border-radius:999px;font-size:12px;font-weight:700;}
    .status-active{background:#e5f7ec;color:#0f7a3d;}
    .status-revoked{background:#fdeaea;color:#a11d1d;}
</style>
</head>
<body>
<div class="bar">
    <a href="{{ route('certificates.index') }}">&larr; My Certificates</a>
    <div class="actions">
        <a class="btn btn-outline" href="{{ route('certificates.index') }}">Back</a>
        <a class="btn btn-gold" href="{{ route('certificates.download', $cert['serial']) }}">Download PDF</a>
    </div>
</div>
<div class="wrap">
    <span class="status-pill {{ ($cert['status'] ?? 'active') === 'revoked' ? 'status-revoked' : 'status-active' }}">
        {{ ($cert['status'] ?? 'active') === 'revoked' ? 'Revoked' : 'Active' }}
    </span>

    @include('components.certificate', ['cert' => $cert])

    @if (!empty($cert['learning_outcomes']))
    <div style="margin-top:24px;background:#fff;border-radius:16px;padding:20px 26px;">
        <h3 style="margin:0 0 8px;font-size:15px;color:#13176b;">Learning Outcomes Achieved</h3>
        <ul style="margin:0;padding-left:18px;font-size:14px;color:#22304f;">
            @foreach ($cert['learning_outcomes'] as $lo)
                <li>@if(!empty($lo['code'])){{ $lo['code'] }}: @endif{{ $lo['description'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (!empty($cert['competencies']))
    <div style="margin-top:16px;background:#fff;border-radius:16px;padding:20px 26px;">
        <h3 style="margin:0 0 8px;font-size:15px;color:#13176b;">Competencies Achieved</h3>
        <ul style="margin:0;padding-left:18px;font-size:14px;color:#22304f;">
            @foreach ($cert['competencies'] as $unit)
                <li>{{ $unit['title'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>
