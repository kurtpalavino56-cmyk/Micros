<?php

use App\Actions\Announcements\CreateAnnouncement;
use App\Actions\Announcements\DeleteAnnouncement;
use App\Actions\Announcements\UpdateAnnouncement;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\User;
use App\Services\CourseCreationService;
use App\Services\CourseLessonService;
use App\Services\CourseModuleService;
use App\Services\QuizManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

function makeFacultyTestCourse(array $attributes = []): Course
{
    return Course::create(array_merge([
        'title' => 'Faculty Test Course',
        'slug' => 'faculty-test-course-'.uniqid(),
        'description' => 'Course used by service tests',
        'level' => 'Beginner',
        'is_published' => false,
        'lessons_count' => 0,
    ], $attributes));
}

function makeFacultyTestModule(Course $course, array $attributes = []): CourseModule
{
    return CourseModule::create(array_merge([
        'course_id' => $course->id,
        'title' => 'Faculty Test Module',
        'order' => 1,
    ], $attributes));
}

test('announcement actions create, update, and delete announcements', function () {
    $author = User::factory()->admin()->create();

    $announcement = app(CreateAnnouncement::class)->execute([
        'title' => 'Important update',
        'body' => 'Please read this announcement.',
        'audience' => ['faculty', 'student'],
    ], $author);

    expect($announcement->created_by)->toBe($author->id)
        ->and($announcement->is_published)->toBeTrue()
        ->and($announcement->audience)->toBe(['faculty', 'student'])
        ->and($announcement->published_at)->not->toBeNull();

    $updated = app(UpdateAnnouncement::class)->execute($announcement, [
        'title' => 'Updated title',
        'body' => 'Updated body',
        'audience' => ['admin'],
    ]);

    expect($updated->title)->toBe('Updated title')
        ->and($updated->audience)->toBe(['admin']);

    expect(app(DeleteAnnouncement::class)->execute($updated))->toBe('Updated title');
    expect(Announcement::find($announcement->id))->toBeNull();
});

test('course creation normalizes fields, stores relationships, and generates unique slugs', function () {
    $author = User::factory()->faculty()->create(['first_name' => 'Faculty', 'last_name' => 'Author']);
    $service = app(CourseCreationService::class);
    $request = Request::create('/courses', 'POST', [
        'related_skills' => [' PHP ', 'Laravel'],
        'related_skills_csv' => "Laravel, Testing\nPHP",
        'prerequisite_ids' => ['4', '0', '4', 'abc'],
        'certificate_enabled' => '1',
        'certificate_title' => 'Completion Certificate',
    ]);

    $first = $service->create($request, [
        'title' => '  Web Development  ',
        'description' => '  Learn web development. ',
        'competencies' => ['PHP', 'Laravel'],
        'learning_objectives' => "Build apps\nDeploy apps,Test apps",
        'category' => 'Development',
        'level' => 'Intermediate',
        'duration' => 6,
        'passing_score' => 80,
        'status' => 'submit',
    ], $author);

    $second = $service->create(Request::create('/courses', 'POST'), [
        'title' => 'Web Development',
        'status' => 'draft',
    ], $author);

    expect($first->title)->toBe('Web Development')
        ->and($first->slug)->toBe('web-development')
        ->and($first->duration)->toBe('6h')
        ->and($first->approval_status)->toBe('pending')
        ->and($first->is_published)->toBeFalse()
        ->and($first->skills)->toBe(['PHP', 'Laravel'])
        ->and($first->objectives)->toBe(['Build apps', 'Deploy apps', 'Test apps'])
        ->and($first->related_skills)->toBe(['PHP', 'Laravel', 'Testing'])
        ->and($first->prerequisite_ids)->toBe([4])
        ->and($first->certificate_enabled)->toBeTrue()
        ->and($second->slug)->toBe('web-development-2')
        ->and($second->approval_status)->toBe('draft');
});

test('course module service assigns the next order and only deletes matching course modules', function () {
    $course = makeFacultyTestCourse();
    $otherCourse = makeFacultyTestCourse(['slug' => 'other-course-'.uniqid()]);
    CourseModule::create(['course_id' => $course->id, 'title' => 'Existing', 'order' => 3]);
    $service = app(CourseModuleService::class);

    $created = $service->create($course, ['module_title' => '  New Module ', 'module_description' => ' Description ']);
    expect($created->title)->toBe('New Module')
        ->and($created->description)->toBe('Description')
        ->and($created->order)->toBe(4);

    $otherModule = CourseModule::create(['course_id' => $otherCourse->id, 'title' => 'Protected', 'order' => 1]);
    $service->deleteByKey($course, 'mod-'.$otherModule->id);
    expect(CourseModule::find($otherModule->id))->not->toBeNull();

    $service->deleteByKey($course, 'mod-'.$created->id);
    expect(CourseModule::find($created->id))->toBeNull();
    $service->deleteByKey($course, 'invalid-key');
});

test('course lesson service creates text lessons and refreshes lesson counts', function () {
    $course = makeFacultyTestCourse();
    $module = makeFacultyTestModule($course);
    $service = app(CourseLessonService::class);

    $lesson = $service->create(
        Request::create('/lessons', 'POST'),
        [
            'lesson_title' => '  Intro Lesson ',
            'lesson_content' => '  Read this first. ',
            'duration' => 15,
        ],
        $course,
        $module
    );

    expect($lesson->title)->toBe('Intro Lesson')
        ->and($lesson->content)->toBe('Read this first.')
        ->and($lesson->type)->toBe('Text')
        ->and($lesson->duration)->toBe('15m')
        ->and($lesson->order)->toBe(1)
        ->and($course->fresh()->lessons_count)->toBe(1);
});

test('course lesson service updates lesson metadata and deletes only matching lessons', function () {
    $course = makeFacultyTestCourse();
    $module = makeFacultyTestModule($course);
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Original',
        'type' => 'Text',
        'content' => 'Old content',
        'order' => 1,
    ]);
    $service = app(CourseLessonService::class);

    $updated = $service->update(Request::create('/lessons', 'POST'), [
        'lesson_title' => ' Updated ',
        'lesson_content' => ' New content ',
        'duration' => 20,
    ], $lesson, $course);

    expect($updated->title)->toBe('Updated')
        ->and($updated->content)->toBe('New content')
        ->and($updated->duration)->toBe('20m');

    $service->deleteByKey($course, 'les-'.$lesson->id);
    expect(CourseLesson::find($lesson->id))->toBeNull()
        ->and($course->fresh()->lessons_count)->toBe(0);
});

test('quiz management parses multiple question types and ignores blank questions', function () {
    $course = makeFacultyTestCourse();
    $module = makeFacultyTestModule($course);
    $request = Request::create('/quiz', 'POST', [
        'quiz_title' => 'Mixed Quiz',
        'passing_score' => 85,
        'attempts' => '2 Attempts',
        'time_limit' => 30,
        'instructions' => ' Answer carefully ',
        'questions' => [
            ['text' => 'Choose one', 'type' => 'Multiple Choice', 'choices' => [' A ', 'B'], 'correct' => 0, 'points' => 2],
            ['text' => 'True question', 'type' => 'True or False', 'tf_correct' => 1, 'points' => 1],
            ['text' => 'Identify this', 'type' => 'Identification', 'answer' => ' Laravel ', 'points' => 3],
            ['text' => '   ', 'type' => 'Multiple Choice', 'choices' => ['Unused'], 'correct' => 0],
        ],
    ]);

    $changed = app(QuizManagementService::class)->save($request, $course, $module);
    $quiz = $module->quiz()->with('questions')->first();

    expect($changed)->toBeTrue()
        ->and($quiz->title)->toBe('Mixed Quiz')
        ->and($quiz->passing_score)->toBe(85)
        ->and($quiz->attempts)->toBe('2 Attempts')
        ->and($quiz->time_limit)->toBe(30)
        ->and($quiz->instructions)->toBe('Answer carefully')
        ->and($quiz->questions)->toHaveCount(3)
        ->and($quiz->questions[0]->options)->toBe(['A', 'B'])
        ->and($quiz->questions[0]->correct_answer)->toBe('A')
        ->and($quiz->questions[1]->options)->toBe(['True', 'False'])
        ->and($quiz->questions[1]->correct_answer)->toBe('False')
        ->and($quiz->questions[2]->correct_answer)->toBe('Laravel')
        ->and($quiz->questions_changed_at)->not->toBeNull();
});

test('quiz management detects unchanged questions and deletes quizzes safely', function () {
    $course = makeFacultyTestCourse();
    $module = makeFacultyTestModule($course);
    $payload = [
        'quiz_title' => 'Stable Quiz',
        'passing_score' => 70,
        'questions' => [
            ['text' => 'Question', 'type' => 'Multiple Choice', 'choices' => ['A', 'B'], 'correct' => 0, 'points' => 1],
        ],
    ];
    $service = app(QuizManagementService::class);
    $service->save(Request::create('/quiz', 'POST', $payload), $course, $module);
    $quiz = $module->quiz()->first();
    $changedAt = $quiz->questions_changed_at;

    expect($service->save(Request::create('/quiz', 'POST', $payload), $course, $module))->toBeFalse();
    expect($module->quiz()->first()->questions_changed_at->equalTo($changedAt))->toBeTrue();
    expect($service->delete($module->fresh()))->toBe('Stable Quiz');
    expect($service->delete($module->fresh()))->toBeNull();
});
