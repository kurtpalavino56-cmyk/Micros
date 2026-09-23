<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Http\Request;

class CourseLessonService
{
    /**
     * Lessons are editor-first: the rich-text editor is the lesson itself.
     * The legacy CourseLesson file columns are retained for compatibility,
     * but new lessons no longer create a separate attachment.
     */
    public function create(Request $request, array $data, Course $course, CourseModule $module): CourseLesson
    {
        $duration = (int) ($data['duration'] ?? 0);

        $lesson = CourseLesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => trim($data['lesson_title'] ?? '') ?: 'Untitled Lesson',
            'type' => 'Text',
            'duration' => $duration > 0 ? $duration.'m' : null,
            'content' => trim($data['lesson_content'] ?? ''),
            'file_url' => null,
            'file_name' => null,
            'order' => ((int) $module->lessons()->max('order')) + 1,
        ]);

        $this->refreshLessonCount($course);

        return $lesson;
    }

    public function update(Request $request, array $data, CourseLesson $lesson, Course $course): CourseLesson
    {
        $duration = (int) ($data['duration'] ?? 0);

        // Retire any legacy attachment when a lesson is edited under the
        // editor-only workflow. Files inserted through CKEditor remain part
        // of the saved lesson content and are not affected by this.
        $this->removeExistingFile($lesson->file_url);

        $lesson->title = trim($data['lesson_title'] ?? '') ?: $lesson->title;
        $lesson->content = trim($data['lesson_content'] ?? '');
        $lesson->duration = $duration > 0 ? $duration.'m' : null;
        $lesson->type = 'Text';
        $lesson->file_url = null;
        $lesson->file_name = null;
        $lesson->save();

        return $lesson;
    }

    public function deleteByKey(Course $course, string $key): void
    {
        if (preg_match('/^les-(\d+)$/', $key, $matches)) {
            CourseLesson::where('course_id', $course->id)
                ->where('id', (int) $matches[1])
                ->delete();
        }

        $this->refreshLessonCount($course);
    }

    private function removeExistingFile(?string $fileUrl): void
    {
        if ($fileUrl && is_file(public_path($fileUrl))) {
            @unlink(public_path($fileUrl));
        }
    }

    private function refreshLessonCount(Course $course): void
    {
        $course->lessons_count = $course->lessons()->count();
        $course->save();
    }
}
