<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthCheck extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'crew_member_id',
        'vessel_id',
        'reported_by',
        'verified_by',
        'validated_by',
        'validated_at',
        'validation_notes',
        'verified_at',
        'verification_notes',
        'checked_at',
        'check_date',
        'work_area',
        'illness_complaints',
        'medications_consumed',
        'temperature',
        'pulse_rate',
        'blood_pressure',
        'respiratory_rate',
        'blood_sugar_level',
        'oxygen_saturation',
        'fatigue_level',
        'napza_test_result',
        'romberg_test_result',
        'remarks',
        'status',
        'has_health_issue',
        'requires_follow_up',
        'crew_name_snapshot',
        'crew_position_snapshot',
    ];

    protected $casts = [
        'check_date' => 'date',
        'checked_at' => 'datetime',
        'temperature' => 'decimal:1',
        'has_health_issue' => 'boolean',
        'requires_follow_up' => 'boolean',
        'verified_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'napza_test_result' => 'not_tested',
        'romberg_test_result' => 'not_tested',
        'has_health_issue' => false,
        'requires_follow_up' => false,
    ];

    /**
     * UPDATED: Health thresholds dengan kategori baru
     * Suhu: <36 (rendah), 36-37.5 (normal), >37.5 (tinggi)
     * Nadi: <60 (rendah), 60-100 (normal), >100 (tinggi)
     * Tensi Sistolik: <90 (rendah), 90-120 (normal), 121-140 (pre-hipertensi), 141-159 (hipertensi stage 1), >159 (hipertensi stage 2)
     * Tensi Diastolik: <60 (rendah), 60-80 (normal), >80 (tinggi)
     * Nafas: <12 (rendah), 12-20 (normal), >20 (tinggi)
     * SpO2: <95% (rendah), ≥95% (normal)
     */
    public const HEALTH_THRESHOLDS = [
        'temperature' => [
            'very_low' => 36,
            'normal_max' => 37.5
        ],
        'pulse_rate' => [
            'low' => 60,
            'normal_max' => 100
        ],
        'respiratory_rate' => [
            'low' => 12,
            'normal_max' => 20
        ],
        'blood_pressure_systolic' => [
            'low' => 90,
            'normal_max' => 120,
            'pre_hypertension_max' => 140,
            'hypertension_stage1_max' => 159
        ],
        'blood_pressure_diastolic' => [
            'low' => 60,
            'normal_max' => 80
        ],
        'oxygen_saturation' => [
            'min' => 95
        ]
    ];

    // --- Boot Method ---

    protected static function boot()
    {
        parent::boot();

        // Auto-save snapshot on creating
        static::creating(function ($healthCheck) {
            if ($healthCheck->crew_member_id && !$healthCheck->crew_name_snapshot) {
                $crew = CrewMember::find($healthCheck->crew_member_id);
                if ($crew) {
                    $healthCheck->crew_name_snapshot = $crew->name;
                    $healthCheck->crew_position_snapshot = $crew->position;
                }
            }
        });
    }

    // --- Relationships ---

    public function crewMember(): BelongsTo
    {
        return $this->belongsTo(CrewMember::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // --- Scopes ---

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('check_date', $date);
    }

    public function scopeForVessel($query, $vesselId)
    {
        return $query->where('vessel_id', $vesselId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWithHealthIssues($query)
    {
        return $query->where('has_health_issue', true);
    }

    // --- Accessors ---

    /**
     * Get crew name from snapshot or relationship
     */
    public function getCrewNameAttribute(): ?string
    {
        return $this->crew_name_snapshot ?? $this->crewMember?->name;
    }

    /**
     * Get crew position from snapshot or relationship
     */
    public function getCrewPositionAttribute(): ?string
    {
        return $this->crew_position_snapshot ?? $this->crewMember?->position;
    }

    // --- Helper Methods ---

    /**
     * Manually save crew snapshot
     */
    public function saveCrewSnapshot(): void
    {
        if ($this->crew_member_id) {
            $crew = $this->crewMember ?? CrewMember::withTrashed()->find($this->crew_member_id);
            if ($crew) {
                $this->crew_name_snapshot = $crew->name;
                $this->crew_position_snapshot = $crew->position;
                $this->save();
            }
        }
    }

    /**
     * Check if vital signs are outside normal ranges (UPDATED)
     */
    public function hasAbnormalVitals(): bool
    {
        // Temperature check
        if ($this->temperature) {
            if ($this->temperature < self::HEALTH_THRESHOLDS['temperature']['very_low'] ||
                $this->temperature > self::HEALTH_THRESHOLDS['temperature']['normal_max']) {
                return true;
            }
        }

        // Pulse rate check
        if ($this->pulse_rate) {
            if ($this->pulse_rate < self::HEALTH_THRESHOLDS['pulse_rate']['low'] ||
                $this->pulse_rate > self::HEALTH_THRESHOLDS['pulse_rate']['normal_max']) {
                return true;
            }
        }

        // Blood pressure check
        if ($this->blood_pressure && strpos($this->blood_pressure, '/') !== false) {
            [$systolic, $diastolic] = explode('/', $this->blood_pressure);
            $systolic = (int) $systolic;
            $diastolic = (int) $diastolic;

            if ($systolic < self::HEALTH_THRESHOLDS['blood_pressure_systolic']['low'] ||
                $systolic > self::HEALTH_THRESHOLDS['blood_pressure_systolic']['normal_max'] ||
                $diastolic < self::HEALTH_THRESHOLDS['blood_pressure_diastolic']['low'] ||
                $diastolic > self::HEALTH_THRESHOLDS['blood_pressure_diastolic']['normal_max']) {
                return true;
            }
        }

        // Respiratory rate check
        if ($this->respiratory_rate) {
            if ($this->respiratory_rate < self::HEALTH_THRESHOLDS['respiratory_rate']['low'] ||
                $this->respiratory_rate > self::HEALTH_THRESHOLDS['respiratory_rate']['normal_max']) {
                return true;
            }
        }

        // Oxygen saturation check
        if ($this->oxygen_saturation) {
            if ($this->oxygen_saturation < self::HEALTH_THRESHOLDS['oxygen_saturation']['min']) {
                return true;
            }
        }

        // Test results check
        if ($this->napza_test_result === 'non_negative' || $this->romberg_test_result === 'positive') {
            return true;
        }

        return false;
    }

    /**
     * Check if condition is critical (BARU - untuk tanda merah)
     * Kriteria kritis: Hipertensi Stage 1 atau lebih tinggi (Sistolik >140 atau Diastolik >80)
     */
    public function hasCriticalCondition(): bool
    {
        // Check blood pressure for hypertension
        if ($this->blood_pressure && strpos($this->blood_pressure, '/') !== false) {
            [$systolic, $diastolic] = explode('/', $this->blood_pressure);
            $systolic = (int) $systolic;
            $diastolic = (int) $diastolic;

            // Hipertensi Stage 1 atau lebih tinggi
            if ($systolic > self::HEALTH_THRESHOLDS['blood_pressure_systolic']['pre_hypertension_max'] ||
                $diastolic > self::HEALTH_THRESHOLDS['blood_pressure_diastolic']['normal_max']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get severity level for blood pressure (BARU)
     */
    public function getBloodPressureSeverity(): ?string
    {
        if (!$this->blood_pressure || strpos($this->blood_pressure, '/') === false) {
            return null;
        }

        [$systolic, $diastolic] = explode('/', $this->blood_pressure);
        $systolic = (int) $systolic;
        $diastolic = (int) $diastolic;

        // Hipertensi Stage 2 (>159 systolic)
        if ($systolic > self::HEALTH_THRESHOLDS['blood_pressure_systolic']['hypertension_stage1_max']) {
            return 'critical_high'; // Merah terang
        }

        // Hipertensi Stage 1 (141-159 systolic)
        if ($systolic > self::HEALTH_THRESHOLDS['blood_pressure_systolic']['pre_hypertension_max']) {
            return 'high'; // Merah
        }

        // Pre-hipertensi (121-140 systolic)
        if ($systolic > self::HEALTH_THRESHOLDS['blood_pressure_systolic']['normal_max']) {
            return 'pre_high'; // Kuning
        }

        // Rendah
        if ($systolic < self::HEALTH_THRESHOLDS['blood_pressure_systolic']['low'] ||
            $diastolic < self::HEALTH_THRESHOLDS['blood_pressure_diastolic']['low']) {
            return 'low';
        }

        return 'normal';
    }

    /**
     * Get array of abnormal vital signs with details (UPDATED)
     */
    public function getAbnormalVitals(): array
    {
        $abnormal = [];

        // Temperature
        if ($this->temperature) {
            if ($this->temperature < self::HEALTH_THRESHOLDS['temperature']['very_low']) {
                $abnormal['temperature'] = [
                    'value' => $this->temperature,
                    'threshold' => '36-37.5°C',
                    'message' => 'Suhu tubuh rendah (<36°C)',
                    'severity' => 'warning'
                ];
            } elseif ($this->temperature > self::HEALTH_THRESHOLDS['temperature']['normal_max']) {
                $abnormal['temperature'] = [
                    'value' => $this->temperature,
                    'threshold' => '36-37.5°C',
                    'message' => 'Suhu tubuh tinggi (>37.5°C)',
                    'severity' => 'warning'
                ];
            }
        }

        // Pulse rate
        if ($this->pulse_rate) {
            if ($this->pulse_rate < self::HEALTH_THRESHOLDS['pulse_rate']['low']) {
                $abnormal['pulse_rate'] = [
                    'value' => $this->pulse_rate,
                    'threshold' => '60-100 bpm',
                    'message' => 'Denyut nadi rendah (<60 bpm)',
                    'severity' => 'warning'
                ];
            } elseif ($this->pulse_rate > self::HEALTH_THRESHOLDS['pulse_rate']['normal_max']) {
                $abnormal['pulse_rate'] = [
                    'value' => $this->pulse_rate,
                    'threshold' => '60-100 bpm',
                    'message' => 'Denyut nadi tinggi (>100 bpm)',
                    'severity' => 'warning'
                ];
            }
        }

        // Blood pressure (UPDATED dengan severity level)
        if ($this->blood_pressure && strpos($this->blood_pressure, '/') !== false) {
            [$systolic, $diastolic] = explode('/', $this->blood_pressure);
            $systolic = (int) $systolic;
            $diastolic = (int) $diastolic;

            $severity = $this->getBloodPressureSeverity();

            if ($severity === 'critical_high') {
                $abnormal['blood_pressure'] = [
                    'value' => $this->blood_pressure,
                    'threshold' => '90-120/60-80 mmHg',
                    'message' => 'Hipertensi Stage 2 (>159 mmHg) - KRITIS',
                    'severity' => 'critical'
                ];
            } elseif ($severity === 'high') {
                $abnormal['blood_pressure'] = [
                    'value' => $this->blood_pressure,
                    'threshold' => '90-120/60-80 mmHg',
                    'message' => 'Hipertensi Stage 1 (141-159 mmHg)',
                    'severity' => 'critical'
                ];
            } elseif ($severity === 'pre_high') {
                $abnormal['blood_pressure'] = [
                    'value' => $this->blood_pressure,
                    'threshold' => '90-120/60-80 mmHg',
                    'message' => 'Pre-hipertensi (121-140 mmHg)',
                    'severity' => 'warning'
                ];
            } elseif ($severity === 'low') {
                $abnormal['blood_pressure'] = [
                    'value' => $this->blood_pressure,
                    'threshold' => '90-120/60-80 mmHg',
                    'message' => 'Tekanan darah rendah (<90/60 mmHg)',
                    'severity' => 'warning'
                ];
            }
        }

        // Respiratory rate
        if ($this->respiratory_rate) {
            if ($this->respiratory_rate < self::HEALTH_THRESHOLDS['respiratory_rate']['low']) {
                $abnormal['respiratory_rate'] = [
                    'value' => $this->respiratory_rate,
                    'threshold' => '12-20 x/min',
                    'message' => 'Frekuensi nafas rendah (<12 x/min)',
                    'severity' => 'warning'
                ];
            } elseif ($this->respiratory_rate > self::HEALTH_THRESHOLDS['respiratory_rate']['normal_max']) {
                $abnormal['respiratory_rate'] = [
                    'value' => $this->respiratory_rate,
                    'threshold' => '12-20 x/min',
                    'message' => 'Frekuensi nafas tinggi (>20 x/min)',
                    'severity' => 'warning'
                ];
            }
        }

        // Oxygen saturation
        if ($this->oxygen_saturation) {
            if ($this->oxygen_saturation < self::HEALTH_THRESHOLDS['oxygen_saturation']['min']) {
                $abnormal['oxygen_saturation'] = [
                    'value' => $this->oxygen_saturation,
                    'threshold' => '≥95%',
                    'message' => 'Saturasi oksigen rendah (<95%)',
                    'severity' => 'warning'
                ];
            }
        }

        // NAPZA Test
        if ($this->napza_test_result === 'non_negative') {
            $abnormal['napza_test'] = [
                'value' => $this->napza_test_result,
                'threshold' => 'negative',
                'message' => 'NAPZA test non-negatif',
                'severity' => 'critical'
            ];
        }

        // Romberg Test
        if ($this->romberg_test_result === 'positive') {
            $abnormal['romberg_test'] = [
                'value' => $this->romberg_test_result,
                'threshold' => 'negative',
                'message' => 'Romberg test positif',
                'severity' => 'warning'
            ];
        }

        return $abnormal;
    }

    /**
     * Get formatted threshold information (UPDATED)
     */
    public static function getThresholdInfo(): array
    {
        return [
            'temperature' => [
                'name' => 'Suhu Tubuh',
                'normal' => '36–37.5°C',
                'unit' => '°C'
            ],
            'pulse_rate' => [
                'name' => 'Denyut Nadi',
                'normal' => '60–100 bpm',
                'unit' => 'bpm'
            ],
            'blood_pressure' => [
                'name' => 'Tekanan Darah',
                'normal' => '90–120 / 60–80 mmHg',
                'unit' => 'mmHg'
            ],
            'respiratory_rate' => [
                'name' => 'Frekuensi Nafas',
                'normal' => '12–20 x/menit',
                'unit' => 'x/menit'
            ],
            'blood_sugar' => [
                'name' => 'Gula Darah',
                'normal' => 'Informational',
                'unit' => 'mg/dL'
            ],
            'oxygen_saturation' => [
                'name' => 'Saturasi Oksigen',
                'normal' => '≥95%',
                'unit' => '%'
            ],
        ];
    }
}
