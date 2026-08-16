<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationInvitation extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'token_expires_at',
        'password_set_at',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'otp_sent_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'password_set_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'otp_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function findActiveByToken(string $plainToken): ?self
    {
        return static::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->whereNull('completed_at')
            ->first();
    }
}
