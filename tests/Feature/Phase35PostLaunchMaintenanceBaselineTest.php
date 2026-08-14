<?php

namespace Tests\Feature;

use App\Support\PostLaunchHealth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase35PostLaunchMaintenanceBaselineTest extends TestCase
{
    public function test_post_launch_command_defaults_to_no_external_http_request(): void
    {
        Artisan::call('nacs:post-launch-check', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"phase": 35', $output);
        $this->assertStringContainsString('"performed": false', $output);
    }

    public function test_live_http_mode_checks_public_paths_and_security_headers_without_credentials(): void
    {
        config()->set('app.url', 'https://nacs-phase35.example.test');

        Http::fake([
            'https://nacs-phase35.example.test*' => Http::response(
                '<html><body>NACS-Phil</body></html>',
                200,
                [
                    'X-Content-Type-Options' => 'nosniff',
                    'Content-Security-Policy' => "default-src 'self'; object-src 'none'",
                    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
                ]
            ),
        ]);

        $report = app(PostLaunchHealth::class)->summary(true);

        $this->assertTrue($report['live_http']['performed']);
        $this->assertSame(0, $report['live_http']['failures']);
        $this->assertArrayHasKey('/', $report['live_http']['paths']);
        $this->assertArrayHasKey('/privacy', $report['live_http']['paths']);
    }

    public function test_phase_thirty_five_docs_and_administrator_handbook_exist(): void
    {
        $this->assertFileExists(base_path('PHASE35_POST_LAUNCH_MAINTENANCE.md'));
        $this->assertFileExists(base_path('NACS_PHIL_ADMINISTRATOR_HANDBOOK.md'));

        $handbook = (string) file_get_contents(base_path('NACS_PHIL_ADMINISTRATOR_HANDBOOK.md'));
        $this->assertStringContainsString('nacs:cutover-check --strict', $handbook);
        $this->assertStringContainsString('nacs:post-launch-check --live-http --strict', $handbook);
        $this->assertStringContainsString('1 Corinthians 14:40, KJV', $handbook);
    }
}
