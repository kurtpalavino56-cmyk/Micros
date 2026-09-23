<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('monitoring live endpoint returns current metrics', function () {
    $this->seed();

    $response = $this->getJson('/monitoring/live');

    $response->assertOk()
        ->assertJsonStructure([
            'timestamp',
            'stats' => [
                'active_users',
                'events_today',
                'enrollments_today',
                'badges_today',
                'certificates_today',
            ],
            'activity' => [
                '*' => [
                    'title',
                    'detail',
                    'time',
                    'type',
                ],
            ],
        ]);
});
