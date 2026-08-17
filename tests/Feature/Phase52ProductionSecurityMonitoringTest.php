<?php

namespace Tests\Feature;

use App\Support\ProductionSecurityReadiness;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase52ProductionSecurityMonitoringTest extends TestCase
{
    public function test_source_only_production_security_readiness_passes(): void
    {
        $report = app(ProductionSecurityReadiness::class)->report(true);

        $this->assertTrue($report['ready']);
        $this->assertTrue($report['source_only']);
        $this->assertSame(0, $report['required_failures']);

        $this->assertSame(0, Artisan::call('nacs:production-security-readiness', [
            '--source-only' => true,
            '--strict' => true,
        ]));
    }

    public function test_full_readiness_remains_pending_without_real_host_evidence(): void
    {
        config([
            'production_security.host_gates' => [
                'tls_https_verified' => false,
                'database_private_verified' => false,
                'backup_restore_verified' => false,
                'central_logging_verified' => false,
                'waf_cdn_verified' => false,
                'access_review_verified' => false,
                'vapt_verified' => false,
            ],
        ]);

        $report = app(ProductionSecurityReadiness::class)->report(false);

        $this->assertFalse($report['ready']);

        $host = collect($report['checks'])->where('scope', 'real-host-evidence');

        $this->assertCount(7, $host);
        $this->assertTrue($host->every(static fn (array $check): bool => $check['passed'] === false));
    }

    public function test_real_host_evidence_flags_default_false_in_tracked_environment_example(): void
    {
        $env = (string) file_get_contents(base_path('.env.example'));

        foreach ([
            'NACS_PROD_TLS_HTTPS_VERIFIED=false',
            'NACS_PROD_DATABASE_PRIVATE_VERIFIED=false',
            'NACS_PROD_BACKUP_RESTORE_VERIFIED=false',
            'NACS_PROD_CENTRAL_LOGGING_VERIFIED=false',
            'NACS_PROD_WAF_CDN_VERIFIED=false',
            'NACS_PROD_ACCESS_REVIEW_VERIFIED=false',
            'NACS_PROD_VAPT_VERIFIED=false',
        ] as $expected) {
            $this->assertStringContainsString($expected, $env);
        }
    }
}
