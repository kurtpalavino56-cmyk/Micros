{{--
    resources/views/my_badges.blade.php

    Expected data from the controller, e.g.:

    return view('student.badges', [
        'user'  => $user,                  // Auth::user() - needs ->name
        'badges' => $recentlyEarnedBadges,  // collection, most recent first
    ]);

    Each $badge is expected to expose:
        ->name, ->description, ->icon_url, ->earned_at (a Carbon instance, for ->diffForHumans())
--}}
<x-layout title="My Badges | Upskill" shell="student" page="badges">
        <div class="page-head page-header ui-page-header student-page-heading">
            <div class="ui-page-header__content">
                <h2 class="ui-page-title">My Badge Collection</h2>
                <p class="ui-page-subtitle">Badges earned through course completion</p>
            </div>
        </div>

        {{-- Recently Earned --}}
        <header class="ui-card-header badge-section-header">
            <h3 class="ui-section-title">Recently Earned</h3>
        </header>
        <div class="badge-grid">
            @forelse ($badges as $badge)
                <div class="badge-card" id="badge-{{ $badge->id }}" role="button" tabindex="0" aria-expanded="false"
                        aria-label="Show details for {{ $badge->name }}">
                    <span class="badge-card__inner">
                        <span class="badge-face badge-front ui-card-surface" data-badge-face="front">
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
                            <span class="badge-earned-pill ui-badge ui-badge--accent">{{ strtolower($badge->status ?? 'active') === 'revoked' ? 'Revoked' : 'Earned' }}</span>
                            <span class="badge-flip-hint">Click or press Enter to view details</span>
                        </span>
                        <span class="badge-face badge-back ui-card-surface" data-badge-face="back" aria-hidden="true">
                            <span class="badge-detail-title">{{ $badge->name }}</span>
                            <dl class="badge-details">
                                <div class="badge-detail">
                                    <dt>Date received</dt>
                                    <dd>{{ $badge->earned_at?->format('F j, Y') ?? 'Date unavailable' }}</dd>
                                </div>
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
                                    <dd><span class="badge-status ui-badge {{ strtolower($badge->status ?? 'active') === 'revoked' ? 'ui-badge--danger is-revoked' : 'ui-badge--success' }}">{{ ucfirst($badge->status ?? 'active') }}</span></dd>
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
                <div class="empty-state ui-empty-state">
                    No badges earned yet. Complete a course to start collecting them.
                </div>
            @endforelse
        </div>

{{-- â”€â”€ Back to top (appears on long pages) â”€â”€ --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>

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
</x-layout>
