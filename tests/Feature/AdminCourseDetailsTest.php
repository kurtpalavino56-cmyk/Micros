<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin course details use the short description and avoid repeating level and duration in the hero', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    $course = Course::create([
        'title' => 'Intermediate Information Technology',
        'slug' => 'intermediate-information-technology',
        'short_description' => 'Short field only.',
        'description' => '<p>Rich-text editor content.</p>',
        'category' => 'Information Technology',
        'level' => 'Intermediate',
        'duration' => '4h',
        'is_published' => true,
        'created_by' => $faculty->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.courses.show', $course->id))
        ->assertOk()
        ->assertSee('<p class="hero-desc">Short field only.</p>', false)
        ->assertSeeText('Intermediate')
        ->assertSeeText('4h');

    preg_match('/<div class="meta-row">(.*?)<\/div>/s', $response->getContent(), $metaRow);

    expect($metaRow)->not->toBeEmpty()
        ->and($metaRow[1])->not->toContain('Intermediate')
        ->and($metaRow[1])->not->toContain('4h');
});
