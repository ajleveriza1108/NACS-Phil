<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTeacherAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'school_year',
        'subject',
        'is_adviser',
        'can_manage_profile',
        'can_manage_grades',
        'can_manage_attendance',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_adviser' => 'boolean',
            'can_manage_profile' => 'boolean',
            'can_manage_grades' => 'boolean',
            'can_manage_attendance' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
