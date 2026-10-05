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

                    $counts['notifications'] = Notification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count();

                    if ($user->isStudent() || $user->isFaculty()) {
                        $roleName = $user->roleName();
                        $readAt = $user->notifications_read_at;
                        $unreadAnnouncementCount = Announcement::where('is_published', true)
                            ->orderByDesc('is_pinned')
                            ->latest('published_at')
                            ->get()
                            ->filter(function (Announcement $announcement) use ($roleName): bool {
                                $audience = $announcement->audience;

                                return empty($audience)
                                    || count($audience) >= 2
                                    || in_array('public', $audience, true)
                                    || in_array($roleName, $audience, true);
                            })
                            ->take(5)
                            ->filter(function (Announcement $announcement) use ($readAt): bool {
                                $postedAt = $announcement->published_at ?? $announcement->created_at;

                                return ! $postedAt || ! $readAt || $readAt->lt($postedAt);
                            })
                            ->count();

                        $counts['notifications'] += $unreadAnnouncementCount;
                    }

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
