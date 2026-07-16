<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiReportVessel extends Model
{
    use HasUuids;

    protected $fillable = [
        'kpi_report_id', 'vessel_name', 'vessel_count',
        'contract_number', 'contract_end_date', 'sort_order',
    ];

    protected $casts = ['contract_end_date' => 'date'];

    public function kpiReport(): BelongsTo { return $this->belongsTo(KpiReport::class); }

    public function isContractExpired(): bool
    {
        return $this->contract_end_date && $this->contract_end_date->isPast();
    }

    public function isContractNearExpiry(int $days = 30): bool
    {
        return $this->contract_end_date
            && !$this->contract_end_date->isPast()
            && $this->contract_end_date->diffInDays(now()) <= $days;
    }
}
