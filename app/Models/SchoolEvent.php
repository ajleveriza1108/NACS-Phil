<?php

namespace App\Models;

use App\Models\Concerns\HasContentAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolEvent extends Model
{
    use HasFactory, SoftDeletes, HasContentAudit;

    protected $fillable = [
        'title','slug','description','venue','starts_at','ends_at','is_all_day',
        'published_at','registration_url','workflow_status','submitted_for_review_at',
        'reviewed_at','reviewed_by_user_id','review_notes','scheduled_publish_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'submitted_for_review_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_all_day' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SchoolEvent $event): void {
            if (blank($event->slug)) {
                $base = Str::slug($event->title) ?: 'event';
                $slug = $base;
                $counter = 2;

                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$counter++;
                }

                $event->slug = $slug;
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('workflow_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
