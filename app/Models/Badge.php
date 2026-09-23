<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon_url',
        'is_stackable',
        'badge_level',
        'prerequisite_badge_id',
        'pathway_id',
        'is_active',
        'pqf_level',
        'issuing_institution',
    ];

    protected function casts(): array
    {
        return [
            'is_stackable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'prerequisite_badge_id');
    }

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(Pathway::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(BadgeRule::class);
    }
}
