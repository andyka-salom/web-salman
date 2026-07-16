<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Show the password reset request form
     */
    public function showRequestForm()
    {
        return view('auth.password-request');
    }

    /**
     * Send verification code to WhatsApp
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->phone) {
            throw ValidationException::withMessages([
                'email' => ['No phone number associated with this account.'],
            ]);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        $cacheKey = 'password_reset_' . $user->id;
        Cache::put($cacheKey, [
            'code' => $code,
            'email' => $user->email,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Send via WhatsApp
        $phone = $this->fonnteService->formatPhoneNumber($user->phone);

        $message = "*Password Reset Verification*\n\n";
        $message .= "Hello {$user->name},\n\n";
        $message .= "Your verification code is: *{$code}*\n\n";
        $message .= "This code will expire in 10 minutes.\n\n";
        $message .= "If you didn't request this, please ignore this message.";

        $result = $this->fonnteService->sendMessage([
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ]);

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'email' => ['Failed to send verification code. Please try again.'],
            ]);
        }

        return redirect()->route('password.verify')
            ->with([
                'email' => $user->email,
                'success' => 'Verification code has been sent to your WhatsApp.'
            ]);
    }

    /**
     * Show verification form
     */
    public function showVerifyForm()
    {
        if (!session('email')) {
            return redirect()->route('password.request');
        }

        return view('auth.password-verify');
    }

    /**
     * Verify the code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();
        $cacheKey = 'password_reset_' . $user->id;
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            throw ValidationException::withMessages([
                'code' => ['Verification code has expired. Please request a new one.'],
            ]);
        }

        // Check attempts
        if ($cached['attempts'] >= 3) {
            Cache::forget($cacheKey);
            throw ValidationException::withMessages([
                'code' => ['Too many failed attempts. Please request a new code.'],
            ]);
        }

        // Verify code
        if ($cached['code'] !== $request->code) {
            $cached['attempts']++;
            Cache::put($cacheKey, $cached, now()->addMinutes(10));

            $remaining = 3 - $cached['attempts'];
            throw ValidationException::withMessages([
                'code' => ["Invalid code. {$remaining} attempts remaining."],
            ]);
        }

        // Generate reset token
        $token = Str::random(60);
        Cache::put('password_reset_token_' . $token, [
            'user_id' => $user->id,
            'email' => $user->email,
        ], now()->addMinutes(30));

        // Clear verification cache
        Cache::forget($cacheKey);

        return redirect()->route('password.reset', ['token' => $token])
            ->with('success', 'Code verified! Please set your new password.');
    }

    /**
     * Show reset password form
     */
    public function showResetForm($token)
    {
        $data = Cache::get('password_reset_token_' . $token);

        if (!$data) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset token.');
        }

        return view('auth.password-reset', [
            'token' => $token,
            'email' => $data['email']
        ]);
    }

    /**
     * Reset the password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $data = Cache::get('password_reset_token_' . $request->token);

        if (!$data || $data['email'] !== $request->email) {
            throw ValidationException::withMessages([
                'email' => ['Invalid or expired reset token.'],
            ]);
        }

        $user = User::find($data['user_id']);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Clear token
        Cache::forget('password_reset_token_' . $request->token);

        // Send notification
        $phone = $this->fonnteService->formatPhoneNumber($user->phone);
        $message = "*Password Changed*\n\n";
        $message .= "Hello {$user->name},\n\n";
        $message .= "Your password has been changed successfully.\n\n";
        $message .= "If you didn't make this change, please contact support immediately.";

        $this->fonnteService->sendMessage([
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ]);

        return redirect()->route('login')
            ->with('success', 'Password has been reset successfully! Please login with your new password.');
    }

    /**
     * Resend verification code
     */
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return $this->sendVerificationCode($request);
    }
}
