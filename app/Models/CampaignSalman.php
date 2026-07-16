<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CampaignSalman extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'campaign_salman'; // Nama tabel spesifik

    protected $fillable = [
        'company_id',
        'created_by',
        'cover_template_id',
        'tanggal',
        'tema',
        'lokasi',
        'jumlah_peserta',
        'pemateri',
        'entitas',
        'ringkasan',
        'dokumentasi',
        'daftar_hadir',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_peserta' => 'integer',
        'dokumentasi' => 'array',
        'daftar_hadir' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coverTemplate()
    {
        return $this->belongsTo(CoverTemplate::class);
    }

    // Helper: Generate PDF Filename
    public function getPdfFilename(): string
    {
        // Format: Report_YYYY-MM-DD_Tema.pdf
        $cleanTema = \Illuminate\Support\Str::slug($this->tema);
        return sprintf('Report_%s_%s.pdf', $this->tanggal->format('Y-m-d'), $cleanTema);
    }
}
