<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

    public static function logoUrl(): string
    {
        $path = static::valueFor('official_logo_path');

        if (filled($path) && str_starts_with($path, 'branding/') && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('assets/current/media/698f631ea74f-nacs-official-logo.png');
    }

    public static function logoAlt(): string
    {
        return static::valueFor(
            'official_logo_alt',
            static::valueFor('short_name', config('nacs.short_name')).' logo'
        ) ?? 'School logo';
    }

    public static function officialBrandingApproved(): bool
    {
        $path = static::valueFor('official_logo_path');
        $approvedAt = static::valueFor('official_branding_approved_at');

        return filled($path)
            && filled($approvedAt)
            && str_starts_with($path, 'branding/')
            && Storage::disk('public')->exists($path);
    }
}
