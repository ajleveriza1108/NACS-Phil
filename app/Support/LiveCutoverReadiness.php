<?php

namespace App\Support;

final class LiveCutoverReadiness
{
    public function __construct(
        private readonly ProductionReadiness $production,
        private readonly HostCapabilityReport $host,
        private readonly ProductionDataInventory $data,
        private readonly BrowserAcceptanceGate $acceptance,
        private readonly SecurityRecoveryReport $recovery,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $productionChecks = $this->production->checks();
        $hostChecks = $this->host->checks();
        $data = $this->data->summary();
        $acceptance = $this->acceptance->summary();
        $recovery = $this->recovery->summary();

        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $turnstileHost = strtolower(trim((string) config('services.turnstile.expected_hostname')));

        $checks = [
            $this->check(
                'production_readiness',
                'Production environment checks',
                $this->production->requiredFailures($productionChecks) === [],
                'php artisan nacs:production-check --strict'
            ),
            $this->check(
                'host_readiness',
                'Hosting capability checks',
                $this->host->requiredFailures($hostChecks) === [],
                'php artisan nacs:host-check --strict'
            ),
            $this->check(
                'data_migration_decision',
                'Production data migration decision',
                $data['ready_for_controlled_migration'] === true,
                'php artisan nacs:data-audit --strict'
            ),
            $this->check(
                'browser_device_acceptance',
                'Real browser/device acceptance',
                $acceptance['ready_for_cutover'] === true,
                'php artisan nacs:acceptance-check --strict'
            ),
            $this->check(
                'security_recovery',
                'Security and restore verification',
                $recovery['ready_for_cutover'] === true,
                'php artisan nacs:recovery-check --strict'
            ),
            $this->check(
                'turnstile_hostname_matches_app',
                'Turnstile hostname equals APP_URL host',
                $appHost !== '' && $turnstileHost === $appHost,
                'Production APP_URL and TURNSTILE_EXPECTED_HOSTNAME must name the same host.'
            ),
        ];

        $failed = array_values(array_filter(
            $checks,
            static fn (array $check): bool => ! $check['passed']
        ));

        return [
            'application' => 'NACS-Phil',
            'phase' => 34,
            'checks' => $checks,
            'required_failures' => count($failed),
            'ready_for_live_cutover' => $failed === [],
            'note' => 'This command never changes DNS, SSL, databases, files, or credentials.',
        ];
    }

    /**
     * @return array{key:string,label:string,passed:bool,detail:string}
     */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }
}
