<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class CourseCompletionService
{
    public function isReady(Course $course, Enrollment $enrollment): bool
    {
        if ((int) $enrollment->progress_percent >= 100) {
            return true;
        }

        $hasQuizQuestions = $course->modules->contains(
            fn ($module) => $module->quiz && $module->quiz->questions->isNotEmpty()
        );
        if ($hasQuizQuestions) {
            return false;
        }

        $lessonCount = $course->lessons->count();
        $completedLessons = collect((array) data_get($enrollment->progress_state, 'completed_lessons', []))
            ->intersect($course->lessons->pluck('id')->map(fn ($id) => (string) $id))
            ->count();

        return $lessonCount > 0 && $completedLessons >= $lessonCount;
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