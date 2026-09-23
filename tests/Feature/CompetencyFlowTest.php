<?php

use App\Models\Assessment;
use App\Models\Badge;
use App\Models\BadgeRule;
use App\Models\CompetencyCategory;
use App\Models\CompetencyLevel;
use App\Models\CompetencyUnit;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\CompetencyService;
use App\Services\MicrocredentialCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('competency flow', function () {
    it('marks a competency complete and issues a badge when an assessment passes', function () {
        $user = User::factory()->create([
            'email' => 'competency@example.com',
            'role_id' => 3,
        ]);

        $category = CompetencyCategory::create([
            'name' => 'Programming',
            'description' => 'Programming competencies',
        ]);

        $unit = CompetencyUnit::create([
            'competency_category_id' => $category->id,
            'title' => 'Laravel Basics',
            'description' => 'Core Laravel concepts',
            'order' => 1,
        ]);

        $level = CompetencyLevel::create([
            'competency_unit_id' => $unit->id,
            'title' => 'Foundation',
            'description' => 'Foundational mastery',
            'level_number' => 1,
            'points' => 100,
        ]);

        $badge = Badge::create([
            'name' => 'Laravel Starter',
            'description' => 'Completed the Laravel Basics competency',
            'is_stackable' => true,
            'badge_level' => 'Bronze',
        ]);

        BadgeRule::create([
            'badge_id' => $badge->id,
            'rule_type' => 'competency_complete',
            'rule_value' => $unit->id,
            'operator' => 'equals',
        ]);

        $assessment = Assessment::create([
            'user_id' => $user->id,
            'competency_unit_id' => $unit->id,
            'competency_level_id' => $level->id,
            'type' => 'Quiz',
            'title' => 'Laravel Basics Quiz',
            'description' => 'A simple quiz',
            'passing_score' => 70,
            'status' => 'assigned',
        ]);

        $service = app(CompetencyService::class);
        $service->completeAssessment($user, $assessment, 85);

        $progress = $user->competencyProgresses()->where('competency_unit_id', $unit->id)->first();

        expect($progress)->not->toBeNull();
        expect($progress->status)->toBe('completed');
        expect($progress->mastery_score)->toBe(85);
        expect($user->badges()->pluck('badges.id')->contains($badge->id))->toBeTrue();
    });

    it('keeps competency achievement badges separate from official course completion', function () {
        $user = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $officialBadge = Badge::create([
            'name' => 'Official Course Badge',
            'is_active' => true,
            'is_stackable' => true,
        ]);
        $course = Course::create([
            'title' => 'Incomplete Microcredential',
            'slug' => 'incomplete-microcredential-'.uniqid(),
            'created_by' => $faculty->id,
            'badge_id' => $officialBadge->id,
            'is_published' => true,
        ]);
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'progress_percent' => 100,
            'is_completed' => true,
            'completion_status' => MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION,
        ]);

        $category = CompetencyCategory::create(['name' => 'Testing', 'description' => 'x']);
        $unit = CompetencyUnit::create([
            'competency_category_id' => $category->id,
            'title' => 'Testing Basics',
            'description' => 'x',
            'order' => 1,
            'is_active' => true,
        ]);
        $level = CompetencyLevel::create([
            'competency_unit_id' => $unit->id,
            'title' => 'Foundation',
            'description' => 'x',
            'level_number' => 1,
            'points' => 100,
        ]);
        $achievementBadge = Badge::create([
            'name' => 'Testing Achievement',
            'is_active' => true,
            'is_stackable' => true,
        ]);
        BadgeRule::create([
            'badge_id' => $achievementBadge->id,
            'rule_type' => 'competency_complete',
            'rule_value' => $unit->id,
            'operator' => 'equals',
        ]);
        BadgeRule::create([
            'badge_id' => $officialBadge->id,
            'rule_type' => 'competency_complete',
            'rule_value' => $unit->id,
            'operator' => 'equals',
        ]);
        $assessment = Assessment::create([
            'user_id' => $user->id,
            'competency_unit_id' => $unit->id,
            'competency_level_id' => $level->id,
            'type' => 'Quiz',
            'title' => 'Testing Basics Quiz',
            'description' => 'x',
            'passing_score' => 70,
            'status' => 'assigned',
        ]);

        app(CompetencyService::class)->completeAssessment($user, $assessment, 85);

        expect($user->badges()->whereKey($achievementBadge->id)->exists())->toBeTrue()
            ->and($user->badges()->whereKey($officialBadge->id)->exists())->toBeFalse()
            ->and($enrollment->fresh()->completion_status)->not->toBe(MicrocredentialCompletionService::STATUS_COMPLETED)
            ->and(DB::table('certificates')->where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(0);
    });
});
