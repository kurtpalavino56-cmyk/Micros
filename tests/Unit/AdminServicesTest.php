<?php

use App\Models\Course;
use App\Models\Notification;
use App\Models\User;
use App\Services\AdminUserManagementService;
use App\Services\CourseModerationService;
use App\Services\UserCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAdminModerationCourse(array $attributes = []): Course
{
    return Course::create(array_merge([
        'title' => 'Moderation Course',
        'slug' => 'moderation-course-'.uniqid(),
        'approval_status' => 'pending',
        'is_approved' => false,
        'is_published' => false,
        'is_featured' => false,
    ], $attributes));
}

test('admin user service creates users with role-specific public codes', function () {
    $service = app(AdminUserManagementService::class);
    $user = $service->create([
        'first_name' => 'New',
        'last_name' => 'Faculty',
        'email' => 'new.faculty@example.com',
        'username' => 'new.faculty',
        'password' => 'Password123!',
        'role_id' => User::ROLE_FACULTY,
    ], app(UserCodeService::class));

    expect($user->role_id)->toBe(User::ROLE_FACULTY)
        ->and($user->user_code)->toMatch('/^'.now()->format('y').'-FC-\d{4}$/')
        ->and($user->student_id)->toBe($user->user_code)
        ->and($user->is_active)->toBeTrue();
});

test('admin user service builds user details including faculty course counts', function () {
    $faculty = User::factory()->faculty()->create([
        'first_name' => 'Course',
        'last_name' => 'Author',
        'student_id' => null,
    ]);
    Course::create([
        'title' => 'Authored Course',
        'slug' => 'authored-course-'.uniqid(),
        'created_by' => $faculty->id,
        'approval_status' => 'pending',
        'is_published' => false,
    ]);

    $detail = app(AdminUserManagementService::class)->detail($faculty->loadCount('enrollments'));

    expect($detail->name)->toBe('Course Author')
        ->and($detail->role)->toBe('Faculty')
        ->and($detail->student_id)->toBe($faculty->user_code)
        ->and($detail->courses_created)->toBe(1)
        ->and($detail->enrollments)->toBe(0)
        ->and($detail->skills_have)->toBe([])
        ->and($detail->skills_want)->toBe([]);
});

test('admin user service protects administrator and self deletion', function () {
    $service = app(AdminUserManagementService::class);
    $admin = User::factory()->admin()->create();
    $faculty = User::factory()->faculty()->create();

    $adminResult = $service->delete($admin, $faculty->id);
    $selfResult = $service->delete($faculty, $faculty->id);

    expect($adminResult)->toMatchArray([
        'deleted' => false,
        'message' => 'Administrator accounts cannot be deleted from this screen.',
    ])->and($selfResult)->toMatchArray([
        'deleted' => false,
        'message' => 'You cannot delete your own account.',
    ]);
    expect(User::find($admin->id))->not->toBeNull()
        ->and(User::find($faculty->id))->not->toBeNull();
});

test('admin user service deletes non-admin users and returns their display name', function () {
    $faculty = User::factory()->faculty()->create(['first_name' => 'Delete', 'last_name' => 'Me']);

    $result = app(AdminUserManagementService::class)->delete($faculty, 999999);

    expect($result)->toMatchArray(['deleted' => true, 'message' => 'Delete Me']);
    expect(User::find($faculty->id))->toBeNull();
});

test('course moderation approves and publishes a course', function () {
    $course = makeAdminModerationCourse();

    app(CourseModerationService::class)->approve($course, 42);
    $course = $course->fresh();

    expect($course->approval_status)->toBe('approved')
        ->and($course->is_approved)->toBeTrue()
        ->and($course->is_published)->toBeTrue()
        ->and($course->approved_by)->toBe(42)
        ->and($course->approved_at)->not->toBeNull();
});

test('course moderation denies a course and notifies its author', function () {
    $author = User::factory()->faculty()->create();
    $course = makeAdminModerationCourse(['created_by' => $author->id, 'title' => 'Denied Course']);

    app(CourseModerationService::class)->deny($course, '  Missing required information.  ', 7);
    $course = $course->fresh();

    expect($course->approval_status)->toBe('denied')
        ->and($course->is_approved)->toBeFalse()
        ->and($course->is_published)->toBeFalse()
        ->and($course->denial_feedback)->toBe('Missing required information.');
    expect(Notification::where('user_id', $author->id)->where('type', 'course')->first())
        ->not->toBeNull();
});

test('course moderation toggles publish and feature without conflating the two flags', function () {
    $course = makeAdminModerationCourse();
    $service = app(CourseModerationService::class);

    expect($service->toggleFeature($course))->toBeTrue();
    $course->refresh();
    expect($course->is_featured)->toBeTrue()
        ->and($course->approval_status)->toBe('pending');

    expect($service->togglePublish($course, 8))->toBeTrue();
    $course->refresh();
    expect($course->is_published)->toBeTrue()
        ->and($course->is_approved)->toBeTrue()
        ->and($course->approval_status)->toBe('approved')
        ->and($course->approved_by)->toBe(8);

    expect($service->togglePublish($course, 8))->toBeFalse();
    expect($course->fresh()->is_published)->toBeFalse();
});

test('course moderation deletes a course and returns its title', function () {
    $course = makeAdminModerationCourse(['title' => 'Remove This Course']);

    expect(app(CourseModerationService::class)->delete($course))->toBe('Remove This Course');
    expect(Course::find($course->id))->toBeNull();
});
