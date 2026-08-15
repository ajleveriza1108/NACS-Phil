<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTeacherAssignment extends Model
{
    protected $fillable = [
        'student_id','teacher_id','school_year','subject','is_adviser',
        'can_manage_profile','can_manage_grades','can_manage_attendance',
    ];

    protected function casts(): array
    {
        return [
            'is_adviser' => 'boolean',
            'can_manage_profile' => 'boolean',
            'can_manage_grades' => 'boolean',
            'can_manage_attendance' => 'boolean',
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
}
