<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller; // Pastikan baris ini ada
use App\Models\Pelanggaran;
use App\Http\Requests\Master\PelanggaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class PelanggaranController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'pelanggaran';
    protected $cacheDuration = 3600; // 1 hour

    public function index(Request $request)
    {
        $this->authorize('manage pelanggaran');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pelanggaran']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        return view('master.pelanggaran.index', compact('breadcrumbs'));
    }

    protected function getDatatablesData(Request $request)
    {
        // PERUBAHAN: Menghapus 'code', Menambahkan 'sequence_number'
        $query = Pelanggaran::query()->select([
            'id', 'sequence_number', 'name', 'severity', 'is_active', 'created_at'
        ])->ordered(); // Menggunakan scope ordered untuk default sorting

        // Filter by Status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        // Filter by Severity
        if ($request->filled('severity') && $request->severity !== '') {
            $query->where('severity', $request->severity);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('sequence_number', function($row) {
                return $row->sequence_number;
            })
            ->editColumn('severity', function($row) {
                // severity_badge_class diambil dari Model accessor
                return '<span class="badge '.$row->severity_badge_class.'">'.ucfirst($row->severity).'</span>';
            })
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function($pelanggaran) {
                return view('master.pelanggaran.partials.actions', compact('pelanggaran'))->render();
            })
            ->rawColumns(['severity', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('manage pelanggaran');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pelanggaran', 'url' => route('pelanggaran.index')],
            ['label' => 'Create']
        ];

        // Opsional: Mencari nomer urut berikutnya secara otomatis
        $nextSequence = Pelanggaran::max('sequence_number') + 1;

        return view('master.pelanggaran.create', compact('breadcrumbs', 'nextSequence'));
    }

    public function store(PelanggaranRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $pelanggaran = Pelanggaran::create($data);

            DB::commit();

            Log::info('Pelanggaran created', ['id' => $pelanggaran->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran created successfully',
                'redirect' => route('pelanggaran.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pelanggaran creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $this->authorize('manage pelanggaran');
        // Cache detail
        $data = Cache::remember(
            "{$this->cacheKey}.{$pelanggaran->id}",
            $this->cacheDuration,
            function() use ($pelanggaran) {
                return $pelanggaran;
            }
        );

        // PERUBAHAN: Menampilkan sequence_number di breadcrumbs
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pelanggaran', 'url' => route('pelanggaran.index')],
            ['label' => 'No. ' . $data->sequence_number]
        ];

        return view('master.pelanggaran.show', [
            'pelanggaran' => $data,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        $this->authorize('manage pelanggaran');

        // PERUBAHAN: Menampilkan sequence_number di breadcrumbs
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pelanggaran', 'url' => route('pelanggaran.index')],
            ['label' => 'Edit No. ' . $pelanggaran->sequence_number]
        ];

        return view('master.pelanggaran.create', compact('pelanggaran', 'breadcrumbs'));
    }

    public function update(PelanggaranRequest $request, Pelanggaran $pelanggaran)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $pelanggaran->update($data);

            $this->clearCache($pelanggaran->id);

            DB::commit();

            Log::info('Pelanggaran updated', ['id' => $pelanggaran->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran updated successfully',
                'redirect' => route('pelanggaran.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pelanggaran update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Pelanggaran $pelanggaran)
    {
        try {
            DB::beginTransaction();

            $pelanggaran->delete();
            $this->clearCache($pelanggaran->id);

            DB::commit();

            Log::info('Pelanggaran deleted', ['id' => $pelanggaran->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pelanggaran deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Pelanggaran $pelanggaran)
    {
        try {
            $pelanggaran->update(['is_active' => !$pelanggaran->is_active]);
            $this->clearCache($pelanggaran->id);

            Log::info('Pelanggaran status toggled', ['id' => $pelanggaran->id, 'status' => $pelanggaran->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'is_active' => $pelanggaran->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error updating status'], 500);
        }
    }

    protected function clearCache($id = null)
    {
        if ($id) Cache::forget("{$this->cacheKey}.{$id}");
    }
}
