<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StudentActivityTracker;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function verificationNotice(): View
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        app(StudentActivityTracker::class)->start($request, $request->user());

        return redirect()->route($this->dashboardRouteFor($request->user()->role));
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($this->dashboardRouteFor($request->user()->role));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::ResetLinkSent) {
            return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('status', 'Your password has been reset.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function storeRegistration(Request $request, StudentActivityTracker $activity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['student', 'parent', 'teacher'])],
            'parent_email' => [
                Rule::excludeIf($request->input('role') !== 'student'),
                'nullable',
                'email',
                Rule::exists('users', 'email')
                    ->where('role', 'parent')
                    ->whereNotNull('email_verified_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $parent = isset($validated['parent_email'])
                ? User::where('email', $validated['parent_email'])
                    ->where('role', 'parent')
                    ->whereNotNull('email_verified_at')
                    ->firstOrFail()
                : null;
            $student = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($parent !== null) {
                $parent->students()->syncWithoutDetaching([
                    $student->id => ['linked_at' => now()],
                ]);
            }

            return $student;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $activity->start($request, $user);

        return redirect()->route($this->dashboardRouteFor($user->role));
    }

    public function store(Request $request, StudentActivityTracker $activity): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $activity->start($request, $request->user());

        return redirect()->intended(route($this->dashboardRouteFor($request->user()->role)));
    }

    public function destroy(Request $request, StudentActivityTracker $activity): RedirectResponse
    {
        $activity->end($request, $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.landing');
    }

    private function dashboardRouteFor(string $role): string
    {
        return match ($role) {
            'parent' => 'parent.home',
            'teacher' => 'teacher.home',
            'administrator' => 'administrator.reports',
            default => 'student.home',
        };
    }
}
