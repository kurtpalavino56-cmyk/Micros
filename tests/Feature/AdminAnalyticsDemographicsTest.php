<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin analytics shows aggregate age and gender counts for the selected course', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::create([
        'title' => 'Demographics Course',
        'slug' => 'demographics-course',
        'category' => 'Information Technology',
        'level' => 'Intermediate',
    ]);
    $sameCategoryCourse = Course::create([
        'title' => 'Second IT Course',
        'slug' => 'second-it-course',
        'category' => 'Information Technology',
        'level' => 'Beginner',
    ]);
    $otherCategoryCourse = Course::create([
        'title' => 'Business Course',
        'slug' => 'business-course',
        'category' => 'Business',
        'level' => 'Beginner',
    ]);
    $femaleLearner = User::factory()->create([
        'date_of_birth' => now()->subYears(26)->format('Y-m-d'),
        'gender' => 'Female',
    ]);
    $secondFemaleLearner = User::factory()->create([
        'date_of_birth' => now()->subYears(26)->format('Y-m-d'),
        'gender' => 'Female',
    ]);
    $maleLearner = User::factory()->create([
        'date_of_birth' => now()->subYears(20)->format('Y-m-d'),
        'gender' => 'Male',
    ]);
    $faculty = User::factory()->faculty()->create([
        'date_of_birth' => now()->subYears(35)->format('Y-m-d'),
        'gender' => 'Female',
    ]);
    $sameCategoryLearner = User::factory()->create([
        'date_of_birth' => now()->subYears(19)->format('Y-m-d'),
        'gender' => 'Prefer not to say',
    ]);
    $otherCategoryLearner = User::factory()->create([
        'date_of_birth' => now()->subYears(42)->format('Y-m-d'),
        'gender' => 'Male',
    ]);

    foreach ([$femaleLearner, $secondFemaleLearner, $maleLearner, $faculty] as $user) {
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);
    }
    Enrollment::create([
        'user_id' => $sameCategoryLearner->id,
        'course_id' => $sameCategoryCourse->id,
        'enrolled_at' => now(),
    ]);
    Enrollment::create([
        'user_id' => $otherCategoryLearner->id,
        'course_id' => $otherCategoryCourse->id,
        'enrolled_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.report', ['course' => $course->id]))
        ->assertOk()
        ->assertSee('Learner Demographics')
        ->assertSee('Age distribution')
        ->assertSee('Gender distribution')
        ->assertSee('conic-gradient', false)
        ->assertDontSee('demographics-course-filter');

    $response->assertViewHas('demographics', function ($demographics) {
        return $demographics['total'] === 3
            && $demographics['age']['25–34'] === 2
            && $demographics['age']['18–24'] === 1
            && $demographics['gender']['Female'] === 2
            && $demographics['gender']['Male'] === 1;
    });

    $pdf = view('admin.analytics-report-pdf', $response->viewData() + [
        'generatedAt' => now(),
        'preparedBy' => $admin,
    ])->render();

    expect($pdf)
        ->toContain('Learner demographics')
        ->toContain('25–34')
        ->toContain('Female')
        ->not->toContain('Competency mastery');

    $categoryResponse = $this->actingAs($admin)
        ->get(route('admin.report', ['category' => 'Information Technology']))
        ->assertOk();

    $categoryResponse->assertViewHas('demographics', function ($demographics) {
        return $demographics['total'] === 4
            && $demographics['age']['25–34'] === 2
            && $demographics['age']['18–24'] === 2
            && $demographics['gender']['Female'] === 2
            && $demographics['gender']['Male'] === 1
            && $demographics['gender']['Prefer not to say'] === 1;
    });

    $categoryPdf = view('admin.analytics-report-pdf', $categoryResponse->viewData() + [
        'generatedAt' => now(),
        'preparedBy' => $admin,
    ])->render();

    expect($categoryPdf)
        ->toContain('Category: Information Technology')
        ->toContain('Course: All')
        ->toContain('Course enrollments in selected scope: 4');

    $this->actingAs($admin)
        ->get(route('admin.report.download', ['category' => 'Information Technology']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
