<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FacultyCode — a shareable, single-use registration code for faculty
 * staff. Generated from the Admin › Faculty Codes page; required on the
 * Faculty Register page.
 */
class FacultyCode extends Model
{
    protected $fillable = [
        'code',
        'created_by',
        'used_by',
        'used_at',
    ];

    protected function casts(): array
    {
        return ['used_at' => 'datetime'];
    }

    /** Green (available) vs Red (used) indicator on the admin page. */
    public function isUsed(): bool
    {
        return $this->used_by !== null;
    }

    /**
     * Generate a unique, human-friendly shareable code,
     * e.g. "FAC-4K9X2M" (ambiguous characters excluded).
     */
    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = 'FAC-';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /** Admin who generated the code. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Faculty account that consumed the code. */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
