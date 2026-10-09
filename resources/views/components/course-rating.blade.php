@php
    $reviewCount = (int) ($count ?? 0);
    $average = (float) ($rating ?? 0);
    $minimumReviews = \App\Support\CourseEvaluationTemplate::MINIMUM_REVIEWS_FOR_RATING;
@endphp
<span class="course-rating" style="display:inline-flex;align-items:center;gap:4px;color:#667085;font-size:12px;font-weight:600;" aria-label="{{ $reviewCount >= $minimumReviews ? number_format($average, 1).' out of 5 from '.$reviewCount.' reviews' : 'New course' }}">
    @if ($reviewCount >= $minimumReviews)
        <span aria-hidden="true" style="color:#d99a00;white-space:nowrap;">
            @for($star = 1; $star <= 5; $star++){{ $star <= round($average) ? '★' : '☆' }}@endfor
        </span>
        {{ number_format($average, 1) }} ({{ $reviewCount }})
    @else
        New
    @endif
</span>
