<?php

namespace Tests\Feature;

use App\Support\SecurityRecoveryReport;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase33SecurityRecoveryHardeningTest extends TestCase
{
    public function test_public_response_has_restrictive_csp_with_turnstile_and_facebook_allowances(): void
    {
        $response = $this->get(route('home'));
        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' https://challenges.cloudflare.com", $csp);
        $this->assertStringContainsString("connect-src 'self' https://challenges.cloudflare.com", $csp);
        $this->assertStringContainsString(
            "frame-src 'self' https://challenges.cloudflare.com https://www.facebook.com",
            $csp
        );

        $response->assertHeader(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );
    }

    public function test_security_report_detects_no_sensitive_repository_artifacts_under_public(): void
    {
        $report = app(SecurityRecoveryReport::class)->summary();

        $this->assertSame([], $report['public_leak_paths']);
        $this->assertArrayHasKey('recovery', $report);
        $this->assertArrayHasKey('ready_for_cutover', $report);

        $checks = [];
        foreach ($report['checks'] as $check) {
            $checks[$check['key']] = $check;
        }

        $this->assertArrayHasKey('strict_csp', $checks);
        $this->assertTrue($checks['strict_csp']['passed']);
    }

    public function test_recovery_command_and_local_only_record_are_prepared(): void
    {
        Artisan::call('nacs:recovery-check', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"phase": 33', $output);

        $gitignore = (string) file_get_contents(base_path('.gitignore'));
        $this->assertStringContainsString('/.nacs-recovery-verification.json', $gitignore);
        $this->assertFileExists(base_path('RECOVERY_VERIFICATION.example.json'));
        $this->assertFileExists(base_path('PHASE33_SECURITY_RECOVERY_HARDENING.md'));
    }
}
