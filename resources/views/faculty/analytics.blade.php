<x-faculty.layout title="Analytics | Upskill" data-faculty-page="faculty-analytics" class="analytics-shell">
    <x-slot:head><link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

@include('components.analytics-design')
    </x-slot:head>

<main class="main">
        <div class="faculty-analytics-page">

        {{-- Toolbar --}}
        <header class="page-head faculty-page-heading">
            <div>
                <h2 class="faculty-page-title">Analytics</h2>
            </div>
            <div class="head-actions">
                <form class="course-filter" action="{{ route('faculty.analytics') }}" method="GET">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" onchange="this.form.submit()">
                        <option value="">All my courses</option>
                        @foreach ($facultyCourses as $facultyCourse)
                            <option value="{{ $facultyCourse->id }}" @selected($selectedCourseId === $facultyCourse->id)>{{ $facultyCourse->title }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Apply</button></noscript>
                </form>
                <a href="{{ route('faculty.analytics.report', request()->only(['course_id', 'page'])) }}" class="download-report-btn" title="Download the full analytics report as a PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M4 21h16"/></svg>
                    Download report
                </a>
            </div>
        </header>

        {{-- KPIs with derived ratios --}}
        @php
            $tl = (int) ($stats['total_learners'] ?? 0);
            $certs = (int) ($stats['certificates'] ?? 0);
            $lessons = (int) ($stats['lessons_done'] ?? 0);
        @endphp
        <section class="stat-cards" aria-label="Key numbers">
            <div class="card kpi live"><div class="label"><span class="dot" aria-hidden="true"></span>Live learners</div><span class="num" id="faculty-live-active-users">{{ $onlineNow ?? 0 }}</span><div class="note">{{ $tl > 0 ? number_format(($onlineNow ?? 0) / $tl * 100, 1) : '0' }}% of <b>{{ $tl }}</b> learners online</div></div>
            <div class="card kpi a"><div class="label">Total learners</div><span class="num">{{ $tl }}</span><div class="note">Across your selected courses</div></div>
            <div class="card kpi b"><div class="label">Certificates issued</div><span class="num">{{ $certs }}</span><div class="note"><b>{{ $tl > 0 ? number_format($certs / $tl * 100, 1) : '0' }}</b> per 100 learners</div></div>
            <div class="card kpi c"><div class="label">Lessons completed</div><span class="num">{{ $lessons }}</span><div class="note"><b>{{ $tl > 0 ? number_format($lessons / $tl, 1) : '0' }}</b> per learner</div></div>
        </section>

        {{-- Growth + learner demographics --}}
        <div class="split">
            <section class="card">
                <div class="card-head"><h3>Enrolled learner growth</h3><span class="sub">Last 12 months</span></div>
                <div class="card-body">
                    @php
                        $points = collect($academyStats ?? []);
                        $W = 720; $H = 260; $padL = 40; $padR = 20; $padT = 20; $padB = 44;
                        $plotW = $W - $padL - $padR; $plotH = $H - $padT - $padB;
                        $rawMax = (int) ($points->max('value') ?? 0);
                        $step = max(10, (int) (ceil($rawMax / 4 / 10) * 10));
                        $maxV = max($step * 3, (int) (ceil($rawMax / $step) * $step));
                        $n = max($points->count(), 1);
                        $coords = $points->values()->map(function ($p, $i) use ($padL, $padT, $plotW, $plotH, $maxV, $n) {
                            $val = is_array($p) ? ($p['value'] ?? 0) : ($p->value ?? 0);
                            $label = is_array($p) ? ($p['label'] ?? '') : ($p->label ?? '');
                            return ['x' => $padL + ($plotW * ($i + 0.5) / $n), 'y' => $padT + $plotH - ($plotH * $val / $maxV), 'label' => str_replace("\n", ' ', $label), 'raw' => $label, 'value' => $val];
                        });
                        $peak = $coords->sortByDesc('value')->first();
                        $vals = $coords->pluck('value');
                        $latest = (int) ($vals->last() ?? 0);
                        $prev = $vals->count() > 1 ? (int) $vals->slice(-2, 1)->first() : $latest;
                        $delta = $latest - $prev;
                        $avgV = $vals->isNotEmpty() ? round($vals->avg(), 1) : 0;
                    @endphp
                    @if($points->isNotEmpty())
                        <div class="mini-stats">
                            <div><span>Latest</span><b class="tnum">{{ $latest }}</b></div>
                            <div><span>Change vs. previous</span><b class="tnum {{ $delta > 0 ? 'up' : ($delta < 0 ? 'down' : '') }}">{{ $delta > 0 ? '+' : '' }}{{ $delta }}{{ $prev > 0 ? ' ('.($delta > 0 ? '+' : '').number_format($delta / $prev * 100, 0).'%)' : '' }}</b></div>
                            <div><span>Peak</span><b class="tnum">{{ $peak['value'] }}</b></div>
                            <div><span>12-month average</span><b class="tnum">{{ $avgV }}</b></div>
                        </div>
                        <div class="chart-wrap" id="growth-chart">
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Enrolled learners per month, last 12 months">
                            @for($v = 0; $v <= $maxV; $v += $step)
                                @php $gy = $padT + $plotH - ($plotH * $v / $maxV); @endphp
                                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#e6e8f0"/>
                                <text x="{{ $padL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end" font-size="11" fill="#667085">{{ $v }}</text>
                            @endfor
                            @foreach($coords as $c)
                                @php
                                    $isPeak = $c['x'] === $peak['x'] && $c['value'] === $peak['value'];
                                    $barWidth = $plotW / $n * 0.58;
                                    $barHeight = $padT + $plotH - $c['y'];
                                    $barColor = $isPeak ? '#dba617' : '#13176b';
                                @endphp
                                <text x="{{ round($c['x'],1) }}" y="{{ $padT + $plotH + 20 }}" text-anchor="middle" font-size="10" fill="#667085">
                                    @foreach(explode("\n", $c['raw']) as $li => $lineTxt)<tspan x="{{ round($c['x'],1) }}" dy="{{ $li === 0 ? 0 : 12 }}">{{ $lineTxt }}</tspan>@endforeach
                                </text>
                                <rect x="{{ round($c['x'] - $barWidth / 2, 1) }}" y="{{ round($c['y'], 1) }}" width="{{ round($barWidth, 1) }}" height="{{ round($barHeight, 1) }}" rx="4" fill="{{ $barColor }}"/>
                                <rect class="hit" x="{{ round($c['x'] - ($plotW / $n / 2), 1) }}" y="{{ $padT }}" width="{{ round($plotW / $n, 1) }}" height="{{ $plotH }}" tabindex="0" data-label="{{ $c['label'] }}" data-value="{{ $c['value'] }}" data-cx="{{ round($c['x'] / $W * 100, 2) }}" data-cy="{{ round(($c['y'] + $padT + $plotH) / 2 / $H * 100, 2) }}" aria-label="{{ $c['label'] }}: {{ $c['value'] }} learners"/>
                            @endforeach
                            @if($avgV > 0)
                                @php $ay = $padT + $plotH - ($plotH * $avgV / $maxV); @endphp
                                <line x1="{{ $padL }}" y1="{{ $ay }}" x2="{{ $padL + $plotW }}" y2="{{ $ay }}" stroke="#dba617" stroke-width="1.5" stroke-dasharray="5 4"/>
                                <rect x="{{ $padL + $plotW - 44 }}" y="{{ $ay - 16 }}" width="44" height="16" rx="4" fill="#fff7db" fill-opacity=".92"/>
                                <text x="{{ $padL + $plotW }}" y="{{ $ay - 5 }}" text-anchor="end" font-size="10" font-weight="600" fill="#b8860b">avg {{ $avgV }}</text>
                            @endif
                        </svg>
                        <div class="tip" id="chart-tip" role="status"></div>
                        </div>
                    @else
                        <div class="empty-state">No enrollment history yet. Growth appears here once learners join your courses.</div>
                    @endif
                </div>
            </section>

            @php
                $demographicGroups = [
                    ['title' => 'Age distribution', 'values' => $demographics['age']],
                    ['title' => 'Gender distribution', 'values' => $demographics['gender']],
                ];
            @endphp
            <section class="card" aria-label="Learner demographics">
                <div class="card-head">
                    <h3>Learner demographics</h3>
                    <span class="sub">{{ number_format($demographics['total']) }} unique learners</span>
                </div>
                <div class="card-body">
                    @if($demographics['total'] > 0)
                        <div class="demographics-groups">
                            @foreach($demographicGroups as $group)
                                <section class="demographics-group" aria-label="{{ $group['title'] }}">
                                    <h4>{{ $group['title'] }}</h4>
                                    <div class="demographics-bars">
                                        @foreach($group['values'] as $label => $count)
                                            @php $percentage = (int) round($count / $demographics['total'] * 100); @endphp
                                            <div class="demographic-row">
                                                <span class="demographic-label">{{ $label }}</span>
                                                <div class="demographic-track" role="img" aria-label="{{ $label }}: {{ number_format($count) }} learners, {{ $percentage }}%">
                                                    <div class="demographic-fill" style="width:{{ $percentage }}%"></div>
                                                </div>
                                                <span class="demographic-value">{{ number_format($count) }} ({{ $percentage }}%)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @else
                        <div class="demographics-empty">No enrolled learner demographics are available for the selected courses.</div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Learner success --}}
        @php
            $success = collect($learnerSuccess ?? []);
            $totalCount = (int) $success->sum('count');
            $total = max($totalCount, 1);
            $colors = ['#13176b', '#dba617', '#7fe9e3'];
            $segments = $success->values()->map(fn ($s, $i) => ['color' => $colors[$i % count($colors)], 'count' => $s['count'] ?? 0, 'label' => $s['label'] ?? '', 'pct' => round(($s['count'] ?? 0) / $total * 100, 1)]);
        @endphp
        <section class="card">
            <div class="card-head"><h3>Learner success</h3><span class="sub">All time · {{ $totalCount }} learners</span></div>
            <div class="card-body">
                @if($success->isNotEmpty() && $totalCount > 0)
                    <div class="success-bar" role="img" aria-label="Learner success breakdown">
                        @foreach($segments as $seg)@if($seg['count'] > 0)<span style="width:{{ $seg['pct'] }}%;background:{{ $seg['color'] }};" title="{{ $seg['label'] }}: {{ $seg['count'] }} ({{ $seg['pct'] }}%)"></span>@endif @endforeach
                    </div>
                    <div class="success-legend">
                        @foreach($segments as $seg)
                            <div class="s-item"><span class="s-swatch" style="background:{{ $seg['color'] }};"></span>
                                <div><div class="s-num tnum">{{ $seg['count'] }}<small>{{ $seg['pct'] }}%</small></div><div class="s-label">{{ $seg['label'] }}</div></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">No learner results yet. Completed courses and passed quizzes show up here.</div>
                @endif
            </div>
        </section>

        {{-- Course outcomes: sortable, searchable, with data bars --}}
        @php
            $rows = method_exists($courseAnalytics, 'getCollection') ? $courseAnalytics->getCollection() : collect($courseAnalytics);
            $avgOf = function ($key) use ($rows) { $v = $rows->pluck($key)->filter(fn ($x) => $x !== null); return $v->isEmpty() ? null : round($v->avg(), 1); };
            $minReviews = \App\Support\CourseEvaluationTemplate::MINIMUM_REVIEWS_FOR_RATING;
        @endphp
        <section class="card">
            <div class="card-head">
                <h3>Course outcomes</h3>
                <div class="toolbar"><input type="search" id="course-search" placeholder="Search courses" aria-label="Search courses"><span class="sub">Your courses</span></div>
            </div>
            <div class="table-scroll paginated-table-scroll">
                <table class="course-outcomes" id="outcomes-table">
                    <thead><tr>
                        <th class="text" data-type="text"><button type="button">Course</button></th>
                        <th data-type="num"><button type="button">Enrolled</button></th>
                        <th data-type="num"><button type="button">Completed</button></th>
                        <th data-type="num"><button type="button">Completion</button></th>
                        <th data-type="num"><button type="button">Avg. Quiz</button></th>
                        <th data-type="num"><button type="button" title="Share of learners who passed the quiz">Quiz Pass Rate (learners)</button></th>
                        <th data-type="num"><button type="button">Avg. Rating</button></th>
                        <th data-type="num"><button type="button">Reviews</button></th>
                        <th data-type="num"><button type="button" title="Reviews waiting for your grade">Reviews to Grade</button></th>
                    </tr></thead>
                    <tbody>
                        @forelse($courseAnalytics as $course)
                            @php $rated = $course->review_count >= $minReviews; @endphp
                            <tr>
                                <td data-v="{{ strtolower($course->title) }}">{{ $course->title }}</td>
                                <td data-v="{{ $course->enrolled }}">{{ $course->enrolled }}</td>
                                <td data-v="{{ $course->completed }}">{{ $course->completed }}</td>
                                <td data-v="{{ $course->completion_rate ?? -1 }}">
                                    @if($course->completion_rate === null)<span class="na">—</span>@else<div class="cell"><span>{{ $course->completion_rate }}%</span><i style="--w:{{ min(100, max(0, $course->completion_rate)) }}%"></i></div>@endif
                                </td>
                                <td data-v="{{ $course->average_score ?? -1 }}">
                                    @if($course->average_score === null)<span class="na">—</span>@else<div class="cell"><span>{{ $course->average_score }}%</span><i style="--w:{{ min(100, max(0, $course->average_score)) }}%;--c:#7b80d6"></i></div>@endif
                                </td>
                                <td data-v="{{ $course->pass_rate ?? -1 }}">
                                    @if($course->pass_rate === null)<span class="na">—</span>@else<div class="cell"><span>{{ $course->pass_rate }}%</span><i style="--w:{{ min(100, max(0, $course->pass_rate)) }}%;--c:var(--teal)"></i></div>@endif
                                </td>
                                <td data-v="{{ $rated ? $course->rating_average : -1 }}">@if($rated)<span class="star">★</span> {{ number_format($course->rating_average, 1) }}@else<span class="pill new">New</span>@endif</td>
                                <td data-v="{{ $course->review_count }}">{{ $course->review_count }}</td>
                                <td data-v="{{ $course->pending_reviews }}">@if($course->pending_reviews > 0)<span class="pill warn">{{ $course->pending_reviews }}</span>@else<span class="pill ok">0</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="empty-state">Create a course to see learner outcomes here.</div></td></tr>
                        @endforelse
                    </tbody>
                    @if($rows->count() > 1)
                    <tfoot><tr>
                        <td>Average on this page</td>
                        <td>{{ $avgOf('enrolled') ?? '—' }}</td><td>{{ $avgOf('completed') ?? '—' }}</td>
                        <td>{{ $avgOf('completion_rate') === null ? '—' : $avgOf('completion_rate').'%' }}</td>
                        <td>{{ $avgOf('average_score') === null ? '—' : $avgOf('average_score').'%' }}</td>
                        <td>{{ $avgOf('pass_rate') === null ? '—' : $avgOf('pass_rate').'%' }}</td>
                        <td colspan="3"></td>
                    </tr></tfoot>
                    @endif
                </table>
            </div>
            @include('components.pagination', ['paginator' => $courseAnalytics])
        </section>

        {{-- Comments --}}
        <section class="card">
            <div class="card-head"><h3>Recent course evaluation comments</h3><span class="sub">Latest 5</span></div>
            <div class="card-body">
                @if($recentCourseComments->isNotEmpty())
                    <div class="comments">
                        @foreach($recentCourseComments as $review)
                            <article class="comment">
                                <div class="comment-top"><strong>{{ $review->course?->title ?? 'Course' }}</strong><span class="star" aria-label="{{ $review->rating }} out of 5">★ {{ $review->rating }}/5</span></div>
                                <p>{{ $review->comment }}</p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">No written comments yet. Learner feedback appears here after they evaluate a course.</div>
                @endif
            </div>
        </section>

        </div>
    </main>

    <x-slot:scripts>
<script>
    // ---- Live learner count ----
    const liveUrl = '{{ route('faculty.analytics.live', $selectedCourseId ? ['course_id' => $selectedCourseId] : []) }}';
    let liveTimer = null;
    function refreshFacultyAnalytics() {
        fetch(liveUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => { if (!r.ok) throw new Error('Unable to refresh live learner count.'); return r.json(); })
            .then(data => {
                const active = document.getElementById('faculty-live-active-users');
                if (active) active.textContent = data.stats.active_users;
            })
            .catch(error => console.error(error));
    }
    function startLive() { refreshFacultyAnalytics(); liveTimer = setInterval(refreshFacultyAnalytics, 15000); }
    document.addEventListener('visibilitychange', () => { if (document.hidden) { clearInterval(liveTimer); liveTimer = null; } else if (!liveTimer) startLive(); });
    startLive();

    // ---- Chart tooltip ----
    (function () {
        const wrap = document.getElementById('growth-chart'), tip = document.getElementById('chart-tip');
        if (!wrap || !tip) return;
        const svg = wrap.querySelector('svg');
        function show(r) {
            const s = svg.getBoundingClientRect(), w = wrap.getBoundingClientRect();
            tip.innerHTML = '<b>' + r.dataset.value + ' learners</b>' + r.dataset.label;
            tip.style.left = (s.left - w.left + wrap.scrollLeft + s.width * r.dataset.cx / 100) + 'px';
            tip.style.top = (s.top - w.top + s.height * r.dataset.cy / 100) + 'px';
            tip.classList.add('show');
        }
        wrap.querySelectorAll('.hit').forEach(r => {
            ['mouseenter', 'focus'].forEach(e => r.addEventListener(e, () => show(r)));
            ['mouseleave', 'blur'].forEach(e => r.addEventListener(e, () => tip.classList.remove('show')));
        });
    })();

    // ---- Sortable + searchable course table (works on the current page) ----
    (function () {
        const table = document.getElementById('outcomes-table'); if (!table) return;
        const body = table.tBodies[0], heads = table.querySelectorAll('thead th');
        heads.forEach((th, i) => th.querySelector('button').addEventListener('click', () => {
            const asc = th.getAttribute('aria-sort') !== 'ascending', num = th.dataset.type === 'num';
            heads.forEach(h => h.removeAttribute('aria-sort'));
            th.setAttribute('aria-sort', asc ? 'ascending' : 'descending');
            const rows = Array.from(body.rows).filter(r => r.cells.length > 1);
            rows.sort((a, b) => {
                const x = a.cells[i].dataset.v, y = b.cells[i].dataset.v;
                const c = num ? parseFloat(x) - parseFloat(y) : x.localeCompare(y);
                return asc ? c : -c;
            });
            rows.forEach(r => body.appendChild(r));
        }));
        const search = document.getElementById('course-search');
        if (search) search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            Array.from(body.rows).forEach(r => { if (r.cells.length > 1) r.style.display = r.cells[0].textContent.toLowerCase().includes(q) ? '' : 'none'; });
        });
    })();
</script>

<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

<script>
    (function () {
        var b = document.getElementById('back-to-top-btn');
        function t() { b.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true }); t();
    })();
</script>

@include('components.responsive')
    </x-slot:scripts>
</x-faculty.layout>
