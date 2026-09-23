<?php

use App\Models\User;

it('assigns a unique public user code when a learner registers', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'Learner',
        'email' => 'learner.test@example.com',
        'username' => 'learner.test',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'learner.test@example.com')->firstOrFail();

    $this->assertNotNull($user->user_code);
    $this->assertMatchesRegularExpression('/^'.now()->format('y').'-LN-\d{4}$/', $user->user_code);
    $this->assertSame(1, User::where('user_code', $user->user_code)->count());
});

it('assigns a role-based user code when an admin creates a user', function () {
    $response = $this->post(route('admin.users.store'), [
        'first_name' => 'Test',
        'last_name' => 'Faculty',
        'email' => 'faculty.create@example.com',
        'username' => 'faculty.create',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role_id' => 2,
    ]);

    $response->assertRedirect();

    $user = User::where('email', 'faculty.create@example.com')->firstOrFail();

    $this->assertNotNull($user->user_code);
    $this->assertMatchesRegularExpression('/^'.now()->format('y').'-FC-\d{4}$/', $user->user_code);
    $this->assertSame(1, User::where('user_code', $user->user_code)->count());
});
