<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\StackingFramework;
use App\Models\StackingFrameworkRequirement;
use App\Models\UserStackingProgress;
use Illuminate\Support\Collection;

class StackingProgressService
{
    private const STATUS_COMPLETED = 'completed';

    private const STATUS_IN_PROGRESS = 'in_progress';

    private const STATUS_REQUIREMENTS_MET = 'requirements_met';

    /**
     * Recalculate every active framework that contains an officially
     * completed course for this enrollment's student.
     */
    public function syncForCompletedEnrollment(Enrollment $enrollment): void
    {
        if ($enrollment->completion_status !== self::STATUS_COMPLETED) {
            return;
        }

        $frameworks = StackingFramework::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereHas('requirements', fn ($query) => $query->where('course_id', $enrollment->course_id))
            ->with('requirements.course')
            ->get();

        foreach ($frameworks as $framework) {
            $this->syncFrameworkForUser($framework, (int) $enrollment->user_id);
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    public function progressForUser(int $userId): Collection
    {
        $courseIds = Enrollment::query()
            ->where('user_id', $userId)
            ->pluck('course_id');

        if ($courseIds->isEmpty()) {
            return collect();
        }

        return StackingFramework::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereHas('requirements', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->with(['requirements.course'])
            ->orderBy('name')
            ->get()
            ->map(fn (StackingFramework $framework): array => $this->progressForFramework($framework, $userId))
            ->values();
    }

    public function syncFrameworkForUser(StackingFramework $framework, int $userId): ?UserStackingProgress
    {
        if ($framework->status !== 'approved' || ! $framework->is_active) {
            return null;
        }

        $framework->loadMissing('requirements.course');
        $data = $this->calculate($framework, $userId);
        $existing = UserStackingProgress::query()
            ->where('user_id', $userId)
            ->where('stacking_framework_id', $framework->id)
            ->first();

        return UserStackingProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'stacking_framework_id' => $framework->id,
            ],
            [
                'status' => $data['status'],
                'requirements_met_at' => $data['status'] === self::STATUS_REQUIREMENTS_MET
                    ? ($existing?->requirements_met_at ?? now())
                    : null,
            ]
        );
    }

    /** @return array<string, mixed> */
    private function progressForFramework(StackingFramework $framework, int $userId): array
    {
        $data = $this->calculate($framework, $userId);
        $progress = $this->syncFrameworkForUser($framework, $userId);

        return $data + [
            'framework' => $framework,
            'progress_record' => $progress,
        ];
    }

    /** @return array<string, mixed> */
    private function calculate(StackingFramework $framework, int $userId): array
    {
        $requirements = $framework->requirements->sortBy('order')->values();
        $courseIds = $requirements->pluck('course_id');
        $enrollments = Enrollment::query()
            ->where('user_id', $userId)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->keyBy('course_id');

        $requirementDetails = $requirements->map(function (StackingFrameworkRequirement $requirement) use ($enrollments): array {
            $enrollment = $enrollments->get($requirement->course_id);
            $officiallyCompleted = $enrollment?->completion_status === self::STATUS_COMPLETED;

            return [
                'requirement' => $requirement,
                'course' => $requirement->course,
                'is_required' => (bool) $requirement->is_required,
                'is_completed' => $officiallyCompleted,
                'completed_at' => $officiallyCompleted ? $enrollment->completed_at : null,
            ];
        });

        $requiredDetails = $requirementDetails->where('is_required', true)->values();
        $completedRequiredCount = $requiredDetails->where('is_completed', true)->count();
        $totalRequiredCount = $requiredDetails->count();
        $targetRequiredCount = $framework->completion_mode === 'minimum_required'
            ? min((int) $framework->required_count, $totalRequiredCount)
            : $totalRequiredCount;
        $sequenceSatisfied = ! $framework->sequence_required
            || $this->sequenceSatisfied($requiredDetails, $targetRequiredCount);
        $status = $completedRequiredCount >= $targetRequiredCount && $sequenceSatisfied
            ? self::STATUS_REQUIREMENTS_MET
            : self::STATUS_IN_PROGRESS;
        $percentage = $targetRequiredCount === 0
            ? 0
            : (int) round(min($completedRequiredCount, $targetRequiredCount) / $targetRequiredCount * 100);
        if ($status === self::STATUS_REQUIREMENTS_MET) {
            $percentage = 100;
        }

        return [
            'completed_required_count' => $completedRequiredCount,
            'total_required_count' => $totalRequiredCount,
            'target_required_count' => $targetRequiredCount,
            'completion_mode' => $framework->completion_mode ?: 'all_required',
            'remaining_required_count' => max(0, $targetRequiredCount - $completedRequiredCount),
            'completion_percentage' => $percentage,
            'status' => $status,
            'sequence_satisfied' => $sequenceSatisfied,
            'requirements' => $requirementDetails,
        ];
    }

    /** @param Collection<int, array<string, mixed>> $requiredDetails */
    private function sequenceSatisfied(Collection $requiredDetails, int $targetRequiredCount): bool
    {
        if ($targetRequiredCount === 0) {
            return true;
        }

        $sequence = $requiredDetails->take($targetRequiredCount);
        if ($sequence->contains(fn (array $detail): bool => ! $detail['is_completed'])) {
            return false;
        }

        $completionTimes = $sequence->map(fn (array $detail) => $detail['completed_at']?->getTimestamp());
        if ($completionTimes->contains(null)) {
            return false;
        }

        return $completionTimes->values()->toArray() === $completionTimes->sort()->values()->toArray();
    }
}
