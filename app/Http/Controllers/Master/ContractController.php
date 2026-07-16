<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\ContractRequest;

// Imports for Excel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContractsExport;
use App\Imports\ContractsImport;
use Maatwebsite\Excel\Validators\ValidationException;


class ContractController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'contracts';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of contracts
     */
    public function index(Request $request)
    {
        $this->authorize('manage contracts'); // Uncomment if using Policies

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contracts']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get statistics
        $stats = $this->getContractStats();

        // Note: We need to define routes for contracts in web.php first for route() calls to work
        return view('master.contracts.index', compact('breadcrumbs', 'stats'));
    }

    /**
     * Get DataTables data
     */
    protected function getDatatablesData(Request $request)
    {
        $query = Contract::select([
            'id',
            'nama_kontraktor',
            'sap_no',
            'alamat_email',
            'no_tlp_kantor',
            'created_at'
        ]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function($contract) {
                // Ensure this path matches your view location
                return view('master.contracts.partials.actions', compact('contract'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new contract
     */
    public function create()
    {
        $this->authorize('manage contracts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contracts', 'url' => route('contracts.index')],
            ['label' => 'Create']
        ];

        return view('master.contracts.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created contract
     */
    public function store(ContractRequest $request)
    {
        $this->authorize('manage contracts');

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $contract = Contract::create($data);

            $this->clearContractCache();

            DB::commit();

            Log::info('Contract created', ['contract_id' => $contract->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Contract created successfully',
                'redirect' => route('contracts.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contract: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified contract
     */
    public function show(Contract $contract)
    {
        $this->authorize('manage contracts');

        $contractData = Cache::remember(
            "{$this->cacheKey}.{$contract->id}",
            $this->cacheDuration,
            fn() => $contract
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contracts', 'url' => route('contracts.index')],
            ['label' => $contract->nama_kontraktor]
        ];

        return view('master.contracts.show', [
            'contract' => $contractData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified contract
     */
    public function edit(Contract $contract)
    {
        $this->authorize('manage contracts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contracts', 'url' => route('contracts.index')],
            ['label' => 'Edit ' . $contract->nama_kontraktor]
        ];

        return view('master.contracts.create', compact('contract', 'breadcrumbs'));
    }

    /**
     * Update the specified contract
     */
    public function update(ContractRequest $request, Contract $contract)
    {
        $this->authorize('manage contracts');

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $contract->update($data);

            $this->clearContractCache($contract->id);

            DB::commit();

            Log::info('Contract updated', ['contract_id' => $contract->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Contract updated successfully',
                'redirect' => route('contracts.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified contract
     */
    public function destroy(Contract $contract)
    {
        $this->authorize('manage contracts');

        try {
            $contract->delete();

            $this->clearContractCache($contract->id);

            Log::info('Contract deleted', ['contract_id' => $contract->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Contract deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Contract deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => str_contains($e->getMessage(), 'Foreign key')
                    ? 'Tidak dapat menghapus kontrak karena sudah terkait dengan data lain.'
                    : 'Gagal menghapus kontrak: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- EXPORT & IMPORT METHODS ---

    /**
     * Export contracts data to Excel/CSV.
     */
    public function export()
    {
        $this->authorize('manage contracts');
        $filename = 'contracts_' . now()->format('Ymd_His') . '.xlsx';
        Log::info('Contract export initiated', ['user_id' => auth()->id()]);

        return Excel::download(new ContractsExport, $filename);
    }

    /**
     * Show the import form.
     */
    public function importForm()
    {
        $this->authorize('manage contracts');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contracts', 'url' => route('contracts.index')],
            ['label' => 'Import']
        ];

        return view('master.contracts.import', compact('breadcrumbs'));
    }

    /**
     * Handle the import of contracts data from file.
     */
    public function import(Request $request)
    {
        $this->authorize('manage contracts');
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            DB::beginTransaction();

            $import = new ContractsImport;
            Excel::import($import, $request->file('file'));

            DB::commit();

            Log::info('Contract import successful', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Contracts imported successfully!',
                'redirect' => route('contracts.index')
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                 $row = $failure->row();
                 // $attribute = $failure->attribute(); // column name
                 $errors[] = "Baris {$row}: " . implode(' ', $failure->errors());
            }

            Log::warning('Contract import validation failed', ['user_id' => auth()->id(), 'errors' => $errors]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $errors,
                'error_type' => 'validation'
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract import failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get contract statistics with caching
     */
    protected function getContractStats()
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, function() {
            return [
                'total' => Contract::count(),
            ];
        });
    }

    /**
     * Clear contract cache
     */
    protected function clearContractCache($contractId = null)
    {
        Cache::forget("{$this->cacheKey}.stats");

        if ($contractId) {
            Cache::forget("{$this->cacheKey}.{$contractId}");
        }
    }
}
