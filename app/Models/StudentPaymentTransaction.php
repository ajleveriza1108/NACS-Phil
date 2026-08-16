<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentPaymentTransaction extends Model
{
    protected $fillable = [
        'public_id',
        'student_id',
        'initiated_by',
        'provider',
        'external_reference',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'metadata',
        'classification',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StudentPaymentTransaction $transaction): void {
            $transaction->public_id ??= (string) Str::uuid();
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
