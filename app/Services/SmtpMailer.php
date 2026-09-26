<?php

namespace App\Services;

use App\Mail\PasswordResetConfirmationMail;
use App\Mail\VerificationCodeMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * SmtpMailer — sends the password-reset emails through Laravel's built-in
 * mailer, over standard SMTP (Brevo, Gmail, Mailtrap, ...).
 *
 * This replaces the old EmailJsMailer. EmailJS is a browser-first service
 * that blocks server-side API calls unless a dashboard toggle is flipped;
 * plain SMTP has no such restriction. The provider is chosen entirely by
 * the MAIL_* values in .env — swap providers without touching this file.
 *
 * Like its predecessor, this class reports WHY a send failed (lastFailure /
 * lastDetail) so the reset form can surface the reason in debug mode, and
 * returns false instead of throwing so a mail outage never becomes a 500.
 */
class SmtpMailer
{
    /**
     * Why the last send() call failed ('not_configured' or 'smtp'), or null
     * when it succeeded.
     */
    protected ?string $lastFailure = null;

    protected string $lastDetail = '';

    public function lastFailure(): ?string
    {
        return $this->lastFailure;
    }

    public function lastDetail(): string
    {
        return $this->lastDetail;
    }

    /** Send the 6-digit verification code. */
    public function sendVerificationCode(string $toEmail, string $toName, string $code, int $ttlMinutes): bool
    {
        return $this->send($toEmail, new VerificationCodeMail($toName, $code, $ttlMinutes), 'verification-code');
    }

    /** Confirm to the user that their password was changed. */
    public function sendResetConfirmation(string $toEmail, string $toName): bool
    {
        return $this->send($toEmail, new PasswordResetConfirmationMail($toName), 'reset-confirmation');
    }

    /**
     * Hand the mailable to Laravel's mailer and translate any failure into
     * a logged, inspectable result.
     */
    protected function send(string $toEmail, Mailable $mailable, string $what): bool
    {
        $this->lastFailure = null;
        $this->lastDetail = '';

        if (! config('mail.default') || config('mail.default') === 'log') {
            // 'log' is a valid local-testing choice, but flag it: the email
            // lands in storage/logs/laravel.log, not in an inbox.
            Log::info('Mail driver is "log" — the email is being written to the log, not delivered.', [
                'to' => $toEmail,
                'mail' => $what,
            ]);
        }

        try {
            Mail::to($toEmail)->send($mailable);

            return true;
        } catch (Throwable $e) {
            $this->lastFailure = 'smtp';
            // Swift/Symfony exceptions carry the server's own words, e.g.
            // "535 5.7.8 Username and Password not accepted" — exactly what
            // is needed to fix the .env.
            $this->lastDetail = $e->getMessage();

            Log::error('Password-reset email failed to send.', [
                'to' => $toEmail,
                'mail' => $what,
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
