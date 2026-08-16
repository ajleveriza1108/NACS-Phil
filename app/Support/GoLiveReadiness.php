<?php

namespace App\Support;

final class GoLiveReadiness
{
    /** @var array<int, string> */
    private const ERROR_VIEWS = [
        'errors/layout.blade.php',
        'errors/404.blade.php',
        'errors/419.blade.php',
        'errors/429.blade.php',
        'errors/500.blade.php',
        'errors/503.blade.php',
    ];

    public function __construct(private readonly LiveCutoverReadiness $cutover)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function sourceSummary(): array
    {
        $missingErrorViews = array_values(array_filter(
            self::ERROR_VIEWS,
            static fn (string $relative): bool => ! is_file(resource_path('views/'.$relative))
        ));

        $bootstrap = $this->read(base_path('bootstrap/app.php'));
        $deployment = $this->read(base_path('.github/deployment-templates/production-deploy.yml'));
        $phase39 = $this->read(base_path('PHASE39_FINAL_VISUAL_STAGING_ACCEPTANCE.md'));
        $phase40 = $this->read(base_path('PHASE40_PRODUCTION_LAUNCH.md'));
        $studentPortal = $this->read(config_path('student_portal.php'));
        $errorsCss = public_path('assets/current/media/2d311fe3b724-errors.css');

        $checks = [
            $this->check(
                'branded_error_pages',
                'Branded resilient error pages exist',
                $missingErrorViews === [] && is_file($errorsCss),
                $missingErrorViews === []
                    ? '404, 419, 429, 500, and 503 fallbacks are present.'
                    : 'Missing error view(s): '.implode(', ', $missingErrorViews)
            ),
            $this->check(
                'health_endpoint',
                'Laravel health endpoint remains configured',
                str_contains($bootstrap, "health: '/up'"),
                'Keep /up available as the non-sensitive uptime probe.'
            ),
            $this->check(
                'deployment_inactive',
                'Production auto-deploy remains inactive',
                str_contains($deployment, '# name:'),
                'The deployment template must remain inactive until the real host is reviewed.'
            ),
            $this->check(
                'manual_acceptance_guard',
                'Manual visual and device acceptance remains explicit',
                str_contains($phase39, 'Manual visual acceptance')
                    && str_contains($phase39, 'approximately 320px width')
                    && str_contains($phase39, 'does **not** mark visual acceptance complete'),
                'Phase 39 must never claim that automation performed the human visual/device review.'
            ),
            $this->check(
                'host_specific_launch_guard',
                'Production launch remains host-specific',
                str_contains($phase40, 'not** activated by the cumulative installer')
                    && str_contains($phase40, 'Deploy the exact approved `main` commit')
                    && str_contains($phase40, 'remains an inactive template'),
                'Phase 40 must require the real host, secrets, backups, and rollback plan.'
            ),
            $this->check(
                'student_documents_private',
                'Student document local fallback remains disabled',
                str_contains($studentPortal, "'allow_local_fallback' => false"),
                'Confidential student documents must not silently fall back to local/public storage.'
            ),
        ];

        $failed = array_values(array_filter(
            $checks,
            static fn (array $check): bool => ! $check['passed']
        ));

        return [
            'application' => 'NACS-Phil',
            'phase' => 42,
            'scope' => 'source',
            'checks' => $checks,
            'required_failures' => count($failed),
            'source_ready' => $failed === [],
            'note' => 'Source readiness does not replace manual acceptance or a real-host production cutover.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $source = $this->sourceSummary();
        $external = $this->cutover->summary();

        return [
            'application' => 'NACS-Phil',
            'phase' => 42,
            'source' => $source,
            'external_cutover' => $external,
            'source_ready' => $source['source_ready'] === true,
            'external_ready' => $external['ready_for_live_cutover'] === true,
            'ready_for_live' => $source['source_ready'] === true
                && $external['ready_for_live_cutover'] === true,
            'note' => 'Repository success is not production success. Manual and real-host gates remain authoritative.',
        ];
    }

    /**
     * @return array{key:string,label:string,passed:bool,detail:string}
     */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }

    private function read(string $path): string
    {
        $contents = @file_get_contents($path);

        return is_string($contents) ? $contents : '';
    }
}
