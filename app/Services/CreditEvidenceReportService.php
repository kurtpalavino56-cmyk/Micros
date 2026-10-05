<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\StackingFramework;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStackingProgress;

class CreditEvidenceReportService
{
    /** @return array<string, mixed> */
    public function build(User $student, StackingFramework $framework): array
    {
        $framework->loadMissing(['requirements.course.learningOutcomes']);
        $requirements = $framework->requirements->sortBy('order')->map(function ($requirement) use ($student): array {
            $course = $requirement->course;
            $enrollment = $course?->enrollments()->where('user_id', $student->id)->first();
            $certificate = $course
                ? Certificate::query()->where('user_id', $student->id)->where('course_id', $course->id)->latest('issued_at')->first()
                : null;
            $badge = $course && $course->badge_id
                ? UserBadge::query()->with('badge')->where('user_id', $student->id)->where('badge_id', $course->badge_id)->latest('earned_at')->first()
                : null;

            return [
                'requirement' => $requirement,
                'course' => $course,
                'enrollment' => $enrollment,
                'is_completed' => $enrollment?->completion_status === 'completed',
                'certificate' => $certificate,
                'badge' => $badge,
                'outcomes' => $course?->learningOutcomes ?? collect(),
            ];
        })->values();
        $progress = UserStackingProgress::query()
            ->where('user_id', $student->id)
            ->where('stacking_framework_id', $framework->id)
            ->first();

        return [
            'student' => $student,
            'framework' => $framework,
            'requirements' => $requirements,
            'progress' => $progress,
            'generated_at' => now(),
        ];
    }
}
