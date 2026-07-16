<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    /**
     * Nama tabel (opsional jika sesuai konvensi)
     */
    protected $table = 'contracts';

    /**
     * Kolom yang bisa di-isi (mass assignable)
     */
    protected $fillable = [
        'nama_kontraktor',
        'sap_no',
        'alamat_kantor',
        'alamat_email',
        'no_tlp_kantor',
    ];

    /**
     * Casting (jika diperlukan)
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Groups (polymorphic many-to-many)
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }
}
