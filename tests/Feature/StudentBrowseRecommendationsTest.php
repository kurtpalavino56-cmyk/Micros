<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPublishedBrowseCourse(string $title, array $attributes = []): Course
{
    return Course::create(array_merge([
        'title' => $title,
        'slug' => str($title)->slug().'-'.uniqid(),
        'is_published' => true,
    ], $attributes));
}

test('recommended filter shows no courses when the student has no profile skills', function () {
    $student = User::factory()->create();
    createPublishedBrowseCourse('General Course');

    $response = $this->actingAs($student)->get(route('courses.browse', ['recommended' => 1]));

    $response->assertOk()
        ->assertSee('No recommended courses yet.')
        ->assertDontSee('General Course');
});

test('recommended filter only shows courses matching the student profile skills', function () {
    $student = User::factory()->create([
        'skills_have' => ['data analysis'],
        'skills_want' => [],
    ]);
    createPublishedBrowseCourse('Recommended Course', [
        'related_skills' => ['Data Analysis'],
    ]);
    createPublishedBrowseCourse('Other Course', [
        'related_skills' => ['Graphic Design'],
    ]);

    $response = $this->actingAs($student)->get(route('courses.browse', ['recommended' => 1]));

    $response->assertOk()
        ->assertSee('Recommended Course')
        ->assertDontSee('Other Course');
});
