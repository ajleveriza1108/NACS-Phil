<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'page_key','title','meta_description','social_title','social_description',
        'social_image_path','no_index','canonical_url','updated_by_user_id',
    ];

    protected function casts(): array
    {
        return ['no_index' => 'boolean'];
    }

    public static function forCurrentRequest(): ?self
    {
        $route = request()->route();
        $name = $route?->getName();

        if (! $name) {
            return null;
        }

        return static::query()->where('page_key', $name)->first();
    }
}
