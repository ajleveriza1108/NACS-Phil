<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFinancialEntry extends Model
{
    protected $fillable = [
        'student_id','recorded_by','school_year','entry_type','description','amount',
        'reference_number','entry_date','due_date','classification',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function signedAmount(): float
    {
        $amount = (float) $this->amount;

        return in_array($this->entry_type, ['payment', 'credit'], true)
            ? -$amount
            : $amount;
    }
}
