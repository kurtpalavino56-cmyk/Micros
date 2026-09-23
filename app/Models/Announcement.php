<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'audience',
        'created_by',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'audience' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** Is this announcement visible to the given role name? */
    public function visibleTo(string $roleName): bool
    {
        $audience = $this->audience;

        // No audience recorded (older rows) = everyone.
        return empty($audience) || in_array($roleName, $audience, true);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
