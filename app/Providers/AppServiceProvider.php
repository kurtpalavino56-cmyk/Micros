<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\MicrocredentialCompletionService;
use App\Services\QuizAttemptService;
use App\Services\UserNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Send mail through Brevo's HTTP API (port 443), because Railway
        // blocks outbound SMTP on most plans.
        Mail::extend('brevo', function () {
            return (new BrevoTransportFactory)->create(
                new Dsn('brevo+api', 'default', config('services.brevo.key'))
            );
        });

        View::composer('components.navbar', function ($view): void {
            $view->with(
                'publicCourseCategories',
                Course::where('is_published', true)
                    ->where('is_approved', true)
                    ->whereNotNull('category')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
                    ->all()
            );
        });

        // Authenticated pages show a notification bell and a student/faculty
        // inbox envelope, so every view needs both unread counts.
        //
        // NOTE: these are cached on the REQUEST, not in a `static` inside the
        // closure. The closure is registered once at boot and reused, so a
        // static would freeze the count for the life of the worker process —
        // invisible under artisan serve / PHP-FPM, but under Octane, Swoole or
        // RoadRunner every later visitor would see the first request's number,
        // possibly another user's.
        View::composer('*', function ($view) {
            $request = request();

            if (! $request->attributes->has('upskill.unread')) {
                $counts = [
                    'notifications' => 0,
                    'inbox' => 0,
                    'admin_course_reviews' => 0,
                    'admin_completion_reviews' => 0,
                    'faculty_learner_progress' => false,
                ];

                if (Auth::check()) {
                    $user = Auth::user();

                    $counts['notifications'] = app(UserNotificationService::class)->unreadCount($user);

                    if ($user->isAdmin()) {
                        $counts['inbox'] = Complaint::query()
                            ->where(function ($query) {
                                $query->whereNull('admin_read_at')
                                    ->orWhereRaw('COALESCE(last_reply_at, created_at) > admin_read_at');
                            })
                            ->count();

                        $counts['admin_course_reviews'] = Course::query()
                            ->where('approval_status', 'pending')
                            ->count();
                        $counts['admin_completion_reviews'] = Enrollment::query()
                            ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
                            ->where('academic_unit_confirmation_status', 'pending')
                            ->whereHas('course.reviews', fn ($query) => $query->whereColumn('course_reviews.user_id', 'enrollments.user_id'))
                            ->count();
                    }

                    if ($user->isFaculty() || $user->isStudent()) {
                        // Inbox = the signed-in user's admin message threads.
                        $counts['inbox'] = Complaint::where('user_id', $user->id)
                            ->get()
                            ->filter
                            ->unreadForSender()
                            ->count();
                    }

                    if ($user->isFaculty()) {
                        $courseIds = Course::query()
                            ->where('created_by', $user->id)
                            ->pluck('id');

                        if ($courseIds->isNotEmpty()) {
                            $hasPendingReview = Enrollment::query()
                                ->whereIn('course_id', $courseIds)
                                ->whereIn('faculty_verification_status', ['pending', 'not_required'])
                                ->where('completion_status', MicrocredentialCompletionService::STATUS_AWAITING_FACULTY_VERIFICATION)
                                ->whereHas('course', fn ($query) => $query->where('requires_faculty_verification', true))
                                ->exists();

                            $counts['faculty_learner_progress'] = $hasPendingReview;

                            if (! $hasPendingReview) {
                                $studentIds = Enrollment::query()
                                    ->whereIn('course_id', $courseIds)
                                    ->distinct()
                                    ->pluck('user_id');
                                $attemptPairs = QuizAttempt::query()
                                    ->whereIn('user_id', $studentIds)
                                    ->whereHas('quiz', fn ($query) => $query->whereIn('course_id', $courseIds)->whereHas('questions'))
                                    ->whereHas('answers')
                                    ->select(['user_id', 'quiz_id'])
                                    ->distinct()
                                    ->get();

                                if ($attemptPairs->isNotEmpty()) {
                                    $quizzes = Quiz::query()
                                        ->with('questions')
                                        ->whereIn('id', $attemptPairs->pluck('quiz_id')->unique())
                                        ->get()
                                        ->keyBy('id');
                                    $attemptService = app(QuizAttemptService::class);

                                    $counts['faculty_learner_progress'] = $attemptPairs->contains(
                                        fn (QuizAttempt $attempt): bool => isset($quizzes[$attempt->quiz_id])
                                            && $attemptService->retakeStatus($quizzes[$attempt->quiz_id], (int) $attempt->user_id)['exhausted']
                                    );
                                }
                            }
                        }
                    }
                }

                $request->attributes->set('upskill.unread', $counts);
            }

            $counts = $request->attributes->get('upskill.unread');

            $view->with('unreadNotificationCount', $counts['notifications']);
            $view->with('unreadInboxCount', $counts['inbox']);
            $view->with('adminPendingCourseReviewCount', $counts['admin_course_reviews']);
            $view->with('adminPendingCompletionReviewCount', $counts['admin_completion_reviews']);
            $view->with('facultyLearnerProgressNeedsAttention', $counts['faculty_learner_progress']);
        });
    }
}
