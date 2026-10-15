<?php

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('homepage renders full announcement content for both short and long announcements', function () {
    Announcement::create([
        'title' => 'Short announcement',
        'body' => '<p>A short update.</p>',
        'audience' => ['public'],
        'is_published' => true,
    ]);

    Announcement::create([
        'title' => 'Long announcement',
        'body' => '<p>'.str_repeat('A', 161).'</p>',
        'audience' => ['public'],
        'is_published' => true,
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Short announcement')
        ->assertSee('Long announcement')
        ->assertSee('class="ann-card__modal-content" hidden', false)
        ->assertSee('<p>A short update.</p>', false)
        ->assertSee('<p>'.str_repeat('A', 161).'</p>', false);

    expect(substr_count($response->getContent(), 'class="ann-card__modal-content" hidden'))->toBe(2);
});
