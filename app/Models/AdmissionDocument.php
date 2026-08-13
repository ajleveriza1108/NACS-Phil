<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDocument extends Model
{
    use HasFactory;

    public const TYPES = [
        'birth_certificate' => 'Birth certificate',
        'report_card' => 'Report card',
        'good_moral' => 'Certificate of good moral character',
        'id_photo' => 'Identification photo',
        'other' => 'Other document requested by the school',
    ];

    protected $fillable = [
        'admission_application_id','document_type','original_name','stored_name',
        'path','mime_type','size_bytes','uploaded_by','is_verified','verified_at',
        'verified_by_user_id','admin_notes',
    ];

    protected $hidden = ['path','stored_name','admin_notes'];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->document_type] ?? 'Requested document';
    }

    public function formattedSize(): string
    {
        if ($this->size_bytes < 1024) {
            return $this->size_bytes . ' B';
        }

        if ($this->size_bytes < 1024 * 1024) {
            return number_format($this->size_bytes / 1024, 1) . ' KB';
        }

        return number_format($this->size_bytes / (1024 * 1024), 1) . ' MB';
    }
}