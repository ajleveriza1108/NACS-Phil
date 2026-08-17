<?php

namespace App\Support;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;

final class SchoolDocumentBranding
{
    public static function data(): array
    {
        $schoolName = SchoolSetting::valueFor(
            'header_school_name',
            SchoolSetting::valueFor('school_name', 'Noel Academy Christian of Sariaya Philippines, Inc.')
        );

        $shortName = SchoolSetting::valueFor(
            'header_short_name',
            SchoolSetting::valueFor('short_name', config('nacs.short_name', 'NACS-Phil'))
        );

        return [
            'school_name' => $schoolName,
            'short_name' => $shortName,
            'address' => SchoolSetting::valueFor('address', ''),
            'phone' => SchoolSetting::valueFor('phone', ''),
            'email' => SchoolSetting::valueFor('email', ''),
            'tagline' => SchoolSetting::valueFor('tagline', ''),
            'watermark' => SchoolSetting::valueFor('document_watermark', $shortName),
            'logo_data_uri' => self::logoDataUri(),
        ];
    }

    private static function logoDataUri(): ?string
    {
        $path = SchoolSetting::valueFor('official_logo_path');
        $absolute = null;

        if (filled($path) && str_starts_with($path, 'branding/') && Storage::disk('public')->exists($path)) {
            $absolute = Storage::disk('public')->path($path);
        } else {
            $fallback = public_path('assets/current/media/698f631ea74f-nacs-official-logo.png');

            if (is_file($fallback)) {
                $absolute = $fallback;
            }
        }

        if (! is_string($absolute) || ! is_file($absolute) || ! is_readable($absolute)) {
            return null;
        }

        $extension = strtolower((string) pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        $content = file_get_contents($absolute);

        return $content === false ? null : 'data:'.$mime.';base64,'.base64_encode($content);
    }
}
