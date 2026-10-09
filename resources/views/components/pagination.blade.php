<div>
    <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
</div>
@if($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $visiblePages = $lastPage <= 5
            ? range(1, $lastPage)
            : collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values()
                ->all();
    @endphp
    <nav class="shared-pagination" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="shared-pagination__link is-disabled" aria-disabled="true">« First</span>
            <span class="shared-pagination__link is-disabled" aria-disabled="true">‹ Back</span>
        @else
            <a class="shared-pagination__link" href="{{ $paginator->url(1) }}" aria-label="First page">« First</a>
            <a class="shared-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Back</a>
        @endif

        @php($previousVisiblePage = null)
        @foreach($visiblePages as $page)
            @if($previousVisiblePage !== null && $page > $previousVisiblePage + 1)
                <span class="shared-pagination__ellipsis" aria-hidden="true">…</span>
            @endif
            @if($page === $currentPage)
                <span class="shared-pagination__link is-active" aria-current="page">{{ $page }}</span>
            @else
                <a class="shared-pagination__link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endif
            @php($previousVisiblePage = $page)
        @endforeach

        @if($paginator->hasMorePages())
            <a class="shared-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>
            <a class="shared-pagination__link" href="{{ $paginator->url($lastPage) }}" aria-label="Last page">Last »</a>
        @else
            <span class="shared-pagination__link is-disabled" aria-disabled="true">Next ›</span>
            <span class="shared-pagination__link is-disabled" aria-disabled="true">Last »</span>
        @endif
    </nav>
    <style>
        .shared-pagination{display:flex;align-items:center;justify-content:center;gap:7px;padding:14px 8px;flex-wrap:wrap;}
        .shared-pagination__link{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 11px;border:1px solid #e5e7eb;border-radius:5px;background:#fff;color:#17212b;font:inherit;font-size:.78rem;text-decoration:none;white-space:nowrap;}
        .shared-pagination__link:hover{border-color:#c8ced8;background:#f8f9fb;}
        .shared-pagination__link.is-active{border-color:#111;background:#111;color:#fff;}
        .shared-pagination__link.is-disabled{color:#9aa1ad;background:#fff;}
        .shared-pagination__ellipsis{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:36px;color:#17212b;font-size:.8rem;}
        @media(max-width:560px){.shared-pagination{gap:4px;padding:12px 4px;}.shared-pagination__link{min-width:32px;height:34px;padding:0 8px;font-size:.74rem;}}
    </style>
@endif
