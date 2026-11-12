<?php

use App\Models\Course;
use App\Models\User;
use App\Services\CourseReadinessService;
use App\Services\LearningOutcomeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('course creation requires a badge name before saving', function () {
    $faculty = User::factory()->faculty()->create();

    $response = $this->actingAs($faculty)->post(route('faculty.create.store'), [
        'title' => 'Course with an unnamed badge',
        'short_description' => 'A course summary.',
        'badge_enabled' => '1',
        'badge_name' => '',
    ]);

    $response->assertSessionHasErrors('badge_name');
    expect(Course::query()->count())->toBe(0);
});

test('course creation rolls back if learning outcomes fail to save', function () {
    $faculty = User::factory()->faculty()->create();
    $learningOutcomes = Mockery::mock(LearningOutcomeService::class);
    $learningOutcomes->shouldReceive('sync')
        ->once()
        ->andThrow(new RuntimeException('Outcome storage failed.'));
    $this->app->instance(LearningOutcomeService::class, $learningOutcomes);

    $response = $this->actingAs($faculty)->post(route('faculty.create.store'), [
        'title' => 'Course with a failed outcome',
        'short_description' => 'A course summary.',
        'learning_outcomes_content' => '<p>Outcome</p>',
    ]);

    $response->assertSessionHasErrors('create');
    expect(Course::query()->count())->toBe(0);
});

test('course creation parses the related skills mirror consistently', function () {
    $faculty = User::factory()->faculty()->create();

    $this->actingAs($faculty)->post(route('faculty.create.store'), [
        'title' => 'Course with related skills',
        'short_description' => 'A course summary.',
        'related_skills_csv' => 'Web Development||Databases',
    ])->assertRedirect();

    expect(Course::query()->firstOrFail()->related_skills)
        ->toBe(['Web Development', 'Databases']);
});

test('editing a course preserves legacy fields and its certificate signature name', function () {
    $faculty = User::factory()->faculty()->create();
    $prerequisite = Course::create([
        'title' => 'Saved Prerequisite',
        'slug' => 'saved-prerequisite',
        'created_by' => $faculty->id,
        'is_approved' => false,
        'is_published' => false,
    ]);
    $course = Course::create([
        'title' => 'Existing Course',
        'short_description' => 'Existing summary.',
        'slug' => 'existing-course',
        'created_by' => $faculty->id,
        'category' => 'Inactive Category',
        'program' => 'Legacy Program',
        'objectives' => ['Legacy objective'],
        'certificate_signature_name' => 'Elbert M Galas',
        'prerequisite_ids' => [$prerequisite->id],
        'approval_status' => 'approved',
        'is_approved' => true,
        'is_published' => true,
    ]);

    $this->actingAs($faculty)->get(route('faculty.courses.edit', $course->id))
        ->assertOk()
        ->assertSee('Inactive Category')
        ->assertSee('Saved Prerequisite');

    $this->actingAs($faculty)->patch(route('faculty.courses.update', $course->id), [
        'title' => 'Updated Course',
        'short_description' => 'Updated summary.',
        'category' => 'Inactive Category',
        'level' => 'Beginner',
        'prerequisite_ids' => [$prerequisite->id],
        'related_skills_csv' => 'Databases||Web Development',
    ])->assertRedirect(route('faculty.courses.manage', $course->id));

    $course->refresh();

    expect($course->program)->toBe('Legacy Program')
        ->and($course->objectives)->toBe(['Legacy objective'])
        ->and($course->certificate_signature_name)->toBe('Elbert M Galas')
        ->and($course->approval_status)->toBe('draft')
        ->and($course->is_approved)->toBeFalse()
        ->and($course->is_published)->toBeFalse()
        ->and($course->prerequisite_ids)->toBe([$prerequisite->id])
        ->and($course->related_skills)->toBe(['Databases', 'Web Development']);
});

test('review notes are entered in the submit-for-approval modal', function () {
    $faculty = User::factory()->faculty()->create();
    $course = Course::create([
        'title' => 'Course awaiting submission',
        'short_description' => 'A course summary.',
        'slug' => 'course-awaiting-submission',
        'created_by' => $faculty->id,
        'approval_status' => 'draft',
        'is_approved' => false,
        'is_published' => false,
    ]);
    $manageUrl = route('faculty.courses.manage', $course->id);

    $this->actingAs($faculty)->get(route('faculty.courses.edit', $course->id))
        ->assertOk()
        ->assertDontSee('Review notes')
        ->assertDontSee('name="change_note"', false);

    $this->get($manageUrl)->assertOk()
        ->assertSee('id="course-approval-dialog"', false)
        ->assertSee('aria-haspopup="dialog"', false)
        ->assertSee('name="change_note"', false);

    $this->from($manageUrl)->post(route('faculty.courses.submit-for-approval', $course->id), [
        'change_note' => str_repeat('a', 2001),
    ])->assertRedirect($manageUrl)->assertSessionHasErrors('change_note');
    $this->get($manageUrl)->assertOk()
        ->assertSee("document.getElementById('course-approval-dialog').showModal()", false);

    $courseReadiness = Mockery::mock(CourseReadinessService::class);
    $courseReadiness->shouldReceive('missingRequirements')->once()->andReturn([]);
    $this->app->instance(CourseReadinessService::class, $courseReadiness);

    $this->post(route('faculty.courses.submit-for-approval', $course->id), [
        'change_note' => 'Updated lessons and added an assessment.',
    ])->assertRedirect($manageUrl);

    expect($course->fresh()->approval_status)->toBe('pending')
        ->and($course->fresh()->change_note)->toBe('Updated lessons and added an assessment.');
});

test('an approved but unpublished course is not labeled published', function () {
    $course = Course::create([
        'title' => 'Unpublished Approved Course',
        'slug' => 'unpublished-approved-course',
        'is_approved' => true,
        'is_published' => false,
        'approval_status' => 'approved',
    ]);

    expect($course->statusLabel())->toBe('Draft');
});
