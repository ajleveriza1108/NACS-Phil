<?php

namespace Tests\Feature;

use App\Support\ProductionReadiness;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase25ProductionDeploymentPreparationTest extends TestCase
{
    public function test_project_env_example_has_nacs_identity_and_explicit_session_security_controls(): void
    {
        $env = file_get_contents(base_path('.env.example'));

        $this->assertIsString($env);
        $this->assertStringContainsString('APP_NAME="NACS-Phil"', $env);
        $this->assertStringContainsString('APP_ENV=local', $env);
        $this->assertStringContainsString('DB_CONNECTION=sqlite', $env);
        $this->assertStringContainsString('SESSION_ENCRYPT=false', $env);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=false', $env);
        $this->assertStringContainsString('SESSION_HTTP_ONLY=true', $env);
        $this->assertStringContainsString('SESSION_SAME_SITE=lax', $env);
    }

    public function test_tracked_production_template_contains_placeholders_not_real_credentials(): void
    {
        $template = file_get_contents(base_path('PRODUCTION_ENV_TEMPLATE.txt'));

        $this->assertIsString($template);
        $this->assertStringContainsString('APP_ENV=production', $template);
        $this->assertStringContainsString('APP_DEBUG=false', $template);
        $this->assertStringContainsString('APP_URL=https://YOUR-FINAL-DOMAIN', $template);
        $this->assertStringContainsString('DB_CONNECTION=mysql', $template);
        $this->assertStringContainsString('DB_PASSWORD=YOUR_DATABASE_PASSWORD', $template);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $template);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $template);
        $this->assertStringContainsString('MAIL_PASSWORD=YOUR_SMTP_PASSWORD', $template);
        $this->assertStringNotContainsString('database/database.sqlite', $template);
    }

    public function test_production_readiness_service_contains_required_security_database_build_and_storage_gates(): void
    {
        $service = new ProductionReadiness();
        $checks = collect($service->checks())->keyBy('key');

        foreach ([
            'environment',
            'debug',
            'https_url',
            'app_key',
            'production_database',
            'session_driver',
            'session_encryption',
            'secure_cookie',
            'http_only_cookie',
            'same_site_cookie',
            'cache_store',
            'private_storage',
            'public_storage',
            'build_manifest',
        ] as $key) {
            $this->assertTrue($checks->has($key), $key);
            $this->assertTrue($checks[$key]['required'], $key);
        }

        $this->assertTrue($checks->has('public_storage_link'));
        $this->assertFalse($checks['public_storage_link']['required']);
        $this->assertTrue($checks->has('mail_transport'));
        $this->assertFalse($checks['mail_transport']['required']);
    }

    public function test_readiness_command_is_registered_and_non_strict_mode_reports_without_mutating(): void
    {
        $exit = Artisan::call('nacs:production-check');
        $output = Artisan::output();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('NACS-Phil Production Readiness', $output);
        $this->assertStringContainsString('does not print secrets or modify the server', $output);
    }

    public function test_strict_readiness_fails_in_local_testing_environment(): void
    {
        $exit = Artisan::call('nacs:production-check', ['--strict' => true]);
        $output = Artisan::output();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('Strict production readiness failed', $output);
    }

    public function test_deployment_guide_protects_private_files_and_uses_safe_production_commands(): void
    {
        $guide = file_get_contents(base_path('PRODUCTION_DEPLOYMENT.md'));

        $this->assertIsString($guide);
        $this->assertStringContainsString("Laravel's `public` directory, not the repository root", $guide);
        $this->assertStringContainsString('Do not upload `database/database.sqlite` as the production database', $guide);
        $this->assertStringContainsString('admissions documents must remain private', $guide);
        $this->assertStringContainsString('composer install --no-dev', $guide);
        $this->assertStringContainsString('npm ci', $guide);
        $this->assertStringContainsString('php artisan migrate --force', $guide);
        $this->assertStringContainsString('php artisan nacs:production-check --strict', $guide);
        $this->assertStringContainsString('Do not make the whole repository globally writable', $guide);
    }
}
