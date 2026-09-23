{{--
    Course Card Component
    Usage: @include('components.course-card', ['course' => $course])
--}}

@php
    $title       = $course['title']       ?? 'Course Title';
    $description = $course['description'] ?? '';
    $professor   = $course['professor']   ?? '';
    $hours       = $course['hours']       ?? 0;
    $rating      = $course['rating']      ?? 0;
    $category    = $course['category']    ?? 'Web Development';
    $level       = $course['level']       ?? 'Intermediate';
    $image       = $course['image']       ?? null;
    $slug        = $course['slug']        ?? '#';
@endphp

<article class="course-card">
    <a href="{{ $slug }}" class="course-card__thumb">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
        @else
            <div class="course-card__thumb-placeholder"></div>
        @endif
    </a>

    <div class="course-card__body">
        <div class="course-card__tags">
            <span class="tag tag-teal">{{ $category }}</span>
            <span class="tag tag-gold">{{ $level }}</span>
        </div>

        <h3 class="course-card__title">
            <a href="{{ $slug }}">{{ $title }}</a>
        </h3>

        <p class="course-card__desc">{{ $description }}</p>

        <div class="course-card__meta">
            <span class="course-card__prof">{{ $professor }}</span>
            <span class="course-card__hours">{{ $hours }}h</span>
            <span class="course-card__rating">
                @for($i = 1; $i <= 5; $i++)
                    {{ $i <= $rating ? '★' : '☆' }}
                @endfor
            </span>
        </div>
    </div>
</article>