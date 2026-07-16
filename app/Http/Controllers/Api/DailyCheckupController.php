<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrewMember;
use App\Models\HealthCheck;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Tetap mempertahankan trait ini
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyHealthCheckExport; // Sesuaikan dengan referensi target

class DailyCheckupController extends Controller
{
    use AuthorizesRequests;

    /**
     * Helper function untuk mendapatkan kapal yang dikoordinatori oleh user.
     * Mengembalikan 403 jika user tidak memiliki akses ke kapal.
     */
    private function getCoordinatedVessel()
    {
        // Asumsi relasi user ke vessel via method vessels() atau coordinatedVessels()
        // Mengambil kapal pertama yang berelasi dengan user saat ini
        $vessel = Auth::user()->vessels()->first();

        if (!$vessel) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke kapal manapun.');
        }
        return $vessel;
    }

    /**
     * Menampilkan dashboard (default) atau riwayat filter (jika ada parameter tanggal).
     */
    public function index(Request $request): JsonResponse
    {
        $vessel = $this->getCoordinatedVessel();

        // ----------------------------------------------------
        // LOGIKA MODE FILTER RIWAYAT (Jika ada parameter tanggal)
        // ----------------------------------------------------
        if ($request->has(['start_date', 'end_date'])) {
            try {
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                ]);

                $startDate = $request->start_date;
                $endDate = $request->end_date;

                $healthChecks = HealthCheck::where('vessel_id', $vessel->id)
                    ->whereDate('check_date', '>=', $startDate)
                    ->whereDate('check_date', '<=', $endDate)
                    ->with(['crewMember:id,name,position', 'reporter:id,name'])
                    ->orderBy('check_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $groupedByDate = $healthChecks->groupBy(function($item) {
                    return Carbon::parse($item->check_date)->format('Y-m-d');
                });

                return response()->json([
                    'success' => true,
                    'mode' => 'history_filter',
                    'vessel_info' => ['name' => $vessel->name],
                    'date_range' => ['start' => $startDate, 'end' => $endDate],
                    'data' => $groupedByDate
                ], Response::HTTP_OK);

            } catch (ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi filter gagal.',
                    'errors' => $e->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // ----------------------------------------------------
        // LOGIKA MODE DASHBOARD (Default jika tanpa parameter tanggal)
        // ----------------------------------------------------

        // Ambil semua kru aktif dari kapal ini
        $crewMembers = $vessel->crewMembers()
            ->where('is_active', true)
            ->select('id', 'name', 'position')
            ->orderBy('name')
            ->get();

        // Ambil data check-up HARI INI saja
        $todayChecks = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', today())
            ->get()
            ->keyBy('crew_member_id');

        // Statistik untuk hari ini menggunakan Raw Query agar efisien
        $todayStats = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', today())
            ->selectRaw('
                COUNT(*) as total_checks,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "reviewed" THEN 1 ELSE 0 END) as reviewed,
                SUM(CASE WHEN status = "validated" THEN 1 ELSE 0 END) as validated
            ')
            ->first();

        // Riwayat pemeriksaan (hanya 3 hari terakhir agar ringan)
        $recentChecks = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', '>=', today()->subDays(3))
            ->with(['crewMember:id,name,position'])
            ->latest('check_date')
            ->latest('checked_at')
            ->get()
            ->groupBy(fn($item) => $item->check_date->format('Y-m-d'));

        return response()->json([
            'mode' => 'dashboard',
            'vessel_info' => [
                'id' => $vessel->id,
                'name' => $vessel->name,
            ],
            'daily_status' => [
                'total_crew' => $crewMembers->count(),
                'checks_done' => $todayChecks->count(),
                'checks_pending' => $crewMembers->count() - $todayChecks->count(),
                'stats' => $todayStats,
                'today_checks' => $todayChecks->map(function ($check) {
                    return [
                        'crew_member_id' => $check->crew_member_id,
                        'health_check_id' => $check->id,
                        'status' => $check->status,
                    ];
                }),
                'active_crew_list' => $crewMembers,
            ],
            'recent_history' => $recentChecks,
        ], Response::HTTP_OK);
    }

    /**
     * Mengambil daftar kru aktif beserta data DCU mereka hari ini.
     * Digunakan untuk form input massal di Frontend.
     */
    public function getCrewWithTodayChecks(): JsonResponse
    {
        $vessel = $this->getCoordinatedVessel();
        $today = today();

        $crewMembers = $vessel->crewMembers()
            ->where('is_active', true)
            ->select('id', 'name', 'position')
            ->orderBy('name')
            ->get();

        $todayChecks = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', $today)
            ->get()
            ->keyBy('crew_member_id');

        // Struktur default jika data belum ada
        $defaultCheckAttributes = [
            'id' => null,
            'temperature' => null,
            'pulse_rate' => null,
            'blood_pressure' => null,
            'respiratory_rate' => null,
            'blood_sugar_level' => null,
            'oxygen_saturation' => null,
            'illness_complaints' => '',
            'medications_consumed' => '',
            'fatigue_level' => null,
            'napza_test_result' => 'not_tested',
            'romberg_test_result' => 'not_tested',
            'status' => 'pending',
            'remarks' => '',
            'checked_at' => null,
        ];

        $dataForForm = $crewMembers->map(function ($crew) use ($todayChecks, $defaultCheckAttributes) {
            $check = $todayChecks->get($crew->id);
            $checkData = $check ? $check->toArray() : $defaultCheckAttributes;

            return [
                'crew_member_id' => $crew->id,
                'name' => $crew->name,
                'position' => $crew->position,
                'dcu_data' => $checkData,
                'is_checked' => (bool) $check,
            ];
        });

        return response()->json([
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'check_date' => $today->toDateString(),
            'crew_list' => $dataForForm,
        ], Response::HTTP_OK);
    }

    /**
     * Menyimpan data DCU massal untuk semua kru (Create or Update).
     */
    public function storeBulk(Request $request): JsonResponse
    {
        // Validasi inline untuk kontrol penuh di controller
        $request->validate([
            'check_date' => 'required|date|before_or_equal:today',
            'work_area' => 'nullable|string|max:255',
            'data' => 'required|array|min:1',
            'data.*.crew_member_id' => 'required|uuid|exists:crew_members,id',
            'data.*.temperature' => 'nullable|numeric|between:34,43',
            'data.*.pulse_rate' => 'required|integer|between:0,200',
            'data.*.blood_pressure' => 'required|string|max:10',
            'data.*.respiratory_rate' => 'nullable|integer|between:0,100',
            'data.*.blood_sugar_level' => 'nullable|integer|between:0,500',
            'data.*.oxygen_saturation' => 'nullable|integer|between:0,100',
            'data.*.illness_complaints' => 'required|string|max:65535',
            'data.*.medications_consumed' => 'required|string|max:65535',
            'data.*.fatigue_level' => 'nullable|string|max:255',
            'data.*.napza_test_result' => 'nullable|in:non-negative,negative,not_tested',
            'data.*.romberg_test_result' => 'nullable|in:positive,negative,not_tested',
            'data.*.remarks' => 'nullable|string|max:1000',
        ]);

        $vessel = $this->getCoordinatedVessel();
        $checkDate = $request->check_date;
        $workArea = $request->work_area;
        $bulkData = $request->input('data');

        $successCount = 0;
        $updatedCount = 0;
        $errors = [];
        $processedIds = [];

        DB::beginTransaction();
        try {
            foreach ($bulkData as $item) {
                $crewId = $item['crew_member_id'];

                // Verifikasi kru milik kapal ini
                $crewMember = CrewMember::where('id', $crewId)
                    ->where('vessel_id', $vessel->id)
                    ->first();

                if (!$crewMember) {
                    $errors[] = "ID kru $crewId tidak valid atau tidak di kapal ini.";
                    continue;
                }

                // Cek apakah data hari ini sudah ada
                $existingCheck = HealthCheck::where('crew_member_id', $crewId)
                    ->where('vessel_id', $vessel->id)
                    ->whereDate('check_date', $checkDate)
                    ->first();

                $dataToSave = [
                    'crew_member_id' => $crewId,
                    'vessel_id' => $vessel->id,
                    'reported_by' => Auth::id(), // User yang login (Koordinator)
                    'check_date' => $checkDate,
                    'checked_at' => now(),
                    'work_area' => $workArea,
                    'temperature' => $item['temperature'] ?? null,
                    'pulse_rate' => $item['pulse_rate'],
                    'blood_pressure' => $item['blood_pressure'],
                    'respiratory_rate' => $item['respiratory_rate'] ?? null,
                    'blood_sugar_level' => $item['blood_sugar_level'] ?? null,
                    'oxygen_saturation' => $item['oxygen_saturation'] ?? null,
                    'illness_complaints' => $item['illness_complaints'],
                    'medications_consumed' => $item['medications_consumed'],
                    'fatigue_level' => $item['fatigue_level'] ?? null,
                    'napza_test_result' => $item['napza_test_result'] ?? 'not_tested',
                    'romberg_test_result' => $item['romberg_test_result'] ?? 'not_tested',
                    'remarks' => $item['remarks'] ?? null,
                    'status' => 'pending', // Reset status ke pending jika ada update
                ];

                if ($existingCheck) {
                    $existingCheck->update($dataToSave);
                    $processedIds[] = $existingCheck->id;
                    $updatedCount++;
                } else {
                    $check = HealthCheck::create($dataToSave);
                    $processedIds[] = $check->id;
                    $successCount++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk DCU Error', ['error' => $e->getMessage()]);
            return response()->json(
                ['message' => 'Gagal menyimpan data massal.', 'debug' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json([
            'message' => "Berhasil menyimpan $successCount pemeriksaan ($updatedCount diperbarui).",
            'success_count' => $successCount,
            'updated_count' => $updatedCount,
            'errors' => $errors,
            'processed_ids' => $processedIds
        ], Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail pemeriksaan.
     */
    public function show(HealthCheck $healthCheck): JsonResponse
    {
        $vessel = $this->getCoordinatedVessel();

        if ($healthCheck->vessel_id !== $vessel->id) {
            abort(Response::HTTP_FORBIDDEN, 'Pemeriksaan ini bukan milik kapal Anda.');
        }

        $healthCheck->load(['crewMember:id,name,position', 'reporter:id,name', 'verifier:id,name', 'validator:id,name']);

        return response()->json($healthCheck, Response::HTTP_OK);
    }

    /**
     * Menghapus pemeriksaan kesehatan.
     */
    public function destroy(HealthCheck $healthCheck): JsonResponse
    {
        $vessel = $this->getCoordinatedVessel();

        if ($healthCheck->vessel_id !== $vessel->id) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        // Validasi: hanya bisa hapus data hari ini
        if ($healthCheck->check_date->format('Y-m-d') !== today()->format('Y-m-d')) {
            return response()->json(
                ['message' => 'Hanya dapat menghapus pemeriksaan yang dilakukan hari ini.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $healthCheck->delete();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            Log::error('Delete DCU Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghapus data.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Export Data ke PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $vessel = $this->getCoordinatedVessel();
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $healthChecks = HealthCheck::where('vessel_id', $vessel->id)
                ->whereDate('check_date', '>=', $startDate)
                ->whereDate('check_date', '<=', $endDate)
                ->with(['crewMember:id,name', 'reporter:id,name', 'verifier:id,name', 'validator:id,name'])
                ->orderBy('check_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($healthChecks->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data untuk periode yang dipilih.'], Response::HTTP_NOT_FOUND);
            }

            $groupedByDate = $healthChecks->groupBy(fn($item) => Carbon::parse($item->check_date)->format('Y-m-d'));

            $data = [
                'groupedByDate' => $groupedByDate,
                'vesselName' => $vessel->name,
                'startDate' => Carbon::parse($startDate)->translatedFormat('d F Y'),
                'endDate' => Carbon::parse($endDate)->translatedFormat('d F Y'),
                'companyName' => $vessel->company->name ?? 'N/A'
            ];

            // Pastikan view export sesuai (contoh: 'my-dcu.export-pdf')
            $pdf = Pdf::loadView('my-dcu.export-pdf', $data)->setPaper('a4', 'landscape');
            $filename = 'Laporan_DCU_' . str_replace(' ', '_', $vessel->name) . '_' . $startDate . '_to_' . $endDate . '.pdf';

            return $pdf->download($filename);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('API PDF Export Error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal membuat file PDF.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Export Data ke Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $vessel = $this->getCoordinatedVessel();
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Cek ketersediaan data sebelum generate Excel
            $exists = HealthCheck::where('vessel_id', $vessel->id)
                ->whereDate('check_date', '>=', $startDate)
                ->whereDate('check_date', '<=', $endDate)
                ->exists();

            if (!$exists) {
                return response()->json(['message' => 'Tidak ada data untuk periode ini.'], Response::HTTP_NOT_FOUND);
            }

            $fileName = 'Laporan_DCU_' . $vessel->name . '_' . $startDate . '_to_' . $endDate . '.xlsx';

            // Menggunakan Export Class seperti di referensi target
            return Excel::download(
                new DailyHealthCheckExport($startDate, $endDate, $vessel->id),
                $fileName
            );

        } catch (\Exception $e) {
            Log::error('API Excel Export Error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal export Excel.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
