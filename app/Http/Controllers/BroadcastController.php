<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBroadcastJob;
use App\Models\BroadcastMessage;
use App\Models\BroadcastRecipient;
use App\Models\Contact;
use App\Models\CrewMember;
use App\Models\Group;
use App\Models\User;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BroadcastController extends Controller
{
    use AuthorizesRequests;

    protected FonnteService $fonnteService;

    private const JOB_DISPATCH_DELAY = 5;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    // ============================================================
    // FORM CREATION METHODS
    // ============================================================

    public function createManual()
    {
        $this->authorize('manage broadcast');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Broadcast History', 'url' => route('broadcast.history')],
            ['label' => 'Manual Broadcast'],
        ];

        return view('broadcast.create-manual', compact('breadcrumbs'));
    }

    public function createGroupContact()
    {
        $this->authorize('manage broadcast');

        $groups = Group::query()
            ->with([
                'users'       => fn ($q) => $q->whereNotNull('phone'),
                'crewMembers' => fn ($q) => $q->whereNotNull('phone'),
                'contacts'    => fn ($q) => $q->where('is_active', true)->whereNotNull('whatsapp_number'),
            ])
            ->orderBy('group_name')
            ->get();

        $users       = User::whereNotNull('phone')->orderBy('name')->get();
        $crewMembers = CrewMember::whereNotNull('phone')->orderBy('name')->get();
        $contacts    = Contact::active()->whereNotNull('whatsapp_number')->orderBy('name')->get();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Broadcast History', 'url' => route('broadcast.history')],
            ['label' => 'Group Contact Broadcast'],
        ];

        return view('broadcast.create-group-contact', compact(
            'groups', 'users', 'crewMembers', 'contacts', 'breadcrumbs'
        ));
    }

    // ============================================================
    // PROCESSING METHODS
    // ============================================================

    public function sendManual(Request $request)
    {
        $this->authorize('manage broadcast');

        $validator = Validator::make($request->all(), [
            'title'         => 'nullable|string|max:255',
            'message'       => 'required|string|max:5000',
            'phone_numbers' => 'required|string',
            'scheduled_at'  => 'nullable|date|after:now',
            'media_file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $numbers = array_filter(
                array_map('trim', preg_split('/[,;\n\r]+/', $request->phone_numbers))
            );

            if (empty($numbers)) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada nomor telepon yang valid!')->withInput();
            }

            $mediaData = $this->handleMediaUpload($request);

            $broadcast = BroadcastMessage::create([
                'created_by'       => auth()->id(),
                'title'            => $request->title ?? 'Manual Broadcast - ' . now()->format('d/m/Y H:i'),
                'message'          => $request->message,
                'target_type'      => 'manual',
                'target_data'      => ['numbers' => $numbers],
                'status'           => 'queued',
                'scheduled_at'     => $request->scheduled_at ? Carbon::parse($request->scheduled_at) : null,
                'total_recipients' => count($numbers),
                ...$mediaData,
            ]);

            foreach ($numbers as $number) {
                BroadcastRecipient::create([
                    'broadcast_message_id' => $broadcast->id,
                    'recipient_type'       => 'manual',
                    'phone_number'         => $number,
                    'recipient_name'       => 'Manual Input',
                    'status'               => 'pending',
                ]);
            }

            DB::commit();

            ProcessBroadcastJob::dispatch($broadcast->id)
                ->delay(now()->addSeconds(self::JOB_DISPATCH_DELAY));

            $message = $request->scheduled_at
                ? 'Broadcast berhasil dijadwalkan!'
                : 'Broadcast sedang diproses dan akan segera dikirim!';

            return redirect()->route('broadcast.show', $broadcast->id)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Exception during sendManual', ['error' => $e->getMessage(), 'user' => auth()->id()]);
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.')->withInput();
        }
    }

    public function sendGroupContact(Request $request)
    {
        $this->authorize('manage broadcast');

        $validator = Validator::make($request->all(), [
            'title'            => 'nullable|string|max:255',
            'message'          => 'required|string|max:5000',
            'target_selection' => 'required|in:group,manual,all',
            'group_id'         => 'required_if:target_selection,group|nullable|exists:groups,id',
            'user_ids'         => 'nullable|array',
            'crew_member_ids'  => 'nullable|array',
            'contact_ids'      => 'nullable|array',
            'scheduled_at'     => 'nullable|date|after:now',
            'media_file'       => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $recipients = $this->collectContactRecipients($request);

            if (empty($recipients)) {
                DB::rollBack();
                return back()
                    ->with('error', 'Tidak ada penerima yang valid! Pastikan kontak memiliki nomor telepon/WhatsApp.')
                    ->withInput();
            }

            $mediaData = $this->handleMediaUpload($request);

            $broadcast = BroadcastMessage::create([
                'created_by'       => auth()->id(),
                'title'            => $request->title ?? 'Group Contact Broadcast - ' . now()->format('d/m/Y H:i'),
                'message'          => $request->message,
                'target_type'      => 'group_contact',
                'target_data'      => [
                    'selection'       => $request->target_selection,
                    'group_id'        => $request->group_id ?? null,
                    'user_ids'        => $request->user_ids ?? [],
                    'crew_member_ids' => $request->crew_member_ids ?? [],
                    'contact_ids'     => $request->contact_ids ?? [],
                ],
                'status'           => 'queued',
                'scheduled_at'     => $request->scheduled_at ? Carbon::parse($request->scheduled_at) : null,
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

            ProcessBroadcastJob::dispatch($broadcast->id)
                ->delay(now()->addSeconds(self::JOB_DISPATCH_DELAY));

            Log::info('Broadcast queued', ['broadcast_id' => $broadcast->id, 'total' => count($recipients)]);

            $message = $request->scheduled_at
                ? 'Broadcast berhasil dijadwalkan!'
                : 'Broadcast sedang diproses dan akan segera dikirim!';

            return redirect()->route('broadcast.show', $broadcast->id)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Exception during sendGroupContact', ['error' => $e->getMessage(), 'user' => auth()->id()]);
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.')->withInput();
        }
    }

    // ============================================================
    // HISTORY AND MANAGEMENT METHODS
    // ============================================================

    public function history(Request $request)
    {
        $this->authorize('manage broadcast');

        $query = BroadcastMessage::with('creator')
            ->where('created_by', auth()->id())
            ->whereIn('target_type', ['manual', 'group_contact'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('title', 'like', $search)->orWhere('message', 'like', $search));
        }

        $broadcasts = $query->paginate(20);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Broadcast History'],
        ];

        return view('broadcast.history', compact('broadcasts', 'breadcrumbs'));
    }

    public function show($id)
    {
        $broadcast = BroadcastMessage::with(['creator', 'recipients'])->findOrFail($id);

        if ($broadcast->created_by !== auth()->id()) abort(403);

        $recipientStats = [
            'successful' => $broadcast->recipients()->whereIn('status', ['sent', 'delivered', 'read'])->count(),
            'failed'     => $broadcast->recipients()->where('status', 'failed')->count(),
            'pending'    => $broadcast->recipients()->whereIn('status', ['pending', 'queued'])->count(),
            'total'      => $broadcast->total_recipients,
        ];

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Broadcast History', 'url' => route('broadcast.history')],
            ['label' => Str::limit($broadcast->title ?? $broadcast->message, 20)],
        ];

        return view('broadcast.show', compact('broadcast', 'recipientStats', 'breadcrumbs'));
    }

    public function retry($id)
    {
        $broadcast = BroadcastMessage::findOrFail($id);

        if ($broadcast->created_by !== auth()->id()) abort(403);

        if (!in_array($broadcast->status, ['failed', 'queued'], true)) {
            return back()->with('info', 'Hanya broadcast yang gagal yang dapat dicoba ulang.');
        }

        DB::beginTransaction();
        try {
            $recipientsToRetry = $broadcast->recipients()->where('status', 'failed')->count();

            if ($recipientsToRetry === 0) {
                DB::rollBack();
                return back()->with('info', 'Tidak ada penerima yang gagal untuk dicoba ulang.');
            }

            $broadcast->recipients()->where('status', 'failed')->update([
                'status'        => 'pending',
                'error_message' => null,
                'api_response'  => null,
            ]);

            $broadcast->update(['status' => 'queued', 'error_message' => null]);

            DB::commit();

            ProcessBroadcastJob::dispatch($broadcast->id)
                ->delay(now()->addSeconds(self::JOB_DISPATCH_DELAY));

            return back()->with('success', "Broadcast diproses ulang! {$recipientsToRetry} penerima akan di-retry.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception during broadcast retry', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat mencoba ulang broadcast.');
        }
    }

    public function destroy($id)
    {
        try {
            $broadcast = BroadcastMessage::findOrFail($id);

            if ($broadcast->created_by !== auth()->id()) abort(403);

            if ($broadcast->status === 'processing') {
                return back()->with('error', 'Tidak dapat menghapus broadcast yang sedang diproses.');
            }

            if ($broadcast->media_url) {
                $path = str_replace(Storage::disk('public')->url(''), '', $broadcast->media_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $broadcast->delete();

            return redirect()->route('broadcast.history')->with('success', 'Broadcast berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Exception during broadcast deletion', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus broadcast.');
        }
    }

    // ============================================================
    // INTERNAL HELPER METHODS
    // ============================================================

    protected function handleMediaUpload(Request $request): array
    {
        $mediaData = ['media_type' => null, 'media_url' => null, 'media_filename' => null];

        if ($request->hasFile('media_file')) {
            $file                        = $request->file('media_file');
            $path                        = $file->store('broadcast-media', 'public');
            $mediaData['media_url']      = $path;
            $mediaData['media_type']     = $this->getMediaType($file->getMimeType());
            $mediaData['media_filename'] = $file->getClientOriginalName();
        }

        return $mediaData;
    }

    protected function getMediaType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            default                               => 'document',
        };
    }

    protected function collectContactRecipients(Request $request): array
    {
        $recipients = [];

        switch ($request->target_selection) {

            case 'group':
                $group = Group::query()
                    ->with([
                        'users'       => fn ($q) => $q->whereNotNull('phone'),
                        'crewMembers' => fn ($q) => $q->whereNotNull('phone'),
                        'contacts'    => fn ($q) => $q->where('is_active', true)->whereNotNull('whatsapp_number'),
                    ])
                    ->find($request->group_id);

                if ($group) {
                    foreach ($group->users as $user) {
                        $recipients[] = [
                            'recipient_type' => 'user',
                            'recipient_id'   => $user->id,
                            'phone_number'   => $user->phone,
                            'recipient_name' => $user->name,
                        ];
                    }
                    foreach ($group->crewMembers as $crew) {
                        $recipients[] = [
                            'recipient_type' => 'crew_member',
                            'recipient_id'   => $crew->id,
                            'phone_number'   => $crew->phone,
                            'recipient_name' => $crew->name,
                        ];
                    }
                    foreach ($group->contacts as $contact) {
                        $recipients[] = [
                            'recipient_type' => 'contact',
                            'recipient_id'   => $contact->id,
                            'phone_number'   => $contact->whatsapp_number,
                            'recipient_name' => $contact->name,
                        ];
                    }
                }
                break;

            case 'manual':
                if (!empty($request->user_ids)) {
                    User::whereIn('id', $request->user_ids)
                        ->whereNotNull('phone')
                        ->each(fn ($u) => $recipients[] = [
                            'recipient_type' => 'user',
                            'recipient_id'   => $u->id,
                            'phone_number'   => $u->phone,
                            'recipient_name' => $u->name,
                        ]);
                }

                if (!empty($request->crew_member_ids)) {
                    CrewMember::whereIn('id', $request->crew_member_ids)
                        ->whereNotNull('phone')
                        ->each(fn ($c) => $recipients[] = [
                            'recipient_type' => 'crew_member',
                            'recipient_id'   => $c->id,
                            'phone_number'   => $c->phone,
                            'recipient_name' => $c->name,
                        ]);
                }

                if (!empty($request->contact_ids)) {
                    Contact::whereIn('id', $request->contact_ids)
                        ->where('is_active', true)
                        ->whereNotNull('whatsapp_number')
                        ->each(fn ($c) => $recipients[] = [
                            'recipient_type' => 'contact',
                            'recipient_id'   => $c->id,
                            'phone_number'   => $c->whatsapp_number,
                            'recipient_name' => $c->name,
                        ]);
                }
                break;

            case 'all':
                User::whereNotNull('phone')
                    ->each(fn ($u) => $recipients[] = [
                        'recipient_type' => 'user',
                        'recipient_id'   => $u->id,
                        'phone_number'   => $u->phone,
                        'recipient_name' => $u->name,
                    ]);

                CrewMember::whereNotNull('phone')
                    ->each(fn ($c) => $recipients[] = [
                        'recipient_type' => 'crew_member',
                        'recipient_id'   => $c->id,
                        'phone_number'   => $c->phone,
                        'recipient_name' => $c->name,
                    ]);

                Contact::active()
                    ->whereNotNull('whatsapp_number')
                    ->each(fn ($c) => $recipients[] = [
                        'recipient_type' => 'contact',
                        'recipient_id'   => $c->id,
                        'phone_number'   => $c->whatsapp_number,
                        'recipient_name' => $c->name,
                    ]);
                break;
        }

        // Deduplikasi berdasarkan nomor telepon/WA
        $unique = [];
        foreach ($recipients as $r) {
            $unique[$r['phone_number']] = $r;
        }

        return array_values($unique);
    }
}
