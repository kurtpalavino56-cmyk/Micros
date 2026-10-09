<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | UPSKILL Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <style>
        :root{--navy:#0d1b6e;--ink:#18213d;--muted:#68758c;--line:#e2e7f0;--bg:#f6f8fc;--gold:#f4c430;--shadow:0 8px 24px rgba(13,27,110,.06);}
        *{box-sizing:border-box;}
        body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;}
        .audit-main{padding:30px 32px 48px!important;}
        .audit-content{max-width:1440px;margin:0 auto;}
        .audit-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px;}
        .audit-heading h1{margin:0 0 6px;color:var(--navy);font-size:30px;}
        .audit-heading p{margin:0;color:var(--muted);font-size:14px;line-height:1.5;}
        .audit-count{flex:none;padding:8px 13px;border:1px solid var(--line);border-radius:999px;background:#fff;color:var(--muted);font-size:13px;font-weight:700;}
        .audit-card{margin-bottom:18px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:var(--shadow);}
        .audit-layout{display:grid;grid-template-columns:minmax(0,1fr) 286px;gap:18px;align-items:start;}
        .audit-filters{display:flex;flex-direction:column;gap:14px;padding:18px;}
        .audit-filter-title{margin:0 0 2px;color:var(--ink);font-size:15px;}
        .audit-field{display:flex;flex-direction:column;gap:6px;min-width:0;}
        .audit-field label{color:#475467;font-size:12px;font-weight:700;}
        .audit-field input,.audit-field select{width:100%;height:40px;padding:8px 10px;border:1px solid #d0d5dd;border-radius:8px;background:#fff;color:var(--ink);font:inherit;font-size:14px;}
        .audit-button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:9px 15px;border:1px solid var(--navy);border-radius:8px;background:var(--navy);color:#fff;font:inherit;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;}
        .audit-button:hover{background:#192b91;}
        .audit-button.secondary{background:#fff;color:var(--navy);}
        .audit-button.secondary:hover{background:#f2f4f8;}
        .audit-actions{display:flex;gap:9px;flex-wrap:wrap;}
        .audit-table-wrap{overflow-x:auto;}
        .audit-table{width:100%;min-width:860px;border-collapse:collapse;}
        .audit-table th,.audit-table td{padding:13px 15px;border-bottom:1px solid #edf0f5;text-align:left;vertical-align:top;font-size:13px;}
        .audit-table th{background:#f9faff;color:#667085;font-size:11px;letter-spacing:.05em;text-transform:uppercase;}
        .audit-table tr:last-child td{border-bottom:0;}
        .audit-event{color:var(--ink);font-weight:700;}
        .audit-meta{margin-top:4px;color:var(--muted);font-size:12px;line-height:1.45;overflow-wrap:anywhere;}
        .audit-method{display:inline-flex;padding:4px 7px;border-radius:6px;background:#f1f3f8;color:#475467;font-size:11px;font-weight:800;}
        .audit-details-button{padding:7px 10px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--navy);font:inherit;font-size:12px;font-weight:700;white-space:nowrap;cursor:pointer;}
        .audit-details-button:hover{border-color:#c5cbe0;background:#f8f9fc;}
        .audit-technical-modal{width:min(480px,calc(100% - 28px));max-width:none;max-height:calc(100% - 28px);padding:0;border:1px solid var(--line);border-radius:14px;background:#fff;color:var(--ink);box-shadow:0 24px 70px rgba(10,20,55,.24);}
        .audit-technical-modal::backdrop{background:rgba(15,23,42,.48);backdrop-filter:blur(2px);}
        .audit-technical-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px 20px;border-bottom:1px solid var(--line);}
        .audit-technical-head h2{margin:0;color:var(--navy);font-size:17px;}
        .audit-technical-head p{margin:5px 0 0;color:var(--muted);font-size:12px;line-height:1.45;}
        .audit-technical-close{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;flex:0 0 32px;border:0;border-radius:7px;background:#f3f5f9;color:#475467;font-size:20px;cursor:pointer;}
        .audit-technical-body{display:grid;gap:14px;padding:20px;}
        .audit-technical-item{display:grid;gap:5px;min-width:0;}
        .audit-technical-item[hidden]{display:none;}
        .audit-technical-label{color:var(--muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;}
        .audit-technical-value{color:var(--ink);font-size:13px;line-height:1.5;overflow-wrap:anywhere;}
        .audit-role{display:inline-flex;margin-top:5px;padding:3px 8px;border-radius:999px;background:#eef1fb;color:var(--navy);font-size:10px;font-weight:700;text-transform:capitalize;}
        .audit-change-button{min-height:25px;padding:4px 7px;border:1px solid #dfe4ee;border-radius:6px;background:#fff;color:var(--navy);font:inherit;font-size:11px;font-weight:700;white-space:nowrap;cursor:pointer;}
        .audit-change-button:hover{border-color:#c5cbe0;background:#f8f9fc;}
        .audit-changes-modal{width:min(760px,calc(100% - 28px));}
        .audit-changes-table-wrap{max-height:min(60vh,520px);overflow:auto;padding:18px 20px;}
        .audit-changes-table{width:100%;border-collapse:collapse;font-size:12px;}
        .audit-changes-table th,.audit-changes-table td{padding:9px 10px;border:1px solid #edf0f5;text-align:left;vertical-align:top;overflow-wrap:anywhere;}
        .audit-changes-table th{position:sticky;top:0;background:#f9faff;color:#667085;font-size:10px;text-transform:uppercase;letter-spacing:.04em;}
        .audit-empty{padding:36px!important;color:var(--muted);text-align:center;}
        .audit-pagination{padding:15px;}
        @media(max-width:1200px){.audit-layout{grid-template-columns:minmax(0,1fr);}.audit-filters{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));}.audit-filter-title,.audit-actions{grid-column:1/-1;}}
        @media(max-width:700px){.audit-main{padding:22px 14px 40px!important;}.audit-heading{flex-direction:column;}.audit-heading h1{font-size:25px;}.audit-filters{display:flex;}.audit-actions{width:100%;}.audit-actions>*{flex:1;}}
    </style>
</head>
<body>
    @include('components.authenticated-topbar', ['user' => $user])
    <div class="layout admin-layout">
        @include('components.admin-sidebar')
        <main class="main audit-main">
            <div class="audit-content">
                <header class="audit-heading">
                    <div>
                        <h1>Audit Logs</h1>
                        <p>Review system activity and download a report for a selected date range.</p>
                    </div>
                    <span class="audit-count">{{ number_format($totalLogs) }} {{ $totalLogs === 1 ? 'record' : 'records' }}</span>
                </header>

                @if($errors->any())
                    <div class="audit-card" role="alert" style="padding:14px 18px;color:#b42318;">{{ $errors->first() }}</div>
                @endif

                <div class="audit-layout">
                <section class="audit-card audit-table-wrap" aria-label="System activity audit log entries">
                    <table class="audit-table">
                        <thead>
                            <tr><th>Date and time</th><th>User</th><th>Action</th><th>Target</th><th>Technical details</th></tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at?->format('M j, Y g:i:s A') }}</td>
                                    <td><span class="audit-event">{{ $log->actor_name }}</span><br><span class="audit-role">{{ $log->actor_role === 'Administrator' ? 'Admin' : $log->actor_role }}</span></td>
                                    <td>
                                        <span class="audit-event">{{ $log->event }}</span>
                                        @if(is_array($log->changes) && count($log->changes) > 0)
                                            <button class="audit-change-button" type="button" data-audit-changes data-changes="{{ base64_encode(json_encode($log->changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}">View changes ({{ count($log->changes) }})</button>
                                        @endif
                                    </td>
                                    <td>{{ $log->target_label ?: '—' }}</td>
                                    <td><button class="audit-details-button" type="button" data-audit-details data-method="{{ $log->method }}" data-route="{{ $log->route }}" data-ip="{{ $log->ip_address ?? '' }}">View details</button></td>
                                </tr>
                            @empty
                                <tr><td class="audit-empty" colspan="5">No activity was recorded for these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($logs->hasPages())
                        <div class="audit-pagination">{{ $logs->links() }}</div>
                    @endif
                </section>
                <aside class="audit-card" aria-label="Audit log filters">
                    <form class="audit-filters" method="GET" action="{{ route('admin.audit-logs') }}">
                        <h2 class="audit-filter-title">Filter activity</h2>
                        <div class="audit-field"><label for="start_date">From</label><input id="start_date" type="date" name="start_date" value="{{ $startDate }}" required></div>
                        <div class="audit-field"><label for="end_date">To</label><input id="end_date" type="date" name="end_date" value="{{ $endDate }}" required></div>
                        <div class="audit-field">
                            <label for="actor_role">Actor role</label>
                            <select id="actor_role" name="actor_role">
                                <option value="all" @selected($actorRole === 'all')>All roles</option>
                                <option value="admin" @selected($actorRole === 'admin')>Admin</option>
                                <option value="faculty" @selected($actorRole === 'faculty')>Faculty</option>
                                <option value="student" @selected($actorRole === 'student')>Student</option>
                            </select>
                        </div>
                        <div class="audit-field">
                            <label for="action">Action</label>
                            <select id="action" name="action">
                                <option value="all" @selected($selectedAction === 'all')>All actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" @selected($selectedAction === $action)>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="audit-actions">
                            <button class="audit-button" type="submit">Apply filters</button>
                            <a class="audit-button secondary" href="{{ route('admin.audit-logs.download', ['start_date' => $startDate, 'end_date' => $endDate, 'actor_role' => $actorRole, 'action' => $selectedAction]) }}">Download report (CSV)</a>
                        </div>
                    </form>
                </aside>
                </div>
            </div>
        </main>
    </div>
    <dialog class="audit-technical-modal" id="audit-technical-modal" aria-labelledby="audit-technical-title">
        <header class="audit-technical-head">
            <div>
                <h2 id="audit-technical-title">Technical details</h2>
                <p>Request metadata recorded for this action.</p>
            </div>
            <button class="audit-technical-close" type="button" data-close-audit-details aria-label="Close details">&times;</button>
        </header>
        <div class="audit-technical-body">
            <div class="audit-technical-item"><span class="audit-technical-label">Method</span><span class="audit-method" id="audit-method-value"></span></div>
            <div class="audit-technical-item"><span class="audit-technical-label">Route</span><span class="audit-technical-value" id="audit-route-value"></span></div>
            <div class="audit-technical-item" id="audit-ip-row"><span class="audit-technical-label">IP address</span><span class="audit-technical-value" id="audit-ip-value"></span></div>
        </div>
    </dialog>
    <dialog class="audit-technical-modal audit-changes-modal" id="audit-changes-modal" aria-labelledby="audit-changes-title">
        <header class="audit-technical-head">
            <div>
                <h2 id="audit-changes-title">Changes</h2>
                <p>Previous and updated values recorded for this action.</p>
            </div>
            <button class="audit-technical-close" type="button" data-close-audit-changes aria-label="Close changes">&times;</button>
        </header>
        <div class="audit-changes-table-wrap">
            <table class="audit-changes-table">
                <thead><tr><th>Field</th><th>Previous value</th><th>New value</th></tr></thead>
                <tbody id="audit-changes-body"></tbody>
            </table>
        </div>
    </dialog>
    <script>
        (function () {
            const modal = document.getElementById('audit-technical-modal');
            const methodValue = document.getElementById('audit-method-value');
            const routeValue = document.getElementById('audit-route-value');
            const ipRow = document.getElementById('audit-ip-row');
            const ipValue = document.getElementById('audit-ip-value');
            const changesModal = document.getElementById('audit-changes-modal');
            const changesBody = document.getElementById('audit-changes-body');

            document.querySelectorAll('[data-audit-details]').forEach((button) => {
                button.addEventListener('click', () => {
                    methodValue.textContent = button.dataset.method || '—';
                    routeValue.textContent = button.dataset.route || '—';
                    ipValue.textContent = button.dataset.ip || '';
                    ipRow.hidden = !button.dataset.ip;
                    modal.showModal();
                });
            });

            document.querySelector('[data-close-audit-details]').addEventListener('click', () => modal.close());
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.close();
                }
            });

            document.querySelectorAll('[data-audit-changes]').forEach((button) => {
                button.addEventListener('click', () => {
                    const bytes = Uint8Array.from(atob(button.dataset.changes || ''), (character) => character.charCodeAt(0));
                    const changes = JSON.parse(new TextDecoder().decode(bytes));
                    changesBody.replaceChildren();

                    Object.entries(changes).forEach(([field, values]) => {
                        const row = document.createElement('tr');
                        const fieldCell = document.createElement('td');
                        const previousCell = document.createElement('td');
                        const nextCell = document.createElement('td');
                        fieldCell.textContent = field.replace(/([a-z])([A-Z])/g, '$1 $2').replace(/[_-]+/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase());
                        previousCell.textContent = formatAuditValue(values.old);
                        nextCell.textContent = formatAuditValue(values.new);
                        row.append(fieldCell, previousCell, nextCell);
                        changesBody.appendChild(row);
                    });

                    changesModal.showModal();
                });
            });

            document.querySelector('[data-close-audit-changes]').addEventListener('click', () => changesModal.close());
            changesModal.addEventListener('click', (event) => {
                if (event.target === changesModal) {
                    changesModal.close();
                }
            });

            function formatAuditValue(value) {
                if (value === null || value === undefined) {
                    return '—';
                }

                if (typeof value === 'object' || typeof value === 'boolean') {
                    return JSON.stringify(value);
                }

                return String(value);
            }
        })();
    </script>
    @include('components.responsive')
</body>
</html>
