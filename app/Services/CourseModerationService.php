<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;

class CourseModerationService
{
    public function __construct(
        private CourseReadinessService $courseReadiness,
        private UserNotificationService $userNotifications,
    ) {}

    public function approve(Course $course, int $adminId): void
    {
        if ($course->approval_status !== 'pending') {
            throw new \DomainException('Only courses submitted for review can be approved.');
        }

        $missingRequirements = $this->courseReadiness->missingRequirements($course);
        if ($missingRequirements !== []) {
            throw new \DomainException('This course is incomplete. The faculty member must complete: '.implode(', ', $missingRequirements).'.');
        }

        $course->approval_status = 'approved';
        $course->is_approved = true;
        $course->is_published = true;
        $course->approved_by = $adminId;
        $course->approved_at = now();
        $course->save();

        $this->userNotifications->createForUser(
            $course->created_by,
            'Course approved: '.$course->title,
            'Your course is approved and now available to students.',
            'course',
            'course',
            $course->id,
        );
        $this->userNotifications->createForRole(
            User::ROLE_STUDENT,
            'New course available',
            '"'.$course->title.'" is approved and open for enrollment.',
            'course',
            'course',
            $course->id,
        );
    }

    public function deny(Course $course, string $feedback, int $adminId): void
    {
        $course->approval_status = 'denied';
        $course->is_approved = false;
        $course->is_published = false;
        $course->approved_by = $adminId;
        $course->approved_at = now();
        $course->denial_feedback = trim($feedback);
        $course->save();

        $this->userNotifications->createForUser(
            $course->created_by,
            'Course denied: '.$course->title,
            $course->denial_feedback,
            'course',
            'course',
            $course->id,
        );
    }

    public function togglePublish(Course $course): bool
    {
        if ($course->approval_status !== 'approved' || ! $course->is_approved) {
            throw new \DomainException('Only approved courses can be published or unpublished.');
        }

        $course->is_published = ! $course->is_published;
        $course->save();

        return (bool) $course->is_published;
    }

    public function toggleFeature(Course $course): bool
    {
        $course->is_featured = ! $course->is_featured;
        $course->save();

        return (bool) $course->is_featured;
    }

    public function delete(Course $course): string
    {
        $title = $course->title;
        $course->delete();

        return $title;
    }
}
