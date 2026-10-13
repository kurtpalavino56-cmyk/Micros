<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profile updates cannot change the email address or role title', function () {
    $profiles = [
        [
            'user' => User::factory()->create([
                'email' => 'student-profile@example.com',
                'role_label' => 'Student title',
                'role_id' => User::ROLE_STUDENT,
            ]),
            'expected_email' => 'student-profile@example.com',
            'expected_role_title' => 'Student title',
            'route' => 'profile.update',
            'payload' => [
                'name' => 'Updated Student',
                'email' => 'changed-student@example.com',
                'role' => 'Administrator',
            ],
        ],
        [
            'user' => User::factory()->faculty()->create([
                'email' => 'faculty-profile@example.com',
                'role_label' => 'Faculty title',
            ]),
            'expected_email' => 'faculty-profile@example.com',
            'expected_role_title' => 'Faculty title',
            'route' => 'faculty.profile.update',
            'payload' => [
                'name' => 'Updated Faculty',
                'email' => 'changed-faculty@example.com',
                'role' => 'Administrator',
            ],
        ],
        [
            'user' => User::factory()->admin()->create([
                'email' => 'admin-profile@example.com',
                'role_label' => 'Admin title',
            ]),
            'expected_email' => 'admin-profile@example.com',
            'expected_role_title' => 'Admin title',
            'route' => 'admin.profile.update',
            'payload' => [
                'first_name' => 'Updated',
                'last_name' => 'Admin',
                'username' => 'updated-admin',
                'email' => 'changed-admin@example.com',
                'role' => 'Student',
            ],
        ],
    ];

    foreach ($profiles as $profile) {
        $this->actingAs($profile['user'])
            ->patch(route($profile['route']), $profile['payload'])
            ->assertRedirect();

        $profile['user']->refresh();

        expect($profile['user']->email)->toBe($profile['expected_email'])
            ->and($profile['user']->role_label)->toBe($profile['expected_role_title']);
    }
});
