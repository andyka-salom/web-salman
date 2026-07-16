<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ContactRequest;
use App\Imports\ContactsImport;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContactController extends Controller
{
    use AuthorizesRequests;

    protected string $cacheKey      = 'contacts';
    protected int    $cacheDuration = 3600; // 1 hour

    // -------------------------------------------------------
    // Index / DataTables
    // -------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorize('manage contacts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contacts'],
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        $stats = $this->getStats();

        return view('master.contacts.index', compact('breadcrumbs', 'stats'));
    }

    protected function getDatatablesData(Request $request)
    {
        $query = Contact::select([
            'id', 'name', 'whatsapp_number', 'position', 'notes', 'is_active', 'created_at',
        ]);

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $query->search($request->search['value']);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', fn ($c) =>
                $c->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>'
            )
            ->addColumn('wa_link', fn ($c) =>
                '<a href="https://wa.me/' . htmlspecialchars($c->whatsapp_number) . '" target="_blank" class="text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>' . htmlspecialchars($c->whatsapp_number) . '</a>'
            )
            ->addColumn('action', fn ($c) =>
                view('master.contacts.partials.actions', compact('c'))->render()
            )
            ->rawColumns(['status', 'wa_link', 'action'])
            ->make(true);
    }

    // -------------------------------------------------------
    // CRUD
    // -------------------------------------------------------

    public function create()
    {
        $this->authorize('manage contacts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contacts', 'url' => route('contacts.index')],
            ['label' => 'Create'],
        ];

        return view('master.contacts.create', compact('breadcrumbs'));
    }

    public function store(ContactRequest $request)
    {
        $this->authorize('manage contacts');

        try {
            DB::beginTransaction();

            $data                    = $request->validated();
            $data['created_by']      = auth()->id();
            $data['is_active']       = $request->boolean('is_active', true);
            $data['whatsapp_number'] = Contact::normalizeWhatsappNumber($data['whatsapp_number']);

            $contact = Contact::create($data);

            $this->clearCache();

            DB::commit();

            Log::info('Contact created', ['contact_id' => $contact->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Contact created successfully.',
                'redirect' => route('contacts.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contact creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Contact $contact)
    {
        $this->authorize('manage contacts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contacts', 'url' => route('contacts.index')],
            ['label' => $contact->name],
        ];

        $contact->load('groups');

        return view('master.contacts.show', compact('contact', 'breadcrumbs'));
    }

    public function edit(Contact $contact)
    {
        $this->authorize('manage contacts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contacts', 'url' => route('contacts.index')],
            ['label' => 'Edit ' . $contact->name],
        ];

        return view('master.contacts.create', compact('contact', 'breadcrumbs'));
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        $this->authorize('manage contacts');

        try {
            DB::beginTransaction();

            $data                    = $request->validated();
            $data['is_active']       = $request->boolean('is_active', true);
            $data['whatsapp_number'] = Contact::normalizeWhatsappNumber($data['whatsapp_number']);

            $contact->update($data);

            $this->clearCache($contact->id);

            DB::commit();

            Log::info('Contact updated', ['contact_id' => $contact->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Contact updated successfully.',
                'redirect' => route('contacts.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contact update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Contact $contact)
    {
        $this->authorize('manage contacts');

        try {
            DB::beginTransaction();

            // Detach from all groups
            $contact->groups()->detach();
            $contact->delete();

            $this->clearCache($contact->id);

            DB::commit();

            Log::info('Contact deleted', ['contact_id' => $contact->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contact deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Contact $contact)
    {
        $this->authorize('manage contacts');

        try {
            $contact->update(['is_active' => !$contact->is_active]);
            $this->clearCache($contact->id);

            return response()->json([
                'success'   => true,
                'message'   => 'Contact status updated successfully.',
                'is_active' => $contact->is_active,
            ]);

        } catch (\Exception $e) {
            Log::error('Contact status toggle failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    // -------------------------------------------------------
    // Import
    // -------------------------------------------------------

    public function importForm()
    {
        $this->authorize('manage contacts');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Contacts', 'url' => route('contacts.index')],
            ['label' => 'Import'],
        ];

        return view('master.contacts.import', compact('breadcrumbs'));
    }

    public function import(Request $request)
    {
        $this->authorize('manage contacts');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // max 5 MB
        ]);

        try {
            $import = new ContactsImport();
            Excel::import($import, $request->file('file'));

            $this->clearCache();

            Log::info('Contacts imported', [
                'imported' => $import->importedCount,
                'skipped'  => $import->skippedCount,
                'user_id'  => auth()->id(),
            ]);

            return response()->json([
                'success'  => true,
                'message'  => "{$import->importedCount} contact(s) imported. {$import->skippedCount} skipped (duplicate / incomplete).",
                'imported' => $import->importedCount,
                'skipped'  => $import->skippedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Contact import failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download blank import template
     */
    public function downloadTemplate()
    {
        $this->authorize('manage contacts');

        $headers = ['name', 'whatsapp_number', 'position', 'notes'];
        $example = ['John Doe', '08123456789', 'Manager', 'Contoh keterangan'];

        $csvContent = implode(',', $headers) . "\n" . implode(',', $example) . "\n";

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_import_template.csv"',
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    protected function getStats(): array
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, fn () => [
            'total'    => Contact::count(),
            'active'   => Contact::active()->count(),
            'inactive' => Contact::where('is_active', false)->count(),
        ]);
    }

    protected function clearCache(?string $contactId = null): void
    {
        Cache::forget("{$this->cacheKey}.stats");

        if ($contactId) {
            Cache::forget("{$this->cacheKey}.{$contactId}");
        }
    }
}
