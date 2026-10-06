<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pathways | UpSkill PSU</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--navy:#0b1b45;--navy-deep:#071432;--gold:#f4c430;--gold-soft:#fff5cf;--gold-deep:#9b7410;--ink:#182442;--muted:#78849a;--line:#e2e8f0;--canvas:#f6f8fc;--white:#fff;--shadow:0 12px 30px rgba(11,27,69,.06)}
        *{box-sizing:border-box}html{min-height:100%;background:var(--canvas)}body{margin:0;background:var(--canvas);color:var(--ink);font:400 14px/1.55 Inter,Arial,sans-serif}a{color:inherit;text-decoration:none}button{font:inherit;cursor:pointer}
        .main{min-width:0;padding:30px 34px 64px}.pathways-page{width:min(100%,1440px);margin:0 auto}.page-header{margin:0 0 22px}.eyebrow{margin:0 0 5px;color:#a17c18;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.page-header h1{margin:0;color:var(--ink);font:700 clamp(26px,3vw,34px)/1.18 Poppins,Inter,sans-serif;letter-spacing:-.035em}.page-header p:last-child{margin:7px 0 0;color:#748097;font-size:14px}.notice{margin:0 0 18px;padding:12px 16px;border:1px solid #b7ebd0;border-radius:13px;background:#effcf5;color:#187448;font-size:13px;font-weight:650}
        .panel{min-width:0;border:1px solid rgba(226,232,240,.9);border-radius:18px;background:var(--white);box-shadow:0 1px 2px rgba(11,27,69,.04)}.empty-pathway{display:flex;align-items:center;justify-content:space-between;gap:26px;margin-bottom:22px;padding:25px 28px;background:linear-gradient(115deg,#fff 0%,#fff 70%,#fff9e3 100%)}.empty-copy{max-width:680px}.empty-copy h2{margin:0 0 7px;color:var(--ink);font:700 20px/1.3 Poppins,Inter,sans-serif}.empty-copy p{margin:0;color:#6c7890;line-height:1.65}.gold-button{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:11px 19px;border:0;border-radius:999px;background:var(--gold);color:var(--navy);font-size:13px;font-weight:800;white-space:nowrap;box-shadow:0 7px 17px rgba(244,196,48,.2);transition:transform .16s,background .16s,box-shadow .16s}.gold-button:hover{transform:translateY(-2px);background:#ffda54;box-shadow:0 10px 20px rgba(244,196,48,.3)}.outline-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:9px 15px;border:1px solid #dbe2ee;border-radius:999px;background:#fff;color:var(--navy);font-size:12px;font-weight:800;transition:background .16s,border-color .16s}.outline-button:hover{background:#f7f9ff;border-color:#bdc8dc}.selected-pathway-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 0 14px}.selected-pathway-head h2{margin:0;color:var(--ink);font:700 20px Poppins,Inter,sans-serif}.selected-pathway-head p{margin:4px 0 0;color:var(--muted);font-size:13px}.pathway-panel{padding:25px 26px 22px;margin-bottom:22px;overflow:hidden}.journey{position:relative}.journey-track{position:absolute;top:27px;right:5%;left:5%;height:2px;background:#e2e8f0}.journey-fill{position:absolute;top:27px;left:5%;height:2px;background:var(--gold)}.journey-goals{position:relative;display:flex;align-items:flex-start;gap:8px}.journey-goal{position:relative;display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;text-align:center}.goal-node{z-index:1;display:grid;width:56px;height:56px;flex:0 0 56px;place-items:center;border-radius:50%;background:var(--navy);color:#fff}.goal-node svg{width:23px;height:23px}.goal-node.is-current{width:64px;height:64px;flex-basis:64px;margin-top:-4px;background:var(--gold);color:var(--navy);box-shadow:0 0 0 5px rgba(244,196,48,.22);font:700 18px Poppins,Inter,sans-serif}.goal-node.is-locked{border:2px dashed #cbd5e1;background:#fff;color:#c2cad7}.goal-label{margin-top:12px;color:#98a2b3;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.goal-title{max-width:150px;margin:4px 0 0;color:var(--ink);font-size:12px;font-weight:750;line-height:1.4}.journey-goal.is-locked .goal-title{color:#98a2b3}.goal-state{margin-top:5px;color:#929db0;font-size:10px;font-weight:600}.journey-destination{display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;text-align:center}.journey-destination .goal-node{background:var(--navy);color:var(--gold)}
        .path-panels{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;align-items:stretch}.path-card{padding:22px 23px}.path-card h3{margin:-22px -23px 18px;padding:14px 23px;border-radius:17px 17px 0 0;background:#172f94;color:#fff;font:700 15px Poppins,Inter,sans-serif}.goal-title-large{margin:0;color:var(--ink);font:700 18px/1.35 Poppins,Inter,sans-serif}.goal-description{margin:7px 0 0;color:#758198;font-size:12px;line-height:1.65}.goal-progress-row{display:flex;align-items:center;gap:11px;margin-top:17px}.progress-track{height:8px;flex:1;overflow:hidden;border-radius:99px;background:#e7ebf2}.progress-fill{height:100%;border-radius:inherit;background:var(--gold);transition:width .2s}.progress-number{color:var(--ink);font:700 13px Poppins,Inter,sans-serif}.goal-meta{margin:7px 0 0;color:#9aa4b4;font-size:11px}.continue-link{margin-top:18px}.competency-label{margin:0;color:#98a2b3;font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.chip-list{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}.chip{display:inline-flex;align-items:center;min-height:27px;padding:5px 10px;border:1px solid #dfe5ee;border-radius:99px;color:var(--navy);font-size:11px;font-weight:650}.chip.is-current{border-color:#f2dc93;background:var(--gold-soft);color:#8a6710}.competency-label.next{margin-top:19px}.chip-empty{color:#98a2b3;font-size:12px}.match-content{display:flex;align-items:center;gap:15px}.match-ring{width:66px;height:66px;flex:0 0 66px}.match-number{margin:0;color:var(--ink);font:700 30px/1 Poppins,Inter,sans-serif}.match-caption{margin:5px 0 0;color:#78849a;font-size:12px}.match-next{margin:15px 0 0;padding:11px 12px;border-radius:11px;background:#f5f7fb;color:#465571;font-size:12px;line-height:1.5}.recommendations{margin-top:22px;padding:22px 24px}.recommendations-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:-22px -24px 12px;padding:14px 24px;border-radius:17px 17px 0 0;background:#172f94}.recommendations-head h3{margin:0;color:#fff;font:700 16px Poppins,Inter,sans-serif}.recommendations-head p{margin:3px 0 0;color:rgba(255,255,255,.78);font-size:11px}.recommendations-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 22px}.rec-item{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid #edf0f5}.rec-item strong{display:block;color:#25334f;font-size:12px}.rec-item span{color:#8994a8;font-size:11px;white-space:nowrap}.rec-item a{font-size:11px;font-weight:800;color:#1d3db8}.empty-rec{padding:14px 0;color:#8994a8;font-size:12px}
        .modal-backdrop{position:fixed;inset:0;z-index:11000;display:grid;place-items:center;padding:18px;background:rgba(5,13,40,.62);backdrop-filter:blur(4px)}.modal-backdrop[hidden]{display:none}.pathway-modal{width:min(100%,650px);max-height:min(88vh,760px);overflow:auto;border:1px solid #e5eaf2;border-radius:20px;background:#fff;box-shadow:0 24px 70px rgba(6,16,52,.28)}.modal-head{position:sticky;top:0;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:21px 23px;border-bottom:1px solid #edf0f5;background:#fff}.modal-head h2{margin:0;color:var(--ink);font:700 19px Poppins,Inter,sans-serif}.modal-head p{margin:5px 0 0;color:#7d889b;font-size:12px}.modal-close{width:35px;height:35px;flex:0 0 35px;border:0;border-radius:50%;background:#f1f4f8;color:#4a566d;font-size:22px;line-height:1}.modal-body{display:grid;gap:10px;padding:19px 23px 23px}.pathway-choice{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 16px;border:1px solid #e1e7f0;border-radius:14px;transition:border-color .16s,background .16s,transform .16s}.pathway-choice:hover{transform:translateY(-1px);border-color:#e8c54d;background:#fffdf4}.choice-copy{min-width:0}.choice-copy strong{display:block;color:var(--ink);font-size:13px}.choice-copy p{margin:4px 0 0;color:#7b879a;font-size:11px;line-height:1.55}.choice-meta{display:block;margin-top:6px;color:#9a7619;font-size:10px;font-weight:700}.choice-submit{flex:0 0 auto;border:0;border-radius:999px;background:var(--gold);padding:8px 13px;color:var(--navy);font-size:11px;font-weight:800}.modal-empty{padding:18px;border-radius:13px;background:#f6f8fb;color:#758198;font-size:13px;text-align:center}.modal-footer{display:flex;justify-content:flex-end;padding:0 23px 20px}.modal-close:focus-visible,.choice-submit:focus-visible,.gold-button:focus-visible,.outline-button:focus-visible{outline:3px solid rgba(34,65,203,.35);outline-offset:3px}
        @media(max-width:1050px){.main{padding:26px 22px 52px}.path-panels{grid-template-columns:repeat(2,minmax(0,1fr))}.path-card:last-child{grid-column:1/-1}}
        @media(max-width:760px){.main{padding:21px 15px 46px}.empty-pathway{align-items:flex-start;flex-direction:column;padding:21px}.pathway-panel{padding:20px 17px}.journey-track,.journey-fill{display:none}.journey-goals{flex-direction:column;gap:0}.journey-goal,.journey-destination{width:100%;min-height:78px;flex-direction:row;align-items:flex-start;gap:14px;text-align:left}.journey-goal:not(:last-child)::after{position:absolute;top:55px;left:27px;width:2px;height:calc(100% - 31px);background:#e2e8f0;content:""}.goal-node,.goal-node.is-current{width:54px;height:54px;flex-basis:54px;margin:0}.goal-label{display:none}.goal-title{max-width:none;margin:4px 0 0}.journey-copy{padding-top:2px}.journey-destination .goal-node{width:54px;height:54px;flex-basis:54px}.path-panels{grid-template-columns:1fr;gap:14px}.path-card:last-child{grid-column:auto}.recommendations{padding:19px}.recommendations-head{margin:-19px -19px 12px;padding:14px 19px}.recommendations-grid{grid-template-columns:1fr}.selected-pathway-head{align-items:flex-start;flex-direction:column}.pathway-choice{align-items:flex-start;flex-direction:column}.choice-submit{align-self:flex-end}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition-duration:.01ms!important}}
        body{font-size:15px}
        .page-header p:last-child,.selected-pathway-head p{font-size:15px}
        .notice,.gold-button,.empty-copy p,.selected-pathway-head p{font-size:14px}
        .outline-button{font-size:13px}
        .goal-title{font-size:13px}
        .goal-state,.goal-meta,.rec-item span{font-size:12px}
        .goal-description,.match-caption,.rec-item a,.recommendations-head p{font-size:13px}
        .competency-label,.goal-label{font-size:11px}
        .chip,.rec-item strong{font-size:13px}
        .chip-empty{font-size:13px}
    </style>
</head>
<body>
@include('components.student-navigation')
<div class="layout student-sidebar-layout">
    @include('components.student-sidebar')
    <main class="main">
        <div class="pathways-page">
            <header class="page-header student-page-heading">
                <h1>My Pathways</h1>
                <p>{{ $selectedPathway ? 'Your learning journey to '.($pathwayDestination ?: $selectedPathway->name).'.' : 'Choose a learning pathway when you are ready.' }}</p>
            </header>

            @if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif

            @unless($selectedPathway)
                <section class="panel empty-pathway">
                    <div class="empty-copy">
                        <h2>Your pathway journey starts when you choose one</h2>
                        <p>You have not selected a pathway yet. Browse the pathways available whenever you are ready. Meanwhile, these course suggestions use the skills you selected in your profile.</p>
                    </div>
                    <button type="button" class="gold-button" data-open-pathway-picker>Select a pathway <span aria-hidden="true">&gt;</span></button>
                </section>
            @else
                <div class="selected-pathway-head">
                    <div><p class="eyebrow">Your selected pathway</p><h2>{{ $selectedPathway->name }}</h2><p>{{ $selectedPathway->description ?: 'Follow the courses in this pathway at your own pace.' }}</p></div>
                    <button type="button" class="outline-button" data-open-pathway-picker>Change pathway</button>
                </div>

                <section class="panel pathway-panel" aria-label="Pathway journey timeline">
                    @if(count($pathwaySteps))
                        @php
                            $goalCount = count($pathwaySteps);
                            $currentIndex = collect($pathwaySteps)->search(fn ($step) => $step['status'] === 'current');
                            $lineInset = $goalCount > 0 ? 50 / ($goalCount + 1) : 0;
                            $fillWidth = $currentIndex === false ? 0 : ($currentIndex / ($goalCount + 1)) * 100;
                        @endphp
                        <div class="journey">
                            <div class="journey-track" style="left:{{ $lineInset }}%;right:{{ $lineInset }}%"></div>
                            @if($currentIndex !== false)<div class="journey-fill" style="left:{{ $lineInset }}%;width:{{ $fillWidth }}%"></div>@endif
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
                    <article class="panel path-card">
                        <h3>Current goal</h3>
                        @if($currentGoal)
                            <p class="goal-title-large">{{ $currentGoal['title'] }}</p>
                            <p class="goal-description">{{ $currentGoal['description'] }}</p>
                            <div class="goal-progress-row"><div class="progress-track"><div class="progress-fill" style="width:{{ min(100, max(0, $currentGoal['pct'])) }}%"></div></div><span class="progress-number">{{ $currentGoal['pct'] }}%</span></div>
                            <p class="goal-meta">{{ $currentGoal['skillsDone'] }}/{{ $currentGoal['skillsTotal'] }} skills already covered</p>
                            <a class="gold-button continue-link" href="{{ $currentGoal['url'] }}">Continue</a>
                        @else
                            <p class="goal-description">You have completed all courses in this pathway. Choose another pathway or browse more courses.</p>
                        @endif
                    </article>

                    <article class="panel path-card">
                        <h3>Competencies</h3>
                        <p class="competency-label">Current competencies</p>
                        <div class="chip-list">
                            @forelse($currentCompetencies as $skill)<span class="chip is-current">{{ $skill }}</span>@empty<span class="chip-empty">No skills recorded yet</span>@endforelse
                        </div>
                        <p class="competency-label next">Next up</p>
                        <div class="chip-list">
                            @forelse($nextCompetencies as $skill)<span class="chip">{{ $skill }}</span>@empty<span class="chip-empty">No additional skills listed for the current course</span>@endforelse
                        </div>
                    </article>

                    <article class="panel path-card">
                        <h3>Pathway match</h3>
                        <div class="match-content">
                            @php($ringCircumference = 2 * pi() * 26)
                            <svg class="match-ring" viewBox="0 0 64 64" role="img" aria-label="{{ $pathwayMatch['percent'] }} percent pathway completion">
                                <circle cx="32" cy="32" r="26" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                                <circle cx="32" cy="32" r="26" fill="none" stroke="#F4C430" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ $ringCircumference }}" stroke-dashoffset="{{ $ringCircumference - ($ringCircumference * $pathwayMatch['percent'] / 100) }}" transform="rotate(-90 32 32)"/>
                                <text x="32" y="37" text-anchor="middle" fill="#182442" font-size="13" font-weight="700">{{ $pathwayMatch['percent'] }}%</text>
                            </svg>
                            <div><p class="match-number">{{ $pathwayMatch['percent'] }}%</p><p class="match-caption">{{ $pathwayMatch['complete'] }} of {{ $pathwayMatch['total'] }} goals complete</p></div>
                        </div>
                        <p class="match-next">Recommended next: <strong>{{ $pathwayMatch['next'] }}</strong></p>
                    </article>
                </section>
            @endunless

            <section class="panel recommendations" aria-labelledby="recommendations-title">
                <div class="recommendations-head"><div><h3 id="recommendations-title">Recommended courses</h3><p>Ranked using the skills you want to learn and your course progress.</p></div><a class="outline-button" href="{{ route('courses.browse') }}">Browse all</a></div>
                <div class="recommendations-grid">
                    @forelse($recommendations as $rec)
                        <div class="rec-item"><div><strong>{{ $rec['title'] }}</strong><span>{{ $rec['completion'] }}% complete</span></div><a href="{{ route('courses.show', $rec['course_id']) }}">View course &gt;</a></div>
                    @empty
                        <p class="empty-rec">No matching published courses are available right now. Browse all courses to explore what is available.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
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
                    <button class="choice-submit" type="submit">{{ (int) ($selectedPathway?->id ?? 0) === (int) $option->id ? 'Selected' : 'Choose pathway' }}</button>
                </form>
            @empty
                <div class="modal-empty">There are no active pathways yet. Check back after an administrator creates one.</div>
            @endforelse
        </div>
        <footer class="modal-footer"><button class="outline-button" type="button" data-close-picker>Close</button></footer>
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
@include('components.responsive')
</body>
</html>
