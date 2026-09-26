<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot read the live monitoring feed', function () {
    $this->getJson('/monitoring/live')
        ->assertRedirect(route('login'));
});

test('admin can read the live monitoring feed', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/monitoring/live')
        ->assertOk()
        ->assertJsonStructure([
            'stats' => [
                'active_users',
                'events_today',
                'enrollments_today',
                'badges_today',
            ],
        ]);
});

test('faculty dashboard renders for a signed-in faculty member', function () {
    $faculty = User::factory()->faculty()->create();

    $this->actingAs($faculty)
        ->get('/Faculty-dashboard')
        ->assertOk();
});

