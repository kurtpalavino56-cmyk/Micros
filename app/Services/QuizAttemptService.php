<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizAttemptGrant;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Support\SeededShuffle;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizAttemptService
{
    /**
     * Tolerance for submissions that reach the server just after the deadline.
     * The browser submits at 0:00, so this only absorbs network and timer drift.
     */
    public const SUBMISSION_GRACE_SECONDS = 5;

    private const PRESENTATION_SESSION_PREFIX = 'quiz_presentation.';

    public function __construct(private QuizGradingService $grading) {}

    /**
     * A timed submission is late once it arrives past the limit plus the grace window.
     * Untimed quizzes and quizzes without a recorded start are never late.
     */
    public function submissionIsLate(Quiz $quiz, ?int $startedAt): bool
    {
        $timeLimitSeconds = max(0, (int) $quiz->time_limit * 60);
        if ($timeLimitSeconds === 0 || $startedAt === null || $startedAt <= 0) {
            return false;
        }

        return now()->timestamp - $startedAt > $timeLimitSeconds + self::SUBMISSION_GRACE_SECONDS;
    }

    public function attemptsAllowed(Quiz $quiz): int
    {
        $raw = strtolower(trim((string) ($quiz->attempts ?? '')));
        if ($raw === '') {
            return 0;
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

    /** @return array{allowed: int, used: int, remaining: int|null, exhausted: bool, unlock: int|null} */
    public function retakeStatus(Quiz $quiz, int $userId): array
    {
        $lastAttempt = $this->latestAttempt($quiz, $userId);
        $baseLimit = $this->attemptsAllowed($quiz);
        $used = $this->attemptsUsed($quiz, $userId);
        $editedAt = $this->lastEditedAt($quiz);
        $extraAttempts = QuizAttemptGrant::query()
            ->where('student_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->when($editedAt, fn ($query) => $query->where('created_at', '>=', $editedAt))
            ->sum('extra_attempts');
        // A zero base limit means unlimited; a grant must never make it finite.
        $allowed = $baseLimit === 0 ? 0 : $baseLimit + (int) $extraAttempts;
        $exhausted = $allowed > 0 && $used >= $allowed;
        $unlock = null;

        if (! $exhausted && $lastAttempt && ! $lastAttempt->passed) {
            $submittedAt = $lastAttempt->submitted_at ?? $lastAttempt->created_at;
            $stale = $editedAt && $submittedAt && $submittedAt->lt($editedAt);
            if (! $stale && $submittedAt && $submittedAt->copy()->addMinutes(5)->isFuture()) {
                $unlock = $submittedAt->copy()->addMinutes(5)->getTimestamp() * 1000;
            }
        }

        $remaining = $allowed > 0 ? max(0, $allowed - $used) : null;

        return compact('allowed', 'used', 'remaining', 'exhausted', 'unlock');
    }

    /** @param array<string, mixed> $result */
    /** @return array<string, mixed> */
    public function submit(User $student, Quiz $quiz, array $result, ?Carbon $startedAt = null, ?array $presentation = null): array
    {
        $status = $this->retakeStatus($quiz, $student->id);
        if ($status['exhausted']) {
            return ['blocked' => true, 'score' => null, 'passed' => false, 'unlock' => null];
        }

        if ($status['unlock'] !== null) {
            return ['blocked' => true, 'score' => null, 'passed' => false, 'unlock' => $status['unlock']];
        }

        // Grade against the set this student was actually served. The
        // presentation comes from the server (session or deterministic
        // derivation), never from the client.
        $presentation ??= $this->presentationFor($quiz, (int) $student->id);
        $grade = $this->grading->grade($quiz, $result, $this->questionsInPresentation($quiz, $presentation));
        $percent = $grade['score'];
        $passed = $grade['passed'];

        DB::transaction(function () use ($student, $quiz, $grade, $percent, $passed, $startedAt, $presentation): void {
            $attempt = QuizAttempt::create([
                'user_id' => $student->id,
                'quiz_id' => $quiz->id,
                'quiz_set_id' => $presentation['set_id'],
                'shuffle_seed' => $presentation['question_seed'],
                'presentation' => $this->presentationSnapshot($presentation),
                'score' => $percent,
                'passed' => $passed,
                'started_at' => $startedAt ?? now(),
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

        $this->forgetPresentation($quiz, (int) $student->id);

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
            'unlock' => $passed ? null : now()->addMinutes(5)->getTimestamp() * 1000,
        ];
    }

    /**
     * The set and order a student is served for their CURRENT attempt.
     *
     * Deterministic and server-side only:
     *   set index   = (crc32(user, quiz) + attemptsUsed) mod number of sets
     *   question seed / choice seed = crc32 of (user, quiz, attempt number),
     *   with different prefixes so the two orders are independent.
     * attemptsUsed is the same counter as the attempt budget (it restarts when
     * the questions change; grants do not move it), so a refresh or resume
     * never changes the set, different students spread across sets, and each
     * retake rotates to the next set.
     *
     * The result is remembered in the session between start and submit, so a
     * faculty edit to the number of sets mid-attempt cannot swap the set under
     * a student who already sees it. A stored entry is only reused while it
     * still describes the same attempt and the same question version.
     *
     * @return array{quiz_id:int,attempt_number:int,version:int|null,set_id:int|null,set_name:string,set_index:int,set_count:int,question_seed:int,choice_seed:int,question_ids:list<int>,choice_orders:array<string,list<int>>}
     */
    public function presentationFor(Quiz $quiz, int $userId, bool $remember = true): array
    {
        $attemptNumber = $this->attemptsUsed($quiz, $userId);
        $version = $this->lastEditedAt($quiz)?->getTimestamp();
        $groups = $this->setGroups($quiz);
        $session = $this->session();
        $key = self::PRESENTATION_SESSION_PREFIX.$userId.'.'.$quiz->id;

        $stored = $session?->get($key);
        if (is_array($stored)
            && ($stored['attempt_number'] ?? null) === $attemptNumber
            && ($stored['version'] ?? null) === $version
            && $groups->has((string) ($stored['set_id'] ?? '0'))
            && $this->sameQuestions($groups->get((string) ($stored['set_id'] ?? '0')), $stored['question_ids'] ?? [])) {
            return $stored;
        }

        $presentation = $this->buildPresentation($quiz, $userId, $attemptNumber, $version, $groups);
        if ($remember) {
            $session?->put($key, $presentation);
        }

        return $presentation;
    }

    /**
     * Questions in the order the student sees them, each with its choices in
     * display order. A choice keeps its ORIGINAL letter as the submitted
     * value (QuizGradingService grades by original position) and gets a
     * sequential display label (A, B, C by position on screen).
     *
     * No correct answers are included: this feeds active attempts only.
     *
     * @return list<array{question:QuizQuestion,options:list<array{value:string,label:string,text:string}>}>
     */
    public function presentedQuestions(Quiz $quiz, array $presentation): array
    {
        $choiceOrders = $presentation['choice_orders'] ?? [];

        return $this->questionsInPresentation($quiz, $presentation)->map(function (QuizQuestion $question) use ($choiceOrders): array {
            $options = array_values((array) ($question->options ?? []));
            $order = $choiceOrders[(string) $question->id] ?? array_keys($options);
            if (count($order) !== count($options) || array_diff(array_keys($options), $order) !== []) {
                $order = array_keys($options);
            }

            $presented = [];
            foreach (array_values($order) as $displayIndex => $originalIndex) {
                $presented[] = [
                    'value' => chr(65 + (int) $originalIndex),
                    'label' => chr(65 + $displayIndex),
                    'text' => (string) $options[$originalIndex],
                ];
            }

            return ['question' => $question, 'options' => $presented];
        })->values()->all();
    }

    /**
     * Server-side check that every submitted question belongs to the set the
     * student was served.
     *
     * @param  array<string|int, mixed>  $answers
     */
    public function answersMatchPresentation(array $answers, array $presentation): bool
    {
        $allowed = array_map('strval', $presentation['question_ids'] ?? []);

        return array_diff(array_map('strval', array_keys($answers)), $allowed) === [];
    }

    /** @return Collection<int, QuizQuestion> */
    public function questionsInPresentation(Quiz $quiz, array $presentation): Collection
    {
        $questions = ($quiz->relationLoaded('questions') ? $quiz->questions : $quiz->questions()->get())->keyBy('id');

        $ordered = collect($presentation['question_ids'] ?? [])
            ->map(fn ($id) => $questions->get((int) $id))
            ->filter()
            ->values();

        // The served questions are gone (deleted between start and submit):
        // fall back to the current default set rather than grading nothing.
        return $ordered->isEmpty() && ! empty($presentation['question_ids'])
            ? $quiz->questionsForSet()
            : $ordered;
    }

    public function forgetPresentation(Quiz $quiz, int $userId): void
    {
        $this->session()?->forget(self::PRESENTATION_SESSION_PREFIX.$userId.'.'.$quiz->id);
    }

    /**
     * Questions grouped by set, ordered by set position. Keys are set ids as
     * strings ("0" for questions without a set: one implicit set).
     *
     * @return Collection<string, Collection<int, QuizQuestion>>
     */
    private function setGroups(Quiz $quiz): Collection
    {
        $questions = ($quiz->relationLoaded('questions') ? $quiz->questions : $quiz->questions()->get())
            ->sortBy([['position', 'asc'], ['id', 'asc']])->values();
        $grouped = $questions->groupBy(fn (QuizQuestion $question) => (string) ((int) $question->quiz_set_id));

        if ($grouped->count() <= 1) {
            $setId = (string) ($grouped->keys()->first() ?? '0');

            return collect([$setId => $questions]);
        }

        $positions = $quiz->orderedSets()->mapWithKeys(fn ($set) => [(string) $set->id => [(int) $set->position, (int) $set->id]]);

        return $grouped->sortBy(fn ($group, $setId) => $positions->get($setId, [PHP_INT_MAX, (int) $setId]));
    }

    /** @param Collection<string, Collection<int, QuizQuestion>> $groups */
    private function buildPresentation(Quiz $quiz, int $userId, int $attemptNumber, ?int $version, Collection $groups): array
    {
        $setIds = $groups->keys()->values();
        $setCount = max(1, $setIds->count());
        $setIndex = (int) ((SeededShuffle::seed('set', (string) $userId, (string) $quiz->id) + $attemptNumber) % $setCount);
        $setKey = (string) $setIds->get($setIndex, '0');
        $questions = $groups->get($setKey, collect());
        $set = $setKey !== '0' ? $quiz->orderedSets()->firstWhere('id', (int) $setKey) : null;

        $questionSeed = SeededShuffle::seed('questions', (string) $userId, (string) $quiz->id, (string) $attemptNumber);
        $choiceSeed = SeededShuffle::seed('choices', (string) $userId, (string) $quiz->id, (string) $attemptNumber);

        $questionIds = $questions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        if ($quiz->shuffle_questions) {
            $questionIds = SeededShuffle::shuffle($questionIds, $questionSeed);
        }

        $choiceOrders = [];
        foreach ($questions as $question) {
            $count = count((array) ($question->options ?? []));
            $order = range(0, max(0, $count - 1));
            if ($count === 0) {
                $order = [];
            } elseif ($quiz->shuffle_choices && $this->choicesShuffle($question)) {
                $order = SeededShuffle::shuffle($order, SeededShuffle::seed((string) $choiceSeed, (string) $question->id));
            }
            $choiceOrders[(string) $question->id] = $order;
        }

        return [
            'quiz_id' => (int) $quiz->id,
            'attempt_number' => $attemptNumber,
            'version' => $version,
            'set_id' => $set?->id ? (int) $set->id : ($setKey !== '0' ? (int) $setKey : null),
            'set_name' => $set?->name ?? 'Set A',
            'set_index' => $setIndex,
            'set_count' => $setCount,
            'question_seed' => $questionSeed,
            'choice_seed' => $choiceSeed,
            'question_ids' => array_values($questionIds),
            'choice_orders' => $choiceOrders,
        ];
    }

    /** True/False and locked questions keep their authored order; Identification has no choices. */
    private function choicesShuffle(QuizQuestion $question): bool
    {
        return ($question->type ?? 'Multiple Choice') === 'Multiple Choice' && ! $question->lock_choice_order;
    }

    /** @param Collection<int, QuizQuestion>|null $questions */
    private function sameQuestions(?Collection $questions, array $ids): bool
    {
        if (! $questions) {
            return false;
        }

        $current = $questions->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $stored = collect($ids)->map(fn ($id) => (int) $id)->sort()->values()->all();

        return $current === $stored;
    }

    /** What is stored on the attempt so review can reproduce the served order after later edits. */
    private function presentationSnapshot(array $presentation): array
    {
        return [
            'set_id' => $presentation['set_id'],
            'set_name' => $presentation['set_name'],
            'attempt_number' => $presentation['attempt_number'],
            'question_seed' => $presentation['question_seed'],
            'choice_seed' => $presentation['choice_seed'],
            'question_ids' => $presentation['question_ids'],
            'choice_orders' => $presentation['choice_orders'],
        ];
    }

    private function session(): ?Session
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request->hasSession() ? $request->session() : null;
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
