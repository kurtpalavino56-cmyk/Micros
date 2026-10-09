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
        .cert-main { padding:26px 28px 42px!important; }
        .cert-content { max-width:1320px; margin:0 auto; }
        .cert-heading { margin-bottom:18px; }
        .cert-heading h1 { margin:0 0 5px; color:var(--navy); font-size:30px; }
        .cert-heading p { margin:0; color:var(--muted); font-size:14px; }
        .credential-tabs{display:flex;gap:22px;margin:0 0 16px;border-bottom:1px solid var(--line);}
        .credential-tab{display:inline-flex;align-items:center;padding:11px 4px;border-bottom:3px solid transparent;color:var(--muted);font-size:14px;font-weight:700;text-decoration:none;}
        .credential-tab:hover{color:var(--navy);}
        .credential-tab.active{border-bottom-color:var(--navy);color:var(--navy);}
        .credential-filters{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 14px;padding:13px;background:#fff;border:1px solid var(--line);border-radius:12px;}
        .credential-filters input,.credential-filters select{min-height:40px;padding:8px 11px;border:1px solid #d0d5dd;border-radius:8px;background:#fff;color:var(--text);font:inherit;font-size:13px;}
        .credential-filters input{flex:1 1 280px;min-width:200px;}
        .credential-filters select{flex:0 1 170px;}
        .credential-filter-submit,.credential-filter-clear{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:8px 13px;border:1px solid var(--navy);border-radius:8px;background:var(--navy);color:#fff;font:inherit;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;}
        .credential-filter-clear{border-color:var(--line);background:#fff;color:var(--text);}
        .notice { margin-bottom:16px; padding:12px 15px; border-radius:10px; font-size:13px; }
        .notice.success { background:#ecfdf3; color:#067647; }
        .notice.error { background:#fff1f2; color:#b42318; }
        .table-wrap { overflow:auto; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 8px 24px rgba(13,27,110,.05); }
        table { width:100%; border-collapse:collapse; min-width:880px; }
        th,td { padding:11px 14px; border-bottom:1px solid #edf0f5; text-align:left; vertical-align:middle; font-size:13px; }
        th { background:#f9faff; color:#667085; font-size:11px; letter-spacing:.05em; text-transform:uppercase; }
        tr:last-child td { border-bottom:0; }
        .primary { color:#18213d; font-weight:700; }
        .secondary { margin-top:4px; color:var(--muted); font-size:12px; }
        .status { display:inline-flex; padding:4px 9px; border-radius:999px; background:#ecfdf3; color:#067647; font-size:11px; font-weight:800; text-transform:capitalize; }
        .status.revoked { background:#fff1f2; color:#b42318; }
        .revoke-button,.view-revocation-button { border:1px solid transparent; border-radius:8px; padding:7px 10px; background:#b42318; color:#fff; font:inherit; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
        .revoke-button:hover { background:#912018; }
        .revoke-button:disabled { border-color:#cbd0da; background:#e4e7ec; color:#667085; cursor:not-allowed; }
        .view-revocation-button { border-color:#dce4f0; background:#fff; color:var(--navy); }
        .view-revocation-button:hover { background:#f5f7fb; }
        .empty { padding:36px!important; color:var(--muted); text-align:center; }
        .cert-modal-overlay { position:fixed; inset:0; z-index:3000; display:flex; align-items:center; justify-content:center; padding:14px; background:rgba(15,23,42,.48); backdrop-filter:blur(2px); }
        .cert-modal-overlay[hidden],.cert-modal[hidden] { display:none; }
        .cert-modal { width:min(520px,calc(100% - 28px)); max-height:calc(100% - 28px); overflow:auto; padding:0; border:1px solid var(--line); border-radius:14px; background:#fff; color:var(--text); box-shadow:0 24px 70px rgba(10,20,55,.24); }
        .cert-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:17px 19px; border-bottom:1px solid var(--line); }
        .cert-modal-head h2 { margin:0; color:var(--navy); font-size:18px; }
        .cert-modal-head p { margin:5px 0 0; color:var(--muted); font-size:12px; line-height:1.45; }
        .cert-modal-close { width:32px; height:32px; flex:0 0 32px; border:0; border-radius:7px; background:#f3f5f9; color:#475467; font-size:20px; cursor:pointer; }
        .cert-modal-body { display:grid; gap:12px; padding:19px; }
        .cert-modal-body[hidden] { display:none; }
        .cert-modal-body label { color:#344054; font-size:12px; font-weight:700; }
        .cert-modal-body textarea { width:100%; min-height:110px; padding:10px 11px; border:1px solid #d0d5dd; border-radius:8px; font:inherit; font-size:13px; resize:vertical; }
        .cert-modal-actions { display:flex; justify-content:flex-end; gap:8px; padding-top:4px; }
        .cert-cancel-button { border:1px solid var(--line); border-radius:8px; padding:7px 11px; background:#fff; color:var(--text); font:inherit; font-size:12px; font-weight:700; cursor:pointer; }
        .cert-confirm-copy { margin:0; padding:19px 19px 4px; color:var(--text); font-size:13px; line-height:1.55; }
        .revocation-details { display:grid; gap:12px; padding:19px; }
        .revocation-details div { display:grid; gap:4px; }
        .revocation-details dt { color:var(--muted); font-size:10px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
        .revocation-details dd { margin:0; color:var(--text); font-size:13px; line-height:1.5; overflow-wrap:anywhere; }
        @media(max-width:1024px) { .cert-main { padding:24px 18px 40px!important; } }
        @media(max-width:600px) { .cert-heading h1 { font-size:26px; } .cert-modal-actions>* { flex:1; } }
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => $user])
    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main cert-main">
            <div class="cert-content">
                <header class="cert-heading">
                    <h1>Credentials</h1>
                    <p>Review issued certificates and badges. Revocations include the administrator and reason.</p>
                </header>

                <x-flash-toast :message="session('success')" />
                @if($errors->any())
                    <div class="notice error" role="alert">{{ $errors->first() }}</div>
                @endif

                <nav class="credential-tabs" aria-label="Credential type">
                    <a class="credential-tab {{ $credentialType === 'certificates' ? 'active' : '' }}" href="{{ route('admin.certificates') }}" @if($credentialType === 'certificates') aria-current="page" @endif>Certificates</a>
                    <a class="credential-tab {{ $credentialType === 'badges' ? 'active' : '' }}" href="{{ route('admin.certificates', ['type' => 'badges']) }}" @if($credentialType === 'badges') aria-current="page" @endif>Badges</a>
                </nav>

                <form class="credential-filters" method="GET" action="{{ route('admin.certificates') }}" role="search">
                    <input type="hidden" name="type" value="{{ $credentialType }}">
                    <input type="search" name="search" value="{{ $search }}" maxlength="120" placeholder="{{ $credentialType === 'badges' ? 'Search badge, student, email, or credential ID' : 'Search certificate, student, email, course, or serial' }}" aria-label="Search {{ $credentialType }}">
                    <select name="status" aria-label="Filter by status">
                        <option value="all" @selected($status === 'all')>All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="revoked" @selected($status === 'revoked')>Revoked</option>
                    </select>
                    <button class="credential-filter-submit" type="submit">Search / Filter</button>
                    @if($search !== '' || $status !== 'all')
                        <a class="credential-filter-clear" href="{{ route('admin.certificates', ['type' => $credentialType]) }}">Clear</a>
                    @endif
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            @if($credentialType === 'certificates')
                                <tr><th>Certificate</th><th>Student</th><th>Course</th><th>Issued</th><th>Status</th><th>Revocation / action</th></tr>
                            @else
                                <tr><th>Badge</th><th>Student</th><th>Credential ID</th><th>Earned</th><th>Status</th><th>Revocation / action</th></tr>
                            @endif
                        </thead>
                        <tbody>
                            @if($credentialType === 'certificates')
                                @forelse($credentials as $certificate)
                                    <tr id="certificate-{{ $certificate->id }}">
                                        <td>
                                            <div class="primary">{{ $certificate->serial }}</div>
                                            <div class="secondary">{{ $certificate->microcredential_title_snapshot ?: $certificate->title ?: 'Certificate' }}</div>
                                        </td>
                                        <td><div class="primary">{{ $certificate->user?->name ?? 'Deleted account' }}</div><div class="secondary">{{ $certificate->user?->email ?? '—' }}</div></td>
                                        <td>{{ $certificate->course?->title ?? 'Deleted course' }}</td>
                                        <td>{{ $certificate->issued_at?->format('M j, Y') ?? '—' }}</td>
                                        <td><span class="status {{ $certificate->status === 'revoked' ? 'revoked' : '' }}">{{ $certificate->status ?: 'active' }}</span></td>
                                        <td>
                                            @if($certificate->status === 'revoked')
                                                <button class="view-revocation-button" type="button" data-view-revocation data-kind="certificate" data-name="{{ $certificate->microcredential_title_snapshot ?: $certificate->title ?: 'Certificate' }}" data-identifier="{{ $certificate->serial }}" data-date="{{ $certificate->revoked_at?->format('M j, Y g:i A') ?? '—' }}" data-reason="{{ $certificate->revocation_reason ?? '' }}" data-revoker="{{ $certificate->revoker?->name ?? '—' }}">View</button>
                                            @else
                                                <button class="revoke-button" type="button" data-open-revoke data-kind="certificate" data-id="{{ $certificate->id }}" data-action="{{ route('admin.certificates.revoke', $certificate->id) }}" data-name="{{ $certificate->microcredential_title_snapshot ?: $certificate->title ?: 'Certificate' }}" data-identifier="{{ $certificate->serial }}">Revoke</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="empty" colspan="6">{{ $search !== '' || $status !== 'all' ? 'No certificates match these filters.' : 'No certificates have been issued yet.' }}</td></tr>
                                @endforelse
                            @else
                                @forelse($credentials as $userBadge)
                                    <tr id="badge-{{ $userBadge->id }}">
                                        <td><div class="primary">{{ $userBadge->badge?->name ?? 'Deleted badge' }}</div><div class="secondary">{{ $userBadge->badge?->description ?? '' }}</div></td>
                                        <td><div class="primary">{{ $userBadge->user?->name ?? 'Deleted account' }}</div><div class="secondary">{{ $userBadge->user?->email ?? '—' }}</div></td>
                                        <td>{{ $userBadge->credential_uid ?: 'Badge #'.$userBadge->id }}</td>
                                        <td>{{ $userBadge->earned_at?->format('M j, Y') ?? '—' }}</td>
                                        <td><span class="status {{ $userBadge->status === 'revoked' ? 'revoked' : '' }}">{{ $userBadge->status ?: 'active' }}</span></td>
                                        <td>
                                            @if($userBadge->status === 'revoked')
                                                <button class="view-revocation-button" type="button" data-view-revocation data-kind="badge" data-name="{{ $userBadge->badge?->name ?? 'Badge' }}" data-identifier="{{ $userBadge->credential_uid ?: 'Badge #'.$userBadge->id }}" data-date="{{ $userBadge->revoked_at?->format('M j, Y g:i A') ?? '—' }}" data-reason="{{ $userBadge->revocation_reason ?? '' }}" data-revoker="{{ $userBadge->revoker?->name ?? '—' }}">View</button>
                                            @else
                                                <button class="revoke-button" type="button" data-open-revoke data-kind="badge" data-id="{{ $userBadge->id }}" data-action="{{ route('admin.badges.revoke', $userBadge->id) }}" data-name="{{ $userBadge->badge?->name ?? 'Badge' }}" data-identifier="{{ $userBadge->credential_uid ?: 'Badge #'.$userBadge->id }}">Revoke</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="empty" colspan="6">{{ $search !== '' || $status !== 'all' ? 'No badges match these filters.' : 'No badges have been issued yet.' }}</td></tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                    @if($credentials->hasPages())
                        @include('components.pagination', ['paginator' => $credentials])
                    @endif
                </div>
            </div>
        </main>
    </div>
    <div class="cert-modal-overlay" id="revoke-certificate-modal" hidden>
        <section class="cert-modal" id="revoke-reason-step" role="dialog" aria-modal="true" aria-labelledby="revoke-credential-title">
            <form method="POST" id="revoke-certificate-form" class="cert-modal-body" data-custom-confirmation>
            @csrf
            <input type="hidden" name="credential_type" id="revoke-credential-type" value="{{ old('credential_type') }}">
            <input type="hidden" name="credential_id" id="revoke-credential-id" value="{{ old('credential_id') }}">
            <header class="cert-modal-head" style="margin:-19px -19px 4px;">
                <div>
                    <h2 id="revoke-credential-title">Revoke credential</h2>
                    <p>Enter a reason for revoking <strong id="revoke-credential-name"></strong> (<span id="revoke-credential-identifier"></span>).</p>
                </div>
                <button class="cert-modal-close" type="button" data-close-cert-modal aria-label="Close">&times;</button>
            </header>
            <label for="revocation-reason">Reason for revocation</label>
            <textarea id="revocation-reason" name="revocation_reason" minlength="5" maxlength="1000" required placeholder="Explain why this credential is being revoked">{{ old('revocation_reason') }}</textarea>
            @error('revocation_reason')<span class="notice error" role="alert" style="margin:0;padding:0;background:none;">{{ $message }}</span>@enderror
            <div class="cert-modal-actions">
                <button class="cert-cancel-button" type="button" data-close-cert-modal>Cancel</button>
                <button class="revoke-button" id="submit-revoke-reason" type="submit" disabled>Revoke</button>
            </div>
            </form>
        </section>
        <section class="cert-modal" id="confirm-revoke-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-revoke-title" hidden>
            <header class="cert-modal-head">
                <div>
                    <h2 id="confirm-revoke-title">Confirm credential revocation</h2>
                    <p>This action cannot be undone.</p>
                </div>
            </header>
            <p class="cert-confirm-copy">Are you sure you want to revoke this <strong id="confirm-revoke-kind">credential</strong>?</p>
            <div class="cert-modal-actions" style="padding:14px 19px 19px;">
                <button class="cert-cancel-button" type="button" data-cancel-revoke>Cancel</button>
                <button class="revoke-button" type="button" data-confirm-revoke>Revoke credential</button>
            </div>
        </section>
    </div>
    <dialog class="cert-modal" id="view-revocation-modal" aria-labelledby="view-revocation-title">
        <header class="cert-modal-head">
            <div>
                <h2 id="view-revocation-title">Revocation details</h2>
                <p><span id="view-revocation-kind"></span>: <strong id="view-revocation-identifier"></strong></p>
            </div>
            <button class="cert-modal-close" type="button" data-close-cert-modal aria-label="Close">&times;</button>
        </header>
        <dl class="revocation-details">
            <div><dt>Revoked</dt><dd id="view-revocation-date"></dd></div>
            <div><dt>Revoked by</dt><dd id="view-revocation-admin"></dd></div>
            <div><dt>Reason</dt><dd id="view-revocation-reason"></dd></div>
        </dl>
    </dialog>
    <script>
        (function () {
            const revokeModal = document.getElementById('revoke-certificate-modal');
            const revokeForm = document.getElementById('revoke-certificate-form');
            const revokeId = document.getElementById('revoke-credential-id');
            const revokeType = document.getElementById('revoke-credential-type');
            const revokeName = document.getElementById('revoke-credential-name');
            const revokeIdentifier = document.getElementById('revoke-credential-identifier');
            const reasonField = document.getElementById('revocation-reason');
            const reasonSubmitButton = document.getElementById('submit-revoke-reason');
            const reasonStep = document.getElementById('revoke-reason-step');
            const confirmModal = document.getElementById('confirm-revoke-modal');
            const detailsModal = document.getElementById('view-revocation-modal');

            function updateReasonSubmitState() {
                reasonSubmitButton.disabled = reasonField.value.trim().length < 5 || !reasonField.checkValidity();
            }

            reasonField.addEventListener('input', updateReasonSubmitState);
            updateReasonSubmitState();

            revokeForm.addEventListener('submit', (event) => {
                event.preventDefault();
                updateReasonSubmitState();
                if (reasonSubmitButton.disabled || !revokeForm.reportValidity()) return;
                reasonStep.hidden = true;
                confirmModal.hidden = false;
                document.getElementById('confirm-revoke-kind').textContent = revokeType.value || 'credential';
                document.querySelector('[data-confirm-revoke]').textContent = 'Revoke ' + (revokeType.value || 'credential');
                document.querySelector('[data-cancel-revoke]').focus();
            });

            document.querySelectorAll('[data-open-revoke]').forEach((button) => {
                button.addEventListener('click', () => {
                    revokeForm.action = button.dataset.action;
                    revokeId.value = button.dataset.id || '';
                    revokeType.value = button.dataset.kind || 'credential';
                    revokeName.textContent = button.dataset.name || revokeType.value;
                    revokeIdentifier.textContent = button.dataset.identifier || '';
                    reasonField.value = '';
                    updateReasonSubmitState();
                    reasonStep.hidden = false;
                    confirmModal.hidden = true;
                    revokeModal.hidden = false;
                    reasonField.focus();
                });
            });

            document.querySelectorAll('[data-view-revocation]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('view-revocation-kind').textContent = (button.dataset.kind || 'credential').replace(/^./, (letter) => letter.toUpperCase());
                    document.getElementById('view-revocation-identifier').textContent = button.dataset.identifier || '—';
                    document.getElementById('view-revocation-title').textContent = (button.dataset.kind || 'Credential').replace(/^./, (letter) => letter.toUpperCase()) + ' revocation details';
                    document.getElementById('view-revocation-date').textContent = button.dataset.date || '—';
                    document.getElementById('view-revocation-admin').textContent = button.dataset.revoker || '—';
                    document.getElementById('view-revocation-reason').textContent = button.dataset.reason || '—';
                    detailsModal.showModal();
                });
            });

            document.querySelectorAll('[data-close-cert-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = button.closest('dialog');
                    if (dialog) {
                        dialog.close();
                    } else {
                        revokeModal.hidden = true;
                    }
                });
            });

            document.querySelector('[data-cancel-revoke]').addEventListener('click', () => {
                confirmModal.hidden = true;
                reasonStep.hidden = false;
                reasonField.focus();
            });

            document.querySelector('[data-confirm-revoke]').addEventListener('click', () => {
                revokeModal.hidden = true;
                HTMLFormElement.prototype.submit.call(revokeForm);
            });

            revokeModal.addEventListener('click', (event) => {
                if (event.target === revokeModal) revokeModal.hidden = true;
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape' || revokeModal.hidden) return;
                if (!confirmModal.hidden) {
                    confirmModal.hidden = true;
                    reasonStep.hidden = false;
                    reasonField.focus();
                    return;
                }
                revokeModal.hidden = true;
            });

            detailsModal.addEventListener('click', (event) => {
                if (event.target === detailsModal) detailsModal.close();
            });

            @if($errors->has('revocation_reason') && old('credential_id'))
                const failedRevokeButton = document.querySelector('[data-open-revoke][data-kind="{{ old('credential_type') }}"][data-id="{{ old('credential_id') }}"]');
                if (failedRevokeButton) {
                    revokeForm.action = failedRevokeButton.dataset.action;
                    revokeId.value = failedRevokeButton.dataset.id || '';
                    revokeType.value = failedRevokeButton.dataset.kind || 'credential';
                    revokeName.textContent = failedRevokeButton.dataset.name || revokeType.value;
                    revokeIdentifier.textContent = failedRevokeButton.dataset.identifier || '';
                    updateReasonSubmitState();
                    reasonStep.hidden = false;
                    confirmModal.hidden = true;
                    revokeModal.hidden = false;
                }
            @endif
        })();
    </script>
    @include('components.responsive')
</body>
</html>
