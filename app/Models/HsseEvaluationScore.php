<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HsseEvaluationScore extends Model
{
    use HasUuids;

    protected $table = 'hsse_evaluation_scores';

    protected $fillable = [
        'evaluation_id',
        'criteria_id',
        'score',
        'keterangan',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Label teks untuk nilai score (1=Kurang, 2=Cukup, 3=Baik)
     */
    public function getScoreLabelAttribute(): string
    {
        return match ((int) $this->score) {
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik',
            default => '-',
        };
    }

    /**
     * Warna Bootstrap untuk badge score
     */
    public function getScoreColorAttribute(): string
    {
        return match ((int) $this->score) {
            1 => 'danger',
            2 => 'warning',
            3 => 'success',
            default => 'secondary',
        };
    }

    // ─────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function evaluation()
    {
        return $this->belongsTo(HsseEvaluation::class, 'evaluation_id');
    }

    public function criteria()
    {
        return $this->belongsTo(HsseCriteria::class, 'criteria_id');
    }
}
