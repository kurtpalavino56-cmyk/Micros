<?php

use App\Models\AnalyticsEvent;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\QuizAttemptService;
use App\Services\QuizGradingService;
use App\Services\StudentProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeCourseWithModule(array $course = [], array $module = []): array
{
    $courseModel = Course::create(array_merge([
        'title' => 'Testing Fundamentals',
        'slug' => 'testing-fundamentals-'.uniqid(),
        'description' => 'A test course',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
    ], $course));

    $moduleModel = CourseModule::create(array_merge([
        'course_id' => $courseModel->id,
        'title' => 'Module One',
        'order' => 1,
    ], $module));

    return [$courseModel, $moduleModel];
}

function addQuiz(Course $course, CourseModule $module, array $quiz = [], int $questionCount = 0): Quiz
{
    $quizModel = Quiz::create(array_merge([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Module Quiz',
        'passing_score' => 70,
        'attempts' => '3 Attempts',
        'is_active' => true,
    ], $quiz));

    for ($index = 1; $index <= $questionCount; $index++) {
        QuizQuestion::create([
            'quiz_id' => $quizModel->id,
            'question' => "Question $index",
            'type' => 'Multiple Choice',
            'options' => ['A', 'B', 'C'],
            'correct_answer' => 'A',
            'points' => 1,
        ]);
    }

    return $quizModel->load('questions');
}

test('quiz grading calculates percentage and passing status from the current question count', function () {
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['passing_score' => 70], 3);

    $grade = app(QuizGradingService::class)->grade($quiz, ['score' => 2]);

    expect($grade)->toMatchArray([
        'correct' => 2,
        'total' => 3,
        'score' => 67,
        'passed' => false,
    ]);

    expect(app(QuizGradingService::class)->grade($quiz, ['score' => 3]))
        ->toMatchArray(['score' => 100, 'passed' => true]);
});

test('quiz grading returns zero for a quiz with no questions', function () {
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['passing_score' => 1]);

    expect(app(QuizGradingService::class)->grade($quiz, ['score' => 1]))
        ->toMatchArray(['correct' => 1, 'total' => 0, 'score' => 0, 'passed' => false]);
});

test('quiz attempts parse default, unlimited, numeric, and malformed attempt limits', function () {
    [$course, $module] = makeCourseWithModule();
    $service = app(QuizAttemptService::class);

    expect($service->attemptsAllowed(addQuiz($course, $module, ['attempts' => null])))->toBe(1);
    expect($service->attemptsAllowed(addQuiz($course, $module, ['attempts' => 'Unlimited'])))->toBe(0);
    expect($service->attemptsAllowed(addQuiz($course, $module, ['attempts' => '5 Attempts'])))->toBe(5);
    expect($service->attemptsAllowed(addQuiz($course, $module, ['attempts' => 'not specified'])))->toBe(1);
});

test('a passing quiz submission is graded, persisted, and emits a quiz-passed event', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['passing_score' => 70], 3);

    $result = app(QuizAttemptService::class)->submit($student, $quiz, ['score' => 3]);

    expect($result)->toMatchArray(['blocked' => false, 'score' => 100, 'passed' => true, 'unlock' => null]);
    expect(QuizAttempt::where('user_id', $student->id)->where('quiz_id', $quiz->id)->count())->toBe(1);
    expect(AnalyticsEvent::where('user_id', $student->id)->where('event_type', 'quiz_passed')->count())->toBe(1);
});

test('a failed quiz submission is persisted and receives a 24-hour unlock timestamp', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['passing_score' => 70], 3);

    Carbon::setTestNow('2026-09-16 12:00:00');
    $result = app(QuizAttemptService::class)->submit($student, $quiz, ['score' => 1]);

    expect($result['blocked'])->toBeFalse()
        ->and($result['score'])->toBe(33)
        ->and($result['passed'])->toBeFalse()
        ->and($result['unlock'])->toBe(Carbon::now()->addHours(24)->getTimestamp() * 1000);
    expect(QuizAttempt::where('user_id', $student->id)->count())->toBe(1);
    Carbon::setTestNow();
});

test('a failed quiz cannot be resubmitted during its cooldown', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['attempts' => '3'], 3);
    $service = app(QuizAttemptService::class);

    Carbon::setTestNow('2026-09-16 12:00:00');
    $service->submit($student, $quiz, ['score' => 0]);
    $blocked = $service->submit($student, $quiz, ['score' => 3]);

    expect($blocked['blocked'])->toBeTrue()
        ->and($blocked['unlock'])->toBe(Carbon::now()->addHours(24)->getTimestamp() * 1000);
    expect(QuizAttempt::where('user_id', $student->id)->count())->toBe(1);
    Carbon::setTestNow();
});

test('attempts are exhausted after the configured budget is used', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['attempts' => '1'], 1);
    $service = app(QuizAttemptService::class);

    Carbon::setTestNow('2026-09-16 12:00:00');
    $service->submit($student, $quiz, ['score' => 1]);
    $status = $service->retakeStatus($quiz, $student->id);
    $blocked = $service->submit($student, $quiz, ['score' => 1]);

    expect($status)->toMatchArray(['allowed' => 1, 'used' => 1, 'exhausted' => true, 'unlock' => null]);
    expect($blocked['blocked'])->toBeTrue();
    expect(QuizAttempt::where('user_id', $student->id)->count())->toBe(1);
    Carbon::setTestNow();
});

test('quiz edits reset the counted attempt history and stale cooldown', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, ['attempts' => '1'], 1);
    $service = app(QuizAttemptService::class);

    Carbon::setTestNow('2026-09-16 12:00:00');
    $service->submit($student, $quiz, ['score' => 0]);
    $quiz->questions_changed_at = Carbon::now()->addMinute();
    $quiz->save();

    expect($service->attemptsUsed($quiz->fresh(), $student->id))->toBe(0);
    expect($service->retakeStatus($quiz->fresh(), $student->id))
        ->toMatchArray(['allowed' => 1, 'used' => 0, 'exhausted' => false, 'unlock' => null]);
    Carbon::setTestNow();
});

test('progress percentage uses current question counts and caps stale scores', function () {
    [$course, $module] = makeCourseWithModule();
    addQuiz($course, $module, [], 2);
    $service = app(StudentProgressService::class);

    expect($service->calculateProgressPercent($course->load('modules.quiz.questions'), ['0' => 5]))->toBe(100);
    expect($service->calculateProgressPercent($course->load('modules.quiz.questions'), ['0' => 1]))->toBe(50);
});

test('earned module scores only retain scores backed by a current quiz attempt', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $quiz = addQuiz($course, $module, [], 2);
    $service = app(StudentProgressService::class);

    expect($service->earnedModuleScores($course->load('modules.quiz.questions'), ['0' => 2], $student->id))->toBe([]);

    QuizAttempt::create([
        'user_id' => $student->id,
        'quiz_id' => $quiz->id,
        'score' => 100,
        'passed' => true,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    expect($service->earnedModuleScores($course->fresh()->load('modules.quiz.questions'), ['0' => 2], $student->id))
        ->toBe(['0' => 2]);
});

test('progress resolves real lesson IDs and legacy positions without attaching stale replacement lessons', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $oldLesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Old Lesson',
        'type' => 'Video',
        'order' => 1,
    ]);
    Carbon::setTestNow('2026-09-16 12:00:00');
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);
    $enrollment->updated_at = now();
    $enrollment->save();

    $oldLesson->delete();
    Carbon::setTestNow('2026-09-16 12:01:00');
    $newLesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Replacement Lesson',
        'type' => 'Video',
        'order' => 1,
    ]);
    $course->load('modules.lessons');

    expect(app(StudentProgressService::class)->resolveCompletedLessons(
        $course,
        [(string) $oldLesson->id, '0-0', (string) $newLesson->id, '99-99'],
        $enrollment
    ))->toContain((string) $newLesson->id)
        ->not->toContain('0-0')
        ->not->toContain('99-99');
    Carbon::setTestNow();
});

test('lesson completion persistence is idempotent and emits analytics once', function () {
    $student = User::factory()->create(['first_name' => 'Test', 'last_name' => 'Student']);
    [$course, $module] = makeCourseWithModule();
    CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Video',
        'order' => 1,
    ]);
    $course->load('modules.lessons');
    $service = app(StudentProgressService::class);

    $service->persistLessonCompletions($course, $student, ['0-0']);
    $service->persistLessonCompletions($course->fresh()->load('modules.lessons'), $student, ['0-0']);

    expect($student->lessonCompletions()->count())->toBe(1);
    expect(AnalyticsEvent::where('user_id', $student->id)->where('event_type', 'lesson_completed')->count())->toBe(1);
});

test('lesson completion persistence also handles modern plain lesson-id keys, not only legacy positions', function () {
    // Regression test for the bug found via enrollment #64 locally:
    // resolveCompletedLessons() already accepted a plain lesson id as a
    // valid key, but persistLessonCompletions()'s regex only ever matched
    // the legacy "moduleIndex-lessonIndex" format, so a modern-format key
    // was silently skipped and never written to lesson_completions.
    $student = User::factory()->create(['first_name' => 'Test', 'last_name' => 'Student']);
    [$course, $module] = makeCourseWithModule();
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Video',
        'order' => 1,
    ]);
    $course->load('modules.lessons');
    $service = app(StudentProgressService::class);

    $service->persistLessonCompletions($course, $student, [(string) $lesson->id]);
    $service->persistLessonCompletions($course->fresh()->load('modules.lessons'), $student, [(string) $lesson->id]);

    expect($student->lessonCompletions()->count())->toBe(1);
    expect(AnalyticsEvent::where('user_id', $student->id)->where('event_type', 'lesson_completed')->count())->toBe(1);
});

test('syncLessonsCompletedFlag marks enrollments.lessons_completed true only once every course lesson is recorded', function () {
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $lessonOne = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Lesson One', 'type' => 'Video', 'order' => 1,
    ]);
    $lessonTwo = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Lesson Two', 'type' => 'Video', 'order' => 2,
    ]);
    $course->load('modules.lessons');

    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);

    $service = app(StudentProgressService::class);

    // Only one of two lessons completed so far — flag must stay false.
    $service->persistLessonCompletions($course, $student, [(string) $lessonOne->id]);
    $service->syncLessonsCompletedFlag($course, $enrollment);
    expect($enrollment->fresh()->lessons_completed)->toBeFalse();

    // Second lesson completed — flag must now flip true.
    $service->persistLessonCompletions($course, $student, [(string) $lessonTwo->id]);
    $service->syncLessonsCompletedFlag($course, $enrollment->fresh());
    expect($enrollment->fresh()->lessons_completed)->toBeTrue();

    // completion_status / quiz gates / academic-unit confirmation are
    // untouched by this sync — confirms it does not run the full
    // MicrocredentialCompletionService state machine.
    expect($enrollment->fresh()->completion_status)->toBe('in_progress');
});

test('syncLessonsCompletedFlag sources lessons via modules, not the direct course_id relation, so a course_id mismatch cannot produce a false positive', function () {
    // Regression test for the bug found via enrollment #66 locally:
    // syncLessonsCompletedFlag() used Course::lessons() (the direct,
    // course_id-based relation). When that relation returns empty for a
    // course whose lesson rows disagree with it, the method vacuously
    // defaulted to "all lessons complete" even with zero real
    // completions. This test creates exactly that disagreement — a
    // lesson whose course_id points elsewhere but whose module_id
    // correctly links it to $course — and proves the method now relies
    // on the module-nested path instead, matching resolveCompletedLessons()
    // and persistLessonCompletions().
    $student = User::factory()->create();
    [$course, $module] = makeCourseWithModule();
    $otherCourse = Course::create([
        'title' => 'Unrelated Course',
        'slug' => 'unrelated-course-'.uniqid(),
        'description' => 'Another course',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
    ]);
    $lesson = CourseLesson::create([
        'course_id' => $otherCourse->id, // deliberately mismatched
        'module_id' => $module->id,      // real link to $course via its module
        'title' => 'Lesson One',
        'type' => 'Video',
        'order' => 1,
    ]);
    $course->load('modules.lessons');

    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);

    $service = app(StudentProgressService::class);

    // No completions recorded yet — must NOT become true just because
    // Course::lessons() (course_id-based) would see zero lessons here.
    $service->syncLessonsCompletedFlag($course, $enrollment);
    expect($enrollment->fresh()->lessons_completed)->toBeFalse();

    // Genuinely complete the lesson via the module-nested path, then
    // the flag must correctly flip true.
    $service->persistLessonCompletions($course, $student, [(string) $lesson->id]);
    $service->syncLessonsCompletedFlag($course, $enrollment->fresh());
    expect($enrollment->fresh()->lessons_completed)->toBeTrue();
});