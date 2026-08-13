<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'guardian_name', 'email', 'phone', 'student_name', 'level_interested',
        'message', 'status', 'admin_notes', 'privacy_consent_at', 'ip_hash', 'user_agent',
    ];

    protected $hidden = [
        'ip_hash',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'privacy_consent_at' => 'datetime',
        ];
    }
}
