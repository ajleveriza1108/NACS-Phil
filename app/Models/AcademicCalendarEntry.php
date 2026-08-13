<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendarEntry extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        'academic' => 'Academic',
        'holiday' => 'Holiday / No Classes',
        'exam' => 'Examination',
        'admissions' => 'Admissions',
        'meeting' => 'Parent / Staff Meeting',
        'activity' => 'School Activity',
        'recognition' => 'Recognition / Graduation',
    ];

    protected $fillable = [
        'title','category','description','starts_at','ends_at','is_all_day',
        'school_year','is_published','created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
