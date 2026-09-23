<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathwayCourse extends Model
{
    protected $fillable = ['pathway_id', 'course_id', 'order'];

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(Pathway::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
