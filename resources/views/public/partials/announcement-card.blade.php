@php($isFeatured = $featured ?? false)
<article
    class="ann-card {{ $isFeatured ? 'ann-card--featured' : '' }}"
    data-announcement-card
    role="button"
    tabindex="0"
    aria-haspopup="dialog"
    aria-label="View announcement: {{ $ann['title'] }}"
>
    @if ($isFeatured && $ann['image'])
        <img class="ann-card__image" src="{{ $ann['image'] }}" alt="" loading="lazy">
    @endif
    @if ($ann['is_pinned'])
        <span class="ann-card__pin" aria-label="Pinned announcement">
            <svg viewBox="0 0 98.106 98.105" fill="#ff0000" aria-hidden="true">
                <path d="M49.04,0C33.03,0,20.03,13.001,20.03,29.038c0,10.452,5.537,19.585,13.82,24.699c-2.734,9.008-14.988,50.194-8.729,43.672 c5.566-5.801,17.43-32.434,20.545-39.547c1.111,0.129,2.232,0.215,3.373,0.215c16.037,0,29.037-13.001,29.037-29.039 C78.076,13.001,65.077,0,49.04,0z M49.47,10.867c-8.395,0.36-15.506,4.445-18.844,10.097c-0.008-0.184-0.035-0.363-0.035-0.548 c0-8.152,8.443-14.76,18.834-14.76c9.3,0,17.007,5.271,18.557,12.203C63.932,13.352,57.102,10.536,49.47,10.867z"/>
            </svg>
            <span class="ann-card__sr-only">Pinned</span>
        </span>
    @endif
    <div class="ann-card__body">
        <div class="ann-card__meta">
            <time class="ann-card__date">{{ $ann['date'] }}</time>
        </div>
        <h3 class="ann-card__title">{{ $ann['title'] }}</h3>
        <p class="ann-card__desc">{{ $ann['desc'] }}</p>
        <div class="ann-card__modal-content" hidden>{!! $ann['full_body'] !!}</div>
    </div>
    <span class="ann-card__open-hint">
        View
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
</article>
