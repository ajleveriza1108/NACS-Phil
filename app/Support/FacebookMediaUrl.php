<?php

namespace App\Support;

final class FacebookMediaUrl
{
    public static function normalize(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || strlen($url) > 2048) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        if (strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            return null;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        $facebookHost = $host === 'facebook.com' || str_ends_with($host, '.facebook.com');
        $watchHost = $host === 'fb.watch' || str_ends_with($host, '.fb.watch');

        if (! $facebookHost && ! $watchHost) {
            return null;
        }

        return $url;
    }

    public static function embedUrl(string $url): ?string
    {
        $normalized = self::normalize($url);

        if ($normalized === null) {
            return null;
        }

        return 'https://www.facebook.com/plugins/video.php?href='
            .rawurlencode($normalized)
            .'&show_text=false&width=1200';
    }
}
