<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ComplaintReplyMail — the administrator's answer to a Help Center message.
 *
 * Sent only to people who have no account (source = 'visitor'), because a
 * signed-in student reads the reply in their inbox instead.
 *
 * Implements ShouldQueue is deliberately NOT used here: on the default
 * 'sync' queue driver it would make no difference, and on a real queue a
 * failed job would be invisible to the admin. AdminController sends it
 * inline and reports failures. See the note in that controller if you later
 * switch to a queue worker.
 */
class ComplaintReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Complaint $complaint,
        public string $replyBody,
        public string $adminName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: '.$this->complaint->subject.' — UPSKILL Help Center',
            // Replies come back to the address in MAIL_REPLY_TO if set.
            replyTo: array_filter([config('mail.reply_to.address')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.complaint-reply',
            with: [
                'recipientName' => $this->complaint->senderName(),
                'originalTitle' => $this->complaint->subject,
                'originalBody' => $this->complaint->message,
                'reply' => $this->replyBody,
                'adminName' => $this->adminName,
                'sentAt' => now(),
            ],
        );
    }
}
