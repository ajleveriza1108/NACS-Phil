<?php

namespace App\Models;

use App\Models\Concerns\HasContentAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory, SoftDeletes, HasContentAudit;

    public const WORKFLOW_STATUSES = [
        'draft' => 'Draft',
        'pending_review' => 'Pending Review',
        'changes_requested' => 'Changes Requested',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'title','slug','excerpt','body','type','starts_at','ends_at','published_at',
        'is_featured','sort_order','workflow_status','submitted_for_review_at',
        'reviewed_at','reviewed_by_user_id','review_notes','scheduled_publish_at',
        'audience','is_pinned',
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
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Announcement $announcement): void {
            if (blank($announcement->slug)) {
                $announcement->slug = static::uniqueSlug($announcement->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('audience', 'public')
            ->where('workflow_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    private static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'announcement';
        $slug = $base;
        $counter = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
