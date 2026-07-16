<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'created_by',
        'name',
        'whatsapp_number',
        'position',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Polymorphic: contact dapat menjadi anggota Group
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable', 'groupables', 'groupable_id', 'group_id')
                    ->withTimestamps();
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('whatsapp_number', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Normalise WhatsApp number:
     * - strip leading 0  → replace with 62
     * - strip +          → keep digits only
     */
    public static function normalizeWhatsappNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number); // keep digits only

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    /**
     * Return normalised WA number attribute
     */
    public function getNormalizedNumberAttribute(): string
    {
        return self::normalizeWhatsappNumber($this->whatsapp_number);
    }
}
