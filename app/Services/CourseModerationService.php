<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Notification;

class CourseModerationService
{
    public function __construct(private CourseReadinessService $courseReadiness) {}

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

        if ($course->created_by) {
            Notification::create([
                'user_id' => $course->created_by,
                'title' => 'Course denied: '.$course->title,
                'message' => $course->denial_feedback,
                'type' => 'course',
                'is_read' => false,
            ]);
        }
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
