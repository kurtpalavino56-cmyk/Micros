<?php

namespace App\Support;

/**
 * Presentation-only helper (Phase 3 FIX 2). Does NOT change
 * `enrollments.completion_status` or the completion state machine in any
 * way — it only derives a clearer label from the already-stored gate
 * statuses, since a single `awaiting_faculty_verification` value is used
 * internally for "awaiting some institutional sign-off" (faculty and/or
 * academic-unit), which reads misleadingly when faculty verification
 * specifically is `not_required`. Used identically by the faculty
 * enrollment display and the admin Enrollment Review section so both
 * surfaces always agree.
 */
class CompletionStatusPresenter
{
    public static function institutionalLabel(
        string $completionStatus,
        string $facultyVerificationStatus,
        string $academicUnitConfirmationStatus
    ): string {
        // Rule 5 — either institutional gate rejected.
        if ($facultyVerificationStatus === 'rejected' || $academicUnitConfirmationStatus === 'rejected') {
            return 'Rejected';
        }

        // Rule 4 — officially completed.
        if ($completionStatus === 'completed' && $academicUnitConfirmationStatus === 'confirmed') {
            return 'Completed';
        }

        // Rule 1 — both gates still pending.
        if ($facultyVerificationStatus === 'pending' && $academicUnitConfirmationStatus === 'pending') {
            return 'Awaiting Institutional Review';
        }

        // Rule 2 — only faculty verification pending (not required by
        // academic-unit confirmation at this point).
        if ($facultyVerificationStatus === 'pending' && $academicUnitConfirmationStatus === 'not_required') {
            return 'Awaiting Faculty Verification';
        }

        // Rule 3 — faculty side satisfied/not applicable, academic-unit
        // confirmation is the actual remaining blocker (the exact case
        // that looked confusing as "Awaiting Faculty Verification"
        // before this fix).
        if (in_array($facultyVerificationStatus, ['verified', 'not_required'], true)
            && $academicUnitConfirmationStatus === 'pending') {
            return 'Awaiting Academic-Unit Confirmation';
        }

        // Fallback for states before institutional review even begins
        // (in_progress, assessment_pending, mastered) — a plain,
        // human-readable version of the stored value.
        return ucfirst(str_replace('_', ' ', $completionStatus));
    }
}