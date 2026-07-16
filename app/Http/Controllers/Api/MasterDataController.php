<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

// Import semua model yang dibutuhkan
use App\Models\Area;
use App\Models\Pelanggaran;
use App\Models\SecurityEventCategory;
use App\Models\UnsafeAct;
use App\Models\UnsafeCondition;
use App\Models\User;

class MasterDataController extends Controller
{
    /**
     * Mengambil semua data Area yang aktif.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreas()
    {
        $data = Area::active()
            ->select('id', 'code', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($data);
    }

    /**
     * Mengambil semua data Pelanggaran yang aktif.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPelanggaran()
    {
        $data = Pelanggaran::active()
            ->select('id', 'code', 'name', 'severity')
            ->orderBy('name')
            ->get();

        return response()->json($data);
    }

    /**
     * Mengambil semua data Kategori Insiden Keamanan yang aktif.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSecurityEventCategories()
    {
        $data = SecurityEventCategory::active()
            ->select('id', 'code', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($data);
    }

    /**
     * Mengambil semua data Unsafe Act yang aktif.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnsafeActs()
    {
        $data = UnsafeAct::active()
            ->select('id', 'code', 'description')
            ->orderBy('description')
            ->get();

        return response()->json($data);
    }

    /**
     * Mengambil semua data Unsafe Condition yang aktif.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnsafeConditions()
    {
        $data = UnsafeCondition::active()
            ->select('id', 'code', 'description')
            ->orderBy('description')
            ->get();

        return response()->json($data);
    }

    /**
     * Mengambil daftar Supervisor (User dengan entitas satu tingkat di atas user saat ini).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function getLineSupervisors(Request $request)
{
    $currentUser = Auth::user();
    $cacheDuration = 3600; // Cache selama 1 jam

    // Pastikan user terautentikasi memiliki relasi yang dibutuhkan
    if (!$currentUser) {
        return response()->json(['message' => 'Unauthorized.'], 401);
    }

    $lineSupervisors = Cache::remember(
        'dropdown:direct_supervisors_for_user:' . $currentUser->id,
        $cacheDuration,
        function () use ($currentUser) {
            // 1. Pastikan relasi entityFunction dimuat dan user memiliki parent
            $user = User::with('entityFunction')->find($currentUser->id);

            if (!$user || !$user->entityFunction || !$user->entityFunction->parent_id) {
                return collect(); // Kembalikan koleksi kosong jika struktur tidak terdefinisi
            }

            $parentEntityId = $user->entityFunction->parent_id;

            // --- LOGIKA BARU DIMULAI DI SINI ---

            // 2. Temukan EntityFunction dari atasan langsung (parent)
            // Ini diperlukan untuk mengetahui level hierarki si atasan.
            $parentEntityFunction = EntityFunction::find($parentEntityId);

            if (!$parentEntityFunction) {
                return collect();
            }

            // Ambil nilai level dari atasan langsung (Misal: 'Lvl 2')
            // Kolom 'level' diasumsikan ada di tabel entity_functions.
            $targetLevel = $parentEntityFunction->level;

            // 3. Cari semua Entity Function IDs yang berada pada level yang sama dengan atasan langsung.
            // Ini akan mengambil EF-3LAKN (BM SPV) dan EF-0DAF1 (Land, SM) jika levelnya sama.
            $entityFunctionIds = EntityFunction::where('level', $targetLevel)
                                               ->pluck('id');

            // Jika tidak ada fungsi di level tersebut (misalnya, jika user berada di level terendah)
            if ($entityFunctionIds->isEmpty()) {
                return collect();
            }

            // 4. Ambil user yang memiliki entity_function_id yang termasuk dalam daftar ID fungsi di level yang ditargetkan.
            return User::active() // Menggunakan scope active dari User model
                ->whereIn('entity_function_id', $entityFunctionIds)
                ->orderBy('name')
                ->get(['id', 'name']);
        }
    );

    // FALLBACK (OPSIONAL):
    // Jika hasil hierarki kosong (misal: data struktur organisasi belum lengkap),
    // kembalikan semua user aktif agar API tetap bisa digunakan.
    if ($lineSupervisors->isEmpty()) {
        $lineSupervisors = Cache::remember(
            'dropdown:users_active_fallback',
            $cacheDuration,
            fn() => User::active()->orderBy('name')->get(['id', 'name'])
        );
    }

    return response()->json([
        'success' => true,
        'data' => $lineSupervisors,
        'count' => $lineSupervisors->count()
    ]);
}
}
