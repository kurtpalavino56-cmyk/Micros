<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizSet;
use App\Models\User;
use App\Services\QuizAttemptService;
use App\Services\QuizGradingService;
use App\Services\QuizManagementService;
use App\Support\SeededShuffle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * A course with one lesson-free module whose summative quiz is open to an
 * enrolled student straight away. Each set gets $perSet multiple-choice
 * questions whose correct answer is always the first option.
 *
 * @return array{0: Course, 1: CourseModule, 2: Quiz, 3: User}
 */
function qsFixture(int $sets = 2, int $perSet = 3, array $quizAttributes = []): array
{
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = Course::create([
        'title' => 'Sets Course',
        'slug' => 'sets-course-'.uniqid(),
        'description' => 'Course for quiz set tests',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
    ]);
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module One', 'order' => 1]);
    $quiz = Quiz::create(array_merge([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'is_summative' => true,
        'title' => 'Sets Quiz',
        'passing_score' => 70,
        'attempts' => 'Unlimited',
        'is_active' => true,
    ], $quizAttributes));

    for ($s = 0; $s < $sets; $s++) {
        $set = QuizSet::create(['quiz_id' => $quiz->id, 'name' => 'Set '.chr(65 + $s), 'position' => $s]);
        for ($q = 0; $q < $perSet; $q++) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'quiz_set_id' => $set->id,
                'position' => $q,
                'question' => "Set {$s} question {$q}",
                'type' => 'Multiple Choice',
                'options' => ["Right {$s}-{$q}", "Wrong one {$s}-{$q}", "Wrong two {$s}-{$q}", "Wrong three {$s}-{$q}"],
                'correct_answer' => "Right {$s}-{$q}",
                'points' => 1,
            ]);
        }
    }
    $quiz->update(['questions_changed_at' => now()->subHour()]);
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now()->subDay(), 'progress_state' => []]);

    return [$course, $module, $quiz->fresh(), $student];
}

/** Original-position letter of each question's correct option (what a correct student submits). */
function qsCorrectAnswers(Quiz $quiz, array $questionIds): array
{
    $answers = [];
    foreach ($questionIds as $id) {
        $question = QuizQuestion::findOrFail($id);
        $answers[(string) $id] = chr(65 + array_search($question->correct_answer, $question->options, true));
    }

    return $answers;
}

/** Builder payload in the nested shape sets[s][questions][q]. */
function qsSetsPayload(array $sets, array $extra = []): Request
{
    return Request::create('/quiz', 'POST', array_merge(['quiz_title' => 'Sets Quiz', 'passing_score' => 70, 'sets' => $sets], $extra));
}

function qsMcQuestion(string $text, array $choices = ['A', 'B', 'C'], int $correct = 0, ?int $id = null): array
{
    return ['id' => $id, 'text' => $text, 'type' => 'Multiple Choice', 'points' => 1, 'choices' => $choices, 'correct' => $correct];
}

// ── Migration ────────────────────────────────────────────────────────────

test('the migration backfills one Set A per quiz and rolls back cleanly', function () {
    $migration = require database_path('migrations/2026_11_06_000001_add_quiz_sets_and_shuffling.php');
    $migration->down();

    expect(Schema::hasTable('quiz_sets'))->toBeFalse()
        ->and(Schema::hasColumn('quiz_questions', 'quiz_set_id'))->toBeFalse()
        ->and(Schema::hasColumn('quiz_attempts', 'shuffle_seed'))->toBeFalse()
        ->and(Schema::hasColumn('quizzes', 'shuffle_questions'))->toBeFalse();

    // Pre-sets data, written the way the old code wrote it.
    $faculty = User::factory()->faculty()->create();
    $courseId = DB::table('courses')->insertGetId(['title' => 'Legacy', 'slug' => 'legacy-'.uniqid(), 'created_by' => $faculty->id, 'created_at' => now(), 'updated_at' => now()]);
    $quizId = DB::table('quizzes')->insertGetId(['course_id' => $courseId, 'title' => 'Legacy quiz', 'created_at' => now(), 'updated_at' => now()]);
    $emptyQuizId = DB::table('quizzes')->insertGetId(['course_id' => $courseId, 'title' => 'Empty quiz', 'created_at' => now(), 'updated_at' => now()]);
    $ids = [];
    foreach (['First', 'Second', 'Third'] as $text) {
        $ids[] = DB::table('quiz_questions')->insertGetId(['quiz_id' => $quizId, 'question' => $text, 'options' => '["x","y"]', 'correct_answer' => 'x', 'points' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    $migration->up();

    $sets = DB::table('quiz_sets')->orderBy('quiz_id')->get();
    expect($sets)->toHaveCount(2)
        ->and($sets->pluck('name')->unique()->all())->toBe(['Set A'])
        ->and($sets->pluck('quiz_id')->all())->toBe([$quizId, $emptyQuizId]);

    $setId = $sets->firstWhere('quiz_id', $quizId)->id;
    $questions = DB::table('quiz_questions')->whereIn('id', $ids)->orderBy('id')->get();
    expect($questions->pluck('quiz_set_id')->unique()->all())->toBe([$setId])
        ->and($questions->pluck('position')->map(fn ($p) => (int) $p)->all())->toBe([0, 1, 2])
        ->and($questions->pluck('quiz_id')->unique()->all())->toBe([$quizId]);

    // Rolling back keeps every question attached to its quiz.
    $migration->down();
    expect(DB::table('quiz_questions')->where('quiz_id', $quizId)->count())->toBe(3);
    $migration->up();
});

// ── Builder persistence and validation ───────────────────────────────────

test('saving rejects sets with different question counts', function () {
    [, $module, $quiz] = qsFixture(sets: 1, perSet: 1);

    expect(fn () => app(QuizManagementService::class)->save(qsSetsPayload([
        ['name' => 'Set A', 'questions' => [qsMcQuestion('One'), qsMcQuestion('Two')]],
        ['name' => 'Set B', 'questions' => [qsMcQuestion('Only one')]],
    ]), $quiz->course, $module))->toThrow(ValidationException::class, 'Every set must have the same number of questions');

    expect($quiz->fresh()->questions()->count())->toBe(1);
});

test('saving validates each question by type on the server', function (array $question, string $message) {
    [, $module, $quiz] = qsFixture(sets: 1, perSet: 1);

    expect(fn () => app(QuizManagementService::class)->save(qsSetsPayload([
        ['name' => 'Set A', 'questions' => [$question]],
    ]), $quiz->course, $module))->toThrow(ValidationException::class, $message);
})->with([
    'multiple choice without a correct choice' => [['text' => 'Q', 'type' => 'Multiple Choice', 'choices' => ['A', 'B'], 'points' => 1], 'mark the correct choice'],
    'multiple choice with one choice' => [['text' => 'Q', 'type' => 'Multiple Choice', 'choices' => ['A', ''], 'correct' => 0, 'points' => 1], 'at least two choices'],
    'true or false without an answer' => [['text' => 'Q', 'type' => 'True or False', 'points' => 1], 'True or False'],
    'identification without an answer' => [['text' => 'Q', 'type' => 'Identification', 'answer' => ' ', 'points' => 1], 'type the correct answer'],
]);

test('new or edited questions normalize points below one while unchanged legacy zero points stay stored', function () {
    [$course, $module, $quiz] = qsFixture(sets: 1, perSet: 1);
    $question = $quiz->questions()->firstOrFail();
    $set = $quiz->sets()->firstOrFail();

    $question->update(['points' => 0]);
    $unchanged = [[
        'id' => $set->id,
        'name' => $set->name,
        'questions' => [array_replace(qsMcQuestion($question->question, $question->options, 0, $question->id), ['points' => '0'])],
    ]];
    app(QuizManagementService::class)->save(qsSetsPayload($unchanged), $course, $module);
    expect($question->fresh()->points)->toBe(0);

    $edited = $unchanged;
    $edited[0]['questions'][0]['text'] = 'Edited legacy question';
    app(QuizManagementService::class)->save(qsSetsPayload($edited), $course, $module);
    expect($question->fresh()->question)->toBe('Edited legacy question')
        ->and($question->fresh()->points)->toBe(1);

    app(QuizManagementService::class)->save(qsSetsPayload([[
        'name' => 'New Set',
        'questions' => [
            array_replace(qsMcQuestion('New zero question', ['A', 'B']), ['points' => 0]),
            array_replace(qsMcQuestion('New blank points question', ['A', 'B']), ['points' => '']),
            array_replace(qsMcQuestion('New negative points question', ['A', 'B']), ['points' => -2]),
        ],
    ]]), $course, $module);
    expect($quiz->fresh()->questions()->whereIn('question', [
        'New zero question', 'New blank points question', 'New negative points question',
    ])->pluck('points')->map(fn ($points) => (int) $points)->all())->toBe([1, 1, 1]);
});

test('editing preserves question ids and attempt history and only changes what was edited', function () {
    [$course, $module, $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $sets = $quiz->sets()->with('questions')->get();
    $answered = $sets[0]->questions[0];
    $attempt = QuizAttempt::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'quiz_set_id' => $sets[0]->id, 'score' => 50, 'passed' => false, 'submitted_at' => now()->subMinutes(10)]);
    QuizAttemptAnswer::create(['quiz_attempt_id' => $attempt->id, 'question_id' => $answered->id, 'answer' => 'A', 'is_correct' => true]);

    $payload = $sets->map(fn (QuizSet $set) => [
        'id' => $set->id,
        'name' => $set->name.' renamed',
        'questions' => $set->questions->map(fn (QuizQuestion $q) => qsMcQuestion($q->question, $q->options, 0, $q->id))->all(),
    ])->all();
    $payload[1]['questions'][1]['text'] = 'Edited text';

    $changed = app(QuizManagementService::class)->save(qsSetsPayload($payload), $course, $module);

    $after = $quiz->fresh()->questions()->orderBy('id')->pluck('id')->all();
    expect($changed)->toBeTrue()
        ->and($after)->toBe($sets->flatMap->questions->pluck('id')->sort()->values()->all())
        ->and(QuizQuestion::find($sets[1]->questions[1]->id)->question)->toBe('Edited text')
        ->and(QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->count())->toBe(1)
        ->and($quiz->sets()->pluck('name')->all())->toBe(['Set A renamed', 'Set B renamed']);
});

test('renaming sets and changing the shuffle toggles do not restart attempts', function () {
    [$course, $module, $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $stamp = $quiz->questions_changed_at;
    QuizAttempt::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 0, 'passed' => false, 'submitted_at' => now()->subMinutes(30)])
        ->answers()->create(['question_id' => $quiz->questions->first()->id, 'answer' => 'B', 'is_correct' => false]);
    $usedBefore = app(QuizAttemptService::class)->attemptsUsed($quiz, $student->id);

    $payload = $quiz->sets()->with('questions')->get()->reverse()->values()->map(fn (QuizSet $set) => [
        'id' => $set->id,
        'name' => 'Renamed '.$set->name,
        'questions' => $set->questions->map(fn (QuizQuestion $q) => qsMcQuestion($q->question, $q->options, 0, $q->id) + ['lock_choice_order' => '1'])->all(),
    ])->all();

    $changed = app(QuizManagementService::class)->save(qsSetsPayload($payload, ['shuffle_questions' => '1', 'shuffle_choices' => '1']), $course, $module);
    $fresh = $quiz->fresh();

    expect($changed)->toBeFalse()
        ->and($fresh->questions_changed_at->equalTo($stamp))->toBeTrue()
        ->and($fresh->shuffle_questions)->toBeTrue()
        ->and($fresh->shuffle_choices)->toBeTrue()
        ->and(app(QuizAttemptService::class)->attemptsUsed($fresh, $student->id))->toBe($usedBefore)->toBe(1);
});

test('adding or deleting a set restarts attempts', function () {
    [$course, $module, $quiz] = qsFixture(sets: 1, perSet: 1);
    $set = $quiz->sets()->with('questions')->first();
    $setPayload = ['id' => $set->id, 'name' => 'Set A', 'questions' => [qsMcQuestion($set->questions[0]->question, $set->questions[0]->options, 0, $set->questions[0]->id)]];

    Carbon::setTestNow(now()->addMinute());
    expect(app(QuizManagementService::class)->save(qsSetsPayload([$setPayload, ['name' => 'Set B', 'questions' => [qsMcQuestion('New')]]]), $course, $module))->toBeTrue();
    expect($quiz->fresh()->sets()->count())->toBe(2);

    Carbon::setTestNow(now()->addMinute());
    expect(app(QuizManagementService::class)->save(qsSetsPayload([$setPayload]), $course, $module))->toBeTrue();
    expect($quiz->fresh()->sets()->count())->toBe(1)
        ->and($quiz->fresh()->questions()->count())->toBe(1);
});

test('removing an answered question or a served set is refused', function () {
    [$course, $module, $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $sets = $quiz->sets()->with('questions')->get();
    $attempt = QuizAttempt::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'quiz_set_id' => $sets[1]->id, 'score' => 0, 'passed' => false, 'submitted_at' => now()]);
    $attempt->answers()->create(['question_id' => $sets[1]->questions[1]->id, 'answer' => 'B', 'is_correct' => false]);
    $keep = fn (QuizSet $set, int $count) => [
        'id' => $set->id,
        'name' => $set->name,
        'questions' => $set->questions->take($count)->map(fn (QuizQuestion $q) => qsMcQuestion($q->question, $q->options, 0, $q->id))->all(),
    ];

    // Removing position 2 drops the answered question from Set B.
    expect(fn () => app(QuizManagementService::class)->save(qsSetsPayload([$keep($sets[0], 1), $keep($sets[1], 1)]), $course, $module))
        ->toThrow(ValidationException::class, 'already been answered');
    // Deleting Set B, which attempts were served (it also holds the answered question).
    try {
        app(QuizManagementService::class)->save(qsSetsPayload([$keep($sets[0], 2)]), $course, $module);
        $this->fail('Deleting a served set should be refused.');
    } catch (ValidationException $exception) {
        expect(implode(' ', $exception->errors()['sets']))->toContain('"Set B" has already been served');
    }

    expect($quiz->fresh()->questions()->count())->toBe(4)
        ->and($attempt->answers()->count())->toBe(1);
});

test('a one-set quiz saved through the legacy flat form behaves exactly as before', function () {
    [$course, $module, $quiz] = qsFixture(sets: 1, perSet: 2);
    $ids = $quiz->questions()->orderBy('id')->pluck('id')->all();
    $flat = Request::create('/quiz', 'POST', [
        'quiz_title' => 'Flat',
        'passing_score' => 70,
        'questions' => [qsMcQuestion('Set 0 question 0', ['Right 0-0', 'Wrong one 0-0'], 0), qsMcQuestion('Set 0 question 1 edited', ['Right 0-1', 'Wrong one 0-1'], 0)],
    ]);

    app(QuizManagementService::class)->save($flat, $course, $module);
    $fresh = $quiz->fresh();

    expect($fresh->sets()->count())->toBe(1)
        ->and($fresh->questions()->orderBy('id')->pluck('id')->all())->toBe($ids)
        ->and($fresh->questionsForSet()->pluck('question')->all())->toBe(['Set 0 question 0', 'Set 0 question 1 edited'])
        ->and($fresh->questionsPerSetCount())->toBe(2)
        ->and($fresh->setCount())->toBe(1);
});

// ── Assignment and shuffling ─────────────────────────────────────────────

test('set assignment is stable across refreshes and rotates on each retake', function () {
    [, , $quiz, $student] = qsFixture(sets: 3, perSet: 2, quizAttributes: ['passing_score' => 100]);
    $service = app(QuizAttemptService::class);

    $first = $service->presentationFor($quiz, $student->id, remember: false);
    expect($service->presentationFor($quiz->fresh(), $student->id, remember: false))->toBe($first);

    $seen = [$first['set_id']];
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        Carbon::setTestNow(now()->addMinutes(6));
        $presentation = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
        $service->submit($student, $quiz->fresh(), ['answers' => []], null, $presentation);
        $seen[] = $service->presentationFor($quiz->fresh(), $student->id, remember: false)['set_id'];
    }

    $setIds = $quiz->sets()->pluck('id')->all();
    expect(array_slice($seen, 0, 3))->toEqualCanonicalizing($setIds)
        ->and($seen[3])->toBe($seen[0]);
});

test('different students are spread across sets', function () {
    [$course, , $quiz] = qsFixture(sets: 2, perSet: 1);
    $service = app(QuizAttemptService::class);

    $assigned = collect(range(1, 24))->map(function () use ($service, $quiz) {
        return $service->presentationFor($quiz, User::factory()->create()->id, remember: false)['set_id'];
    });

    expect($assigned->unique()->count())->toBe(2);
});

test('the served set is remembered in the session between start and submit', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $this->actingAs($student)->postJson(route('quiz.start', $quiz->id))->assertOk();
    $stored = session('quiz_presentation.'.$student->id.'.'.$quiz->id);

    // Faculty adds a set mid-attempt without changing questions_changed_at
    // (e.g. a direct data fix): the student keeps the set already shown.
    $extra = QuizSet::create(['quiz_id' => $quiz->id, 'name' => 'Set C', 'position' => 2]);
    QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $extra->id, 'question' => 'C1', 'type' => 'True or False', 'options' => ['True', 'False'], 'correct_answer' => 'True']);
    QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $extra->id, 'question' => 'C2', 'type' => 'True or False', 'options' => ['True', 'False'], 'correct_answer' => 'True']);

    expect(app(QuizAttemptService::class)->presentationFor($quiz->fresh(), $student->id)['set_id'])->toBe($stored['set_id']);
});

test('seeded shuffles are stable and isolated from the global random generator', function () {
    $items = range(1, 20);
    $order = SeededShuffle::shuffle($items, 12345);

    mt_srand(1);
    shuffle($items);
    expect(SeededShuffle::shuffle(range(1, 20), 12345))->toBe($order)
        ->and(SeededShuffle::shuffle(range(1, 20), 54321))->not->toBe($order)
        ->and($order)->toEqualCanonicalizing(range(1, 20))
        ->and(SeededShuffle::seed('a', 'b'))->toBe(crc32('a:b'));
});

test('question and choice orders are seeded per attempt, and true or false and locked questions keep their choice order', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 6, quizAttributes: ['shuffle_questions' => true, 'shuffle_choices' => true]);
    $set = $quiz->sets()->first();
    $locked = QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $set->id, 'position' => 6, 'question' => 'Locked', 'type' => 'Multiple Choice', 'options' => ['W', 'X', 'Y', 'All of the above'], 'correct_answer' => 'All of the above', 'lock_choice_order' => true]);
    $trueFalse = QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $set->id, 'position' => 7, 'question' => 'TF', 'type' => 'True or False', 'options' => ['True', 'False'], 'correct_answer' => 'False']);
    $service = app(QuizAttemptService::class);

    $presentation = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
    $again = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
    $authored = $quiz->fresh()->questionsForSet()->pluck('id')->all();

    expect($again)->toBe($presentation)
        ->and($presentation['question_ids'])->toEqualCanonicalizing($authored)
        ->and($presentation['question_ids'])->not->toBe($authored)
        ->and($presentation['choice_orders'][(string) $locked->id])->toBe([0, 1, 2, 3])
        ->and($presentation['choice_orders'][(string) $trueFalse->id])->toBe([0, 1])
        ->and(collect($presentation['choice_orders'])->except([(string) $locked->id, (string) $trueFalse->id])->contains(fn ($order) => $order !== [0, 1, 2, 3]))->toBeTrue();

    // A different attempt number yields a different, but equally stable, order.
    expect($service->presentationFor($quiz->fresh(), User::factory()->create()->id, remember: false)['question_ids'])->not->toBe($presentation['question_ids']);
});

test('a drawn attempt has the configured number of unique questions and keeps the same draw on resume', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 8, quizAttributes: ['questions_per_attempt' => 3]);
    $service = app(QuizAttemptService::class);

    $first = $service->presentationFor($quiz->fresh(), $student->id);
    $resumed = $service->presentationFor($quiz->fresh(), $student->id);

    expect($first['question_ids'])->toHaveCount(3)
        ->and(array_unique($first['question_ids']))->toHaveCount(3)
        ->and($resumed['question_ids'])->toBe($first['question_ids'])
        ->and($first['draw_seed'])->toBeInt();
});

test('draws differ by learner and retakes prioritize questions not shown before', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 8, quizAttributes: ['questions_per_attempt' => 3]);
    $service = app(QuizAttemptService::class);
    $first = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
    $draws = collect(range(1, 24))->map(fn () => $service->presentationFor($quiz->fresh(), User::factory()->create()->id, remember: false)['question_ids']);

    $service->submit($student, $quiz->fresh(), ['answers' => array_fill_keys($first['question_ids'], 'B')], null, $first);
    $retake = $service->presentationFor($quiz->fresh(), $student->id, remember: false);

    expect($draws->push($first['question_ids'])->unique()->count())->toBeGreaterThan(1)
        ->and(array_intersect($first['question_ids'], $retake['question_ids']))->toBe([]);
});

test('a pool smaller than the configured draw returns the whole pool', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 3, quizAttributes: ['questions_per_attempt' => 7]);
    $presentation = app(QuizAttemptService::class)->presentationFor($quiz->fresh(), $student->id, remember: false);

    expect($presentation['question_ids'])->toHaveCount(3)
        ->and($presentation['question_ids'])->toEqualCanonicalizing($quiz->questions()->pluck('id')->all());
});

test('weighted grading scores only the questions drawn for the attempt', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 4, quizAttributes: ['questions_per_attempt' => 2]);
    $quiz->questions()->orderBy('position')->first()->update(['points' => 3]);
    $quiz->questions()->orderBy('position')->skip(1)->first()->update(['points' => 1]);
    $quiz->update(['shuffle_questions' => false]);
    $questions = $quiz->questions()->orderBy('position')->take(2)->get();
    $presentation = [
        'quiz_id' => $quiz->id,
        'attempt_number' => 0,
        'version' => null,
        'set_id' => $quiz->sets()->first()->id,
        'set_name' => 'Set A',
        'set_index' => 0,
        'set_count' => 1,
        'question_seed' => 123,
        'choice_seed' => 456,
        'draw_seed' => 789,
        'question_ids' => $questions->pluck('id')->all(),
        'choice_orders' => [],
    ];
    $answers = [
        (string) $questions[0]->id => 'A',
        (string) $questions[1]->id => 'B',
    ];

    $result = app(QuizAttemptService::class)->submit($student, $quiz->fresh(), ['answers' => $answers], null, $presentation);

    expect($result['score'])->toBe(75)
        ->and(QuizAttempt::where('quiz_id', $quiz->id)->latest('id')->first()->presentation['question_ids'])->toBe($presentation['question_ids']);
});

test('a drawn learner payload contains no answer key and repeats the same draw on refresh', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 5, quizAttributes: ['questions_per_attempt' => 2]);
    $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk()->assertDontSee('correct_answer');
    $this->actingAs($student)->postJson(route('quiz.start', $quiz->id))->assertOk()->assertDontSee('correct_answer');
    $first = session('quiz_presentation.'.$student->id.'.'.$quiz->id);

    $this->postJson(route('quiz.start', $quiz->id))->assertOk()->assertDontSee('correct_answer');
    $this->postJson(route('courses.progress', $quiz->course_id), [])->assertOk()->assertDontSee('correct_answer');
    $again = session('quiz_presentation.'.$student->id.'.'.$quiz->id);

    expect($again['question_ids'])->toBe($first['question_ids']);
});

test('saving a draw validates its pool size and rejects quizzes with multiple sets', function () {
    [, $module, $quiz] = qsFixture(sets: 2, perSet: 2);
    $sets = $quiz->sets()->with('questions')->get()->map(fn (QuizSet $set) => [
        'id' => $set->id,
        'name' => $set->name,
        'questions' => $set->questions->map(fn (QuizQuestion $question) => qsMcQuestion($question->question, $question->options, 0, $question->id))->all(),
    ])->all();

    expect(fn () => app(QuizManagementService::class)->save(qsSetsPayload($sets, ['questions_per_attempt' => 1]), $quiz->course, $module))
        ->toThrow(ValidationException::class, 'available only when the quiz has one set');

    [, $singleSetModule, $singleSetQuiz] = qsFixture(sets: 1, perSet: 2);
    $singleSet = $singleSetQuiz->sets()->with('questions')->first();
    $payload = [['id' => $singleSet->id, 'name' => $singleSet->name, 'questions' => $singleSet->questions->map(fn (QuizQuestion $question) => qsMcQuestion($question->question, $question->options, 0, $question->id))->all()]];
    expect(fn () => app(QuizManagementService::class)->save(qsSetsPayload($payload, ['questions_per_attempt' => 3]), $singleSetQuiz->course, $singleSetModule))
        ->toThrow(ValidationException::class, 'whole number from 1 to 2');
});

test('blank draw setting preserves the existing set rotation', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 2, quizAttributes: ['questions_per_attempt' => null]);
    $service = app(QuizAttemptService::class);
    $first = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
    $second = $service->presentationFor($quiz->fresh(), User::factory()->create()->id, remember: false);

    expect($first['question_ids'])->toHaveCount(2)
        ->and($first['set_count'])->toBe(2)
        ->and($first['set_id'])->not->toBe($second['set_id']);
});

test('attempt start snapshots the shown questions for pooled and non-pooled quizzes', function () {
    foreach ([null, 2] as $drawCount) {
        [, , $quiz, $student] = qsFixture(sets: 1, perSet: 4, quizAttributes: ['questions_per_attempt' => $drawCount]);
        $quiz->update(['shuffle_questions' => true, 'shuffle_choices' => true]);

        $presentation = app(QuizAttemptService::class)->presentationFor($quiz->fresh(), $student->id);
        $snapshot = $presentation['question_snapshot'];
        $pendingAttempt = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $student->id)->latest('id')->firstOrFail();

        expect($snapshot)->toHaveKeys(['v', 'questions'])
            ->and($snapshot['v'])->toBe(1)
            ->and(collect($snapshot['questions'])->pluck('id')->all())->toBe($presentation['question_ids'])
            ->and($snapshot['questions'][0])->toHaveKeys(['id', 'text', 'type', 'points', 'options', 'correct_answer'])
            ->and($snapshot['questions'][0]['options'])->toHaveCount(4)
            ->and($pendingAttempt->submitted_at)->toBeNull()
            ->and($pendingAttempt->question_snapshot)->toBe($snapshot);
    }
});

test('active attempt grading and review use its frozen question text choices answer and points', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 1);
    $question = $quiz->questions->first();
    $question->update(['points' => 3]);
    $service = app(QuizAttemptService::class);
    $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk();
    $presentation = session('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $frozen = $presentation['question_snapshot']['questions'][0];
    $correctLetter = chr(65 + array_search($frozen['correct_answer'], $frozen['options'], true));

    $question->update([
        'question' => 'Edited after start',
        'options' => ['New correct', 'New wrong'],
        'correct_answer' => 'New correct',
        'points' => 9,
    ]);
    session()->forget('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $start = $this->postJson(route('quiz.start', $quiz->id))->assertOk();
    $resumed = session('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $result = $service->submit($student, $quiz->fresh(), ['answers' => [(string) $question->id => $correctLetter]], null, $resumed);
    $attempt = QuizAttempt::query()->latest('id')->firstOrFail();
    $review = $service->reviewQuestionsForAttempt($attempt);

    expect($resumed['question_snapshot'])->toBe($frozen === [] ? [] : $presentation['question_snapshot'])
        ->and($result['score'])->toBe(100)
        ->and($start->json('questions.0.question'))->toBe($frozen['text'])
        ->and($start->getContent())->not->toContain('question_snapshot')
        ->and($start->getContent())->not->toContain('correct_answer')
        ->and($attempt->question_snapshot['questions'][0]['text'])->toBe($frozen['text'])
        ->and($review[0]['question'])->toBe($frozen['text'])
        ->and(collect($review[0]['options'])->pluck('text')->all())->toBe($frozen['options'])
        ->and(collect($review[0]['options'])->firstWhere('is_correct', true)['text'])->toBe($frozen['correct_answer']);
});

test('released course review renders the attempt snapshot without serializing it', function () {
    [$course, , $quiz, $student] = qsFixture(sets: 1, perSet: 1);
    $question = $quiz->questions->first();
    $service = app(QuizAttemptService::class);
    $presentation = $service->presentationFor($quiz->fresh(), $student->id, remember: false);
    $result = $service->submit($student, $quiz->fresh(), ['answers' => [(string) $question->id => 'B']], null, $presentation);
    $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    $enrollment->update(['progress_state' => ['completed_lessons' => [], 'module_scores' => [0 => $result['score']]]]);

    $page = $this->actingAs($student)->get(route('courses.learn', $course->id))->assertOk();
    preg_match('/var QUIZ_DATA = (.*?);\n/s', $page->getContent(), $match);
    $data = json_decode($match[1], true);

    expect($data[0]['questions'][0]['text'])->toBe('Set 0 question 0')
        ->and(collect($data[0]['questions'][0]['options'])->firstWhere('is_correct', true)['text'])->toBe('Right 0-0')
        ->and($page->getContent())->not->toContain('question_snapshot')
        ->and($page->getContent())->not->toContain('"correct_answer"');
});

test('deleting an unanswered live question after start does not break submission', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 1);
    $service = app(QuizAttemptService::class);
    $presentation = $service->presentationFor($quiz->fresh(), $student->id);
    $questionId = $presentation['question_ids'][0];

    QuizQuestion::query()->whereKey($questionId)->delete();
    $served = $service->presentedQuestions($quiz->fresh(), $presentation);
    $result = $service->submit($student, $quiz->fresh(), ['answers' => [(string) $questionId => 'A']], null, $presentation);

    expect($served[0]['question']->question)->toBe('Set 0 question 0')
        ->and($result['score'])->toBe(100)
        ->and(QuizAttempt::query()->latest('id')->first()->question_snapshot['questions'][0]['id'])->toBe($questionId);
});

test('snapshot scoring applies weighted points and treats null and zero points as one', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 3);
    $questions = $quiz->questions()->orderBy('position')->get();
    $questions[0]->update(['points' => 3]);
    $questions[1]->update(['points' => 0]);
    $service = app(QuizAttemptService::class);
    $presentation = $service->presentationFor($quiz->fresh(), $student->id);
    $presentation['question_snapshot']['questions'][2]['points'] = null;
    $answers = [];
    foreach ($presentation['question_snapshot']['questions'] as $question) {
        $answers[(string) $question['id']] = chr(65 + array_search($question['correct_answer'], $question['options'], true));
    }
    $answers[(string) $questions[1]->id] = 'B';

    $result = $service->submit($student, $quiz->fresh(), ['answers' => $answers], null, $presentation);

    $attempt = QuizAttempt::query()->latest('id')->first();

    expect($result['earned_points'])->toBe(4)
        ->and($result['possible_points'])->toBe(5)
        ->and($result['score'])->toBe(80)
        ->and($attempt->question_snapshot['questions'][1]['points'])->toBe(0)
        ->and($attempt->question_snapshot['questions'][2]['points'])->toBeNull();
});

test('learner submitted IDs are checked against the frozen snapshot IDs', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk();
    $presentation = session('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $foreignId = $quiz->questions()->whereNotIn('id', $presentation['question_ids'])->value('id');

    expect($foreignId)->not->toBeNull()
        ->and(app(QuizAttemptService::class)->answersMatchPresentation([(string) $foreignId => 'A'], $presentation))->toBeFalse()
        ->and(app(QuizAttemptService::class)->answersMatchPresentation([(string) $presentation['question_ids'][0] => 'A'], $presentation))->toBeTrue();
});

test('legacy attempts without snapshots keep live grading and never expose snapshot data', function () {
    [, , $quiz, $student] = qsFixture(sets: 1, perSet: 1);
    $question = $quiz->questions->first();
    $legacyPresentation = app(QuizAttemptService::class)->presentationFor($quiz, $student->id, remember: false);
    unset($legacyPresentation['question_snapshot']);
    $grader = app(QuizGradingService::class);
    $grade = $grader->grade($quiz, ['answers' => [(string) $question->id => 'A']], $quiz->questions);
    $attempt = QuizAttempt::create([
        'user_id' => $student->id,
        'quiz_id' => $quiz->id,
        'presentation' => ['question_ids' => [$question->id], 'choice_orders' => []],
        'question_snapshot' => null,
        'score' => $grade['score'],
        'passed' => $grade['passed'],
        'submitted_at' => now(),
    ]);
    $question->update(['question' => 'Live edited legacy question']);

    $page = $this->actingAs($student)->get(route('courses.learn', $quiz->course_id))->assertOk();
    $start = $this->postJson(route('quiz.start', $quiz->id))->assertOk();

    expect($attempt->toArray())->not->toHaveKey('question_snapshot')
        ->and($page->getContent())->not->toContain('question_snapshot')
        ->and($start->getContent())->not->toContain('question_snapshot')
        ->and($start->getContent())->not->toContain('correct_answer')
        ->and($legacyPresentation['question_ids'])->toBe([$question->id]);
});

// ── Grading and the student flows ────────────────────────────────────────

test('grading with shuffled choices uses the original letters, while the page shows sequential labels', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 4, quizAttributes: ['shuffle_questions' => true, 'shuffle_choices' => true]);
    $service = app(QuizAttemptService::class);

    $html = $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk()->getContent();
    $presentation = session('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $served = $service->presentedQuestions($quiz->fresh(), $presentation);

    // Only the assigned set is on the page, in served order.
    $otherSet = $quiz->questions->where('quiz_set_id', '!=', $presentation['set_id']);
    foreach ($otherSet as $question) {
        expect($html)->not->toContain($question->question);
    }
    $positions = array_map(fn ($item) => strpos($html, $item['question']->question), $served);
    expect($positions)->toBe(collect($positions)->sort()->values()->all());

    // The correct option sits at a shuffled position but still submits "A".
    expect(collect($served)->contains(fn ($item) => $item['options'][0]['value'] !== 'A'))->toBeTrue()
        ->and(collect($served)->every(fn ($item) => collect($item['options'])->pluck('label')->all() === ['A', 'B', 'C', 'D']))->toBeTrue();

    $this->post(route('quiz.submit', $quiz->id), ['answers' => qsCorrectAnswers($quiz, $presentation['question_ids'])])->assertRedirect();

    $attempt = QuizAttempt::where('user_id', $student->id)->latest('id')->firstOrFail();
    expect($attempt->score)->toBe(100)
        ->and($attempt->passed)->toBeTrue()
        ->and($attempt->quiz_set_id)->toBe($presentation['set_id'])
        ->and($attempt->shuffle_seed)->toBe($presentation['question_seed'])
        ->and($attempt->presentation['question_ids'])->toBe($presentation['question_ids'])
        ->and($attempt->answers()->count())->toBe(4);
});

test('the grader only counts the served set, so the score is out of the per-set count', function () {
    [, , $quiz] = qsFixture(sets: 2, perSet: 3);
    $setA = $quiz->questionsForSet($quiz->sets()->first());
    $grade = app(QuizGradingService::class)->grade($quiz, ['answers' => qsCorrectAnswers($quiz, $setA->pluck('id')->all())], $setA);

    expect($grade)->toMatchArray(['correct' => 3, 'total' => 3, 'score' => 100, 'passed' => true])
        ->and($quiz->questionsPerSetCount())->toBe(3)
        ->and($quiz->setCount())->toBe(2);
});

test('answers for questions outside the served set are rejected', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk();
    $presentation = session('quiz_presentation.'.$student->id.'.'.$quiz->id);
    $foreign = $quiz->questions->firstWhere('quiz_set_id', '!=', $presentation['set_id']);

    $this->postJson(route('quiz.submit', $quiz->id), ['answers' => [(string) $foreign->id => 'A']])->assertStatus(422);
    expect(QuizAttempt::where('user_id', $student->id)->whereNotNull('submitted_at')->count())->toBe(0)
        ->and(QuizAttempt::where('user_id', $student->id)->whereNull('submitted_at')->first()->question_snapshot)->not->toBeNull();
});

test('the course player and quiz start payloads carry the served set without correct answers', function () {
    [$course, , $quiz, $student] = qsFixture(sets: 2, perSet: 2, quizAttributes: ['shuffle_choices' => true]);

    $start = $this->actingAs($student)->postJson(route('quiz.start', $quiz->id))->assertOk()->json();
    $presentation = session('quiz_presentation.'.$student->id.'.'.$quiz->id);

    expect(collect($start['questions'])->pluck('id')->all())->toBe($presentation['question_ids'])
        ->and(json_encode($start))->not->toContain('correct')
        ->and(json_encode($start))->not->toContain('Right 0-0 is correct');

    $html = $this->get(route('courses.learn', $course->id))->assertOk()->getContent();
    preg_match('/var QUIZ_DATA = (.*?);\n/s', $html, $match);
    $quizData = json_decode($match[1], true);
    expect(collect($quizData[0]['questions'])->pluck('id')->all())->toBe($presentation['question_ids'])
        ->and($match[1])->not->toContain('correct_answer')
        ->and(array_keys($quizData[0]['questions'][0]))->toBe(['id', 'text', 'type', 'points', 'options']);

    // Submitting through the course player grades against the same set.
    $this->postJson(route('courses.progress', $course->id), [
        'quiz_results' => [0 => ['answers' => qsCorrectAnswers($quiz, $presentation['question_ids'])]],
    ])->assertOk()->assertJsonPath('quiz_submissions.0.passed', true);
});

test('student quiz responses omit correct answers before submission', function () {
    [$course, $module, $quiz, $student] = qsFixture(sets: 1, perSet: 1);
    $question = $quiz->questions->first();

    $page = $this->actingAs($student)->get(route('quiz.show', $quiz->id))->assertOk();
    expect($page->getContent())->not->toContain('correct_answer');

    $start = $this->postJson(route('quiz.start', $quiz->id))->assertOk();
    expect(json_encode($start->json()))->not->toContain('correct_answer');

    $progress = $this->postJson(route('courses.progress', $course->id), [])->assertOk();
    expect(json_encode($progress->json()))->not->toContain('correct_answer');

    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson with inline quiz',
        'content' => 'Read this lesson.',
        'module_order' => 1,
        'duration' => 1,
    ]);
    $inlineQuiz = Quiz::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'lesson_id' => $lesson->id,
        'title' => 'Inline quiz',
        'passing_score' => 70,
        'attempts' => 'Unlimited',
        'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $inlineQuiz->id,
        'question' => 'Inline question?',
        'type' => 'Multiple Choice',
        'options' => ['Correct option', 'Other option'],
        'correct_answer' => 'Correct option',
        'points' => 1,
    ]);

    $this->postJson(route('courses.lessons.start', [$course->id, $lesson->id]))->assertOk();
    $this->travel(6)->seconds();
    $this->postJson(route('courses.lessons.complete', [$course->id, $lesson->id]))->assertOk();

    $inlineResponse = $this->getJson(route('quiz.show', $inlineQuiz->id))->assertOk();
    expect($inlineResponse->json('html'))
        ->toContain('Inline question?')
        ->not->toContain('correct_answer');

    expect($question->toArray())->not->toHaveKey('correct_answer');
});

test('existing cooldown, attempt limits and grants work unchanged with sets', function () {
    [, , $quiz, $student] = qsFixture(sets: 2, perSet: 1, quizAttributes: ['attempts' => '1 Attempt']);
    $service = app(QuizAttemptService::class);

    $first = $service->submit($student, $quiz, ['answers' => []]);
    expect($first['blocked'])->toBeFalse()
        ->and($service->retakeStatus($quiz->fresh(), $student->id)['exhausted'])->toBeTrue()
        ->and($service->submit($student, $quiz->fresh(), ['answers' => []])['blocked'])->toBeTrue();

    $quiz->update(['attempts' => '2 Attempts']);
    $status = $service->retakeStatus($quiz->fresh(), $student->id);
    expect($status['exhausted'])->toBeFalse()
        ->and($status['unlock'])->not->toBeNull();
});

// ── Admin and faculty screens ────────────────────────────────────────────

test('the admin course preview lists each set separately', function () {
    [$course] = qsFixture(sets: 2, perSet: 1);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.courses.show', $course->id))
        ->assertOk()
        ->assertSeeInOrder(['Set A', 'Set 0 question 0', 'Set B', 'Set 1 question 0'])
        ->assertSee('(correct)')
        ->assertSee('2 sets');
});

test('the attempt review shows the set name and the questions in the order the student saw them', function () {
    [$course, , $quiz, $student] = qsFixture(sets: 1, perSet: 3, quizAttributes: ['shuffle_questions' => true]);
    $presentation = app(QuizAttemptService::class)->presentationFor($quiz, $student->id, remember: false);
    app(QuizAttemptService::class)->submit($student, $quiz, ['answers' => qsCorrectAnswers($quiz, $presentation['question_ids'])], null, $presentation);
    $enrollment = Enrollment::where('user_id', $student->id)->firstOrFail();

    $texts = array_map(fn ($id) => QuizQuestion::find($id)->question, $presentation['question_ids']);
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.enrollments.show', $enrollment->id))
        ->assertOk()
        ->assertSee('Set A')
        ->assertSeeInOrder($texts);
});

test('the faculty builder loads every set with ids and answered flags', function () {
    [$course, $module, $quiz, $student] = qsFixture(sets: 2, perSet: 2);
    $question = $quiz->questions->first();
    QuizAttempt::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'quiz_set_id' => $question->quiz_set_id, 'score' => 0, 'passed' => false, 'submitted_at' => now()])
        ->answers()->create(['question_id' => $question->id, 'answer' => 'B', 'is_correct' => false]);

    $response = $this->actingAs($course->creator)->get(route('faculty.quiz.create', [$course->id, $module->id]))->assertOk();
    $quizData = $response->viewData('quiz');

    expect($quizData->items)->toBe(2)
        ->and(collect($quizData->sets)->pluck('name')->all())->toBe(['Set A', 'Set B'])
        ->and($quizData->sets[0]['used'])->toBeTrue()
        ->and($quizData->sets[0]['questions'][0]['id'])->toBe($question->id)
        ->and($quizData->sets[0]['questions'][0]['correct'])->toBe(0)
        ->and($quizData->sets[0]['questions'][0]['answered'])->toBeTrue()
        ->and($quizData->has_attempts)->toBeTrue();
    $response->assertSee('var QUIZ_BUILDER_SETS', false)
        ->assertSee('name="shuffle_questions"', false)
        ->assertSee('name="shuffle_choices"', false);
});

test('the faculty builder posts nested sets through the real route', function () {
    [$course, $module, $quiz] = qsFixture(sets: 1, perSet: 1);
    $set = $quiz->sets()->with('questions')->first();

    $this->actingAs($course->creator)->post(route('faculty.quiz.store', [$course->id, $module->id]), [
        'quiz_title' => 'Two sets',
        'passing_score' => 70,
        'shuffle_choices' => '1',
        'sets' => [
            ['id' => $set->id, 'name' => 'Set A', 'questions' => [qsMcQuestion('A1', ['x', 'y'], 1, $set->questions[0]->id) + ['lock_choice_order' => '1']]],
            ['id' => '', 'name' => 'Set B', 'questions' => [['text' => 'B1', 'type' => 'True or False', 'tf_correct' => '1', 'points' => 2]]],
        ],
    ])->assertRedirect();

    $fresh = $quiz->fresh();
    expect($fresh->title)->toBe('Two sets')
        ->and($fresh->shuffle_choices)->toBeTrue()
        ->and($fresh->sets()->pluck('name')->all())->toBe(['Set A', 'Set B'])
        ->and($fresh->questionsForSet($fresh->sets()->first())->first()->id)->toBe($set->questions[0]->id)
        ->and($fresh->questionsForSet($fresh->sets()->first())->first()->lock_choice_order)->toBeTrue()
        ->and($fresh->questionsForSet($fresh->sets()->skip(1)->first())->first()->correct_answer)->toBe('False');

    $this->post(route('faculty.quiz.store', [$course->id, $module->id]), [
        'quiz_title' => 'Uneven',
        'sets' => [
            ['name' => 'Set A', 'questions' => [qsMcQuestion('A1'), qsMcQuestion('A2')]],
            ['name' => 'Set B', 'questions' => [qsMcQuestion('B1')]],
        ],
    ])->assertSessionHasErrors('sets');
});
