<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HsseEvaluation extends Model
{
    use HasUuids;
    // SoftDeletes dihapus — semua delete langsung hard delete

    protected $table = 'hsse_evaluations';

    protected $fillable = [
        'company_id',
        'vessel_id',
        'crew_member_id',
        'crew_name',
        'crew_position',
        'evaluated_date',
        'total_score',
        'score_category',
        'notes',
        'assessor_id',
        'assessor_name',
        'assessor_position',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'evaluated_date' => 'date',
        'submitted_at'   => 'datetime',
        'total_score'    => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────────

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('score_category', $category);
    }

    public function scopeInPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('evaluated_date', [$start, $end]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return ucfirst($this->score_category ?? '-');
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->score_category) {
            'baik'   => 'success',
            'cukup'  => 'warning',
            'kurang' => 'danger',
            default  => 'secondary',
        };
    }

    public function getPdfFilenameAttribute(): string
    {
        $date = $this->evaluated_date
            ? $this->evaluated_date->format('Ymd')
            : now()->format('Ymd');

        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->crew_name ?? 'kru');

        return "HSSE_Evaluation_{$name}_{$date}.pdf";
    }


    // ─────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }

    public function crewMember()
    {
        return $this->belongsTo(CrewMember::class, 'crew_member_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function scores()
    {
        return $this->hasMany(HsseEvaluationScore::class, 'evaluation_id');
    }

    public function scoresOrdered()
    {
        return $this->hasMany(HsseEvaluationScore::class, 'evaluation_id')
            ->join('hsse_criteria', 'hsse_evaluation_scores.criteria_id', '=', 'hsse_criteria.id')
            ->orderBy('hsse_criteria.order_no')
            ->select('hsse_evaluation_scores.*');
    }
}