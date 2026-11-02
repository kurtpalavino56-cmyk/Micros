<?php

use App\Models\User;

test('opening the profile completion endpoint redirects to onboarding', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get('/profile/complete')
        ->assertRedirect(route('onboarding.show'));
});

test('onboarding renders the cascading address dropdowns', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('id="address_province"', false)
        ->assertSee('id="address_city"', false)
        ->assertSee('id="address_barangay"', false)
        ->assertSee('id="career_goal"', false)
        ->assertSee('id="career_goal_other"', false)
        ->assertSee('name="main_goal"', false)
        ->assertSee('id="main_goal_other"', false)
        ->assertSee('What is your reason for taking microcredentials?')
        ->assertSee('What is your main goal?');
});

test('onboarding saves the selected province city and barangay as the profile address', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'address_barangay' => 'Alos',
            'career_goal' => 'Software Engineer',
            'main_goal' => 'Advance my career',
        ])
        ->assertRedirect(route('dashboard'));

    expect($student->fresh()->address)->toBe('Alos, City of Alaminos, Pangasinan');
});

test('onboarding saves a custom career goal when other is selected', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'address_barangay' => 'Alos',
            'career_goal' => 'Other',
            'career_goal_other' => 'Architect',
            'main_goal' => 'Advance my career',
        ])
        ->assertRedirect(route('dashboard'));

    expect($student->fresh()->career_goal)->toBe('Architect');
});

test('onboarding saves a custom main goal when other is selected', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'address_barangay' => 'Alos',
            'career_goal' => 'Software Engineer',
            'main_goal' => 'Other',
            'main_goal_other' => 'Prepare to launch a local business',
        ])
        ->assertRedirect(route('dashboard'));

    expect($student->fresh()->main_goal)->toBe('Prepare to launch a local business');
});

test('onboarding requires all address selections', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->from(route('onboarding.show'))
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'career_goal' => 'Software Engineer',
            'main_goal' => 'Advance my career',
        ])
        ->assertSessionHasErrors('address_barangay');
});

test('onboarding requires a custom career goal when other is selected', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->from(route('onboarding.show'))
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'address_barangay' => 'Alos',
            'career_goal' => 'Other',
            'main_goal' => 'Advance my career',
        ])
        ->assertSessionHasErrors('career_goal_other');
});

test('onboarding requires a custom main goal when other is selected', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->from(route('onboarding.show'))
        ->post(route('profile.complete'), [
            'date_of_birth' => '2000-01-01',
            'gender' => 'Prefer not to say',
            'education' => "Bachelor's Degree",
            'school' => 'Pangasinan State University',
            'address_province' => 'Pangasinan',
            'address_city' => 'City of Alaminos',
            'address_barangay' => 'Alos',
            'career_goal' => 'Software Engineer',
            'main_goal' => 'Other',
        ])
        ->assertSessionHasErrors('main_goal_other');
});
