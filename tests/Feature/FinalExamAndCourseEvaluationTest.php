<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\CourseReadinessService;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function finalExamEvaluationFixture(array $courseOverrides = []): array
{
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = Course::create(array_merge([
        'title' => 'Final Exam Course',
        'slug' => 'final-exam-course-'.uniqid(),
        'short_description' => 'A course for final exam checks.',
        'description' => '<p>Course description</p>',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'is_approved' => true,
        'approval_status' => 'approved',
        'created_by' => $faculty->id,
        'objectives' => ['Build practical skills'],
    ], $courseOverrides));

    return [$faculty, $student, $course];
}

function finalExamEvaluationQuiz(Course $course, CourseModule $module, string $title = 'Exam'): Quiz
{
    $quiz = Quiz::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => $title,
        'passing_score' => 70,
        'attempts' => 'Unlimited',
        'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question' => 'A question?',
        'type' => 'Multiple Choice',
        'options' => ['A', 'B'],
        'correct_answer' => 'A',
        'points' => 1,
    ]);

    return $quiz;
}

test('faculty can add one final exam, regular modules remain before it, and approved courses return to draft', function () {
    [$faculty, , $course] = finalExamEvaluationFixture();
    $regular = CourseModule::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);
    $this->actingAs($faculty)
        ->post(route('faculty.final-exam.store', $course->id))
        ->assertRedirect();

    $final = $course->modules()->where('is_final', true)->firstOrFail();
    expect($final->title)->toBe('Final Exam')
        ->and($final->order)->toBe(2)
        ->and($course->fresh()->approval_status)->toBe('draft');

    $this->post(route('faculty.module.store', $course->id), ['module_title' => 'Module 2'])
        ->assertRedirect();
    $regularModules = $course->modules()->where('is_final', false)->orderBy('order')->get();
    expect($regularModules->pluck('title')->all())->toBe(['Module 1', 'Module 2'])
        ->and($course->modules()->where('is_final', true)->first()->order)->toBe(3);

    $this->postJson(route('faculty.modules.reorder', $course->id), ['items' => [$final->id, $regular->id]])
        ->assertUnprocessable();
});

test('faculty cannot create or remove a final exam for a course they do not own', function () {
    [, , $course] = finalExamEvaluationFixture();
    $otherFaculty = User::factory()->faculty()->create();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);

    $this->actingAs($otherFaculty)
        ->post(route('faculty.final-exam.store', $course->id))
        ->assertNotFound();
    $this->post(route('faculty.quiz.store', [$course->id, $module->id]), ['quiz_title' => 'Unauthorized edit'])
        ->assertNotFound();
    $this->post(route('faculty.final-exam.destroy', $course->id))
        ->assertNotFound();
});

test('a blank final exam attempts value is saved as unlimited', function () {
    [$faculty, , $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);

    $this->actingAs($faculty)
        ->post(route('faculty.quiz.store', [$course->id, $module->id]), [
            'quiz_title' => 'Final Assessment',
            'passing_score' => 70,
            'attempts' => '',
            'questions' => [[
                'text' => 'Question?',
                'type' => 'Multiple Choice',
                'points' => 1,
                'choices' => ['A', 'B'],
                'correct' => 0,
            ]],
        ])->assertRedirect();

    expect($module->quiz()->firstOrFail()->attempts)->toBe('Unlimited')
        ->and($course->fresh()->approval_status)->toBe('draft');
});

test('final exam readiness requires a question and exempts the exam module from lessons', function () {
    [, , $course] = finalExamEvaluationFixture();
    $regular = CourseModule::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);
    CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $regular->id, 'title' => 'Lesson', 'type' => 'Text',
        'order' => 1, 'content' => 'Learning material',
    ]);
    $final = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 2, 'is_final' => true]);

    expect(app(CourseReadinessService::class)->isReady($course->fresh()))->toBeFalse();
    finalExamEvaluationQuiz($course, $final);
    expect(app(CourseReadinessService::class)->isReady($course->fresh()))->toBeTrue();
});

test('final exam stays locked until prior lessons and module quizzes are complete', function () {
    [$faculty, $student, $course] = finalExamEvaluationFixture();
    $regular = CourseModule::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $regular->id, 'title' => 'Lesson', 'type' => 'Text',
        'order' => 1, 'content' => 'Learning material',
    ]);
    $regularQuiz = finalExamEvaluationQuiz($course, $regular, 'Module Quiz');
    $final = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 2, 'is_final' => true]);
    $finalQuiz = finalExamEvaluationQuiz($course, $final, 'Final Exam');
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);

    $this->actingAs($student)->get(route('quiz.show', $finalQuiz->id))->assertForbidden();
    DB::table('lesson_completions')->insert([
        'user_id' => $student->id, 'lesson_id' => $lesson->id, 'completed_at' => now(), 'server_verified_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $regularQuiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);

    $this->get(route('quiz.show', $finalQuiz->id))->assertOk();
});

test('completion service includes the final module quiz in quiz mastery', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    $enrollment = Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);

    app(MicrocredentialCompletionService::class)->evaluate($enrollment);
    expect($enrollment->fresh()->quiz_mastery_met)->toBeFalse();

    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);
    app(MicrocredentialCompletionService::class)->evaluate($enrollment->fresh());
    expect($enrollment->fresh()->quiz_mastery_met)->toBeTrue();
});

test('students may submit one course evaluation only after passing the final exam', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    $payload = [
        'rating' => 5,
        'answers' => [
            'course' => array_fill_keys(['materials', 'instructions', 'assessments', 'skills', 'recommend'], 4),
            'platform' => ['navigation' => 3, 'progress' => 4],
        ],
    ];

    $this->actingAs($student)->postJson(route('courses.evaluation.store', $course->id), $payload)->assertForbidden();
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);
    $this->post(route('courses.evaluation.store', $course->id), $payload)->assertRedirect(route('courses.learn', $course->id));
    expect(CourseReview::query()->where('course_id', $course->id)->where('user_id', $student->id)->firstOrFail()->rating)->toBe(5);
    $this->post(route('courses.evaluation.store', $course->id), $payload)->assertStatus(409);
});

test('the course review is presented as a learning step after the final exam', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('courses.learn', $course->id))->assertOk();
    $html = $response->getContent();

    expect(strpos($html, 'id="view-course-evaluation"'))->toBeGreaterThan(strpos($html, '</aside>'));
    $response->assertSee('id="course-evaluation-form"', false)
        ->assertSee('Browse Courses')
        ->assertSee('Return to My Courses')
        ->assertDontSee('Leave a course review')
        ->assertDontSee('course-evaluation-cta');
});

test('the eligible course review remains available after reloading the learning page', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('courses.learn', $course->id))->assertOk();
    $html = $response->getContent();
    $formStart = strpos($html, '<form id="course-evaluation-form"');
    $formTagEnd = strpos($html, '>', $formStart);

    $response->assertSee('id="course-review-nav"', false);
    expect($formStart)->not->toBeFalse()
        ->and(substr($html, $formStart, $formTagEnd - $formStart))->not->toContain('hidden');
});

test('an ineligible student sees a locked review entry and a hidden review form', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);
    CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Lesson', 'type' => 'Text',
        'order' => 1, 'content' => 'Learning material',
    ]);
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);

    $response = $this->actingAs($student)->get(route('courses.learn', $course->id))->assertOk();
    $html = $response->getContent();
    $formStart = strpos($html, '<form id="course-evaluation-form"');
    $formTagEnd = strpos($html, '>', $formStart);

    $response->assertSee('id="course-review-nav"', false)
        ->assertSee('id="course-evaluation-locked"', false);
    expect($html)->toContain('id="course-evaluation-form" method="POST" action=')
        ->and(substr($html, $formStart, $formTagEnd - $formStart))->toContain('hidden');
});

test('a previously submitted review is marked submitted and the form is omitted', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    CourseReview::create([
        'course_id' => $course->id, 'user_id' => $student->id, 'rating' => 5, 'answers' => [],
    ]);

    $this->actingAs($student)->get(route('courses.learn', $course->id))
        ->assertOk()
        ->assertSee('id="course-review-sub">✓ Submitted</span>', false)
        ->assertDontSee('id="course-evaluation-form"', false);
});

test('course evaluation returns JSON for success and validation failures', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 100, 'passed' => true,
        'started_at' => now(), 'submitted_at' => now(),
    ]);
    $payload = [
        'rating' => 5,
        'answers' => [
            'course' => array_fill_keys(['materials', 'instructions', 'assessments', 'skills', 'recommend'], 4),
            'platform' => ['navigation' => 4, 'progress' => 4],
        ],
    ];
    $this->actingAs($student);

    $this->postJson(route('courses.evaluation.store', $course->id), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rating', 'answers.course.materials', 'answers.platform.navigation']);
    $this->postJson(route('courses.evaluation.store', $course->id), $payload)
        ->assertOk()
        ->assertJson(['ok' => true, 'review_submitted' => true, 'evaluation_eligible' => true]);
    $this->postJson(route('courses.evaluation.store', $course->id), $payload)
        ->assertStatus(409);
});

test('failed final exam attempt exhaustion unlocks review without suggesting credentials', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    $quiz->update(['attempts' => '1 Attempt']);
    $question = $quiz->questions()->firstOrFail();
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);

    $response = $this->actingAs($student)->postJson(route('courses.progress', $course->id), [
        'quiz_results' => [0 => ['answers' => [(string) $question->id => 'B']]],
    ])->assertOk()->assertJsonPath('evaluation_eligible', true);

    expect($response->json('quiz_submissions.0.passed'))->toBeFalse()
        ->and($response->json('quiz_attempts.0.exhausted'))->toBeTrue();
    $this->get(route('courses.learn', $course->id))
        ->assertOk()
        ->assertSee('id="course-evaluation-exhausted-note"', false)
        ->assertSee('credentials may not be awarded');
});

test('blocked quiz submission returns a handled blocked result and current attempt state', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    $quiz->update(['attempts' => '1 Attempt']);
    $question = $quiz->questions()->firstOrFail();
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);
    $attempt = QuizAttempt::create([
        'user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 0, 'passed' => false,
        'started_at' => now(), 'submitted_at' => now(),
    ]);
    QuizAttemptAnswer::create([
        'quiz_attempt_id' => $attempt->id, 'question_id' => $question->id, 'answer' => 'B', 'is_correct' => false,
    ]);

    $this->actingAs($student)->postJson(route('courses.progress', $course->id), [
        'quiz_results' => [0 => ['answers' => [(string) $question->id => 'A']]],
    ])->assertOk()
        ->assertJsonPath('quiz_submissions.0.blocked', true)
        ->assertJsonPath('quiz_attempts.0.exhausted', true)
        ->assertJsonPath('evaluation_eligible', true);
});

test('standalone quiz submission returns course review eligibility', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Final Exam', 'order' => 1, 'is_final' => true]);
    $quiz = finalExamEvaluationQuiz($course, $module, 'Final Exam');
    $question = $quiz->questions()->firstOrFail();
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'progress_state' => []]);

    $this->actingAs($student)->postJson(route('quiz.submit', $quiz->id), [
        'answers' => [(string) $question->id => 'A'],
    ])->assertOk()
        ->assertJsonPath('passed', true)
        ->assertJsonPath('evaluation_eligible', true);
});

test('courses without a final exam require full learning progress before evaluation', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Lesson', 'type' => 'Text',
        'order' => 1, 'content' => 'Learning material',
    ]);
    Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now()->subDay(), 'progress_state' => []]);
    $payload = [
        'rating' => 4,
        'answers' => [
            'course' => array_fill_keys(['materials', 'instructions', 'assessments', 'skills', 'recommend'], 4),
            'platform' => ['navigation' => 4, 'progress' => 4],
        ],
    ];

    $this->actingAs($student)->post(route('courses.evaluation.store', $course->id), $payload)->assertForbidden();
    DB::table('lesson_completions')->insert([
        'user_id' => $student->id, 'lesson_id' => $lesson->id, 'completed_at' => now(), 'server_verified_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->post(route('courses.evaluation.store', $course->id), $payload)->assertRedirect(route('courses.learn', $course->id));
});

test('non-enrolled students cannot submit an evaluation', function () {
    [, $student, $course] = finalExamEvaluationFixture();
    $this->actingAs($student)->post(route('courses.evaluation.store', $course->id), [])->assertForbidden();
});

test('course rating cards show new below three reviews and the aggregate when eligible', function () {
    $newRating = view('components.course-rating', ['rating' => 5, 'count' => 2])->render();
    $ratedCourse = view('components.course-rating', ['rating' => 4.6, 'count' => 12])->render();

    expect($newRating)->toContain('New')
        ->and($ratedCourse)->toContain('4.6 (12)')
        ->and($ratedCourse)->toContain('★★★★★');
});

test('platform answers are stored separately from the average and faculty analytics respects course selection', function () {
    [$faculty, , $course] = finalExamEvaluationFixture();
    [, , $otherCourse] = finalExamEvaluationFixture(['title' => 'Unselected Course', 'slug' => 'unselected-course-'.uniqid()]);
    foreach ([4, 5, 5] as $index => $rating) {
        $student = User::factory()->create();
        CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => $rating,
            'answers' => ['platform' => ['navigation' => 1, 'progress' => 1]],
            'comment' => 'Useful course feedback '.($index + 1),
        ]);
    }

    $aggregate = Course::query()->withAvg('reviews', 'rating')->withCount('reviews')->findOrFail($course->id);
    expect((int) $aggregate->reviews_count)->toBe(3)
        ->and((float) $aggregate->reviews_avg_rating)->toBeGreaterThan(4.6);

    $otherStudent = User::factory()->create();
    CourseReview::create([
        'course_id' => $otherCourse->id,
        'user_id' => $otherStudent->id,
        'rating' => 2,
        'answers' => [],
        'comment' => 'Unselected course comment',
    ]);

    $this->actingAs($faculty)->get(route('faculty.analytics', ['course_id' => $course->id]))
        ->assertOk()
        ->assertSee('4.7')
        ->assertSee('Useful course feedback 3')
        ->assertDontSee('Unselected course comment');
});
