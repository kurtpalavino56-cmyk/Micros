<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleBasedAccess — guards a route group so only one role may enter.
 *
 * Usage (FQCN + parameter, no alias registration needed):
 *   Route::middleware(RoleBasedAccess::class.':admin')->group(...);
 *   Route::middleware(RoleBasedAccess::class.':faculty')->group(...);
 *   Route::middleware(RoleBasedAccess::class.':student')->group(...);
 *
 * Guests are sent to the login page; a logged-in user who belongs to a
 * different role is bounced back to their own dashboard (never a 403
 * dead-end), which keeps every sidebar/topbar link usable.
 */
class RoleBasedAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if ($user->roleName() !== strtolower($role)) {
            return redirect()->route(match ($user->roleName()) {
                'admin' => 'admin.dashboard',
                'faculty' => 'faculty.dashboard',
                default => 'dashboard',
            });
        }

        return $next($request);
    }
}
