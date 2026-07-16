<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BroadcastMessage;
use App\Models\BroadcastRecipient;
use App\Models\User;
use App\Models\CrewMember;
use App\Models\Group;
use App\Models\WhatsAppGroupCache;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BroadcastController extends Controller
{
    use AuthorizesRequests;

    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * GET /api/broadcast/dashboard
     * Mengambil statistik untuk dashboard broadcast
     */
    public function index()
    {
        $this->authorize('manage broadcast');

        $stats = [
            'total_broadcasts' => BroadcastMessage::where('created_by', auth()->id())->count(),
            'total_sent' => BroadcastMessage::where('created_by', auth()->id())->sum('sent_count'),
            'pending' => BroadcastMessage::where('created_by', auth()->id())
                ->whereIn('status', ['draft', 'scheduled', 'processing'])->count(),
        ];

        return response()->json([
            'message' => 'Dashboard statistics retrieved successfully',
            'status' => 200,
            'data' => $stats
        ], 200);
    }

    /**
     * GET /api/broadcast/form-data
     * Mengambil data master (Grup, User, Crew, WA Groups) untuk mengisi dropdown di Frontend
     */
    public function formData(Request $request)
    {
        $this->authorize('manage broadcast');

        $type = $request->query('type'); // manual, group_contact, group_wa

        $data = [];

        if ($type === 'group_contact') {
            $data['groups'] = Group::byType('contact')
                ->withCount(['users', 'crewMembers'])
                ->orderBy('group_name')
                ->get(['id', 'group_name']);

            $data['users'] = User::whereNotNull('phone')
                ->orderBy('name')
                ->get(['id', 'name', 'phone']);

            $data['crew_members'] = CrewMember::whereNotNull('phone')
                ->orderBy('name')
                ->get(['id', 'name', 'phone']);
        }

        if ($type === 'group_wa') {
            $data['wa_groups'] = WhatsAppGroupCache::select('group_id', 'group_name', 'member_count')
                ->orderBy('group_name')
                ->get();
        }

        return response()->json([
            'message' => 'Form data retrieved successfully',
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * POST /api/broadcast/sync-wa-groups
     * Sinkronisasi Grup WA dari Fonnte ke Database Lokal
     */
    public function syncWhatsAppGroups()
    {
        $this->authorize('manage broadcast');

        try {
            $result = $this->fonnteService->getWhatsAppGroups(true); // Force refresh

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to sync WhatsApp groups: ' . ($result['error'] ?? 'Unknown error'),
                    'status' => 500
                ], 500);
            }

            return response()->json([
                'message' => 'Successfully synced ' . count($result['data']) . ' WhatsApp groups!',
                'status' => 200,
                'data' => [
                    'total_synced' => count($result['data']),
                    'groups' => $result['data']
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Sync WA Groups Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal server error',
                'status' => 500
            ], 500);
        }
    }

    /**
     * POST /api/broadcast/send/manual
     * Kirim Broadcast Manual (Input nomor langsung)
     */
    public function sendManual(Request $request)
    {
        $this->authorize('manage broadcast');

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'phone_numbers' => 'required|string',
            'scheduled_at' => 'nullable|date_format:Y-m-d H:i:s|after:now',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Bersihkan input nomor
            $numbers = array_filter(array_map('trim', preg_split('/[,;\n\r]+/', $request->phone_numbers)));

            if (empty($numbers)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No valid phone numbers provided',
                    'status' => 422
                ], 422);
            }

            $mediaData = $this->handleMediaUpload($request);

            $broadcast = BroadcastMessage::create([
                'created_by' => auth()->id(),
                'title' => $request->title ?? 'Manual Broadcast - ' . now()->format('d/m/Y H:i'),
                'message' => $request->message,
                'target_type' => 'manual',
                'target_data' => ['numbers' => $numbers],
                'status' => $request->scheduled_at ? 'scheduled' : 'processing',
                'scheduled_at' => $request->scheduled_at ? Carbon::parse($request->scheduled_at) : null,
                'total_recipients' => count($numbers),
                ...$mediaData,
            ]);

            foreach ($numbers as $number) {
                BroadcastRecipient::create([
                    'broadcast_message_id' => $broadcast->id,
                    'recipient_type' => 'manual',
                    'phone_number' => $number,
                    'recipient_name' => 'Manual Input',
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Process External API
            $result = $this->fonnteService->processBroadcast($broadcast);

            if (!$result['success']) {
                $broadcast->update(['status' => 'failed', 'error_message' => $result['error']]);
                return response()->json([
                    'message' => 'Broadcast saved but failed to send via Fonnte',
                    'status' => 502,
                    'data' => $broadcast,
                    'error' => $result['error']
                ], 502);
            }

            return response()->json([
                'message' => $request->scheduled_at ? 'Broadcast scheduled successfully' : 'Broadcast is being processed',
                'status' => 201,
                'data' => $broadcast
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Send Manual Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error',
                'status' => 500
            ], 500);
        }
    }

    /**
     * POST /api/broadcast/send/group-contact
     * Kirim Broadcast ke Grup Kontak Database
     */
    public function sendGroupContact(Request $request)
    {
        $this->authorize('manage broadcast');

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'target_selection' => 'required|in:group,manual,all',
            'group_id' => 'required_if:target_selection,group|exists:groups,id',
            'user_ids' => 'nullable|array',
            'crew_member_ids' => 'nullable|array',
            'scheduled_at' => 'nullable|date_format:Y-m-d H:i:s|after:now',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $recipients = $this->collectContactRecipients($request);

            if (empty($recipients)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No valid recipients found',
                    'status' => 422
                ], 422);
            }

            $mediaData = $this->handleMediaUpload($request);

            $broadcast = BroadcastMessage::create([
                'created_by' => auth()->id(),
                'title' => $request->title ?? 'Group Contact Broadcast - ' . now()->format('d/m/Y H:i'),
                'message' => $request->message,
                'target_type' => 'group_contact',
                'target_data' => [
                    'selection' => $request->target_selection,
                    'group_id' => $request->group_id,
                    'user_ids' => $request->user_ids ?? [],
                    'crew_member_ids' => $request->crew_member_ids ?? [],
                ],
                'status' => $request->scheduled_at ? 'scheduled' : 'processing',
                'scheduled_at' => $request->scheduled_at ? Carbon::parse($request->scheduled_at) : null,
                'total_recipients' => count($recipients),
                ...$mediaData,
            ]);

            foreach ($recipients as $recipient) {
                BroadcastRecipient::create([
                    'broadcast_message_id' => $broadcast->id,
                    ...$recipient,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            $result = $this->fonnteService->processBroadcast($broadcast);

            if (!$result['success']) {
                $broadcast->update(['status' => 'failed', 'error_message' => $result['error']]);
                return response()->json([
                    'message' => 'Broadcast saved but failed to send via Fonnte',
                    'status' => 502,
                    'data' => $broadcast,
                    'error' => $result['error']
                ], 502);
            }

            return response()->json([
                'message' => 'Broadcast created successfully',
                'status' => 201,
                'data' => $broadcast
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Send Group Contact Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error',
                'status' => 500
            ], 500);
        }
    }

    /**
     * POST /api/broadcast/send/group-wa
     * Kirim Broadcast ke Grup WhatsApp Asli
     */
    public function sendGroupWA(Request $request)
    {
        $this->authorize('manage broadcast');

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'wa_group_ids' => 'required|array|min:1',
            'wa_group_ids.*' => 'string|exists:whatsapp_groups_cache,group_id',
            'scheduled_at' => 'nullable|date_format:Y-m-d H:i:s|after:now',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $groups = WhatsAppGroupCache::whereIn('group_id', $request->wa_group_ids)->get();

            if ($groups->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'WhatsApp groups not found',
                    'status' => 404
                ], 404);
            }

            $mediaData = $this->handleMediaUpload($request);

            $broadcast = BroadcastMessage::create([
                'created_by' => auth()->id(),
                'title' => $request->title ?? 'WhatsApp Group Broadcast - ' . now()->format('d/m/Y H:i'),
                'message' => $request->message,
                'target_type' => 'group_wa',
                'target_data' => [
                    'group_ids' => $request->wa_group_ids,
                    'groups' => $groups->map(fn($g) => [
                        'id' => $g->group_id,
                        'name' => $g->group_name,
                        'member_count' => $g->member_count,
                    ])->toArray(),
                ],
                'status' => $request->scheduled_at ? 'scheduled' : 'processing',
                'scheduled_at' => $request->scheduled_at ? Carbon::parse($request->scheduled_at) : null,
                'total_recipients' => $groups->count(),
                ...$mediaData,
            ]);

            foreach ($groups as $group) {
                BroadcastRecipient::create([
                    'broadcast_message_id' => $broadcast->id,
                    'recipient_type' => 'wa_group',
                    'phone_number' => $group->group_id,
                    'recipient_name' => $group->group_name,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            $result = $this->fonnteService->processBroadcast($broadcast);

            if (!$result['success']) {
                $broadcast->update(['status' => 'failed', 'error_message' => $result['error']]);
                return response()->json([
                    'message' => 'Broadcast saved but failed to send via Fonnte',
                    'status' => 502,
                    'data' => $broadcast,
                    'error' => $result['error']
                ], 502);
            }

            return response()->json([
                'message' => 'WhatsApp group broadcast created successfully',
                'status' => 201,
                'data' => $broadcast
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Send Group WA Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error',
                'status' => 500
            ], 500);
        }
    }

    /**
     * GET /api/broadcast/history
     * Mengambil riwayat broadcast
     */
    public function history(Request $request)
    {
        $this->authorize('manage broadcast');

        $query = BroadcastMessage::with('creator:id,name')
            ->where('created_by', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('message', 'like', $search);
            });
        }

        $broadcasts = $query->paginate(20);

        return response()->json([
            'message' => 'Broadcast history retrieved successfully',
            'status' => 200,
            'data' => $broadcasts->items(),
            'pagination' => [
                'current_page' => $broadcasts->currentPage(),
                'last_page' => $broadcasts->lastPage(),
                'per_page' => $broadcasts->perPage(),
                'total' => $broadcasts->total(),
                'from' => $broadcasts->firstItem(),
                'to' => $broadcasts->lastItem(),
            ]
        ], 200);
    }

    /**
     * GET /api/broadcast/{id}
     * Detail broadcast
     */
    public function show($id)
    {
        $broadcast = BroadcastMessage::with(['creator:id,name', 'recipients'])
            ->find($id);

        if (!$broadcast) {
            return response()->json([
                'message' => 'Broadcast not found',
                'status' => 404
            ], 404);
        }

        if ($broadcast->created_by !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 403
            ], 403);
        }

        $stats = [
            'successful' => $broadcast->recipients()->whereIn('status', ['sent', 'delivered', 'read'])->count(),
            'failed' => $broadcast->recipients()->where('status', 'failed')->count(),
            'pending' => $broadcast->recipients()->where('status', 'pending')->count(),
            'total' => $broadcast->total_recipients,
        ];

        return response()->json([
            'message' => 'Broadcast detail retrieved successfully',
            'status' => 200,
            'data' => [
                'broadcast' => $broadcast,
                'stats' => $stats
            ]
        ], 200);
    }

    /**
     * POST /api/broadcast/{id}/retry
     * Retry broadcast yang gagal
     */
    public function retry($id)
    {
        $broadcast = BroadcastMessage::find($id);

        if (!$broadcast) {
            return response()->json([
                'message' => 'Broadcast not found',
                'status' => 404
            ], 404);
        }

        if ($broadcast->created_by !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 403
            ], 403);
        }

        DB::beginTransaction();
        try {
            $failedRecipients = $broadcast->recipients()->where('status', 'failed');
            $count = $failedRecipients->count();

            if ($count === 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No failed recipients to retry',
                    'status' => 400
                ], 400);
            }

            $failedRecipients->update([
                'status' => 'pending',
                'error_message' => null,
                'api_response' => null,
            ]);

            $broadcast->update(['status' => 'processing', 'error_message' => null]);

            DB::commit();

            $result = $this->fonnteService->processBroadcast($broadcast);

            if ($result['success']) {
                return response()->json([
                    'message' => "Retry successful for {$count} recipients",
                    'status' => 200,
                    'data' => [
                        'retried_count' => $count
                    ]
                ], 200);
            } else {
                $broadcast->refresh()->update(['status' => 'failed', 'error_message' => $result['error']]);
                return response()->json([
                    'message' => 'Retry failed to send via Fonnte',
                    'status' => 502,
                    'error' => $result['error']
                ], 502);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Retry Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error',
                'status' => 500
            ], 500);
        }
    }

    /**
     * DELETE /api/broadcast/{id}
     * Hapus broadcast
     */
    public function destroy($id)
    {
        $broadcast = BroadcastMessage::find($id);

        if (!$broadcast) {
            return response()->json([
                'message' => 'Broadcast not found',
                'status' => 404
            ], 404);
        }

        if ($broadcast->created_by !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 403
            ], 403);
        }

        try {
            if ($broadcast->media_url) {
                $path = str_replace(Storage::url(''), '', $broadcast->media_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $broadcast->delete();

            return response()->json([
                'message' => 'Broadcast deleted successfully',
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Delete Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error',
                'status' => 500
            ], 500);
        }
    }

    // --- Helpers (Private) ---

    protected function handleMediaUpload(Request $request)
    {
        $mediaData = [
            'media_type' => null,
            'media_url' => null,
            'media_filename' => null,
        ];

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('broadcast-media', 'public');

            // Gunakan full URL agar bisa diakses Fonnte
            $mediaData['media_url'] = url(Storage::url($path));
            $mediaData['media_type'] = $this->getMediaType($file->getMimeType());
            $mediaData['media_filename'] = $file->getClientOriginalName();
        }

        return $mediaData;
    }

    protected function getMediaType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';
        return 'document';
    }

    protected function collectContactRecipients(Request $request)
    {
        $recipients = [];

        switch ($request->target_selection) {
            case 'group':
                $group = Group::byType('contact')
                    ->with(['users' => fn($q) => $q->whereNotNull('phone'), 'crewMembers' => fn($q) => $q->whereNotNull('phone')])
                    ->find($request->group_id);

                if ($group) {
                    foreach ($group->users as $user) {
                        $recipients[] = [
                            'recipient_type' => 'user',
                            'recipient_id' => $user->id,
                            'phone_number' => $user->phone,
                            'recipient_name' => $user->name,
                        ];
                    }
                    foreach ($group->crewMembers as $crew) {
                        $recipients[] = [
                            'recipient_type' => 'crew_member',
                            'recipient_id' => $crew->id,
                            'phone_number' => $crew->phone,
                            'recipient_name' => $crew->name,
                        ];
                    }
                }
                break;

            case 'manual':
                if (!empty($request->user_ids)) {
                    User::whereIn('id', $request->user_ids)->whereNotNull('phone')->get()
                        ->each(function ($u) use (&$recipients) {
                            $recipients[] = ['recipient_type' => 'user', 'recipient_id' => $u->id, 'phone_number' => $u->phone, 'recipient_name' => $u->name];
                        });
                }
                if (!empty($request->crew_member_ids)) {
                    CrewMember::whereIn('id', $request->crew_member_ids)->whereNotNull('phone')->get()
                        ->each(function ($c) use (&$recipients) {
                            $recipients[] = ['recipient_type' => 'crew_member', 'recipient_id' => $c->id, 'phone_number' => $c->phone, 'recipient_name' => $c->name];
                        });
                }
                break;

            case 'all':
                User::whereNotNull('phone')->get()->each(function ($u) use (&$recipients) {
                    $recipients[] = ['recipient_type' => 'user', 'recipient_id' => $u->id, 'phone_number' => $u->phone, 'recipient_name' => $u->name];
                });
                CrewMember::whereNotNull('phone')->get()->each(function ($c) use (&$recipients) {
                    $recipients[] = ['recipient_type' => 'crew_member', 'recipient_id' => $c->id, 'phone_number' => $c->phone, 'recipient_name' => $c->name];
                });
                break;
        }

        $unique = [];
        foreach ($recipients as $recipient) {
            $unique[$recipient['phone_number']] = $recipient;
        }

        return array_values($unique);
    }
}
