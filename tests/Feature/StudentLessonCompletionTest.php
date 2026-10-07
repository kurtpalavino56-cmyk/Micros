<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function lessonTrackingFixture(): array
{
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = Course::create([
        'title' => 'Lesson Tracking Course',
        'slug' => 'lesson-tracking-'.uniqid(),
        'description' => 'Test course',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
    ]);
    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module One',
        'order' => 1,
    ]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Text',
        'order' => 1,
        'content' => 'Lesson content',
    ]);

    return [$student, $course, $lesson];
}

test('progress endpoint ignores browser claims that lessons are complete', function () {
    [$student, $course, $lesson] = lessonTrackingFixture();
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);

    $this->actingAs($student)
        ->postJson(route('courses.progress', $course->id), [
            'completed_lessons' => [(string) $lesson->id],
        ])
        ->assertOk();

    expect(DB::table('lesson_completions')->where('user_id', $student->id)->count())->toBe(0)
        ->and($student->enrollments()->first()->progress_state['completed_lessons'])->toBe([]);
});

test('lesson completion requires enrollment and a preceding lesson start', function () {
    [$student, $course, $lesson] = lessonTrackingFixture();

    $this->actingAs($student)
        ->postJson(route('courses.progress', $course->id), [
            'completed_lessons' => [(string) $lesson->id],
        ])
        ->assertNotFound();

    expect($student->enrollments()->exists())->toBeFalse();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);

    $this->actingAs($student)
        ->postJson(route('courses.lessons.start', [$course->id, $lesson->id]))
        ->assertOk()
        ->assertJson(['ok' => true]);

    $this->travel(6)->seconds();

    $this->postJson(route('courses.lessons.complete', [$course->id, $lesson->id]))
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(DB::table('lesson_completions')
        ->where('user_id', $student->id)
        ->where('lesson_id', $lesson->id)
        ->whereNotNull('server_verified_at')
        ->exists())->toBeTrue();
});

test('reopening the course restores lessons from server-verified completions, not a stale progress snapshot', function () {
    [$student, $course, $completedLesson] = lessonTrackingFixture();
    $module = $completedLesson->module;
    $nextLesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson Two',
        'type' => 'Text',
        'order' => 2,
        'content' => 'Next lesson content',
    ]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => ['completed_lessons' => [], 'module_scores' => []],
    ]);

    DB::table('lesson_completions')->insert([
        'user_id' => $student->id,
        'lesson_id' => $completedLesson->id,
        'completed_at' => now(),
        'server_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($student)
        ->get(route('courses.learn', $course->id))
        ->assertOk()
        ->assertSee('course-completion-dialog');

    expect($response->viewData('saved_progress')['completed_lessons'])
        ->toBe([(string) $completedLesson->id])
        ->and($response->viewData('saved_progress')['completed_lessons'])
        ->not->toContain((string) $nextLesson->id)
        ->and($enrollment->fresh()->progress_state['completed_lessons'])
        ->toBe([(string) $completedLesson->id]);
});

test('the next lesson remains locked until the preceding lesson is server-verified', function () {
    [$student, $course, $firstLesson] = lessonTrackingFixture();
    $secondLesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $firstLesson->module_id,
        'title' => 'Lesson Two',
        'type' => 'Text',
        'order' => 2,
        'content' => 'Next lesson content',
    ]);
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 0,
        'progress_state' => [],
    ]);

    $this->actingAs($student)
        ->postJson(route('courses.lessons.start', [$course->id, $secondLesson->id]))
        ->assertForbidden();
});
