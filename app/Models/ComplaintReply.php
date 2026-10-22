<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ComplaintReply — one message in a support thread, from its sender or an administrator.
 */
class ComplaintReply extends Model
{
    protected $fillable = [
        'complaint_id',
        'user_id',
        'body',
        'is_admin',
        'attachment_url',
        'attachment_name',
    ];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean'];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachmentIsImage(): bool
    {
        if (! $this->attachment_url) {
            return false;
        }

        $extension = strtolower(pathinfo($this->attachment_url, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
