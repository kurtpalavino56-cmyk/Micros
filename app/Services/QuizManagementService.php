<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LessonActivity;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizSet;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizManagementService
{
    /** Question types the builder offers. */
    public const TYPES = ['Multiple Choice', 'True or False', 'Identification'];

    public function save(Request $request, Course $course, CourseModule $module): bool
    {
        return $this->persist($request, $course, ['module_id' => $module->id, 'lesson_id' => null, 'is_summative' => true]);
    }

    public function saveRegularForModule(Request $request, Course $course, CourseModule $module, ?Quiz $quiz = null): bool
    {
        return $this->persist($request, $course, [
            'module_id' => $module->id,
            'lesson_id' => null,
            'is_summative' => false,
        ], $quiz, $quiz === null);
    }

    public function saveForLesson(Request $request, Course $course, CourseLesson $lesson): bool
    {
        return $this->persist($request, $course, [
            'module_id' => $lesson->module_id,
            'lesson_id' => $lesson->id,
            'is_summative' => false,
        ]);
    }

    /** @param array{module_id: int|null, lesson_id: int|null, is_summative: bool} $scope */
    private function persist(Request $request, Course $course, array $scope, ?Quiz $targetQuiz = null, bool $createNew = false): bool
    {
        $passing = (int) $request->input('passing_score', 0);
        if ($passing < 1 || $passing > 100) {
            $passing = 75;
        }

        $existingQuiz = $targetQuiz ?? ($createNew ? null : Quiz::where($scope)->first());
        [$sets, $isNested] = $this->parseSets($request, $existingQuiz);
        $quizData = [
            'course_id' => $course->id,
            'title' => trim($request->input('quiz_title', '')) ?: 'Untitled Quiz',
            'passing_score' => $passing,
            'attempts' => trim((string) $request->input('attempts', '')) ?: null,
            'time_limit' => (int) $request->input('time_limit', 0),
            'instructions' => RichTextSanitizer::sanitize($request->input('instructions', '')),
            // Presentation settings only: changing them never restarts attempts.
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_choices' => $request->boolean('shuffle_choices'),
            'is_active' => true,
        ];
        if ($scope['lesson_id'] !== null) {
            $quizData['sort_order'] = $existingQuiz?->sort_order
                ?? max(
                    (int) LessonActivity::where('lesson_id', $scope['lesson_id'])->max('sort_order'),
                    (int) Quiz::where('lesson_id', $scope['lesson_id'])->max('sort_order'),
                ) + 1;
        }
        if (! $scope['is_summative']) {
            $quizData['module_order'] = $existingQuiz?->module_order ?? CourseModule::findOrFail($scope['module_id'])->nextContentOrder();
        }
        if ($targetQuiz) {
            $targetQuiz->update($scope + $quizData);
            $quiz = $targetQuiz;
        } elseif ($createNew) {
            $quiz = Quiz::create($scope + $quizData);
        } else {
            $quiz = Quiz::updateOrCreate($scope, $quizData);
        }

        $module = CourseModule::findOrFail($scope['module_id']);
        if (! $scope['is_summative']) {
            $summativeQuiz = $module->quiz;
            $summativeQuiz?->update(['module_order' => (int) $quiz->module_order + 1000]);
        } else {
            $quiz->update(['module_order' => $module->nextContentOrder()]);
        }

        $questionsChanged = DB::transaction(fn (): bool => $this->syncSets($quiz, $sets, $isNested));

        if ($questionsChanged || ! $quiz->questions_changed_at) {
            $quiz->questions_changed_at = now();
        }

        $quiz->save();
        $quiz->touch();

        return $questionsChanged;
    }

    /**
     * Save sets and questions in place, by ID, so quiz_attempt_answers rows
     * (which cascade from quiz_questions) survive every edit that does not
     * remove the question they belong to.
     *
     * Returns true when question content or set structure changed: that
     * bumps questions_changed_at (and so restarts attempts). Renaming or
     * reordering sets, the shuffle toggles and "lock choice order" do not.
     *
     * @param  list<array{id:int|null,name:string,questions:list<array<string,mixed>>}>  $sets
     */
    private function syncSets(Quiz $quiz, array $sets, bool $isNested): bool
    {
        $existingSets = QuizSet::query()->where('quiz_id', $quiz->id)->orderBy('position')->orderBy('id')->get()->keyBy('id');
        $existingQuestions = QuizQuestion::query()->where('quiz_id', $quiz->id)->orderBy('position')->orderBy('id')->get()->keyBy('id');

        // Legacy questions with no set join the first set (or a new Set A).
        if ($existingQuestions->contains(fn (QuizQuestion $question) => ! $question->quiz_set_id)) {
            $defaultSet = $existingSets->first() ?? QuizSet::query()->create(['quiz_id' => $quiz->id, 'name' => 'Set A', 'position' => 0]);
            QuizQuestion::query()->where('quiz_id', $quiz->id)->whereNull('quiz_set_id')->update(['quiz_set_id' => $defaultSet->id]);
            $existingSets->put($defaultSet->id, $defaultSet);
            $existingQuestions = QuizQuestion::query()->where('quiz_id', $quiz->id)->orderBy('position')->orderBy('id')->get()->keyBy('id');
        }

        // The flat legacy form ("questions[]", no ids) edits the first set in
        // place, matching cards to existing questions by position.
        if (! $isNested && $existingSets->isNotEmpty()) {
            $firstSet = $existingSets->first();
            $sets[0]['id'] = $firstSet->id;
            $sets[0]['name'] = $firstSet->name;
            $positional = $existingQuestions->where('quiz_set_id', $firstSet->id)->values();
            foreach ($sets[0]['questions'] as $index => $question) {
                $sets[0]['questions'][$index]['id'] = $positional->get($index)?->id;
            }
        }

        // Ignore ids that do not belong to this quiz (treated as new rows).
        foreach ($sets as $s => $set) {
            if ($set['id'] !== null && ! $existingSets->has($set['id'])) {
                $sets[$s]['id'] = null;
            }
            foreach ($set['questions'] as $q => $question) {
                if ($question['id'] !== null && ! $existingQuestions->has($question['id'])) {
                    $sets[$s]['questions'][$q]['id'] = null;
                }
            }
        }

        $keptSetIds = collect($sets)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $keptQuestionIds = collect($sets)->flatMap(fn ($set) => collect($set['questions'])->pluck('id'))->filter()->map(fn ($id) => (int) $id)->values()->all();
        // filter() keeps the id keys (Eloquent's except() re-indexes them).
        $removedQuestions = $existingQuestions->filter(fn (QuizQuestion $question) => ! in_array((int) $question->id, $keptQuestionIds, true));
        $removedSets = $existingSets->filter(fn (QuizSet $set) => ! in_array((int) $set->id, $keptSetIds, true));

        $this->guardAttemptHistory($quiz, $removedQuestions, $removedSets);

        $before = $this->signature($existingSets->map(fn (QuizSet $set) => [
            'id' => $set->id,
            'questions' => $existingQuestions->where('quiz_set_id', $set->id)->values()->map(fn (QuizQuestion $question) => [
                'question' => (string) $question->question,
                'type' => (string) $question->type,
                'points' => (int) $question->points,
                'options' => array_values((array) ($question->options ?? [])),
                'correct_answer' => $question->correct_answer,
            ])->all(),
        ])->values()->all());

        $savedSetIds = [];
        foreach (array_values($sets) as $position => $set) {
            $model = $set['id'] ? $existingSets->get($set['id']) : new QuizSet(['quiz_id' => $quiz->id]);
            $model->fill(['name' => $set['name'], 'position' => $position])->save();
            $savedSetIds[$position] = $model->id;

            foreach (array_values($set['questions']) as $questionPosition => $question) {
                $row = $question['id'] ? $existingQuestions->get($question['id']) : new QuizQuestion(['quiz_id' => $quiz->id]);
                $row->fill([
                    'quiz_set_id' => $model->id,
                    'position' => $questionPosition,
                    'question' => $question['question'],
                    'type' => $question['type'],
                    'points' => $question['points'],
                    'options' => $question['options'],
                    'correct_answer' => $question['correct_answer'],
                    'lock_choice_order' => $question['lock_choice_order'],
                ])->save();
            }
        }

        if ($removedQuestions->isNotEmpty()) {
            QuizQuestion::query()->whereKey($removedQuestions->keys())->delete();
        }
        if ($removedSets->isNotEmpty()) {
            QuizSet::query()->whereKey($removedSets->keys())->delete();
        }

        $after = $this->signature(collect(array_values($sets))->map(fn ($set, $position) => [
            'id' => $set['id'] ?? 'new-'.$savedSetIds[$position],
            'questions' => array_map(fn ($question) => [
                'question' => (string) $question['question'],
                'type' => (string) $question['type'],
                'points' => (int) $question['points'],
                'options' => array_values((array) $question['options']),
                'correct_answer' => $question['correct_answer'],
            ], array_values($set['questions'])),
        ])->all());

        return $before !== $after;
    }

    /**
     * Removing a question students answered, or a set attempts were served,
     * would cascade-delete their attempt history. That is refused instead.
     *
     * @param  Collection<int, QuizQuestion>  $removedQuestions
     * @param  Collection<int, QuizSet>  $removedSets
     */
    private function guardAttemptHistory(Quiz $quiz, $removedQuestions, $removedSets): void
    {
        $messages = [];

        if ($removedQuestions->isNotEmpty()) {
            $answered = QuizAttemptAnswer::query()->whereIn('question_id', $removedQuestions->keys())->distinct()->pluck('question_id');
            if ($answered->isNotEmpty()) {
                $messages['sets'][] = $answered->count() === 1
                    ? 'One question you removed has already been answered by students, so it cannot be deleted. Edit its text instead, or keep it.'
                    : $answered->count().' questions you removed have already been answered by students, so they cannot be deleted. Edit their text instead, or keep them.';
            }
        }

        if ($removedSets->isNotEmpty()) {
            $usedSetIds = QuizAttempt::query()->where('quiz_id', $quiz->id)->whereIn('quiz_set_id', $removedSets->keys())->distinct()->pluck('quiz_set_id');
            foreach ($usedSetIds as $setId) {
                $messages['sets'][] = '"'.$removedSets->get($setId)->name.'" has already been served to students, so it cannot be deleted.';
            }
        }

        if ($messages) {
            throw ValidationException::withMessages($messages);
        }
    }

    /** @param list<array{id:int|string,questions:list<array<string,mixed>>}> $sets */
    private function signature(array $sets): array
    {
        $keyed = [];
        foreach ($sets as $set) {
            $keyed[(string) $set['id']] = $set['questions'];
        }
        ksort($keyed);

        return $keyed;
    }

    public function delete(CourseModule $module): ?string
    {
        $quiz = $module->quiz;
        if (! $quiz) {
            return null;
        }

        $title = $quiz->title;
        $quiz->delete();

        return $title;
    }

    /**
     * Normalize the builder payload into sets of questions.
     *
     * Accepts the nested shape sets[s][questions][q][...] and the legacy flat
     * questions[q][...] (treated as one set), so older callers keep working.
     * Blank cards (no question text) are drafts and are ignored.
     *
     * Validation lives here, not only in the browser: every set has the same
     * number of questions and every question is valid for its type.
     *
     * @return array{0: list<array{id:int|null,name:string,questions:list<array<string,mixed>>}>, 1: bool}
     */
    private function parseSets(Request $request, ?Quiz $quiz): array
    {
        $rawSets = $request->input('sets');
        $isNested = is_array($rawSets) && $rawSets !== [];
        if (! $isNested) {
            $rawSets = [['id' => null, 'name' => 'Set A', 'questions' => (array) $request->input('questions', [])]];
        }

        $errors = [];
        $sets = [];
        foreach (array_values($rawSets) as $s => $rawSet) {
            $rawSet = (array) $rawSet;
            $name = trim((string) ($rawSet['name'] ?? ''));
            $name = mb_substr($name !== '' ? $name : 'Set '.$this->setLetter($s), 0, 60);
            $questions = [];
            foreach (array_values((array) ($rawSet['questions'] ?? [])) as $q => $rawQuestion) {
                $parsed = $this->parseQuestion((array) $rawQuestion);
                if ($parsed === null) {
                    continue;
                }
                if ($parsed['error']) {
                    $errors['sets.'.$s.'.questions.'.$q][] = $name.', question '.(count($questions) + 1).': '.$parsed['error'];
                }
                unset($parsed['error']);
                $questions[] = $parsed;
            }

            $id = isset($rawSet['id']) && is_numeric($rawSet['id']) ? (int) $rawSet['id'] : null;
            $sets[] = ['id' => $id, 'name' => $name, 'questions' => $questions];
        }

        $counts = array_map(fn ($set) => count($set['questions']), $sets);
        if (count(array_unique($counts)) > 1) {
            $summary = implode(', ', array_map(fn ($set) => $set['name'].' has '.count($set['questions']), $sets));
            $errors['sets'][] = 'Every set must have the same number of questions ('.$summary.').';
        }

        if ($quiz && max($counts ?: [0]) === 0 && QuizAttempt::query()->where('quiz_id', $quiz->id)->exists()) {
            $errors['sets'][] = 'Students have already taken this quiz, so it needs at least one question.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return [$sets, $isNested];
    }

    /**
     * Rules by type. Multiple Choice: at least two non-empty choices and a
     * correct choice. True or False: a correct answer. Identification: an
     * answer. Points are never negative.
     *
     * @param  array<string, mixed>  $rawQuestion
     * @return array<string, mixed>|null null for a blank draft card
     */
    private function parseQuestion(array $rawQuestion): ?array
    {
        $text = trim((string) ($rawQuestion['text'] ?? ''));
        if ($text === '') {
            return null;
        }

        $type = in_array($rawQuestion['type'] ?? null, self::TYPES, true) ? $rawQuestion['type'] : 'Multiple Choice';
        $error = null;
        if ($type === 'Identification') {
            $choices = [];
            $correct = trim((string) ($rawQuestion['answer'] ?? ''));
            if ($correct === '') {
                $error = 'type the correct answer.';
            }
            $correct = $correct !== '' ? $correct : null;
        } elseif ($type === 'True or False') {
            $choices = ['True', 'False'];
            $correct = isset($rawQuestion['tf_correct']) && $rawQuestion['tf_correct'] !== null && $rawQuestion['tf_correct'] !== ''
                ? ($choices[(int) $rawQuestion['tf_correct']] ?? null)
                : null;
            if ($correct === null) {
                $error = 'choose True or False as the correct answer.';
            }
        } else {
            $rawChoices = array_map(fn ($choice) => trim((string) $choice), (array) ($rawQuestion['choices'] ?? []));
            $choices = array_values(array_filter($rawChoices, fn ($choice) => $choice !== ''));
            // "correct" is the index of the radio among the RAW choice inputs;
            // map it through the filter so a blank choice above it cannot
            // shift the answer onto the wrong option.
            $correct = null;
            if (isset($rawQuestion['correct']) && $rawQuestion['correct'] !== null && $rawQuestion['correct'] !== '') {
                $picked = $rawChoices[(int) $rawQuestion['correct']] ?? '';
                $correct = $picked !== '' ? $picked : null;
            }
            if (count($choices) < 2) {
                $error = 'add at least two choices.';
            } elseif (count($choices) !== count(array_unique($choices))) {
                $error = 'each choice must be different.';
            } elseif ($correct === null) {
                $error = 'mark the correct choice.';
            }
        }

        $points = (int) ($rawQuestion['points'] ?? 0);
        if ($points < 0) {
            $error ??= 'points cannot be negative.';
        }

        return [
            'id' => isset($rawQuestion['id']) && is_numeric($rawQuestion['id']) ? (int) $rawQuestion['id'] : null,
            'question' => $text,
            'type' => $type,
            'points' => max(0, $points),
            'options' => $choices,
            'correct_answer' => $correct,
            'lock_choice_order' => $type === 'Multiple Choice' && filter_var($rawQuestion['lock_choice_order'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'error' => $error,
        ];
    }

    private function setLetter(int $index): string
    {
        $letters = '';
        $index++;
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }
}
