<?php

use App\Models\CompetencyProgress;
use App\Models\CompetencyUnit;
use App\Models\CompetencyCategory;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LearningOutcome;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Same "ready" fixture approach as the MicrocredentialCompletionService
 * unit tests: every real gate (lessons, quiz mastery, competency mastery)
 * is satisfied through actual persisted data so evaluate() genuinely
 * reaches the institutional-verification stage, not by hand-setting
 * enrollment flags.
 */
function makeReadyEnrollmentFor(User $faculty, array $courseOverrides = []): array
{
    $student = User::factory()->create();

    $course = Course::create(array_merge([
        'title' => 'Test Microcredential',
        'slug' => 'test-microcredential-'.uniqid(),
        'description' => 'A microcredential under test',
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

    DB::table('lesson_completions')->insert([
        'user_id' => $student->id, 'lesson_id' => $lesson->id,
        'completed_at' => now(), 'server_verified_at' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id,
        'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now(),
    ]);

    $enrollment = Enrollment::create([
        'user_id' => $student->id, 'course_id' => $course->id,
        'enrolled_at' => now(), 'progress_percent' => 100, 'progress_state' => [],
    ]);

    $enrollment = app(MicrocredentialCompletionService::class)->evaluate($enrollment);

    return [$course, $enrollment->fresh(), $student];
}

// ── Faculty verification ────────────────────────────────────────────────

test('faculty can verify an enrollment belonging to their own course', function () {
    $faculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty, ['requires_faculty_verification' => true]);

    $this->actingAs($faculty);
    $response = $this->post(route('faculty.enrollments.verify', $enrollment->id), ['decision' => 'verified']);

    $response->assertRedirect();
    expect($enrollment->fresh()->faculty_verification_status)->toBe('verified')
        ->and($enrollment->fresh()->faculty_verified_by)->toBe($faculty->id);
});

test('faculty cannot verify an enrollment belonging to another faculty members course', function () {
    $owner = User::factory()->faculty()->create();
    $otherFaculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($owner);

    $this->actingAs($otherFaculty);
    $response = $this->post(route('faculty.enrollments.verify', $enrollment->id), ['decision' => 'verified']);

    $response->assertForbidden();
    expect($enrollment->fresh()->faculty_verification_status)->not->toBe('verified');
});

test('faculty rejection reaches rejected completion status when faculty verification is required', function () {
    $faculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty, ['requires_faculty_verification' => true]);

    $this->actingAs($faculty);
    $this->post(route('faculty.enrollments.verify', $enrollment->id), ['decision' => 'rejected']);

    expect($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);
});

test('student cannot access the faculty verification endpoint', function () {
    $faculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);
    $student = User::factory()->create();

    $this->actingAs($student);
    $response = $this->post(route('faculty.enrollments.verify', $enrollment->id), ['decision' => 'verified']);

    // RoleBasedAccess redirects a mismatched role away rather than 403ing
    // at the middleware layer (see RoleBasedAccess::handle()); either way
    // the action must never actually be recorded.
    $response->assertStatus(302);
    expect($enrollment->fresh()->faculty_verification_status)->not->toBe('verified');
});

// ── Admin academic-unit confirmation ────────────────────────────────────

test('admin can confirm academic-unit confirmation for an enrollment', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);

    $this->actingAs($admin);
    $response = $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]),
        ['decision' => 'confirmed']
    );

    $response->assertRedirect();
    expect($enrollment->fresh()->academic_unit_confirmation_status)->toBe('confirmed')
        ->and($enrollment->fresh()->academic_unit_confirmed_by)->toBe($admin->id)
        ->and($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});

test('admin rejection reaches rejected completion status', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);

    $this->actingAs($admin);
    $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]),
        ['decision' => 'rejected']
    );

    expect($enrollment->fresh()->academic_unit_confirmation_status)->toBe('rejected')
        ->and($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);
});

test('faculty cannot access the admin academic-confirmation endpoint', function () {
    $faculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);

    $this->actingAs($faculty);
    $response = $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]),
        ['decision' => 'confirmed']
    );

    $response->assertStatus(302);
    expect($enrollment->fresh()->academic_unit_confirmation_status)->not->toBe('confirmed');
});

test('admin cannot confirm an enrollment through a mismatched course context', function () {
    $facultyA = User::factory()->faculty()->create();
    $facultyB = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    [$courseA, $enrollmentA] = makeReadyEnrollmentFor($facultyA);
    [$courseB] = makeReadyEnrollmentFor($facultyB);

    $this->actingAs($admin);
    // enrollmentA genuinely belongs to courseA, but the route is called
    // with courseB's id as the {id} course-context segment.
    $response = $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $courseB->id, 'enrollment' => $enrollmentA->id]),
        ['decision' => 'confirmed']
    );

    $response->assertNotFound();
    expect($enrollmentA->fresh()->academic_unit_confirmation_status)->not->toBe('confirmed');
});

test('student cannot access the admin academic-confirmation endpoint', function () {
    $faculty = User::factory()->faculty()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);
    $student = User::factory()->create();

    $this->actingAs($student);
    $response = $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]),
        ['decision' => 'confirmed']
    );

    $response->assertStatus(302);
    expect($enrollment->fresh()->academic_unit_confirmation_status)->not->toBe('confirmed');
});

// ── Regression: credentials must be issued by the institutional-action
// route itself, with no subsequent student request ─────────────────────

test('a single admin academic-confirmation request issues the badge and certificate immediately, with no student action afterward', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty);

    $badge = \App\Models\Badge::create(['name' => 'Admin Confirm Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    // Academic-unit confirmation is the only outstanding gate here:
    // makeReadyEnrollmentFor() already satisfies lessons/quiz/competency
    // mastery, and requires_faculty_verification is false by default.
    expect($enrollment->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);

    $this->actingAs($admin);
    $response = $this->post(
        route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id]),
        ['decision' => 'confirmed']
    );
    $response->assertRedirect();

    // No second (student or otherwise) request is made below — this is
    // the exact real-world scenario: admin confirms, nothing else happens.
    $fresh = $enrollment->fresh();

    expect($fresh->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($fresh->academic_unit_confirmation_status)->toBe('confirmed')
        ->and(DB::table('user_badges')->where('user_id', $fresh->user_id)->where('badge_id', $badge->id)->count())->toBe(1)
        ->and(DB::table('certificates')->where('user_id', $fresh->user_id)->where('course_id', $course->id)->count())->toBe(1);
});

test('a single faculty verification request issues the badge and certificate immediately when it is the final outstanding gate', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    [$course, $enrollment] = makeReadyEnrollmentFor($faculty, ['requires_faculty_verification' => true]);

    $badge = \App\Models\Badge::create(['name' => 'Faculty Verify Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    // Satisfy academic-unit confirmation FIRST (via the authorized
    // service directly, not a student request), so faculty verification
    // is the final outstanding gate for this request.
    app(MicrocredentialCompletionService::class)->recordAcademicUnitConfirmation($enrollment->fresh(), $admin->id, 'confirmed');
    expect($enrollment->fresh()->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);

    $this->actingAs($faculty);
    $response = $this->post(route('faculty.enrollments.verify', $enrollment->id), ['decision' => 'verified']);
    $response->assertRedirect();

    $fresh = $enrollment->fresh();

    expect($fresh->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($fresh->faculty_verification_status)->toBe('verified')
        ->and(DB::table('user_badges')->where('user_id', $fresh->user_id)->where('badge_id', $badge->id)->count())->toBe(1)
        ->and(DB::table('certificates')->where('user_id', $fresh->user_id)->where('course_id', $course->id)->count())->toBe(1);
});
