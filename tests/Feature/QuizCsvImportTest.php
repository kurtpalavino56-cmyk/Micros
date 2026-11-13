<?php

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizSet;
use App\Models\User;
use App\Services\QuizCsvImportService;
use App\Services\QuizManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(RefreshDatabase::class);

/** @return array{Course,CourseModule,Quiz,QuizSet,User} */
function csvQuizFixture(): array
{
    $faculty = User::factory()->faculty()->create();
    $course = Course::create([
        'title' => 'CSV Course',
        'slug' => 'csv-course-'.uniqid(),
        'description' => 'CSV import test course',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
    ]);
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'CSV Module', 'order' => 1]);
    $quiz = Quiz::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'is_summative' => true,
        'title' => 'Imported Quiz',
        'passing_score' => 75,
        'attempts' => 'Unlimited',
        'is_active' => true,
    ]);
    $set = QuizSet::create(['quiz_id' => $quiz->id, 'name' => 'Set A', 'position' => 0]);

    return [$course, $module, $quiz, $set, $faculty];
}

/** @param array<string, string> $values */
function csvImportRow(array $values = []): array
{
    return array_replace([
        'type' => 'multiple_choice',
        'question' => 'Question?',
        'choice_a' => 'First',
        'choice_b' => 'Second',
        'choice_c' => '',
        'choice_d' => '',
        'correct_answer' => 'A',
        'points' => '1',
    ], $values);
}

/** @param list<array<string, string>> $rows */
function csvImportContents(array $rows, string $delimiter = ',', bool $bom = false): string
{
    $headers = ['type', 'question', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_answer', 'points'];
    $lines = [implode($delimiter, $headers)];
    foreach ($rows as $row) {
        $values = array_values(array_intersect_key($row, array_flip($headers)));
        $lines[] = implode($delimiter, array_map(fn (string $value): string => '"'.str_replace('"', '""', $value).'"', $values));
    }

    return ($bom ? "\xEF\xBB\xBF" : '').implode("\r\n", $lines)."\r\n";
}

function csvImportPost(TestCase $testCase, Course $course, Quiz $quiz, User $faculty, string $contents): TestResponse
{
    Storage::fake('local');

    return $testCase->actingAs($faculty)->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->createWithContent('questions.csv', $contents),
    ]);
}

function csvImportState(User $faculty, Quiz $quiz): array
{
    return session('quiz_csv_import.'.$faculty->id.'.'.$quiz->id);
}

test('CSV imports multiple choice by letter and exact text, true false and identification in the existing question shape', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $rows = [
        csvImportRow(['question' => 'Letter key', 'choice_a' => 'one', 'choice_b' => 'two', 'choice_c' => 'three', 'correct_answer' => 'C', 'points' => '']),
        csvImportRow(['question' => 'Text key', 'choice_a' => 'red', 'choice_b' => 'blue', 'correct_answer' => 'blue', 'points' => '0']),
        csvImportRow(['type' => 'TRUE_FALSE', 'question' => 'The sky is blue', 'choice_a' => '', 'choice_b' => '', 'correct_answer' => 'tRuE', 'points' => '-2']),
        csvImportRow(['type' => 'tRuE Or FaLsE', 'question' => 'Water is wet', 'choice_a' => '', 'choice_b' => '', 'correct_answer' => 'FALSE', 'points' => '']),
        csvImportRow(['type' => 'Identification', 'question' => 'Negative answer', 'choice_a' => '', 'choice_b' => '', 'correct_answer' => 'minus 42', 'points' => '2']),
    ];
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents($rows))
        ->assertRedirect(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]));
    expect(Storage::disk('local')->allFiles('quiz-imports/'.$faculty->id.'/'.$quiz->id))->toHaveCount(2);

    $this->actingAs($faculty)->get(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]))
        ->assertOk()
        ->assertSee('<strong>5</strong>', false)
        ->assertSee('valid rows')
        ->assertSee('errors')
        ->assertSee('duplicates')
        ->assertSee('2 row(s) will be normalized');
    $state = csvImportState($faculty, $quiz);
    $this->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])
        ->assertRedirect(route('faculty.quiz.create', [$course->id, $quiz->module_id]).'#questions')
        ->assertSessionHas('success');
    expect(session('success'))->toContain('quiz pool now has');

    $questions = $quiz->questions()->orderBy('position')->get()->keyBy('question');
    expect($questions)->toHaveCount(5)
        ->and($questions['Letter key']->type)->toBe('Multiple Choice')
        ->and($questions['Letter key']->options)->toBe(['one', 'two', 'three'])
        ->and($questions['Letter key']->correct_answer)->toBe('three')
        ->and($questions['Letter key']->points)->toBe(1)
        ->and($questions['Text key']->correct_answer)->toBe('blue')
        ->and($questions['The sky is blue']->correct_answer)->toBe('True')
        ->and($questions['Water is wet']->correct_answer)->toBe('False')
        ->and($questions['Negative answer']->correct_answer)->toBe('minus 42')
        ->and(QuizQuestion::where('quiz_id', $quiz->id)->count())->toBe(5)
        ->and(AuditLog::where('actor_id', $faculty->id)->exists())->toBeTrue();
});

test('CSV accepts a UTF-8 BOM and semicolon delimiter', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $content = csvImportContents([csvImportRow(['question' => 'Semicolon row'])], ';', true);
    csvImportPost($this, $course, $quiz, $faculty, $content)->assertRedirect(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]));

    $preview = app(QuizCsvImportService::class)->preview($quiz->fresh(), $content);
    expect($preview['valid'])->toHaveCount(1)
        ->and($preview['valid'][0]['question']['question'])->toBe('Semicolon row');
});

test('CSV detects a semicolon delimiter from the header row when note lines come first', function () {
    [, , $quiz] = csvQuizFixture();
    $content = "# A note line\r\n# Another note\r\n".csvImportContents([csvImportRow(['question' => 'Resaved row'])], ';');

    $preview = app(QuizCsvImportService::class)->preview($quiz, $content);

    expect($preview['errors'])->toBe([])
        ->and($preview['valid'])->toHaveCount(1)
        ->and($preview['valid'][0]['question']['question'])->toBe('Resaved row');
});

test('the saved quiz builder links to CSV import and the template has the documented columns', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $this->actingAs($faculty)->get(route('faculty.quiz.create', [$course->id, $quiz->module_id]))
        ->assertOk()->assertSee('Upload CSV')->assertSee('name="continue_to_import"', false)
        ->assertSee('Save Quiz')->assertSee('min="1"', false)->assertSee('0 or blank counts as 1');
    $this->actingAs($faculty)->get(route('faculty.quiz-import.form', [$course->id, $quiz->id]))
        ->assertOk()
        ->assertSee('<header class="auth-topbar faculty-auth-topbar', false)
        ->assertSee('<aside class="faculty-sidebar" id="faculty-sidebar">', false)
        ->assertSee('class="main quiz-csv-page"', false)
        ->assertSee('Identification questions accept one correct answer')
        ->assertSee('Rephrase any answer starting with')
        ->assertSee('2 MB and 500 data rows');
    $template = $this->get(route('faculty.quiz-import.template', [$course->id, $quiz->id]))
        ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($template->streamedContent())->toContain('type,question,choice_a,choice_b,choice_c,choice_d,correct_answer,points')
        ->and($template->streamedContent())->toContain('Rephrase answers starting with -')
        ->and(app(QuizCsvImportService::class)->preview($quiz, $template->streamedContent())['valid'])->toHaveCount(3);
});

test('saving an empty bank can go directly to CSV import with all questions shown', function () {
    [$course, $module, $quiz, , $faculty] = csvQuizFixture();

    $this->actingAs($faculty)->post(route('faculty.quiz.store', [$course->id, $module->id]), [
        'quiz_title' => $quiz->title,
        'continue_to_import' => '1',
    ])->assertRedirect(route('faculty.quiz-import.form', [$course->id, $quiz->id]));

    expect($quiz->fresh()->questions()->count())->toBe(0)
        ->and($quiz->fresh()->questions_per_attempt)->toBeNull()
        ->and($quiz->fresh()->setCount())->toBe(1);
});

test('CSV validation errors retain their source row number', function (array $row, string $reason) {
    [, , $quiz] = csvQuizFixture();
    $preview = app(QuizCsvImportService::class)->preview($quiz, csvImportContents([$row]));

    expect($preview['errors'])->toHaveCount(1)
        ->and($preview['errors'][0]['row'])->toBe(2)
        ->and($preview['errors'][0]['reason'])->toContain($reason);
})->with([
    'unknown type' => [csvImportRow(['type' => 'essay']), 'Type must be'],
    'question required' => [csvImportRow(['question' => '']), 'Question is required'],
    'answer required' => [csvImportRow(['correct_answer' => '']), 'Correct answer is required'],
    'two choices required' => [csvImportRow(['choice_b' => '', 'correct_answer' => 'A']), 'at least two'],
    'blank choice gap' => [csvImportRow(['choice_b' => '', 'choice_c' => 'third']), 'blank between'],
    'correct key must match' => [csvImportRow(['correct_answer' => 'missing']), 'must match a choice'],
    'letter must point to a filled choice' => [csvImportRow(['choice_c' => '', 'correct_answer' => 'C']), 'must refer to a filled choice'],
    'true false requires a boolean answer' => [csvImportRow(['type' => 'true_false', 'correct_answer' => 'sometimes']), 'must be True or False'],
    'points must be integral' => [csvImportRow(['points' => '1.5']), 'whole number'],
]);

test('CSV detects normalized duplicates against the pool and within the file and skips them by default', function () {
    [$course, , $quiz, $set, $faculty] = csvQuizFixture();
    QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $set->id, 'question' => 'Existing   Question', 'type' => 'Identification', 'correct_answer' => 'yes', 'options' => [], 'points' => 1]);
    $rows = [
        csvImportRow(['question' => ' existing question ', 'correct_answer' => 'A']),
        csvImportRow(['question' => 'New Question']),
        csvImportRow(['question' => " new\tquestion "]),
    ];
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents($rows))->assertRedirect();
    $preview = app(QuizCsvImportService::class)->preview($quiz->fresh(), csvImportContents($rows));

    expect($preview['valid'])->toHaveCount(1)
        ->and($preview['duplicates'])->toHaveCount(2)
        ->and($preview['duplicates'][0]['reason'])->toContain('already in this quiz')
        ->and($preview['duplicates'][1]['reason'])->toContain('earlier row');

    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])->assertRedirect();
    expect($quiz->questions()->count())->toBe(2);
});

test('faculty can explicitly include duplicates', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $rows = [csvImportRow(['question' => 'Repeat']), csvImportRow(['question' => ' repeat  '])];
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents($rows))->assertRedirect();
    $state = csvImportState($faculty, $quiz);

    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), [
        'token' => $state['token'],
        'include_duplicates' => '1',
    ])->assertRedirect();
    expect($quiz->questions()->count())->toBe(2);
});

test('replace clears the existing pool only when there are no attempts, while append remains the default', function () {
    [$course, , $quiz, $set, $faculty] = csvQuizFixture();
    $old = QuizQuestion::create(['quiz_id' => $quiz->id, 'quiz_set_id' => $set->id, 'question' => 'Old', 'type' => 'Identification', 'correct_answer' => 'old', 'options' => [], 'points' => 1]);
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => 'Appended'])]))->assertRedirect();
    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])->assertRedirect();
    expect($quiz->questions()->count())->toBe(2);

    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => 'Replacement'])]))->assertRedirect();
    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token'], 'replace' => '1'])->assertRedirect();
    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions()->first()->question)->toBe('Replacement')
        ->and(QuizQuestion::find($old->id))->toBeNull();

    QuizAttempt::create(['user_id' => $faculty->id, 'quiz_id' => $quiz->id, 'score' => 0, 'passed' => false, 'submitted_at' => now()]);
    $this->get(route('faculty.quiz-import.form', [$course->id, $quiz->id]))->assertOk()->assertSee('Replace mode is unavailable');
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => 'Blocked replacement'])]))->assertRedirect();
    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->from(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]))
        ->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token'], 'replace' => '1'])
        ->assertSessionHasErrors('replace');
    expect($quiz->questions()->count())->toBe(1);
});

test('CSV import works with legacy multiple-set quizzes and keeps past attempts', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $secondSet = QuizSet::create(['quiz_id' => $quiz->id, 'name' => 'Set B', 'position' => 1]);
    $oldQuestion = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_set_id' => $secondSet->id,
        'question' => 'Old question',
        'type' => 'Identification',
        'correct_answer' => 'answer',
        'options' => [],
        'points' => 1,
    ]);
    $attempt = QuizAttempt::create([
        'user_id' => $faculty->id,
        'quiz_id' => $quiz->id,
        'quiz_set_id' => $secondSet->id,
        'score' => 0,
        'passed' => false,
        'submitted_at' => now(),
    ]);
    $attempt->answers()->create(['question_id' => $oldQuestion->id, 'answer' => 'wrong', 'is_correct' => false]);

    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => 'Imported question'])]))
        ->assertRedirect(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]));
    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])
        ->assertRedirect(route('faculty.quiz.create', [$course->id, $quiz->module_id]).'#questions')
        ->assertSessionHas('success');

    expect($quiz->fresh()->questions()->count())->toBe(2)
        ->and($quiz->fresh()->sets()->count())->toBe(2)
        ->and($attempt->fresh()->quiz_set_id)->toBe($secondSet->id)
        ->and($attempt->answers()->count())->toBe(1)
        ->and(QuizQuestion::where('quiz_id', $quiz->id)->where('question', 'Imported question')->exists())->toBeTrue();
});

test('CSV formula-like cells are rejected but negative identification answers and points normalize safely', function () {
    [, , $quiz] = csvQuizFixture();
    $rows = [
        csvImportRow(['question' => '=1+1']),
        csvImportRow(['question' => '+SUM(A1:A2)']),
        csvImportRow(['question' => '@HYPERLINK(1)']),
        csvImportRow(['question' => '-cmd']),
        csvImportRow(['type' => 'identification', 'question' => 'Negative', 'choice_a' => '', 'choice_b' => '', 'correct_answer' => '-17', 'points' => '-3']),
        csvImportRow(['type' => 'identification', 'question' => 'Safe answer', 'choice_a' => '', 'choice_b' => '', 'correct_answer' => 'safe answer', 'points' => '-3']),
    ];
    $preview = app(QuizCsvImportService::class)->preview($quiz, csvImportContents($rows));

    expect($preview['errors'])->toHaveCount(5)
        ->and(array_column($preview['errors'], 'row'))->toBe([2, 3, 4, 5, 6])
        ->and($preview['errors'][0]['reason'])->toContain('Formula-like')
        ->and($preview['errors'][4]['reason'])->toContain('must be rephrased')
        ->and($preview['valid'])->toHaveCount(1)
        ->and($preview['valid'][0]['question']['correct_answer'])->toBe('safe answer')
        ->and($preview['valid'][0]['question']['points'])->toBe(1)
        ->and($preview['points_normalized'])->toBe(1);
});

test('empty files, more than 500 rows, oversized uploads and non-CSV files are rejected', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $service = app(QuizCsvImportService::class);
    expect(fn () => $service->preview($quiz, ''))->toThrow(ValidationException::class, 'empty')
        ->and(fn () => $service->preview($quiz, csvImportContents([])))->toThrow(ValidationException::class, 'no data rows');

    $manyRows = array_fill(0, 501, csvImportRow());
    expect(fn () => $service->preview($quiz, csvImportContents($manyRows)))->toThrow(ValidationException::class, '500 data rows');

    Storage::fake('local');
    $this->actingAs($faculty)->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->create('questions.txt', 1, 'text/plain'),
    ])->assertSessionHasErrors('csv_file');
    $this->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->create('questions.csv', 1, 'application/octet-stream'),
    ])->assertSessionHasErrors('csv_file');
    $this->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->createWithContent('too-large.csv', str_repeat('x', 2 * 1024 * 1024 + 1)),
    ])->assertSessionHasErrors('csv_file');
});

test('failed inserts roll back every imported question', function () {
    [, , $quiz] = csvQuizFixture();
    $preview = app(QuizCsvImportService::class)->preview($quiz, csvImportContents([
        csvImportRow(['question' => 'First']),
        csvImportRow(['question' => 'Second']),
    ]));
    $rows = array_column($preview['valid'], 'question');
    $event = 'eloquent.creating: '.QuizQuestion::class;
    $created = 0;
    Event::listen($event, function () use (&$created): void {
        $created++;
        if ($created === 2) {
            throw new RuntimeException('Simulated write failure.');
        }
    });

    try {
        expect(fn () => app(QuizManagementService::class)->storeImportedQuestions($quiz, $rows, false))
            ->toThrow(RuntimeException::class, 'Simulated write failure');
    } finally {
        Event::forget($event);
    }

    expect($quiz->questions()->count())->toBe(0);
});

test('import routes enforce faculty ownership, role access, preview tokens and expiry', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $otherFaculty = User::factory()->faculty()->create();
    $otherCourse = Course::create([
        'title' => 'Other Course', 'slug' => 'other-'.uniqid(), 'description' => 'Other', 'category' => 'Development',
        'level' => 'Beginner', 'is_published' => true, 'created_by' => $otherFaculty->id,
    ]);
    $learner = User::factory()->create();

    $this->actingAs($otherFaculty)->get(route('faculty.quiz-import.form', [$course->id, $quiz->id]))->assertNotFound();
    $this->get(route('faculty.quiz-import.form', [$otherCourse->id, $quiz->id]))->assertNotFound();
    $this->get(route('faculty.quiz-import.template', [$otherCourse->id, $quiz->id]))->assertNotFound();
    $this->post(route('faculty.quiz-import.preview', [$otherCourse->id, $quiz->id]))->assertNotFound();
    $this->post(route('faculty.quiz-import.confirm', [$otherCourse->id, $quiz->id]))->assertNotFound();
    $this->actingAs($learner)->get(route('faculty.quiz-import.form', [$course->id, $quiz->id]))->assertRedirect(route('dashboard'));
    $this->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]))->assertRedirect(route('dashboard'));
    $this->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]))->assertRedirect(route('dashboard'));

    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => '<script>alert(1)</script>'])]))->assertRedirect();
    $state = csvImportState($faculty, $quiz);
    $preview = $this->actingAs($faculty)->get(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]))
        ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);

    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => '00000000-0000-0000-0000-000000000000'])
        ->assertRedirect()->assertSessionHasErrors('preview');
    expect($quiz->questions()->count())->toBe(0);

    $this->withSession(['quiz_csv_import.'.$faculty->id.'.'.$quiz->id => array_merge($state, ['expires_at' => now()->subMinute()->timestamp])])
        ->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])
        ->assertRedirect()->assertSessionHasErrors('preview');
    expect($quiz->questions()->count())->toBe(0)
        ->and($otherCourse->created_by)->toBe($otherFaculty->id);
});

test('confirm cannot replay after commit and imported learner payloads keep the answer key private', function () {
    [$course, , $quiz, , $faculty] = csvQuizFixture();
    $quiz->update(['questions_per_attempt' => 1]);
    $learner = User::factory()->create();
    Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    csvImportPost($this, $course, $quiz, $faculty, csvImportContents([csvImportRow(['question' => '<script>alert(2)</script>'])]))->assertRedirect();
    $state = csvImportState($faculty, $quiz);
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), [
        'token' => $state['token'],
        'questions' => [['question' => 'Tampered client row', 'correct_answer' => 'Injected answer']],
    ])->assertRedirect();

    $this->actingAs($faculty)->get(route('faculty.quiz.create', [$course->id, $quiz->module_id]))
        ->assertOk()->assertDontSee('<script>alert(2)</script>', false);
    $this->actingAs($learner)->get(route('quiz.show', $quiz->id))->assertOk()->assertDontSee('correct_answer')->assertDontSee('<script>alert(2)</script>', false);
    $start = $this->postJson(route('quiz.start', $quiz->id))->assertOk();
    expect(json_encode($start->json()))->not->toContain('correct_answer');
    $this->actingAs($faculty)->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $state['token']])->assertRedirect()->assertSessionHasErrors('preview');
    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions()->first()->question)->toBe('<script>alert(2)</script>');
});
