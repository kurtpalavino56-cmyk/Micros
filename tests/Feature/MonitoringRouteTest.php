<?php

test('monitoring live endpoint returns expected JSON payload', function () {
    $response = $this->getJson('/monitoring/live');

    $response
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

test('faculty dashboard renders without requiring a course id', function () {
    $response = $this->get('/Faculty-dashboard');

    $response->assertOk();
});
