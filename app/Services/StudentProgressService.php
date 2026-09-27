<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentProgressService
{
    public function __construct(private QuizAttemptService $quizAttempts) {}

    /**
     * Derive module scores exclusively from server-created quiz attempt answers.
     * The submitted $scores array is retained in the signature for backwards
     * compatibility with existing callers, but is never trusted.
     *
     * All attempts for the course's quizzes are fetched in ONE query and the
     * latest-per-quiz pick happens in memory — previously each module fired
     * its own attempt query (plus one per questions table lookup), which is
     * what made dashboards with several enrolled courses so query-heavy.
     *
     * @param array<string, int> $scores
     */
    public function earnedModuleScores(Course $course, array $scores, int $userId): array
    {
        $valid = [];

        $quizzes = [];
        foreach ($course->modules as $moduleIndex => $module) {
            if ($module->quiz) {
                $quizzes[(int) $module->quiz->id] = [$moduleIndex, $module->quiz];
            }
        }

        if (empty($quizzes)) {
            return $valid;
        }

        $attemptsByQuiz = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('quiz_id', array_keys($quizzes))
            ->whereHas('answers')
            ->with('answers')
            ->latest('submitted_at')
            ->latest('created_at')
            ->get()
            ->groupBy('quiz_id');

        foreach ($quizzes as $quizId => [$moduleIndex, $quiz]) {
            $editedAt = $this->quizAttempts->lastEditedAt($quiz);

            // Candidates are already ordered newest-first; take the first
            // one that post-dates the quiz's last edit (same rule as before).
            $attempt = $attemptsByQuiz->get($quizId, collect())
                ->first(fn (QuizAttempt $a) => ! $editedAt
                    || ($a->submitted_at && $a->submitted_at->gte($editedAt))
                    || ($a->created_at && $a->created_at->gte($editedAt)));

            if (! $attempt) {
                continue;
            }

            $valid[(string) $moduleIndex] = (int) $attempt->answers->where('is_correct', true)->count();
        }

        return $valid;
    }

    public function syncEnrollmentProgress(Course $course, ?Enrollment $enrollment): int
    {
        // Course detail pages are also available before enrollment. In that
        // case there is no enrollment to synchronize, so the learner starts
        // at zero progress instead of triggering a type error.
        if (! $enrollment) {
            return 0;
        }

        $state = (array) ($enrollment->progress_state ?? []);
        $scores = $this->earnedModuleScores(
            $course,
            (array) ($state['module_scores'] ?? []),
            (int) $enrollment->user_id
        );
        $percent = $this->calculateProgressPercent($course, $scores);

        if ((int) $enrollment->progress_percent !== $percent) {
            // Progress only ever updates progress_percent. It must never
            // touch is_completed (legacy) or completion_status: official
            // completion belongs solely to MicrocredentialCompletionService.
            $enrollment->progress_percent = $percent;
            $enrollment->save();
        }

        return $percent;
    }

    /** @param array<string, int> $moduleScores */
    public function calculateProgressPercent(Course $course, array $moduleScores): int
    {
        $totalQuestions = 0;
        $totalCorrect = 0;

        foreach ($course->modules as $moduleIndex => $module) {
            if (! $module->quiz) {
                continue;
            }

            // Use the eager-loaded questions collection when the controller
            // provided it; only hit the database when it was not loaded.
            $questionCount = $module->quiz->relationLoaded('questions')
                ? $module->quiz->questions->count()
                : $module->quiz->questions()->count();
            if ($questionCount === 0) {
                continue;
            }

            $totalQuestions += $questionCount;
            $score = $moduleScores[(string) $moduleIndex] ?? $moduleScores[$moduleIndex] ?? null;
            if ($score !== null) {
                $totalCorrect += min((int) $score, $questionCount);
            }
        }

        if ($totalQuestions === 0) {
            return 0;
        }

        return (int) min(100, round(($totalCorrect / $totalQuestions) * 100));
    }

    /** @param array<int, string> $saved */
    public function resolveCompletedLessons(Course $course, array $saved, Enrollment $enrollment): array
    {
        $lastSavedAt = $enrollment->updated_at;
        $byId = [];

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $byId[(string) $lesson->id] = $lesson;
            }
        }

        $resolved = [];
        foreach ($saved as $key) {
            $key = (string) $key;
            $isLegacyKey = str_contains($key, '-');
            $lesson = null;

            if ($isLegacyKey) {
                [$moduleIndex, $lessonIndex] = array_pad(explode('-', $key, 2), 2, null);
                $lesson = $course->modules->get((int) $moduleIndex)?->lessons->get((int) $lessonIndex);
            } elseif (isset($byId[$key])) {
                $lesson = $byId[$key];
            }

            if (! $lesson) {
                continue;
            }

            if ($isLegacyKey && $lastSavedAt && $lesson->created_at && $lesson->created_at->gt($lastSavedAt)) {
                continue;
            }

            $resolved[(string) $lesson->id] = true;
        }

        return array_keys($resolved);
    }

    /** @param array<int, string> $completedKeys */
    public function persistLessonCompletions(Course $course, User $student, array $completedKeys): void
    {
        // Same dual key-format resolution resolveCompletedLessons() above
        // already supports (legacy "moduleIndex-lessonIndex" AND the
        // modern plain lesson id). This method previously only handled
        // the legacy format via its regex, silently skipping every
        // modern-format key — which is why lesson_completions never
        // received a row for courses whose frontend sends plain lesson
        // ids. Fixed here without changing either key format or anything
        // about how completed_lessons itself is computed/stored.
        $byId = [];
        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $byId[(string) $lesson->id] = $lesson;
            }
        }

        foreach ($completedKeys as $key) {
            $key = (string) $key;
            $lesson = null;

            if (preg_match('/^(\d+)-(\d+)$/', $key, $matches)) {
                $lesson = $course->modules->get((int) $matches[1])?->lessons->values()->get((int) $matches[2]);
            } elseif (isset($byId[$key])) {
                $lesson = $byId[$key];
            }

            if (! $lesson) {
                continue;
            }

            $alreadyDone = DB::table('lesson_completions')
                ->where('user_id', $student->id)
                ->where('lesson_id', $lesson->id)
                ->exists();
            DB::table('lesson_completions')->insertOrIgnore([
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! $alreadyDone) {
                AnalyticsEvent::create([
                    'user_id' => $student->id,
                    'event_type' => 'lesson_completed',
                    'entity_type' => 'lesson',
                    'entity_id' => $lesson->id,
                    'metadata' => ['detail' => $student->name.' finished '.($lesson->title ?? 'a lesson')],
                    'occurred_at' => now(),
                ]);
            }
        }
    }

    /**
     * Keeps `enrollments.lessons_completed` (the Phase 3 completion gate)
     * in sync with the `lesson_completions` table, using the exact same
     * "every course lesson has a completion row for this user" rule
     * MicrocredentialCompletionService::allLessonsCompleted() uses. This
     * is a narrow, local sync only — it does not call into
     * MicrocredentialCompletionService, does not run evaluate(), and does
     * not touch quiz_mastery_met, competency_mastery_met,
     * completion_status, or any faculty/academic-unit gate. Those remain
     * exclusively that service's responsibility, not wired here.
     */
    public function syncLessonsCompletedFlag(Course $course, Enrollment $enrollment): void
    {
        // Uses the SAME module-nested lesson source resolveCompletedLessons()
        // and persistLessonCompletions() already use, rather than
        // Course::lessons() (the direct course_id-based relation). Those two
        // relation paths can disagree for a given course, and relying on
        // Course::lessons() meant an empty result there silently defaulted
        // this method to "all lessons complete" — the exact bug that
        // produced lessons_completed = 1 with zero real completions.
        $lessonIds = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->pluck('id')
            ->unique();
        $allCompleted = $lessonIds->isEmpty();

        if ($lessonIds->isNotEmpty()) {
            $completedCount = DB::table('lesson_completions')
                ->where('user_id', $enrollment->user_id)
                ->whereIn('lesson_id', $lessonIds)
                ->distinct('lesson_id')
                ->count('lesson_id');

            $allCompleted = $completedCount >= $lessonIds->count();
        }

        if ((bool) $enrollment->lessons_completed !== $allCompleted) {
            $enrollment->lessons_completed = $allCompleted;
            $enrollment->save();
        }
    }
}