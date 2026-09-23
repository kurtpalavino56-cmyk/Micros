<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ComplaintReply — one message in a complaint thread, from either the
 * student who raised it or an administrator.
 */
class ComplaintReply extends Model
{
    protected $fillable = [
        'complaint_id',
        'user_id',
        'body',
        'is_admin',
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
}
