<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRecordAudit extends Model
{
    protected $fillable = [
        'student_id','actor_user_id','action','record_type','record_id',
        'changed_fields','summary',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
