<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LearningOutcome;
use App\Models\LessonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function richTextCourse(User $faculty, array $attributes = []): Course
{
    return Course::create(array_merge([
        'title' => 'Table Support Course',
        'slug' => 'table-support-'.uniqid(),
        'short_description' => 'Short summary.',
        'description' => '<p>Course description</p>',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'is_approved' => true,
        'created_by' => $faculty->id,
    ], $attributes));
}

test('the create form gives table controls only to the four content fields and the three new basic fields', function () {
    $faculty = User::factory()->faculty()->create();

    $response = $this->actingAs($faculty)->get(route('faculty.create'))->assertOk();

    $response
        ->assertSee('id="course-description-editor"', false)
        ->assertSee('data-editor-preset="content-table"', false)
        ->assertSee('id="learning-outcomes-editor"', false)
        ->assertSee('id="targetLearners"', false)
        ->assertSee('id="assessmentStrategy"', false)
        ->assertSee('id="gradingRubric"', false)
        ->assertSee('data-editor-preset="basic-table"', false);

    // The three converted fields are editors now, not plain textareas.
    $response
        ->assertDontSee('<textarea class="textarea" id="targetLearners"', false)
        ->assertDontSee('<textarea class="textarea" id="assessmentStrategy"', false)
        ->assertDontSee('<textarea class="textarea" id="gradingRubric"', false);
});

test('legacy plain text in fields 5 to 7 loads into the editors with line breaks preserved', function () {
    $faculty = User::factory()->faculty()->create();
    $course = richTextCourse($faculty, [
        'target_learners' => "New starters\n- Career changers",
        'assessment_strategy' => "Formative quizzes\nSummative project",
        'grading_rubric' => "Pass\n- 70% overall",
    ]);

    $response = $this->actingAs($faculty)
        ->get(route('faculty.courses.edit', $course->id))
        ->assertOk();

    // Blade escapes the generated HTML inside the editor textarea.
    $response
        ->assertSee('New starters&lt;br&gt;- Career changers', false)
        ->assertSee('Formative quizzes&lt;br&gt;Summative project', false)
        ->assertSee('Pass&lt;br&gt;- 70% overall', false);
});

test('the manage page gives table controls to lesson and activity editors but not module or quiz editors', function () {
    $faculty = User::factory()->faculty()->create();
    $course = richTextCourse($faculty);
    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module One',
        'description' => '<p>Module overview</p>',
        'order' => 1,
    ]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Text',
        'order' => 1,
        'content' => '<p>Lesson body</p>',
    ]);
    LessonActivity::create([
        'lesson_id' => $lesson->id,
        'title' => 'Activity One',
        'activity_type' => 'reflection',
        'instructions' => '<p>Activity instructions</p>',
        'is_required' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($faculty)
        ->get(route('faculty.courses.manage', $course->id))
        ->assertOk();

    $response
        ->assertSee('id="lesson-content-add-'.$module->id.'"', false)
        ->assertSee('id="module-activity-instructions-add-'.$module->id.'"', false)
        ->assertSee('id="lesson-content-edit-'.$lesson->id.'"', false)
        ->assertSee('data-editor-preset="content-table"', false);

    // Module description and quiz instruction editors keep the default preset.
    $response
        ->assertSee('id="module-description-edit-'.$module->id.'"', false)
        ->assertDontSee('id="module-description-edit-'.$module->id.'" data-editor-preset', false);
});

test('the public course page renders tables in every description path safely', function () {
    $faculty = User::factory()->faculty()->create();
    $course = richTextCourse($faculty, [
        'description' => '<p>Overview</p><table><tbody><tr><td>Description cell</td></tr></tbody></table>',
        'target_learners' => '<table><tbody><tr><td>Learners cell</td></tr></tbody></table>',
        'assessment_strategy' => '<table><tbody><tr><td>Assessment cell</td></tr></tbody></table>',
        'grading_rubric' => '<table><tbody><tr><td>Rubric cell</td></tr></tbody></table>',
    ]);
    LearningOutcome::create([
        'course_id' => $course->id,
        'description' => '<table><tbody><tr><td>Outcome cell</td></tr></tbody></table>',
        'order' => 1,
    ]);

    $this->get(route('public.courses.show', $course->id))
        ->assertOk()
        ->assertSee('Description cell')
        ->assertSee('Learners cell')
        ->assertSee('Assessment cell')
        ->assertSee('Rubric cell')
        ->assertSee('Outcome cell')
        ->assertSee('<td>Description cell</td>', false)
        ->assertSee('<div class="table-scroll"><table><tbody><tr><td>Description cell</td></tr></tbody></table></div>', false)
        ->assertSee('<div class="table-scroll"><table><tbody><tr><td>Learners cell</td></tr></tbody></table></div>', false);
});

test('the public course page renders legacy plain text fields 5 to 7 with line breaks', function () {
    $faculty = User::factory()->faculty()->create();
    $course = richTextCourse($faculty, [
        'target_learners' => "Learner one\nLearner two",
        'assessment_strategy' => "Quiz one\nQuiz two",
        'grading_rubric' => "Pass\nFail",
    ]);

    $this->get(route('public.courses.show', $course->id))
        ->assertOk()
        ->assertSee('Learner one<br>Learner two', false)
        ->assertSee('Quiz one<br>Quiz two', false)
        ->assertSee('Pass<br>Fail', false);
});

test('the student course page renders tables and legacy line breaks', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = richTextCourse($faculty, [
        'description' => '<p>Overview</p><table><tbody><tr><td>Student description cell</td></tr></tbody></table>',
        'target_learners' => "Student learner one\nStudent learner two",
        'assessment_strategy' => '<table><tbody><tr><td>Student assessment cell</td></tr></tbody></table>',
        'grading_rubric' => '<table><tbody><tr><td>Student rubric cell</td></tr></tbody></table>',
    ]);
    LearningOutcome::create([
        'course_id' => $course->id,
        'description' => '<table><tbody><tr><td>Student outcome cell</td></tr></tbody></table>',
        'order' => 1,
    ]);

    $this->actingAs($student)
        ->get(route('courses.show', $course->id))
        ->assertOk()
        ->assertSee('Student description cell')
        ->assertSee('Student learner one<br>Student learner two', false)
        ->assertSee('Student assessment cell')
        ->assertSee('Student rubric cell')
        ->assertSee('Student outcome cell')
        ->assertSee('<div class="table-scroll"><table><tbody><tr><td>Student description cell</td></tr></tbody></table></div>', false);
});

test('the lesson player wraps saved lesson and module tables for scrolling', function () {
    $faculty = User::factory()->faculty()->create();
    $student = User::factory()->create();
    $course = richTextCourse($faculty);
    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module One',
        'description' => '<table><tbody><tr><td>Module table cell</td></tr></tbody></table>',
        'order' => 1,
    ]);
    CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Text',
        'order' => 1,
        'content' => '<table><tbody><tr><td>Lesson table cell</td></tr></tbody></table>',
    ]);
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_state' => [],
    ]);

    $response = $this->actingAs($student)
        ->get(route('courses.learn', $course->id))
        ->assertOk();

    // The player receives JSON-encoded HTML, so the wrapper appears escaped.
    $response->assertSee('table-scroll', false);
    $response->assertSee('Lesson table cell');
    $response->assertSee('Module table cell');
});

test('the admin course page renders tables and legacy line breaks', function () {
    $faculty = User::factory()->faculty()->create();
    $admin = User::factory()->admin()->create();
    $course = richTextCourse($faculty, [
        'target_learners' => "Admin learner one\nAdmin learner two",
        'assessment_strategy' => '<table><tbody><tr><td>Admin assessment cell</td></tr></tbody></table>',
        'grading_rubric' => '<table><tbody><tr><td>Admin rubric cell</td></tr></tbody></table>',
    ]);
    LearningOutcome::create([
        'course_id' => $course->id,
        'description' => '<table><tbody><tr><td>Admin outcome cell</td></tr></tbody></table>',
        'order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.courses.show', $course->id))
        ->assertOk()
        ->assertSee('Admin learner one<br>Admin learner two', false)
        ->assertSee('Admin assessment cell')
        ->assertSee('Admin rubric cell')
        ->assertSee('Admin outcome cell')
        ->assertSee('<div class="table-scroll"><table><tbody><tr><td>Admin assessment cell</td></tr></tbody></table></div>', false);
});

test('only the table presets configure the insert table button', function () {
    $source = file_get_contents(resource_path('js/app.js'));

    expect($source)
        ->toContain("const preset = element.dataset.editorPreset || '';")
        ->toContain("isContentTablePreset = preset === 'content-table'")
        ->toContain("isBasicTablePreset = preset === 'basic-table'")
        ->toContain('contentToolbar: [\'tableColumn\', \'tableRow\']');

    // The shared default toolbar must not grow a table button.
    $defaultToolbar = substr(
        $source,
        strpos($source, 'const defaultToolbar = ['),
        strpos($source, 'const config = {') - strpos($source, 'const defaultToolbar = [')
    );

    expect($defaultToolbar)->not->toContain('insertTable');
});
