<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'jabatan',
        'photo_path',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'company_id',
        'entity_function_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_active' => 'boolean',
        'failed_login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function entityFunction()
    {
        return $this->belongsTo(EntityFunction::class);
    }

    public function reportedCermatReports()
    {
        return $this->hasMany(CermatReport::class, 'reporter_id');
    }

    public function supervisedCermatReports()
    {
        return $this->hasMany(CermatReport::class, 'line_supervisor_id');
    }

    public function hsseReviews()
    {
        return $this->hasMany(CermatReport::class, 'hsse_officer_id');
    }

    public function closedOutReports()
    {
        return $this->hasMany(CermatReport::class, 'close_out_performer_id');
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class, 'responsible_id');
    }

    public function coordinatedVessels()
    {
        return $this->hasMany(Vessel::class, 'coordinator_id');
    }

    public function broadcastMessages()
    {
        return $this->hasMany(BroadcastMessage::class, 'created_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('jabatan', $role);
    }

    public function scopeNotLocked($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('locked_until')
              ->orWhere('locked_until', '<', now());
        });
    }

    // Permission Scopes
    public function scopePermission($query, $permissions)
    {
        return $query->whereHas('permissions', function ($q) use ($permissions) {
            $q->whereIn('name', (array) $permissions);
        });
    }

    public function scopeRoleScope($query, $roles)
    {
        return $query->whereHas('roles', function ($q) use ($roles) {
            $q->whereIn('name', (array) $roles);
        });
    }

    // Methods
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    public function unlockAccount(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }
    }

    public function resetFailedLogins(): void
    {
        $this->update(['failed_login_attempts' => 0]);
    }

    public function recordLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_attempts' => 0,
        ]);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }

    // Permission Helper Methods
    // public function hasAnyRole(...$roles): bool
    // {
    //     return $this->roles()->whereIn('name', $roles)->exists();
    // }

    public function hasAllRoles(...$roles): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return count(array_intersect($userRoles, $roles)) === count($roles);
    }

    public function assignRoleIfNotExists(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->assignRole($role);
        }
    }

    public function syncRolesWithLogging(array $roles): void
    {
        $oldRoles = $this->roles()->pluck('name')->toArray();
        $this->syncRoles($roles);

        // Optional: Log perubahan roles
        activity()
            ->causedBy($this)
            ->performedOn($this)
            ->withProperties([
                'old_roles' => $oldRoles,
                'new_roles' => $roles,
            ])
            ->log('roles_updated');
    }
    public function vessels()
    {
        return $this->coordinatedVessels();
    }
    public function isCoordinator(): bool
    {
        return $this->coordinatedVessels()->exists();
    }
}
