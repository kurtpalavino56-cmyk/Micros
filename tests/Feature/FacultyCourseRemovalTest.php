<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCourseForFacultyRemoval(User $faculty, string $title = 'Course Removal Test'): Course
{
    return Course::create([
        'title' => $title,
        'slug' => str()->slug($title).'-'.uniqid(),
        'created_by' => $faculty->id,
        'is_published' => true,
        'approval_status' => 'approved',
        'is_approved' => true,
    ]);
}

function enrollStudentForFacultyRemoval(Course $course, User $student): Enrollment
{
    return Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 35,
    ]);
}

test('faculty cannot remove a student without a reason', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = makeCourseForFacultyRemoval($faculty);
    $enrollment = enrollStudentForFacultyRemoval($course, $student);

    $this->actingAs($faculty)
        ->post(route('faculty.students.enrollments.remove', [
            'id' => $course->id,
            'enrollment' => $enrollment->id,
        ]), ['reason' => ''])
        ->assertSessionHasErrors('reason');

    expect(Enrollment::query()->whereKey($enrollment->id)->exists())->toBeTrue()
        ->and(Notification::query()->where('user_id', $student->id)->exists())->toBeFalse();
});

test('faculty can remove an enrolled student with a reason and the student can re-enroll', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create(['profile_completed' => true]);
    $course = makeCourseForFacultyRemoval($faculty);
    $enrollment = enrollStudentForFacultyRemoval($course, $student);
    $reason = 'Repeatedly missed required course activities.';

    $this->actingAs($faculty)
        ->post(route('faculty.students.enrollments.remove', [
            'id' => $course->id,
            'enrollment' => $enrollment->id,
        ]), ['reason' => $reason])
        ->assertRedirect(route('faculty.students.course', ['id' => $course->id, 'filter' => 'all']));

    expect(Enrollment::query()->whereKey($enrollment->id)->exists())->toBeFalse()
        ->and((int) $course->fresh()->enrolled_count)->toBe(0);

    $notification = Notification::query()->where('user_id', $student->id)->firstOrFail();
    expect($notification->message)->toContain($reason)
        ->and($notification->entity_type)->toBe('course_removal')
        ->and((int) $notification->entity_id)->toBe($course->id);

    $notificationUrl = app(UserNotificationService::class)->feed($student)->first()->url;
    expect($notificationUrl)->toBe(route('courses.show', [
        'id' => $course->id,
        'removal_notice' => $notification->id,
    ]));

    $this->actingAs($student)
        ->get($notificationUrl)
        ->assertOk()
        ->assertSee($reason);

    $this->post(route('courses.enroll', $course->id))->assertRedirect(route('courses.learn', $course->id));

    expect(Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->count())->toBe(1);
});

test('faculty student roster supports search, alphabetical sorting, and removal actions only in all students', function () {
    $faculty = User::factory()->faculty()->create();
    $course = makeCourseForFacultyRemoval($faculty);
    $zoe = User::factory()->create(['first_name' => 'Zoe', 'last_name' => 'Zulu']);
    $amy = User::factory()->create(['first_name' => 'Amy', 'last_name' => 'Able']);
    enrollStudentForFacultyRemoval($course, $zoe);
    enrollStudentForFacultyRemoval($course, $amy);

    $allStudentsResponse = $this->actingAs($faculty)
        ->get(route('faculty.students.course', [
            'id' => $course->id,
            'filter' => 'all',
            'sort' => 'name_desc',
        ]));

    $allStudentsResponse
        ->assertOk()
        ->assertSee('Name Z–A')
        ->assertSee('Remove')
        ->assertDontSee('>Review</a>', false)
        ->assertSeeInOrder(['Zoe Zulu', 'Amy Able']);

    $this->get(route('faculty.students.course', [
        'id' => $course->id,
        'filter' => 'all',
        'q' => 'Amy',
    ]))
        ->assertOk()
        ->assertSee('Amy Able')
        ->assertDontSee('Zoe Zulu');

    $reviewFaculty = User::factory()->faculty()->create();
    $reviewCourse = makeCourseForFacultyRemoval($reviewFaculty, 'Verification Test');
    $reviewCourse->requires_faculty_verification = true;
    $reviewCourse->save();
    $waitingStudent = User::factory()->create();
    $waitingEnrollment = Enrollment::create([
        'user_id' => $waitingStudent->id,
        'course_id' => $reviewCourse->id,
        'enrolled_at' => now(),
        'completion_status' => 'awaiting_faculty_verification',
        'faculty_verification_status' => 'pending',
        'progress_percent' => 100,
    ]);

    $this->actingAs($reviewFaculty)
        ->get(route('faculty.students.course', [
            'id' => $reviewCourse->id,
            'filter' => 'needs_review',
        ]))
        ->assertOk()
        ->assertSee('Review')
        ->assertSee(route('faculty.students.enrollments.review', [
            'id' => $reviewCourse->id,
            'enrollment' => $waitingEnrollment->id,
        ]), false)
        ->assertDontSee('student-remove-button');
});
