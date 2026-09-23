<?php

test('micro credentials supports quizzes, payments, notifications, and analytics tables', function () {
    $tables = array_map(
        fn ($table) => current((array) $table),
        DB::connection('mysql')->select('SHOW TABLES')
    );

    expect($tables)->toContain('quizzes')
        ->and($tables)->toContain('quiz_questions')
        ->and($tables)->toContain('quiz_attempts')
        ->and($tables)->toContain('quiz_attempt_answers')
        ->and($tables)->toContain('payments')
        ->and($tables)->toContain('notifications')
        ->and($tables)->toContain('analytics_events');
});
