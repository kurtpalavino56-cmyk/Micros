<x-layout title="My Pathways | UpSkill PSU" shell="student" page="pathways">
        <div class="pathways-page">
            <header class="page-header ui-page-header student-page-heading">
                <div class="ui-page-header__content">
                    <h1 class="ui-page-title">My Pathways</h1>
                    <p class="ui-page-subtitle">{{ $selectedPathway ? 'Your learning journey to '.($pathwayDestination ?: $selectedPathway->name).'.' : 'Choose a learning pathway when you are ready.' }}</p>
                </div>
            </header>

            <x-flash-toast :message="session('success')" />

            @unless($selectedPathway)
                <section class="panel empty-pathway ui-card-surface">
                    <div class="empty-copy">
                        <h2>Your pathway journey starts when you choose one</h2>
                        <p>You have not selected a pathway yet. Browse the pathways available whenever you are ready. Meanwhile, these course suggestions use the skills you selected in your profile.</p>
                    </div>
                    <button type="button" class="gold-button ui-button ui-button--primary" data-open-pathway-picker>Select a pathway <span aria-hidden="true">&gt;</span></button>
                </section>
            @else
                <div class="selected-pathway-head">
                    <div><p class="eyebrow">Your selected pathway</p><h2 class="ui-section-title">{{ $selectedPathway->name }}</h2><p>{{ $selectedPathway->description ?: 'Follow the courses in this pathway at your own pace.' }}</p></div>
                    <button type="button" class="outline-button ui-button ui-button--secondary" data-open-pathway-picker>Change pathway</button>
                </div>

                <section class="panel pathway-panel ui-card-surface" aria-label="Pathway journey timeline">
                    @if(count($pathwaySteps))
                        @php
                            $goalCount = count($pathwaySteps);
                            $currentIndex = collect($pathwaySteps)->search(fn ($step) => $step['status'] === 'current');
                            $lineInset = $goalCount > 0 ? 50 / ($goalCount + 1) : 0;
                            $fillWidth = $currentIndex === false ? 0 : ($currentIndex / ($goalCount + 1)) * 100;
                        @endphp
                        <div class="journey">
                            <div class="journey-track" style="left:{{ $lineInset }}%;right:{{ $lineInset }}%"></div>
                            @if($currentIndex !== false)<div class="journey-fill ui-progress-fill" style="left:{{ $lineInset }}%;width:{{ $fillWidth }}%"></div>@endif
                            <div class="journey-goals">
                                @foreach($pathwaySteps as $step)
                                    <div class="journey-goal {{ $step['status'] === 'locked' ? 'is-locked' : '' }}">
                                        <div class="goal-node {{ $step['status'] === 'current' ? 'is-current' : ($step['status'] === 'locked' ? 'is-locked' : '') }}">
                                            @if($step['status'] === 'completed')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                            @elseif($step['status'] === 'locked')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                            @else{{ $loop->iteration }}@endif
                                        </div>
                                        <span class="goal-label">{{ $step['label'] }}</span>
                                        <span class="goal-title">{{ $step['title'] }}</span>
                                        <span class="goal-state">{{ $step['status'] === 'completed' ? 'Completed' : ($step['status'] === 'current' ? 'In progress' : 'Locked') }}</span>
                                    </div>
                                @endforeach
                                <div class="journey-destination">
                                    <div class="goal-node"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.75 6.15 6.7.62-5.05 4.43 1.47 6.56L12 16.3l-5.87 3.46 1.47-6.56L2.55 8.77l6.7-.62L12 2Z"/></svg></div>
                                    <span class="goal-label">Destination</span>
                                    <span class="goal-title">{{ $pathwayDestination ?: $selectedPathway->name }}</span>
                                    <span class="goal-state">Final goal</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="empty-rec">This pathway does not have courses assigned yet. Check back after an administrator updates it.</p>
                    @endif
                </section>

                <section class="path-panels" aria-label="Pathway progress">
                    <article class="panel path-card ui-card-surface">
                        <header class="ui-card-header">
                            <h3 class="ui-section-title">Current goal</h3>
                        </header>
                        @if($currentGoal)
                            <p class="goal-title-large">{{ $currentGoal['title'] }}</p>
                            <p class="goal-description">{{ $currentGoal['description'] }}</p>
                            <div class="goal-progress-row"><div class="progress-track ui-progress-track"><div class="progress-fill ui-progress-fill" style="width:{{ min(100, max(0, $currentGoal['pct'])) }}%"></div></div><span class="progress-number">{{ $currentGoal['pct'] }}%</span></div>
                            <p class="goal-meta">{{ $currentGoal['skillsDone'] }}/{{ $currentGoal['skillsTotal'] }} skills already covered</p>
                            <a class="gold-button ui-button ui-button--primary continue-link" href="{{ $currentGoal['url'] }}">Continue</a>
                        @else
                            <p class="goal-description">You have completed all courses in this pathway. Choose another pathway or browse more courses.</p>
                        @endif
                    </article>

                    <article class="panel path-card ui-card-surface">
                        <header class="ui-card-header">
                            <h3 class="ui-section-title">Competencies</h3>
                        </header>
                        <p class="competency-label">Current competencies</p>
                        <div class="chip-list">
                            @forelse($currentCompetencies as $skill)<span class="chip is-current">{{ $skill }}</span>@empty<span class="chip-empty">No skills recorded yet</span>@endforelse
                        </div>
                        <p class="competency-label next">Next up</p>
                        <div class="chip-list">
                            @forelse($nextCompetencies as $skill)<span class="chip">{{ $skill }}</span>@empty<span class="chip-empty">No additional skills listed for the current course</span>@endforelse
                        </div>
                    </article>

                    <article class="panel path-card ui-card-surface">
                        <header class="ui-card-header">
                            <h3 class="ui-section-title">Pathway match</h3>
                        </header>
                        <div class="match-content">
                            @php($ringCircumference = 2 * pi() * 26)
                            <svg class="match-ring" viewBox="0 0 64 64" role="img" aria-label="{{ $pathwayMatch['percent'] }} percent pathway completion">
                                <circle class="match-ring-track" cx="32" cy="32" r="26" fill="none" stroke-width="6"/>
                                <circle class="match-ring-fill" cx="32" cy="32" r="26" fill="none" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ $ringCircumference }}" stroke-dashoffset="{{ $ringCircumference - ($ringCircumference * $pathwayMatch['percent'] / 100) }}" transform="rotate(-90 32 32)"/>
                                <text class="match-ring-label" x="32" y="37" text-anchor="middle" font-weight="700">{{ $pathwayMatch['percent'] }}%</text>
                            </svg>
                            <div><p class="match-number">{{ $pathwayMatch['percent'] }}%</p><p class="match-caption">{{ $pathwayMatch['complete'] }} of {{ $pathwayMatch['total'] }} goals complete</p></div>
                        </div>
                        <p class="match-next">Recommended next: <strong>{{ $pathwayMatch['next'] }}</strong></p>
                    </article>
                </section>
            @endunless

            <section class="panel recommendations ui-card-surface" aria-labelledby="recommendations-title">
                <div class="recommendations-head ui-card-header"><div class="ui-card-header__content"><h3 class="ui-section-title" id="recommendations-title">Recommended courses</h3><p>Ranked using the skills you want to learn and your course progress.</p></div><a class="outline-button ui-button ui-button--secondary" href="{{ route('courses.browse') }}">Browse all</a></div>
                <div class="recommendations-grid">
                    @forelse($recommendations as $rec)
                        <div class="rec-item"><div><strong>{{ $rec['title'] }}</strong><span>{{ $rec['completion'] }}% complete</span></div><a href="{{ route('courses.show', $rec['course_id']) }}">View course &gt;</a></div>
                    @empty
                        <p class="empty-rec">No matching published courses are available right now. Browse all courses to explore what is available.</p>
                    @endforelse
                </div>
            </section>
        </div>
<div class="modal-backdrop" id="pathway-picker" hidden>
    <section class="pathway-modal" role="dialog" aria-modal="true" aria-labelledby="pathway-picker-title" tabindex="-1">
        <header class="modal-head"><div><h2 id="pathway-picker-title">Select a pathway</h2><p>Choose a pathway created by your administrators. You can change it later.</p></div><button class="modal-close" type="button" data-close-picker aria-label="Close">&times;</button></header>
        <div class="modal-body">
            @forelse($availablePathways as $option)
                <form method="POST" action="{{ route('pathways.select') }}" class="pathway-choice">
                    @csrf
                    <input type="hidden" name="pathway_id" value="{{ $option->id }}">
                    <div class="choice-copy"><strong>{{ $option->name }}</strong><p>{{ $option->description ?: 'A guided sequence of courses to help you reach your next goal.' }}</p><span class="choice-meta">{{ $option->courses->count() }} {{ $option->courses->count() === 1 ? 'course' : 'courses' }}</span></div>
                    <button class="choice-submit ui-button ui-button--primary" type="submit">{{ (int) ($selectedPathway?->id ?? 0) === (int) $option->id ? 'Selected' : 'Choose pathway' }}</button>
                </form>
            @empty
                <div class="modal-empty">There are no active pathways yet. Check back after an administrator creates one.</div>
            @endforelse
        </div>
        <footer class="modal-footer"><button class="outline-button ui-button ui-button--secondary" type="button" data-close-picker>Close</button></footer>
    </section>
</div>

<script>
    (function(){
        var modal=document.getElementById('pathway-picker');
        if(!modal)return;
        var dialog=modal.querySelector('[role="dialog"]');
        var previousFocus=null;
        function openPicker(){previousFocus=document.activeElement;modal.hidden=false;document.body.style.overflow='hidden';dialog.focus();}
        function closePicker(){modal.hidden=true;document.body.style.overflow='';if(previousFocus&&previousFocus.focus)previousFocus.focus();}
        document.querySelectorAll('[data-open-pathway-picker]').forEach(function(button){button.addEventListener('click',openPicker);});
        modal.querySelectorAll('[data-close-picker]').forEach(function(button){button.addEventListener('click',closePicker);});
        modal.addEventListener('click',function(event){if(event.target===modal)closePicker();});
        document.addEventListener('keydown',function(event){if(event.key==='Escape'&&!modal.hidden)closePicker();});
    })();
</script>
</x-layout>
