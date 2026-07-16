<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class KpiReportDetailEvidence extends Model
{
    use HasUuids, SoftDeletes;

    // Laravel auto-generates "kpi_report_detail_evidence" (salah).
    // Tabel di DB adalah "kpi_report_detail_evidences" (plural).
    protected $table = 'kpi_report_detail_evidences';

    protected $fillable = [
        'kpi_report_detail_id', 'file_path', 'file_name',
        'file_type', 'file_size', 'caption', 'sort_order', 'uploaded_by',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(KpiReportDetail::class, 'kpi_report_detail_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
