<?php

namespace Tests\Feature;

use App\Support\GoLiveReadiness;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase42GoLiveReadinessTest extends TestCase
{
    public function test_source_only_go_live_readiness_passes_without_claiming_live_cutover(): void
    {
        $report = app(GoLiveReadiness::class)->sourceSummary();

        $this->assertTrue($report['source_ready']);
        $this->assertSame(0, $report['required_failures']);

        $exit = Artisan::call('nacs:go-live-check', [
            '--source-only' => true,
            '--strict' => true,
        ]);

        $this->assertSame(0, $exit);
    }

    public function test_full_go_live_report_does_not_falsely_mark_testing_environment_live(): void
    {
        $report = app(GoLiveReadiness::class)->summary();

        $this->assertTrue($report['source_ready']);
        $this->assertFalse($report['external_ready']);
        $this->assertFalse($report['ready_for_live']);
    }

    public function test_health_endpoint_is_available_without_sensitive_details(): void
    {
        $response = $this->get('/up')->assertOk();

        $body = $response->getContent();

        $this->assertIsString($body);
        $this->assertStringNotContainsString('APP_KEY', $body);
        $this->assertStringNotContainsString('DB_PASSWORD', $body);
        $this->assertStringNotContainsString('TURNSTILE_SECRET', $body);
    }

    public function test_error_pages_are_branded_but_do_not_depend_on_database_settings_or_vite(): void
    {
        $layout = file_get_contents(resource_path('views/errors/layout.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('/assets/phase42-launch/errors.css', $layout);
        $this->assertStringContainsString('/assets/phase17-theme/nacs-official-logo.png', $layout);
        $this->assertStringNotContainsString('SchoolSetting::', $layout);
        $this->assertStringNotContainsString('@vite', $layout);

        foreach ([404, 419, 429, 500, 503] as $status) {
            $path = resource_path('views/errors/'.$status.'.blade.php');
            $this->assertFileExists($path);
            $this->assertStringContainsString("@extends('errors.layout')", (string) file_get_contents($path));
        }
    }

    public function test_phase_forty_two_keeps_manual_and_host_specific_gates_explicit(): void
    {
        $phase39 = file_get_contents(base_path('PHASE39_FINAL_VISUAL_STAGING_ACCEPTANCE.md'));
        $phase40 = file_get_contents(base_path('PHASE40_PRODUCTION_LAUNCH.md'));
        $phase42 = file_get_contents(base_path('PHASE42_GO_LIVE_READINESS.md'));

        $this->assertStringContainsString('Manual visual acceptance', (string) $phase39);
        $this->assertStringContainsString('approximately 320px width', (string) $phase39);
        $this->assertStringContainsString('not** activated by the cumulative installer', (string) $phase40);
        $this->assertStringContainsString('remains an inactive template', (string) $phase40);
        $this->assertStringContainsString('Repository publication success is not proof that the site is already live.', (string) $phase42);
        $this->assertStringContainsString('independent VAPT', (string) $phase42);
    }
}
