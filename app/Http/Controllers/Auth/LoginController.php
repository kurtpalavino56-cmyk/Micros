<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * LoginController — signs users in with either their USERNAME or EMAIL
 * (the login Blade labels the field "Username" but accepts both), then
 * routes each role to its own dashboard:
 *
 *   admin → admin.dashboard · faculty → faculty.dashboard · student → dashboard
 */
class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route($this->dashboardRouteFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],   // username OR email
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['email']);

        // Case-insensitive match on BOTH email and username. The previous
        // exact match broke on phones: mobile keyboards auto-capitalize the
        // first letter ("admin1" arrives as "Admin1"), and SQLite's '='
        // comparison is case-sensitive, so legitimate users were rejected.
        // LOWER() on both sides works identically on SQLite and MySQL.
        $loginLower = mb_strtolower($login);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$loginLower])
            ->orWhereRaw('LOWER(username) = ?', [$loginLower])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (! $user->is_active) {
            return back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact an administrator.'])
                ->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route($this->dashboardRouteFor($user)));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('Homepage');
    }

    private function dashboardRouteFor(User $user): string
    {
        return match ($user->roleName()) {
            'admin' => 'admin.dashboard',
            'faculty' => 'faculty.dashboard',
            default => 'dashboard',
        };
    }
}
