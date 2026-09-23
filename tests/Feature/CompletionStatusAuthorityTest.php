<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student dashboard counts only officially completed enrollments', function () {
    $student = User::factory()->create(['profile_completed' => true]);
    $faculty = User::factory()->faculty()->create();

    $officialCourse = Course::create([
        'title' => 'Official Course',
        'slug' => 'official-course-'.uniqid(),
        'created_by' => $faculty->id,
        'is_published' => true,
    ]);
    $pendingCourse = Course::create([
        'title' => 'Pending Course',
        'slug' => 'pending-course-'.uniqid(),
        'created_by' => $faculty->id,
        'is_published' => true,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $officialCourse->id,
        'progress_percent' => 100,
        'is_completed' => false,
        'completion_status' => MicrocredentialCompletionService::STATUS_COMPLETED,
    ]);
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $pendingCourse->id,
        'progress_percent' => 100,
        'is_completed' => true,
        'completion_status' => MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION,
    ]);

    $response = $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk();

    $stats = $response->viewData('stats');

    expect($stats['completed'])->toBe(1)
        ->and($stats['active_courses'])->toBe(1);
});
