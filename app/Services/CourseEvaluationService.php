<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;

class CourseEvaluationService
{
    public function __construct(
        private StudentProgressService $progressService,
        private QuizAttemptService $quizAttempts,
    ) {}

    public function isEligible(Course $course, User $user, Enrollment $enrollment): bool
    {
        $finalModule = $course->modules->firstWhere('is_final', true);
        $finalQuiz = $finalModule?->quiz;

        if ($finalModule && ! $finalQuiz) {
            return false;
        }

        if ($finalQuiz) {
            $passedAttempt = QuizAttempt::query()
                ->where('quiz_id', $finalQuiz->id)
                ->where('user_id', $user->id)
                ->where('passed', true)
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->first();

            $editedAt = $this->quizAttempts->lastEditedAt($finalQuiz);
            if ($passedAttempt && (! $editedAt || $passedAttempt->submitted_at->gte($editedAt))) {
                return true;
            }

            return false;
        }

        return $this->progressService->progressBreakdown($course, (int) $user->id, $enrollment->enrolled_at)['percent'] >= 100;
    }
}
