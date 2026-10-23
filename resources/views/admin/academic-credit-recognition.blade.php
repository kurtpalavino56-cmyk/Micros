
<x-admin.layout title="Academic Credit Recognition | UPSKILL" page="admin-academic-credit-recognition" :user="$user" :use-auth-user="false">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">
        <main class="main recognition-main">
            <div class="recognition-content">
                <header class="recognition-head">
                    <h1>Legacy Academic Credit Requests</h1>
                    <p>Historical requests are retained for reference. UpSkill now prepares evidence reports; academic credit decisions and registrar recording must be completed through institutional processes outside this platform.</p>
                </header>

                <x-flash-toast :message="session('success')" />
                @if($errors->any())<div class="notice err">{{ $errors->first() }}</div>@endif

                <h2>Students ready for an evidence report</h2>
                <p>These stacking records meet the platform’s configured requirements. Generate a report to submit through the institution’s separate credit-review process.</p>
                <section class="recognition-list adm-inline-admin-academic-credit-recognition-1">
                    @forelse($eligibleReports as $progress)
                        @if($progress->user && $progress->framework)
                            <article class="recognition-card">
                                <div class="recognition-grid">
                                    <div class="meta"><span>Student</span><strong>{{ $progress->user->name }}</strong></div>
                                    <div class="meta"><span>Framework</span><strong>{{ $progress->framework->name }}</strong></div>
                                    <div class="meta"><span>Platform status</span><strong>Requirements met</strong></div>
                                    <div class="meta"><span>Ready since</span><strong>{{ $progress->requirements_met_at?->format('M j, Y') ?? '—' }}</strong></div>
                                </div>
                                <div class="actions"><a href="{{ route('admin.credit-evidence', [$progress->user->id, $progress->framework->id]) }}" target="_blank" rel="noopener" class="adm-inline-admin-academic-credit-recognition-2">Generate evidence report</a></div>
                            </article>
                        @endif
                    @empty
                        <div class="empty-card">No student stacking records have met their configured requirements yet.</div>
                    @endforelse
                </section>

                <h2>Historical requests</h2>
                <section class="recognition-list">
                    @forelse($recognitions as $record)
                        <article class="recognition-card">
                            <div class="recognition-grid">
                                <div class="meta"><span>Learner</span><strong>{{ $record->user?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Framework</span><strong>{{ $record->framework?->name ?? '—' }}</strong></div>
                                <div class="meta"><span>Equivalent course</span><strong>{{ $record->framework?->equivalent_course ?? '—' }}</strong></div>
                                <div class="meta"><span>Historical status</span><strong><span class="status {{ $record->status === 'registrar_recorded' ? 'done' : '' }}">{{ str_replace('_',' ',ucfirst($record->status)) }}</span></strong></div>
                            </div>
                            <div class="actions">
                                @if($record->user && $record->framework)<a class="actions button adm-inline-admin-academic-credit-recognition-3" href="{{ route('admin.credit-evidence', [$record->user->id, $record->framework->id]) }}" target="_blank" rel="noopener">Generate evidence report</a>@endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-card">No academic credit recognition requests are currently available.</div>
                    @endforelse
                </section>
            </div>
        </main>
    </div>
</x-admin.layout>
