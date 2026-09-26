<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CompetencyProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\UserBadge;
use App\Support\CertificateBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The single authoritative engine for deciding whether a student has
 * OFFICIALLY completed a microcredential.
 *
 * This class is the only place authorized to move
 * `enrollments.completion_status` toward `completed`. It is deliberately
 * separate from:
 *  - StudentProgressService, which keeps computing `progress_percent`
 *    (a pure learning-progress metric). Reaching 100% progress there
 *    NEVER by itself causes `completion_status` to become `completed`.
 *  - CourseCompletionService, whose `isReady()` currently short-circuits
 *    on `progress_percent >= 100`. That method is untouched in this step;
 *    it is not called from here and this service does not replace it yet.
 *
 * UPSKILL microcredentials are free of charge. This service never reads
 * `Payment`/`payments`, and no gate below is defined in terms of a fee,
 * tuition, or payment status. Payment is not, and must never become, a
 * completion, badge, or certificate requirement.
 *
 * Every status this service writes is set exclusively by this service or
 * by the explicit faculty/academic-unit action methods added in a later
 * Phase 3 step. A student can never set any of these fields directly —
 * there is intentionally no student-facing endpoint that accepts
 * `completion_status`, `*_status`, or any gate flag as input.
 *
 * `quiz_mastery_met` and `competency_mastery_met` are deliberately kept
 * independent: the former reflects quiz/assessment mastery only; the
 * latter reflects mastery of every competency unit linked via
 * `learning_outcomes.competency_unit_id`. A course with no such links
 * treats competency mastery as not applicable/satisfied, never as failed.
 */
class MicrocredentialCompletionService
{
    public function __construct(private StackingProgressService $stackingProgress) {}

    /**
     * Statuses `enrollments.completion_status` can hold, matching the
     * Phase 2 migration exactly.
     */
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_ASSESSMENT_PENDING = 'assessment_pending';

    public const STATUS_MASTERED = 'mastered';

    // Covers "mastery reached, awaiting required institutional sign-off" —
    // whether that sign-off is faculty verification, academic-unit
    // confirmation, or both. Which sub-step is still outstanding is always
    // readable from `faculty_verification_status` /
    // `academic_unit_confirmation_status` individually; this value only
    // means "not yet officially completed, an institutional action is
    // still required."
    public const STATUS_AWAITING_FACULTY_VERIFICATION = 'awaiting_faculty_verification';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REVOKED = 'revoked';

    /**
     * Terminal statuses. Once an enrollment holds one of these, a normal
     * evaluate() never re-derives its status:
     *  - completed: official completion is permanent. Later edits to the
     *    course (a new lesson, module, quiz question, threshold or
     *    requirement) must never silently strip a learner of a credential
     *    already earned. Only an explicit, separate revocation action
     *    (not implemented yet) may move an enrollment out of `completed`.
     *  - rejected: an explicit institutional rejection. Nothing in the app
     *    can currently move a rejected enrollment forward, and that is
     *    deliberately left as-is here — a reversal workflow would be its
     *    own designed change. tests/Unit/MicrocredentialCompletionServiceTest
     *    documents the current behavior.
     *  - revoked: reserved for the future revocation action.
     */
    public const TERMINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
        self::STATUS_REVOKED,
    ];

    // Sub-status value conventions used across manual_assessment_status,
    // attendance_status, performance_status, administrative_status,
    // faculty_verification_status, academic_unit_confirmation_status.
    private const NOT_REQUIRED = 'not_required';

    private const PENDING = 'pending';

    /**
     * Recompute every gate for this enrollment and advance
     * `completion_status` as far as the current evidence allows.
     *
     * Idempotent and side-effect-free beyond writing the enrollment's own
     * status columns: calling this repeatedly (e.g. after every lesson
     * completion or quiz attempt) never issues anything by itself — that
     * is wired in a later Phase 3 step, gated on the transition this
     * method reports.
     *
     * Terminal statuses (see TERMINAL_STATUSES) are never re-derived:
     * an enrollment that is completed, rejected or revoked is returned
     * untouched, so `completed_at` and the gate columns recorded at the
     * time of that decision stay intact.
     */
    public function evaluate(Enrollment $enrollment): Enrollment
    {
        if ($this->isTerminal($enrollment)) {
            if ($this->isOfficiallyCompleted($enrollment)) {
                // Stacking recalculation is idempotent and reads only
                // completion_status, so repeating it can never invalidate
                // existing stacking state. It is kept on this path so a
                // framework approved after the enrollment completed is
                // still picked up, exactly as before.
                $this->stackingProgress->syncForCompletedEnrollment($enrollment);
                $this->issueBadgeIfEligible($enrollment);
                $this->issueCertificateIfEligible($enrollment);
            }

            return $enrollment;
        }

        $enrollment->loadMissing(['course.modules.quiz.questions', 'course.lessons', 'course.learningOutcomes']);
        $course = $enrollment->course;

        // --- Gate 1: lessons ---------------------------------------------
        $enrollment->lessons_completed = $this->allLessonsCompleted($enrollment);

        // --- Gate 2: quizzes / quiz mastery (quiz/assessment mastery ONLY)
        [$quizzesCompleted, $quizMasteryMet] = $this->evaluateQuizGates($enrollment);
        $enrollment->quizzes_completed = $quizzesCompleted;
        $enrollment->quiz_mastery_met = $quizMasteryMet;

        // --- Gate 2b: competency mastery (independent gate) --------------
        // Mastery of every competency unit linked via
        // learning_outcomes.competency_unit_id. If the course has no such
        // links, this gate is not applicable and is treated as satisfied,
        // never as failed.
        $enrollment->competency_mastery_met = $this->allLinkedCompetenciesMastered($enrollment);

        // --- Gate 3: manual/overall assessment ----------------------------
        $enrollment->manual_assessment_status = $this->resolveOptionalGateStatus(
            enabled: (bool) $course->manual_assessment_required,
            current: $enrollment->manual_assessment_status,
        );

        // --- Gate 4: attendance / performance / administrative -----------
        // Administrative here is INSTITUTIONAL/ADMINISTRATIVE sign-off only
        // (e.g. clearance, required documentation) — never payment, tuition,
        // or a fee. UPSKILL microcredentials are free of charge; no gate in
        // this method reads `Payment`/`payments`.
        $enrollment->attendance_status = $this->resolveOptionalGateStatus(
            enabled: (bool) $course->attendance_requirement_enabled,
            current: $enrollment->attendance_status,
        );
        $enrollment->performance_status = $this->resolveOptionalGateStatus(
            enabled: (bool) $course->performance_requirement_enabled,
            current: $enrollment->performance_status,
        );
        $enrollment->administrative_status = $this->resolveOptionalGateStatus(
            enabled: (bool) $course->admin_requirement_enabled,
            current: $enrollment->administrative_status,
        );

        // --- Gate 5: faculty verification (course-configurable) -----------
        // --- Gate 6: academic-unit confirmation (ALWAYS required) --------
        // Kept strictly independent from faculty verification. Unlike
        // every other optional gate above, academic-unit confirmation is
        // an UNCONDITIONAL institutional requirement for official
        // completion — it is never skipped based on
        // `course->requires_academic_unit_confirmation`, and can never
        // resolve to `not_required`.
        $enrollment->faculty_verification_status = $this->resolveOptionalGateStatus(
            enabled: (bool) $course->requires_faculty_verification,
            current: $enrollment->faculty_verification_status,
        );
        $enrollment->academic_unit_confirmation_status = $this->resolveAcademicUnitConfirmationStatus(
            current: $enrollment->academic_unit_confirmation_status,
        );

        $enrollment->completion_status = $this->deriveCompletionStatus($enrollment);

        // `is_completed` is a legacy column. It no longer means anything
        // on its own: it is only ever a mirror of the authoritative
        // completion_status, written here and nowhere else. Progress
        // reaching 100% never sets it.
        $enrollment->is_completed = $enrollment->completion_status === self::STATUS_COMPLETED;

        if ($enrollment->completion_status === self::STATUS_COMPLETED && ! $enrollment->completed_at) {
            $enrollment->completed_at = now();
        }

        $enrollment->save();

        if ($this->isOfficiallyCompleted($enrollment)) {
            $this->stackingProgress->syncForCompletedEnrollment($enrollment);
            $this->issueBadgeIfEligible($enrollment);
            $this->issueCertificateIfEligible($enrollment);
        }

        return $enrollment;
    }

    /**
     * True only once `completion_status` is officially `completed`. Later
     * Phase 3 steps use this as the trigger to check/issue credentials —
     * it is safe to call after every evaluate(), since it reflects
     * persisted state, not a one-time transition flag.
     */
    public function isOfficiallyCompleted(Enrollment $enrollment): bool
    {
        return $enrollment->completion_status === self::STATUS_COMPLETED;
    }

    /**
     * True when the enrollment is in a status a normal evaluate() must
     * never move (completed, rejected or revoked).
     */
    public function isTerminal(Enrollment $enrollment): bool
    {
        return in_array($enrollment->completion_status, self::TERMINAL_STATUSES, true);
    }

    // ----------------------------------------------------------------
    // Institutional actions (Phase 3, faculty/academic-unit action layer).
    //
    // These are the ONLY authorized way faculty_verification_status and
    // academic_unit_confirmation_status ever move to a "satisfied" or
    // "rejected" value — evaluate() itself only ever initializes them to
    // `pending` (see resolveOptionalGateStatus() /
    // resolveAcademicUnitConfirmationStatus()), never advances them
    // further on its own. Neither method here accepts completion_status,
    // badge/certificate fields, or any other gate directly — each writes
    // only its own institutional decision, then re-runs evaluate() so the
    // full gate chain (including the unconditional academic-unit
    // requirement) decides whether that decision is enough to reach
    // `completed`. Neither method reads Payment/payments or creates an
    // AcademicCreditRecognition row.
    // ----------------------------------------------------------------

    private const VALID_FACULTY_DECISIONS = ['verified', 'rejected'];

    private const VALID_ACADEMIC_UNIT_DECISIONS = ['confirmed', 'rejected'];

    /**
     * Records a faculty verification decision for this enrollment. Only
     * meaningful when the course actually requires faculty verification
     * (`course->requires_faculty_verification`) — evaluate() ignores this
     * status entirely for a course that doesn't require it, since
     * gateSatisfied() treats `not_required` as satisfied there. Recording
     * a decision on a course that doesn't require it is harmless (it
     * simply won't affect completion) but is still persisted faithfully,
     * since the caller — not this method — is responsible for only
     * inviting faculty action where it's meaningful.
     *
     * State rule (enforced here, not only by the Blade buttons): a
     * decision is accepted only while the enrollment is genuinely awaiting
     * institutional sign-off — i.e. a fresh evaluate() puts it in
     * STATUS_AWAITING_FACULTY_VERIFICATION, meaning every learning and
     * mastery gate is satisfied. Anything earlier (in_progress,
     * assessment_pending, mastered) or already terminal (completed,
     * rejected, revoked) throws \DomainException and writes nothing.
     * The one exception is an idempotent repeat of the decision already
     * on record, which changes nothing and keeps the original
     * actor/timestamp.
     *
     * @throws \InvalidArgumentException  unknown decision value
     * @throws \DomainException           enrollment is not awaiting institutional action
     */
    public function recordFacultyVerification(Enrollment $enrollment, int $userId, string $decision): Enrollment
    {
        if (! in_array($decision, self::VALID_FACULTY_DECISIONS, true)) {
            throw new \InvalidArgumentException(
                "Invalid faculty verification decision [{$decision}]. Allowed: ".implode(', ', self::VALID_FACULTY_DECISIONS)
            );
        }

        $alreadyRecorded = $this->assertInstitutionalActionAllowed(
            $enrollment,
            'Faculty verification',
            'faculty_verification_status',
            $decision
        );

        if (! $alreadyRecorded) {
            $enrollment->faculty_verification_status = $decision;
            $enrollment->faculty_verified_by = $userId;
            $enrollment->faculty_verified_at = now();
            $enrollment->save();
        }

        return $this->evaluateAndIssueIfEligible($enrollment);
    }

    /**
     * Records an academic-unit confirmation decision for this enrollment.
     * Academic-unit confirmation is UNCONDITIONAL — evaluate() requires an
     * explicit `confirmed` here before completion_status can ever reach
     * `completed`, regardless of any course flag. This method is the
     * ONLY way that happens; evaluate() itself never writes `confirmed`
     * or `rejected` to this field.
     *
     * Subject to the same state rule as recordFacultyVerification(): only
     * an enrollment that is awaiting institutional sign-off can be
     * confirmed or rejected. A crafted request therefore cannot
     * pre-confirm an enrollment that has not reached mastery, and cannot
     * touch a completed, rejected or revoked one (an identical repeat of
     * the recorded decision is an idempotent no-op that still runs the
     * idempotent issuance step).
     *
     * @throws \InvalidArgumentException  unknown decision value
     * @throws \DomainException           enrollment is not awaiting institutional action
     */
    public function recordAcademicUnitConfirmation(Enrollment $enrollment, int $userId, string $decision = 'confirmed'): Enrollment
    {
        if (! in_array($decision, self::VALID_ACADEMIC_UNIT_DECISIONS, true)) {
            throw new \InvalidArgumentException(
                "Invalid academic-unit confirmation decision [{$decision}]. Allowed: ".implode(', ', self::VALID_ACADEMIC_UNIT_DECISIONS)
            );
        }

        $alreadyRecorded = $this->assertInstitutionalActionAllowed(
            $enrollment,
            'Academic-unit confirmation',
            'academic_unit_confirmation_status',
            $decision
        );

        if (! $alreadyRecorded) {
            $enrollment->academic_unit_confirmation_status = $decision;
            $enrollment->academic_unit_confirmed_by = $userId;
            $enrollment->academic_unit_confirmed_at = now();
            $enrollment->save();
        }

        return $this->evaluateAndIssueIfEligible($enrollment);
    }

    /**
     * Server-side precondition shared by both institutional actions.
     *
     * The enrollment is judged on fresh evidence (evaluate() is idempotent
     * and returns terminal enrollments untouched), not on a possibly stale
     * persisted status. Returns true when this exact decision is already
     * on record for a terminal or awaiting enrollment (the caller must not
     * overwrite the original actor/timestamp); returns false when a new
     * decision may be written; throws when the enrollment is not in a
     * state that accepts a decision.
     *
     * Only STATUS_AWAITING_FACULTY_VERIFICATION accepts a new decision.
     * `mastered` (an optional manual/attendance/performance/administrative
     * gate still open) is deliberately excluded: mastery is not fully
     * reached until those gates are satisfied.
     *
     * @throws \DomainException
     */
    private function assertInstitutionalActionAllowed(
        Enrollment $enrollment,
        string $label,
        string $statusColumn,
        string $decision
    ): bool {
        $enrollment = $this->evaluate($enrollment);

        $alreadyRecorded = $enrollment->{$statusColumn} === $decision;

        if ($alreadyRecorded && $this->isTerminal($enrollment)) {
            return true;
        }

        if ($enrollment->completion_status !== self::STATUS_AWAITING_FACULTY_VERIFICATION) {
            throw new \DomainException(sprintf(
                '%s cannot be recorded for an enrollment in status [%s]; it must be awaiting institutional sign-off.',
                $label,
                $enrollment->completion_status
            ));
        }

        return $alreadyRecorded;
    }

    /**
     * Shared tail for both institutional-action methods above: re-run the
     * gate chain, then — ONLY if that genuinely reached official
     * completion — issue the badge/certificate via the same idempotent
     * methods used everywhere else. Centralizing this here (rather than
     * in AdminController/FacultyController) means every current and
     * future caller of recordFacultyVerification()/
     * recordAcademicUnitConfirmation() gets correct issuance behavior
     * automatically, with no duplicated credential-creation logic
     * anywhere else. issueBadgeIfEligible()/issueCertificateIfEligible()
     * are unchanged and already idempotent: an existing active OR revoked
     * credential is always returned as-is, never duplicated or
     * resurrected.
     */
    private function evaluateAndIssueIfEligible(Enrollment $enrollment): Enrollment
    {
        $enrollment = $this->evaluate($enrollment);

        if ($this->isOfficiallyCompleted($enrollment)) {
            $this->issueBadgeIfEligible($enrollment);
            $this->issueCertificateIfEligible($enrollment);
        }

        return $enrollment->fresh();
    }

    // ----------------------------------------------------------------
    // Badge issuance (Phase 3, Step 3).
    // ----------------------------------------------------------------

    /**
     * Issue the microcredential's configured badge to this learner, but
     * ONLY if `enrollments.completion_status` has legitimately reached
     * `completed` through evaluate()'s full gate chain — never because
     * progress is 100%, and never for a course still awaiting faculty
     * verification or academic-unit confirmation.
     *
     * Fully idempotent: an existing UserBadge for this user+badge is
     * always looked up and returned as-is before anything is created.
     * completed_at is never used as the duplicate-prevention check.
     */
    public function issueBadgeIfEligible(Enrollment $enrollment): ?UserBadge
    {
        if (! $this->isOfficiallyCompleted($enrollment)) {
            return null;
        }

        $course = $enrollment->course;

        if (! $course->badge_id) {
            // No badge configured for this microcredential — nothing to
            // issue. Not an error condition.
            return null;
        }

        $existing = UserBadge::query()
            ->where('user_id', $enrollment->user_id)
            ->where('badge_id', $course->badge_id)
            ->first();

        if ($existing) {
            // Already issued — whatever its current status (active or
            // revoked), never create a second row for the same
            // user+badge. Reinstating a revoked badge is a distinct,
            // explicit action, out of scope for issuance.
            return $existing;
        }

        $snapshot = $this->buildCredentialSnapshot($course);

        try {
            return UserBadge::create([
                'user_id' => $enrollment->user_id,
                'badge_id' => $course->badge_id,
                'earned_at' => now(),
                'credential_uid' => $this->generateCredentialUid(),
                'status' => 'active',
                'competencies_snapshot' => $snapshot['competencies'],
                'learning_outcomes_snapshot' => $snapshot['learning_outcomes'],
                'pqf_level_snapshot' => $snapshot['pqf_level'],
            ]);
        } catch (QueryException $e) {
            // Lost a race to a concurrent evaluate() call for the same
            // learner+badge. The DB-level unique(['user_id','badge_id'])
            // constraint (already present since the original
            // 2026_07_09_000001 migration, untouched here) is the final
            // backstop — return the row that won instead of duplicating
            // or failing the caller.
            return UserBadge::query()
                ->where('user_id', $enrollment->user_id)
                ->where('badge_id', $course->badge_id)
                ->first();
        }
    }

    /**
     * A public, verification-safe unique identifier — deliberately never
     * the database row id. Collision chance of a v4 UUID is negligible,
     * but the loop plus the DB unique index on `credential_uid` together
     * make a collision provably impossible to persist.
     */
    private function generateCredentialUid(): string
    {
        do {
            $uid = (string) Str::uuid();
        } while (UserBadge::where('credential_uid', $uid)->exists());

        return $uid;
    }

    /**
     * Captures learning outcomes, linked competencies, and PQF level from
     * the EXACT Course row this enrollment references — i.e. the version
     * the learner actually completed. Course versioning (Phase 2) creates
     * a new `courses` row per version rather than mutating an existing
     * one, and `enrollments.course_id` is a stable reference to that
     * specific row, so reading `$enrollment->course` here — at the moment
     * completion is reached — reflects exactly what the learner
     * completed. The values are then copied into the badge's own snapshot
     * columns, so any later edit to this course row (in-place metadata
     * tweaks, or a future version) can never retroactively change an
     * already-issued badge.
     */
    private function buildCredentialSnapshot(Course $course): array
    {
        $course->loadMissing('learningOutcomes.competencyUnit');

        $learningOutcomes = $course->learningOutcomes
            ->map(fn ($outcome) => [
                'code' => $outcome->code,
                'description' => $outcome->description,
            ])
            ->values()
            ->all();

        $competencies = $course->learningOutcomes
            ->pluck('competencyUnit')
            ->filter()
            ->unique('id')
            ->map(fn ($unit) => [
                'id' => $unit->id,
                'title' => $unit->title,
            ])
            ->values()
            ->all();

        return [
            'learning_outcomes' => $learningOutcomes,
            'competencies' => $competencies,
            'pqf_level' => $course->pqf_level,
        ];
    }

    // ----------------------------------------------------------------
    // Certificate issuance (Phase 3, Step 4).
    // ----------------------------------------------------------------

    /**
     * Issue the certificate of completion for this learner, but ONLY if
     * `enrollments.completion_status` has reached `completed` through the
     * SAME gate chain used by badge issuance — including required
     * academic-unit confirmation when the course requires it. This method
     * never runs off progress_percent or any partial gate state.
     *
     * Fully idempotent, mirroring issueBadgeIfEligible(): an existing
     * Certificate for this user+course is always looked up and returned
     * as-is — including a revoked one, which is never resurrected here —
     * before anything is created. completed_at is never used as the
     * duplicate-prevention check.
     */
    public function issueCertificateIfEligible(Enrollment $enrollment): ?Certificate
    {
        if (! $this->isOfficiallyCompleted($enrollment)) {
            return null;
        }

        $course = $enrollment->course;

        $existing = Certificate::query()
            ->where('user_id', $enrollment->user_id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            // Already issued — active or revoked — never create a second
            // row for the same user+course. Reinstating a revoked
            // certificate is a distinct, explicit action, out of scope
            // for issuance.
            return $existing;
        }

        $credentialSnapshot = $this->buildCredentialSnapshot($course);
        $certificateSnapshot = $this->buildCertificateSnapshot($course, $credentialSnapshot);

        try {
            $certificate = Certificate::create([
                'serial' => CertificateBuilder::newSerial(),
                'user_id' => $enrollment->user_id,
                'course_id' => $course->id,
                'title' => $certificateSnapshot['microcredential_title'],
                // Certificates are always auto-generated by UPSKILL.
                // The database row is the authoritative issued credential;
                // PDF generation is attempted immediately but is not allowed
                // to invalidate the credential if the renderer is unavailable.
                'file_path' => null,
                'issued_at' => now(),
                'status' => 'active',
                'microcredential_title_snapshot' => $certificateSnapshot['microcredential_title'],
                'learning_outcomes_snapshot' => $certificateSnapshot['learning_outcomes'],
                'competencies_snapshot' => $certificateSnapshot['competencies'],
                'pqf_level_snapshot' => $certificateSnapshot['pqf_level'],
                'credit_equivalency_snapshot' => $certificateSnapshot['credit_equivalency'],
                'learning_hours_snapshot' => $certificateSnapshot['learning_hours'],
            ]);

            try {
                return CertificateBuilder::ensureFileGenerated($certificate);
            } catch (\Throwable $e) {
                report($e);
                return $certificate;
            }
        } catch (QueryException $e) {
            // Lost a race to a concurrent evaluate() call for the same
            // learner+course. The unique(['user_id','course_id']) index on
            // `certificates` (2026_09_21_000001) is the final backstop:
            // return the row that won instead of duplicating.
            $existing = Certificate::query()
                ->where('user_id', $enrollment->user_id)
                ->where('course_id', $course->id)
                ->first();

            // Only a lost race is swallowed. Any other database failure
            // (e.g. a serial collision or a broken foreign key) leaves no
            // certificate for this learner+course and must surface, not
            // return null as though nothing was wrong.
            if ($existing === null) {
                throw $e;
            }

            return $existing;
        }
    }

    /**
     * Certificate-specific snapshot fields, built on top of the same
     * learning-outcomes/competencies/PQF snapshot badges use, so the two
     * credential types can never drift apart on those three fields for
     * the same completion.
     *
     * `credit_equivalency_snapshot` records what the microcredential's
     * configuration said at issuance time — it is NOT evidence that
     * academic credit was actually recognized. Formal academic-credit
     * recognition is a separate institutional workflow, tracked
     * exclusively in `AcademicCreditRecognition`, which this method never
     * creates, updates, or even queries.
     */
    private function buildCertificateSnapshot(Course $course, array $credentialSnapshot): array
    {
        return [
            'microcredential_title' => $course->title,
            'learning_outcomes' => $credentialSnapshot['learning_outcomes'],
            'competencies' => $credentialSnapshot['competencies'],
            'pqf_level' => $credentialSnapshot['pqf_level'],
            'credit_equivalency' => $course->credit_equivalency,
            'learning_hours' => $course->learning_hours,
        ];
    }

    // ----------------------------------------------------------------
    // Gate computation (each reads only server-persisted data — never
    // trusts anything a student could have sent in a request).
    // ----------------------------------------------------------------

    private function allLessonsCompleted(Enrollment $enrollment): bool
    {
        $course = $enrollment->course;
        $course->loadMissing('modules.lessons');
        $lessonIds = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->pluck('id')
            ->unique();

        if ($lessonIds->isEmpty()) {
            // No lessons configured: this gate cannot block completion.
            return true;
        }

        $completedCount = DB::table('lesson_completions')
            ->where('user_id', $enrollment->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('server_verified_at')
            ->distinct('lesson_id')
            ->count('lesson_id');

        return $completedCount >= $lessonIds->count();
    }

    /**
     * @return array{0: bool, 1: bool} [quizzesCompleted, quizMasteryMet]
     */
    private function evaluateQuizGates(Enrollment $enrollment): array
    {
        $course = $enrollment->course;

        $requiredQuizzes = $course->modules
            ->map(fn ($module) => $module->quiz)
            ->filter(fn ($quiz) => $quiz && $quiz->questions->isNotEmpty());

        if ($requiredQuizzes->isEmpty()) {
            return [true, true];
        }

        $completed = true;
        $masteryMet = true;

        foreach ($requiredQuizzes as $quiz) {
            // Server-persisted attempts only. Scores/pass flags here were
            // computed and stored by QuizAttemptService/QuizGradingService
            // at submission time — never taken from the current request.
            $attempts = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $enrollment->user_id)
                ->whereNotNull('submitted_at')
                ->get();

            if ($attempts->isEmpty()) {
                $completed = false;
                $masteryMet = false;

                continue;
            }

            $bestAttempt = $attempts->sortByDesc('score')->first();
            $passedAny = $attempts->contains('passed', true);

            // Credential-level mastery threshold, if the microcredential
            // configures one, is an ADDITIONAL bar on top of the quiz's own
            // passing_score (already enforced server-side by
            // QuizAttemptService) — distinct concepts kept distinct.
            $meetsCredentialThreshold = $course->mastery_passing_percent === null
                || (int) $bestAttempt->score >= (int) $course->mastery_passing_percent;

            if (! $passedAny || ! $meetsCredentialThreshold) {
                $masteryMet = false;
            }
        }

        return [$completed, $masteryMet];
    }

    /**
     * Learning outcomes that are linked to an existing competency unit
     * must show mastery there (`CompetencyProgress.status === 'completed'`)
     * before assessment mastery counts as met. Reuses the existing
     * competency framework — records nothing new, no parallel system.
     */
    private function allLinkedCompetenciesMastered(Enrollment $enrollment): bool
    {
        $competencyUnitIds = $enrollment->course->learningOutcomes
            ->pluck('competency_unit_id')
            ->filter()
            ->unique();

        if ($competencyUnitIds->isEmpty()) {
            return true;
        }

        $masteredCount = CompetencyProgress::query()
            ->where('user_id', $enrollment->user_id)
            ->whereIn('competency_unit_id', $competencyUnitIds)
            ->where('status', 'completed')
            ->distinct('competency_unit_id')
            ->count('competency_unit_id');

        return $masteredCount >= $competencyUnitIds->count();
    }

    /**
     * Shared rule for every optional institutional gate
     * (manual_assessment_status, attendance_status, performance_status,
     * administrative_status, faculty_verification_status,
     * academic_unit_confirmation_status):
     *  - not enabled by the course config  -> not_required
     *  - enabled, never touched yet        -> pending (awaiting an explicit
     *                                          authorized action)
     *  - already has any other value (an explicit action already ran,
     *    e.g. passed/failed/verified/rejected/met) -> left untouched; this
     *    method only initializes, it never downgrades or overwrites a
     *    recorded institutional decision.
     */
    private function resolveOptionalGateStatus(bool $enabled, ?string $current): string
    {
        if (! $enabled) {
            return self::NOT_REQUIRED;
        }

        if ($current === null || $current === self::NOT_REQUIRED) {
            return self::PENDING;
        }

        return $current;
    }

    /**
     * Academic-unit confirmation is an UNCONDITIONAL institutional
     * requirement for official completion — unlike every other gate,
     * it is never gated by a course config flag and can never resolve
     * to `not_required`. `confirmed` satisfies it; `pending` blocks
     * completion; `rejected` blocks completion (and is treated as a
     * full rejection of the enrollment, exactly like any other
     * rejected gate — see deriveCompletionStatus()). A never-touched
     * enrollment (including one whose stored value is the legacy
     * `not_required` default from before this correction) starts at
     * `pending`; an explicit action elsewhere is the only way to move
     * it to `confirmed` or `rejected`. This method never downgrades or
     * overwrites an institutional decision already recorded.
     */
    private function resolveAcademicUnitConfirmationStatus(?string $current): string
    {
        if ($current === null || $current === self::NOT_REQUIRED) {
            return self::PENDING;
        }

        return $current;
    }

    private function gateSatisfied(string $status, array $satisfiedValues): bool
    {
        return in_array($status, [self::NOT_REQUIRED, ...$satisfiedValues], true);
    }

    private function deriveCompletionStatus(Enrollment $enrollment): string
    {
        if (! $enrollment->lessons_completed || ! $enrollment->quizzes_completed) {
            return self::STATUS_IN_PROGRESS;
        }

        if (! $enrollment->quiz_mastery_met || ! $enrollment->competency_mastery_met) {
            return self::STATUS_ASSESSMENT_PENDING;
        }

        // Any explicit rejection at any institutional gate stops completion
        // outright. This is only ever set by an explicit authorized action
        // in a later step — never inferred by evaluate() itself.
        $rejected = $enrollment->manual_assessment_status === 'failed'
            || $enrollment->attendance_status === 'rejected'
            || $enrollment->performance_status === 'rejected'
            || $enrollment->administrative_status === 'rejected'
            || $enrollment->faculty_verification_status === 'rejected'
            || $enrollment->academic_unit_confirmation_status === 'rejected';

        if ($rejected) {
            return self::STATUS_REJECTED;
        }

        $masteryGatesSatisfied = $this->gateSatisfied($enrollment->manual_assessment_status, ['passed'])
            && $this->gateSatisfied($enrollment->attendance_status, ['met'])
            && $this->gateSatisfied($enrollment->performance_status, ['met'])
            && $this->gateSatisfied($enrollment->administrative_status, ['met']);

        if (! $masteryGatesSatisfied) {
            return self::STATUS_MASTERED;
        }

        // Faculty verification and academic-unit confirmation are checked
        // independently. Faculty verification is course-configurable
        // (gateSatisfied() treats not_required as satisfied there).
        // Academic-unit confirmation is UNCONDITIONAL — only an explicit
        // `confirmed` satisfies it, regardless of any course flag, and it
        // is never inferred from mastery or from faculty verification.
        $facultyOk = $this->gateSatisfied($enrollment->faculty_verification_status, ['verified']);
        $academicUnitOk = $enrollment->academic_unit_confirmation_status === 'confirmed';

        if (! $facultyOk || ! $academicUnitOk) {
            return self::STATUS_AWAITING_FACULTY_VERIFICATION;
        }

        return self::STATUS_COMPLETED;
    }
}

