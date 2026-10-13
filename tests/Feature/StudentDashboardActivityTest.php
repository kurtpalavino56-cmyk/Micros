<?php

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student dashboard shows a monthly activity calendar', function () {
    $student = User::factory()->create([
        'profile_completed' => true,
        'role_id' => User::ROLE_STUDENT,
    ]);
    $today = now('Asia/Manila')->setTime(12, 0);

    foreach ([
        $today->copy(),
        $today->copy(),
        $today->copy()->subDays(2),
        $today->copy()->subMonth(),
    ] as $occurredAt) {
        AnalyticsEvent::create([
            'user_id' => $student->id,
            'event_type' => 'lesson_completed',
            'occurred_at' => $occurredAt->copy()->utc(),
        ]);
    }

    $response = $this->actingAs($student)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Your learning actions')
        ->assertSee('Past 12 months')
        ->assertSee('4')
        ->assertSee('logged learning actions in the past 12 months')
        ->assertDontSee('Course enrollments, completed lessons, passed quizzes, and completed courses.')
        ->assertSee('Learning actions for '.now('Asia/Manila')->format('M Y'))
        ->assertSee(now('Asia/Manila')->format('Y'));

    $months = $response->viewData('activityMonths');
    $currentMonth = $months[array_key_last($months)];
    $previousMonth = $months[array_key_last($months) - 1];
    $currentMonthDays = array_merge(...$currentMonth['weeks']);
    $currentMonthDates = array_values(array_filter($currentMonthDays, fn (array $day): bool => ! $day['placeholder']));
    $todayActivity = array_values(array_filter($currentMonthDates, fn (array $day): bool => $day['date'] === $today->format('F j, Y')))[0];

    expect($months)->toHaveCount(12)
        ->and($currentMonth['label'])->toBe($today->format('M'))
        ->and($currentMonth['year'])->toBe($today->format('Y'))
        ->and(count($currentMonthDates))->toBe($today->daysInMonth)
        ->and($currentMonth['weeks'])->each->toHaveCount(7)
        ->and($currentMonth['total'])->toBe(3)
        ->and($todayActivity['count'])->toBe(2)
        ->and($previousMonth['total'])->toBe(1);
});
