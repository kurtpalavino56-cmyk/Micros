<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Support\Facades\DB;

class CourseModuleService
{
    public function create(Course $course, array $data): CourseModule
    {
        return DB::transaction(function () use ($course, $data): CourseModule {
            $finalModule = $course->modules()->where('is_final', true)->first();
            $order = $finalModule ? (int) $finalModule->order : ((int) $course->modules()->max('order')) + 1;

            if ($finalModule) {
                $course->modules()->where('order', '>=', $order)->increment('order');
                $finalModule->refresh();
            }

            return CourseModule::create([
                'course_id' => $course->id,
                'title' => trim($data['module_title'] ?? '') ?: 'Untitled Module',
                'description' => trim($data['module_description'] ?? ''),
                'order' => $order,
                'is_final' => false,
            ]);
        });
    }

    public function createFinalExam(Course $course): CourseModule
    {
        return DB::transaction(function () use ($course): CourseModule {
            $existing = $course->modules()->where('is_final', true)->first();
            if ($existing) {
                return $existing;
            }

            return CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Final Exam',
                'description' => null,
                'order' => ((int) $course->modules()->max('order')) + 1,
                'is_final' => true,
            ]);
        });
    }

    public function deleteFinalExam(Course $course): bool
    {
        return (bool) $course->modules()->where('is_final', true)->delete();
    }

    public function deleteByKey(Course $course, string $key): void
    {
        if (preg_match('/^mod-(\d+)$/', $key, $matches)) {
            CourseModule::where('course_id', $course->id)
                ->where('id', (int) $matches[1])
                ->where('is_final', false)
                ->delete();
        }
    }

    public function update(CourseModule $module, array $data): CourseModule
    {
        $module->title = trim($data['module_title'] ?? '') ?: $module->title;
        $module->description = trim($data['module_description'] ?? '');
        $module->save();

        return $module;
    }
}
