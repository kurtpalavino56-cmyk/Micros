<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin analytics route uses database counts', function () {
    $this->seed();

    $response = $this->get('/Admin-report');

    $response->assertOk();
    $response->assertViewHas('stats', function ($stats) {
        return $stats['total_students'] >= 1
            && $stats['badges_issued'] >= 0
            && $stats['faculty_total'] >= 1
            && $stats['course_score_avg'] >= 0;
    });
});
