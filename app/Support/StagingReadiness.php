<?php

namespace App\Support;

final class StagingReadiness
{
    public function __construct(private readonly HostCapabilityReport $host)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $url = trim((string) config('app.url'));
        $hostName = strtolower((string) parse_url($url, PHP_URL_HOST));
        $expectedTurnstileHost = strtolower(trim((string) config('services.turnstile.expected_hostname')));
        $siteKey = trim((string) config('services.turnstile.site_key'));
        $secret = trim((string) config('services.turnstile.secret_key'));
        $hostChecks = $this->host->checks();

        $checks = [
            $this->check(
                'controlled_environment',
                'Controlled staging/production environment',
                in_array((string) config('app.env'), ['staging', 'production'], true),
                'Use APP_ENV=staging on staging or APP_ENV=production on the live host.'
            ),
            $this->check(
                'debug_disabled',
                'Debug disabled',
                config('app.debug') === false,
                'APP_DEBUG=false is required outside local development.'
            ),
            $this->check(
                'https_url',
                'HTTPS non-local application URL',
                str_starts_with($url, 'https://')
                    && $hostName !== ''
                    && ! in_array($hostName, ['localhost', '127.0.0.1'], true)
                    && ! str_contains($hostName, 'YOUR-FINAL-DOMAIN'),
                'Use the real HTTPS staging hostname.'
            ),
            $this->check(
                'production_database',
                'Server database is not SQLite',
                ! in_array((string) config('database.default'), ['', 'sqlite'], true),
                'Use the staging MySQL/MariaDB database.'
            ),
            $this->check(
                'secure_session',
                'Secure encrypted server session',
                config('session.secure') === true
                    && config('session.encrypt') === true
                    && ! in_array((string) config('session.driver'), ['', 'array', 'cookie'], true),
                'Use encrypted HTTPS-only server-side sessions.'
            ),
            $this->check(
                'persistent_cache',
                'Persistent cache',
                ! in_array((string) config('cache.default'), ['', 'array'], true),
                'Use a persistent cache store.'
            ),
            $this->check(
                'turnstile_enabled',
                'Turnstile enabled for staging browser acceptance',
                config('services.turnstile.enabled') === true,
                'Enable Turnstile on the staging hostname before browser acceptance.'
            ),
            $this->check(
                'turnstile_site_key',
                'Turnstile site key configured',
                $this->isConfiguredCredential($siteKey),
                'Use a valid staging/production Turnstile site key.'
            ),
            $this->check(
                'turnstile_secret',
                'Turnstile secret configured server-side',
                $this->isConfiguredCredential($secret),
                'Keep the valid Turnstile secret only in the server environment.'
            ),
            $this->check(
                'turnstile_hostname',
                'Turnstile hostname matches APP_URL',
                $hostName !== '' && $expectedTurnstileHost === $hostName,
                'TURNSTILE_EXPECTED_HOSTNAME must exactly match the APP_URL hostname.'
            ),
            $this->check(
                'build_manifest',
                'Production frontend build available',
                is_file(public_path('build/manifest.json')),
                'Deploy the Vite production build.'
            ),
            $this->check(
                'host_capabilities',
                'Required host capabilities',
                $this->host->requiredFailures($hostChecks) === [],
                'Run php artisan nacs:host-check --strict on the staging host.'
            ),
        ];

        $failed = array_values(array_filter(
            $checks,
            static fn (array $check): bool => ! $check['passed']
        ));

        return [
            'application' => 'NACS-Phil',
            'phase' => 31,
            'checks' => $checks,
            'required_failures' => count($failed),
            'ready_for_staging_acceptance' => $failed === [],
            'note' => 'This check never prints database passwords, APP_KEY, SMTP credentials, or Turnstile secrets.',
        ];
    }

    private function isConfiguredCredential(string $value): bool
    {
        return $value !== ''
            && ! str_contains($value, 'YOUR_')
            && ! str_contains($value, 'YOUR-');
    }

    /**
     * @return array{key:string,label:string,passed:bool,detail:string}
     */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }
}
