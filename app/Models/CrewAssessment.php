<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class CrewAssessment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id', 'company_name_text',
        'vessel_id', 'vessel_name_text',
        'crew_member_id', 'crew_name_text',
        'created_by',
        'certificate_number', 'coc',
        'position_proposed', 'position_type',
        'experience_pertamina', 'experience_master', 'experience_outside',
        'mev_type', 'assessment_date', 'assessment_location',
        'assessor_mar', 'assessor_hse', 'assessor_fmc',
        'result', 'notes',
        'valid_from', 'valid_until', 'status',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'valid_from'      => 'date',
        'valid_until'     => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    protected $appends = ['result_color', 'days_until_expiry'];

    // ── Konstanta sesuai Excel ────────────────────────────────────────────────

    // Jabatan yang diusulkan (kolom F)
    public const POSITIONS = [
        'Nakhoda'    => 'Nakhoda',
        'KKM'        => 'KKM',
        'Mualim I'   => 'Mualim I',
        'Mualim II'  => 'Mualim II',
        'Masinis I'  => 'Masinis I',
        'Masinis II' => 'Masinis II',
        'ANT IV M'   => 'ANT IV M',
        'ANT IV -M'  => 'ANT IV -M',
        'ANT V M'    => 'ANT V M',
        'ANT.V'      => 'ANT.V',
        'ANT.II'     => 'ANT.II',
        'ATT IV M'   => 'ATT IV M',
        'ATT IV -M'  => 'ATT IV -M',
        'ATT V M'    => 'ATT V M',
        'ATT.V'      => 'ATT.V',
        'ATT.II'     => 'ATT.II',
        'Lainnya'    => 'Lainnya',
    ];

    // Tipe jabatan (kolom G)
    public const POSITION_TYPES = [
        'baru'          => 'Baru',
        'New'           => 'New',
        'promote'       => 'Promote',
        'promote dr AB' => 'Promote dr AB',
        'refresh'       => 'Refresh',
        'Promote'       => 'Promote',
    ];

    // MEV Type (kolom L)
    public const MEV_TYPES = [
        'TRP'      => 'TRP',
        'PROD/CPA' => 'PROD/CPA',
        'PRJ'      => 'PRJ',
        'Lainnya'  => 'Lainnya',
    ];

    // HASIL (kolom Q)
    public const RESULTS = [
        'Lulus'       => 'Lulus',
        'Pending'     => 'Pending',
        'Tidak Lulus' => 'Tidak Lulus',
    ];

    // Lokasi (kolom M area)
    public const LOCATIONS = [
        'SBY' => 'Surabaya (SBY)',
        'RA'  => 'RA',
        'JKT' => 'Jakarta (JKT)',
        'MDN' => 'Medan (MDN)',
        'MKS' => 'Makassar (MKS)',
        'BPP' => 'Balikpapan (BPP)',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function company()    { return $this->belongsTo(Company::class); }
    public function vessel()     { return $this->belongsTo(Vessel::class); }
    public function crewMember() { return $this->belongsTo(CrewMember::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function attachments(){ return $this->hasMany(CrewAssessmentAttachment::class)->latest(); }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeByCompany($q, $id)   { return $q->where('company_id', $id); }
    public function scopeByResult($q, $r)     { return $q->where('result', $r); }
    public function scopeByMev($q, $m)        { return $q->where('mev_type', $m); }
    public function scopeByLocation($q, $l)   { return $q->where('assessment_location', $l); }
    public function scopeExpiringSoon($q, $d=30) {
        return $q->where('status','active')->whereNotNull('valid_until')
                 ->where('valid_until','>=',now())->where('valid_until','<=',now()->addDays($d));
    }
    public function scopeSearch($q, $term) {
        return $q->where(function($q) use ($term) {
            $q->where('certificate_number','like',"%{$term}%")
              ->orWhere('coc','like',"%{$term}%")
              ->orWhere('position_proposed','like',"%{$term}%")
              ->orWhereHas('crewMember', fn($r) => $r->where('name','like',"%{$term}%"))
              ->orWhereHas('vessel',     fn($r) => $r->where('name','like',"%{$term}%"));
        });
    }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getResultColorAttribute(): string {
        return match(strtolower($this->result ?? '')) {
            'lulus'       => 'success',
            'pending'     => 'warning',
            'tidak lulus' => 'danger',
            default       => 'secondary',
        };
    }

    public function getDaysUntilExpiryAttribute(): ?int {
        if (!$this->valid_until) return null;
        if ($this->valid_until->isPast()) return 0;
        return (int) now()->diffInDays($this->valid_until);
    }
}
