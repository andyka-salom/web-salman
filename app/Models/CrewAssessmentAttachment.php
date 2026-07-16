<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class CrewAssessmentAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'crew_assessment_id', 'uploaded_by', 'original_name',
        'stored_name', 'disk', 'path', 'mime_type', 'size', 'label',
    ];

    protected $casts = ['size' => 'integer'];
    protected $appends = ['size_human', 'extension', 'icon_class'];

    public function assessment()
    {
        return $this->belongsTo(CrewAssessment::class, 'crew_assessment_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getSizeHumanAttribute(): string
    {
        if ($this->size >= 1_048_576) return round($this->size / 1_048_576, 2) . ' MB';
        if ($this->size >= 1_024)    return round($this->size / 1_024, 1) . ' KB';
        return $this->size . ' B';
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function getIconClassAttribute(): string
    {
        return match($this->extension) {
            'pdf'              => 'bi bi-file-earmark-pdf text-danger',
            'doc', 'docx'      => 'bi bi-file-earmark-word text-primary',
            'xls', 'xlsx'      => 'bi bi-file-earmark-excel text-success',
            'jpg', 'jpeg', 'png' => 'bi bi-file-earmark-image text-info',
            default            => 'bi bi-file-earmark text-secondary',
        };
    }

    public function deleteFile(): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }
        return $this->delete();
    }

    public static function maxSizeKb(): int { return 10 * 1024; }
}
