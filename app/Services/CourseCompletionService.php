<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\CompetencyProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CourseCompletionService
{
    public function isReady(Course $course, Enrollment $enrollment): bool
    {
        $course->loadMissing([
            'modules.quiz.questions',
            'modules.lessons.quizzes.questions',
            'modules.lessons.activities',
            'learningOutcomes',
        ]);

        $lessons = $course->modules->flatMap(fn ($module) => $module->lessons);
        $lessonIds = $lessons->pluck('id')->unique();

        if ($lessonIds->isNotEmpty()) {
            $verifiedLessonIds = DB::table('lesson_completions')
                ->where('user_id', $enrollment->user_id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('server_verified_at')
                ->distinct()
                ->pluck('lesson_id')
                ->map(fn ($id) => (int) $id);

            if ($verifiedLessonIds->count() < $lessonIds->count()) {
                return false;
            }
        }

        foreach ($lessons as $lesson) {
            foreach ($lesson->activities->where('is_active', true)->where('is_required', true) as $activity) {
                if ($enrollment->enrolled_at && $activity->created_at && $activity->created_at->gt($enrollment->enrolled_at)) {
                    continue;
                }

                $acceptedStatuses = $activity->activity_type === 'assignment'
                    ? ['passed']
                    : ['completed', 'passed'];

                if (! $activity->submissions()
                    ->where('user_id', $enrollment->user_id)
                    ->whereIn('status', $acceptedStatuses)
                    ->exists()) {
                    return false;
                }
            }
        }

        $quizzes = $course->modules->flatMap(fn ($module) => collect([$module->quiz])->merge(
            $module->lessons->flatMap(fn ($lesson) => $lesson->quizzes)
        ))->filter(fn ($quiz) => $quiz && $quiz->questions->isNotEmpty());

        foreach ($quizzes as $quiz) {
            if ($quiz->lesson_id && $enrollment->enrolled_at && $quiz->created_at && $quiz->created_at->gt($enrollment->enrolled_at)) {
                continue;
            }

            if (! QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $enrollment->user_id)
                ->where('passed', true)
                ->whereNotNull('submitted_at')
                ->exists()) {
                return false;
            }
        }

        $competencyUnitIds = $course->learningOutcomes
            ->reject(fn ($outcome) => $enrollment->enrolled_at
                && $outcome->updated_at
                && $outcome->updated_at->gt($enrollment->enrolled_at))
            ->pluck('competency_unit_id')
            ->filter()
            ->unique();

        if ($competencyUnitIds->isNotEmpty()) {
            $masteredCount = CompetencyProgress::query()
                ->where('user_id', $enrollment->user_id)
                ->whereIn('competency_unit_id', $competencyUnitIds)
                ->where('status', 'completed')
                ->distinct()
                ->count('competency_unit_id');

            if ($masteredCount < $competencyUnitIds->count()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @deprecated Phase 3 fix: this method is no longer a source of
     * official badge/certificate issuance and has NO live callers —
     * StudentController now routes through
     * MicrocredentialCompletionService::evaluate() +
     * issueBadgeIfEligible()/issueCertificateIfEligible() instead (see
     * StudentController::finalizeCompletion()), which correctly enforces
     * completion_status, faculty_verification_status, and the
     * unconditional academic_unit_confirmation_status gate. The
     * Certificate/UserBadge creation that used to happen here has been
     * removed so this method can never again silently bypass those
     * gates if something calls it in the future. The legacy
     * `is_completed` and `progress_percent` fields are no longer mutated by
     * this compatibility method; official completion remains exclusively
     * owned by MicrocredentialCompletionService.
     */
    public function complete(User $student, Course $course, Enrollment $enrollment): ?string
    {
        $wasCompleted = $enrollment->completion_status === 'completed';
        $wasRecentlyCreated = $enrollment->wasRecentlyCreated;

        // This legacy method is retained only for compatibility with old
        // callers. It must never create or mutate official completion state.
        if (! $wasRecentlyCreated && ! $wasCompleted) {
            AnalyticsEvent::create([
                'user_id' => $student->id,
                'event_type' => 'course_completed',
                'entity_type' => 'course',
                'entity_id' => $course->id,
                'metadata' => ['detail' => $student->name.' completed '.$course->title],
                'occurred_at' => now(),
            ]);
        }

        return null;
    }
}
