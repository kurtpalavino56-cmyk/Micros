<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Course;
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
                $counts = ['notifications' => 0, 'inbox' => 0];

                if (Auth::check()) {
                    $user = Auth::user();

                    $counts['notifications'] = app(UserNotificationService::class)->unreadCount($user);

                    if ($user->isFaculty() || $user->isStudent()) {
                        // Inbox = the signed-in user's admin message threads.
                        $counts['inbox'] = Complaint::where('user_id', $user->id)
                            ->get()
                            ->filter
                            ->unreadForSender()
                            ->count();
                    }
                }

                $request->attributes->set('upskill.unread', $counts);
            }

            $counts = $request->attributes->get('upskill.unread');

            $view->with('unreadNotificationCount', $counts['notifications']);
            $view->with('unreadInboxCount', $counts['inbox']);
        });
    }
}