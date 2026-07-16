<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiReportStatusLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'kpi_report_id', 'from_status', 'to_status',
        'comment', 'acted_by', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function kpiReport(): BelongsTo
    {
        return $this->belongsTo(KpiReport::class);
    }

    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function getToStatusLabelAttribute(): string
    {
        return match($this->to_status) {
            'draft'     => 'Draft',
            'submitted' => 'Disubmit',
            'validated' => 'Divalidasi',
            'rejected'  => 'Ditolak',
            default     => ucfirst($this->to_status),
        };
    }

    public function getToStatusBadgeAttribute(): string
    {
        $map = [
            'draft'     => 'secondary',
            'submitted' => 'warning',
            'validated' => 'success',
            'rejected'  => 'danger',
        ];
        $class = $map[$this->to_status] ?? 'secondary';
        return "<span class=\"badge badge-light-{$class}\">{$this->to_status_label}</span>";
    }
}
