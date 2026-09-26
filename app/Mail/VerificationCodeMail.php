<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The 6-digit password-reset code, emailed in step 1 of forgot-password.
 */
class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $toName,
        public string $code,
        public int $ttlMinutes,
    ) {}

    public function envelope(): Envelope
    {
        // The code goes in the subject too, so the user can read it from a
        // lock-screen notification without opening the email.
        return new Envelope(
            subject: 'Your UPSKILL verification code: '.$this->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
        );
    }
}
