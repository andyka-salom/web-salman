<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastRecipient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'broadcast_message_id',
        'recipient_type',
        'recipient_id',
        'phone_number',
        'recipient_name',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        'api_response',
    ];

    protected $casts = [
        'api_response' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Relasi: Pesan Broadcast induk
     */
    public function broadcastMessage(): BelongsTo
    {
        return $this->belongsTo(BroadcastMessage::class);
    }

    // Optional: MorphTo relationship to easily fetch the actual user/crew member object
    public function recipient()
    {
        return $this->morphTo();
    }
}
