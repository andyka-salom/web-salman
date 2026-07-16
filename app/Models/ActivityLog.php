<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    const UPDATED_AT = null; // Activity logs don't update

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction(Builder $query, $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeBySubjectType(Builder $query, $type): Builder
    {
        return $query->where('subject_type', $type);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent(Builder $query, $limit = 50): Builder
    {
        return $query->latest()->limit($limit);
    }

    // Static Methods for Logging
    public static function log(string $action, $subject = null, string $description = null, array $properties = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    // Helper methods for common actions
    public static function logLogin(): self
    {
        return static::log('login', null, 'User logged in');
    }

    public static function logLogout(): self
    {
        return static::log('logout', null, 'User logged out');
    }

    public static function logView($subject): self
    {
        $name = $subject->name
            ?? $subject->title
            ?? 'item';

        return static::log('view', $subject, "Viewed {$name}");
    }

    public static function logCreate($subject): self
    {
        $name = static::getSubjectName($subject);
        return static::log('create', $subject, "Created {$name}");
    }

    public static function logUpdate($subject): self
    {
        $name = static::getSubjectName($subject);
        return static::log('update', $subject, "Updated {$name}");
    }

    public static function logDelete($subject): self
    {
        $name = static::getSubjectName($subject);
        return static::log('delete', $subject, "Deleted {$name}");
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'login', 'view' => 'blue',
            'create' => 'green',
            'update' => 'yellow',
            'delete' => 'red',
            'logout' => 'gray',
            default => 'gray',
        };
    }
}
