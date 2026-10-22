<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
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

test('faculty analytics report reflects the selected dashboard course and current sections', function () {
    $faculty = User::factory()->faculty()->create();
    $course = Course::create([
        'title' => 'Report scope course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    $learner = User::factory()->create([
        'gender' => 'Female',
        'date_of_birth' => now()->subYears(22)->format('Y-m-d'),
    ]);
    Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
    ]);

    $dashboardResponse = $this->actingAs($faculty)
        ->get(route('faculty.analytics', ['course_id' => $course->id]))
        ->assertOk();

    $reportHtml = view('faculty.analytics-report-pdf', $dashboardResponse->viewData() + [
        'generatedAt' => now(),
        'preparedBy' => $faculty,
        'reportCourseTitle' => $course->title,
    ])->render();

    expect($reportHtml)
        ->toContain('Report scope course')
        ->toContain('Learner demographics')
        ->toContain('Age distribution')
        ->toContain('Gender distribution')
        ->toContain('18–24')
        ->toContain('Female')
        ->toContain('Course outcomes')
        ->toContain('Recent course evaluation comments')
        ->not->toContain('Recent Activity');

    $this->get(route('faculty.analytics.report', ['course_id' => $course->id]))
        ->assertOk()
        ->assertDownload('Faculty-Analytics-Report-'.now()->format('Y-m-d').'.pdf')
        ->assertHeader('content-type', 'application/pdf');
});

test('faculty analytics shows age and gender demographics for unique learners in scope', function () {
    $faculty = User::factory()->faculty()->create();
    $selectedCourse = Course::create([
        'title' => 'Demographics course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    $otherCourse = Course::create([
        'title' => 'Other demographics course',
        'slug' => Str::uuid()->toString(),
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_published' => false,
    ]);
    $youngLearner = User::factory()->create([
        'gender' => 'Female',
        'date_of_birth' => now()->subYears(22)->format('Y-m-d'),
    ]);
    $olderLearner = User::factory()->create([
        'gender' => 'Non-binary',
        'date_of_birth' => now()->subYears(60)->format('Y-m-d'),
    ]);

    foreach ([$selectedCourse, $otherCourse] as $course) {
        Enrollment::create([
            'user_id' => $youngLearner->id,
            'course_id' => $course->id,
        ]);
    }
    Enrollment::create([
        'user_id' => $olderLearner->id,
        'course_id' => $otherCourse->id,
    ]);

    $this->actingAs($faculty)
        ->get(route('faculty.analytics', ['course_id' => $selectedCourse->id]))
        ->assertOk()
        ->assertSee('Learner demographics')
        ->assertSee('Age distribution')
        ->assertSee('Gender distribution')
        ->assertDontSee('Live monitoring feed')
        ->assertViewHas('demographics', fn (array $demographics): bool => $demographics['total'] === 1
            && $demographics['age']['18–24'] === 1
            && $demographics['gender']['Female'] === 1);

    $this->get(route('faculty.analytics'))
        ->assertOk()
        ->assertViewHas('demographics', fn (array $demographics): bool => $demographics['total'] === 2
            && $demographics['age']['18–24'] === 1
            && $demographics['age']['55+'] === 1
            && $demographics['gender']['Female'] === 1
            && $demographics['gender']['Other'] === 1);
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
