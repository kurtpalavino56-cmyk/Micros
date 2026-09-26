<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizAttemptService
{
    public function __construct(private QuizGradingService $grading) {}

    public function attemptsAllowed(Quiz $quiz): int
    {
        $raw = strtolower(trim((string) ($quiz->attempts ?? '')));
        if ($raw === '') {
            return 1;
        }
        if (str_contains($raw, 'unlimited') || str_contains($raw, 'no limit')) {
            return 0;
        }

        preg_match('/\d+/', $raw, $matches);

        return isset($matches[0]) ? max(1, (int) $matches[0]) : 1;
    }

    public function attemptsUsed(Quiz $quiz, int $userId): int
    {
        $editedAt = $this->lastEditedAt($quiz);

        return QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->whereHas('answers')
            ->when($editedAt, fn ($query) => $query->where(function ($where) use ($editedAt) {
                $where->where('submitted_at', '>=', $editedAt)
                    ->orWhere('created_at', '>=', $editedAt);
            }))
            ->count();
    }

    /** @return array{allowed: int, used: int, exhausted: bool, unlock: int|null} */
    public function retakeStatus(Quiz $quiz, int $userId): array
    {
        $lastAttempt = $this->latestAttempt($quiz, $userId);
        $allowed = $this->attemptsAllowed($quiz);
        $used = $this->attemptsUsed($quiz, $userId);
        $exhausted = $allowed > 0 && $used >= $allowed;
        $unlock = null;

        if (! $exhausted && $lastAttempt && ! $lastAttempt->passed) {
            $submittedAt = $lastAttempt->submitted_at ?? $lastAttempt->created_at;
            $editedAt = $this->lastEditedAt($quiz);
            $stale = $editedAt && $submittedAt && $submittedAt->lt($editedAt);
            if (! $stale && $submittedAt && $submittedAt->copy()->addHours(24)->isFuture()) {
                $unlock = $submittedAt->copy()->addHours(24)->getTimestamp() * 1000;
            }
        }

        return compact('allowed', 'used', 'exhausted', 'unlock');
    }

    /** @param array<string, mixed> $result */
    /** @return array<string, mixed> */
    public function submit(User $student, Quiz $quiz, array $result): array
    {
        $status = $this->retakeStatus($quiz, $student->id);
        if ($status['exhausted']) {
            return ['blocked' => true, 'score' => null, 'passed' => false, 'unlock' => null];
        }

        if ($status['unlock'] !== null) {
            return ['blocked' => true, 'score' => null, 'passed' => false, 'unlock' => $status['unlock']];
        }

        $grade = $this->grading->grade($quiz, $result);
        $percent = $grade['score'];
        $passed = $grade['passed'];

        DB::transaction(function () use ($student, $quiz, $grade, $percent, $passed): void {
            $attempt = QuizAttempt::create([
                'user_id' => $student->id,
                'quiz_id' => $quiz->id,
                'score' => $percent,
                'passed' => $passed,
                'started_at' => now(),
                'submitted_at' => now(),
            ]);

            foreach ($grade['answers'] as $questionId => $answer) {
                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => (int) $questionId,
                    'answer' => $answer['answer'],
                    'is_correct' => $answer['correct'],
                ]);
            }
        });

        if ($passed) {
            AnalyticsEvent::create([
                'user_id' => $student->id,
                'event_type' => 'quiz_passed',
                'entity_type' => 'quiz',
                'entity_id' => $quiz->id,
                'metadata' => ['detail' => $student->name.' passed '.($quiz->title ?? 'a quiz').' ('.$percent.'%)'],
                'occurred_at' => now(),
            ]);
        }

        return [
            'blocked' => false,
            'score' => $percent,
            'passed' => $passed,
            'correct' => $grade['correct'],
            'total' => $grade['total'],
            'answers' => $grade['answers'],
            'unlock' => $passed ? null : now()->addHours(24)->getTimestamp() * 1000,
        ];
    }

    public function lastEditedAt(?Quiz $quiz): ?Carbon
    {
        if (! $quiz) {
            return null;
        }
        if ($quiz->questions_changed_at) {
            return $quiz->questions_changed_at;
        }

        // Prefer the eager-loaded collection (dashboards load questions for
        // every quiz up front); query only when it was not loaded.
        $newestQuestion = $quiz->relationLoaded('questions')
            ? $quiz->questions->max('created_at')
            : $quiz->questions()->max('created_at');

        return $newestQuestion ? Carbon::parse($newestQuestion) : null;
    }

    private function latestAttempt(Quiz $quiz, int $userId): ?QuizAttempt
    {
        return QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->whereHas('answers')
            ->latest('submitted_at')
            ->first();
    }
}
