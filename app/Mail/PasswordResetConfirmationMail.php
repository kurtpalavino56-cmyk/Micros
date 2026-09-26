<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Courtesy email after a successful password change. Also a security
 * signal: if the user did NOT make the change, this is how they find out.
 */
class PasswordResetConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetAt;

    public function __construct(
        public string $toName,
    ) {
        $this->resetAt = now()->format('M j, Y g:i A');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your UPSKILL password was changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-confirmation',
        );
    }
}
