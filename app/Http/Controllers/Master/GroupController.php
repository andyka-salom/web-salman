<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\CrewMember;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\GroupRequest;

class GroupController extends Controller
{
    use AuthorizesRequests;

    protected string $cacheKey      = 'groups';
    protected int    $cacheDuration = 3600;

    // -------------------------------------------------------
    // Index / DataTables
    // -------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorize('manage groups');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Groups'],
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        $stats = $this->getGroupStats();

        return view('master.groups.index', compact('breadcrumbs', 'stats'));
    }

    protected function getDatatablesData(Request $request)
    {
        $query = Group::select(['id', 'group_name', 'description', 'is_active', 'member_count', 'created_at']);

        if ($request->has('search') && !empty($request->search['value'])) {
            $query->search($request->search['value']);
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', fn ($g) =>
                $g->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>'
            )
            ->addColumn('member_count', fn ($g) =>
                '<span class="badge badge-info">' . $g->member_count . ' member' . ($g->member_count != 1 ? 's' : '') . '</span>'
            )
            ->addColumn('action', fn ($g) =>
                view('master.groups.partials.actions', ['group' => $g])->render()
            )
            ->rawColumns(['status', 'member_count', 'action'])
            ->make(true);
    }

    // -------------------------------------------------------
    // CRUD
    // -------------------------------------------------------

    public function create()
    {
        $this->authorize('manage groups');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Groups', 'url' => route('groups.index')],
            ['label' => 'Create'],
        ];

        return view('master.groups.create', compact('breadcrumbs'));
    }

    public function store(GroupRequest $request)
    {
        $this->authorize('manage groups');

        try {
            DB::beginTransaction();

            $data               = $request->validated();
            $data['is_active']  = $request->has('is_active');
            $data['member_count'] = 0;

            $group = Group::create($data);
            $this->clearGroupCache();

            DB::commit();
            Log::info('Group created', ['group_id' => $group->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Group created successfully.',
                'redirect' => route('groups.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group creation failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create group: ' . $e->getMessage()], 500);
        }
    }

    public function show(Group $group)
    {
        $this->authorize('manage groups');

        $group->load(['users', 'crewMembers', 'contracts', 'contacts']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Groups', 'url' => route('groups.index')],
            ['label' => $group->group_name],
        ];

        return view('master.groups.show', compact('group', 'breadcrumbs'));
    }

    public function edit(Group $group)
    {
        $this->authorize('manage groups');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Groups', 'url' => route('groups.index')],
            ['label' => 'Edit ' . $group->group_name],
        ];

        return view('master.groups.create', compact('group', 'breadcrumbs'));
    }

    public function update(GroupRequest $request, Group $group)
    {
        $this->authorize('manage groups');

        try {
            DB::beginTransaction();

            $data              = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $group->update($data);
            $this->clearGroupCache($group->id);

            DB::commit();
            Log::info('Group updated', ['group_id' => $group->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Group updated successfully.',
                'redirect' => route('groups.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group update failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update group: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Group $group)
    {
        $this->authorize('manage groups');

        try {
            DB::beginTransaction();

            $group->users()->detach();
            $group->crewMembers()->detach();
            $group->contracts()->detach();
            $group->contacts()->detach();
            $group->delete();

            $this->clearGroupCache($group->id);

            DB::commit();
            Log::info('Group deleted', ['group_id' => $group->id, 'user_id' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Group deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group deletion failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to delete group: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Group $group)
    {
        $this->authorize('manage groups');

        try {
            $group->update(['is_active' => !$group->is_active]);
            $this->clearGroupCache($group->id);

            return response()->json([
                'success'   => true,
                'message'   => 'Group status updated successfully.',
                'is_active' => $group->is_active,
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }

    // -------------------------------------------------------
    // Member Management
    // -------------------------------------------------------

    public function members(Group $group)
    {
        $this->authorize('manage groups');

        $group->load(['users', 'crewMembers', 'contracts', 'contacts']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Groups', 'url' => route('groups.index')],
            ['label' => $group->group_name, 'url' => route('groups.show', $group)],
            ['label' => 'Manage Members'],
        ];

        $members   = $group->getAllMembers();
        $users     = User::active()->orderBy('name')->get();
        $crews     = CrewMember::where('is_active', true)->orderBy('name')->get();
        $contracts = Contract::orderBy('nama_kontraktor')->get();
        $contacts  = Contact::active()->orderBy('name')->get();

        $existingUserIds     = $group->users->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $existingCrewIds     = $group->crewMembers->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $existingContractIds = $group->contracts->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $existingContactIds  = $group->contacts->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        return view('master.groups.members', compact(
            'group', 'breadcrumbs', 'members',
            'users', 'crews', 'contracts', 'contacts',
            'existingUserIds', 'existingCrewIds', 'existingContractIds', 'existingContactIds'
        ));
    }

    public function addMembersBatch(Request $request, Group $group)
    {
        $this->authorize('manage groups');

        $request->validate([
            'selected_members'   => 'nullable|array',
            'selected_members.*' => 'required|string',
        ]);

        if (empty($request->selected_members)) {
            return response()->json(['success' => false, 'message' => 'No members selected.'], 400);
        }

        try {
            DB::beginTransaction();

            $added   = 0;
            $skipped = 0;

            foreach ($request->selected_members as $memberString) {
                if (!str_contains($memberString, '|')) continue;

                [$type, $id] = explode('|', $memberString, 2);

                $member = match ($type) {
                    'user'     => User::find($id),
                    'crew'     => CrewMember::find($id),
                    'contract' => Contract::find($id),
                    'contact'  => Contact::find($id),
                    default    => null,
                };

                if (!$member) continue;

                if ($group->hasMember($member)) {
                    $skipped++;
                    continue;
                }

                $group->addMember($member);
                $added++;
            }

            $group->updateMemberCount();
            $this->clearGroupCache($group->id);

            DB::commit();

            Log::info('Batch members added to group', [
                'group_id' => $group->id,
                'added'    => $added,
                'skipped'  => $skipped,
                'user_id'  => auth()->id(),
            ]);

            $message = $added > 0 ? "{$added} member(s) added successfully." : "No new members added.";
            if ($skipped > 0) $message .= " {$skipped} skipped (already in group).";

            return response()->json([
                'success' => true,
                'message' => $message,
                'added'   => $added,
                'skipped' => $skipped,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch add member failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to add members: ' . $e->getMessage()], 500);
        }
    }

    public function removeMember(Request $request, Group $group)
    {
        $this->authorize('manage groups');

        $request->validate([
            'member_type' => 'required|in:user,crew,contract,contact',
            'member_id'   => 'required',
        ]);

        try {
            DB::beginTransaction();

            match ($request->member_type) {
                'user'     => $group->users()->detach($request->member_id),
                'crew'     => $group->crewMembers()->detach($request->member_id),
                'contract' => $group->contracts()->detach($request->member_id),
                'contact'  => $group->contacts()->detach($request->member_id),
            };

            $group->updateMemberCount();
            $this->clearGroupCache($group->id);

            DB::commit();

            Log::info('Member removed from group', [
                'group_id'    => $group->id,
                'member_type' => $request->member_type,
                'member_id'   => $request->member_id,
                'user_id'     => auth()->id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Member removed successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove member failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to remove member: ' . $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    protected function getGroupStats(): array
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, fn () => [
            'total'    => Group::count(),
            'active'   => Group::active()->count(),
            'inactive' => Group::where('is_active', false)->count(),
        ]);
    }

    protected function clearGroupCache(?string $groupId = null): void
    {
        Cache::forget("{$this->cacheKey}.stats");
        if ($groupId) Cache::forget("{$this->cacheKey}.{$groupId}");
    }
}
