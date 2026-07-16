<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'cermat_report_id',
        'type',
        'consequence_category',
        'real_severity',
        'potential_severity',
        'likelihood',
        'risk_score',
        'risk_level',
    ];

    protected $casts = [
        'real_severity' => 'integer',
        'potential_severity' => 'integer',
        'likelihood' => 'integer',
        'risk_score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function cermatReport()
    {
        return $this->belongsTo(CermatReport::class);
    }

    // Methods
    public function calculateRiskScore(): void
    {
        $severity = $this->potential_severity ?? $this->real_severity;
        if ($severity && $this->likelihood) {
            $this->risk_score = $severity * $this->likelihood;
            $this->risk_level = $this->determineRiskLevel($this->risk_score);
            $this->save();
        }
    }

    private function determineRiskLevel(int $score): string
    {
        return match(true) {
            $score >= 20 => 'critical',
            $score >= 12 => 'high',
            $score >= 6 => 'medium',
            default => 'low',
        };
    }

    public function getRiskColorAttribute(): string
    {
        return match($this->risk_level) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary',
        };
    }
}
