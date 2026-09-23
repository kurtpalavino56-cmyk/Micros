<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Every page shows a notification bell and (for students) an inbox
        // envelope, so every view needs both unread counts.
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

                    $counts['notifications'] = Notification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count();

                    if ($user->isFaculty()) {
                        // Faculty inbox = announcements addressed to faculty.
                        // Read state is a watermark, so anything posted after
                        // announcements_read_at counts as unread.
                        $watermark = $user->announcements_read_at;

                        $counts['inbox'] = Announcement::where('is_published', true)
                            ->get()
                            ->filter(fn (Announcement $a) => $a->visibleTo('faculty'))
                            ->filter(function (Announcement $a) use ($watermark) {
                                $postedAt = $a->published_at ?? $a->created_at;

                                return $postedAt && (! $watermark || $watermark->lt($postedAt));
                            })
                            ->count();
                    } elseif ($user->isStudent()) {
                        // Student inbox = their own complaint threads.
                        // Announcements live on the notifications page.
                        $counts['inbox'] = Complaint::where('user_id', $user->id)
                            ->get()
                            ->filter
                            ->unreadForStudent()
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
