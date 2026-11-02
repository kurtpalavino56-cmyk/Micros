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

test('profile shows the saved onboarding address below education', function () {
    $user = User::factory()->create([
        'address' => 'Alos, City of Alaminos, Pangasinan',
        'education' => "Bachelor's Degree",
    ]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Alos, City of Alaminos, Pangasinan');

    $content = $response->getContent();
    expect(strpos($content, 'Education'))->toBeLessThan(strpos($content, 'Address'));
});
