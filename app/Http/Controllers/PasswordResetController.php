<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailJsMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * PasswordResetController — "Forgot Password?" on the login screen.
 *
 * Three steps:
 *
 *   1. /forgot-password         enter the account email
 *   2. /forgot-password/verify  enter the 6-digit code emailed to you
 *   3. /reset-password          choose the new password
 *
 * The code lives in the existing password_reset_tokens table, HASHED, with
 * a timestamp. It is never sent to the browser and never placed in a URL:
 * step 2 only succeeds if the code the user types hashes to the stored
 * value, and step 3 is reachable only after step 2 marks the session.
 *
 * That last point matters. Without it, anyone could skip verification by
 * navigating straight to /reset-password and posting an email address.
 */
class PasswordResetController extends Controller
{
    /** Session keys used to carry state between the three steps. */
    private const SESSION_EMAIL = 'pwreset.email';

    private const SESSION_VERIFIED = 'pwreset.verified';

    // ── Step 1: ask for the email ────────────────────────────────────────

    public function request()
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request, EmailJsMailer $mailer)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        // Only really send when the account exists, but respond the same
        // either way. Saying "no such account" would turn this form into a
        // way to find out which email addresses are registered.
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            $sent = $mailer->sendVerificationCode($email, $user->first_name ?: 'there', $code);

            if (! $sent) {
                $message = 'We could not send the email just now. Please try again in a moment.';

                // In debug mode, append WHY the send failed (missing .env
                // keys, EmailJS rejection, connection error) so the problem
                // can be fixed instead of guessed at. Production keeps the
                // generic message.
                if (config('app.debug') && $mailer->lastDetail() !== '') {
                    $message .= ' [debug] '.$mailer->lastDetail();
                }

                return back()
                    ->withErrors(['email' => $message])
                    ->withInput();
            }
        }

        // Remember which address is being verified so step 2 can show it.
        $request->session()->put(self::SESSION_EMAIL, $email);
        $request->session()->forget(self::SESSION_VERIFIED);

        return redirect()->route('password.verify')
            ->with('status', 'If that email address has an account, a 6-digit code is on its way.');
    }

    // ── Step 2: verify the code ──────────────────────────────────────────

    public function showVerify(Request $request)
    {
        if (! $request->session()->has(self::SESSION_EMAIL)) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-code', [
            'email' => $request->session()->get(self::SESSION_EMAIL),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->session()->get(self::SESSION_EMAIL);

        if (! $email) {
            return redirect()->route('password.request');
        }

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $row) {
            return back()->withErrors(['code' => 'That code is not valid. Please request a new one.']);
        }

        // created_at arrives from the query builder as a string, and
        // diffInMinutes is signed in Carbon 3 — parse explicitly and ask a
        // yes/no question instead.
        $expired = Carbon::parse($row->created_at)
            ->addMinutes((int) config('emailjs.code_ttl_minutes'))
            ->isPast();

        if ($expired) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->withErrors(['code' => 'That code has expired. Please request a new one.']);
        }

        if (! Hash::check($request->input('code'), $row->token)) {
            return back()->withErrors(['code' => 'That code is not correct. Please check the email and try again.']);
        }

        // Verified. Step 3 checks this flag, so the reset screen cannot be
        // reached by typing the URL.
        $request->session()->put(self::SESSION_VERIFIED, true);

        return redirect()->route('password.reset');
    }

    /** Send a fresh code for the address already being verified. */
    public function resend(Request $request, EmailJsMailer $mailer)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            $mailer->sendVerificationCode($email, $user->first_name ?: 'there', $code);
        }

        return back()->with('status', 'A new code has been sent.');
    }

    // ── Step 3: set the new password ─────────────────────────────────────

    public function showReset(Request $request)
    {
        if (! $request->session()->get(self::SESSION_VERIFIED)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get(self::SESSION_EMAIL),
        ]);
    }

    public function update(Request $request, EmailJsMailer $mailer)
    {
        if (! $request->session()->get(self::SESSION_VERIFIED)) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $email = $request->session()->get(self::SESSION_EMAIL);
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Something went wrong. Please start again.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        // The code is single-use: consume it so the same one cannot be
        // replayed, and clear the session so the reset screen closes.
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED]);

        // Confirmation email. Sent after the save, and its failure is not
        // reported to the user — the password really has changed, and
        // saying otherwise would be worse than a missing courtesy email.
        $mailer->sendResetConfirmation($email, $user->first_name ?: 'there');

        return redirect()->route('login')
            ->with('status', 'Your password has been reset. You can sign in with it now.');
    }
}
