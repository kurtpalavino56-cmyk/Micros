<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can open the posted announcement edit form', function () {
    $admin = User::factory()->admin()->create();
    $announcement = Announcement::create([
        'title' => 'Original announcement',
        'body' => '<p>Original body.</p>',
        'audience' => ['public'],
        'created_by' => $admin->id,
        'is_published' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.announcements'))
        ->assertOk()
        ->assertSee('data-edit-announcement="'.$announcement->id.'"', false)
        ->assertSee('id="edit-modal-'.$announcement->id.'"', false)
        ->assertSee('id="edit-'.$announcement->id.'" data-ckeditor-deferred', false)
        ->assertSee('data-view-announcement="'.$announcement->id.'"', false)
        ->assertSee('class="page-intro"', false);
});

test('admin can update a posted announcement', function () {
    $admin = User::factory()->admin()->create();
    $announcement = Announcement::create([
        'title' => 'Original announcement',
        'body' => '<p>Original body.</p>',
        'audience' => ['public'],
        'created_by' => $admin->id,
        'is_published' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.announcements.update', $announcement->id), [
            'title' => 'Updated announcement',
            'body' => '<p>Updated body.</p>',
            'audience' => ['faculty'],
        ])
        ->assertRedirect(route('admin.announcements'))
        ->assertSessionHas('success', 'Announcement updated.');

    $announcement->refresh();

    expect($announcement->title)->toBe('Updated announcement')
        ->and($announcement->audience)->toBe(['faculty'])
        ->and($announcement->body)->toContain('Updated body.');
});
