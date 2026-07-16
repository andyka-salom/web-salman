<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Groupable extends Pivot
{
    protected $table = 'groupables';

    public $incrementing = false;

    protected $fillable = [
        'group_id',
        'groupable_id',
        'groupable_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function groupable()
    {
        return $this->morphTo();
    }
}
