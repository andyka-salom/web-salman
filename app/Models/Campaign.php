<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Campaign extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'media',
        'media_type',
        'summary',
        'content',
        'published_at',
        'category',
        'is_published',
        'is_featured',
        'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_published', false);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, $term): Builder
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('summary', 'like', "%{$term}%");
        });
    }

    public function scopePopular(Builder $query, $limit = 10): Builder
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    public function scopeRecent(Builder $query, $limit = 10): Builder
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }

    // Methods
    public function publish(): bool
    {
        return $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): bool
    {
        return $this->update(['is_published' => false]);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function toggleFeatured(): bool
    {
        return $this->update(['is_featured' => !$this->is_featured]);
    }

    public function getReadTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return ceil($words / 200); // Assuming 200 words per minute
    }

    public function getExcerptAttribute(): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), 150);
    }

    public function getMediaUrlAttribute(): ?string
    {
        return $this->media ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->media) : null;
    }

    // Mutators
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = \Illuminate\Support\Str::slug($value);
    }
}
