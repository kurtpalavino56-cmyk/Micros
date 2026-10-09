<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'event',
        'target_type',
        'target_id',
        'target_label',
        'changes',
        'description',
        'method',
        'route',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new LogicException('Audit log entries are append-only.');
        });

        static::deleting(static function (): void {
            throw new LogicException('Audit log entries are append-only.');
        });
    }
}
