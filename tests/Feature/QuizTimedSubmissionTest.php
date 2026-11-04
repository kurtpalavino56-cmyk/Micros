<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * @return array{0: User, 1: Course, 2: Quiz, 3: QuizQuestion}
 */
function makeTimedQuizSubmissionFixture(bool $summative = false): array
{
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = Course::query()->create([
        'title' => 'Timed Quiz Course',
        'slug' => 'timed-quiz-'.uniqid(),
        'description' => 'Course for timed quiz submission coverage',
        'level' => 'Beginner',
        'created_by' => $faculty->id,
        'is_published' => true,
    ]);
    $module = CourseModule::query()->create([
        'course_id' => $course->id,
        'title' => 'Assessment Module',
        'order' => 1,
    ]);
    $quiz = Quiz::query()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'module_order' => 1,
        'is_summative' => $summative,
        'title' => 'Timed Assessment',
        'passing_score' => 100,
        'attempts' => 'Unlimited',
        'time_limit' => 1,
        'is_active' => true,
    ]);
    $question = QuizQuestion::query()->create([
        'quiz_id' => $quiz->id,
        'question' => 'Select the correct option',
        'type' => 'Multiple Choice',
        'options' => ['Correct', 'Wrong'],
        'correct_answer' => 'Correct',
        'points' => 1,
    ]);
    $quiz->update(['questions_changed_at' => now()->subHour()]);
    Enrollment::query()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now()->subDay(),
        'progress_state' => [],
    ]);

    return [$student, $course, $quiz->fresh()->load('questions'), $question];
}

function timedQuizSessionKey(User $student, Quiz $quiz): string
{
    return 'quiz_tracking.'.$student->id.'.'.$quiz->id;
}

test('a timed standalone quiz grades answers that arrive within the grace window', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    [$student, , $quiz, $question] = makeTimedQuizSubmissionFixture();

    $response = $this->actingAs($student)
        ->withSession([
            timedQuizSessionKey($student, $quiz) => Carbon::now()->subSeconds(61)->timestamp,
        ])
        ->postJson(route('quiz.submit', $quiz->id), [
            'answers' => [(string) $question->id => 'A'],
        ]);

    $response->assertOk()
        ->assertJsonPath('score', 100)
        ->assertJsonPath('passed', true);
});

test('a timed standalone quiz grades selected answers and treats unanswered questions as incorrect', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    [$student, , $quiz, $question] = makeTimedQuizSubmissionFixture();

    QuizQuestion::query()->create([
        'quiz_id' => $quiz->id,
        'question' => 'Second question',
        'type' => 'Multiple Choice',
        'options' => ['Correct', 'Wrong'],
        'correct_answer' => 'Correct',
        'points' => 1,
    ]);
    $quiz->load('questions');

    $response = $this->actingAs($student)
        ->withSession([
            timedQuizSessionKey($student, $quiz) => Carbon::now()->subSeconds(61)->timestamp,
        ])
        ->postJson(route('quiz.submit', $quiz->id), [
            'answers' => [(string) $question->id => 'A'],
        ]);

    $response->assertOk()
        ->assertJsonPath('score', 50)
        ->assertJsonPath('passed', false);
});

test('a timed standalone quiz discards answers that arrive after the grace window', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    [$student, , $quiz, $question] = makeTimedQuizSubmissionFixture();

    $response = $this->actingAs($student)
        ->withSession([
            timedQuizSessionKey($student, $quiz) => Carbon::now()->subSeconds(66)->timestamp,
        ])
        ->postJson(route('quiz.submit', $quiz->id), [
            'answers' => [(string) $question->id => 'A'],
        ]);

    $response->assertOk()
        ->assertJsonPath('score', 0)
        ->assertJsonPath('passed', false);
});

test('a timed module quiz grades answers that arrive within the grace window', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    [$student, $course, $quiz, $question] = makeTimedQuizSubmissionFixture(summative: true);

    $response = $this->actingAs($student)
        ->withSession([
            timedQuizSessionKey($student, $quiz) => Carbon::now()->subSeconds(61)->timestamp,
        ])
        ->postJson(route('courses.progress', $course->id), [
            'quiz_results' => [
                0 => ['answers' => [(string) $question->id => 'A']],
            ],
        ]);

    $response->assertOk()
        ->assertJsonPath('quiz_submissions.0.score', 100)
        ->assertJsonPath('quiz_submissions.0.passed', true)
        ->assertJsonPath('quiz_submissions.0.correct', 1);
});

test('a timed module quiz discards answers that arrive after the grace window', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    [$student, $course, $quiz, $question] = makeTimedQuizSubmissionFixture(summative: true);

    $response = $this->actingAs($student)
        ->withSession([
            timedQuizSessionKey($student, $quiz) => Carbon::now()->subSeconds(66)->timestamp,
        ])
        ->postJson(route('courses.progress', $course->id), [
            'quiz_results' => [
                0 => ['answers' => [(string) $question->id => 'A']],
            ],
        ]);

    $response->assertOk()
        ->assertJsonPath('quiz_submissions.0.score', 0)
        ->assertJsonPath('quiz_submissions.0.passed', false)
        ->assertJsonPath('quiz_submissions.0.correct', 0);
});
