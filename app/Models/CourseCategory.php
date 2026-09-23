<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CourseCategory — a Program Category a faculty member can file a course
 * under. Managed from Admin › Management › Program Categories.
 *
 * There is no foreign key from courses: courses.category stores the label as
 * a string, so renaming or removing a category never orphans a course. The
 * admin screen shows how many courses use each one before you delete it.
 */
class CourseCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Categories offered to faculty, in display order. */
    public static function activeNames(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /** How many courses currently carry this category label. */
    public function courseCount(): int
    {
        return Course::where('category', $this->name)->count();
    }
}
