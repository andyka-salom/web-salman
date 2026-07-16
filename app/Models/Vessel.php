<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Vessel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'coordinator_id',
        'name',
        'type',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['status', 'status_color'];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function vesselAssignments()
    {
        return $this->hasMany(VesselAssignment::class);
    }

    public function crewMembers()
    {
        return $this->belongsToMany(CrewMember::class, 'vessel_assignments')
                    ->withPivot([
                        'id',
                        'assigned_by',
                        'assigned_at',
                        'unassigned_at',
                        'is_active',
                        'notes',
                        'created_at',
                        'updated_at'
                    ])
                    ->withTimestamps()
                    ->using(VesselAssignment::class);
    }

    public function currentCrewMembers()
    {
        return $this->belongsToMany(CrewMember::class, 'vessel_assignments')
                    ->wherePivot('is_active', true)
                    ->wherePivotNull('unassigned_at')
                    ->withPivot([
                        'id',
                        'assigned_by',
                        'assigned_at',
                        'unassigned_at',
                        'is_active',
                        'notes',
                        'created_at',
                        'updated_at'
                    ])
                    ->withTimestamps();
    }

    public function activeAssignments()
    {
        return $this->hasMany(VesselAssignment::class)
                    ->where('is_active', true)
                    ->whereNull('unassigned_at');
    }

    public function healthChecks()
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_vessel');
    }

    public function cermatReports()
    {
        return $this->hasMany(CermatReport::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('valid_until', '<=', now()->addDays($days))
                    ->where('valid_until', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }

    public function scopeWithCrewCount($query)
    {
        return $query->withCount(['activeAssignments as current_crew_count']);
    }

    // Accessors
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiringSoon()) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            'inactive' => 'secondary',
            default => 'secondary'
        };
    }

    public function getCurrentCrewCountAttribute(): int
    {
        return $this->activeAssignments()->count();
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isExpiringSoon($days = 30): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return $this->valid_until->isFuture() &&
               $this->valid_until->diffInDays(now()) <= $days;
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }

        if ($this->valid_until->isPast()) {
            return 0;
        }

        return $this->valid_until->diffInDays(now());
    }

    public function assignCrewMember(string $crewMemberId, ?string $assignedBy = null, ?string $notes = null): VesselAssignment
    {
        // Get crew member and unassign from current vessel
        $crewMember = CrewMember::findOrFail($crewMemberId);
        $crewMember->unassignFromCurrentVessel("Auto-unassigned before new assignment to {$this->name}");

        return $this->vesselAssignments()->create([
            'crew_member_id' => $crewMemberId,
            'assigned_by' => $assignedBy ?? auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
            'unassigned_at' => null, // Explicitly set to null
            'notes' => $notes,
        ]);
    }

    public function unassignCrewMember(string $crewMemberId, ?string $notes = null): bool
    {
        $assignment = $this->activeAssignments()
                          ->where('crew_member_id', $crewMemberId)
                          ->first();

        if ($assignment) {
            return $assignment->unassign($notes);
        }

        return false;
    }

    public function getCrewByPosition(string $position)
    {
        return $this->currentCrewMembers()
                    ->where('crew_members.position', $position)
                    ->get();
    }

    public function hasCrewMember(string $crewMemberId): bool
    {
        return $this->activeAssignments()
                    ->where('crew_member_id', $crewMemberId)
                    ->exists();
    }

    public function getCrewPositionSummary(): array
    {
        return $this->currentCrewMembers()
                    ->select('position', \DB::raw('count(*) as count'))
                    ->groupBy('position')
                    ->pluck('count', 'position')
                    ->toArray();
    }

    public function crewUsers()
    {
        return User::whereHas('coordinatedVessels', function ($query) {
            $query->where('vessels.id', $this->id);
        });
    }
}
