<?php

namespace Tests\Feature;

use App\Support\LiveCutoverReadiness;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase34LiveCutoverPreparationTest extends TestCase
{
    public function test_cutover_gate_is_registered_and_reports_without_modifying_environment(): void
    {
        Artisan::call('nacs:cutover-check', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"phase": 34', $output);
        $this->assertStringContainsString('"ready_for_live_cutover"', $output);
        $this->assertStringContainsString('"production_readiness"', $output);
        $this->assertStringContainsString('"browser_device_acceptance"', $output);
        $this->assertStringContainsString('"security_recovery"', $output);
    }

    public function test_local_testing_environment_is_not_falsely_reported_ready_for_live_cutover(): void
    {
        $report = app(LiveCutoverReadiness::class)->summary();

        $this->assertFalse($report['ready_for_live_cutover']);
        $this->assertGreaterThan(0, $report['required_failures']);
    }

    public function test_live_cutover_runbook_explicitly_keeps_external_actions_manual(): void
    {
        $guide = (string) file_get_contents(base_path('PHASE34_LIVE_CUTOVER.md'));

        $this->assertStringContainsString('nacs:cutover-check --strict', $guide);
        $this->assertStringContainsString('Change DNS only after all gates pass', $guide);
        $this->assertStringContainsString('cannot execute the actual DNS', $guide);
    }
}
