<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\User;
use App\Models\UserStackingProgress;
use App\Services\AcademicCreditRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeRecognitionCourse(string $name): Course
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
        'pqf_level' => '5',
    ]);
}

function makeRecognitionFramework(Course $course, array $attributes = []): StackingFramework
{
    $framework = StackingFramework::create(array_merge([
        'name' => 'Recognition Framework '.uniqid(),
        'status' => 'approved',
        'is_active' => true,
        'required_count' => 1,
        'equivalent_course' => 'BSIT 301',
        'equivalent_units' => 6,
        'credit_equivalency' => '6 units',
        'credit_recognition_conditions' => 'Registrar review required.',
        'sequence_required' => false,
    ], $attributes));

    StackingFrameworkRequirement::create([
        'stacking_framework_id' => $framework->id,
        'course_id' => $course->id,
        'order' => 1,
        'is_required' => true,
    ]);

    return $framework->fresh('requirements.course');
}

test('student cannot request recognition when stacking is in progress', function () {
    $user = User::factory()->create();
    $course = makeRecognitionCourse('In Progress Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'in_progress',
    ]);

    $service = app(AcademicCreditRecognitionService::class);

    expect($service->canRequestRecognition($user, $framework))->toBeFalse();
});

test('student can request recognition when stacking requirements are met', function () {
    $user = User::factory()->create();
    $course = makeRecognitionCourse('Ready Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);

    $service = app(AcademicCreditRecognitionService::class);

    expect($service->canRequestRecognition($user, $framework))->toBeTrue();
    $record = $service->requestRecognition($user, $framework, 'Student request');
    expect($record->status)->toBe('pending')
        ->and($record->user_id)->toBe($user->id)
        ->and($record->stacking_framework_id)->toBe($framework->id);
});

test('duplicate active recognition requests are prevented', function () {
    $user = User::factory()->create();
    $course = makeRecognitionCourse('Duplicate Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);

    $service = app(AcademicCreditRecognitionService::class);
    $service->requestRecognition($user, $framework);

    expect(fn () => $service->requestRecognition($user, $framework))
        ->toThrow(InvalidArgumentException::class, 'already has an active academic credit recognition');
});

test('pending to unit recommended and unit recommended to dean endorsed work', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $course = makeRecognitionCourse('Review Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);

    $service = app(AcademicCreditRecognitionService::class);
    $record = $service->requestRecognition($user, $framework);
    $record = $service->recommend($record->id, $admin->id, 'Recommended');

    expect($record->status)->toBe('unit_recommended')
        ->and($record->unit_reviewed_by)->toBe($admin->id);

    $record = $service->endorse($record->id, $admin->id, 'Approved by dean');

    expect($record->status)->toBe('dean_endorsed')
        ->and($record->dean_endorsed_by)->toBe($admin->id);
});

test('invalid transitions are rejected', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $course = makeRecognitionCourse('Invalid Transition Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);

    $service = app(AcademicCreditRecognitionService::class);
    $record = $service->requestRecognition($user, $framework);

    expect(fn () => $service->recordRegistrar($record->id, $admin->id))
        ->toThrow(InvalidArgumentException::class, 'Invalid transition');
});

test('denial preserves stacking progress and does not change completion status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $course = makeRecognitionCourse('Denied Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    $progress = UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);

    $service = app(AcademicCreditRecognitionService::class);
    $record = $service->requestRecognition($user, $framework);
    $service->deny($record->id, $admin->id, 'Insufficient evidence');

    expect($progress->fresh()->status)->toBe('requirements_met')
        ->and($record->fresh()->status)->toBe('denied')
        ->and($user->certificates()->count())->toBe(0);
});

test('student can view recognition status and unauthorized users cannot perform institutional actions', function () {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $course = makeRecognitionCourse('Status Course');
    $framework = makeRecognitionFramework($course);
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'completion_status' => 'completed',
        'progress_percent' => 100,
        'is_completed' => true,
        'completed_at' => now(),
    ]);
    UserStackingProgress::create([
        'user_id' => $user->id,
        'stacking_framework_id' => $framework->id,
        'status' => 'requirements_met',
    ]);
    $record = app(AcademicCreditRecognitionService::class)->requestRecognition($user, $framework);

    $this->actingAs($user)->get(route('stacking.progress'))->assertOk();
    $this->actingAs($user)->post(route('admin.academic-credit-recognition.recommend', $record->id))->assertRedirect();
    $this->actingAs($admin)->post(route('admin.academic-credit-recognition.recommend', $record->id))->assertRedirect();
});
