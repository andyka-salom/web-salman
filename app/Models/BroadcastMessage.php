<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastMessage extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'message',
        'target_type',
        'target_data',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'media_type',
        'media_url',
        'media_filename',
        'total_recipients',
        'sent_count',
        'failed_count',
        'pending_count',
        'success_rate',
        'api_response',
        'error_message',
    ];

    protected $casts = [
        'target_data' => 'array',
        'api_response' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'success_rate' => 'float',
    ];

    /**
     * Relasi: Pembuat Broadcast
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi: Penerima Broadcast
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(BroadcastRecipient::class);
    }
}
