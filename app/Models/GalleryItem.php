<?php

namespace App\Models;

use App\Models\Concerns\HasContentAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryItem extends Model
{
    use HasFactory, SoftDeletes, HasContentAudit;

    protected $fillable = [
        'title','category','image_path','alt_text','caption','taken_at',
        'is_published','sort_order','consent_confirmed_at','photographer_credit',
        'workflow_status','submitted_for_review_at','reviewed_at',
        'reviewed_by_user_id','review_notes',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'consent_confirmed_at' => 'datetime',
            'submitted_for_review_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('workflow_status', 'published')
            ->where('is_published', true)
            ->whereNotNull('consent_confirmed_at');
    }
}
