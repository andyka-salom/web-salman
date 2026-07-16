<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiReportItemReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'kpi_report_detail_id',
        'reviewed_by',
        'decision',          // approved | rejected
        'comment',           // catatan verifikasi (wajib jika rejected)
        'is_marked',         // boolean — HSSE tandai item perlu perhatian
        'mark_note',         // catatan khusus untuk mark
        'submission_round',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'is_marked'   => 'boolean',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(KpiReportDetail::class, 'kpi_report_detail_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
