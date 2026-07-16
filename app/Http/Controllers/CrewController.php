<?php

namespace App\Http\Controllers;

use App\Models\CrewMember;
use App\Models\Vessel;
use App\Models\VesselAssignment;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class CrewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->ajax()) {
            return $this->getCrewDataTable($request);
        }

        $companies = $user->hasRole('super-admin')
            ? Company::where('is_active', true)->orderBy('name')->get()
            : collect();

        $vessels = $user->hasRole('super-admin')
            ? Vessel::with('company')->where('is_active', true)->orderBy('name')->get()
            : Vessel::where('company_id', $user->company_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

        $positions        = $this->getPositions();
        $healthCategories = CrewMember::HEALTH_CATEGORIES;

        return view('crew.index', compact('companies', 'vessels', 'positions', 'healthCategories'));
    }

    // -------------------------------------------------------------------------
    // DataTable
    // -------------------------------------------------------------------------

    protected function getCrewDataTable(Request $request)
    {
        $user = Auth::user();

        $query = CrewMember::with([
            'company',
            'currentVesselAssignment.vessel.company',
            'currentVesselAssignment.assignedBy',
        ]);

        if (! $user->hasRole('super-admin')) {
            $query->where('company_id', $user->company_id);
        }

        if ($request->filled('company_id') && $user->hasRole('super-admin')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('vessel_id')) {
            $query->whereHas('currentVesselAssignment', function ($q) use ($request) {
                $q->where('vessel_id', $request->vessel_id);
            });
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }

        if ($request->filled('health_category')) {
            $query->where('health_category', $request->health_category);
        }

        if ($request->filled('mcu_status')) {
            match ($request->mcu_status) {
                'expired'       => $query->mcuExpired(),
                'expiring_soon' => $query->mcuExpiringSoon(),
                'valid'         => $query->whereNotNull('mcu_valid_until')
                                         ->where('mcu_valid_until', '>=', now()->addDays(30)),
                'none'          => $query->whereNull('mcu_valid_until'),
                default         => null,
            };
        }

        return DataTables::of($query)
            ->addColumn('name_avatar', function ($crew) {
                $initials = strtoupper(substr($crew->name, 0, 2));
                $colors   = ['#4361ee', '#e7515a', '#2196f3', '#009688', '#e2a03f'];
                $color    = $colors[ord($initials[0]) % 5];

                $mcuBadge = '';
                if ($crew->mcu_status !== 'none') {
                    $colorMap = [
                        'valid'         => 'success',
                        'expiring_soon' => 'warning',
                        'expired'       => 'danger',
                    ];
                    $badgeColor = $colorMap[$crew->mcu_status] ?? 'secondary';
                    $mcuBadge   = '<span class="badge bg-' . $badgeColor . ' ms-1" style="font-size:10px;">MCU</span>';
                }

                return '
                    <div class="d-flex align-items-center">
                        <div class="crew-avatar me-2" style="background:' . $color . '">
                            ' . $initials . '
                        </div>
                        <div>
                            <div class="fw-bold">' . e($crew->name) . $mcuBadge . '</div>
                            <small class="text-muted">' . e($crew->position ?: '-') . '</small>
                        </div>
                    </div>
                ';
            })
            ->addColumn('vessel_badge', function ($crew) {
                $vesselName = $crew->currentVesselAssignment?->vessel?->name;
                if ($vesselName) {
                    return '<span class="badge bg-success">' . e($vesselName) . '</span>';
                }
                return '<span class="badge bg-secondary">Unassigned</span>';
            })
            ->addColumn('company_name', function ($crew) {
                return e($crew->company?->name ?? '-');
            })
            ->addColumn('position_name', function ($crew) {
                return e($crew->position ?: '-');
            })
            ->addColumn('mcu_badge', function ($crew) {
                if (! $crew->mcu_valid_until) {
                    return '<span class="badge bg-secondary">No MCU</span>';
                }
                $colorMap = [
                    'valid'         => 'success',
                    'expiring_soon' => 'warning',
                    'expired'       => 'danger',
                ];
                $color = $colorMap[$crew->mcu_status] ?? 'secondary';
                $label = $crew->mcu_valid_until->format('d M Y');
                return '<span class="badge bg-' . $color . '">' . $label . '</span>';
            })
            ->addColumn('health_badge', function ($crew) {
                if (! $crew->health_category) {
                    return '<span class="badge bg-secondary">-</span>';
                }
                $colorMap = [
                    'P1' => 'success',
                    'P2' => 'info',
                    'P3' => 'warning',
                    'P4' => 'warning',
                    'P5' => 'danger',
                    'P6' => 'secondary',
                    'P7' => 'secondary',
                ];
                $color = $colorMap[$crew->health_category] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . e($crew->health_category) . '</span>';
            })
            ->addColumn('status_badge', function ($crew) {
                return $crew->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($crew) use ($user) {
                $canEdit = $user->hasRole('super-admin') || $crew->company_id === $user->company_id;

                if (! $canEdit) {
                    return '<span class="text-muted">No access</span>';
                }

                $crewData = $crew->only([
                    'id', 'name', 'nik', 'phone', 'position',
                    'gender', 'birth_date', 'blood_type', 'address',
                    'mcu_valid_until', 'health_category',
                    'is_active', 'company_id',
                ]);

                $crewData['birth_date']      = $crew->birth_date?->format('Y-m-d');
                $crewData['mcu_valid_until'] = $crew->mcu_valid_until?->format('Y-m-d');

                $encodedData = base64_encode(json_encode($crewData));

                $actions = '<div class="btn-group" role="group">';

                $actions .= '<button type="button" class="btn btn-sm btn-primary edit-crew"
                    data-crew="' . $encodedData . '" title="Edit">
                    <i data-feather="edit-2"></i>
                </button>';

                $assignment = $crew->currentVesselAssignment;
                $vesselName = $assignment?->vessel?->name;

                if ($assignment && $vesselName) {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning transfer-crew"
                        data-crew-id="' . $crew->id . '"
                        data-crew-name="' . htmlspecialchars($crew->name, ENT_QUOTES, 'UTF-8') . '"
                        data-vessel-name="' . htmlspecialchars($vesselName, ENT_QUOTES, 'UTF-8') . '" title="Transfer">
                        <i data-feather="repeat"></i>
                    </button>';

                    $actions .= '<button type="button" class="btn btn-sm btn-secondary unassign-crew"
                        data-crew-id="' . $crew->id . '"
                        data-crew-name="' . htmlspecialchars($crew->name, ENT_QUOTES, 'UTF-8') . '" title="Unassign">
                        <i data-feather="user-minus"></i>
                    </button>';
                } else {
                    $actions .= '<button type="button" class="btn btn-sm btn-success assign-crew"
                        data-crew-id="' . $crew->id . '"
                        data-crew-name="' . htmlspecialchars($crew->name, ENT_QUOTES, 'UTF-8') . '" title="Assign">
                        <i data-feather="anchor"></i>
                    </button>';
                }

                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-crew"
                    data-crew-id="' . $crew->id . '" title="Delete">
                    <i data-feather="trash-2"></i>
                </button>';

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['name_avatar', 'vessel_badge', 'mcu_badge', 'health_badge', 'status_badge', 'action'])
            ->make(true);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $user     = Auth::user();
        $rules    = $this->validationRules();
        $messages = $this->validationMessages();

        if ($user->hasRole('super-admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules, $messages);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $validated['created_by'] = $user->id;
            $validated['is_active']  = $request->boolean('is_active');

            if (! $user->hasRole('super-admin')) {
                $validated['company_id'] = $user->company_id;
            }

            $crew = CrewMember::create($validated);

            DB::commit();

            Log::info('Crew member created', [
                'crew_id'    => $crew->id,
                'company_id' => $crew->company_id,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member added successfully.',
                'data'    => $crew->load('company'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create crew member: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, CrewMember $crew)
    {
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $crew->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this crew member.',
            ], 403);
        }

        $rules    = $this->validationRules($crew->id);
        $messages = $this->validationMessages();

        if ($user->hasRole('super-admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules, $messages);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $validated['is_active'] = $request->boolean('is_active');

            if (! $user->hasRole('super-admin')) {
                unset($validated['company_id']);
            }

            $crew->update($validated);

            DB::commit();

            Log::info('Crew member updated', [
                'crew_id'    => $crew->id,
                'company_id' => $crew->company_id,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member updated successfully.',
                'data'    => $crew->fresh()->load('company'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update crew member: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(CrewMember $crew)
    {
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $crew->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this crew member.',
            ], 403);
        }

        if ($crew->isAssignedToVessel()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete crew member who is currently assigned to a vessel. Please unassign first.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete all related vessel assignment history first (avoid FK constraint)
            $crew->vesselAssignments()->delete();

            $crew->forceDelete();

            DB::commit();

            Log::info('Crew member deleted', [
                'crew_id'    => $crew->id,
                'company_id' => $crew->company_id,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete crew member: ' . $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Vessel Assignment
    // -------------------------------------------------------------------------

    public function assignToVessel(Request $request, CrewMember $crew)
    {
        try {
            $validated = $request->validate([
                'vessel_id' => 'required|exists:vessels,id',
                'notes'     => 'nullable|string|max:500',
            ], [
                'vessel_id.required' => 'Vessel must be selected',
                'vessel_id.exists'   => 'Selected vessel is invalid',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user   = Auth::user();
            $vessel = Vessel::findOrFail($request->vessel_id);

            if (! $user->hasRole('super-admin')) {
                if ($crew->company_id !== $user->company_id || $vessel->company_id !== $user->company_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to assign this crew member.',
                    ], 403);
                }
            }

            if ($crew->isAssignedToVessel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is already assigned to another vessel. Please unassign or transfer instead.',
                ], 422);
            }

            $crew->assignToVessel($request->vessel_id, $user->id, $request->notes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Crew member assigned to {$vessel->name} successfully.",
                'data'    => $crew->fresh()->load('currentVesselAssignment.vessel'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign: ' . $e->getMessage()], 500);
        }
    }

    public function unassignFromVessel(Request $request, CrewMember $crew)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (! $user->hasRole('super-admin') && $crew->company_id !== $user->company_id) {
                return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
            }

            if (! $crew->isAssignedToVessel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is not currently assigned to any vessel.',
                ], 422);
            }

            $vesselName = $crew->currentVesselAssignment->vessel?->name ?? 'Unknown Vessel';

            $crew->unassignFromCurrentVessel($request->notes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Crew member unassigned from {$vesselName} successfully.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to unassign: ' . $e->getMessage()], 500);
        }
    }

    public function transferToVessel(Request $request, CrewMember $crew)
    {
        try {
            $request->validate([
                'new_vessel_id' => 'required|exists:vessels,id',
                'notes'         => 'nullable|string|max:500',
            ], [
                'new_vessel_id.required' => 'New vessel must be selected',
                'new_vessel_id.exists'   => 'Selected vessel is invalid',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user      = Auth::user();
            $newVessel = Vessel::findOrFail($request->new_vessel_id);

            if (! $crew->isAssignedToVessel()) {
                return response()->json(['success' => false, 'message' => 'Crew is not assigned to any vessel.'], 422);
            }

            $currentAssignment = $crew->currentVesselAssignment;
            $oldVesselName     = $currentAssignment->vessel?->name ?? 'Unknown';

            if ($currentAssignment->vessel_id == $request->new_vessel_id) {
                return response()->json(['success' => false, 'message' => 'Crew is already on this vessel.'], 422);
            }

            if (! $user->hasRole('super-admin')) {
                if ($crew->company_id !== $user->company_id || $newVessel->company_id !== $user->company_id) {
                    return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
                }
            }

            $crew->transferToVessel($request->new_vessel_id, $user->id, $request->notes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Crew transferred from {$oldVesselName} to {$newVessel->name} successfully.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to transfer: ' . $e->getMessage()], 500);
        }
    }

    public function getAssignmentHistory(CrewMember $crew)
    {
        $user = Auth::user();

        if (! $user->hasRole('super-admin') && $crew->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $crew->getAssignmentHistory(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function validationRules(?string $crewId = null): array
    {
        $nikUnique = 'required|string|max:50|unique:crew_members,nik';
        if ($crewId) {
            $nikUnique .= ',' . $crewId;
        }

        return [
            'name'            => 'required|string|max:255',
            'nik'             => $nikUnique,
            'position'        => 'nullable|string|max:255',
            'gender'          => 'nullable|in:male,female',
            'birth_date'      => 'nullable|date|before:today',
            'blood_type'      => 'nullable|in:A,B,AB,O',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'mcu_valid_until' => 'required|date',
            'health_category' => 'required|in:P1,P2,P3,P4,P5,P6,P7',
            'is_active'       => 'boolean',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'name.required'            => 'Crew name is required',
            'name.max'                 => 'Crew name cannot exceed 255 characters',
            'nik.required'             => 'NIK is required',
            'nik.unique'               => 'NIK is already registered in the system',
            'nik.max'                  => 'NIK cannot exceed 50 characters',
            'gender.in'                => 'Gender must be male or female',
            'birth_date.date'          => 'Invalid birth date format',
            'birth_date.before'        => 'Birth date must be in the past',
            'blood_type.in'            => 'Blood type must be A, B, AB, or O',
            'phone.max'                => 'Phone number cannot exceed 20 characters',
            'address.max'              => 'Address cannot exceed 500 characters',
            'mcu_valid_until.required' => 'MCU valid until date is required',
            'mcu_valid_until.date'     => 'Invalid MCU date format',
            'health_category.required' => 'Health category is required',
            'health_category.in'       => 'Invalid health category',
            'company_id.required'      => 'Company must be selected',
            'company_id.exists'        => 'Selected company is invalid',
        ];
    }

    protected function getPositions(): array
    {
        return [
            'Captain'              => 'Captain',
            'Chief Officer'        => 'Chief Officer',
            'Second Officer'       => 'Second Officer',
            'Third Officer'        => 'Third Officer',
            'Chief Engineer'       => 'Chief Engineer',
            'Second Engineer'      => 'Second Engineer',
            'Third Engineer'       => 'Third Engineer',
            'Bosun'                => 'Bosun',
            'AB (Able Seaman)'     => 'AB (Able Seaman)',
            'OS (Ordinary Seaman)' => 'OS (Ordinary Seaman)',
            'Oiler'                => 'Oiler',
            'Fitter'               => 'Fitter',
            'Cook'                 => 'Cook',
            'Steward'              => 'Steward',
            'Operator'             => 'Operator',
            'Driver'               => 'Driver',
            'Rigger'               => 'Rigger',
            'Helper'               => 'Helper',
            'Others'               => 'Others',
        ];
    }
}