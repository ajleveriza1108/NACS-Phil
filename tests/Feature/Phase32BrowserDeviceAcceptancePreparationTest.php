<?php

namespace Tests\Feature;

use App\Support\BrowserAcceptanceGate;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase32BrowserDeviceAcceptancePreparationTest extends TestCase
{
    public function test_acceptance_gate_defines_all_required_device_and_interaction_checks(): void
    {
        $checks = app(BrowserAcceptanceGate::class)->requiredChecks();

        $this->assertGreaterThanOrEqual(20, count($checks));

        foreach ([
            'phone_320',
            'tablet_portrait',
            'tablet_landscape',
            'ultrawide',
            'facebook_playback',
            'turnstile_desktop_mobile',
            'admin_login_2fa',
            'admin_crud_safe_trash',
            'no_crop_overflow',
        ] as $required) {
            $this->assertArrayHasKey($required, $checks);
        }
    }

    public function test_acceptance_command_reports_manual_state_without_marking_it_complete(): void
    {
        Artisan::call('nacs:acceptance-check', ['--json' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('"phase": 32', $output);
        $this->assertStringContainsString('"manual_checks"', $output);
        $this->assertStringContainsString('"ready_for_cutover"', $output);
    }

    public function test_example_acceptance_record_starts_false_and_is_not_the_real_record(): void
    {
        $example = json_decode(
            (string) file_get_contents(base_path('BROWSER_DEVICE_ACCEPTANCE.example.json')),
            true
        );

        $this->assertIsArray($example);
        $this->assertSame(32, $example['phase']);
        $this->assertNotContains(true, array_values($example['checks']));

        $gitignore = (string) file_get_contents(base_path('.gitignore'));
        $this->assertStringContainsString('/.nacs-browser-acceptance.json', $gitignore);
    }
}
