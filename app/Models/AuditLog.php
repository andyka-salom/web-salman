<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    const UPDATED_AT = null; // Audit logs don't update

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEvent(Builder $query, $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeByModel(Builder $query, $modelType): Builder
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeCreated(Builder $query): Builder
    {
        return $query->where('event', 'created');
    }

    public function scopeUpdated(Builder $query): Builder
    {
        return $query->where('event', 'updated');
    }

    public function scopeDeleted(Builder $query): Builder
    {
        return $query->where('event', 'deleted');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // Methods
    public function getChanges(): array
    {
        if ($this->event !== 'updated' || !$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    public function getEventColorAttribute(): string
    {
        return match($this->event) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'restored' => 'yellow',
            default => 'gray',
        };
    }
}
