<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function registerTermsPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Terms',
        'last_name' => 'Tester',
        'email' => 'terms.tester@example.com',
        'username' => 'terms.tester',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ], $overrides);
}

test('students cannot register without agreeing to the terms and conditions', function () {
    $payload = registerTermsPayload();
    unset($payload['terms']);

    $this->from(route('register'))
        ->post(route('register.store'), $payload)
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('terms');

    expect(User::where('email', 'terms.tester@example.com')->exists())->toBeFalse();
});

test('students can register after agreeing to the terms and conditions', function () {
    $this->post(route('register.store'), registerTermsPayload())
        ->assertRedirect(route('dashboard'));

    expect(User::where('email', 'terms.tester@example.com')->exists())->toBeTrue();
});

test('the sign-up form shows the agreement checkbox, a locked sign up button, and the shared terms and privacy text', function () {
    $html = $this->get(route('register'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-terms-checkbox')
        ->toContain('I have read and agree to the')
        ->toContain('of UPSKILL')
        ->not->toContain('operated by Pangasinan State University')
        ->toContain('id="termsModal"')
        ->toContain('data-terms-pane="terms"')
        ->toContain('data-terms-next')
        ->toContain('data-terms-pane="privacy"')
        ->toContain('data-terms-agree')
        ->toContain('data-terms-jump="up"')
        ->toContain('data-terms-jump="down"')
        ->toContain('1. Acceptance of Terms')
        ->toContain('21. Acknowledgment and Agreement')
        ->toContain('Information We Collect')
        ->toContain('Changes to This Privacy Policy')
        ->toMatch('/<button type="submit" class="register-submit" data-register-submit\s+disabled>/');
});

test('the public terms and privacy pages still show their full text from the shared partials', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('1. Acceptance of Terms')
        ->assertSee('21. Acknowledgment and Agreement');

    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Information We Collect')
        ->assertSee('Changes to This Privacy Policy');
});
