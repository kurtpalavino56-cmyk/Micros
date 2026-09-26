<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates | UPSKILL Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        :root { --navy:#0d1b6e; --text:#18213d; --muted:#68758c; --line:#e2e7f0; --bg:#f6f8fc; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--bg); color:var(--text); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .cert-main { padding:30px 32px 48px!important; }
        .cert-content { max-width:1320px; margin:0 auto; }
        .cert-heading { margin-bottom:22px; }
        .cert-heading h1 { margin:0 0 6px; color:var(--navy); font-size:30px; }
        .cert-heading p { margin:0; color:var(--muted); font-size:14px; }
        .notice { margin-bottom:16px; padding:12px 15px; border-radius:10px; font-size:13px; }
        .notice.success { background:#ecfdf3; color:#067647; }
        .notice.error { background:#fff1f2; color:#b42318; }
        .table-wrap { overflow:auto; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 8px 24px rgba(13,27,110,.05); }
        table { width:100%; border-collapse:collapse; min-width:880px; }
        th,td { padding:14px 16px; border-bottom:1px solid #edf0f5; text-align:left; vertical-align:top; font-size:13px; }
        th { background:#f9faff; color:#667085; font-size:11px; letter-spacing:.05em; text-transform:uppercase; }
        tr:last-child td { border-bottom:0; }
        .primary { color:#18213d; font-weight:700; }
        .secondary { margin-top:4px; color:var(--muted); font-size:12px; }
        .status { display:inline-flex; padding:4px 9px; border-radius:999px; background:#ecfdf3; color:#067647; font-size:11px; font-weight:800; text-transform:capitalize; }
        .status.revoked { background:#fff1f2; color:#b42318; }
        .reason-form { min-width:210px; }
        .reason-form textarea { display:block; width:100%; min-height:62px; margin-bottom:7px; padding:8px 9px; border:1px solid #d0d5dd; border-radius:8px; font:inherit; font-size:12px; resize:vertical; }
        .revoke-button { border:0; border-radius:8px; padding:8px 11px; background:#b42318; color:#fff; font:inherit; font-size:12px; font-weight:700; cursor:pointer; }
        .revoke-button:hover { background:#912018; }
        .revocation-reason { max-width:240px; color:#b42318; font-size:12px; overflow-wrap:anywhere; }
        .empty { padding:36px!important; color:var(--muted); text-align:center; }
        .pagination { padding:16px; }
        @media(max-width:1024px) { .cert-main { padding:24px 18px 40px!important; } }
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => $user])
    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main cert-main">
            <div class="cert-content">
                <header class="cert-heading">
                    <h1>Certificates</h1>
                    <p>Review issued credentials and revoke a certificate when necessary. Revocation is recorded with the acting administrator and reason.</p>
                </header>

                @if(session('success'))
                    <div class="notice success" role="status">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="notice error" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Certificate</th><th>Student</th><th>Course</th><th>Issued</th><th>Status</th><th>Revocation / action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $certificate)
                                <tr>
                                    <td>
                                        <div class="primary">{{ $certificate->serial }}</div>
                                        <div class="secondary">{{ $certificate->microcredential_title_snapshot ?: $certificate->title ?: 'Certificate' }}</div>
                                    </td>
                                    <td>
                                        <div class="primary">{{ $certificate->user?->name ?? 'Deleted account' }}</div>
                                        <div class="secondary">{{ $certificate->user?->email ?? '—' }}</div>
                                    </td>
                                    <td>{{ $certificate->course?->title ?? 'Deleted course' }}</td>
                                    <td>{{ $certificate->issued_at?->format('M j, Y') ?? '—' }}</td>
                                    <td><span class="status {{ $certificate->status === 'revoked' ? 'revoked' : '' }}">{{ $certificate->status ?: 'active' }}</span></td>
                                    <td>
                                        @if($certificate->status === 'revoked')
                                            <div class="revocation-reason">
                                                <strong>Revoked {{ $certificate->revoked_at?->format('M j, Y') ?? '' }}</strong>
                                                @if($certificate->revocation_reason)<div>{{ $certificate->revocation_reason }}</div>@endif
                                                @if($certificate->revoker)<div>By {{ $certificate->revoker->name }}</div>@endif
                                            </div>
                                        @else
                                            <form class="reason-form" method="POST" action="{{ route('admin.certificates.revoke', $certificate->id) }}" onsubmit="return confirm('Revoke this certificate? This action cannot be undone.');">
                                                @csrf
                                                <label class="secondary" for="reason-{{ $certificate->id }}">Reason (required)</label>
                                                <textarea id="reason-{{ $certificate->id }}" name="revocation_reason" minlength="5" maxlength="1000" required placeholder="Explain why this certificate is being revoked"></textarea>
                                                <button class="revoke-button" type="submit">Revoke certificate</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="empty" colspan="6">No certificates have been issued yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($certificates->hasPages())
                        <div class="pagination">{{ $certificates->links() }}</div>
                    @endif
                </div>
            </div>
        </main>
    </div>
    @include('components.responsive')
</body>
</html>

