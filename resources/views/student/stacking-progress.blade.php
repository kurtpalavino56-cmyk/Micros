<x-layout title="Stacking Progress | Upskill" shell="student" page="stacking-progress">
            <div class="page">
                <header class="page-head page-header ui-page-header student-page-heading">
                    <div>
                        <h1 class="ui-page-title">Stacking Progress</h1>
                        <p class="subtitle ui-page-subtitle">Track officially completed microcredentials toward approved frameworks.</p>
                    </div>
                </header>

                @forelse($frameworks as $item)
                    @php
                        $framework = $item['framework'];
                        $isMet = $item['status'] === 'requirements_met';
                    @endphp
                    <section class="framework-card ui-card-surface">
                        <div class="framework-head ui-card-header">
                            <div class="ui-card-header__content">
                                <h2 class="ui-section-title">{{ $framework->name }}</h2>
                                @if($framework->description)<p class="framework-description ui-card-header__subtitle">{{ $framework->description }}</p>@endif
                            </div>
                            <span class="status ui-badge {{ $isMet ? 'ui-badge--success met' : 'ui-badge--warning' }}">{{ $isMet ? 'Requirements Met' : 'In Progress' }}</span>
                        </div>
                        <div class="meta">
                            @if($framework->pqf_level)<span>PQF <strong>{{ $framework->pqf_level }}</strong></span>@endif
                            @if($framework->target_recognition)<span>Target <strong>{{ $framework->target_recognition }}</strong></span>@endif
                            <span><strong>{{ $item['completed_required_count'] }}</strong> of <strong>{{ $item['target_required_count'] }}</strong> required target met</span><span>{{ $item['total_required_count'] }} required configured</span>
                            <span><strong>{{ $item['remaining_required_count'] }}</strong> remaining</span>
                        </div>
                        <div class="progress-row" aria-label="{{ $item['completion_percentage'] }} percent complete">
                            <div class="track ui-progress-track"><div class="fill ui-progress-fill" style="width:{{ $item['completion_percentage'] }}%"></div></div>
                            <div class="percent">{{ $item['completion_percentage'] }}%</div>
                        </div>

                        @if($isMet)
                            <a class="evidence-report-link ui-button ui-button--secondary" href="{{ route('stacking.evidence-report', $framework->id) }}" target="_blank" rel="noopener">Generate academic credit evidence report</a>
                        @endif
                        <div class="requirements-wrap">
                            <table class="requirements ui-table">
                                <thead><tr><th>Microcredential</th><th>Order</th><th>Type</th><th>Completion</th></tr></thead>
                                <tbody>
                                    @foreach($item['requirements'] as $detail)
                                        <tr>
                                            <td><span class="course-title">{{ $detail['course']->title }}</span></td>
                                            <td>{{ $framework->sequence_required ? $detail['requirement']->order : '—' }}</td>
                                            <td>{{ $detail['is_required'] ? 'Required' : 'Optional' }}</td>
                                            <td>
                                                @if($detail['is_completed'])
                                                    <span class="complete">Completed</span><br><small>{{ $detail['completed_at']?->format('M j, Y') }}</small>
                                                @else
                                                    <span class="pending">Not completed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @empty
                    <div class="empty ui-empty-state">No active stacking frameworks match your enrolled microcredentials yet.</div>
                @endforelse
            </div>
</x-layout>
