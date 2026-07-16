<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is temporarily locked. Please try again later.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact administrator.'],
            ]);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLogins();
            RateLimiter::hit($key, 60);

            $remainingAttempts = 5 - $user->failed_login_attempts;

            if ($remainingAttempts > 0) {
                throw ValidationException::withMessages([
                    'email' => ["Invalid credentials. {$remainingAttempts} attempts remaining."],
                ]);
            } else {
                throw ValidationException::withMessages([
                    'email' => ['Too many failed attempts. Your account has been locked for 30 minutes.'],
                ]);
            }
        }

        // Check email verification
        if (!$user->email_verified_at && config('auth.email_verification_required', false)) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address first.'],
            ]);
        }

        // Successful login
        RateLimiter::clear($key);
        $user->resetFailedLogins();
        $user->recordLogin();

        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
