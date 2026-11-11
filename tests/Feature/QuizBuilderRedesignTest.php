<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSet;
use App\Models\User;
use App\Services\QuizAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/** @return array{Course,User,string,string,Closure(Quiz):string} */
function quizBuilderRedesignContext(string $type): array
{
    $faculty = User::factory()->faculty()->create();
    $course = Course::create([
        'title' => 'Quiz Builder Course',
        'slug' => 'quiz-builder-'.uniqid(),
        'description' => 'Shared quiz builder feature test',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'created_by' => $faculty->id,
    ]);
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module One', 'order' => 1]);
    $finalModule = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 2, 'is_final' => true]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Text',
        'order' => 1,
    ]);

    if ($type === 'module') {
        $createUrl = route('faculty.module-quiz.create', [$course->id, $module->id]);
        $storeUrl = route('faculty.module-quiz.store', [$course->id, $module->id]);
        $editUrl = fn (Quiz $quiz): string => route('faculty.module-quiz.edit', [$course->id, $module->id, $quiz->id]);
    } elseif ($type === 'final') {
        $createUrl = route('faculty.final-exam.edit', [$course->id, $finalModule->id]);
        $storeUrl = route('faculty.final-exam.update', [$course->id, $finalModule->id]);
        $editUrl = fn (Quiz $quiz): string => route('faculty.final-exam.edit', [$course->id, $finalModule->id]);
    } elseif ($type === 'lesson') {
        $createUrl = route('faculty.lesson-quiz.create', [$course->id, $lesson->id]);
        $storeUrl = route('faculty.lesson-quiz.store', [$course->id, $lesson->id]);
        $editUrl = fn (Quiz $quiz): string => route('faculty.lesson-quiz.create', [$course->id, $lesson->id]);
    } else {
        $createUrl = route('faculty.quiz.create', [$course->id, $module->id]);
        $storeUrl = route('faculty.quiz.store', [$course->id, $module->id]);
        $editUrl = fn (Quiz $quiz): string => route('faculty.quiz.create', [$course->id, $module->id]);
    }

    return [$course, $faculty, $createUrl, $storeUrl, $editUrl];
}

/**
 * @return list<array{id:int,name:string,questions:list<array<string,mixed>>}>
 */
function quizBuilderRedesignPayload(Quiz $quiz): array
{
    return $quiz->sets()->with('questions')->orderBy('position')->get()->map(fn ($set) => [
        'id' => $set->id,
        'name' => $set->name,
        'questions' => $set->questions->map(function (QuizQuestion $question): array {
            $options = $question->options ?? [];

            return [
                'id' => $question->id,
                'text' => $question->question,
                'type' => $question->type,
                'points' => $question->points,
                'choices' => $options,
                'correct' => $question->type === 'Multiple Choice'
                    ? array_search($question->correct_answer, $options, true)
                    : null,
                'tf_correct' => $question->type === 'True or False'
                    ? array_search($question->correct_answer, ['True', 'False'], true)
                    : null,
                'answer' => $question->type === 'Identification' ? $question->correct_answer : '',
                'alternative_answers' => $question->type === 'Identification' ? $options : [],
            ];
        })->all(),
    ])->all();
}

dataset('shared quiz builder assessment types', [
    'module quiz' => ['module'],
    'summative quiz' => ['summative'],
    'final exam' => ['final'],
    'lesson quiz' => ['lesson'],
]);

test('shared builder creates empty quizzes, authors questions, imports CSV, and saves settings', function (string $type) {
    [$course, $faculty, $createUrl, $storeUrl, $editUrlFor] = quizBuilderRedesignContext($type);
    Storage::fake('local');
    $this->actingAs($faculty)->get($createUrl)->assertOk()
        ->assertSee('name="quiz_title"', false)
        ->assertSee('name="passing_score"', false)
        ->assertSee('name="instructions"', false)
        ->assertSee("moduleCard.addEventListener('click'", false)
        ->assertSee('toggleModuleCollapse(moduleCard.dataset.orderId, moduleToggle)', false)
        ->assertDontSee('name="attempts"', false)
        ->assertDontSee('name="time_limit"', false);

    $createResponse = $this->from($createUrl)->post($storeUrl, [
        'quiz_title' => 'Empty '.$type.' quiz',
        'passing_score' => 75,
        'instructions' => '',
    ]);
    $quiz = Quiz::query()->where('course_id', $course->id)->firstOrFail();
    $editUrl = $editUrlFor($quiz);
    $createResponse->assertRedirect($editUrl);
    $this->assertSame(0, $quiz->questions()->count());
    $this->assertNull($quiz->attempts);
    $this->assertSame(0, (int) $quiz->time_limit);
    $this->assertFalse((bool) $quiz->shuffle_questions);
    $this->assertFalse((bool) $quiz->shuffle_choices);
    $this->assertNull($quiz->questions_per_attempt);

    $set = $quiz->sets()->firstOrFail();
    $questionPayload = [[
        'id' => $set->id,
        'name' => $set->name,
        'questions' => [[
            'text' => 'Manual question '.$type,
            'type' => 'Multiple Choice',
            'points' => 1,
            'choices' => ['First', 'Second'],
            'correct' => 1,
        ]],
    ]];
    $saveUrl = $type === 'module'
        ? route('faculty.module-quiz.update', [$course->id, $quiz->module_id, $quiz->id])
        : $storeUrl;
    $this->actingAs($faculty)->post($saveUrl, [
        'quiz_title' => $quiz->title,
        'passing_score' => 75,
        'sets' => $questionPayload,
    ])->assertRedirect($editUrl);
    $quiz->refresh();
    $this->assertSame(1, $quiz->questions()->count());

    $settings = [
        'quiz_title' => 'Settings saved '.$type,
        'passing_score' => 80,
        'attempts' => '2 Attempts',
        'time_limit' => 20,
        'questions_per_attempt' => 1,
        'shuffle_questions' => '1',
        'shuffle_choices' => '1',
        'sets' => quizBuilderRedesignPayload($quiz),
    ];
    $this->post($saveUrl, $settings)->assertRedirect($editUrl);
    $quiz->refresh();
    $this->assertSame('Settings saved '.$type, $quiz->title);
    $this->assertSame(80, (int) $quiz->passing_score);
    $this->assertSame('2 Attempts', $quiz->attempts);
    $this->assertSame(20, (int) $quiz->time_limit);
    $this->assertSame(1, (int) $quiz->questions_per_attempt);
    $this->assertTrue((bool) $quiz->shuffle_questions);
    $this->assertTrue((bool) $quiz->shuffle_choices);

    $csvSave = $settings;
    $csvSave['quiz_title'] = 'Saved before CSV '.$type;
    $csvSave['continue_to_import'] = '1';
    $this->from($editUrl)->post($saveUrl, $csvSave)
        ->assertRedirect(route('faculty.quiz-import.form', [$course->id, $quiz->id]));
    $this->assertSame('Saved before CSV '.$type, $quiz->fresh()->title);

    $csv = implode("\r\n", [
        'type,question,choice_a,choice_b,choice_c,choice_d,correct_answer,points',
        'multiple_choice,Imported question '.$type.',Yes,No,,,A,1',
    ]);
    $this->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
    ])->assertRedirect(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]));
    $previewState = session('quiz_csv_import.'.$faculty->id.'.'.$quiz->id);
    $this->get(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]))->assertOk();
    $this->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $previewState['token']])
        ->assertRedirect($editUrl.'#questions');
    $this->assertSame(2, $quiz->fresh()->questions()->count());
    $this->get($editUrl)->assertOk()
        ->assertSee('id="quiz-tab-questions" type="button" role="tab" aria-controls="quiz-panel-questions" aria-selected="true"', false)
        ->assertSee('Questions in pool:', false)
        ->assertSee('question-card-toggle')
        ->assertSee('+ Add alternative answer', false)
        ->assertSee('Delete question')
        ->assertSee("panel.querySelector('.set-cards').appendChild(card);", false)
        ->assertDontSee("+ panel.querySelector('.set-cards').appendChild(card);", false);

    $invalidSettings = $settings;
    $invalidSettings['questions_per_attempt'] = 3;
    $invalidSettings['sets'] = quizBuilderRedesignPayload($quiz->fresh());
    $this->from($editUrl)->followingRedirects()->post($saveUrl, $invalidSettings)
        ->assertOk()
        ->assertSee('The quiz was not saved.', false)
        ->assertSee('id="quiz-tab-settings" type="button" role="tab" aria-controls="quiz-panel-settings" aria-selected="true"', false)
        ->assertSee('questions_per_attempt', false);
})->with('shared quiz builder assessment types');

test('identification questions save and accept alternative answers without exposing them to learners', function () {
    [$course, $faculty, , $storeUrl] = quizBuilderRedesignContext('module');
    $this->actingAs($faculty)->post($storeUrl, [
        'quiz_title' => 'Alternative answers quiz',
        'passing_score' => 75,
    ])->assertRedirect();

    $quiz = Quiz::query()->where('course_id', $course->id)->firstOrFail();
    $set = $quiz->sets()->firstOrFail();
    $saveUrl = route('faculty.module-quiz.update', [$course->id, $quiz->module_id, $quiz->id]);
    $this->post($saveUrl, [
        'quiz_title' => $quiz->title,
        'passing_score' => 75,
        'sets' => [[
            'id' => $set->id,
            'name' => $set->name,
            'questions' => [[
                'text' => 'What is the opposite of hot?',
                'type' => 'Identification',
                'points' => 1,
                'answer' => 'cold',
                'alternative_answers' => ['chilly', 'cool'],
            ]],
        ]],
    ])->assertRedirect();

    $question = $quiz->fresh()->questions()->firstOrFail();
    $student = User::factory()->create();
    $attemptService = app(QuizAttemptService::class);
    $presentation = $attemptService->presentationFor($quiz->fresh(), (int) $student->id, remember: false, persistAttempt: false);
    $result = $attemptService->submit(
        $student,
        $quiz->fresh(),
        ['answers' => [(string) $question->id => '  CHILLY  ']],
        null,
        $presentation,
    );

    expect($question->options)->toBe(['chilly', 'cool'])
        ->and($result['passed'])->toBeTrue()
        ->and($result['answers'][(string) $question->id]['correct'])->toBeTrue()
        ->and($result['review_questions'][0]['review_answer'])->toBe('cold')
        ->and($result['review_questions'][0]['options'])->toBe([])
        ->and($presentation['question_snapshot']['questions'][0]['options'])->toBe(['chilly', 'cool'])
        ->and($attemptService->presentedQuestions($quiz, $presentation)[0]['options'])->toBe([]);
});

test('CSV import remains available for quizzes with legacy multiple sets', function () {
    [$course, $faculty, $createUrl, $storeUrl] = quizBuilderRedesignContext('summative');
    $this->actingAs($faculty)->post($storeUrl, [
        'quiz_title' => 'Multiple set quiz',
        'passing_score' => 75,
    ])->assertRedirect();
    $quiz = Quiz::query()->where('course_id', $course->id)->firstOrFail();
    QuizSet::create(['quiz_id' => $quiz->id, 'name' => 'Set B', 'position' => 1]);
    $formUrl = route('faculty.quiz-import.form', [$course->id, $quiz->id]);
    $builderUrl = $createUrl.'#questions';

    $this->get($formUrl)->assertOk();
    $csv = implode("\r\n", [
        'type,question,choice_a,choice_b,choice_c,choice_d,correct_answer,points',
        'multiple_choice,Imported legacy question,Yes,No,,,A,1',
    ]);
    $this->post(route('faculty.quiz-import.preview', [$course->id, $quiz->id]), [
        'csv_file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
    ])->assertRedirect(route('faculty.quiz-import.preview.show', [$course->id, $quiz->id]));
    $previewState = session('quiz_csv_import.'.$faculty->id.'.'.$quiz->id);
    $this->post(route('faculty.quiz-import.confirm', [$course->id, $quiz->id]), ['token' => $previewState['token']])
        ->assertRedirect($builderUrl);

    expect($quiz->fresh()->questions()->count())->toBe(1)
        ->and($quiz->fresh()->sets()->count())->toBe(2);
});
