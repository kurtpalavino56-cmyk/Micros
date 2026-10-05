<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Complaint — a Help Centre message thread raised by a student, faculty member,
 * or website visitor. Administrators reply from the Complaint Inbox.
 */
class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'guest_name',
        'guest_email',
        'subject',
        'message',
        'attachment_url',
        'attachment_name',
        'category',
        'status',
        'last_reply_at',
        'student_read_at',
        'admin_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
            'student_read_at' => 'datetime',
            'admin_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ComplaintReply::class)->orderBy('created_at');
    }

    /** Raised by someone without an account? */
    public function isFromVisitor(): bool
    {
        return $this->source === 'visitor' || $this->user_id === null;
    }

    /** Name to show in the admin inbox, whoever sent it. */
    public function senderName(): string
    {
        if ($this->isFromVisitor()) {
            return $this->guest_name ?: 'Website Visitor';
        }

        return $this->user->name ?? 'Student';
    }

    /** Contact line under the sender name. */
    public function senderContact(): ?string
    {
        return $this->isFromVisitor()
            ? $this->guest_email
            : ($this->user->email ?? null);
    }

    /** Is the attachment something a browser can render inline? */
    public function attachmentIsImage(): bool
    {
        if (! $this->attachment_url) {
            return false;
        }

        $ext = strtolower(pathinfo($this->attachment_url, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    /** Newest activity on the thread, used for ordering the inbox. */
    public function lastActivityAt()
    {
        return $this->last_reply_at ?? $this->created_at;
    }

    /** Does the student have something new to read? */
    public function unreadForStudent(): bool
    {
        return $this->unreadForSender();
    }

    /** Does the account that opened the thread have something new to read? */
    public function unreadForSender(): bool
    {
        $at = $this->lastActivityAt();

        return $at && (! $this->student_read_at || $this->student_read_at->lt($at));
    }

    /** Role label shown to administrators in the support inbox. */
    public function senderRoleLabel(): string
    {
        if ($this->isFromVisitor()) {
            return 'Visitor';
        }

        return $this->user?->isFaculty() ? 'Faculty' : 'Student';
    }

    /** Does the admin have something new to read? */
    public function unreadForAdmin(): bool
    {
        $at = $this->lastActivityAt();

        return $at && (! $this->admin_read_at || $this->admin_read_at->lt($at));
    }
}
