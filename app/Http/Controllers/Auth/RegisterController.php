<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FacultyCode;
use App\Models\User;
use App\Services\UserCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * RegisterController — creates STUDENT accounts (the public sign-up form
 * in register.blade.php). A unique user_code in the YY-LN-NNNN format is
 * generated automatically and the new student is logged in right away.
 */
class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route(match (Auth::user()->roleName()) {
                'admin' => 'admin.dashboard',
                'faculty' => 'faculty.dashboard',
                default => 'dashboard',
            });
        }

        return view('auth.register');
    }

    public function store(Request $request, UserCodeService $userCodes)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $code = $userCodes->generateForRole(User::ROLE_STUDENT);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id' => User::ROLE_STUDENT,
            'student_id' => $code,
            'user_code' => $code,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to UPSKILL, '.$user->first_name.'! Your account is ready.');
    }

    /**
     * Faculty Register — same form as the student registration plus a
     * required Faculty Code Number issued by the Admin.
     */
    public function showFaculty()
    {
        if (Auth::check()) {
            return redirect()->route(match (Auth::user()->roleName()) {
                'admin' => 'admin.dashboard',
                'faculty' => 'faculty.dashboard',
                default => 'dashboard',
            });
        }

        return view('faculty.register');
    }

    /**
     * Create a FACULTY account. The submitted Faculty Code must exist and
     * be unused (green); it is stamped as used (red) on success.
     */
    public function storeFaculty(Request $request, UserCodeService $userCodes)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'faculty_code' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $code = FacultyCode::where('code', strtoupper(trim($validated['faculty_code'])))->first();

        if (! $code) {
            return back()
                ->withErrors(['faculty_code' => 'This Faculty Code Number is not valid. Please request one from the administrator.'])
                ->withInput();
        }

        if ($code->isUsed()) {
            return back()
                ->withErrors(['faculty_code' => 'This Faculty Code Number has already been used.'])
                ->withInput();
        }

        $userCode = $userCodes->generateForRole(User::ROLE_FACULTY);

        $user = DB::transaction(function () use ($validated, $userCode, $code) {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role_id' => User::ROLE_FACULTY,
                'student_id' => $userCode,
                'user_code' => $userCode,
                'is_active' => true,
            ]);

            // Mark the code as used → turns RED on the admin page.
            $code->used_by = $user->id;
            $code->used_at = now();
            $code->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('faculty.dashboard')
            ->with('success', 'Welcome to UPSKILL, Prof. '.$user->last_name.'! Your faculty account is ready.');
    }
}
