<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WhatsAppGroupCache extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'whatsapp_groups_cache';

    protected $fillable = [
        'group_id',
        'group_name',
        'members',
        'member_count',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'members' => 'array',
        'member_count' => 'integer',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
