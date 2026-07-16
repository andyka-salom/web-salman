<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HsseCriteria extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hsse_criteria';

    protected $fillable = [
        'order_no',
        'aspect',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_no'  => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────────

    /**
     * Hanya kriteria yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Urutkan berdasar order_no.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no');
    }

    // ─────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Semua nilai / scores yang menggunakan kriteria ini.
     */
    public function scores()
    {
        return $this->hasMany(HsseEvaluationScore::class, 'criteria_id');
    }
}
