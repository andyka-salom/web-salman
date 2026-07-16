<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'] ?? null,
            'is_active' => false, // User tidak aktif sampai diapprove admin
        ]);

        // Fire registered event
        event(new Registered($user));

        // --- MULAI LOGIKA NOTIFIKASI WHATSAPP ---
        try {
            // 1. Kirim Notifikasi ke User Baru
            if ($user->phone) {
                $userPhone = $this->fonnteService->formatPhoneNumber($user->phone);

                $userMsg = "*Registration Successful*\n\n";
                $userMsg .= "Hello {$user->name},\n\n";
                $userMsg .= "Your account has been created successfully.\n";
                $userMsg .= "Status: *PENDING APPROVAL*\n\n";
                $userMsg .= "Please wait for admin to activate your account.";

                $this->fonnteService->sendMessage([
                    'target' => $userPhone,
                    'message' => $userMsg,
                    'countryCode' => '62',
                ]);
            }

            // 2. Kirim Notifikasi ke Semua Super Admin
            // Menggunakan scope 'role' dari Spatie Permission
            $superAdmins = User::role('super-admin')
                                ->whereNotNull('phone')
                                ->get();

            foreach ($superAdmins as $admin) {
                $adminPhone = $this->fonnteService->formatPhoneNumber($admin->phone);

                $adminMsg = "*New User Registration*\n\n";
                $adminMsg .= "A new user has registered and needs approval:\n\n";
                $adminMsg .= "Name: *{$user->name}*\n";
                $adminMsg .= "Email: {$user->email}\n";
                $adminMsg .= "Phone: {$user->phone}\n";
                $adminMsg .= "Company ID: " . ($user->company_id ?? '-') . "\n\n";
                $adminMsg .= "Please login to dashboard to activate this user.";

                $this->fonnteService->sendMessage([
                    'target' => $adminPhone,
                    'message' => $adminMsg,
                    'countryCode' => '62',
                ]);
            }

        } catch (\Exception $e) {
            // Log error jika pengiriman WA gagal, tapi jangan hentikan proses register
            Log::error("WhatsApp Notification Failed: " . $e->getMessage());
        }
        // --- SELESAI LOGIKA NOTIFIKASI ---

        return redirect()->route('login')
            ->with('success', 'Registration successful! Please wait for admin approval.');
    }
}
