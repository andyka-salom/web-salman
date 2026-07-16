<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// SoftDeletes dihapus — delete sekarang permanent & cascade

class KpiReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id', 'kpi_period_id',
        'total_score_lagging', 'total_score_leading', 'total_score',
        'status',
        'created_by', 'submitted_by', 'validated_by',
        'submitted_at', 'validated_at',
    ];

    protected $casts = [
        'submitted_at'        => 'datetime',
        'validated_at'        => 'datetime',
        'total_score_lagging' => 'decimal:4',
        'total_score_leading' => 'decimal:4',
        'total_score'         => 'decimal:4',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VALIDATED = 'validated';
    const STATUS_REJECTED  = 'rejected';

    // ── Boot: cascade delete semua relasi ──────────────────────────────────
    protected static function booted(): void
    {
        static::deleting(function (KpiReport $report) {
            // Hapus evidence files & records untuk setiap detail
            foreach ($report->details as $detail) {
                foreach ($detail->evidences as $ev) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($ev->file_path);
                    $ev->delete();
                }
                $detail->reviews()->delete();
                $detail->delete();
            }
            $report->vessels()->delete();
            $report->statusLogs()->delete();
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────
    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function kpiPeriod(): BelongsTo { return $this->belongsTo(KpiPeriod::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function validatedBy(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }

    public function vessels(): HasMany
    {
        return $this->hasMany(KpiReportVessel::class)->orderBy('sort_order');
    }

    public function details(): HasMany   { return $this->hasMany(KpiReportDetail::class); }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(KpiReportStatusLog::class)->orderBy('acted_at');
    }

    // ── Accessors ─────────────────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_SUBMITTED => 'Menunggu Validasi',
            self::STATUS_VALIDATED => 'Tervalidasi',
            self::STATUS_REJECTED  => 'Ditolak',
            default                => ucfirst($this->status),
        };
    }

    public function canBeEdited(): bool
    {
        return true;
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function getSubmissionRoundAttribute(): int
    {
        return $this->statusLogs()->where('to_status', 'submitted')->count();
    }
}