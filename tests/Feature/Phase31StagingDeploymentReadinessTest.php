<?php

namespace Tests\Feature;

use App\Support\StagingReadiness;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase31StagingDeploymentReadinessTest extends TestCase
{
    public function test_staging_report_never_exposes_turnstile_secret_value(): void
    {
        config()->set('services.turnstile.secret_key', 'phase31-do-not-print-this-secret');

        Artisan::call('nacs:staging-check', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"phase": 31', $output);
        $this->assertStringContainsString('"ready_for_staging_acceptance"', $output);
        $this->assertStringNotContainsString('phase31-do-not-print-this-secret', $output);
    }

    public function test_staging_summary_contains_required_host_and_security_gates(): void
    {
        $report = app(StagingReadiness::class)->summary();
        $keys = array_column($report['checks'], 'key');

        foreach ([
            'controlled_environment',
            'debug_disabled',
            'https_url',
            'production_database',
            'secure_session',
            'persistent_cache',
            'turnstile_enabled',
            'turnstile_hostname',
            'build_manifest',
            'host_capabilities',
        ] as $required) {
            $this->assertContains($required, $keys);
        }
    }

    public function test_phase_thirty_one_runbook_exists(): void
    {
        $this->assertFileExists(base_path('PHASE31_STAGING_DEPLOYMENT.md'));
        $guide = (string) file_get_contents(base_path('PHASE31_STAGING_DEPLOYMENT.md'));

        $this->assertStringContainsString('Laravel `public`', $guide);
        $this->assertStringContainsString('nacs:staging-check --strict', $guide);
        $this->assertStringContainsString('cannot be performed from the repository alone', $guide);
    }
}
