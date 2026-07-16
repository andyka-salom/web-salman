<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiReportDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'kpi_report_id', 'kpi_item_id',
        // Diisi KOORDINATOR
        'actual_count', 'keterangan',
        // Diisi HSSE
        'nilai', 'score', 'hsse_catatan',
        // Status review & mark
        'review_status',
        'is_marked',
        'mark_note',
    ];

    protected $casts = [
        'actual_count' => 'float',    // ← ADDED: pastikan disimpan sebagai angka
        'nilai'        => 'decimal:2',
        'score'        => 'decimal:4',
        'is_marked'    => 'boolean',
    ];

    public function kpiReport(): BelongsTo { return $this->belongsTo(KpiReport::class); }
    public function kpiItem(): BelongsTo   { return $this->belongsTo(KpiItem::class); }

    public function evidences(): HasMany
    {
        return $this->hasMany(KpiReportDetailEvidence::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(KpiReportItemReview::class)->orderByDesc('reviewed_at');
    }

    public function getLatestRejectionCommentAttribute(): ?string
    {
        return $this->reviews()->where('decision', 'rejected')->value('comment');
    }

    public function getLatestReviewAttribute(): ?KpiReportItemReview
    {
        return $this->reviews()->first();
    }
}
