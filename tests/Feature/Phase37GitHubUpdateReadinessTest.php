<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase37GitHubUpdateReadinessTest extends TestCase
{
    public function test_quality_gate_covers_pr_main_and_manual_runs(): void
    {
        $path = base_path('.github/workflows/quality-gate.yml');
        $this->assertFileExists($path);

        $workflow = file_get_contents($path);
        $this->assertIsString($workflow);
        $this->assertStringContainsString('pull_request:', $workflow);
        $this->assertStringContainsString('push:', $workflow);
        $this->assertStringContainsString('workflow_dispatch:', $workflow);
        $this->assertStringContainsString('- main', $workflow);
        $this->assertStringContainsString('contents: read', $workflow);
        $this->assertStringContainsString("php-version: '8.4'", $workflow);
        $this->assertStringContainsString('php artisan nacs:functional-check --strict', $workflow);
        $this->assertStringContainsString('php artisan test --stop-on-failure', $workflow);
        $this->assertStringContainsString('npm run build', $workflow);
        $this->assertStringContainsString('git diff --check', $workflow);
        $this->assertStringNotContainsString('secrets.', $workflow);
    }

    public function test_production_deployment_template_is_present_but_not_active(): void
    {
        $template = base_path('.github/deployment-templates/production-deploy.yml');
        $active = base_path('.github/workflows/production-deploy.yml');

        $this->assertFileExists($template);
        $this->assertFileDoesNotExist($active);

        $contents = file_get_contents($template);
        $this->assertIsString($contents);
        $this->assertStringContainsString('NACS_AUTO_DEPLOY_ENABLED', $contents);
        $this->assertStringContainsString('never replace the server .env from Git', $contents);
        $this->assertStringContainsString('backup/rollback point before migrations', $contents);
    }

    public function test_future_update_runbook_protects_runtime_data(): void
    {
        $path = base_path('GITHUB_FUTURE_UPDATES.md');
        $this->assertFileExists($path);

        $runbook = file_get_contents($path);
        $this->assertIsString($runbook);
        $this->assertStringContainsString('`main` is the intended production source branch', $runbook);
        $this->assertStringContainsString('Production auto-deployment is deliberately NOT enabled yet', $runbook);
        $this->assertStringContainsString('production `.env`', $runbook);
        $this->assertStringContainsString('private admissions files', $runbook);
        $this->assertStringContainsString('GitHub Quality Gate', $runbook);
        $this->assertStringContainsString('PHP 8.4.1 or newer', $runbook);
    }

    public function test_host_update_example_is_fast_forward_only_and_migrations_default_off(): void
    {
        $path = base_path('scripts/deployment/production-update.example.sh');
        $this->assertFileExists($path);

        $script = file_get_contents($path);
        $this->assertIsString($script);
        $this->assertStringContainsString('git merge --ff-only origin/main', $script);
        $this->assertStringContainsString('NACS_RUN_MIGRATIONS:-false', $script);
        $this->assertStringNotContainsString('reset --hard', $script);
        $this->assertStringNotContainsString('push --force', $script);
    }

    public function test_agent_guidance_requires_quality_gate_and_host_specific_enablement(): void
    {
        $agents = file_get_contents(base_path('AGENTS.md'));
        $this->assertIsString($agents);
        $this->assertStringContainsString('## GitHub future-update workflow', $agents);
        $this->assertStringContainsString('`.github/workflows/quality-gate.yml`', $agents);
        $this->assertStringContainsString('Do not enable production auto-deployment', $agents);
        $this->assertStringContainsString('production `.env`', $agents);
        $this->assertStringContainsString('PHP runtime baseline: 8.4.1 or newer', $agents);
    }
}
