<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiPeriod extends Model
{
    use HasUuids;

    protected $fillable = [
        'period_month', 'period_year',
        'submission_start', 'submission_end', 'is_active',
    ];

    protected $casts = [
        'submission_start' => 'date',
        'submission_end'   => 'date',
        'is_active'        => 'boolean',
    ];

    private static array $monthNames = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
    ];

    public function reports(): HasMany { return $this->hasMany(KpiReport::class); }

    public function getLabelAttribute(): string
    {
        return (self::$monthNames[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderByDesc('period_year')->orderByDesc('period_month'); }

    public function isSubmissionOpen(): bool
    {
        if (!$this->submission_start || !$this->submission_end) return true;
        $today = now()->toDateString();
        return $today >= $this->submission_start->toDateString()
            && $today <= $this->submission_end->toDateString();
    }

    /**
     * Auto-create period if it doesn't exist for given month/year.
     */
    public static function findOrCreateForMonthYear(int $month, int $year): self
    {
        return static::firstOrCreate(
            ['period_month' => $month, 'period_year' => $year],
            ['is_active' => true]
        );
    }
}
