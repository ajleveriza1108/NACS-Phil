<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SchoolSetting extends Model
{
    protected $fillable = ['key','value','group','is_public','updated_by_user_id'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('nacs.school.settings'));
        static::deleted(fn () => Cache::forget('nacs.school.settings'));
    }

    public static function allValues(): array
    {
        return Cache::remember('nacs.school.settings', 300, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function valueFor(string $key, ?string $default = null): ?string
    {
        $values = static::allValues();

        return array_key_exists($key, $values) && filled($values[$key]) ? $values[$key] : $default;
    }
}
