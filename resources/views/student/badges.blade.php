{{--
    resources/views/my_badges.blade.php

    Expected data from the controller, e.g.:

    return view('student.badges', [
        'user'  => $user,                  // Auth::user() - needs ->name
        'stats' => [
            'active_courses' => $activeCoursesCount,
            'completed'      => $completedCount,
            'badges_earned'  => $badgesEarnedCount,
            'certificates'   => $certificatesCount,
        ],
        'badges' => $recentlyEarnedBadges,  // collection, most recent first
    ]);

    Each $badge is expected to expose:
        ->name, ->description, ->icon_url, ->earned_at (a Carbon instance, for ->diffForHumans())
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Badges | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --badge-bg:#f7ecc4;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo svg{width:30px;height:30px;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-icon-box svg{width:26px;height:26px;color:#fff;transition:color .15s ease;}
    .side-link.active .side-icon-box svg{color:var(--navy);}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0 0 28px;color:var(--muted);font-size:15px;}

    /* Stats (shared with dashboard) */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:40px;}
    /* Gradient stat cards (same design as course Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:38px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Recently Earned */
    .section-head{font-size:24px;margin:0 0 18px;color:var(--navy);}
    .badge-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:24px;}
    .badge-card{display:block;width:100%;min-height:340px;padding:0;border:0;border-radius:20px;background:transparent;color:inherit;text-align:inherit;perspective:1200px;cursor:pointer;}
    .badge-card:focus-visible{outline:3px solid #2fb3ab;outline-offset:4px;border-radius:22px;}
    .badge-card__inner{position:relative;display:block;min-height:340px;height:100%;transform-style:preserve-3d;transition:transform .65s cubic-bezier(.2,.75,.25,1);}
    .badge-card.is-flipped .badge-card__inner{transform:rotateY(180deg);}
    .badge-face{position:absolute;inset:0;display:block;min-height:340px;padding:20px;border-radius:20px;box-shadow:var(--shadow);backface-visibility:hidden;-webkit-backface-visibility:hidden;overflow:auto;}
    .badge-front{background:var(--badge-bg);text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .badge-back{background:#fff;border:1px solid #e7eaf2;transform:rotateY(180deg);text-align:left;}
    .badge-time{position:absolute;top:16px;right:18px;font-size:12px;color:var(--muted);}
    .badge-icon{width:90px;height:90px;border-radius:16px;background:var(--thumb);margin:28px auto 18px;background-size:cover;background-position:center;display:grid;place-items:center;overflow:hidden;}
    .badge-icon img{width:100%;height:100%;object-fit:cover;}
    .badge-title{display:block;margin:0 0 10px;color:var(--navy);font-size:18px;font-weight:800;line-height:1.3;}
    .badge-card .desc{display:block;color:var(--muted);font-size:13px;margin:0 0 18px;line-height:1.45;}
    .badge-earned-pill{display:inline-block;background:var(--gold);color:var(--navy);font-weight:800;font-size:13px;padding:8px 22px;border-radius:999px;}
    .badge-detail-title{display:block;font-size:18px;font-weight:800;color:var(--navy);margin:0 0 12px;padding-right:28px;}
    .badge-details{display:grid;gap:9px;margin:0;}
    .badge-detail{display:block;border-bottom:1px solid #edf0f5;padding-bottom:8px;}
    .badge-detail dt{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);font-weight:800;margin-bottom:3px;}
    .badge-detail dd{font-size:13px;color:#202642;margin:0;line-height:1.45;overflow-wrap:anywhere;}
    .badge-detail ul{margin:4px 0 0;padding-left:18px;}
    .badge-status{display:inline-block;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:800;background:#ecfdf3;color:#067647;}
    .badge-status.is-revoked{background:#fff1f2;color:#b42318;}
    .badge-flip-hint{display:block;font-size:12px;color:var(--muted);margin:14px 0 0;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:40px;text-align:center;color:var(--muted);grid-column:1/-1;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .badge-grid{grid-template-columns:repeat(2,1fr);}
    }
</style>
</head>
<body>

@include('components.student-navigation')

<div class="layout student-sidebar-layout">

    {{-- Sidebar --}}
    @include('components.student-sidebar')

    {{-- Main content --}}
    <main class="main">

        <div class="page-head">
            <h2>My Badge Collection</h2>
            <p>Badges earned through course completion</p>
        </div>

        {{-- Stat cards --}}
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg>
                    <span class="num">{{ $stats['active_courses'] ?? 0 }}</span>
                </div>
                <div class="label">Active Courses</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    <span class="num">{{ $stats['completed'] ?? 0 }}</span>
                </div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num">{{ $stats['badges_earned'] ?? 0 }}</span>
                </div>
                <div class="label">Badges Earned</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z" fill="#fff"/><path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num">{{ $stats['certificates'] ?? 0 }}</span>
                </div>
                <div class="label">Certificates</div>
            </div>
        </section>

        {{-- Recently Earned --}}
        <h3 class="section-head">Recently Earned</h3>
        <div class="badge-grid">
            @forelse ($badges as $badge)
                <div class="badge-card" role="button" tabindex="0" aria-expanded="false"
                        aria-label="Show details for {{ $badge->name }}">
                    <span class="badge-card__inner">
                        <span class="badge-face badge-front" data-badge-face="front">
                            <span class="badge-time">{{ $badge->earned_at?->diffForHumans() ?? '' }}</span>
                            <span class="badge-icon">
                                @if($badge->icon_url)
                                    <img src="{{ $badge->icon_url }}" alt="" loading="lazy">
                                @else
                                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3z"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="badge-title">{{ $badge->name }}</span>
                            @if($badge->description)
                                <span class="desc">{{ $badge->description }}</span>
                            @endif
                            <span class="badge-earned-pill">{{ strtolower($badge->status ?? 'active') === 'revoked' ? 'Revoked' : 'Earned' }}</span>
                            <span class="badge-flip-hint">Click or press Enter to view details</span>
                        </span>
                        <span class="badge-face badge-back" data-badge-face="back" aria-hidden="true">
                            <span class="badge-detail-title">{{ $badge->name }}</span>
                            <dl class="badge-details">
                                <div class="badge-detail">
                                    <dt>Date received</dt>
                                    <dd>{{ $badge->earned_at?->format('F j, Y') ?? 'Date unavailable' }}</dd>
                                </div>
                                @if($badge->badge_level)
                                    <div class="badge-detail"><dt>Badge level</dt><dd>{{ $badge->badge_level }}</dd></div>
                                @endif
                                @if($badge->pqf_level)
                                    <div class="badge-detail"><dt>PQF level</dt><dd>{{ $badge->pqf_level }}</dd></div>
                                @endif
                                @if($badge->issuing_institution)
                                    <div class="badge-detail"><dt>Issued by</dt><dd>{{ $badge->issuing_institution }}</dd></div>
                                @endif
                                @if($badge->pathway_name)
                                    <div class="badge-detail"><dt>Pathway</dt><dd>{{ $badge->pathway_name }}</dd></div>
                                @endif
                                @if($badge->course_names->isNotEmpty())
                                    <div class="badge-detail"><dt>Related course</dt><dd>{{ $badge->course_names->implode(', ') }}</dd></div>
                                @endif
                                @if(!empty($badge->competencies))
                                    <div class="badge-detail">
                                        <dt>Competencies</dt>
                                        <dd><ul>
                                            @foreach($badge->competencies as $competency)
                                                <li>{{ is_array($competency) ? ($competency['title'] ?? $competency['name'] ?? 'Competency') : $competency }}</li>
                                            @endforeach
                                        </ul></dd>
                                    </div>
                                @endif
                                @if(!empty($badge->learning_outcomes))
                                    <div class="badge-detail">
                                        <dt>Learning outcomes</dt>
                                        <dd><ul>
                                            @foreach($badge->learning_outcomes as $outcome)
                                                <li>{{ is_array($outcome) ? trim(($outcome['code'] ?? '').' '.($outcome['description'] ?? '')) : $outcome }}</li>
                                            @endforeach
                                        </ul></dd>
                                    </div>
                                @endif
                                @if($badge->credential_uid)
                                    <div class="badge-detail"><dt>Credential ID</dt><dd>{{ $badge->credential_uid }}</dd></div>
                                @endif
                                <div class="badge-detail">
                                    <dt>Status</dt>
                                    <dd><span class="badge-status {{ strtolower($badge->status ?? 'active') === 'revoked' ? 'is-revoked' : '' }}">{{ ucfirst($badge->status ?? 'active') }}</span></dd>
                                </div>
                                @if($badge->revoked_at)
                                    <div class="badge-detail"><dt>Revoked on</dt><dd>{{ $badge->revoked_at->format('F j, Y') }}</dd></div>
                                @endif
                                @if($badge->revocation_reason)
                                    <div class="badge-detail"><dt>Revocation reason</dt><dd>{{ $badge->revocation_reason }}</dd></div>
                                @endif
                            </dl>
                            <span class="badge-flip-hint">Click to return to badge</span>
                        </span>
                    </span>
                </div>
            @empty
                <div class="empty-state">
                    No badges earned yet. Complete a course to start collecting them.
                </div>
            @endforelse
        </div>

    </main>
</div>


{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    #back-to-top-btn svg{width:22px;height:22px;}
</style>
<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')

<script>
    document.querySelectorAll('.badge-card').forEach(function (card) {
        var front = card.querySelector('[data-badge-face="front"]');
        var back = card.querySelector('[data-badge-face="back"]');
        var title = card.querySelector('.badge-title').textContent.trim();

        function toggleBadge() {
            var flipped = card.classList.toggle('is-flipped');
            card.setAttribute('aria-expanded', flipped ? 'true' : 'false');
            card.setAttribute('aria-label', (flipped ? 'Hide details for ' : 'Show details for ') + title);
            front.setAttribute('aria-hidden', flipped ? 'true' : 'false');
            back.setAttribute('aria-hidden', flipped ? 'false' : 'true');
        }

        card.addEventListener('click', toggleBadge);
        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleBadge();
            }
        });
    });
</script>
</body>
</html>


