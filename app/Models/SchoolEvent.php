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
        'title', 'slug', 'description', 'venue', 'starts_at', 'ends_at',
        'is_all_day', 'published_at', 'registration_url',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
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
                    $slug = $base . '-' . $counter;
                    $counter++;
                }

                $event->slug = $slug;
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}