@php($items = $items ?? [])
@php($breadcrumbClass = $breadcrumbClass ?? '')

<nav class="student-breadcrumbs {{ $breadcrumbClass }}" aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @if ($index > 0)
            <svg class="student-breadcrumbs__separator" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        @endif

        @if (!empty($item['url']) && $index < count($items) - 1)
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span aria-current="page">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>

<style>
    .student-breadcrumbs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0 0 18px;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
    }

    .student-breadcrumbs a {
        color: #13176b;
        text-decoration: none;
    }

    .student-breadcrumbs a:hover {
        color: #c4930f;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .student-breadcrumbs__separator {
        width: 15px;
        height: 15px;
        color: #9ca3af;
        flex: 0 0 auto;
    }

    .student-breadcrumbs span[aria-current="page"] {
        color: #6b7280;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .student-breadcrumbs--detail {
        padding: 22px 36px 16px;
    }

    .faculty-breadcrumbs {
        padding-bottom: 18px;
    }

    .faculty-breadcrumbs--standalone {
        padding-top: 0;
    }

    @media (max-width: 640px) {
        .student-breadcrumbs {
            padding-bottom: 14px;
            font-size: 12px;
        }

        .student-breadcrumbs--detail {
            padding: 16px 14px 12px;
        }
    }
</style>