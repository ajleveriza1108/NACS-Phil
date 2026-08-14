<?php

namespace App\Support;

final class ProductionReadiness
{
    /**
     * @return array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}>
     */
    public function checks(): array
    {
        $url = (string) config('app.url');
        $key = (string) config('app.key');
        $database = (string) config('database.default');
        $sessionDriver = (string) config('session.driver');
        $sameSite = strtolower((string) config('session.same_site'));
        $cacheStore = (string) config('cache.default');
        $turnstileEnabled = config('services.turnstile.enabled') === true;
        $turnstileSiteKey = trim((string) config('services.turnstile.site_key'));
        $turnstileSecretKey = trim((string) config('services.turnstile.secret_key'));
        $turnstileHostname = trim((string) config('services.turnstile.expected_hostname'));

        $privateStorage = storage_path('app/private');
        $publicStorage = storage_path('app/public');
        $buildManifest = public_path('build/manifest.json');
        $publicStorageLink = public_path('storage');

        return [
            $this->check(
                'environment',
                'Production environment',
                config('app.env') === 'production',
                true,
                'APP_ENV must be production on the live server.'
            ),
            $this->check(
                'debug',
                'Debug disabled',
                config('app.debug') === false,
                true,
                'APP_DEBUG must be false in production.'
            ),
            $this->check(
                'https_url',
                'HTTPS application URL',
                str_starts_with($url, 'https://') && ! str_contains($url, 'YOUR-FINAL-DOMAIN'),
                true,
                'APP_URL must use the final HTTPS domain.'
            ),
            $this->check(
                'app_key',
                'Application key configured',
                $key !== '' && ! str_contains($key, 'GENERATE_ON_SERVER'),
                true,
                'Generate APP_KEY on the production server and keep it secret.'
            ),
            $this->check(
                'production_database',
                'Production database is not SQLite',
                $database !== '' && $database !== 'sqlite',
                true,
                'Use the hosting production database, normally MySQL or MariaDB.'
            ),
            $this->check(
                'session_driver',
                'Server-side session driver',
                $sessionDriver !== '' && ! in_array($sessionDriver, ['array', 'cookie'], true),
                true,
                'Use database, file, or Redis sessions rather than array/cookie sessions.'
            ),
            $this->check(
                'session_encryption',
                'Session encryption enabled',
                config('session.encrypt') === true,
                true,
                'SESSION_ENCRYPT=true is required for the live NACS-Phil deployment.'
            ),
            $this->check(
                'secure_cookie',
                'HTTPS-only session cookie',
                config('session.secure') === true,
                true,
                'SESSION_SECURE_COOKIE=true prevents the session cookie from being sent over HTTP.'
            ),
            $this->check(
                'http_only_cookie',
                'HTTP-only session cookie',
                config('session.http_only') === true,
                true,
                'SESSION_HTTP_ONLY should remain true.'
            ),
            $this->check(
                'same_site_cookie',
                'SameSite session protection',
                in_array($sameSite, ['lax', 'strict'], true),
                true,
                'SESSION_SAME_SITE should normally be lax or strict.'
            ),
            $this->check(
                'cache_store',
                'Persistent cache store',
                $cacheStore !== '' && $cacheStore !== 'array',
                true,
                'Use a persistent production cache store such as database or Redis.'
            ),
            $this->check(
                'turnstile_enabled',
                'Adaptive anti-bot protection enabled',
                $turnstileEnabled,
                true,
                'TURNSTILE_ENABLED=true is required on the live NACS-Phil website.'
            ),
            $this->check(
                'turnstile_site_key',
                'Turnstile site key configured',
                $turnstileSiteKey !== '' && ! str_contains($turnstileSiteKey, 'YOUR_CLOUDFLARE'),
                true,
                'Configure the real production Turnstile site key.'
            ),
            $this->check(
                'turnstile_secret_key',
                'Turnstile secret key configured',
                $turnstileSecretKey !== '' && ! str_contains($turnstileSecretKey, 'YOUR_CLOUDFLARE'),
                true,
                'Configure the Turnstile secret only in the production server environment.'
            ),
            $this->check(
                'turnstile_hostname',
                'Turnstile production hostname configured',
                $turnstileHostname !== ''
                    && ! str_contains($turnstileHostname, 'YOUR-FINAL-DOMAIN')
                    && ! str_contains($turnstileHostname, '://'),
                true,
                'Set TURNSTILE_EXPECTED_HOSTNAME to the exact final hostname without a URL scheme.'
            ),
            $this->check(
                'private_storage',
                'Private storage writable',
                is_dir($privateStorage) && is_writable($privateStorage),
                true,
                'storage/app/private must exist and be writable by PHP.'
            ),
            $this->check(
                'public_storage',
                'Public media storage writable',
                is_dir($publicStorage) && is_writable($publicStorage),
                true,
                'storage/app/public must exist and be writable by PHP.'
            ),
            $this->check(
                'build_manifest',
                'Production frontend build present',
                is_file($buildManifest),
                true,
                'public/build/manifest.json must exist after npm run build or artifact deployment.'
            ),
            $this->check(
                'public_storage_link',
                'Public storage link available',
                is_link($publicStorageLink) || is_dir($publicStorageLink),
                false,
                'Run php artisan storage:link when the host supports symlinks; otherwise configure the host equivalent.'
            ),
            $this->check(
                'mail_transport',
                'Real outgoing mail transport',
                ! in_array((string) config('mail.default'), ['', 'log', 'array'], true),
                false,
                'Configure SMTP before enabling production email notifications.'
            ),
        ];
    }

    /**
     * @param array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}> $checks
     */
    public function requiredFailures(array $checks): array
    {
        return array_values(array_filter(
            $checks,
            fn (array $check): bool => $check['required'] && ! $check['passed']
        ));
    }

    /**
     * @return array{key:string,label:string,passed:bool,required:bool,detail:string}
     */
    private function check(
        string $key,
        string $label,
        bool $passed,
        bool $required,
        string $detail
    ): array {
        return compact('key', 'label', 'passed', 'required', 'detail');
    }
}
