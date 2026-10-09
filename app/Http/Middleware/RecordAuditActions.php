<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\FacultyCode;
use App\Models\LessonActivity;
use App\Models\LessonActivitySubmission;
use App\Models\Pathway;
use App\Models\Quiz;
use App\Models\StackingFramework;
use App\Models\User;
use App\Models\UserBadge;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordAuditActions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $user = Auth::user();

        if (
            ! ($user instanceof User)
            || ! $this->isSupportedRole($user)
            || $request->isMethodSafe()
            || ! is_string($routeName)
            || $routeName === 'courses.progress'
        ) {
            return $next($request);
        }

        $beforeTarget = $this->isCreateAction($routeName)
            ? null
            : $this->resolveTarget($request, $routeName, $user);
        $response = $next($request);

        if (
            $response->getStatusCode() < 200
            || $response->getStatusCode() >= 400
            || $request->session()->has('errors')
        ) {
            return $response;
        }

        try {
            $afterTarget = $this->resolveTarget($request, $routeName, $user);
            $target = $afterTarget ?? $beforeTarget;
            $operation = Str::afterLast($routeName, '.');
            $deleting = in_array($request->method(), ['DELETE'], true)
                || in_array($operation, ['delete', 'destroy', 'remove'], true)
                    && $routeName !== 'faculty.lesson-activity.destroy';
            $beforeModel = $beforeTarget['model'] ?? null;
            $afterModel = $deleting ? null : ($afterTarget['model'] ?? null);
            $changes = $this->captureChanges($routeName, $beforeModel, $afterModel, $request);
            $role = $this->roleKey($user);
            $event = $this->describeEvent($routeName, $target['type'] ?? 'system');
            $routeUri = $route?->uri() ?? $request->path();

            AuditLog::create([
                'actor_id' => $user->id,
                'actor_name' => (string) $user->name,
                'actor_role' => $role,
                'event' => Str::limit($event, 120, ''),
                'target_type' => $target['type'] ?? null,
                'target_id' => $target['id'] ?? null,
                'target_label' => isset($target['label']) ? Str::limit($target['label'], 255, '') : null,
                'changes' => $changes === [] ? null : $changes,
                'description' => '',
                'method' => $request->method(),
                'route' => Str::limit($routeUri, 255, ''),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Could not record an audit action.', [
                'route' => $routeName,
                'actor_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    private function isCreateAction(string $routeName): bool
    {
        return in_array($routeName, [
            'admin.users.store',
            'admin.announcements.store',
            'admin.pathways.store',
            'admin.categories.store',
            'admin.facultycodes.generate',
            'admin.stacking-frameworks.store',
            'faculty.create.store',
            'faculty.module.store',
            'faculty.lesson.store',
            'faculty.lesson-activity.store',
            'inbox.store',
        ], true);
    }

    private function isSupportedRole(User $user): bool
    {
        return in_array((int) $user->role_id, [User::ROLE_ADMIN, User::ROLE_FACULTY, User::ROLE_STUDENT], true);
    }

    private function roleKey(User $user): string
    {
        return match ((int) $user->role_id) {
            User::ROLE_ADMIN => 'admin',
            User::ROLE_FACULTY => 'faculty',
            default => 'student',
        };
    }

    /** @return array{model: Model, type: string, id: int, label: string}|null */
    private function resolveTarget(Request $request, string $routeName, User $actor): ?array
    {
        $parameters = $request->route()?->parameters() ?? [];
        $id = $this->parameterId($parameters['id'] ?? $parameters['courseId'] ?? null);
        $model = null;
        $type = null;

        if (in_array($routeName, ['admin.profile.update', 'faculty.profile.update', 'profile.update', 'profile.complete'], true)) {
            $model = User::find($actor->id);
            $type = 'user';
        } elseif (str_starts_with($routeName, 'admin.users.')) {
            $model = $id ? User::find($id) : $this->findCreatedUser($request);
            $type = 'user';
        } elseif (str_starts_with($routeName, 'admin.courses.') || str_starts_with($routeName, 'faculty.courses.')) {
            $model = $id ? Course::find($id) : $this->findCreatedCourse($request, $actor);
            $type = 'course';
        } elseif ($routeName === 'faculty.create.store') {
            $model = $this->findCreatedCourse($request, $actor);
            $type = 'course';
        } elseif (str_starts_with($routeName, 'admin.enrollments.')) {
            $model = Enrollment::with(['course', 'user'])->find($this->parameterId($parameters['enrollment'] ?? null));
            $type = 'enrollment';
        } elseif ($routeName === 'faculty.enrollments.verify') {
            $model = Enrollment::with(['course', 'user'])->find($this->parameterId($parameters['enrollment'] ?? null));
            $type = 'enrollment';
        } elseif ($routeName === 'faculty.lesson-activity.review') {
            $model = LessonActivitySubmission::with(['activity.lesson.course', 'student'])->find($this->parameterId($parameters['submissionId'] ?? null));
            $type = 'assignment submission';
        } elseif ($routeName === 'faculty.lesson-activity.store') {
            $lessonId = $this->parameterId($parameters['lessonId'] ?? null);
            $model = $lessonId ? LessonActivity::with('lesson.course')->where('lesson_id', $lessonId)->where('title', $request->input('title'))->latest('id')->first() : null;
            $type = 'lesson activity';
        } elseif ($routeName === 'faculty.module.store') {
            $model = $id ? CourseModule::with('course')->where('course_id', $id)->where('title', $request->input('module_title'))->latest('id')->first() : null;
            $type = 'module';
        } elseif ($routeName === 'faculty.lesson.store') {
            $model = $id ? CourseLesson::with(['course', 'module'])->where('course_id', $id)->where('title', $request->input('lesson_title'))->latest('id')->first() : null;
            $type = 'lesson';
        } elseif ($routeName === 'faculty.module.update') {
            $moduleId = $this->parameterId($parameters['moduleIndex'] ?? null);
            $model = $moduleId ? CourseModule::with('course')->where('course_id', $id)->find($moduleId) : null;
            $type = 'module';
        } elseif ($routeName === 'faculty.module.delete') {
            $key = (string) ($parameters['key'] ?? '');
            preg_match('/^mod-(\d+)$/', $key, $matches);
            $model = isset($matches[1]) ? CourseModule::with('course')->where('course_id', $id)->find((int) $matches[1]) : null;
            $type = 'module';
        } elseif ($routeName === 'faculty.lesson.update') {
            $lessonId = $this->parameterId($parameters['lessonId'] ?? null);
            $model = $lessonId ? CourseLesson::with(['course', 'module'])->where('course_id', $id)->find($lessonId) : null;
            $type = 'lesson';
        } elseif ($routeName === 'faculty.lesson.delete') {
            $key = (string) ($parameters['key'] ?? '');
            preg_match('/^les-(\d+)$/', $key, $matches);
            $model = isset($matches[1]) ? CourseLesson::with(['course', 'module'])->where('course_id', $id)->find((int) $matches[1]) : null;
            $type = 'lesson';
        } elseif (in_array($routeName, ['faculty.lesson-activity.update', 'faculty.lesson-activity.destroy'], true)) {
            $activityId = $this->parameterId($parameters['activityId'] ?? null);
            $model = $activityId ? LessonActivity::with('lesson.course')->find($activityId) : null;
            $type = 'lesson activity';
        } elseif (in_array($routeName, ['faculty.quiz.store', 'faculty.quiz.destroy'], true)) {
            $moduleId = $this->parameterId($parameters['moduleIndex'] ?? null);
            $model = $moduleId ? Quiz::with('course')->where('module_id', $moduleId)->first() : null;
            $type = 'quiz';
        } elseif ($routeName === 'faculty.lesson-quiz.store') {
            $lessonId = $this->parameterId($parameters['lessonId'] ?? null);
            $model = $lessonId ? Quiz::with('course')->where('lesson_id', $lessonId)->first() : null;
            $type = 'quiz';
        } elseif (str_starts_with($routeName, 'admin.announcements.')) {
            $model = $id ? Announcement::find($id) : Announcement::query()->where('created_by', $actor->id)->where('title', $request->input('title'))->latest('id')->first();
            $type = 'announcement';
        } elseif (str_starts_with($routeName, 'admin.complaints.')) {
            $model = $id ? Complaint::find($id) : null;
            $type = 'message thread';
        } elseif (str_starts_with($routeName, 'admin.pathways.')) {
            $model = $id ? Pathway::find($id) : Pathway::query()->where('name', $request->input('name'))->latest('id')->first();
            $type = 'pathway';
        } elseif (str_starts_with($routeName, 'admin.categories.')) {
            $model = $id ? CourseCategory::find($id) : CourseCategory::query()->where('name', $request->input('name'))->latest('id')->first();
            $type = 'category';
        } elseif (str_starts_with($routeName, 'admin.facultycodes.')) {
            $model = $id ? FacultyCode::find($id) : FacultyCode::query()->where('created_by', $actor->id)->latest('id')->first();
            $type = 'faculty code';
        } elseif (str_starts_with($routeName, 'admin.stacking-frameworks.')) {
            $model = $id ? StackingFramework::find($id) : StackingFramework::query()->where('name', $request->input('name'))->latest('id')->first();
            $type = 'stacking framework';
        } elseif ($routeName === 'admin.badges.revoke') {
            $model = $id ? UserBadge::with('badge')->find($id) : null;
            $type = 'badge';
        } elseif (str_starts_with($routeName, 'admin.certificates.')) {
            $model = $id ? Certificate::find($id) : null;
            $type = 'certificate';
        } elseif (str_starts_with($routeName, 'courses.')) {
            $model = $id ? Course::find($id) : null;
            $type = 'course';
        } elseif (str_starts_with($routeName, 'lesson-activities.')) {
            $activityId = $this->parameterId($parameters['id'] ?? null);
            $model = $activityId ? LessonActivity::with('lesson.course')->find($activityId) : null;
            $type = 'lesson activity';
        } elseif (str_starts_with($routeName, 'quiz.')) {
            $quizId = $this->parameterId($parameters['id'] ?? null);
            $model = $quizId ? Quiz::find($quizId) : null;
            $type = 'quiz';
        } elseif (str_starts_with($routeName, 'inbox.')) {
            $complaintId = $this->parameterId($parameters['id'] ?? null);
            $model = $complaintId
                ? Complaint::find($complaintId)
                : Complaint::query()->where('user_id', $actor->id)->where('subject', $request->input('subject'))->latest('id')->first();
            $type = 'message thread';
        } elseif (str_starts_with($routeName, 'faculty.inbox.')) {
            $model = Complaint::find($id);
            $type = 'message thread';
        } elseif (str_starts_with($routeName, 'faculty.') && $id !== null) {
            $model = Course::find($id);
            $type = 'course';
        }

        if (! $model instanceof Model || ! is_string($type)) {
            return null;
        }

        return [
            'model' => $model,
            'type' => $type,
            'id' => (int) $model->getKey(),
            'label' => $this->targetLabel($model, $type),
        ];
    }

    private function parameterId(mixed $parameter): ?int
    {
        if ($parameter instanceof Model) {
            return (int) $parameter->getKey();
        }

        return is_numeric($parameter) ? (int) $parameter : null;
    }

    private function targetLabel(Model $model, string $type): string
    {
        return match (true) {
            $model instanceof Course => 'Course: '.$model->title,
            $model instanceof CourseModule => 'Course: '.($model->course?->title ?? 'Unknown').'; Module: '.$model->title,
            $model instanceof CourseLesson => 'Course: '.($model->course?->title ?? 'Unknown').'; Lesson: '.$model->title,
            $model instanceof User => 'User: '.$model->name,
            $model instanceof Announcement => 'Announcement: '.$model->title,
            $model instanceof Enrollment => 'Course: '.($model->course?->title ?? 'Unknown').'; Student: '.($model->user?->name ?? 'Unknown'),
            $model instanceof LessonActivitySubmission => 'Course: '.($model->activity?->lesson?->course?->title ?? 'Unknown').'; Assignment: '.($model->activity?->title ?? 'Unknown').'; Student: '.($model->student?->name ?? 'Unknown'),
            $model instanceof LessonActivity => 'Course: '.($model->lesson?->course?->title ?? 'Unknown').'; Activity: '.$model->title,
            $model instanceof Complaint => 'Message thread: '.$model->subject,
            $model instanceof Quiz => 'Course: '.($model->course?->title ?? 'Unknown').'; Quiz: '.$model->title,
            $model instanceof Certificate => 'Certificate: '.$model->serial,
            $model instanceof UserBadge => 'Badge: '.($model->badge?->name ?? 'Unknown'),
            $model instanceof CourseCategory => 'Category: '.$model->name,
            $model instanceof Pathway => 'Pathway: '.$model->name,
            $model instanceof FacultyCode => 'Faculty code: '.$model->code,
            $model instanceof StackingFramework => 'Stacking framework: '.$model->name,
            default => Str::headline($type).' #'.$model->getKey(),
        };
    }

    private function findCreatedUser(Request $request): ?User
    {
        $email = $request->input('email');

        return is_string($email) ? User::query()->where('email', $email)->latest('id')->first() : null;
    }

    private function findCreatedCourse(Request $request, User $actor): ?Course
    {
        $title = $request->input('title');

        return is_string($title)
            ? Course::query()->where('created_by', $actor->id)->where('title', $title)->latest('id')->first()
            : null;
    }

    /** @return array<string, array{old: mixed, new: mixed}> */
    private function captureChanges(string $routeName, ?Model $before, ?Model $after, Request $request): array
    {
        $fields = match (true) {
            str_ends_with($routeName, '.profile.update'), str_ends_with($routeName, '.profile.complete') => ['name', 'email', 'phone', 'location', 'about', 'bio', 'education', 'gender', 'date_of_birth', 'school', 'skills_have', 'skills_want', 'language', 'timezone'],
            $routeName === 'faculty.lesson-activity.review' => ['score', 'feedback', 'status'],
            $routeName === 'faculty.lesson-activity.destroy' => ['is_active'],
            $routeName === 'faculty.module.update' => ['title', 'description'],
            $routeName === 'faculty.module.delete' => ['title', 'description'],
            $routeName === 'faculty.lesson.update' => ['title', 'content', 'type', 'duration'],
            $routeName === 'faculty.lesson.delete' => ['title', 'type', 'duration'],
            $routeName === 'faculty.lesson-activity.update' => ['title', 'activity_type', 'instructions', 'is_required', 'max_points', 'passing_percent', 'rubric'],
            in_array($routeName, ['faculty.quiz.store', 'faculty.lesson-quiz.store'], true) => ['title', 'description', 'passing_score', 'attempts', 'time_limit', 'instructions'],
            $routeName === 'faculty.enrollments.verify' => ['faculty_verification_status', 'faculty_verified_by'],
            $routeName === 'admin.enrollments.academic-confirm' => ['academic_unit_confirmation_status', 'academic_unit_confirmed_by'],
            str_ends_with($routeName, 'courses.update') => ['title', 'short_description', 'category', 'level', 'passing_score', 'duration', 'target_learners', 'delivery_mode'],
            str_ends_with($routeName, 'announcements.update') => ['title', 'body'],
            str_ends_with($routeName, 'announcements.pin') => ['is_pinned'],
            str_ends_with($routeName, 'certificates.revoke') => ['status', 'revoked_at', 'revocation_reason'],
            str_ends_with($routeName, 'badges.revoke') => ['status', 'revoked_at', 'revocation_reason'],
            str_ends_with($routeName, 'complaints.resolve') => ['status'],
            str_ends_with($routeName, 'categories.update') => ['name', 'description', 'is_active'],
            str_ends_with($routeName, 'pathways.update') => ['name', 'description', 'is_active'],
            str_ends_with($routeName, 'stacking-frameworks.update') => ['name', 'description', 'is_active'],
            str_ends_with($routeName, '.approve'), str_ends_with($routeName, '.deny'), str_ends_with($routeName, '.publish') => ['approval_status', 'is_published', 'denial_feedback'],
            default => [],
        };

        $changes = [];
        foreach ($fields as $field) {
            $oldValue = $before?->getAttribute($field);
            $newValue = $after?->getAttribute($field);

            if ($before === null && $after !== null) {
                $newValue = $request->input($field, $newValue);
            }

            $oldValue = $this->normaliseChangeValue($oldValue);
            $newValue = $this->normaliseChangeValue($newValue);

            if ($oldValue !== $newValue) {
                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        return $changes;
    }

    private function normaliseChangeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_scalar($value) || $value === null || is_array($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function describeEvent(string $routeName, string $targetType): string
    {
        $operation = Str::afterLast($routeName, '.');
        $verb = $routeName === 'faculty.lesson-activity.destroy' ? 'Removed' : match ($operation) {
            'store', 'generate' => 'Created',
            'enroll' => 'Enrolled',
            'select' => 'Selected',
            'update', 'toggle', 'progress', 'academic-confirm' => 'Updated',
            'start' => 'Started',
            'complete' => 'Completed',
            'verify' => 'Verified',
            'submit' => 'Submitted',
            'approve' => 'Approved',
            'deny' => 'Denied',
            'pin' => 'Pinned',
            'publish' => 'Published',
            'destroy', 'delete', 'remove' => 'Deleted',
            'revoke' => 'Revoked',
            'resolve' => 'Resolved',
            'reply' => 'Replied to',
            'review' => 'Reviewed',
            default => Str::headline($operation),
        };
        $subject = match (true) {
            str_contains($routeName, '.lessons.') => 'Lesson',
            str_starts_with($routeName, 'quiz.') => 'Quiz',
            str_starts_with($routeName, 'lesson-activities.') => 'Assignment',
            str_contains($routeName, '.lesson-activity.review') => 'Assignment',
            str_contains($routeName, '.inbox.') => 'Message',
            str_contains($routeName, '.profile.') || str_starts_with($routeName, 'profile.') => 'Profile',
            default => Str::headline($targetType),
        };

        return trim($subject.' '.$verb);
    }
}
