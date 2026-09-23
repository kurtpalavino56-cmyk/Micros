<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EmailJsMailer — sends the password-reset emails through EmailJS.
 *
 * Called from Laravel, not from the browser. EmailJS publishes a JavaScript
 * snippet that sends straight from the page, but that approach cannot be
 * used for anything secret: the browser has to know the message body, so a
 * verification code would be visible in DevTools and in the network tab.
 * Anyone could then request a code for another person's email, read it off
 * their own screen, and take over that account.
 *
 * Sending from the server with the PRIVATE key keeps the code server-side.
 * The user's browser never sees it.
 */
class EmailJsMailer
{
    /** Send the 6-digit verification code. */
    public function sendVerificationCode(string $toEmail, string $toName, string $code): bool
    {
        return $this->send('verify', array_merge($this->branding(), [
            'to_email' => $toEmail,
            'to_name' => $toName,
            'code' => $code,
            'expires_in' => config('emailjs.code_ttl_minutes').' minutes',
        ]));
    }

    /** Confirm to the user that their password was changed. */
    public function sendResetConfirmation(string $toEmail, string $toName): bool
    {
        return $this->send('reset', array_merge($this->branding(), [
            'to_email' => $toEmail,
            'to_name' => $toName,
            'reset_at' => now()->format('M j, Y g:i A'),
        ]));
    }

    /**
     * Logo and link used by both templates.
     *
     * The logo must be a PUBLICLY reachable URL. Mail clients fetch images
     * from their own servers, so anything on localhost, 127.0.0.1 or a LAN
     * address renders as a broken image for the recipient — set
     * EMAILJS_LOGO_URL to something on the open internet.
     */
    protected function branding(): array
    {
        return [
            'logo_url' => config('emailjs.logo_url'),
            'site_url' => config('emailjs.site_url') ?: config('app.url'),
            'site_name' => 'UPSKILL',
        ];
    }

    /**
     * POST to the EmailJS REST API.
     *
     * Returns false rather than throwing: a mail outage should not produce
     * a 500 on the reset screen. The caller decides what to tell the user,
     * and the reason is written to the log for you to inspect.
     */
    protected function send(string $template, array $params): bool
    {
        $serviceId = config('emailjs.service_id');
        $templateId = config("emailjs.templates.$template");
        $publicKey = config('emailjs.public_key');
        $privateKey = config('emailjs.private_key');

        if (! $serviceId || ! $templateId || ! $privateKey) {
            Log::warning('EmailJS is not configured; password-reset email not sent.', [
                'template' => $template,
                'has_service' => (bool) $serviceId,
                'has_template' => (bool) $templateId,
                'has_privatekey' => (bool) $privateKey,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post(config('emailjs.endpoint'), [
                    'service_id' => $serviceId,
                    'template_id' => $templateId,
                    'user_id' => $publicKey,
                    'accessToken' => $privateKey,
                    'template_params' => $params,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('EmailJS rejected the request.', [
                'template' => $template,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('EmailJS request failed.', [
                'template' => $template,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
