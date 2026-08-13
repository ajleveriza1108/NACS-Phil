<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','position','department','biography','credentials','grade_subject',
        'photo_path','alt_text','sort_order','is_featured','is_published',
        'consent_confirmed_at','created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'consent_confirmed_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('photo_path')->orWhereNotNull('consent_confirmed_at'));
    }
}
