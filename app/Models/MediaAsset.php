<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','file_path','original_name','mime_type','file_size','alt_text',
        'caption','category','rights_confirmed_at','consent_confirmed_at',
        'credit','uploaded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'rights_confirmed_at' => 'datetime',
            'consent_confirmed_at' => 'datetime',
        ];
    }
}
