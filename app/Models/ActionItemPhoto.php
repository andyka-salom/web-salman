<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionItemPhoto extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'action_item_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'photo_type',
        'order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function actionItem()
    {
        return $this->belongsTo(ActionItem::class);
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }
}
