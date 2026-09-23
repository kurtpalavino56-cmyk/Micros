<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;

class CourseModuleService
{
    public function create(Course $course, array $data): CourseModule
    {
        return CourseModule::create([
            'course_id' => $course->id,
            'title' => trim($data['module_title'] ?? '') ?: 'Untitled Module',
            'description' => trim($data['module_description'] ?? ''),
            'order' => ((int) $course->modules()->max('order')) + 1,
        ]);
    }

    public function deleteByKey(Course $course, string $key): void
    {
        if (preg_match('/^mod-(\d+)$/', $key, $matches)) {
            CourseModule::where('course_id', $course->id)
                ->where('id', (int) $matches[1])
                ->delete();
        }
    }
}
