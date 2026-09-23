<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Badge;
use App\Models\BadgeRule;
use App\Models\CompetencyProgress;
use App\Models\User;

class CompetencyService
{
    /**
     * Records competency-level achievement only. Official course
     * microcredential badges are issued exclusively by
     * MicrocredentialCompletionService after enrollment completion.
     */
    public function completeAssessment(User $user, Assessment $assessment, int $score): CompetencyProgress
    {
        $assessment->update([
            'score' => $score,
            'status' => $score >= $assessment->passing_score ? 'passed' : 'failed',
        ]);

        if ($score < $assessment->passing_score) {
            return $this->upsertProgress($user, $assessment, 'pending', $score);
        }

        $progress = $this->upsertProgress($user, $assessment, 'completed', $score);
        $this->issueBadgeForCompletedCompetency($user, $assessment->unit()->first());

        return $progress;
    }

    protected function upsertProgress(User $user, Assessment $assessment, string $status, int $score): CompetencyProgress
    {
        return CompetencyProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'competency_unit_id' => $assessment->competency_unit_id,
            ],
            [
                'assessment_id' => $assessment->id,
                'status' => $status,
                'mastery_score' => $score,
                'completed_at' => $status === 'completed' ? now() : null,
            ]
        );
    }

    protected function issueBadgeForCompletedCompetency(User $user, $competencyUnit): void
    {
        $candidateBadges = Badge::query()
            ->where('is_active', true)
            ->where('is_stackable', true)
            ->whereDoesntHave('courses')
            ->get();

        foreach ($candidateBadges as $badge) {
            if (! $this->badgeMatchesCompetency($badge, $competencyUnit)) {
                continue;
            }

            if ($this->hasPrerequisiteBadge($user, $badge)) {
                $user->badges()->syncWithoutDetaching([$badge->id]);
            }
        }
    }

    protected function badgeMatchesCompetency(Badge $badge, $competencyUnit): bool
    {
        return BadgeRule::query()
            ->where('badge_id', $badge->id)
            ->where('rule_type', 'competency_complete')
            ->where('rule_value', (string) $competencyUnit->id)
            ->exists();
    }

    protected function hasPrerequisiteBadge(User $user, Badge $badge): bool
    {
        if (! $badge->prerequisite_badge_id) {
            return true;
        }

        return $user->badges()->where('badges.id', $badge->prerequisite_badge_id)->exists();
    }
}
