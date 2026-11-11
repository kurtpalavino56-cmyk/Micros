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
        $snapshotAttemptIds = $this->completedSnapshotAttemptIds((int) $quiz->id, $userId, $editedAt);

        return QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->where(function ($query) use ($snapshotAttemptIds): void {
                $query->whereHas('answers');
                if ($snapshotAttemptIds !== []) {
                    $query->orWhereIn('id', $snapshotAttemptIds);
                }
            })
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
        if (! is_array($presentation['question_snapshot'] ?? null)) {
            $presentation['question_snapshot'] = $this->questionSnapshot($quiz, $presentation);
        }

        $grade = $this->grading->grade($quiz, $result, $this->questionsInPresentation($quiz, $presentation));
        $percent = $grade['score'];
        $passed = $grade['passed'];

        $attempt = DB::transaction(function () use ($student, $quiz, $grade, $percent, $passed, $startedAt, $presentation): QuizAttempt {
            $attributes = [
                'quiz_set_id' => $presentation['set_id'],
                'shuffle_seed' => $presentation['question_seed'],
                'presentation' => $this->presentationSnapshot($presentation),
                'question_snapshot' => $presentation['question_snapshot'],
                'score' => $percent,
                'passed' => $passed,
                'started_at' => $startedAt ?? now(),
                'submitted_at' => now(),
            ];
            $attempt = isset($presentation['attempt_id'])
                ? QuizAttempt::query()->whereKey($presentation['attempt_id'])
                    ->where('user_id', $student->id)
                    ->where('quiz_id', $quiz->id)
                    ->whereNull('submitted_at')
                    ->first()
                : null;
            if ($attempt) {
                $attempt->update($attributes);
            } else {
                $attempt = QuizAttempt::create([
                    'user_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    ...$attributes,
                ]);
            }

            foreach ($grade['answers'] as $questionId => $answer) {
                if (! QuizQuestion::query()->whereKey((int) $questionId)->exists()) {
                    continue;
                }

                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => (int) $questionId,
                    'answer' => $answer['answer'],
                    'is_correct' => $answer['correct'],
                ]);
            }

            return $attempt;
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
            'earned_points' => $grade['earned_points'],
            'possible_points' => $grade['possible_points'],
            'answers' => $passed
                ? $grade['answers']
                : collect($grade['answers'])
                    ->map(fn (array $answer): array => ['answer' => $answer['answer']])
                    ->all(),
            'review_questions' => $this->reviewQuestionsForAttempt($attempt),
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
    public function presentationFor(Quiz $quiz, int $userId, bool $remember = true, bool $persistAttempt = true): array
    {
        $attemptNumber = $this->attemptsUsed($quiz, $userId);
        $version = $this->lastEditedAt($quiz)?->getTimestamp();
        $groups = $this->setGroups($quiz);
        $session = $this->session();
        $key = self::PRESENTATION_SESSION_PREFIX.$userId.'.'.$quiz->id;

        $stored = $session?->get($key);
        if (is_array($stored)
            && ($stored['attempt_number'] ?? null) === $attemptNumber
            && (is_array($stored['question_snapshot'] ?? null)
                || (($stored['version'] ?? null) === $version
                    && ($quiz->questions_per_attempt !== null
                        ? $this->sameDrawQuestions($quiz, $stored['question_ids'] ?? [])
                        : ($groups->has((string) ($stored['set_id'] ?? '0'))
                            && $this->sameQuestions($groups->get((string) ($stored['set_id'] ?? '0')), $stored['question_ids'] ?? [])))))) {
            if ($remember && $persistAttempt && empty($stored['attempt_id'])) {
                $stored = $this->persistActivePresentation($quiz, $userId, $stored);
                $session?->put($key, $stored);
            }

            return $stored;
        }

        $pending = QuizAttempt::query()
            ->where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->whereNotNull('question_snapshot')
            ->latest('id')
            ->first();
        if ($pending && is_array($pending->presentation) && is_array($pending->question_snapshot)) {
            $presentation = $pending->presentation;
            $presentation['attempt_number'] = $attemptNumber;
            $presentation['question_snapshot'] = $pending->question_snapshot;
            $presentation['attempt_id'] = (int) $pending->id;
            if ($remember) {
                $session?->put($key, $presentation);
            }

            return $presentation;
        }

        $presentation = $this->buildPresentation($quiz, $userId, $attemptNumber, $version, $groups);
        $presentation['question_snapshot'] = $this->questionSnapshot($quiz, $presentation);
        if ($remember && $persistAttempt) {
            $presentation = $this->persistActivePresentation($quiz, $userId, $presentation);
        }
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
        if (is_array($presentation['question_snapshot'] ?? null)) {
            $choiceOrders = $presentation['choice_orders'] ?? [];

            return collect($presentation['question_snapshot']['questions'])->map(function (array $snapshot) use ($choiceOrders): array {
                $type = (string) ($snapshot['type'] ?? '');
                $options = $type === 'Identification' ? [] : array_values((array) ($snapshot['options'] ?? []));
                $order = $choiceOrders[(string) ($snapshot['id'] ?? '')] ?? array_keys($options);
                $presented = [];
                foreach ($options as $displayIndex => $option) {
                    $presented[] = [
                        'value' => chr(65 + (int) ($order[$displayIndex] ?? $displayIndex)),
                        'label' => chr(65 + $displayIndex),
                        'text' => (string) $option,
                    ];
                }

                $question = new QuizQuestion([
                    'question' => (string) ($snapshot['text'] ?? ''),
                    'type' => $type,
                    'points' => $snapshot['points'] ?? null,
                    'options' => $options,
                    'correct_answer' => $snapshot['correct_answer'] ?? null,
                ]);
                $question->setAttribute('id', (int) ($snapshot['id'] ?? 0));
                $question->exists = true;

                return ['question' => $question, 'options' => $presented];
            })->values()->all();
        }

        $choiceOrders = $presentation['choice_orders'] ?? [];

        return $this->questionsInPresentation($quiz, $presentation)->map(function (QuizQuestion $question) use ($choiceOrders): array {
            $options = $question->type === 'Identification' ? [] : array_values((array) ($question->options ?? []));
            if ($question->type === 'Identification') {
                $question->setAttribute('options', []);
            }
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

    /**
     * Return snapshot question data in a learner-safe shape for an authorized
     * post-submission review. The storage snapshot itself is never serialized.
     *
     * @return list<array<string, mixed>>|null
     */
    public function reviewQuestionsForAttempt(QuizAttempt $attempt): ?array
    {
        $snapshot = $attempt->question_snapshot;
        if (! is_array($snapshot['questions'] ?? null)) {
            return null;
        }

        $includeCorrectAnswers = (bool) $attempt->passed;

        return collect($snapshot['questions'])->map(function (array $question) use ($attempt, $includeCorrectAnswers): array {
            $options = ($question['type'] ?? null) === 'Identification' ? [] : array_values((array) ($question['options'] ?? []));
            $answer = $question['correct_answer'] ?? null;
            $order = $attempt->presentation['choice_orders'][(string) ($question['id'] ?? '')] ?? array_keys($options);

            $review = [
                'id' => (int) ($question['id'] ?? 0),
                'label' => mb_substr((string) ($question['text'] ?? ''), 0, 40),
                'question' => (string) ($question['text'] ?? ''),
                'type' => (string) ($question['type'] ?? ''),
                'points' => $question['points'] ?? null,
                'options' => collect($options)->map(fn ($option, int $index): array => [
                    'letter' => chr(65 + (int) ($order[$index] ?? $index)),
                    'label' => chr(65 + $index),
                    'text' => (string) $option,
                    ...($includeCorrectAnswers ? ['is_correct' => is_string($answer) && $option === $answer] : []),
                ])->all(),
            ];

            if ($includeCorrectAnswers && ($question['type'] ?? null) === 'Identification') {
                $review['review_answer'] = (string) $answer;
            }

            return $review;
        })->values()->all();
    }

    /** @return Collection<int, QuizQuestion> */
    public function questionsInPresentation(Quiz $quiz, array $presentation): Collection
    {
        $snapshotQuestions = $presentation['question_snapshot']['questions'] ?? null;
        if (is_array($snapshotQuestions)) {
            $choiceOrders = $presentation['choice_orders'] ?? [];

            return collect($snapshotQuestions)->map(function (array $snapshot) use ($choiceOrders): QuizQuestion {
                $options = array_values((array) ($snapshot['options'] ?? []));
                $order = $choiceOrders[(string) ($snapshot['id'] ?? '')] ?? array_keys($options);
                $authoredOptions = [];
                foreach ($options as $displayIndex => $option) {
                    $authoredOptions[(int) ($order[$displayIndex] ?? $displayIndex)] = (string) $option;
                }
                ksort($authoredOptions);

                $question = new QuizQuestion([
                    'question' => (string) ($snapshot['text'] ?? ''),
                    'type' => (string) ($snapshot['type'] ?? ''),
                    'points' => $snapshot['points'] ?? null,
                    'options' => array_values($authoredOptions),
                    'correct_answer' => $snapshot['correct_answer'] ?? null,
                ]);
                $question->setAttribute('id', (int) ($snapshot['id'] ?? 0));
                $question->exists = true;

                return $question;
            })->values();
        }

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
        if ($quiz->questions_per_attempt !== null) {
            return $this->buildDrawPresentation($quiz, $userId, $attemptNumber, $version, $groups);
        }

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

    /** @param Collection<string, Collection<int, QuizQuestion>> $groups */
    private function buildDrawPresentation(Quiz $quiz, int $userId, int $attemptNumber, ?int $version, Collection $groups): array
    {
        $setKey = (string) ($groups->keys()->first() ?? '0');
        $questions = $groups->get($setKey, collect());
        $poolIds = $questions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $drawCount = min(max(0, (int) $quiz->questions_per_attempt), count($poolIds));
        $seenIds = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->get(['presentation'])
            ->flatMap(fn (QuizAttempt $attempt) => (array) ($attempt->presentation['question_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
        $seen = array_values(array_intersect($poolIds, $seenIds));
        $unseen = array_values(array_diff($poolIds, $seen));
        $drawSeed = SeededShuffle::seed('draw', (string) $userId, (string) $quiz->id, (string) $attemptNumber, (string) ($version ?? 0));
        $drawn = array_slice(SeededShuffle::shuffle($unseen, $drawSeed), 0, $drawCount);
        if (count($drawn) < $drawCount) {
            $seenSeed = SeededShuffle::seed('seen', (string) $drawSeed);
            $drawn = array_merge($drawn, array_slice(SeededShuffle::shuffle($seen, $seenSeed), 0, $drawCount - count($drawn)));
        }

        $questionSeed = SeededShuffle::seed('questions', (string) $userId, (string) $quiz->id, (string) $attemptNumber);
        if ($quiz->shuffle_questions) {
            $drawn = SeededShuffle::shuffle($drawn, $questionSeed);
        } else {
            $positions = array_flip($poolIds);
            usort($drawn, fn (int $left, int $right): int => $positions[$left] <=> $positions[$right]);
        }

        $choiceSeed = SeededShuffle::seed('choices', (string) $userId, (string) $quiz->id, (string) $attemptNumber);
        $choiceOrders = [];
        foreach ($questions->whereIn('id', $drawn) as $question) {
            $count = count((array) ($question->options ?? []));
            $order = $count > 0 ? range(0, $count - 1) : [];
            if ($count > 0 && $quiz->shuffle_choices && $this->choicesShuffle($question)) {
                $order = SeededShuffle::shuffle($order, SeededShuffle::seed((string) $choiceSeed, (string) $question->id));
            }
            $choiceOrders[(string) $question->id] = $order;
        }

        $set = $setKey !== '0' ? $quiz->orderedSets()->firstWhere('id', (int) $setKey) : null;

        return [
            'quiz_id' => (int) $quiz->id,
            'attempt_number' => $attemptNumber,
            'version' => $version,
            'set_id' => $set?->id ? (int) $set->id : ($setKey !== '0' ? (int) $setKey : null),
            'set_name' => $set?->name ?? 'Set A',
            'set_index' => 0,
            'set_count' => 1,
            'question_seed' => $questionSeed,
            'choice_seed' => $choiceSeed,
            'question_ids' => array_values($drawn),
            'choice_orders' => $choiceOrders,
            'draw_seed' => $drawSeed,
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

    /** A saved draw remains valid when its IDs are a unique subset of the current pool. */
    private function sameDrawQuestions(Quiz $quiz, array $ids): bool
    {
        if ($quiz->orderedSets()->count() > 1) {
            return false;
        }

        $poolIds = $quiz->questionsForSet()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $stored = collect($ids)->map(fn ($id) => (int) $id)->values()->all();
        $expectedCount = min(max(0, (int) $quiz->questions_per_attempt), count($poolIds));

        return count($stored) === $expectedCount
            && count($stored) === count(array_unique($stored))
            && array_diff($stored, $poolIds) === [];
    }

    /** What is stored on the attempt so review can reproduce the served order after later edits. */
    private function presentationSnapshot(array $presentation): array
    {
        $snapshot = [
            'set_id' => $presentation['set_id'],
            'set_name' => $presentation['set_name'],
            'attempt_number' => $presentation['attempt_number'],
            'question_seed' => $presentation['question_seed'],
            'choice_seed' => $presentation['choice_seed'],
            'question_ids' => $presentation['question_ids'],
            'choice_orders' => $presentation['choice_orders'],
        ];

        if (isset($presentation['draw_seed'])) {
            $snapshot['draw_seed'] = $presentation['draw_seed'];
        }

        return $snapshot;
    }

    /** @return array{v:int,questions:list<array{id:int,text:string,type:string,points:int|null,options:list<string>,correct_answer:?string}>} */
    private function questionSnapshot(Quiz $quiz, array $presentation): array
    {
        $questions = ($quiz->relationLoaded('questions') ? $quiz->questions : $quiz->questions()->get())->keyBy('id');
        $choiceOrders = $presentation['choice_orders'] ?? [];
        $snapshots = [];

        foreach ($presentation['question_ids'] ?? [] as $id) {
            $question = $questions->get((int) $id);
            if (! $question) {
                continue;
            }

            $options = array_values((array) ($question->options ?? []));
            $order = $choiceOrders[(string) $question->id] ?? array_keys($options);
            if (count($order) !== count($options) || array_diff(array_keys($options), $order) !== []) {
                $order = array_keys($options);
            }

            $snapshots[] = [
                'id' => (int) $question->id,
                'text' => (string) $question->question,
                'type' => (string) $question->type,
                'points' => $question->points === null ? null : (int) $question->points,
                'options' => array_values(array_map(fn ($index): string => (string) $options[$index], $order)),
                'correct_answer' => $question->correct_answer,
            ];
        }

        return ['v' => 1, 'questions' => $snapshots];
    }

    private function persistActivePresentation(Quiz $quiz, int $userId, array $presentation): array
    {
        $activeAttempt = QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'quiz_set_id' => $presentation['set_id'],
            'shuffle_seed' => $presentation['question_seed'],
            'presentation' => $this->presentationSnapshot($presentation),
            'question_snapshot' => $presentation['question_snapshot'],
            'score' => 0,
            'passed' => false,
            'started_at' => now(),
        ]);

        $presentation['attempt_id'] = (int) $activeAttempt->id;

        return $presentation;
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
        $snapshotAttemptIds = $this->completedSnapshotAttemptIds((int) $quiz->id, $userId);

        return QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->where(function ($query) use ($snapshotAttemptIds): void {
                $query->whereHas('answers');
                if ($snapshotAttemptIds !== []) {
                    $query->orWhereIn('id', $snapshotAttemptIds);
                }
            })
            ->latest('submitted_at')
            ->first();
    }

    /** @return list<int> */
    private function completedSnapshotAttemptIds(int $quizId, int $userId, ?Carbon $editedAt = null): array
    {
        return QuizAttempt::query()
            ->where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->whereNotNull('submitted_at')
            ->whereNotNull('question_snapshot')
            ->when($editedAt, fn ($query) => $query->where(function ($where) use ($editedAt): void {
                $where->where('submitted_at', '>=', $editedAt)
                    ->orWhere('created_at', '>=', $editedAt);
            }))
            ->get(['id', 'question_snapshot'])
            ->filter(fn (QuizAttempt $attempt): bool => count($attempt->question_snapshot['questions'] ?? []) > 0)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }
}
