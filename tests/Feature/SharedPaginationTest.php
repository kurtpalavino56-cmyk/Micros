<?php

use Illuminate\Pagination\LengthAwarePaginator;

test('shared pagination renders navigation and page summaries consistently', function () {
    $paginator = new LengthAwarePaginator(
        items: range(6, 10),
        total: 12,
        perPage: 5,
        currentPage: 2,
        options: ['path' => '/items'],
    );

    $html = view('components.pagination', [
        'paginator' => $paginator,
        'summary' => '6–10 of 12 · Page 2 of 3',
        'ariaLabel' => 'Test pages',
    ])->render();

    expect($html)
        ->toContain('class="shared-pagination"')
        ->toContain('aria-label="Test pages"')
        ->toContain('6–10 of 12 · Page 2 of 3')
        ->toContain('aria-current="page">2</span>')
        ->toContain('href="/items?page=1"')
        ->toContain('href="/items?page=3"');
});
