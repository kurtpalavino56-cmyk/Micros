<?php

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

test('POST /courses/{id}/complete does not create a badge or certificate before official institutional completion', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();

    $course = Course::create([
        'title' => 'Endpoint Test Course',
        'slug' => 'endpoint-test-course-'.uniqid(),
        'description' => 'x',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
        'requires_faculty_verification' => false,
        // requires_academic_unit_confirmation is irrelevant here — Phase
        // 3 enforces academic-unit confirmation unconditionally.
    ]);
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
        'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id,
        'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now(),
    ]);

    $enrollment = Enrollment::create([
        'user_id' => $student->id, 'course_id' => $course->id,
        'enrolled_at' => now(), 'progress_percent' => 100, 'progress_state' => [],
    ]);

    $this->actingAs($student);
    $response = $this->post(route('courses.complete', $course->id));

    $response->assertOk();
    $response->assertJson(['ok' => true, 'completed' => false, 'pending_review' => true]);

    expect(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0)
        ->and($enrollment->fresh()->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($enrollment->fresh()->is_completed)->toBeFalse();

    // Now the (mandatory, per Phase 3) academic-unit confirmation happens
    // through the authorized institutional action — never through this
    // student endpoint — and re-hitting the SAME endpoint afterward is
    // what actually surfaces the already-issued credentials, still
    // without creating a duplicate.
    $admin = User::factory()->admin()->create();
    app(MicrocredentialCompletionService::class)->recordAcademicUnitConfirmation($enrollment->fresh(), $admin->id, 'confirmed');

    $second = $this->post(route('courses.complete', $course->id));
    $second->assertOk();
    $second->assertJson(['ok' => true, 'completed' => true]);

    expect(DB::table('user_badges')->count())->toBeLessThanOrEqual(1)
        ->and(DB::table('certificates')->count())->toBeLessThanOrEqual(1);

    // Hitting it a third time must not duplicate anything.
    $this->post(route('courses.complete', $course->id));
    expect(DB::table('user_badges')->count())->toBeLessThanOrEqual(1)
        ->and(DB::table('certificates')->count())->toBeLessThanOrEqual(1);
});
