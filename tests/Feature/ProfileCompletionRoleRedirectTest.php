<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin users are redirected to the admin dashboard after login', function () {
    $user = User::factory()->create([
        'email' => 'admin-redirect@example.com',
        'password' => Hash::make('password'),
        'role_id' => 1,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
});

test('faculty users are redirected to the faculty dashboard after login', function () {
    $user = User::factory()->create([
        'email' => 'faculty-redirect@example.com',
        'password' => Hash::make('password'),
        'role_id' => 2,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('faculty.dashboard'));
});

test('student users are redirected to the student dashboard after login', function () {
    $user = User::factory()->create([
        'email' => 'student-redirect@example.com',
        'password' => Hash::make('password'),
        'role_id' => 3,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
});

test('faculty users are redirected away from admin routes', function () {
    $user = User::factory()->create([
        'email' => 'faculty-route@example.com',
        'password' => Hash::make('password'),
        'role_id' => 2,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('faculty.dashboard'));
});
