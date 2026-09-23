<?php

use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Builds a course with one module, one lesson, and a single-question quiz
 * (passing_score 70) — one question means score=1 is a pass (100%) and
 * score=0 is a fail (0%), exercising QuizGradingService exactly as it
 * exists today (unchanged).
 */
function makeQuizIntegrationCourse(array $courseOverrides = []): array
{
    $faculty = User::factory()->faculty()->create();
    $course = Course::create(array_merge([
        'title' => 'Quiz Integration Course',
        'slug' => 'quiz-integration-course-'.uniqid(),
        'description' => 'x',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
        'requires_faculty_verification' => false,
    ], $courseOverrides));

    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module One', 'order' => 1]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Lesson One', 'type' => 'Video', 'order' => 1,
    ]);
    $quiz = Quiz::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Module Quiz', 'passing_score' => 70, 'attempts' => '3 Attempts', 'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $quiz->id, 'question' => 'Q1', 'type' => 'Multiple Choice',
        'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 1,
    ]);

    return [$course, $module, $lesson, $quiz];
}

// ── A. Passing final quiz + all lessons completed ────────────────────────

test('passing the final quiz with all lessons completed automatically triggers completion evaluation', function () {
    [$course, $module, $lesson, $quiz] = makeQuizIntegrationCourse();
    $student = User::factory()->create();
    $this->actingAs($student);

    $response = $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [(string) $lesson->id],
        'quiz_results' => [0 => ['score' => 1]],
    ]);

    $response->assertOk()->assertJsonPath('percent', 100);

    $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->fresh()->progress_percent)->toBe(100)
        ->and($enrollment->fresh()->progress_state['module_scores']['0'])->toBe(1)
        ->and(QuizAttempt::where('user_id', $student->id)->where('quiz_id', $quiz->id)->count())->toBe(1)
        ->and($enrollment->lessons_completed)->toBeTrue()
        ->and($enrollment->quizzes_completed)->toBeTrue()
        ->and($enrollment->quiz_mastery_met)->toBeTrue()
        ->and($enrollment->academic_unit_confirmation_status)->toBe('pending')
        ->and($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
        ->and($enrollment->is_completed)->toBeFalse()
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);
});

// ── B. Failing quiz ────────────────────────────────────────────────────

test('failing the quiz does not mark mastery, issue credentials, or enter institutional confirmation', function () {
    [$course, $module, $lesson, $quiz] = makeQuizIntegrationCourse();
    $student = User::factory()->create();
    $this->actingAs($student);

    $response = $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [(string) $lesson->id],
        'quiz_results' => [0 => ['score' => 0]],
    ]);

    $response->assertOk();

    $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();

    expect($enrollment->quiz_mastery_met)->toBeFalse()
        ->and($enrollment->academic_unit_confirmation_status)->toBe('pending')
        ->and($enrollment->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);
});

// ── C. Passing quiz but incomplete lessons ───────────────────────────────

test('passing the quiz with incomplete lessons does not reach official completion', function () {
    [$course, $module, $lesson, $quiz] = makeQuizIntegrationCourse();
    $student = User::factory()->create();
    $this->actingAs($student);

    // Lesson deliberately NOT included in completed_lessons.
    $response = $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [],
        'quiz_results' => [0 => ['score' => 1]],
    ]);

    $response->assertOk();

    $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();

    expect($enrollment->lessons_completed)->toBeFalse()
        ->and($enrollment->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);
});

// ── D. Passing quiz on a course requiring faculty verification ───────────

test('passing the quiz on a course requiring faculty verification stops at faculty verification, not academic confirmation', function () {
    [$course, $module, $lesson, $quiz] = makeQuizIntegrationCourse(['requires_faculty_verification' => true]);
    $student = User::factory()->create();
    $this->actingAs($student);

    $response = $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [(string) $lesson->id],
        'quiz_results' => [0 => ['score' => 1]],
    ]);

    $response->assertOk();

    $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();

    expect($enrollment->faculty_verification_status)->toBe('pending')
        // Must NOT have skipped ahead to academic-unit confirmation being
        // the only thing shown as pending — faculty is genuinely the
        // first outstanding gate here, though both remain independent
        // fields and academic-unit confirmation is also initialized.
        ->and($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);
});

// ── E. After academic confirmation ────────────────────────────────────────

test('after academic-unit confirmation exactly one badge and one certificate are issued and evaluation stays idempotent', function () {
    [$course, $module, $lesson, $quiz] = makeQuizIntegrationCourse();
    $badge = Badge::create(['name' => 'Integration Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    $student = User::factory()->create();
    $this->actingAs($student);

    $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [(string) $lesson->id],
        'quiz_results' => [0 => ['score' => 1]],
    ])->assertOk();

    $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();
    expect(DB::table('user_badges')->count())->toBe(0);

    $admin = User::factory()->admin()->create();
    app(MicrocredentialCompletionService::class)->recordAcademicUnitConfirmation($enrollment->fresh(), $admin->id, 'confirmed');

    // Another progress save after confirmation (e.g. student revisits the
    // player) must not duplicate anything.
    $this->post(route('courses.progress', $course->id), [
        'completed_lessons' => [(string) $lesson->id],
        'quiz_results' => [],
    ])->assertOk();

    expect(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1)
        ->and($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});
