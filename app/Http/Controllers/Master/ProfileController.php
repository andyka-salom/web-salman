<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdatePasswordRequest;
use App\Http\Requests\Master\UpdateProfileRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    use AuthorizesRequests;
    /**
     * Show profile page.
     */

    public function index()
    {
        $this->authorize('update profile');
        $user = Auth::user()->load(['company', 'entityFunction', 'roles']);

        return view('master.profile', compact('user'));
    }

    /**
     * Update profile information via AJAX.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        try {
            $data = $request->validated();

            // Handle Photo Upload with Optimization
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                    Storage::disk('public')->delete($user->photo_path);
                }

                $file = $request->file('photo');
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $path = 'profile-photos/' . $filename;

                // Optimize Image
                try {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file);
                    if ($image->width() > 300) {
                        $image->scale(width: 300);
                    }
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 85));
                } catch (Exception $imgEx) {
                    Log::warning('Profile image optimization failed: ' . $imgEx->getMessage());
                    // Fallback: keep the original upload untouched
                    $path = $file->storeAs('profile-photos', $filename, 'public');
                }

                $data['photo_path'] = $path;
            }

            $user->update($data);

            Log::info("User profile updated.", ['id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'photo_url' => $user->photo_path ? Storage::disk('public')->url($user->photo_path) : null
            ]);

        } catch (Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update profile.'], 500);
        }
    }

    /**
     * Update password via AJAX.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            Log::info("User password updated.", ['id' => $user->id]);

            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);

        } catch (Exception $e) {
            Log::error('Password update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update password.'], 500);
        }
    }
}
