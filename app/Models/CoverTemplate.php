<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class CoverTemplate extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'cover_image_path',
        'page_image_path',
        'description',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query, $limit = 10): Builder
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    // Methods
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function getCoverUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->cover_image_path);
    }

    public function getPageUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->page_image_path);
    }

    // Mutators
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = \Illuminate\Support\Str::slug($value);
    }
}
