<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class CermatReport extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    // ===================================
    // GLOBAL STATUS CONSTANTS (DB: status)
    // ===================================
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_REVIEW = 'in_review'; // Tahap Verifikasi SPV & Review HSSE
    public const STATUS_ACTION_IN_PROGRESS = 'action_in_progress';
    public const STATUS_AWAITING_CLOSEOUT = 'awaiting_closeout';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_NO_ACTION_REQUIRED = 'no_action_required';

    // ===================================
    // SUPERVISOR STATUS CONSTANTS (DB: supervisor_status)
    // ===================================
    public const SUPERVISOR_STATUS_AWAITING = 'awaiting_approval';
    public const SUPERVISOR_STATUS_APPROVED = 'approved';
    public const SUPERVISOR_STATUS_REJECTED = 'rejected';
    public const SUPERVISOR_STATUS_NOT_REQUIRED = 'not_required';

    // ===================================
    // HSSE STATUS CONSTANTS (DB: hsse_status)
    // ===================================
    public const HSSE_STATUS_PENDING_REVIEW = 'pending_review';
    public const HSSE_STATUS_REVIEWED = 'reviewed';
    public const HSSE_STATUS_RECOMMENDED = 'recommended';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_number',
        'area_id',
        'reporter_id',
        'line_supervisor_id',
        'hsse_officer_id',
        'close_out_performer_id',
        'pelanggaran_id',
        'security_event_category_id',

        'details',
        'location_details',
        'report_datetime',
        'immediate_action_taken',
        'suggestion_for_improvement',

        // Data Analisis & Klasifikasi
        'classification', // unsafe_act / unsafe_condition / etc
        'event_type',
        'is_limited_circulation',
        'synergi_register_no',

        // Data Finansial / Dampak
        'value_of_loss',
        'cost_to_business_actual',
        'cost_to_business_potential',

        // Workflow Status Fields
        'status',
        'supervisor_status',
        'hsse_status',

        // Flags & Options
        'notified_line_management',
        'stop_card_issued',
        'requires_feedback',
        'short_term_mitigation',
        'reported_event_categories', // JSON/Array

        // Notes & Reasons
        'supervisor_notes',
        'rejection_reason',
        'close_out_description',

        // Timestamps Logic
        'internal_deadline',
        'submitted_at',
        'supervisor_approved_at',
        'supervisor_rejected_at',
        'hsse_reviewed_at',
        'hsse_recommended_at',
        'close_out_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'report_datetime' => 'datetime',
        'internal_deadline' => 'date',

        // Workflow Timestamps
        'submitted_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'supervisor_rejected_at' => 'datetime',
        'hsse_reviewed_at' => 'datetime',
        'hsse_recommended_at' => 'datetime',
        'close_out_at' => 'datetime',

        // Numeric
        'value_of_loss' => 'decimal:2',
        'cost_to_business_actual' => 'decimal:2',
        'cost_to_business_potential' => 'decimal:2',

        // Boolean & Array
        'is_limited_circulation' => 'boolean',
        'notified_line_management' => 'boolean',
        'stop_card_issued' => 'boolean',
        'requires_feedback' => 'boolean',
        'reported_event_categories' => 'array',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ===================================
    // RELATIONSHIPS
    // ===================================

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function lineSupervisor()
    {
        return $this->belongsTo(User::class, 'line_supervisor_id');
    }

    public function hsseOfficer()
    {
        return $this->belongsTo(User::class, 'hsse_officer_id');
    }

    public function closeOutPerformer()
    {
        return $this->belongsTo(User::class, 'close_out_performer_id');
    }

    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function securityEventCategory()
    {
        return $this->belongsTo(SecurityEventCategory::class);
    }

    public function unsafeActs()
    {
        return $this->belongsToMany(UnsafeAct::class, 'cermat_report_unsafe_act')
            ->withTimestamps();
    }

    public function unsafeConditions()
    {
        return $this->belongsToMany(UnsafeCondition::class, 'cermat_report_unsafe_condition')
            ->withTimestamps();
    }

    public function riskAssessments()
    {
        return $this->hasMany(RiskAssessment::class);
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(ReportAttachment::class);
    }

    // ===================================
    // PERMISSION LOGIC (CORE REQUEST)
    // ===================================

    /**
     * Menentukan apakah user saat ini memiliki izin untuk mengedit laporan.
     */
    public function canUserEdit(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // 1. HSSE: Bisa edit kapanpun (Analisis & Klasifikasi)
        if ($user->hasRole('hsse')) {
            return true;
        }

        // 2. Supervisor: Bisa edit selama belum diambil alih HSSE
        // Syarat: Status global 'IN_REVIEW' DAN status HSSE masih 'PENDING'
        if ($this->isSupervisor($user->id)) {
            return $this->status === self::STATUS_IN_REVIEW
                && $this->hsse_status === self::HSSE_STATUS_PENDING_REVIEW;
        }

        // 3. Reporter (Pelapor):
        if ($this->isReporter($user->id)) {
            // a. Masih Draft (Belum dikirim)
            if ($this->status === self::STATUS_DRAFT) {
                return true;
            }

            // b. Ditolak Supervisor (Perlu perbaikan)
            if ($this->supervisor_status === self::SUPERVISOR_STATUS_REJECTED) {
                return true;
            }

            // c. Sudah Submit TAPI belum diverifikasi Supervisor (Awaiting Approval)
            // User masih boleh revisi sebelum Supervisor menekan tombol Approve/Reject
            if ($this->status === self::STATUS_IN_REVIEW
                && $this->supervisor_status === self::SUPERVISOR_STATUS_AWAITING) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Alias untuk backward compatibility dengan controller
     */
    public function canBeEdited(): bool
    {
        return $this->canUserEdit();
    }

    // ===================================
    // ROLE CHECKERS
    // ===================================

    public function isReporter($userId): bool
    {
        return $this->reporter_id === $userId;
    }

    public function isSupervisor($userId): bool
    {
        return $this->line_supervisor_id === $userId;
    }

    public function isHsseOfficer($userId): bool
    {
        return $this->hsse_officer_id === $userId;
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeActive(Builder $query): Builder
    {
        // Laporan yang sedang berjalan prosesnya (bukan draft, bukan closed/no action)
        return $query->whereIn('status', [
            self::STATUS_IN_REVIEW,
            self::STATUS_ACTION_IN_PROGRESS,
            self::STATUS_AWAITING_CLOSEOUT
        ]);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_CLOSED,
            self::STATUS_NO_ACTION_REQUIRED
        ]);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('report_datetime', [$startDate, $endDate]);
    }

    // ===================================
    // HELPER METHODS
    // ===================================

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_IN_REVIEW => 'info',
            self::STATUS_ACTION_IN_PROGRESS => 'primary',
            self::STATUS_AWAITING_CLOSEOUT => 'warning',
            self::STATUS_CLOSED => 'success',
            self::STATUS_NO_ACTION_REQUIRED => 'dark',
            default => 'secondary',
        };
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->value_of_loss
            + (float) $this->cost_to_business_actual
            + (float) $this->cost_to_business_potential;
    }

    /**
     * Sinkronisasi Unsafe Acts dengan tracking penggunaan
     */
    public function syncUnsafeActsWithTracking(array $unsafeActIds): array
    {
        $syncResult = $this->unsafeActs()->sync($unsafeActIds);

        // Jika ada yang baru ditambahkan (attached), update counter di master data
        if (!empty($syncResult['attached'])) {
            UnsafeAct::whereIn('id', $syncResult['attached'])->get()->each->incrementUsage();
        }

        return $syncResult;
    }

    /**
     * Sinkronisasi Unsafe Conditions dengan tracking penggunaan
     */
    public function syncUnsafeConditionsWithTracking(array $unsafeConditionIds): array
    {
        $syncResult = $this->unsafeConditions()->sync($unsafeConditionIds);

        if (!empty($syncResult['attached'])) {
            UnsafeCondition::whereIn('id', $syncResult['attached'])->get()->each->incrementUsage();
        }

        return $syncResult;
    }
        public function isConsideredFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_CLOSED,
            self::STATUS_NO_ACTION_REQUIRED,
        ]);
    }
}
