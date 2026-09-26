<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can load the current live monitoring payload', function () {
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
            'activity' => [
                '*' => ['title', 'detail', 'time'],
            ],
            'recentBadges' => [
                '*' => ['name', 'earned_count'],
            ],
        ]);
});

