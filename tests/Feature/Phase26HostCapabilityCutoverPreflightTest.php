<?php

namespace Tests\Feature;

use App\Support\HostCapabilityReport;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase26HostCapabilityCutoverPreflightTest extends TestCase
{
    public function test_host_report_contains_required_php_database_upload_storage_and_build_checks(): void
    {
        $report = new HostCapabilityReport();
        $checks = collect($report->checks())->keyBy('key');

        foreach ([
            'php_version',
            'extension_ctype',
            'extension_fileinfo',
            'extension_filter',
            'extension_mbstring',
            'extension_openssl',
            'extension_pdo',
            'extension_session',
            'extension_tokenizer',
            'pdo_driver',
            'upload_limit',
            'post_limit',
            'writable_storage',
            'writable_bootstrap_cache',
            'writable_private_storage',
            'writable_public_storage',
            'public_index',
            'build_manifest',
        ] as $key) {
            $this->assertTrue($checks->has($key), $key);
            $this->assertTrue($checks[$key]['required'], $key);
        }

        $this->assertTrue($checks->has('memory_limit'));
        $this->assertFalse($checks['memory_limit']['required']);
        $this->assertTrue($checks->has('public_storage_link'));
        $this->assertFalse($checks['public_storage_link']['required']);
        $this->assertTrue($checks->has('shell_exec'));
        $this->assertFalse($checks['shell_exec']['required']);
    }

    public function test_host_check_command_is_registered_and_non_strict_mode_is_read_only(): void
    {
        $exit = Artisan::call('nacs:host-check');
        $output = Artisan::output();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('NACS-Phil Host Capability Preflight', $output);
        $this->assertStringContainsString('does not print environment secrets or change server settings', $output);
    }

    public function test_host_check_json_output_contains_no_environment_secret_values(): void
    {
        $exit = Artisan::call('nacs:host-check', ['--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertSame(0, $exit);
        $this->assertIsArray($decoded);
        $this->assertSame('NACS-Phil', $decoded['application']);
        $this->assertArrayHasKey('php_version', $decoded);
        $this->assertArrayHasKey('database_connection', $decoded);
        $this->assertArrayHasKey('required_failures', $decoded);
        $this->assertArrayHasKey('checks', $decoded);

        $serialized = json_encode($decoded);

        $this->assertIsString($serialized);
        $this->assertStringNotContainsString('DB_PASSWORD', $serialized);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $serialized);
        $this->assertStringNotContainsString('APP_KEY=', $serialized);
    }

    public function test_host_preflight_document_preserves_safe_document_root_and_private_storage_rules(): void
    {
        $guide = file_get_contents(base_path('HOST_CUTOVER_PREFLIGHT.md'));

        $this->assertIsString($guide);
        $this->assertStringContainsString("Laravel's `public` directory", $guide);
        $this->assertStringContainsString('Do not expose the repository root', $guide);
        $this->assertStringContainsString('Admissions/private files must stay outside the web-accessible public tree', $guide);
        $this->assertStringContainsString('upload_max_filesize = 5M', $guide);
        $this->assertStringContainsString('post_max_size = 6M', $guide);
        $this->assertStringContainsString('php artisan nacs:production-check --strict', $guide);
        $this->assertStringContainsString('Once the actual provider and domain are known', $guide);
    }

    public function test_composer_runtime_requirement_matches_php_eight_four_one_baseline(): void
    {
        $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);

        $this->assertIsArray($composer);
        $this->assertSame('^8.4.1', $composer['require']['php'] ?? null);
        $this->assertSame('^13.8', $composer['require']['laravel/framework'] ?? null);
    }
}
