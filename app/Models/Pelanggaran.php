<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelanggaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pelanggaran';

    protected $fillable = [
        // 'code', // Dihapus
        'sequence_number', // Ditambahkan
        'name',
        'description',
        'severity',
        'is_active',
    ];

    protected $casts = [
        'sequence_number' => 'integer', // Casting sebagai integer
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function cermatReports()
    {
        return $this->hasMany(CermatReport::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    // Helper for Badge Color
    public function getSeverityBadgeClassAttribute()
    {
        return match($this->severity) {
            'low' => 'badge-success',
            'medium' => 'badge-warning',
            'high' => 'badge-danger',
            'critical' => 'badge-dark',
            default => 'badge-secondary',
        };
    }

    // Scope baru untuk pengurutan berdasarkan nomer urut
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_number', 'asc');
    }
}
