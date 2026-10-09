<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LessonActivity;
use App\Models\Quiz;

class CourseReadinessService
{
    /** @return list<array{label: string, complete: bool, detail: string}> */
    public function checklist(Course $course): array
    {
        $course->loadMissing([
            'learningOutcomes',
            'modules.lessons.activities',
            'modules.lessons.quizzes.questions',
            'modules.quiz.questions',
        ]);

        $modules = $course->modules;
        $finalModule = $modules->firstWhere('is_final', true);
        $regularModules = $modules->reject(fn (CourseModule $module): bool => $module->is_final);
        $hasCourseDetails = filled($course->title)
            && $course->title !== 'Untitled Course'
            && filled($course->short_description)
            && $this->hasMeaningfulText($course->description)
            && filled($course->category)
            && filled($course->level);
        $hasLearningOutcome = $course->learningOutcomes->isNotEmpty()
            || ! empty($course->objectives);
        $hasModules = $regularModules->isNotEmpty();
        $hasLessonContent = $hasModules && $regularModules->every(fn (CourseModule $module): bool => $module->lessons->isNotEmpty()
            && $module->lessons->every(fn (CourseLesson $lesson): bool => $this->hasLessonMaterial($lesson))
        );
        $hasFinalExam = ! $finalModule || ($finalModule->quiz
            && $finalModule->quiz->is_active
            && $finalModule->quiz->questions->isNotEmpty());
        $hasAssessment = $modules->contains(function (CourseModule $module): bool {
            if ($module->quiz && $module->quiz->is_active && $module->quiz->questions->isNotEmpty()) {
                return true;
            }

            return $module->lessons->contains(function (CourseLesson $lesson): bool {
                $hasQuiz = $lesson->quizzes->contains(fn (Quiz $quiz): bool => $quiz->is_active && $quiz->questions->isNotEmpty()
                );
                $hasRequiredActivity = $lesson->activities->contains(fn (LessonActivity $activity): bool => $activity->is_active && $activity->is_required
                );

                return $hasQuiz || $hasRequiredActivity;
            });
        });

        return [
            [
                'label' => 'Course details',
                'complete' => $hasCourseDetails,
                'detail' => 'Add a title, category, level, short description, and full description.',
            ],
            [
                'label' => 'Measurable learning outcome',
                'complete' => $hasLearningOutcome,
                'detail' => 'Add at least one learning outcome for this course.',
            ],
            [
                'label' => 'Modules',
                'complete' => $hasModules,
                'detail' => 'Add at least one course module.',
            ],
            [
                'label' => 'Lessons and learning materials',
                'complete' => $hasLessonContent && $hasFinalExam,
                'detail' => $finalModule
                    ? 'Every regular module needs lesson content, and the Final Exam needs at least one question.'
                    : 'Every module needs a lesson, and each lesson needs written content or an uploaded learning file.',
            ],
            [
                'label' => 'Assessment',
                'complete' => $hasAssessment,
                'detail' => 'Add a quiz with questions or a required lesson activity.',
            ],
        ];
    }

    public function isReady(Course $course): bool
    {
        return collect($this->checklist($course))->every(fn (array $item): bool => $item['complete']);
    }

    /** @return list<string> */
    public function missingRequirements(Course $course): array
    {
        return collect($this->checklist($course))
            ->reject(fn (array $item): bool => $item['complete'])
            ->map(fn (array $item): string => $item['label'])
            ->values()
            ->all();
    }

    private function hasLessonMaterial(CourseLesson $lesson): bool
    {
        return $this->hasMeaningfulText($lesson->content)
            || filled($lesson->file_url)
            || preg_match('/<img\b/i', (string) $lesson->content) === 1;
    }

    private function hasMeaningfulText(?string $content): bool
    {
        $text = html_entity_decode(strip_tags((string) $content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? '';

        return trim($text) !== '';
    }
}
