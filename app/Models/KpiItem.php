<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'section',
        'item_no',
        'name',
        'guidance',
        'unit',       // '∑' atau '%'
        'bobot',
        'is_scored',
        'is_active',
    ];

    protected $casts = [
        'bobot' => 'decimal:4',
        'is_scored' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function reportDetails(): HasMany
    {
        return $this->hasMany(KpiReportDetail::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('section')->orderBy('item_no');
    }

    // ── Accessors ──────────────────────────────────────────────────────────
    public function getSectionLabelAttribute(): string
    {
        return $this->section === 'lagging'
            ? 'Section 1 - Lagging Indicator'
            : 'Section 2 - Leading Indicator';
    }

    public function getBobotPercentAttribute(): string
    {
        return number_format((float) $this->bobot * 100, 1) . '%';
    }

    /**
     * Unit label: '∑' atau '%'
     * Berdasarkan revisi Excel KPI HSSE Rev01:
     *   Leading items yang menggunakan %:
     *     No. 2,3,4,5,6,10,11,12,14,15 (rata-rata / kepatuhan)
     *   Leading items yang menggunakan ∑:
     *     No. 1,7,8,9,13 (jumlah / count)
     *   Semua lagging menggunakan ∑
     */
    public function getUnitLabelAttribute(): string
    {
        if (!empty($this->unit))
            return $this->unit;

        // Fallback otomatis berdasarkan item_no dan section
        if ($this->section === 'leading') {
            $pctItems = [2, 3, 4, 5, 6, 10, 11, 12, 14, 15];
            return in_array($this->item_no, $pctItems) ? '%' : '∑';
        }

        return '∑'; // Semua lagging pakai ∑
    }

    /**
     * Panduan pengisian per item — sesuai revisi Excel Rev01.
     * Digunakan untuk tooltip/popover di UI, bukan ditampilkan sebagai kolom tabel.
     */
    public function getGuidanceLabelAttribute(): string
    {
        if (!empty($this->guidance))
            return $this->guidance;

        // Fallback guidance sesuai Excel Rev01
        $map = [
            'lagging' => [
                1 => 'Jumlah Fatality (NOA) pada bulan berjalan',
                2 => 'Jumlah LTI pada bulan berjalan',
                3 => 'Jumlah RWDC pada bulan berjalan',
                4 => 'Jumlah MTC pada bulan berjalan',
                5 => 'Jumlah HIPO pada bulan berjalan',
                6 => 'Jumlah FAC pada bulan berjalan',
                7 => 'Jumlah nearmiss dan insiden tanpa personal injury - Non HIPO',
                8 => 'Jumlah Manhours pada bulan berjalan',
            ],
            'leading' => [
                1 => 'Jumlah CERMAT sesuai dashboard Kapten Salman pada bulan berjalan',
                2 => 'Persentasi perbandingan valid MCU vs semua personnel on board / site pada bulan berjalan',
                3 => 'Persentasi kepatuhan DCU sesuai dashboard aplikasi Kapten Salman pada bulan berjalan',
                4 => 'Persentasi realisasi wellness vs jadwal pada bulan berjalan',
                5 => 'Persentasi pelaksanaan random test vs rencana pada bulan berjalan',
                6 => 'Persentasi jumlah valid training vs target training pada bulan berjalan',
                7 => 'Jumlah safety campaign / rapat sesuai dashboard KaptenSalman pada bulan berjalan',
                8 => 'Jumlah implementasi drill / exercise pada bulan berjalan',
                9 => 'Jumlah inspeksi HSSE pada bulan berjalan',
                10 => 'Presentasi pelaksanaan internal audit vs rencana pada bulan berjalan',
                11 => 'Persentasi implementasi MWT vs rencana pada bulan berjalan',
                12 => 'Persentasi pemberian apresiasi / penghargaan yang diberikan pada tiap kuartal (3 monthly)',
                13 => 'Jumlah inspeksi dalam bulan berjalan',
                14 => 'Persentasi realisasi perawatan (PMS) vs rencana pada bulan berjalan',
                15 => 'Presentasi pencapaian tindak lanjut rekomendasi pada bulan berjalan',
            ],
        ];

        return $map[$this->section][$this->item_no] ?? '';
    }

    /**
     * Apakah item ini menggunakan rumus RATA-RATA (untuk leading AVG items)
     * Sesuai revisi: No. 2,3,4,5,6,10,11,12,14,15
     */
    public function getIsAvgItemAttribute(): bool
    {
        if ($this->section !== 'leading')
            return false;
        return in_array($this->item_no, [2, 3, 4, 5, 6, 10, 11, 12, 14, 15]);
    }
}