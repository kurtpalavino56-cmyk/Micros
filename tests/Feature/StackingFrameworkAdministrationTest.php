<?php

use App\Models\Course;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeStackingAdminCourse(string $suffix, bool $microcredential = true): Course
{
    $faculty = User::factory()->faculty()->create();

    return Course::create([
        'title' => 'Microcredential '.$suffix,
        'slug' => 'microcredential-'.strtolower($suffix).'-'.uniqid(),
        'created_by' => $faculty->id,
        'is_published' => true,
        'approval_status' => 'approved',
        'is_approved' => true,
        'certificate_enabled' => $microcredential,
    ]);
}

function stackingFrameworkPayload(array $requirements = []): array
{
    return [
        'name' => 'Web Technology Stack',
        'description' => 'Approved sequence of microcredentials.',
        'pqf_level' => '5',
        'target_recognition' => 'Diploma pathway',
        'equivalent_course' => 'BSIT 301',
        'equivalent_units' => '6',
        'credit_equivalency' => 'Six academic units subject to recognition',
        'credit_recognition_conditions' => 'Registrar review remains separate.',
        'approving_academic_unit' => 'College of Computing',
        'sequence_required' => '1',
        'completion_mode' => 'all_required',
        'required_count' => collect($requirements)->where('is_required', 1)->count(),
        'requirements' => $requirements,
    ];
}

test('admin can view the stacking framework list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.stacking-frameworks'))
        ->assertOk()
        ->assertViewIs('admin.stacking-frameworks');
});

test('admin can create a draft with multiple ordered required and optional requirements', function () {
    $admin = User::factory()->admin()->create();
    $first = makeStackingAdminCourse('One');
    $second = makeStackingAdminCourse('Two');

    $response = $this->actingAs($admin)->post(route('admin.stacking-frameworks.store'), stackingFrameworkPayload([
        ['course_id' => $first->id, 'order' => 1, 'is_required' => 1],
        ['course_id' => $second->id, 'order' => 2, 'is_required' => 0],
    ]));

    $response->assertRedirect(route('admin.stacking-frameworks'));
    $framework = StackingFramework::with('requirements')->firstOrFail();

    expect($framework->status)->toBe('draft')
        ->and($framework->is_active)->toBeFalse()
        ->and($framework->completion_mode)->toBe('all_required')
        ->and($framework->required_count)->toBe(1)
        ->and($framework->requirements)->toHaveCount(2)
        ->and($framework->requirements[0]->course_id)->toBe($first->id)
        ->and($framework->requirements[0]->order)->toBe(1)
        ->and($framework->requirements[0]->is_required)->toBeTrue()
        ->and($framework->requirements[1]->course_id)->toBe($second->id)
        ->and($framework->requirements[1]->is_required)->toBeFalse();
});

test('framework creation validates required fields and numeric values', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.stacking-frameworks'))
        ->post(route('admin.stacking-frameworks.store'), [
            'equivalent_units' => 'six',
            'completion_mode' => 'minimum_required',
            'required_count' => -1,
        ])
        ->assertRedirect(route('admin.stacking-frameworks'))
        ->assertSessionHasErrors(['name', 'equivalent_units', 'required_count']);
});

test('completion model distinguishes all-required from minimum-of-required', function () {
    $admin = User::factory()->admin()->create();
    $first = makeStackingAdminCourse('All One');
    $second = makeStackingAdminCourse('All Two');

    $allPayload = stackingFrameworkPayload([
        ['course_id' => $first->id, 'order' => 1, 'is_required' => 1],
        ['course_id' => $second->id, 'order' => 2, 'is_required' => 1],
    ]);
    $allPayload['completion_mode'] = 'all_required';
    $allPayload['required_count'] = 1;

    $this->actingAs($admin)->post(route('admin.stacking-frameworks.store'), $allPayload)->assertRedirect();
    $all = StackingFramework::latest('id')->firstOrFail();

    expect($all->completion_mode)->toBe('all_required')
        ->and($all->required_count)->toBe(2);

    $minimumPayload = stackingFrameworkPayload([
        ['course_id' => $first->id, 'order' => 1, 'is_required' => 1],
        ['course_id' => $second->id, 'order' => 2, 'is_required' => 1],
    ]);
    $minimumPayload['completion_mode'] = 'minimum_required';
    $minimumPayload['required_count'] = 1;

    $this->actingAs($admin)->post(route('admin.stacking-frameworks.store'), $minimumPayload)->assertRedirect();
    $minimum = StackingFramework::latest('id')->firstOrFail();

    expect($minimum->completion_mode)->toBe('minimum_required')
        ->and($minimum->required_count)->toBe(1);
});

test('duplicate and non-microcredential course requirements are rejected', function () {
    $admin = User::factory()->admin()->create();
    $course = makeStackingAdminCourse('Duplicate');
    $ordinaryCourse = makeStackingAdminCourse('Ordinary', false);
    $ordinaryCourse->update(['certificate_enabled' => false]);

    $this->actingAs($admin)
        ->from(route('admin.stacking-frameworks'))
        ->post(route('admin.stacking-frameworks.store'), stackingFrameworkPayload([
            ['course_id' => $course->id, 'order' => 1, 'is_required' => 1],
            ['course_id' => $course->id, 'order' => 2, 'is_required' => 1],
        ]))
        ->assertRedirect(route('admin.stacking-frameworks'))
        ->assertSessionHasErrors('requirements');

    $this->actingAs($admin)
        ->from(route('admin.stacking-frameworks'))
        ->post(route('admin.stacking-frameworks.store'), stackingFrameworkPayload([
            ['course_id' => $ordinaryCourse->id, 'order' => 1, 'is_required' => 1],
        ]))
        ->assertRedirect(route('admin.stacking-frameworks'))
        ->assertSessionHasErrors('requirements');
});

test('a framework cannot be approved without requirements', function () {
    $admin = User::factory()->admin()->create();
    $framework = StackingFramework::create([
        'name' => 'Empty Framework',
        'status' => 'draft',
        'completion_mode' => 'all_required',
        'required_count' => 0,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stacking-frameworks'))
        ->post(route('admin.stacking-frameworks.approve', $framework->id))
        ->assertRedirect(route('admin.stacking-frameworks'))
        ->assertSessionHasErrors('required_count');

    expect($framework->fresh()->status)->toBe('draft')
        ->and($framework->fresh()->is_active)->toBeFalse();
});

test('admin can activate a valid draft directly without a separate submission step', function () {
    $admin = User::factory()->admin()->create();
    $course = makeStackingAdminCourse('Direct Activate');
    $framework = StackingFramework::create([
        'name' => 'Direct Activation Framework',
        'status' => 'draft',
        'completion_mode' => 'all_required',
        'required_count' => 1,
        'is_active' => false,
    ]);
    StackingFrameworkRequirement::create([
        'stacking_framework_id' => $framework->id,
        'course_id' => $course->id,
        'order' => 1,
        'is_required' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stacking-frameworks.approve', $framework->id))
        ->assertRedirect(route('admin.stacking-frameworks'));

    $fresh = $framework->fresh();
    expect($fresh->status)->toBe('approved')
        ->and($fresh->is_active)->toBeTrue()
        ->and($fresh->approved_by)->toBe($admin->id)
        ->and($fresh->approved_at)->not->toBeNull();
});

test('admin can approve a valid framework and no academic credit is created', function () {
    $admin = User::factory()->admin()->create();
    $course = makeStackingAdminCourse('Approved');
    $framework = StackingFramework::create([
        'name' => 'Approved Framework',
        'status' => 'pending_approval',
        'completion_mode' => 'all_required',
        'required_count' => 1,
        'is_active' => false,
    ]);
    StackingFrameworkRequirement::create([
        'stacking_framework_id' => $framework->id,
        'course_id' => $course->id,
        'order' => 1,
        'is_required' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stacking-frameworks.approve', $framework->id))
        ->assertRedirect(route('admin.stacking-frameworks'));

    $fresh = $framework->fresh();
    expect($fresh->status)->toBe('approved')
        ->and($fresh->is_active)->toBeTrue()
        ->and($fresh->approved_by)->toBe($admin->id)
        ->and($fresh->approved_at)->not->toBeNull()
        ->and(DB::table('academic_credit_recognitions')->count())->toBe(0);
});

test('approved framework structure cannot be edited without deactivation', function () {
    $admin = User::factory()->admin()->create();
    $framework = StackingFramework::create([
        'name' => 'Locked Framework',
        'status' => 'approved',
        'completion_mode' => 'all_required',
        'required_count' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stacking-frameworks'))
        ->patch(route('admin.stacking-frameworks.update', $framework->id), stackingFrameworkPayload())
        ->assertRedirect(route('admin.stacking-frameworks'))
        ->assertSessionHasErrors('framework');

    expect($framework->fresh()->name)->toBe('Locked Framework');
});

test('non-admin users cannot access framework administration', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get(route('admin.stacking-frameworks'))
        ->assertRedirect();
});
