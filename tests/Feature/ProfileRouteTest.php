<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profile route uses the authenticated user data', function () {
    $this->seed();

    $user = User::query()->where('email', 'student@example.com')->firstOrFail();
    $this->actingAs($user);

    $response = $this->get('/profile');

    $response->assertOk();
    $response->assertViewHas('user', function ($viewUser) use ($user) {
        return $viewUser->name === trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
            && $viewUser->email === $user->email;
    });
});
