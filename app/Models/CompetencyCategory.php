<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyCategory extends Model
{
    protected $fillable = ['name', 'description', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function units(): HasMany
    {
        return $this->hasMany(CompetencyUnit::class)->orderBy('order');
    }
}
