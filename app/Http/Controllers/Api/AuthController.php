<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AuthController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Helper: Format Output User
     * - Menambahkan photo_url
     * - Menambahkan role_names
     * - Menyembunyikan field sensitive/tidak perlu
     */
    private function formatUserResponse($user)
    {
        // 1. Generate Full URL untuk Photo
        $user->photo_url = $user->photo_path
            ? url(Storage::url($user->photo_path))
            : null;

        // 2. Ambil Role Names (tanpa object roles lengkap)
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        $user->role_names = $user->roles->pluck('name');

        // 3. Sembunyikan field yang tidak diinginkan
        $user->makeHidden([
            // Sembunyikan relasi object role lengkap
            'roles',

            // Field database yang diminta untuk dihapus
            'is_active',
            'email_verified_at',
            'last_login_at',
            'last_login_ip',
            'failed_login_attempts',
            'locked_until',
            'created_at',
            'updated_at',
            'deleted_at',
            'company_id',
            'entity_function_id',

            // Opsional: sembunyikan photo_path karena sudah ada photo_url
            // 'photo_path'
        ]);

        return $user;
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => __('auth.throttle', ['seconds' => $seconds])
            ], 429);
        }

        // Load relasi company & entityFunction jika perlu ditampilkan objectnya
        $user = User::with(['company', 'entityFunction'])->where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            return response()->json(['message' => __('auth.failed')], 401);
        }

        if ($user->isLocked()) {
            return response()->json(['message' => 'Your account is temporarily locked.'], 403);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLogins();
            RateLimiter::hit($key, 60);
            $remainingAttempts = 5 - $user->failed_login_attempts;

            $msg = $remainingAttempts > 0
                ? "Invalid credentials. {$remainingAttempts} attempts remaining."
                : 'Too many failed attempts. Account locked for 30 minutes.';
            return response()->json(['message' => $msg], 401);
        }

        if (!$user->email_verified_at && config('auth.email_verification_required', false)) {
            return response()->json(['message' => 'Please verify your email address first.'], 403);
        }

        RateLimiter::clear($key);
        $user->resetFailedLogins();
        $user->recordLogin();

        $token = $user->createToken('api_login')->plainTextToken;

        // FORMAT RESPON
        $this->formatUserResponse($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    /**
     * REGISTER
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'] ?? null,
            'is_active' => false,
        ]);

        event(new Registered($user));
        $this->sendRegistrationNotifications($user);

        // FORMAT RESPON
        $this->formatUserResponse($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please wait for admin approval.',
            'data' => $user
        ], 201);
    }

    /**
     * FORGOT PASSWORD FLOW
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->first();

        if (!$user->phone) return response()->json(['message' => 'No phone number associated.'], 400);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'password_reset_' . $user->id;
        Cache::put($cacheKey, ['code' => $code, 'email' => $user->email, 'attempts' => 0], now()->addMinutes(10));

        $phone = $this->fonnteService->formatPhoneNumber($user->phone);
        $message = "*Password Reset*\nHello {$user->name},\nCode: *{$code}*\nExpires in 10 mins.";

        $this->fonnteService->sendMessage(['target' => $phone, 'message' => $message, 'countryCode' => '62']);

        return response()->json(['success' => true, 'message' => 'Code sent to WhatsApp.', 'email' => $user->email]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['email' => 'required|email', 'code' => 'required|size:6']);
        $user = User::where('email', $request->email)->first();
        if(!$user) return response()->json(['message' => 'User not found'], 404);

        $cacheKey = 'password_reset_' . $user->id;
        $cached = Cache::get($cacheKey);

        if (!$cached) return response()->json(['message' => 'Code expired.'], 400);
        if ($cached['attempts'] >= 3) { Cache::forget($cacheKey); return response()->json(['message' => 'Too many attempts.'], 429); }

        if ($cached['code'] !== $request->code) {
            $cached['attempts']++; Cache::put($cacheKey, $cached, now()->addMinutes(10));
            return response()->json(['message' => "Invalid code."], 400);
        }

        $token = Str::random(60);
        Cache::put('password_reset_token_' . $token, ['user_id' => $user->id, 'email' => $user->email], now()->addMinutes(30));
        Cache::forget($cacheKey);

        return response()->json(['success' => true, 'message' => 'Code verified.', 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $data = Cache::get('password_reset_token_' . $request->token);
        if (!$data || $data['email'] !== $request->email) return response()->json(['message' => 'Invalid token.'], 400);

        $user = User::find($data['user_id']);
        $user->update(['password' => Hash::make($request->password), 'failed_login_attempts' => 0, 'locked_until' => null]);
        Cache::forget('password_reset_token_' . $request->token);

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    /**
     * PROFILE - GET DATA
     */
    public function getProfile(Request $request)
    {
        // Load relation company & entity agar objectnya tampil (walaupun ID-nya di hide)
        $user = $request->user()->load(['company', 'entityFunction']);

        $this->formatUserResponse($user);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * PROFILE - UPDATE INFO
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            if ($request->hasFile('photo')) {
                if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                    Storage::disk('public')->delete($user->photo_path);
                }

                $file = $request->file('photo');
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile-photos', $filename, 'public');

                try {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read(storage_path('app/public/' . $path));
                    if ($image->width() > 300) $image->scale(width: 300);
                    $image->save(storage_path('app/public/' . $path), quality: 85);
                } catch (Exception $e) { Log::warning('Image optim failed'); }

                $validated['photo_path'] = $path;
            }

            $user->update($validated);

            $user->refresh();
            $user->load(['company', 'entityFunction']);
            $this->formatUserResponse($user);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user
            ]);

        } catch (Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update profile.'], 500);
        }
    }

    /**
     * PROFILE - UPDATE PASSWORD
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);
        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }

    /**
     * Notification Helper
     */
    private function sendRegistrationNotifications($user)
    {
        try {
            if ($user->phone) {
                $this->fonnteService->sendMessage([
                    'target' => $this->fonnteService->formatPhoneNumber($user->phone),
                    'message' => "*Registration Successful*\nHello {$user->name},\nStatus: *PENDING APPROVAL*",
                    'countryCode' => '62',
                ]);
            }

            $superAdmins = User::role('super-admin')->whereNotNull('phone')->get();
            foreach ($superAdmins as $admin) {
                $this->fonnteService->sendMessage([
                    'target' => $this->fonnteService->formatPhoneNumber($admin->phone),
                    'message' => "*New User*\nName: {$user->name}\nPlease activate.",
                    'countryCode' => '62',
                ]);
            }
        } catch (Exception $e) { Log::error("WA Failed: " . $e->getMessage()); }
    }
}
