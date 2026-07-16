<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntityFunction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'level',
        'path',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(EntityFunction::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(EntityFunction::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Methods
    public function updatePath()
    {
        if ($this->parent) {
            $this->path = $this->parent->path . '/' . $this->id;
        } else {
            $this->path = '/' . $this->id;
        }
        $this->save();
    }

    public function getAncestors()
    {
        if (!$this->path) return collect();

        $ids = array_filter(explode('/', $this->path));
        return static::whereIn('id', $ids)->get();
    }
}
