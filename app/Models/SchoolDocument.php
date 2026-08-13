<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','slug','description','category','file_path','original_name',
        'mime_type','file_size','school_year','audience','published_at',
        'expires_at','sort_order','uploaded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SchoolDocument $document): void {
            if (blank($document->slug)) {
                $base = Str::slug($document->title) ?: 'document';
                $slug = $base;
                $counter = 2;

                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$counter++;
                }

                $document->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query
            ->where('audience', 'public')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function formattedSize(): string
    {
        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 1).' MB';
        }

        return number_format(max(1, $this->file_size) / 1024, 0).' KB';
    }
}
