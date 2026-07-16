<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VesselAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vessel_id',
        'crew_member_id',
        'assigned_by',
        'assigned_at',
        'unassigned_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }

    public function crewMember()
    {
        return $this->belongsTo(CrewMember::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->whereNull('unassigned_at');
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false)
                    ->whereNotNull('unassigned_at');
    }

    public function scopeByVessel($query, $vesselId)
    {
        return $query->where('vessel_id', $vesselId);
    }

    public function scopeByCrewMember($query, $crewMemberId)
    {
        return $query->where('crew_member_id', $crewMemberId);
    }

    public function scopeCurrentlyAssigned($query)
    {
        return $query->where('is_active', true)
                    ->whereNull('unassigned_at')
                    ->where('assigned_at', '<=', now());
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('assigned_at', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('assigned_at', '<=', $endDate)
                     ->where(function($q3) use ($startDate) {
                         $q3->whereNull('unassigned_at')
                            ->orWhere('unassigned_at', '>=', $startDate);
                     });
              });
        });
    }

    // Methods
    public function unassign(?string $notes = null): bool
    {
        // Set unassigned_at and is_active in single operation
        $this->unassigned_at = now();
        $this->is_active = false;

        if ($notes) {
            $this->notes = $this->notes ? $this->notes . "\n" . $notes : $notes;
        }

        return $this->save();
    }

    public function isCurrentlyAssigned(): bool
    {
        return $this->is_active &&
               is_null($this->unassigned_at) &&
               $this->assigned_at <= now();
    }

    public function getDurationDays(): ?int
    {
        $endDate = $this->unassigned_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }

    public function getDurationAttribute(): ?int
    {
        return $this->getDurationDays();
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_active && is_null($this->unassigned_at)) {
            return 'active';
        }

        if (!$this->is_active && !is_null($this->unassigned_at)) {
            return 'completed';
        }

        return 'inactive';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'completed' => 'secondary',
            'inactive' => 'danger',
            default => 'secondary'
        };
    }
}
