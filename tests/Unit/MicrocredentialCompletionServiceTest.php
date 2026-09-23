<?php

use App\Models\CompetencyProgress;
use App\Models\CompetencyUnit;
use App\Models\CompetencyCategory;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LearningOutcome;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Builds a course + enrollment where every REAL gate (lessons, quiz
 * mastery, competency mastery — when linked) is already genuinely
 * satisfied via actual persisted data (lesson_completions, a passing
 * QuizAttempt, a completed CompetencyProgress), not by hand-setting
 * enrollment flags. evaluate() recomputes those flags from this data
 * every time it runs, so hand-setting them would be overwritten anyway —
 * this fixture exercises the real gate chain up to (but not through) the
 * institutional actions under test.
 */
function makeReadyEnrollment(array $courseOverrides = [], bool $withCompetency = true): array
{
    $student = User::factory()->create();

    $course = Course::create(array_merge([
        'title' => 'Test Microcredential',
        'slug' => 'test-microcredential-'.uniqid(),
        'description' => 'A microcredential under test',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'requires_faculty_verification' => false,
    ], $courseOverrides));

    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module One',
        'order' => 1,
    ]);

    $lesson = CourseLesson::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Lesson One',
        'type' => 'Video',
        'order' => 1,
    ]);

    $quiz = Quiz::create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Module Quiz',
        'passing_score' => 70,
        'attempts' => '3 Attempts',
        'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question' => 'Q1',
        'type' => 'Multiple Choice',
        'options' => ['A', 'B'],
        'correct_answer' => 'A',
        'points' => 1,
    ]);

    if ($withCompetency) {
        $category = CompetencyCategory::create(['name' => 'Category', 'description' => 'x']);
        $unit = CompetencyUnit::create([
            'competency_category_id' => $category->id,
            'title' => 'Unit One',
            'description' => 'x',
            'order' => 1,
            'is_active' => true,
        ]);
        LearningOutcome::create([
            'course_id' => $course->id,
            'competency_unit_id' => $unit->id,
            'code' => 'LO1',
            'description' => 'Demonstrate the thing',
            'order' => 1,
        ]);
        CompetencyProgress::create([
            'user_id' => $student->id,
            'competency_unit_id' => $unit->id,
            'status' => 'completed',
            'mastery_score' => 100,
        ]);
    }

    DB::table('lesson_completions')->insert([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    QuizAttempt::create([
        'user_id' => $student->id,
        'quiz_id' => $quiz->id,
        'score' => 100,
        'passed' => true,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percent' => 100,
        'progress_state' => [],
    ]);

    $service = app(MicrocredentialCompletionService::class);
    $enrollment = $service->evaluate($enrollment);

    return [$course, $enrollment->fresh(), $student, $service];
}

test('academic-unit confirmation changes pending to confirmed and records user and timestamp', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    expect($enrollment->academic_unit_confirmation_status)->toBe('pending');

    $result = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');

    expect($result->academic_unit_confirmation_status)->toBe('confirmed')
        ->and($result->academic_unit_confirmed_by)->toBe($officer->id)
        ->and($result->academic_unit_confirmed_at)->not->toBeNull();
});

test('academic-unit confirmation triggers evaluate() and completes the enrollment once it is the only remaining gate', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    expect($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION);

    $result = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');

    expect($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});

test('rejected academic-unit confirmation sets completion_status to rejected', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $result = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'rejected');

    expect($result->academic_unit_confirmation_status)->toBe('rejected')
        ->and($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);
});

test('faculty verification only affects completion when the course requires it', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['requires_faculty_verification' => false]);
    $faculty = User::factory()->create();

    // Course does not require faculty verification, so the status stays
    // not_required and is never a blocker regardless of any decision
    // recorded — but the decision is still persisted faithfully.
    expect($enrollment->faculty_verification_status)->toBe('not_required');

    $result = $service->recordFacultyVerification($enrollment, $faculty->id, 'verified');

    // evaluate() (run internally by recordFacultyVerification) recomputes
    // faculty_verification_status via resolveOptionalGateStatus(), which
    // always resolves to not_required when the course doesn't require
    // faculty verification — regardless of what was just recorded. The
    // action itself is still faithfully recorded via faculty_verified_by.
    expect($result->faculty_verification_status)->toBe('not_required')
        ->and($result->faculty_verified_by)->toBe($faculty->id);

    // Still blocked only by academic-unit confirmation, unaffected by
    // faculty verification either way.
    expect($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION);
});

test('faculty verification is required and completion is blocked until verified when the course requires it', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['requires_faculty_verification' => true]);
    $faculty = User::factory()->create();
    $officer = User::factory()->create();

    expect($enrollment->faculty_verification_status)->toBe('pending');

    // Academic-unit confirmation alone is not enough when faculty
    // verification is also required and still pending.
    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    expect($enrollment->fresh()->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);

    $result = $service->recordFacultyVerification($enrollment->fresh(), $faculty->id, 'verified');

    expect($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});

test('rejected faculty verification sets completion_status to rejected when the course requires it', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['requires_faculty_verification' => true]);
    $faculty = User::factory()->create();

    $result = $service->recordFacultyVerification($enrollment, $faculty->id, 'rejected');

    expect($result->faculty_verification_status)->toBe('rejected')
        ->and($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);
});

test('repeated academic-unit confirmation issues the badge and certificate exactly once, never duplicating', function () {
    // Previously named "...does not itself issue a badge or certificate"
    // — that was only true because issuance wasn't wired into this
    // method yet. Now that recordAcademicUnitConfirmation() issues via
    // evaluateAndIssueIfEligible() once officially completed, this test
    // instead proves the important remaining guarantee: calling it
    // repeatedly (e.g. an admin re-submitting the form) never creates a
    // duplicate credential, and never touches AcademicCreditRecognition.
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $badge = \App\Models\Badge::create(['name' => 'Repeat Confirm Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $service->recordAcademicUnitConfirmation($enrollment->fresh(), $officer->id, 'confirmed');
    $service->recordAcademicUnitConfirmation($enrollment->fresh(), $officer->id, 'confirmed');

    expect(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1)
        ->and(DB::table('academic_credit_recognitions')->count())->toBe(0);
});

test('recordFacultyVerification rejects an invalid decision value', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();

    expect(fn () => $service->recordFacultyVerification($enrollment, $student->id, 'approved'))
        ->toThrow(InvalidArgumentException::class);
});

test('recordAcademicUnitConfirmation rejects an invalid decision value', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();

    expect(fn () => $service->recordAcademicUnitConfirmation($enrollment, $student->id, 'approved'))
        ->toThrow(InvalidArgumentException::class);
});

test('there is no student-facing route that can invoke the institutional action methods', function () {
    // These methods are only ever reachable via direct service calls
    // (from a future authorized faculty/admin controller action, not yet
    // wired per Step 11). No route in the app currently maps to either
    // method, so a student has no HTTP path to them at all.
    $routes = collect(app('router')->getRoutes())
        ->map(fn ($route) => $route->getActionName());

    expect($routes->contains(fn ($action) => str_contains($action, 'recordFacultyVerification')))->toBeFalse()
        ->and($routes->contains(fn ($action) => str_contains($action, 'recordAcademicUnitConfirmation')))->toBeFalse();
});

// ── Phase 3 institutional-workflow fix regression tests ─────────────────

test('mastery reached with faculty not required and academic-unit pending does not officially complete and issues nothing', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();

    expect($enrollment->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($enrollment->faculty_verification_status)->toBe('not_required')
        ->and($enrollment->academic_unit_confirmation_status)->toBe('pending')
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);
});

test('confirming academic-unit review completes the enrollment and issues badge and certificate when configured', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $badge = \App\Models\Badge::create(['name' => 'Test Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    // Issuance is now automatic: recordAcademicUnitConfirmation() itself
    // calls evaluateAndIssueIfEligible() internally once completion_status
    // genuinely reaches 'completed' — no separate manual
    // issueBadgeIfEligible()/issueCertificateIfEligible() call is needed
    // or made here, matching the real admin-route behavior with no
    // subsequent student request.
    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $enrollment = $enrollment->fresh();

    expect($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1);
});

test('a course requiring faculty verification stays blocked through faculty verification, then still requires academic-unit confirmation before any credential', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['requires_faculty_verification' => true]);
    $faculty = User::factory()->create();
    $officer = User::factory()->create();

    expect($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
        ->and($enrollment->faculty_verification_status)->toBe('pending');

    $service->recordFacultyVerification($enrollment, $faculty->id, 'verified');
    $enrollment = $enrollment->fresh();

    // Faculty verified, but academic-unit confirmation is still pending —
    // must NOT be completed and must NOT have issued anything.
    expect($enrollment->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('user_badges')->count())->toBe(0)
        ->and(DB::table('certificates')->count())->toBe(0);

    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    expect($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});

test('credential issuance remains idempotent across repeated evaluate/issue calls', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $badge = \App\Models\Badge::create(['name' => 'Idempotency Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    // recordAcademicUnitConfirmation() already issues the badge/certificate
    // internally at this point (evaluateAndIssueIfEligible()). The extra
    // evaluate()/issueXIfEligible() calls below are deliberately redundant
    // — kept as explicit idempotency coverage proving repeat calls from
    // any angle never create a duplicate.
    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $enrollment = $enrollment->fresh();

    $service->issueBadgeIfEligible($enrollment);
    $service->issueCertificateIfEligible($enrollment);
    $service->evaluate($enrollment);
    $service->issueBadgeIfEligible($enrollment->fresh());
    $service->issueCertificateIfEligible($enrollment->fresh());

    expect(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1);
});

// ═══════════════════════════════════════════════════════════════════════
// Phase 1 — Credential Lifecycle Integrity
//
// enrollments.completion_status is the only authority for official
// completion. `completed` is terminal, rejected/revoked are protected,
// institutional actions are only accepted for an enrollment that is
// genuinely awaiting sign-off, and the legacy is_completed column is a
// mirror, never an input.
// ═══════════════════════════════════════════════════════════════════════

/**
 * A course + enrollment that is deliberately NOT at mastery.
 *  - 'in_progress'        : lesson not completed, quiz not attempted.
 *  - 'assessment_pending' : lesson completed, quiz attempted and failed.
 */
function makeUnmasteredEnrollment(array $courseOverrides = [], string $state = 'in_progress'): array
{
    $student = User::factory()->create();

    $course = Course::create(array_merge([
        'title' => 'Unmastered Microcredential',
        'slug' => 'unmastered-microcredential-'.uniqid(),
        'description' => 'A microcredential under test',
        'category' => 'Development',
        'level' => 'Beginner',
        'is_published' => true,
        'requires_faculty_verification' => false,
    ], $courseOverrides));

    $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Module One', 'order' => 1]);
    $lesson = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Lesson One', 'type' => 'Video', 'order' => 1,
    ]);
    $quiz = Quiz::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Module Quiz', 'passing_score' => 70, 'attempts' => '3 Attempts', 'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $quiz->id, 'question' => 'Q1', 'type' => 'Multiple Choice',
        'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 1,
    ]);

    if ($state === 'assessment_pending') {
        DB::table('lesson_completions')->insert([
            'user_id' => $student->id, 'lesson_id' => $lesson->id,
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        QuizAttempt::create([
            'user_id' => $student->id, 'quiz_id' => $quiz->id,
            'score' => 0, 'passed' => false, 'started_at' => now(), 'submitted_at' => now(),
        ]);
    }

    $enrollment = Enrollment::create([
        'user_id' => $student->id, 'course_id' => $course->id,
        'enrolled_at' => now(), 'progress_percent' => 100, 'progress_state' => [],
    ]);

    $service = app(MicrocredentialCompletionService::class);
    $enrollment = $service->evaluate($enrollment);

    return [$course, $enrollment->fresh(), $student, $service];
}

/**
 * Simulates faculty editing a live course afterwards: one more lesson in
 * the existing module, plus a brand-new module with an unanswered quiz.
 * A learner with no evidence for this new work would fail every learning
 * gate if the enrollment were re-derived from scratch.
 */
function addUnfinishedContent(Course $course): CourseLesson
{
    $module = $course->modules()->first();

    $lesson = CourseLesson::create([
        'course_id' => $course->id, 'module_id' => $module->id,
        'title' => 'Added Later', 'type' => 'Video', 'order' => 2,
    ]);

    $newModule = CourseModule::create(['course_id' => $course->id, 'title' => 'Module Two', 'order' => 2]);
    $newQuiz = Quiz::create([
        'course_id' => $course->id, 'module_id' => $newModule->id,
        'title' => 'Second Quiz', 'passing_score' => 70, 'attempts' => '3 Attempts', 'is_active' => true,
    ]);
    QuizQuestion::create([
        'quiz_id' => $newQuiz->id, 'question' => 'Q2', 'type' => 'Multiple Choice',
        'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 1,
    ]);

    return $lesson;
}

// ── Task 1 / Task 6 (1–3): completed is terminal ───────────────────────

test('a completed enrollment stays completed when course content and requirements change afterwards', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $completed = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    expect($completed->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);

    $newLesson = addUnfinishedContent($course);
    $course->update(['requires_faculty_verification' => true, 'mastery_passing_percent' => 100]);

    // Sanity: the learner has no evidence at all for the new work, so a
    // from-scratch derivation would NOT be `completed`.
    expect(DB::table('lesson_completions')->where('user_id', $student->id)->where('lesson_id', $newLesson->id)->exists())->toBeFalse();

    $result = $service->evaluate($completed->fresh());

    expect($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED);
});

test('a completed enrollment keeps its original completed_at and institutional audit fields through re-evaluation', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();
    $laterOfficer = User::factory()->create();

    $completed = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $stamp = $completed->completed_at;
    expect($stamp)->not->toBeNull();

    \Illuminate\Support\Carbon::setTestNow(now()->addDays(3));
    try {
        addUnfinishedContent($course);
        $service->evaluate($completed->fresh());
        // An identical repeat by someone else is an idempotent no-op.
        $service->recordAcademicUnitConfirmation($completed->fresh(), $laterOfficer->id, 'confirmed');
    } finally {
        \Illuminate\Support\Carbon::setTestNow();
    }

    $after = $enrollment->fresh();

    expect($after->completed_at->equalTo($stamp))->toBeTrue()
        ->and($after->academic_unit_confirmed_by)->toBe($officer->id);
});

test('normal evaluation cannot regress a completed enrollment even when its evidence is gone, and leaves credentials and stacking intact', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $badge = \App\Models\Badge::create(['name' => 'Terminal Badge', 'is_active' => true]);
    $course->badge_id = $badge->id;
    $course->certificate_enabled = true;
    $course->save();

    $framework = \App\Models\StackingFramework::create([
        'name' => 'Terminal Framework',
        'status' => 'approved',
        'is_active' => true,
        'required_count' => 1,
        'sequence_required' => false,
    ]);
    \App\Models\StackingFrameworkRequirement::create([
        'stacking_framework_id' => $framework->id,
        'course_id' => $course->id,
        'order' => 1,
        'is_required' => true,
    ]);

    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');

    $stackingStatus = fn () => \App\Models\UserStackingProgress::where('user_id', $student->id)
        ->where('stacking_framework_id', $framework->id)
        ->value('status');

    expect($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1)
        ->and($stackingStatus())->toBe('requirements_met');

    // Remove every piece of learner evidence, then evaluate repeatedly.
    DB::table('lesson_completions')->where('user_id', $student->id)->delete();
    QuizAttempt::where('user_id', $student->id)->delete();

    $service->evaluate($enrollment->fresh());
    $service->evaluate($enrollment->fresh());

    $after = $enrollment->fresh();

    expect($after->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($after->lessons_completed)->toBeTrue()
        ->and(DB::table('user_badges')->count())->toBe(1)
        ->and(DB::table('certificates')->count())->toBe(1)
        ->and($stackingStatus())->toBe('requirements_met');
});

// ── Task 2 / Task 6 (4): rejected and revoked are protected ─────────────

test('normal evaluation never moves a rejected or revoked enrollment', function () {
    foreach ([
        MicrocredentialCompletionService::STATUS_REJECTED,
        MicrocredentialCompletionService::STATUS_REVOKED,
    ] as $terminalStatus) {
        [$course, $enrollment, $student, $service] = makeReadyEnrollment();

        $enrollment->update(['completion_status' => $terminalStatus]);
        $before = $enrollment->fresh();

        // Full evidence is present, so a from-scratch derivation would
        // land on awaiting_faculty_verification — it must not.
        $service->evaluate($before);
        $service->evaluate($before->fresh());

        $after = $enrollment->fresh();

        expect($service->isTerminal($after))->toBeTrue()
            ->and($after->completion_status)->toBe($terminalStatus)
            ->and($after->academic_unit_confirmation_status)->toBe($before->academic_unit_confirmation_status)
            ->and(DB::table('user_badges')->count())->toBe(0)
            ->and(DB::table('certificates')->count())->toBe(0);
    }
});

test('a rejected enrollment cannot be pushed forward by a later decision (documented current behavior: rejected is final)', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $rejected = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'rejected');
    expect($rejected->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);

    // No reversal workflow exists yet. A later "confirmed" is refused and
    // writes nothing, so the record can never claim `confirmed` while the
    // status stays `rejected`.
    expect(fn () => $service->recordAcademicUnitConfirmation($rejected->fresh(), $officer->id, 'confirmed'))
        ->toThrow(\DomainException::class);

    $after = $enrollment->fresh();
    expect($after->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED)
        ->and($after->academic_unit_confirmation_status)->toBe('rejected');

    // An identical repeat (e.g. a double-clicked Reject) is a harmless no-op.
    $again = $service->recordAcademicUnitConfirmation($after, $officer->id, 'rejected');
    expect($again->completion_status)->toBe(MicrocredentialCompletionService::STATUS_REJECTED);
});

// ── Task 3 / Task 6 (5, 6): server-side state validation ────────────────

test('faculty verification is refused before mastery is reached and records nothing', function () {
    foreach (['in_progress', 'assessment_pending'] as $state) {
        [$course, $enrollment, $student, $service] = makeUnmasteredEnrollment(['requires_faculty_verification' => true], $state);
        $faculty = User::factory()->create();

        expect($enrollment->completion_status)->toBe(
            $state === 'in_progress'
                ? MicrocredentialCompletionService::STATUS_IN_PROGRESS
                : MicrocredentialCompletionService::STATUS_ASSESSMENT_PENDING
        );

        foreach (['verified', 'rejected'] as $decision) {
            expect(fn () => $service->recordFacultyVerification($enrollment, $faculty->id, $decision))
                ->toThrow(\DomainException::class);
        }

        $after = $enrollment->fresh();
        expect($after->faculty_verification_status)->not->toBe('verified')
            ->and($after->faculty_verification_status)->not->toBe('rejected')
            ->and($after->faculty_verified_by)->toBeNull()
            ->and($after->completion_status)->toBe($enrollment->completion_status);
    }
});

test('academic-unit confirmation is refused before mastery is reached and records nothing', function () {
    foreach (['in_progress', 'assessment_pending'] as $state) {
        [$course, $enrollment, $student, $service] = makeUnmasteredEnrollment([], $state);
        $officer = User::factory()->create();

        foreach (['confirmed', 'rejected'] as $decision) {
            expect(fn () => $service->recordAcademicUnitConfirmation($enrollment, $officer->id, $decision))
                ->toThrow(\DomainException::class);
        }

        $after = $enrollment->fresh();
        expect($after->academic_unit_confirmation_status)->not->toBe('confirmed')
            ->and($after->academic_unit_confirmation_status)->not->toBe('rejected')
            ->and($after->academic_unit_confirmed_by)->toBeNull()
            ->and($after->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
            ->and(DB::table('user_badges')->count())->toBe(0)
            ->and(DB::table('certificates')->count())->toBe(0);
    }
});

test('institutional decisions are refused while an optional mastery gate is still open (mastered state)', function () {
    // manual assessment required + nothing able to satisfy it yet =>
    // mastery is not fully reached, so no sign-off is accepted.
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['manual_assessment_required' => true]);
    $officer = User::factory()->create();

    expect($enrollment->completion_status)->toBe(MicrocredentialCompletionService::STATUS_MASTERED);

    expect(fn () => $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed'))
        ->toThrow(\DomainException::class);

    expect($enrollment->fresh()->academic_unit_confirmation_status)->not->toBe('confirmed');
});

test('a decision on a completed enrollment is refused unless it repeats the one on record', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $completed = $enrollment->fresh();

    expect(fn () => $service->recordAcademicUnitConfirmation($completed, $officer->id, 'rejected'))
        ->toThrow(\DomainException::class);

    $after = $enrollment->fresh();
    expect($after->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($after->academic_unit_confirmation_status)->toBe('confirmed');
});

test('a repeated identical decision never overwrites the original actor', function () {
    // Faculty verification is also required here, so the enrollment stays
    // in awaiting status after the academic-unit confirmation.
    [$course, $enrollment, $student, $service] = makeReadyEnrollment(['requires_faculty_verification' => true]);
    $first = User::factory()->create();
    $second = User::factory()->create();

    $service->recordAcademicUnitConfirmation($enrollment, $first->id, 'confirmed');
    $service->recordAcademicUnitConfirmation($enrollment->fresh(), $second->id, 'confirmed');

    $after = $enrollment->fresh();
    expect($after->academic_unit_confirmed_by)->toBe($first->id)
        ->and($after->completion_status)->toBe(MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION);
});

// ── Task 4 / Task 6 (7, 8): certificate idempotency + DB uniqueness ─────

test('certificate issuance returns the winning row when a concurrent issue takes the slot first', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    // Complete WITHOUT a certificate configured, then enable it, so the
    // next issue attempt goes through the create path.
    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $course->certificate_enabled = true;
    $course->save();

    $raced = false;
    \App\Models\Certificate::creating(function () use ($student, $course, &$raced) {
        if ($raced) {
            return;
        }
        $raced = true;

        // A concurrent request wins the slot between our existence check
        // and our INSERT.
        DB::table('certificates')->insert([
            'serial' => 'RACE-WINNER-0001',
            'user_id' => $student->id,
            'course_id' => $course->id,
            'title' => 'Winner',
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    try {
        $result = $service->issueCertificateIfEligible($enrollment->fresh());
    } finally {
        \App\Models\Certificate::flushEventListeners();
    }

    expect($raced)->toBeTrue()
        ->and($result)->not->toBeNull()
        ->and($result->serial)->toBe('RACE-WINNER-0001')
        ->and(DB::table('certificates')->where('user_id', $student->id)->where('course_id', $course->id)->count())->toBe(1);
});

test('certificate issuance is idempotent across repeated calls and never duplicates for one user and course', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    $course->certificate_enabled = true;
    $course->save();

    $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');
    $first = $service->issueCertificateIfEligible($enrollment->fresh());
    $second = $service->issueCertificateIfEligible($enrollment->fresh());

    expect($first->id)->toBe($second->id)
        ->and(DB::table('certificates')->count())->toBe(1);
});

test('the database rejects a second certificate for the same user and course', function () {
    $student = User::factory()->create();
    $otherStudent = User::factory()->create();

    $makeCourse = fn (string $title) => Course::create([
        'title' => $title,
        'slug' => str()->slug($title).'-'.uniqid(),
        'is_published' => true,
    ]);
    $courseA = $makeCourse('Unique Cert Course A');
    $courseB = $makeCourse('Unique Cert Course B');

    $row = fn (string $serial, int $userId, int $courseId) => [
        'serial' => $serial,
        'user_id' => $userId,
        'course_id' => $courseId,
        'title' => 'Certificate of Completion',
        'issued_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('certificates')->insert($row('UNIQ-TEST-0001', $student->id, $courseA->id));

    expect(fn () => DB::table('certificates')->insert($row('UNIQ-TEST-0002', $student->id, $courseA->id)))
        ->toThrow(\Illuminate\Database\QueryException::class);

    // Different course, or different user, is still fine.
    DB::table('certificates')->insert($row('UNIQ-TEST-0003', $student->id, $courseB->id));
    DB::table('certificates')->insert($row('UNIQ-TEST-0004', $otherStudent->id, $courseA->id));

    expect(DB::table('certificates')->count())->toBe(3);
});

// ── Task 5 / Task 6 (9): completion_status, not is_completed ────────────

test('the legacy is_completed flag and 100% progress cannot make an enrollment complete', function () {
    [$course, $enrollment, $student, $service] = makeUnmasteredEnrollment();

    $enrollment->update(['is_completed' => true, 'progress_percent' => 100]);

    $result = $service->evaluate($enrollment->fresh());

    expect($result->completion_status)->toBe(MicrocredentialCompletionService::STATUS_IN_PROGRESS)
        // is_completed is only a mirror of completion_status now.
        ->and($result->is_completed)->toBeFalse();
});

test('is_completed mirrors completion_status once an enrollment is officially completed', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $officer = User::factory()->create();

    expect($enrollment->is_completed)->toBeFalse();

    $completed = $service->recordAcademicUnitConfirmation($enrollment, $officer->id, 'confirmed');

    expect($completed->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and($completed->is_completed)->toBeTrue();
});

test('reaching 100% learning progress does not touch is_completed or completion_status', function () {
    [$course, $enrollment, $student, $service] = makeUnmasteredEnrollment([], 'assessment_pending');

    $enrollment->update([
        'progress_percent' => 0,
        'is_completed' => false,
        'progress_state' => ['module_scores' => [0 => 1]],
    ]);

    $percent = app(\App\Services\StudentProgressService::class)
        ->syncEnrollmentProgress($course->fresh(), $enrollment->fresh());

    $after = $enrollment->fresh();

    expect($percent)->toBe(100)
        ->and($after->progress_percent)->toBe(100)
        ->and($after->is_completed)->toBeFalse()
        ->and($after->completion_status)->toBe(MicrocredentialCompletionService::STATUS_ASSESSMENT_PENDING);
});

test('official completion issues the automatic certificate even when the legacy certificate flag is disabled', function () {
    [$course, $enrollment, $student, $service] = makeReadyEnrollment();
    $course->certificate_enabled = false;
    $course->certificate_mode = 'upload';
    $course->save();

    $service->recordAcademicUnitConfirmation($enrollment, User::factory()->create()->id, 'confirmed');

    expect($enrollment->fresh()->completion_status)->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
        ->and(DB::table('certificates')->where('user_id', $student->id)->where('course_id', $course->id)->count())->toBe(1)
        ->and(DB::table('certificates')->where('user_id', $student->id)->where('course_id', $course->id)->value('title'))->toBe('Certificate of Completion');
});
