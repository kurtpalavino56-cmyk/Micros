<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LessonActivity;
use App\Models\LessonActivitySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('faculty analytics returns 404 for a course owned by another faculty member', function () {
    $faculty = User::factory()->faculty()->create();
    $otherFaculty = User::factory()->faculty()->create();
    $foreignCourse = Course::create([
        'title' => 'Another faculty course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $otherFaculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);

    $this->actingAs($faculty)
        ->get(route('faculty.analytics', ['course_id' => $foreignCourse->id]))
        ->assertNotFound();
});

test('faculty analytics preserves the selected course and filters course outcomes', function () {
    $faculty = User::factory()->faculty()->create();
    $selectedCourse = Course::create([
        'title' => 'Selected course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    Course::create([
        'title' => 'Other owned course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);

    $this->actingAs($faculty)
        ->get(route('faculty.analytics', ['course_id' => $selectedCourse->id]))
        ->assertOk()
        ->assertViewHas('selectedCourseId', $selectedCourse->id)
        ->assertViewHas('facultyCourses', fn ($courses) => $courses->count() === 2)
        ->assertViewHas('courseAnalytics', fn ($courses) => $courses->count() === 1
            && $courses->first()->title === 'Selected course');
});

test('faculty dashboard grading count matches the assignment review queue', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = Course::create([
        'title' => 'Assignment review course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module one',
    ]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson one',
    ]);
    $activeAssignment = LessonActivity::create([
        'lesson_id' => $lesson->id,
        'title' => 'Active assignment',
        'activity_type' => 'assignment',
        'is_active' => true,
    ]);
    $inactiveAssignment = LessonActivity::create([
        'lesson_id' => $lesson->id,
        'title' => 'Inactive assignment',
        'activity_type' => 'assignment',
        'is_active' => false,
    ]);
    $practiceActivity = LessonActivity::create([
        'lesson_id' => $lesson->id,
        'title' => 'Practice activity',
        'activity_type' => 'practice',
        'is_active' => true,
    ]);

    foreach ([$activeAssignment, $activeAssignment, $inactiveAssignment, $practiceActivity] as $index => $activity) {
        LessonActivitySubmission::create([
            'lesson_activity_id' => $activity->id,
            'user_id' => $student->id,
            'attempt_number' => $index + 1,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    $this->actingAs($faculty)
        ->get(route('faculty.dashboard'))
        ->assertOk()
        ->assertSee('2 to grade')
        ->assertSee(route('faculty.activities.reviews', $course->id), false)
        ->assertViewHas('courses', fn ($courses) => $courses->firstWhere('id', $course->id)?->to_grade === 2);

    $this->get(route('faculty.activities.reviews', $course->id))
        ->assertOk()
        ->assertViewHas('pendingCount', 2);
});

test('faculty analytics pass rate counts each learner once across quiz retakes', function () {
    $faculty = User::factory()->faculty()->create();
    $passedLearner = User::factory()->create();
    $notPassedLearner = User::factory()->create();
    $course = Course::create([
        'title' => 'Quiz analytics course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    $quizId = DB::table('quizzes')->insertGetId([
        'course_id' => $course->id,
        'title' => 'Course quiz',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([
        ['user_id' => $passedLearner->id, 'score' => 40, 'passed' => false],
        ['user_id' => $passedLearner->id, 'score' => 80, 'passed' => true],
        ['user_id' => $notPassedLearner->id, 'score' => 60, 'passed' => false],
    ] as $attempt) {
        DB::table('quiz_attempts')->insert([
            ...$attempt,
            'quiz_id' => $quizId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs($faculty)
        ->get(route('faculty.analytics', ['course_id' => $course->id]))
        ->assertOk()
        ->assertViewHas('courseAnalytics', fn ($courses) => $courses->first()->pass_rate === 50
            && $courses->first()->average_score === 60);
});
