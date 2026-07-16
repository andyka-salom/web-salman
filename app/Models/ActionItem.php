<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class ActionItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // Status Constants
    public const STATUS_DO = 'do';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANT_DO = 'cannot_do';

    protected $fillable = [
        'cermat_report_id',
        'responsible_id',
        'action_category_id',
        'description',
        'target_date',
        'current_target_date', // Pastikan ini tetap fillable
        'extension_count',
        'extend_date_1',
        'extend_reason_1',
        'extend_date_2',
        'extend_reason_2',
        'extend_date_3',
        'extend_reason_3',
        'status',
        'completion_notes',
        'completed_at',
        'is_overdue',
    ];

    protected $casts = [
        'target_date' => 'date',
        'current_target_date' => 'date',
        'extend_date_1' => 'date',
        'extend_date_2' => 'date',
        'extend_date_3' => 'date',
        'extension_count' => 'integer',
        'completed_at' => 'datetime',
        'is_overdue' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     * Mengatur current_target_date sama dengan target_date saat item dibuat.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($actionItem) {
            // Jika current_target_date kosong, gunakan target_date.
            if (empty($actionItem->current_target_date) && !empty($actionItem->target_date)) {
                $actionItem->current_target_date = $actionItem->target_date;
            }
        });
    }

    // Relationships
    public function cermatReport()
    {
        return $this->belongsTo(CermatReport::class);
    }

    // ... (sisa relasi dan scope) ...

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function actionCategory()
    {
        return $this->belongsTo(ActionCategory::class);
    }

    public function category()
    {
        return $this->actionCategory();
    }

    public function photos()
    {
        return $this->hasMany(ActionItemPhoto::class);
    }

    // Scopes
    public function scopeTodo(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DO); // Diubah dari STATUS_TODO menjadi STATUS_DO
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('is_overdue', true)
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANT_DO]);
    }

    // Methods
    public function extend(string $newDate, string $reason): bool
    {
        if ($this->extension_count >= 3) {
            return false;
        }

        $extensionNumber = $this->extension_count + 1;

        return $this->update([
            "extend_date_{$extensionNumber}" => $newDate,
            "extend_reason_{$extensionNumber}" => $reason,
            'current_target_date' => $newDate,
            'extension_count' => $extensionNumber,
        ]);
    }

    public function checkOverdue(): void
    {
        $targetDate = $this->current_target_date ?? $this->target_date;

        $isOverdue = $targetDate->isPast()
            && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANT_DO]);

        if ($isOverdue !== $this->is_overdue) {
            $this->update(['is_overdue' => $isOverdue]);
        }
    }

    public function canExtend(): bool
    {
        return $this->extension_count < 3
            && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANT_DO]);
    }

    public function getDaysRemainingAttribute(): int
    {
        $targetDate = $this->current_target_date ?? $this->target_date;
        return now()->diffInDays($targetDate, false);
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue) return 'danger';

        return match($this->status) {
            self::STATUS_DO => 'secondary',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANT_DO => 'danger',
            default => 'secondary',
        };
    }
}
