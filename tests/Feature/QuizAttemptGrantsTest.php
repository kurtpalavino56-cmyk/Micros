<?php

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizAttemptGrant;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\QuizAttemptService;
use App\Services\UserNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeGrantCourse(User $faculty): array
{
    $course = Course::query()->create([
        'title' => 'Grant Test Course',
        'slug' => 'grant-test-'.uniqid(),
        'description' => 'Course for attempt grant coverage',
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
        'title' => 'Assessment',
        'passing_score' => 100,
        'attempts' => '1 Attempt',
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

    return [$course, $module, $quiz->fresh()->load('questions'), $question];
}

function exhaustGrantQuiz(User $student, Quiz $quiz, QuizQuestion $question, ?Carbon $at = null): QuizAttempt
{
    $at ??= now()->subMinutes(6);
    $attempt = QuizAttempt::query()->create([
        'user_id' => $student->id,
        'quiz_id' => $quiz->id,
        'score' => 0,
        'passed' => false,
        'started_at' => $at,
        'submitted_at' => $at,
    ]);
    $attempt->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
    QuizAttemptAnswer::query()->create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'answer' => 'B',
        'is_correct' => false,
    ]);

    return $attempt;
}

test('effective attempt limit adds current grants and unlimited remains unlimited', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [, , $quiz] = makeGrantCourse($faculty);
    $service = app(QuizAttemptService::class);
    QuizAttemptGrant::query()->create([
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'extra_attempts' => 2,
        'granted_by' => $faculty->id,
        'reason' => 'Additional practice',
    ]);

    expect($service->retakeStatus($quiz, $student->id)['allowed'])->toBe(3);

    $quiz->update(['attempts' => 'Unlimited']);
    expect($service->retakeStatus($quiz->fresh(), $student->id)['allowed'])->toBe(0)
        ->and($service->retakeStatus($quiz->fresh(), $student->id)['exhausted'])->toBeFalse();
});

test('grants made before the latest question edit are stale', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [, , $quiz] = makeGrantCourse($faculty);
    exhaustGrantQuiz($student, $quiz, $quiz->questions->first(), now()->subDay());
    $grant = QuizAttemptGrant::query()->create([
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'extra_attempts' => 3,
        'granted_by' => $faculty->id,
        'reason' => 'Before edit',
    ]);
    $grant->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();
    $quiz->update(['questions_changed_at' => now()]);

    expect(app(QuizAttemptService::class)->retakeStatus($quiz->fresh(), $student->id))
        ->toMatchArray(['allowed' => 1, 'used' => 0, 'exhausted' => false]);
});

test('a grant raises the limit without bypassing the existing cooldown', function () {
    Carbon::setTestNow('2026-10-25 12:00:00');
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [, , $quiz, $question] = makeGrantCourse($faculty);
    exhaustGrantQuiz($student, $quiz, $question, Carbon::now()->subMinute());
    QuizAttemptGrant::query()->create([
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'extra_attempts' => 1,
        'granted_by' => $faculty->id,
        'reason' => 'Retry after review',
    ]);

    $status = app(QuizAttemptService::class)->retakeStatus($quiz, $student->id);
    $submission = app(QuizAttemptService::class)->submit($student, $quiz, ['answers' => [(string) $question->id => 'A']]);

    expect($status['allowed'])->toBe(2)
        ->and($status['exhausted'])->toBeFalse()
        ->and($status['unlock'])->not->toBeNull()
        ->and($submission['blocked'])->toBeTrue()
        ->and(QuizAttempt::query()->where('user_id', $student->id)->count())->toBe(1);
    Carbon::setTestNow();
});

test('failed quiz submission returns the updated attempts and cooldown for the live result', function () {
    Carbon::setTestNow('2026-10-25 12:00:00');
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [$course, , $quiz, $question] = makeGrantCourse($faculty);
    $quiz->update(['attempts' => '2 Attempts']);
    Enrollment::query()->create(['user_id' => $student->id, 'course_id' => $course->id]);

    $this->actingAs($student)->postJson(route('quiz.submit', $quiz->id), [
        'answers' => [(string) $question->id => 'B'],
    ])->assertOk()->assertJson([
        'ok' => true,
        'passed' => false,
        'attempts_remaining' => 1,
        'attempts_allowed' => 2,
        'attempts_exhausted' => false,
        'retake_unlock_at' => Carbon::now()->addMinutes(5)->getTimestamp() * 1000,
    ]);

    Carbon::setTestNow();
});

test('faculty can grant a final exam attempt and passing it uses the existing student completion flow', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [$course, $module, $quiz, $question] = makeGrantCourse($faculty);
    $module->update(['is_final' => true]);
    $enrollment = Enrollment::query()->create(['user_id' => $student->id, 'course_id' => $course->id]);
    exhaustGrantQuiz($student, $quiz, $question);

    $this->actingAs($faculty)->post(route('faculty.students.course.attempt-grants', $course->id), [
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'amount' => 1,
        'reason' => 'Final exam retake approved',
    ])->assertRedirect();

    $notification = Notification::query()->where('user_id', $student->id)->firstOrFail();
    $notificationFeedItem = app(UserNotificationService::class)->feed($student)->firstWhere('id', $notification->id);
    $auditLog = AuditLog::query()->where('event', 'quiz_attempts_granted')->firstOrFail();

    expect(QuizAttemptGrant::query()->where('quiz_id', $quiz->id)->count())->toBe(1)
        ->and($notification->title)->toBe('Additional final exam attempt granted')
        ->and($notification->message)->toContain($faculty->name, 'Final exam retake approved', '2 attempts')
        ->and($notification->entity_type)->toBe('enrollment')
        ->and($notification->entity_id)->toBe($enrollment->id)
        ->and($notificationFeedItem->url)->toBe(route('courses.learn', $course->id))
        ->and($auditLog->changes)->toMatchArray([
            'extra_attempts' => ['old' => null, 'new' => 1],
            'reason' => ['old' => null, 'new' => 'Final exam retake approved'],
        ]);

    Carbon::setTestNow(now()->addMinutes(6));
    $response = $this->actingAs($student)->post(route('quiz.submit', $quiz->id), [
        'answers' => [(string) $question->id => 'A'],
    ]);

    $response->assertRedirect();
    expect(QuizAttempt::query()->where('user_id', $student->id)->where('passed', true)->exists())->toBeTrue()
        ->and(Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->value('is_completed'))->toBeFalse();
    Carbon::setTestNow();
});

test('only the faculty who owns the course can grant attempts', function () {
    $faculty = User::factory()->faculty()->create();
    $otherFaculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [$course, , $quiz, $question] = makeGrantCourse($faculty);
    Enrollment::query()->create(['user_id' => $student->id, 'course_id' => $course->id]);
    exhaustGrantQuiz($student, $quiz, $question);

    $this->actingAs($otherFaculty)->post(route('faculty.students.course.attempt-grants', $course->id), [
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'amount' => 1,
        'reason' => 'Unauthorized',
    ])->assertNotFound();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post(route('faculty.students.course.attempt-grants', $course->id), [
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'amount' => 1,
        'reason' => 'Administrator attempt grant',
    ])->assertForbidden();

    expect(QuizAttemptGrant::query()->count())->toBe(0);
});

test('bulk grants are atomic when one selected learner is not exhausted', function () {
    $faculty = User::factory()->faculty()->create();
    $exhaustedStudent = User::factory()->create();
    $availableStudent = User::factory()->create();
    [$course, , $quiz, $question] = makeGrantCourse($faculty);
    foreach ([$exhaustedStudent, $availableStudent] as $student) {
        Enrollment::query()->create(['user_id' => $student->id, 'course_id' => $course->id]);
    }
    exhaustGrantQuiz($exhaustedStudent, $quiz, $question);

    $this->actingAs($faculty)->post(route('faculty.students.course.attempt-grants', $course->id), [
        'grants' => [
            ['student_id' => $exhaustedStudent->id, 'quiz_id' => $quiz->id, 'amount' => 1, 'reason' => 'Approved'],
            ['student_id' => $availableStudent->id, 'quiz_id' => $quiz->id, 'amount' => 1, 'reason' => 'Not exhausted'],
        ],
    ])->assertStatus(422);

    expect(QuizAttemptGrant::query()->count())->toBe(0)
        ->and(AuditLog::query()->where('event', 'quiz_attempts_granted')->count())->toBe(0);
});

test('grant amount and reason are validated', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    [$course, , $quiz, $question] = makeGrantCourse($faculty);
    Enrollment::query()->create(['user_id' => $student->id, 'course_id' => $course->id]);
    exhaustGrantQuiz($student, $quiz, $question);

    $this->actingAs($faculty)->post(route('faculty.students.course.attempt-grants', $course->id), [
        'student_id' => $student->id,
        'quiz_id' => $quiz->id,
        'amount' => 11,
        'reason' => '',
    ])->assertSessionHasErrors(['amount', 'reason']);

    expect(QuizAttemptGrant::query()->count())->toBe(0);
});
