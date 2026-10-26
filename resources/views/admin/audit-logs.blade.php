
<x-admin.layout title="Audit Logs | UPSKILL Admin" page="admin-audit-logs" :user="$user" :use-auth-user="false">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">
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
                    <div class="audit-card adm-inline-admin-audit-logs-1" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="audit-layout">
                <section class="audit-card audit-table-wrap" aria-label="System activity audit log entries">
                    <div class="paginated-table-scroll audit-table-scroll">
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
                    </div>
                    @include('components.pagination', ['paginator' => $logs, 'ariaLabel' => 'Audit log pages'])
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
</x-admin.layout>
