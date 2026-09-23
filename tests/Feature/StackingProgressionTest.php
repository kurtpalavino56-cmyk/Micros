<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\User;
use App\Models\UserStackingProgress;
use App\Services\MicrocredentialCompletionService;
use App\Services\StackingProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeProgressCourseFixture(string $name): Course
{
    $faculty = User::factory()->faculty()->create();

    return Course::create([
        'title' => $name,
        'slug' => str()->slug($name).'-'.uniqid(),
        'created_by' => $faculty->id,
        'is_published' => true,
        'approval_status' => 'approved',
        'is_approved' => true,
        'certificate_enabled' => true,
    ]);
}

function makeProgressFrameworkFixture(array $courses, array $attributes = [], array $required = []): StackingFramework
{
    $framework = StackingFramework::create(array_merge([
        'name' => 'Progress Framework '.uniqid(),
        'status' => 'approved',
        'is_active' => true,
        'required_count' => count($courses),
        'sequence_required' => false,
    ], $attributes));

    foreach ($courses as $index => $course) {
        StackingFrameworkRequirement::create([
            'stacking_framework_id' => $framework->id,
            'course_id' => $course->id,
            'order' => $index + 1,
            'is_required' => $required[$index] ?? true,
        ]);
    }

    return $framework->fresh('requirements.course');
}

function makeProgressEnrollmentFixture(User $student, Course $course, string $status, ?int $completedAt = null): Enrollment
{
    return Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'progress_percent' => 100,
        'is_completed' => true,
        'completion_status' => $status,
        'completed_at' => $completedAt ? now()->setTimestamp($completedAt) : null,
    ]);
}

test('active framework progress counts only official completion and is idempotent', function () {
    $student = User::factory()->create();
    $first = makeProgressCourseFixture('First Microcredential');
    $second = makeProgressCourseFixture('Second Microcredential');
    $framework = makeProgressFrameworkFixture([$first, $second]);
    makeProgressEnrollmentFixture($student, $first, MicrocredentialCompletionService::STATUS_COMPLETED, now()->subMinute()->timestamp);
    makeProgressEnrollmentFixture($student, $second, MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION);

    $service = app(StackingProgressService::class);
    $firstResult = $service->syncFrameworkForUser($framework, $student->id);
    $secondResult = $service->syncFrameworkForUser($framework, $student->id);

    expect($firstResult->status)->toBe('in_progress')
        ->and($secondResult->id)->toBe($firstResult->id)
        ->and(UserStackingProgress::where('user_id', $student->id)->where('stacking_framework_id', $framework->id)->count())->toBe(1);

    $progress = $service->progressForUser($student->id)->first();
    expect($progress['completed_required_count'])->toBe(1)
        ->and($progress['total_required_count'])->toBe(2)
        ->and($progress['remaining_required_count'])->toBe(1)
        ->and($progress['completion_percentage'])->toBe(50);
});

test('draft pending and inactive frameworks do not generate student progress', function () {
    $student = User::factory()->create();
    $course = makeProgressCourseFixture('Eligible Course');
    makeProgressEnrollmentFixture($student, $course, MicrocredentialCompletionService::STATUS_COMPLETED);

    foreach ([['draft', false], ['pending_approval', false], ['inactive', false]] as [$status, $active]) {
        $framework = makeProgressFrameworkFixture([$course], ['status' => $status, 'is_active' => $active]);
        app(StackingProgressService::class)->syncFrameworkForUser($framework, $student->id);
    }

    expect(UserStackingProgress::where('user_id', $student->id)->count())->toBe(0);
});

test('optional requirements do not block requirements met', function () {
    $student = User::factory()->create();
    $required = makeProgressCourseFixture('Required Course');
    $optional = makeProgressCourseFixture('Optional Course');
    $framework = makeProgressFrameworkFixture([$required, $optional], ['required_count' => 1], [true, false]);
    makeProgressEnrollmentFixture($student, $required, MicrocredentialCompletionService::STATUS_COMPLETED);
    makeProgressEnrollmentFixture($student, $optional, MicrocredentialCompletionService::STATUS_IN_PROGRESS);

    $progress = app(StackingProgressService::class)->progressForUser($student->id)->first();

    expect($progress['status'])->toBe('requirements_met')
        ->and($progress['total_required_count'])->toBe(1)
        ->and($progress['requirements']->last()['is_completed'])->toBeFalse();
});

test('required requirements block requirements met', function () {
    $student = User::factory()->create();
    $first = makeProgressCourseFixture('Required One');
    $second = makeProgressCourseFixture('Required Two');
    $framework = makeProgressFrameworkFixture([$first, $second]);
    makeProgressEnrollmentFixture($student, $first, MicrocredentialCompletionService::STATUS_COMPLETED);

    $progress = app(StackingProgressService::class)->progressForUser($student->id)->first();

    expect($progress['status'])->toBe('in_progress');
});

test('all-required frameworks ignore a lower legacy required count', function () {
    $student = User::factory()->create();
    $first = makeProgressCourseFixture('Legacy Count One');
    $second = makeProgressCourseFixture('Legacy Count Two');
    $framework = makeProgressFrameworkFixture([$first, $second], ['completion_mode' => 'all_required', 'required_count' => 1]);

    makeProgressEnrollmentFixture($student, $first, MicrocredentialCompletionService::STATUS_COMPLETED);

    $progress = app(StackingProgressService::class)->progressForUser($student->id)->firstWhere('framework.id', $framework->id);

    expect($progress['completion_mode'])->toBe('all_required')
        ->and($progress['completed_required_count'])->toBe(1)
        ->and($progress['total_required_count'])->toBe(2)
        ->and($progress['target_required_count'])->toBe(2)
        ->and($progress['completion_percentage'])->toBe(50)
        ->and($progress['status'])->toBe('in_progress');
});

test('sequence false accepts any completion order and sequence true enforces order', function () {
    $student = User::factory()->create();
    $first = makeProgressCourseFixture('Sequence One');
    $second = makeProgressCourseFixture('Sequence Two');
    $unordered = makeProgressFrameworkFixture([$first, $second]);
    $ordered = makeProgressFrameworkFixture([$first, $second], ['sequence_required' => true]);
    $firstEnrollment = makeProgressEnrollmentFixture($student, $first, MicrocredentialCompletionService::STATUS_COMPLETED, now()->timestamp);
    $secondEnrollment = makeProgressEnrollmentFixture($student, $second, MicrocredentialCompletionService::STATUS_COMPLETED, now()->subMinute()->timestamp);

    $service = app(StackingProgressService::class);
    expect($service->progressForUser($student->id)->firstWhere('framework.id', $unordered->id)['status'])->toBe('requirements_met')
        ->and($service->progressForUser($student->id)->firstWhere('framework.id', $ordered->id)['status'])->toBe('in_progress');

    $firstEnrollment->update(['completed_at' => now()->subMinutes(2)]);
    $secondEnrollment->update(['completed_at' => now()->subMinute()]);

    expect($service->progressForUser($student->id)->firstWhere('framework.id', $ordered->id)['status'])->toBe('requirements_met');
});

test('official completion triggers stacking progress without academic credit recognition', function () {
    $student = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $course = makeProgressCourseFixture('Official Completion Course');
    $framework = makeProgressFrameworkFixture([$course]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'progress_percent' => 100,
        'is_completed' => false,
        'completion_status' => 'in_progress',
    ]);

    app(MicrocredentialCompletionService::class)->recordAcademicUnitConfirmation($enrollment, $admin->id, 'confirmed');

    expect($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(UserStackingProgress::where('user_id', $student->id)->where('stacking_framework_id', $framework->id)->value('status'))->toBe('requirements_met')
        ->and(DB::table('academic_credit_recognitions')->count())->toBe(0);
});

test('student sees only relevant active stacking frameworks', function () {
    $student = User::factory()->create(['profile_completed' => true]);
    $enrolledCourse = makeProgressCourseFixture('Relevant Course');
    $unrelatedCourse = makeProgressCourseFixture('Unrelated Course');
    makeProgressFrameworkFixture([$enrolledCourse]);
    makeProgressFrameworkFixture([$unrelatedCourse]);
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $enrolledCourse->id,
        'completion_status' => 'in_progress',
        'progress_percent' => 0,
    ]);

    $response = $this->actingAs($student)->get(route('stacking.progress'));

    $response->assertOk()->assertViewIs('student.stacking-progress');
    expect($response->viewData('frameworks'))->toHaveCount(1);
});

test('non-students cannot access student stacking progress', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('stacking.progress'))->assertRedirect();
});

test('stacking progress reaches 100 percent and zero remaining when the configured n-of-m target is met', function () {
    $student = User::factory()->create();
    $first = makeProgressCourseFixture('N of M One');
    $second = makeProgressCourseFixture('N of M Two');
    $third = makeProgressCourseFixture('N of M Three');
    $framework = makeProgressFrameworkFixture([$first, $second, $third], ['completion_mode' => 'minimum_required', 'required_count' => 2]);

    makeProgressEnrollmentFixture($student, $first, MicrocredentialCompletionService::STATUS_COMPLETED);
    makeProgressEnrollmentFixture($student, $second, MicrocredentialCompletionService::STATUS_COMPLETED);
    makeProgressEnrollmentFixture($student, $third, MicrocredentialCompletionService::STATUS_IN_PROGRESS);

    $progress = app(StackingProgressService::class)->progressForUser($student->id)->firstWhere('framework.id', $framework->id);

    expect($progress['status'])->toBe('requirements_met')
        ->and($progress['completed_required_count'])->toBe(2)
        ->and($progress['target_required_count'])->toBe(2)
        ->and($progress['remaining_required_count'])->toBe(0)
        ->and($progress['completion_percentage'])->toBe(100);
});
