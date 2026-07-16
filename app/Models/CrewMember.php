<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrewMember extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'nik',
        'position',
        'gender',
        'birth_date',
        'age',
        'blood_type',
        'phone',
        'address',
        'mcu_valid_until',
        'health_category',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'birth_date'      => 'date',
        'mcu_valid_until' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Accessors appended to JSON/array output.
     * Includes MCU and health category accessors so they're available
     * in AJAX responses (e.g. showCheckup detail modal).
     */
    protected $appends = [
        'initials',
        'mcu_status',
        'mcu_status_color',
        'mcu_days_left',
        'health_category_label',
    ];

    /**
     * Health category descriptions.
     */
    public const HEALTH_CATEGORIES = [
        'P1' => 'P1 - No medical abnormal',
        'P2' => 'P2 - Non-serious medical abnormal',
        'P3' => 'P3 - Low health risk',
        'P4' => 'P4 - Moderate health risk',
        'P5' => 'P5 - High health risk',
        'P6' => 'P6 - Limitation to normal work',
        'P7' => 'P7 - Unable to work',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vesselAssignments()
    {
        return $this->hasMany(VesselAssignment::class);
    }

    public function vessels()
    {
        return $this->belongsToMany(Vessel::class, 'vessel_assignments')
                    ->withPivot([
                        'id', 'assigned_by', 'assigned_at',
                        'unassigned_at', 'is_active', 'notes',
                        'created_at', 'updated_at',
                    ])
                    ->withTimestamps()
                    ->using(VesselAssignment::class);
    }

    public function currentVesselAssignment()
    {
        return $this->hasOne(VesselAssignment::class)
                    ->where('is_active', true)
                    ->whereNull('unassigned_at')
                    ->latest('assigned_at');
    }

    public function activeAssignment()
    {
        return $this->currentVesselAssignment();
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function reportedCermatReports()
    {
        return $this->hasMany(CermatReport::class, 'reporter_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByVessel($query, $vesselId)
    {
        return $query->whereHas('currentVesselAssignment', function ($q) use ($vesselId) {
            $q->where('vessel_id', $vesselId);
        });
    }

    public function scopeAssignedToVessel($query, $vesselId)
    {
        return $query->whereHas('vesselAssignments', function ($q) use ($vesselId) {
            $q->where('vessel_id', $vesselId)
              ->where('is_active', true)
              ->whereNull('unassigned_at');
        });
    }

    public function scopeUnassigned($query)
    {
        return $query->whereDoesntHave('currentVesselAssignment');
    }

    public function scopeAssigned($query)
    {
        return $query->whereHas('currentVesselAssignment');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByHealthCategory($query, $category)
    {
        return $query->where('health_category', $category);
    }

    /** Crew whose MCU expires within the given number of days (default 30). */
    public function scopeMcuExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('mcu_valid_until')
                     ->whereBetween('mcu_valid_until', [now(), now()->addDays($days)]);
    }

    /** Crew whose MCU has already expired. */
    public function scopeMcuExpired($query)
    {
        return $query->whereNotNull('mcu_valid_until')
                     ->where('mcu_valid_until', '<', now());
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('nik', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
        });
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function getCurrentVesselAttribute(): ?Vessel
    {
        return $this->currentVesselAssignment?->vessel;
    }

    public function getAssignmentHistoryAttribute()
    {
        return $this->vesselAssignments()
                    ->with(['vessel', 'assignedBy'])
                    ->orderBy('assigned_at', 'desc')
                    ->get();
    }

    public function getAssignmentStatusAttribute(): string
    {
        return $this->isAssignedToVessel() ? 'assigned' : 'unassigned';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Human-readable label for the health category.
     * e.g. "P1 - Fit for all duties"
     * Returns '-' when no category is set.
     */
    public function getHealthCategoryLabelAttribute(): string
    {
        if (! $this->health_category) {
            return '-';
        }
        return self::HEALTH_CATEGORIES[$this->health_category] ?? $this->health_category;
    }

    /**
     * MCU status: 'valid' | 'expiring_soon' | 'expired' | 'none'
     */
    public function getMcuStatusAttribute(): string
    {
        if (! $this->mcu_valid_until) {
            return 'none';
        }
        $daysLeft = now()->diffInDays($this->mcu_valid_until, false);

        if ($daysLeft < 0) {
            return 'expired';
        }
        if ($daysLeft <= 30) {
            return 'expiring_soon';
        }
        return 'valid';
    }

    /**
     * Bootstrap badge color for MCU status.
     * Used in Blade templates: bg-{{ $crew->mcu_status_color }}
     */
    public function getMcuStatusColorAttribute(): string
    {
        return match ($this->mcu_status) {
            'valid'         => 'success',
            'expiring_soon' => 'warning',
            'expired'       => 'danger',
            default         => 'secondary',
        };
    }

    /**
     * Remaining days until MCU expiry (negative = already expired).
     * Returns null when no MCU date is set.
     */
    public function getMcuDaysLeftAttribute(): ?int
    {
        if (! $this->mcu_valid_until) {
            return null;
        }
        return (int) now()->diffInDays($this->mcu_valid_until, false);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function assignToVessel(string $vesselId, ?string $assignedBy = null, ?string $notes = null): VesselAssignment
    {
        $this->unassignFromCurrentVessel('Auto-unassigned before new assignment');

        return $this->vesselAssignments()->create([
            'vessel_id'     => $vesselId,
            'assigned_by'   => $assignedBy ?? auth()->id(),
            'assigned_at'   => now(),
            'is_active'     => true,
            'unassigned_at' => null,
            'notes'         => $notes,
        ]);
    }

    public function unassignFromCurrentVessel(?string $notes = null): bool
    {
        $currentAssignment = $this->currentVesselAssignment;
        if ($currentAssignment) {
            return $currentAssignment->unassign($notes);
        }
        return false;
    }

    public function transferToVessel(string $newVesselId, ?string $assignedBy = null, ?string $notes = null): VesselAssignment
    {
        $oldVesselName = $this->currentVessel?->name ?? 'Unknown';
        $this->unassignFromCurrentVessel("Transferred to vessel ID: {$newVesselId}");
        $finalNotes = $notes ?? "Transferred from {$oldVesselName}";
        return $this->assignToVessel($newVesselId, $assignedBy, $finalNotes);
    }

    public function isAssignedToVessel(?string $vesselId = null): bool
    {
        if ($vesselId) {
            return $this->currentVesselAssignment()->where('vessel_id', $vesselId)->exists();
        }
        return $this->currentVesselAssignment()->exists();
    }

    public function getAssignmentHistory()
    {
        return $this->vesselAssignments()
                    ->with(['vessel.company', 'assignedBy'])
                    ->orderBy('assigned_at', 'desc')
                    ->get();
    }

    public function getCurrentAssignmentDuration(): ?int
    {
        return $this->currentVesselAssignment?->getDurationDays();
    }
}
