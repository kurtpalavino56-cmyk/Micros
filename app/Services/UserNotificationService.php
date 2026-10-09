<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonActivitySubmission;
use App\Models\Notification;
use App\Models\User;
use App\Support\RichTextSanitizer;
use Illuminate\Support\Collection;

class UserNotificationService
{
    public function createForUser(
        ?int $recipientId,
        string $title,
        string $message,
        string $type,
        ?string $entityType = null,
        ?int $entityId = null,
    ): ?Notification {
        if (! $recipientId) {
            return null;
        }

        $recipient = User::query()->where('is_active', true)->find($recipientId);

        if (! $recipient) {
            return null;
        }

        return $recipient->notifications()->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    public function createForRole(
        int $roleId,
        string $title,
        string $message,
        string $type,
        ?string $entityType = null,
        ?int $entityId = null,
    ): void {
        User::query()
            ->where('role_id', $roleId)
            ->where('is_active', true)
            ->select('id')
            ->chunkById(500, function (Collection $users) use ($title, $message, $type, $entityType, $entityId): void {
                $createdAt = now();
                $rows = $users->map(fn (User $user): array => [
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'is_read' => false,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ])->all();

                Notification::query()->insert($rows);
            });
    }

    /** @return Collection<int, object> */
    public function feed(?User $user, int $limit = 30): Collection
    {
        $storedRecords = $user
            ? Notification::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit($limit)
                ->get()
            : collect();
        $courseTargets = Course::query()
            ->whereIn('id', $storedRecords->where('entity_type', 'course')->pluck('entity_id')->filter()->all())
            ->get()
            ->keyBy('id');
        $enrollmentTargets = Enrollment::with('course')
            ->whereIn('id', $storedRecords->where('entity_type', 'enrollment')->pluck('entity_id')->filter()->all())
            ->get()
            ->keyBy('id');
        $submissionTargets = LessonActivitySubmission::with('activity.lesson.course')
            ->whereIn('id', $storedRecords->where('entity_type', 'activity_submission')->pluck('entity_id')->filter()->all())
            ->get()
            ->keyBy('id');
        $certificateTargets = Certificate::query()
            ->whereIn('id', $storedRecords->where('entity_type', 'certificate')->pluck('entity_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $stored = $user
            ? $storedRecords->map(fn (Notification $notification): object => (object) [
                'title' => $notification->title,
                'message' => $notification->message,
                'time' => $notification->created_at?->diffForHumans() ?? '',
                'type' => $notification->type,
                'unread' => ! $notification->is_read,
                'url' => $this->targetUrl(
                    $user,
                    $notification,
                    $courseTargets->get($notification->entity_id),
                    $enrollmentTargets->get($notification->entity_id),
                    $submissionTargets->get($notification->entity_id),
                    $certificateTargets->get($notification->entity_id),
                ),
                'occurred_at' => $notification->created_at,
            ])
            : collect();

        return $this->announcementFeed($user)
            ->concat($stored)
            ->sortByDesc(fn (object $notification): int => $notification->occurred_at?->getTimestamp() ?? 0)
            ->values();
    }

    public function unreadCount(User $user): int
    {
        $storedUnreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $unreadAnnouncementCount = $this->announcementFeed($user)
            ->where('unread', true)
            ->count();

        return $storedUnreadCount + $unreadAnnouncementCount;
    }

    /** @return Collection<int, object> */
    private function announcementFeed(?User $user): Collection
    {
        $role = $user?->roleName();

        return Announcement::query()
            ->where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->get()
            ->filter(function (Announcement $announcement) use ($role, $user): bool {
                $audience = $announcement->audience;

                if (empty($audience)) {
                    return true;
                }

                if (in_array('public', $audience, true)) {
                    return true;
                }

                return $user !== null && $role !== null && in_array($role, $audience, true);
            })
            ->take(5)
            ->map(function (Announcement $announcement) use ($user): object {
                $publishedAt = $announcement->published_at ?? $announcement->created_at;
                $readAt = $user?->notifications_read_at;
                $plainMessage = trim(html_entity_decode(strip_tags(preg_replace('/<\/(?:p|h[1-6]|li|blockquote)>/i', ' ', (string) $announcement->body) ?? '')));

                return (object) [
                    'entity_id' => $announcement->id,
                    'title' => $announcement->title,
                    'message' => $plainMessage,
                    'content' => RichTextSanitizer::sanitize((string) $announcement->body),
                    'time' => $publishedAt?->diffForHumans() ?? '',
                    'type' => 'announcement',
                    'unread' => $user !== null && (! $readAt || ($publishedAt && $publishedAt->gt($readAt))),
                    'url' => null,
                    'occurred_at' => $publishedAt,
                ];
            })
            ->values();
    }

    private function targetUrl(
        User $user,
        Notification $notification,
        ?Course $course,
        ?Enrollment $enrollment,
        ?LessonActivitySubmission $submission,
        ?Certificate $certificate,
    ): ?string {
        $type = $notification->type;
        $entityType = $notification->entity_type;
        $entityId = $notification->entity_id;

        if ($entityType === 'course' && $course) {
            return match ((int) $user->role_id) {
                User::ROLE_ADMIN => route('admin.courses.show', $entityId),
                User::ROLE_FACULTY => route('faculty.courses.manage', $entityId),
                default => route('courses.show', $entityId),
            };
        }

        if ($entityType === 'enrollment' && $enrollment) {
            return match ((int) $user->role_id) {
                User::ROLE_ADMIN => route('admin.enrollments.show', $enrollment->id),
                User::ROLE_FACULTY => $enrollment->completion_status === 'awaiting_faculty_verification'
                    ? route('faculty.students.course', $enrollment->course_id)
                    : route('faculty.students.course', ['id' => $enrollment->course_id, 'filter' => 'all']),
                default => route('courses.learn', $enrollment->course_id),
            };
        }

        if ($entityType === 'activity_submission' && $submission?->activity) {
            if ($user->isFaculty()) {
                return route('faculty.activities.reviews', [
                    'id' => $submission->activity->lesson->course_id,
                    'activity_id' => $submission->lesson_activity_id,
                ]).'#submission-'.$submission->id;
            }

            return route('lesson-activities.show', $submission->lesson_activity_id).'#submission-'.$submission->id;
        }

        if ($entityType === 'badge' && $entityId) {
            return match ((int) $user->role_id) {
                User::ROLE_ADMIN => route('admin.certificates', ['type' => 'badges']).'#badge-'.$entityId,
                User::ROLE_FACULTY => route('faculty.students'),
                default => route('badges.index').'#badge-'.$entityId,
            };
        }

        if ($entityType === 'certificate' && $certificate) {
            return match ((int) $user->role_id) {
                User::ROLE_ADMIN => route('admin.certificates').'#certificate-'.$certificate->id,
                User::ROLE_FACULTY => route('faculty.students'),
                default => route('certificates.view', $certificate->serial),
            };
        }

        return match ($type) {
            'course' => $user->isAdmin()
                ? route('admin.courses')
                : ($user->isFaculty() ? route('faculty.courses') : route('courses.browse')),
            'enrollment' => $user->isAdmin()
                ? route('admin.enrollments')
                : ($user->isFaculty() ? route('faculty.students') : route('courses.enrolled')),
            'assessment' => $user->isFaculty() ? route('faculty.courses') : route('courses.enrolled'),
            'badge' => $user->isAdmin()
                ? route('admin.certificates', ['type' => 'badges'])
                : ($user->isFaculty() ? route('faculty.students') : route('badges.index')),
            'certificate' => $user->isAdmin()
                ? route('admin.certificates')
                : ($user->isFaculty() ? route('faculty.students') : route('certificates.index')),
            'announcement' => $user->isAdmin()
                ? route('admin.dashboard')
                : ($user->isFaculty() ? route('faculty.dashboard') : route('dashboard')),
            default => null,
        };
    }
}
