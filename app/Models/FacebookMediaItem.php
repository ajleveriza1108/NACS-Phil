<?php

namespace App\Models;

use App\Models\Concerns\HasContentAudit;
use App\Support\FacebookMediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FacebookMediaItem extends Model
{
    use SoftDeletes, HasContentAudit;

    public const MEDIA_TYPES = [
        'video' => 'Recorded Video',
        'live' => 'Facebook Live',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'title',
        'slug',
        'media_type',
        'facebook_url',
        'description',
        'starts_at',
        'status',
        'is_featured',
        'published_at',
        'public_confirmed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'public_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FacebookMediaItem $item): void {
            if (blank($item->slug)) {
                $item->slug = static::uniqueSlug($item->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function embedUrl(): ?string
    {
        return FacebookMediaUrl::embedUrl($this->facebook_url);
    }

    public function mediaTypeLabel(): string
    {
        return self::MEDIA_TYPES[$this->media_type] ?? 'Facebook Video';
    }

    private static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'facebook-video';
        $slug = $base;
        $counter = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
