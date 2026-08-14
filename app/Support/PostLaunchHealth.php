<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

final class PostLaunchHealth
{
    /** @var array<int, string> */
    private const LIVE_PATHS = [
        '/',
        '/about',
        '/programs',
        '/admissions',
        '/contact',
        '/privacy',
        '/robots.txt',
    ];

    public function __construct(
        private readonly ProductionReadiness $production,
        private readonly LiveCutoverReadiness $cutover,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(bool $liveHttp = false): array
    {
        $productionChecks = $this->production->checks();
        $productionFailures = $this->production->requiredFailures($productionChecks);
        $cutover = $this->cutover->summary();

        $live = [
            'performed' => false,
            'paths' => [],
            'failures' => 0,
        ];

        if ($liveHttp) {
            $live = $this->liveHttpSummary();
        }

        $ready = $productionFailures === []
            && $cutover['ready_for_live_cutover'] === true
            && (! $liveHttp || $live['failures'] === 0);

        return [
            'application' => 'NACS-Phil',
            'phase' => 35,
            'production_required_failures' => count($productionFailures),
            'cutover_ready' => $cutover['ready_for_live_cutover'],
            'live_http' => $live,
            'ready_for_post_launch_baseline' => $ready,
            'note' => $liveHttp
                ? 'Live HTTP verification was requested.'
                : 'Add --live-http only after the final HTTPS domain is serving the production release.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveHttpSummary(): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $host = (string) parse_url($base, PHP_URL_HOST);

        if (! str_starts_with($base, 'https://')
            || $host === ''
            || in_array(strtolower($host), ['localhost', '127.0.0.1'], true)
            || str_contains($host, 'YOUR-FINAL-DOMAIN')
        ) {
            return [
                'performed' => true,
                'paths' => [],
                'failures' => 1,
                'error' => 'APP_URL is not a valid final HTTPS hostname.',
            ];
        }

        $results = [];
        $failures = 0;

        foreach (self::LIVE_PATHS as $path) {
            try {
                $response = Http::accept('text/html')->timeout(7)->get($base.$path);
                $passed = $response->successful();

                if ($path === '/') {
                    $passed = $passed
                        && strtolower((string) $response->header('X-Content-Type-Options')) === 'nosniff'
                        && str_contains((string) $response->header('Content-Security-Policy'), "default-src 'self'")
                        && str_contains((string) $response->header('Strict-Transport-Security'), 'max-age=');
                }

                $results[$path] = [
                    'passed' => $passed,
                    'status' => $response->status(),
                ];

                if (! $passed) {
                    $failures++;
                }
            } catch (Throwable) {
                $results[$path] = [
                    'passed' => false,
                    'status' => null,
                ];
                $failures++;
            }
        }

        return [
            'performed' => true,
            'paths' => $results,
            'failures' => $failures,
        ];
    }
}
